<?php
// API: Add a page to an album (at the end)

include_once('../../lib/tools.php');
$json = new JSON_Resp();

validate_params(array("album_id"), $_GET);
$album_id = $_GET["album_id"];

try {
    $page = AlbumPage::create($album_id);
    $json->add_field("page", $page);
} catch (Exception $e) {
    $json->error($e->getMessage());
}

?>