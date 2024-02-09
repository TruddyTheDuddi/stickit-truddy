<?php
// API: Reorder album pages by giving a list of the page IDs

include_once('../../lib/tools.php');
$json = new JSON_Resp();

validate_params(array("album_id", "page_order"), $_GET);

try {
    // From JSON to array
    $album_id = $_GET["album_id"];
    $page_order = json_decode($_GET["page_order"]);
    $json->add_field("page_order", $page_order);

    // Get the album
    $album = new Album($album_id);

    // Reorder the pages
    $album->reorder_pages($page_order);
    $json->message("Pages reordered successfully");
} catch (Exception $e) {
    $json->error($e->getMessage());
}
?>