<?php
require_once("tools.php");

/**
 * Class to represent album pages.
 * 
 * Pages have an ID and an order (page number) in the album.
 */
class AlbumPage {
    public int $page_id;
    public int $album_id;
    public int $page_number;
    public int $obtained_timestamp;

    /**
     * Constructor
     * @param int $id Page ID
     */
    public function __construct($id) {
        global $db;

        $id = make_sql_safe($id);
        $this->page_id = $id;

        $sql = "SELECT * FROM album_pages WHERE page_id = $id";
        $result = mysqli_query($db, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $page = mysqli_fetch_assoc($result);
            $this->album_id = $page["album_id"];
            $this->page_number = $page["page_num"];
        } else {
            throw new Exception("Page does not exist.");
        }
    }

    /**
     * Set page's order in the album and update the database.
     * @param int $order_num New order
     */
    public function set_order($order_num){
        global $db;

        // Check if user has edit permissions
        $album = new Album($this->album_id);
        $album->can_edit();

        $order_num = make_sql_safe($order_num);
        $sql = "UPDATE album_pages SET page_num = $order_num WHERE page_id = $this->page_id";
        mysqli_query($db, $sql);
    }

    /**
     * Factory, creates a list of AlbumPage
     * objects given an album ID.
     * @param int $album_id Album ID
     */
    public static function get_by_album($album_id) {
        global $db;
        $album_id = make_sql_safe($album_id);

        // Import pages with the DB's order
        $pages = array();
        $sql = "SELECT *, UNIX_TIMESTAMP(P.created) AS created FROM album_pages P WHERE album_id = $album_id";
        $res = mysqli_query($db, $sql);
        while($page = mysqli_fetch_assoc($res)){
            $pages[$page['page_num']] = new AlbumPage($page['page_id']);
        }

        // Make sure the list is cleaned up
        $pages = self::clean_up($pages);

        return $pages;
    }

    /**
     * Create a new page and insert it into 
     * the database as last page.
     * @param int $album_id Album ID
     */
    public static function create($album_id){
        global $db;

        // Check if user has permissions
        $album = new Album($album_id);
        $album->can_edit();

        // Get the last page number
        $pages = self::get_by_album($album_id);
        $last_page = end($pages);
        $last_page_num = $last_page->page_number;

        // Create page
        $sql = "INSERT INTO album_pages (album_id, page_num) VALUES ($album_id, $last_page_num + 1)";
        mysqli_query($db, $sql);

        $page_id = mysqli_insert_id($db);
        return new AlbumPage($page_id);
    }

    /**
     * Clean up pages array, if it's not sequencial or has gaps
     * to have an ordered list, regardless of the page_num field.
     * @param array $pages Array of AlbumPage objects
     */
    private static function clean_up($pages) {
        ksort($pages);
        return array_values($pages);
    }
    
}

?>