<?php
/**
 * @file
 * @brief
 * Responds to a login request and send all user-created data in the repsonse
 * @returns TogetherAPIResponse as JSON when POST request to that file is made
 */
error_reporting(E_ERROR | E_PARSE);
if ($_POST) {
    require_once("auth.php");
    $username = htmlspecialchars($_POST["username"]);
    $password = htmlspecialchars($_POST["password"]);
    $userData = authenticate($username, $password);
    /**
     * @var TogetherAPIResponse $resultObj
     */
    $resultObj = new stdClass();
    if ($userData !== false) {
        require_once("encrypt.php");
        $userCredenc = encryptCredentials($username, $password);
        /**
         * @var UserInfo $userInfo
         */
        $userInfo = new stdClass();
        
        $resultObj->status = "OK";

        $userInfo->credentials = $userCredenc;
        $userInfo->id = $userData->ID;
        $userInfo->name = $userData->user_nicename;
        $userInfo->avatar = get_avatar_url($userInfo->id);
        $userInfo->username = $username;

        $resultObj->userInfo = $userInfo;

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
