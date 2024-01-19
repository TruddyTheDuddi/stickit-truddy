<?php
// Start session if it hasn't been started
if (!session_id()) session_start();

include_once("keys.php");
include_once("db_connect.php");

// Picture def
define("MAX_FILE_SIZE", 10*1000*1000); // 10Mb
define("ALLOWED_PIC", array('image/png','image/jpg','image/jpeg'));
define("MAX_FILE_DIM", 2000); // 2000px

// Set timezone
date_default_timezone_set('Europe/Vienna');

/**
 * Create a proper JSON response for XHR requests.
 * 
 * A response will have the following values:
 * | `success` - Did the request succeed? (true/false)
 * | `msg`     - Field for the main message
 * | `payload` - Array of your custom designed fields
 * | `debug`
 *     | `log`     - Array of debug log messages
 *     | `ms_time` - Time it took in milliseconds to execute PHP
 * 
 * By default, the response success is marked as successful, 
 * so you need to change it manually if you want to show an error.
 * 
 * If $json_header is true, then the object changes the header 
 * type to application/json. Turn off for debugging
 */
class JSON_Resp {
    private $start_time;
    private $success; // false or true
    private $status; // a short string justifying the status (for frontend)
    private $msg;
    private $debug_log;
    private $payload;

    function __construct($json_header = true){
        if ($json_header) header('Content-Type: application/json');
        $this->start_time = round(microtime(true) * 1000);
        $this->success = true;
        $this->msg = "";
        $this->payload = array();
        $this->debug_log = array();
        $this->status = null;
    }

    /**
     * Show that something went wrong with 
     * the message telling the cause (optional, 
     * will override previous message)
     */
    function error($error_msg = "") {
        $this->success = false;
        $this->msg = ($error_msg == "") ? $this->msg : $error_msg;
    }

    /**
     * Validate response's status to show everything
     * went fine and computation was successful 
     * (optional, will override previous message)
     */
    function success($success_msg = "") {
        $this->success = true;
        $this->msg = ($success_msg == "") ? $this->msg : $success_msg;
    }

    /**
     * Set response message
     */
    function message($new_msg) {
        $this->msg = $this->msg.$new_msg;
    }

    /**
     * Set a status
     */
    function status($new_status) {
        $this->status = $new_status;
    }

    /**
     * Add a new value into the `payload` field. 
     * If can be a simple value or an array
     * @param string $identifier Key of value
     * @param any $val Value
     */
    function add_field($identifier, $val){
        $this->payload[$identifier] = $val;
    }

    /**
     * Add a new debug entry into the debug log
     */
    function log($new_log) {
        array_push($this->debug_log, $new_log);
    }

    /**
     * Prints result on end of PHP
     * If the $json_header is enabled then you MUST NOT 
     * print anything else with echo, only call this function
     */
    function __destruct() {
        echo json_encode(
            array(
                "success" => $this->success,
                "status" => $this->status,
                "msg" => $this->msg,
                "payload" => $this->payload,
                "debug" => array(
                    "log" => $this->debug_log,
                    "ms_time" => (round(microtime(true) * 1000) - $this->start_time)
                    )
                )
        );
    }
}

/**
 * Validate a list of GET/POST parameters and properly report missing ones,
 * and sanitize the existing ones.
 * @param array $required_params List of required parameters
 * @param array $request The request array ($_GET or $_POST)
 * @param bool $sanitize Whether to sanitize the parameters or not
 */
function validate_params($required_params, $request, $sanitize = true) {
    global $json;

    $missing_params = array();

    foreach($required_params as $param) {
        if(!isset($request[$param]) || $request[$param] == "") {
            array_push($missing_params, $param);
        } else {
            // Sanitize
            if ($sanitize)
                $request[$param] = make_sql_safe($request[$param]);
        }
    }

    if(count($missing_params) > 0) {
        $json->error("Missing parameters: ".implode(", ", $missing_params));
        die();
    }
    return;
}

?>