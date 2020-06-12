<?php

/**
 * @file
 * @brief
 * Functionality for getting Zpěvníkátor Together data about one particular user's songs and groups  
 * 
 * Do a GET request to that file for retrieving all of the user's data. Returns a User object as JSON
 */
/**
 * @param[in,out] User $resultObj: An User object to bind data to
 */
function refreshUserData(&$resultObj, $id)
{
    require '../groups/list.php';
    require '../notes/list.php';
    require '../playlists/list.php';
    require '../songs/list.php';
    $resultObj->songs = ListSongs(false, $id);
    $resultObj->groups = ListGroups($id);
    $resultObj->playlists = ListPlaylists($id);
    $resultObj->notes = ListNotes($id);
}

if (pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME) == pathinfo(__FILE__, PATHINFO_FILENAME)) //If this is the file which was directly requested
{
    require_once 'encrypt.php';

    if (!($_POST && isset($_POST['id']) && isset($_POST['credentials'])))
        return;
    $resultObj = new stdClass();
    $resultObj->status = 'FAIL';

    if (decryptCredentials($_POST['credentials']) != -1) {
        //If authentication is valid
        refreshUserData($resultObj, $_GET['id']);
        $resultObj->status = 'OK';
    }

    echo json_encode($resultObj, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
