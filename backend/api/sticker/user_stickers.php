<?php
// API: Fetch all of the user's stickers

include_once('../../lib/tools.php');
$json = new JSON_Resp();

try {
    $user = LoggedUser::get();
    $stickers = $user->get_stickers();
    $json->add_field("stickers", $stickers);
} catch (Exception $e) {
    $json->error($e->getMessage());
}

?>