<?php
include_once('../../lib/tools.php');
$json = new JSON_Resp();

$json->log($_GET);

validate_params(array("email", "code"), $_GET);
$email = $_GET["email"];
$code = $_GET["code"];

// Check code's validity for this email address
$sql = "SELECT * FROM user_email_confirmation WHERE email = '$email' AND created > (NOW() - INTERVAL 1 DAY) AND attempts < 5";
$res = mysqli_query($db, $sql);
if (mysqli_num_rows($res) == 0) {
    $json->status("email");
    $json->error("There's no valid code for this email address. Maybe it expired? Try sending a new one!");
    exit();
}

// Check if code is correct
$sql = "SELECT * FROM user_email_confirmation WHERE email = '$email' AND code = '$code'";
$res = mysqli_query($db, $sql);
if (mysqli_num_rows($res) == 0) {
    $json->status("code");
    $json->error("Hmm this code seems incorrect. Try again!");

    // Increase number of attempts
    $sql = "UPDATE user_email_confirmation SET attempts = attempts + 1 WHERE email = '$email'";
    $res = mysqli_query($db, $sql);

    // Check if we chould block this code
    $sql = "SELECT * FROM user_email_confirmation WHERE email = '$email' AND attempts >= 5";
    $res = mysqli_query($db, $sql);
    if (mysqli_num_rows($res) > 0) {
        $json->error("You have tried too many times! I've invalidated this code. Please request a new one!");
        exit();
    }

    exit();
}

// Correct, mark the code as validated to remove time constraint on the next steps
$sql = "UPDATE user_email_confirmation SET validated = 1 WHERE email = '$email'";
$res = mysqli_query($db, $sql);

?>