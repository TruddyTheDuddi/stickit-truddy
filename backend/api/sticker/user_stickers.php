<?php
// API: Fetch all of the user's stickers that were not sticked

include_once('../../lib/tools.php');
$json = new JSON_Resp();

try {
    // Get the User's stickers
    $stickers_rel = StickerCollected::get_by_user(LoggedUser::get()->id);

    // Remove the user object in each relation from the response
    foreach ($stickers_rel as $rel) {
        unset($rel->user);
    }

    $json->add_field("stickers", $stickers_rel);
} catch (Exception $e) {
    $json->error($e->getMessage());
}

?>