<?php
// API: Fetch all of the user's stickers

include_once('../../lib/tools.php');
$json = new JSON_Resp();

try {
    $stickers_rel = StickerCollected::get_by_user(LoggedUser::get()->id, false);
    $json->add_field("stickers", $stickers_rel);
} catch (Exception $e) {
    $json->error($e->getMessage());
}

?>