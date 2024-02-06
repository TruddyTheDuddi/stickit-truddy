<?php
include_once("tools.php");
include_once("user.php");
include_once("img.php");
include_once("sticker.php");

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
            throw new Exception("Sticker does not exist.");
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
        
        $this->author = new User($album["user_id"]);
        
        // Pages
        global $db;
        $sql = "SELECT *, UNIX_TIMESTAMP(P.created) AS created FROM album_pages P WHERE album_id = $this->id";
        $res = mysqli_query($db, $sql);
        while($page = mysqli_fetch_assoc($res)){
            $this->pages[$page['page_num']] = $page;
        }

        // Clean up pages, if it's not sequencial or has gaps
        ksort($this->pages);
        $this->pages = array_values($this->pages);

        // Load the stickers            
        $this->stickers = array();
        $sql = "SELECT sticker_id FROM stickers WHERE album_id = $this->id";
        $res = mysqli_query($db, $sql);
        while($sticker = mysqli_fetch_assoc($res)){
            array_push($this->stickers, new Sticker($sticker['sticker_id']));
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

$a = new Album(1);

echo $a;

?>
