<?php
// API: Stick a sticker to an album

include_once('../../lib/tools.php');
$json = new JSON_Resp();

validate_params(array("rel_id"), $_GET);

try {
    $rel_id = $_GET["rel_id"];

    // Get the sticker relation
    $sticker_rel = new StickerCollected($rel_id);

    // Stick the sticker to the album
    $sticker_rel->stick();

    $json->add_field("sticker_rel", $sticker_rel);
} catch (Exception $e) {
    $json->error($e->getMessage());
}

?>