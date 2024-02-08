<?php
require_once("tools.php");

/**
 * This class represents a relationship/connection between the
 * user and a sticker. It is used to represent the fact that the
 * user has collected a sticker, acts as a Sticker decorator.
 */
class StickerCollected {
    public Sticker $sticker;
    public User $user;
    public bool $is_sticked;
    public int $obtained_timestamp;

    /**
     * Constructor
     * @param int $rel_id ID of the relationship in the database
     */
    public function __construct($rel_id) {
        global $db;

        $rel_id = make_sql_safe($rel_id);
        $sql = "SELECT *, UNIX_TIMESTAMP(obtained_time) AS obtained_time FROM user_rel_stickers WHERE rel_id = $rel_id";
        $result = mysqli_query($db, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $rel = mysqli_fetch_assoc($result);
            $this->sticker = new Sticker($rel["sticker_id"]);
            $this->user = new User($rel["user_id"]);
            $this->is_sticked = $rel["is_sticked"];
            $this->obtained_timestamp = $rel["obtained_time"];
        } else {
            throw new Exception("Sticker relationship does not exist.");
        }
    }

    /**
     * Factory, creates a list of StickerCollected
     * objects given a user ID.
     * @param int $user_id User ID
     * @param boolean $only_unsticked If true, only return
     * stickers that are not sticked to an album
     */
    public static function get_by_user($user_id, $only_unsticked = true) {
        global $db;
        $user_id = make_sql_safe($user_id);
        
        if($only_unsticked){
            // Get only unsticked stickers
            $sql = "SELECT * FROM user_rel_stickers WHERE user_id = $user_id AND is_sticked = 0";
        } else {
            // Get all stickers (sticked and unsticked)
            $sql = "SELECT * FROM user_rel_stickers WHERE user_id = $user_id";
        }

        $result = mysqli_query($db, $sql);
        $rels = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rels[] = new StickerCollected($row["rel_id"]);
        }
        return $rels;
    }

    /**
     * Create a new relationship between a user and 
     * a sticker and insert it into the database.
     * @param int $user_id User ID
     * @param int $sticker_id Sticker ID
     */
    public static function create($user_id, $sticker_id) {
        global $db;
        $user_id = make_sql_safe($user_id);
        $sticker_id = make_sql_safe($sticker_id);
        $sql = "INSERT INTO user_rel_stickers (user_id, sticker_id, obtained_time) VALUES ($user_id, $sticker_id, NOW())";
        mysqli_query($db, $sql);
    }

    /**
     * Stick a sticker to an album
     * @param int $rel_id ID of the relationship
     */
    public static function stick($rel_id) {
        global $db;
        $rel_id = make_sql_safe($rel_id);
        $sql = "UPDATE user_rel_stickers SET is_sticked = 1 WHERE rel_id = $rel_id";
        mysqli_query($db, $sql);
    }

    /**
     * Public tostring method
     */
    public function __toString() {
        return "<b>StickerCollected:</b><br> " . $this->sticker . " <br>by<br> " . $this->user;
    }
}

?>