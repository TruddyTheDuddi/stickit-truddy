<?php
include_once("keys.php");

/**
 * General database login called with include
 */
$servername = DB_SERVERNAME;
$username = DB_USERNAME;
$password = DB_PASSWORD;
$database = DB_NAME;

$db = mysqli_connect($servername, $username, $password, $database);
mysqli_select_db($db, $database);

$db->set_charset('utf8mb4');

/**
 * Makes a string into a MySQL safe string
 */
function make_sql_safe($string){
    global $db;
    return mysqli_real_escape_string($db, htmlspecialchars($string));
}
?>