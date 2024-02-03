<?php
include_once("tools.php");

/**
 * Utilty functions on the logged-in user
 */
class LoggedUser {
    const SESSION_FIELD = "stickers_logged_user_id";

    /**
     * Get data on the logged in user
     * @return User object or null if not logged in
     */
    public static function get(){
        // User not logged in
        if (!isset($_SESSION[self::SESSION_FIELD])) {
            return null;
        }
        $user_id = $_SESSION[self::SESSION_FIELD];
        return new User($user_id);
    }

    /**
     * Set the logged in user
     * @param int $user_id Target user ID
     */
    public static function set($user_id) {
        $_SESSION[self::SESSION_FIELD] = $user_id;
    }

    /**
     * Unset the logged in user (logout)
     */
    public static function unset() {
        unset($_SESSION[self::SESSION_FIELD]);
    }

    /**
     * Check the permission of the logged user
     */
    public static function is_above($role){
        $user = self::get();
        // If user not logged in, no permission
        if(!isset($user)){
            return false;
        }
        return $user->is_above($role);
    }
}

/**
 * Class that resprents a user
 */
class User {
    // Default avatar (no shit! I add too many comments)
    private const DEFAULT_AVATAR = "default.png";

    // Roles variables
    const ROLE_USER  = "user";
    const ROLE_MOD   = "mod";
    const ROLE_ADMIN = "admin";


    // Roles array
    const ROLES = array(
        User::ROLE_USER => 0,
        User::ROLE_MOD => 1,
        User::ROLE_ADMIN => 2
    );

    // User data
    public $id;
    public $username;
    public $email;
    public $passhash;
    public $created;
    public $avatar;
    public $role;
    public $banned;

    /**
     * User constructor. It can be constructed in two ways:
     *  1. By passing the user ID: if you have the ID already
     *  2. By passing an associative array with the user data: 
     * if want to use custom SQL queries and generate all users.
     * 
     * @param mixed $identifier User ID or SQL associative array
     */
    public function __construct($identifier = null) {
        if (is_numeric($identifier)) {
            $this->fetch_by_id($identifier);
        } elseif (is_array($identifier)) {
            $this->form_array($identifier);
        }
    }

    /**
     * Fetch user data by user ID and construct the object.
     * @param int $id User ID
     */
    private function fetch_by_id($id) {
        global $db;

        $id = make_sql_safe($id);
        $sql = "SELECT *, UNIX_TIMESTAMP(created) AS created FROM users WHERE user_id = $id";

        $result = mysqli_query($db, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $this->form_array($user);
        }
    }

    /**
     * Construct user object from an array.
     * @param array $user Array of user data
     */
    private function form_array($user) {
        $this->id = $user["user_id"];
        $this->username = $user["username"];
        $this->email = $user["email"];
        $this->passhash = $user["passhash"];
        $this->created = $user["created"];
        $this->avatar = $user["avatar"] ?? self::DEFAULT_AVATAR;
        $this->role = $user["role"];
        $this->banned = $user["banned"];
    }

    /**
     * Check if user is valid
     */
    public function is_valid() {
        return $this->id != null;
    }

    /**
     * Check if user has permissions for a level
     * @param string $role Role string to check
     */
    public function is_above($role) {
        return self::ROLES[$this->role] >= self::ROLES[$role];
    }

    /**
     * Print user data. Cool for debugging.
     */
    public function __toString() {
        $print = "<b>User:</b><br>";
        if(!$this->is_valid()) {
            $print .= "Invalid user<br>";
            return $print;
        }
        foreach ($this as $key => $value) {
            $print .= "$key: <code>$value</code><br>";
        }
        return $print;
    }
}

// // Test the class
// LoggedUser::set(1);
// $u = LoggedUser::get();
// if($u == null) {
//     echo "Not logged in";
// } else {
//     echo $u;
// }
?>