<?php
/**
 * @file
 * @brief
 * Saves everything that comes to it
 */
$file = "data.txt";
if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $path = 'usrContent/'.date('Y').'/'.date('W');
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    $message = file_get_contents("php://input")."\n";
    file_put_contents($path.'/'.$file, $message, FILE_APPEND);
    echo "Success";
}
else
{
    echo "Fail";
}