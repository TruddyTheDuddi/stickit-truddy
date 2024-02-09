<?php
require_once("tools.php");

/**
 * A sticker object is built by giving the sticker's ID or an
 * associative array. You can update its properties and it will
 * reflect in the database:
 *  - Name
 *  - Image (revealed and shadow version)
 *  - Transformation
 *  - Changing the page (only if it is ready: images & name set)
 *  - If it's obtainable
 * 
 * Write rules are based on the logged user. Writes are enabled
 * if the user is author of album or has greater than the MOD role.
 * 
 * This allows to create new stickers. A new one can be created 
 * by providing an album ID.
 * 
 * A sticker is visible if it is added to a page in the album, which
 * by default is null.
 * 
 * A sticker is revealed (image is visible) if the user has found it
 * if the user has it once or is the author of the sticker.
 */
class Sticker {
    public int $id;
    public ?string $name;

    public string $img_path;
    public string $img_path_secret;

    public array $transform;                // How is the sticker displayed on the page
    public bool $user_revealed = false;     // Was this sticker discovered by user?
    public bool $has_sticked = false;       // Has the user sticked this sticker? (for album rendering)
    public bool $obtainable;                // Is this sticker obtainable from packs?

    public int $album_id;
    public ?int $page_id;
    public User $author;

    /**
     * Constructor may take integer id or an
     * associative array (when bulk object creation
     * for example).
     * @param mixed $query
     */
    public function __construct($query){
        if (is_numeric($query)){
            $this->fetch_by_id($query);
        } else {
            $this->from_array($query);
        }
    }

    /**
     * Static factory method to create a new Sticker object and 
     * insert it into the database.
     * @return Sticker The created Sticker object.
     */
    public static function create_sticker($album_id) {
        global $db;
        $album_id = make_sql_safe($album_id);

        // Check if user is a creator
        if(!LoggedUser::get()->is_creator){
            throw new Exception("You are not a creator, you cannot create new stickers!");
        }

        // Check if can edit album
        $album = new Album($album_id);
        $album->can_edit();
        
        $sql = "INSERT INTO stickers (album_id, img_path, img_path_secret) VALUES ($album_id, '', '')";
        mysqli_query($db, $sql);

        $sticker_id = mysqli_insert_id($db);
        return new Sticker($sticker_id);
    }

    /**
     * Fetch sticker from database by its ID
     * @param int $id of the stricker
     */
    private function fetch_by_id($id){
        global $db;
        $id = make_sql_safe($id);

        $sql = "SELECT *, UNIX_TIMESTAMP(S.created) AS created, A.name AS album_name, S.name AS sticker_name FROM stickers S LEFT JOIN album_pages P ON S.page_id = P.page_id INNER JOIN albums A ON S.album_id = A.album_id WHERE sticker_id = $id";
        $res = mysqli_query($db, $sql);
        if (mysqli_num_rows($res) > 0) {
            $sticker_data = mysqli_fetch_assoc($res);
            $this->from_array($sticker_data);
        } else {
            throw new Exception("Sticker does not exist.");
        }
    }

    /**
     * Create sticker from associative array directly by 
     * the row of the `stickers` table
     * @param array $sticker 
     */
    private function from_array($sticker) {
        global $db;

        $this->id = $sticker["sticker_id"];
        $this->page_id = $sticker["page_id"];
        $this->name = $sticker["sticker_name"];
        $this->img_path = $sticker["img_path"];
        $this->img_path_secret = $sticker["img_path_secret"];
        $this->author = new User($sticker["author_id"]);
        $this->album_id = $sticker["album_id"];
        $this->obtainable = $sticker["obtainable"];

        // Transform data
        $this->transform = array(
            "pos_x" => (float)$sticker['pos_x'],
            "pos_y" => (float)$sticker['pos_y'],
            "rotation" => (float)$sticker['rotation'],
            "scale" => (float)$sticker['scale']
        );

        // If the user is logged in
        if(LoggedUser::is_logged()){
            // Soft check if user revealed sticker
            $user_id = LoggedUser::get()->id;
            $sql = "SELECT count(*) AS sticker_counts FROM user_rel_stickers WHERE user_id = $user_id AND sticker_id = $this->id";
            $res = mysqli_query($db, $sql);
            $data = mysqli_fetch_assoc($res);
            // User has at least one or is the author
            if($data['sticker_counts'] > 0 || $this->author->id == $user_id){
                $this->user_revealed = true;
            }
        }

        // Check if sticker has been sticked
        $this->has_sticked = StickerCollected::has_user_sticked($this->id);

        // If sticker unavailable, remove the img_path
        if(!$this->user_revealed){
            $this->img_path = "";
        }
    }
    
