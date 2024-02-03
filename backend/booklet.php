<?php
include_once("tools.php");
include_once("user.php");

/**
 * Utility class to retrieve albums with stickers. 
 * 
 * This time, I've tried using the throw messages so that 
 * the callee handles the error displaying however it wants,
 * and having more abstraction.
 */
class Album {
    public $album_id;
    public $pages;

    /**
     * Create an album based on an ID
     */
    public function __construct($id) {
        global $db;
        $id = make_sql_safe($id);

        // Check if album exists
        $sql = "SELECT * FROM albums WHERE album_id = $id";
        $res = mysqli_query($db, $sql);
        if (mysqli_num_rows($res) == 0) {
            throw new Exception("This album does not exist!");
        }

        // if(!LoggedUser::is_above(User::ROLE_USER)){
        //     throw new Exception("User does not have the permissions to access this file.");
        // }

        $this->album_id = $id;
    }

    public function get_pages(){

    }
}

class Sticker {
    public $id;
    public $name;
    public $img_path;
    public $img_path_secret;    
    public $transform;              // How is the sticker displayed on the page
    public $user_revealed = false;  // Was this sticker discovered by user?

    // TODO: fill in following variables
    public $album_id;
    public $page_id;
    public $author_id;

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
     * Fetch sticker from database by its ID
     * @param int $id of the stricker
     */
    private function fetch_by_id($id){
        global $db;

        $id = make_sql_safe($id);
        $sql = "SELECT *, UNIX_TIMESTAMP(S.created) AS created FROM stickers S INNER JOIN album_pages P ON S.page_id = P.page_id INNER JOIN albums A ON P.album_id = A.album_id WHERE sticker_id = $id";
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
        $this->name = $sticker["name"];
        $this->img_path = $sticker["img_path"];
        $this->img_path_secret = $sticker["img_path_secret"];
        $this->author_id = $sticker["author_id"];
        $this->album_id = $sticker["album_id"];

        // Transform data
        $this->transform = array(
            "pos_x" => $sticker['pos_x'],
            "pos_y" => $sticker['pos_y'],
            "rotation" => $sticker['rotation'],
            "scale" => $sticker['scale']
        );

        // Check if user revealed sticker
        $user = LoggedUser::get();
        if(isset($user)){
            $sql = "SELECT count(*) AS sticker_counts FROM sticker_user_collected WHERE user_id = $user->id AND sticker_id = $this->id";
            $res = mysqli_query($db, $sql);
            $data = mysqli_fetch_assoc($res);
            if($data['sticker_counts'] > 0){
                $this->user_revealed = true;
            }
        }

        // If sticker unavailable, remove the img_path
        if(!$this->user_revealed){
            $this->img_path = null;
        }
    }
    
    /**
     * Update the transformation parameters of this sticker
     * in the Database.
     */
    public function update_transform($pos_x, $pos_y, $rotation, $scale){
        // Check user perms
        if(!LoggedUser::is_above(User::ROLE_USER)){
            throw new Exception("User does not have the permissions to access this file.");
        }


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
     * Moves the sticker to a specific page, throws exception if
     */
    public function move_to_page($id){

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

echo new Sticker(1);

?>