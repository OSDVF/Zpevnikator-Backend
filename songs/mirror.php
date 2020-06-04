<?php
/**
 * @file
 * @brief
 * Mirrors content of a song by its URL in this domain to another (our) domain :)
 */
if(!$_GET||!$_GET['id'])
{
    echo "Hey, you want to print some song? Go to the <a href='seznam'>seznam</a> first!";
    die();//Yes, commit suicide. So soon?
}
$song_id = $_GET['id'];
echo file_get_contents('https://dorostmladez.cz/song/'.$song_id.'/?print=print');//Mirror from ugly url to less ugly :)
?>