<?php
/**
 * @file
 * @brief
 * Functionality for generating Zpěvníkátor-wide user authentication hashes and their checking
 */

function encryptCredentials($username, $password)
{
    $obj = new stdClass();
    $obj->username = $username;
    $obj->password = $password;

    $json = json_encode($obj);


    $publicKey = file_get_contents('../shared/public_key.pem', TRUE);
    $res = openssl_get_publickey($publicKey);

    $boo = openssl_public_encrypt($json, $encrypted, $res);

    return base64_encode($encrypted);
}

function decryptCredentials($encryptedText)
{
    $encryptedText = base64_decode($encryptedText);

    $privateKey = file_get_contents('../shared/private_key.pem', TRUE);
    $res = openssl_get_privatekey($privateKey);

    $boo = openssl_private_decrypt($encryptedText, $decrypted, $res);
    //echo "Boo:".$boo;
    if ($boo == 1) {
        //echo $decrypted;
        return json_decode($decrypted);
    } else {
        return -1;
    }
}