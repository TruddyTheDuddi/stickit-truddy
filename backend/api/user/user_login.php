<?php
include_once("../../tools.php");
include_once("../../user.php");
$json = new JSON_Resp();

validate_params(array("username", "password"), $_GET);
$username = $_GET["username"];
$password = $_GET["password"];

// Check if username exists
$sql = "SELECT * FROM users WHERE username = '$username'";
$res = mysqli_query($db, $sql);
if (mysqli_num_rows($res) == 0) {
    $json->status("username");
    $json->error("This username doesn't exist!");
    exit();
}

// Check if password is correct
$user = mysqli_fetch_assoc($res);
if (!password_verify($password, $user["passhash"])) {
    $json->status("password");
    $json->error("This password is incorrect!");
    exit();
}

$logged_user = new User($user["user_id"]);

// Check if user is banned
if($logged_user->banned) {
    $json->error("Oh no, you were are banned from this website.");
    exit();
}

// Login successful, set session
LoggedUser::set($logged_user->id);

// Add login entry in user_logins
$ip = make_sql_safe(md5($_SERVER['REMOTE_ADDR']));
$sql = "INSERT INTO user_logins (user_id, ip_addr) VALUES ($logged_user->id, '$ip')";
mysqli_query($db, $sql);
?>