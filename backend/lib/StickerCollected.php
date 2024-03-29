<?php
require_once("tools.php");

/**
 * This class represents a relationship/connection between the
 * user and a sticker. It is used to represent the fact that the
 * user has collected a sticker, acts as a Sticker decorator.
 */
class StickerCollected {
    public int $rel_id;
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
        $this->rel_id = $rel_id;

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
     * @param int $album_id Album ID
     * stickers that are not sticked to an album
     */
    public static function get_by_user($user_id, $album_id = null) {
        global $db;
        $user_id = make_sql_safe($user_id);
        
        if ($album_id) {
            $album_id = make_sql_safe($album_id);
            $sql = "SELECT * FROM user_rel_stickers WHERE user_id = $user_id AND is_sticked = 0 AND sticker_id IN (SELECT sticker_id FROM stickers WHERE album_id = $album_id)";
        } else {
            $sql = "SELECT * FROM user_rel_stickers WHERE user_id = $user_id AND is_sticked = 0";
        }

        $result = mysqli_query($db, $sql);
        $rels = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rels[] = new StickerCollected($row["rel_id"]);
        }
        return $rels;
    }

    /**
     * Checks if a user has sticked a sticker
     * @param int $sticer_id Sticker ID
     * @return boolean True if the user has sticked the sticker
     */
    public static function has_user_sticked($sticer_id){
        global $db;
        $user_id = LoggedUser::get()->id;
        $sticer_id = make_sql_safe($sticer_id);
        $sql = "SELECT * FROM user_rel_stickers WHERE user_id = $user_id AND sticker_id = $sticer_id AND is_sticked = 1";
        $result = mysqli_query($db, $sql);
        return mysqli_num_rows($result) > 0;
    }

    /**
     * Create a new relationship between a user and 
     * a sticker and insert it into the database.
     * @param int $user_id User ID
     * @param int $sticker_id Sticker ID
     * @return StickerCollected The created StickerCollected object
     */
    public static function create($user_id, $sticker_id) {
        global $db;
        $user_id = make_sql_safe($user_id);
        $sticker_id = make_sql_safe($sticker_id);
        $sql = "INSERT INTO user_rel_stickers (user_id, sticker_id, obtained_time) VALUES ($user_id, $sticker_id, NOW())";
        mysqli_query($db, $sql);

        $rel_id = mysqli_insert_id($db);
        return new StickerCollected($rel_id);
    }

    /**
     * Stick a sticker to an album
     */
    public function stick() {
        global $db;
        
        // Check if logged user is the owner of the rel
        if($this->user->id != LoggedUser::get()->id){
            throw new Exception("You can't stick a sticker that you don't own.");
        }

        // Check if same sticker is already sticked
        $sql = "SELECT * FROM user_rel_stickers WHERE user_id = " . $this->user->id . " AND sticker_id = " . $this->sticker->id . " AND is_sticked = 1";
        $result = mysqli_query($db, $sql);
        if (mysqli_num_rows($result) > 0) {
            throw new Exception("This sticker is already sticked to an album.");
        }

        // Stick the sticker
        $this->is_sticked = true;
        $sql = "UPDATE user_rel_stickers SET is_sticked = 1 WHERE rel_id = " . $this->rel_id;
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