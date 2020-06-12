<?
/**
 * @file
 * @brief
 * Simple Like button press counting system
 */
$path = '../usrContent';
$file = 'likes.txt';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    try
    {
        $count = file_get_contents($path.'/'.$file);
    }
    catch(Exception $e)
    {
        $count = 0;
    }   
    file_put_contents($path.'/'.$file, $count+1);
    echo "Thank you for liking the Dorostomládežový Zpěvníkátor!";
}
else
{
    echo file_get_contents($path.'/'.$file);
}