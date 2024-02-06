<?php
include_once("tools.php");
include_once("user.php");
include_once("img.php");

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

?>
