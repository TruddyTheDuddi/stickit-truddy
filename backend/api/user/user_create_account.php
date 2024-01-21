<?php
// Initiate a new user account creation
include_once("../../tools.php");
$json = new JSON_Resp();

// The validator ID is a random string that is linked to an email confirmation,
// and we use it to trace back to the initial email address.
validate_params(array("username", "password", "email"), $_GET);
$username = $_GET["username"];
$password = $_GET["password"];
$email = $_GET["email"];

// Check if confirmation code was validated (email was verified)
$sql = "SELECT * FROM user_email_confirmation WHERE email = '$email' AND validated = 1";
$res = mysqli_query($db, $sql);
if (mysqli_num_rows($res) == 0) {
    $json->error("Looks like something went wrong while validating your email address. Please try again!");
    exit();
}

// Check if username is already in use
$sql = "SELECT * FROM users WHERE username = '$username'";
$res = mysqli_query($db, $sql);
if (mysqli_num_rows($res) > 0) {
    $json->status("username");
    $json->error("Sorry, this username is already in use.");
    exit();
}

// Check if email is already in use
$sql = "SELECT * FROM users WHERE email = '$email'";
$res = mysqli_query($db, $sql);
if (mysqli_num_rows($res) > 0) {
    $json->error("Sorry, seems like this email is already in use by someone else.");
    exit();
}

// Validate username: 4-16 characters, only letters, numbers, dashes and underscores
if (!preg_match("/^[a-zA-Z0-9_-]{4,16}$/", $username)) {
    $json->status("username");
    $json->error("This username doesn't seem to meet the requirements. Please try another one!");
    exit();
}

// Password, length above 10 characters
if (strlen($password) < 10) {
    $json->status("password");
    $json->error("Your password is too short! Please use at least 10 characters.");
    exit();
}
$passhash = password_hash($password, PASSWORD_DEFAULT);

// Insert the new user into the database
$sql = "INSERT INTO users (username, email, passhash) VALUES ('$username', '$email', '$passhash')";
if (!mysqli_query($db, $sql)) {
    $json->error("Sorry, there was an error while creating your account. Could you try again?");
    exit();
}

// Add the user_id into the confirmation table
$user_id = mysqli_insert_id($db);
$sql = "UPDATE user_email_confirmation SET user_id = $user_id WHERE email = '$email'";
mysqli_query($db, $sql);

$json->success("Your account has been created! You can now log in.");

?>