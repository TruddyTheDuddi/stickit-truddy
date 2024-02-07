<?php
require_once("tools.php");

/**
 * Utilty functions on the logged-in user
 */
class LoggedUser {
    const SESSION_FIELD = "stickers_logged_user_id";

    /**
     * Is there a logged user? Doesn't go through the 
     * user object creation.
     * @return boolean
     */
    public static function is_logged(){
        return isset($_SESSION[self::SESSION_FIELD]);
    }

    /**
     * Get data on the logged in user
     * @return User object or throws exception if not logged in
     */
    public static function get(){
        // User not logged in
        if (!LoggedUser::is_logged()) {
            throw new Exception("User is not logged in.");
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

?>