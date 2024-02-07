<?php
// Initiate a new user account creation
include_once('../lib/tools.php');
include_once('../lib/mail.php');
$json = new JSON_Resp();

define("DEBUG", False);

validate_params(array("email"), $_GET);
$email = $_GET["email"];

// Simple email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $json->error("Invalid email address!");
    exit();
}

// Check if the email is already in use by another account
$sql = "SELECT * FROM users WHERE email = '$email'";
$res = mysqli_query($db, $sql);
if (mysqli_num_rows($res) > 0) {
    $json->error("This email is already in use!");
    exit();
}

// Check if user has already requested an email in the last 3 minutes
$sql = "SELECT * FROM user_email_confirmation WHERE email = '$email' AND created > (NOW() - INTERVAL 3 MINUTE)";
$res = mysqli_query($db, $sql);
if (mysqli_num_rows($res) > 0) {
    $json->error("I've already sent an email to this address in the last 3 minutes. Please wait a little!");
    exit();
}

// Check if this IP has already requested two emails in the last 10 minutes, 
// and if the email is different once again.
$ip = make_sql_safe(md5($_SERVER['REMOTE_ADDR']));
$sql = "SELECT * FROM user_email_confirmation WHERE ip_addr = '$ip' AND created > (NOW() - INTERVAL 10 MINUTE) AND email != '$email'";
$res = mysqli_query($db, $sql);
if (mysqli_num_rows($res) > 1) {
    $json->error("I have already sent two emails for two different addresses in the last 10 minutes for you! Who are you trying to fool?!");
    exit();
}

// Generate a random confirmation code between 10000 and 99999
$code = rand(10000, 99999);

// Insert the new confirmation code into the database, update the old one if it exists
// NOTE: we only care to see if the code was validated once, any new emails sent won't do anything
$sql = "INSERT INTO user_email_confirmation (email, code, ip_addr) VALUES ('$email', '$code', '$ip') ON DUPLICATE KEY UPDATE code = '$code', ip_addr = '$ip', created = NOW(), attempts = 0";
$res = mysqli_query($db, $sql);
if (!$res) {
    $json->error("Database error: ".mysqli_error($db));
    exit();
}

// Send the email (if not in debug mode)
if(DEBUG) {
    $json->success("Confirmation code: $code");
} else {
    $mail = new Mailer($email);
    if($mail->send_confirmation_code($code)){
        $json->success("Email sent to $email successfully!");
    } else {
        // Internal error :(
        $json->error("There was an issue when sending the confirmation e-mail. Sorry, please again later.");
    
        // Remove the confirmation code from the database
        $sql = "DELETE FROM user_email_confirmation WHERE email = '$email'";
        $res = mysqli_query($db, $sql);
    }
}

?>