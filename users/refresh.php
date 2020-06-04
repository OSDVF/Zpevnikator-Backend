<?php
/**
 * @file
 * Functionality for getting Zpěvníkátor Together data about one particular user's songs and groups
 */
function refreshUserData(&$resultObj,$id)
{
    require '../groups/list.php';
    require '../notes/list.php';
    require '../playlists/list.php';
    require '../songs/list.php';
    $resultObj->songs = ListSongs(false,$id);
    $resultObj->groups = ListGroups($id);
    $resultObj->playlists = ListPlaylists($id);
    $resultObj->notes = ListNotes($id);
}

if (pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME) == pathinfo(__FILE__, PATHINFO_FILENAME)) //If this is the file which was directly requested
{
    if (!($_GET && isset($_GET['id'])))
        return;

    $resultObj = new stdClass();
    refreshUserData($resultObj,$_GET['id']);
    echo json_encode($resultObj, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
