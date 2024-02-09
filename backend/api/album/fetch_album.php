<?php
// API: Fetch a specific album, given an ID as an argument

include_once('../../lib/tools.php');
$json = new JSON_Resp();

validate_params(array("album_id"), $_GET);
$album_id = $_GET["album_id"];

try {
    $album = new Album($album_id);
    $json->add_field("album", $album);

    // Remove author from all stickers
    foreach ($album->stickers as $sticker) {
        unset($sticker->author);
    }
} catch (Exception $e) {
    $json->error($e->getMessage());
}

?>