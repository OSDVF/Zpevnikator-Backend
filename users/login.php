<?php
/**
 * @file
 * @brief
 * Responds to a login request and send all user-created data in the repsonse
 */
error_reporting(E_ERROR | E_PARSE);
if ($_POST) {
    require_once("auth.php");
    $username = htmlspecialchars($_POST["username"]);
    $password = htmlspecialchars($_POST["password"]);
    $userData = authenticate($username, $password);
    $resultObj = new stdClass();
    if ($userData !== false) {
        require_once("encrypt.php");
        $userCredenc = encryptCredentials($username, $password);
        $resultObj->status = "OK";
        $resultObj->credentials = $userCredenc;
        $resultObj->id = $userData->ID;

        //Add Zpěvníkátor data
        require 'refresh.php';
        refreshUserData($resultObj,$userData->ID);
    } else {
        $resultObj->status = "FAIL";
    }
    echo json_encode($resultObj);
} else {
    echo "Bad method";
}
