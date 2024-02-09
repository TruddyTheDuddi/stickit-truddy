<?php
// API: Fetch all of the user's stickers that were not sticked
// Can also provide album_id to fetch only the stickers of a specific album

include_once('../../lib/tools.php');
$json = new JSON_Resp();

try {
    // Get the User's stickers
    if(isset($_GET['album_id'])) {
        $stickers_rel = StickerCollected::get_by_user(LoggedUser::get()->id, $_GET['album_id']);
    } else {
        $stickers_rel = StickerCollected::get_by_user(LoggedUser::get()->id);
    }

    // Remove the user object in each relation from the response
    foreach ($stickers_rel as $rel) {
        unset($rel->user);
    }

    $json->add_field("stickers", $stickers_rel);
} catch (Exception $e) {
    $json->error($e->getMessage());
}

?>