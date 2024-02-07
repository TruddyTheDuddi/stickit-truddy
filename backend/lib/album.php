<?php
require_once("tools.php");
require_once("user.php");
require_once("sticker.php");

/**
 * Utility class to retrieve albums.
 * 
 * This time, I've tried using the throw messages so that 
 * the callee handles the error displaying however it wants,
 * and having more abstraction.
 */
class Album {
    public int $id;
    public string $name;
    public string $description;
    public bool $is_available;

    public User $author;    // A user object

    public array $pages;    // Index is order, value is ID of the page in DB

    public array $stickers; // List of the stickers (not loaded by default)

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
     * Static factory method to create a new Album object
     * and insert it into the database.
     * @return Album The created Album object.
     */
    public static function create_album(){
        global $db;

        // Check if user is a creator
        if(!LoggedUser::get()->is_creator){
            throw new Exception("You are not a creator, you cannot create new albums!");
        }

        // Create album
        $sql = "INSERT INTO albums (author_id, name, description) VALUES (".LoggedUser::get()->id.", '', '')";
        mysqli_query($db, $sql);

        $album_id = mysqli_insert_id($db);
        return new Album($album_id);
    }

    /**
     * Fetch album from database by its ID
     * @param int $album_id of the album
     */
    private function fetch_by_id($album_id){
        global $db;
        $album_id = make_sql_safe($album_id);

        $sql = "SELECT *, UNIX_TIMESTAMP(A.created) AS created FROM albums A LEFT JOIN users U ON A.author_id = U.user_id WHERE album_id = $album_id";
        $res = mysqli_query($db, $sql);
        if (mysqli_num_rows($res) > 0) {
            $sticker_data = mysqli_fetch_assoc($res);
            $this->from_array($sticker_data);
        } else {
            throw new Exception("Album does not exist.");
        }
    }

    /**
     * Create album from associative array directly by 
     * the row of the `stickers` table
     * @param array $sticker 
     */
    private function from_array($album) {
        // Fetch basic info
        $this->id = $album["album_id"];
        $this->name = $album["name"];
        $this->description = $album["description"];
        $this->is_available = $album["available"];
        
        $this->author = new User($album["user_id"]);
        
        // Pages
        global $db;
        $sql = "SELECT *, UNIX_TIMESTAMP(P.created) AS created FROM album_pages P WHERE album_id = $this->id";
        $res = mysqli_query($db, $sql);
        while($page = mysqli_fetch_assoc($res)){
            $this->pages[$page['page_num']] = $page;
        }

        // Clean up pages, if it's not sequencial or has gaps
        if(!empty($this->pages)){
            ksort($this->pages);
            $this->pages = array_values($this->pages);
        }
        
        // Load the stickers            
        $this->stickers = array();
        $sql = "SELECT sticker_id FROM stickers WHERE album_id = $this->id";
        $res = mysqli_query($db, $sql);
        while($sticker = mysqli_fetch_assoc($res)){
            array_push($this->stickers, new Sticker($sticker['sticker_id']));
        }
    }

    /**
     * Set album's name
     */
    public function set_name($name){
        global $db;
        $this->name = make_sql_safe($name);

        $sql = "UPDATE albums SET name = '$this->name' WHERE album_id = $this->id";
        if(!mysqli_query($db, $sql)){
            throw new Exception("Rip: ".$db->error);
        }
    }

    /**
     * Set album's description
     */
    public function set_desc($desc){
        global $db;
        $this->description = make_sql_safe($desc);

        $sql = "UPDATE albums SET description = '$this->description' WHERE album_id = $this->id";
        if(!mysqli_query($db, $sql)){
            throw new Exception("Rip: ".$db->error);
        }
    }

    /**
     * Set album's availablity
     */
    public function set_availability($status){
        global $db;

        // Check for requirements before publishing
        if($status){
            // If want to publish
            if($this->name == ""){
                throw new Exception("Your album must have a name!");
            }

            if($this->description == ""){
                throw new Exception("Your album must have at least a short descirption.");
            }

            if(empty($this->pages)){
                throw new Exception("Your album must have at least one page");
            }

            // Check if has at least one sticker on one page
            $has_placed_sticker = false;
            foreach ($this->stickers as $id => $s) {
                if(isset($s->page_id)){
                    echo "asdf";
                    $has_placed_sticker = true;
                }
            }
            if(!$has_placed_sticker){
                throw new Exception("Your album must contain at least one placed sticker");
            }

            // Set the album as available
            $this->is_available = true;
        } else {
            $this->is_available = false;
        }

        $sql = "UPDATE albums SET available = $this->is_available WHERE album_id = $this->id";
        if(!mysqli_query($db, $sql)){
            throw new Exception("Rip: ".$db->error);
        }
    }

    public function __toString() {
        $print = "<b>Album:</b><br>";
        foreach ($this as $key => $value) {
            if(!is_array($value)) {
                $print .= "$key: <code>$value</code><br>";
            } else {
                $print .= "$key: <pre>".print_r($value, true)."</pre><br>";
            }
        }
        return $print;
    }
}

?>