    /**
     * Update the transformation parameters of this sticker
     * in the Database.
     */
    public function update_transform($pos_x, $pos_y, $rotation, $scale){
        // Check perms
        $album = new Album($this->album_id);
        $album->can_edit();

        // Update data in object
        $this->transform = array(
            "pos_x" => $pos_x,
            "pos_y" => $pos_y,
            "rotation" => $rotation,
            "scale" => $scale
        );

        // Update sticker in database
        global $db;
        $pos_x = make_sql_safe($pos_x);
        $pos_y = make_sql_safe($pos_x);
        $rotation = make_sql_safe($rotation);
        $scale = make_sql_safe($scale);

        $sql = "UPDATE stickers SET pos_x = $pos_x, pos_y = $pos_y, rotation = $rotation, scale = $scale WHERE sticker_id = $this->id";
        if(!mysqli_query($db, $sql)){
            throw new Exception("Rip: ".$db->error);
        }
    }

    /**
     * Moves the sticker to a specific page, throws exception
     * @param int $id of the page
     */
    public function move_to_page($id = null){
        global $db;

        // Check perms
        $album = new Album($this->album_id);
        $album->can_edit();

        // Check if sticker has name before able to add to page
        if(!isset($this->name)){
            throw new Exception("You need to set a name to this sticker before you can add it to a page.");
        }

        // Must have an image setup
        if($this->img_path != "" OR $this->img_path_secret != ""){
            throw new Exception("The image for this sticker is not set. Please add one.");
        }

        // If id is null, set page to null
        if(isset($id)){
            $id = make_sql_safe($id);

            // Check if page exists
            $page = new AlbumPage($id);
    
            // Perform sticker's page update
            $this->page_id = $page->page_id;
            $sql = "UPDATE stickers SET page_id = $id WHERE sticker_id = $this->id";
        } else {
            // Perform sticker's page update
            $this->page_id = null;
            $sql = "UPDATE stickers SET page_id = NULL WHERE sticker_id = $this->id";
        }

        if(!mysqli_query($db, $sql)){
            throw new Exception("Rip: ".$db->error);
        }
    }

    /**
     * Set obtainability of this sticker
     * @param bool $status
     */
    public function set_obtainability($status){
        global $db;

        // Check perms
        $album = new Album($this->album_id);
        $album->can_edit();
        
        $bool = make_sql_safe((int)$status);

        $this->obtainable = $status;
        $sql = "UPDATE stickers SET obtainable = $bool WHERE sticker_id = $this->id";
        if(!mysqli_query($db, $sql)){
            throw new Exception("Rip: ".$db->error);
        }
    }

    /**
     * Updating the image for the sticker
     */
    public function change_image($file){
        global $db;

        // Check perms
        $album = new Album($this->album_id);
        $album->can_edit();

        // Upload and create images
        $tmp_sticker = Img::image_process($file);
        $tmp_shadow_sticker = Img::create_secret_sticker($tmp_sticker);

        // Move to folders
        Img::move($tmp_sticker, ImgPaths::PATH_STICKERS_REVEAL);
        Img::move($tmp_shadow_sticker, ImgPaths::PATH_STICKERS_HIDDEN);

        // Prepare for DB update
        $sticker_name = make_sql_safe(basename($tmp_sticker));
        $sticker_shadow_name = make_sql_safe(basename($tmp_shadow_sticker));

        $this->img_path = basename($sticker_name);
        $this->img_path_secret = basename($sticker_shadow_name);
        
        // Update file names in database
        $sql = "UPDATE stickers SET img_path = '$sticker_name', img_path_secret = '$sticker_shadow_name' WHERE sticker_id = $this->id";
        if(!mysqli_query($db, $sql)){
            throw new Exception("Rip: ".$db->error);
        }
    }

    /**
     * Update name of the sticker
     */
    public function change_name($name){
        global $db;

        // Check perms
        $album = new Album($this->album_id);
        $album->can_edit();
        

        // Update name in database
        $name = make_sql_safe(trim($name));
        if($name == ""){
            // If empty, set to null, but only if page_id is null
            if(!isset($this->page_id)){
                $this->name = null;
                $sql = "UPDATE stickers SET name = NULL WHERE sticker_id = $this->id";
            }
        } else {
            $this->name = $name;
            $sql = "UPDATE stickers SET name = '$name' WHERE sticker_id = $this->id";
        }
        if(!mysqli_query($db, $sql)){
            throw new Exception("Rip: ".$db->error);
        }
    }

    /**
     * Get stickers by album
     * @param int $album_id
     */
    public static function get_by_album($album_id){
        global $db;
        $album_id = make_sql_safe($album_id);

        $sql = "SELECT * FROM stickers WHERE album_id = $album_id";
        $result = mysqli_query($db, $sql);
        $stickers = array();
        while($row = mysqli_fetch_assoc($result)){
            $stickers[$row['sticker_id']] = new Sticker($row['sticker_id']);
        }
        return $stickers;
    }

    public function __toString() {
        $print = "<b>Sticker:</b><br>";
        foreach ($this as $key => $value) {
            if(!is_array($value)) {
                $print .= "$key: <code>$value</code><br>";
            } else {
                $print .= "$key: <code>".print_r($value, true)."</code><br>";
            }
        }
        return $print;
    }
}

?>
