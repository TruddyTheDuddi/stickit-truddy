<?php
// API: Fetch all of the user's albums, reduced data

include_once('../../lib/tools.php');
$json = new JSON_Resp();

try {
    $user = LoggedUser::get();
    $albums = $user->get_albums();

    // Reduce the data by removing pages and stickers
    foreach ($albums as $album) {
        unset($album->pages);
        unset($album->stickers);
    }

    $json->add_field("albums", $albums);
} catch (Exception $e) {
    $json->error($e->getMessage());
}

?>