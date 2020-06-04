<?php
/**
 * @file
 * @brief
 * Polls status of a particular conversion
 */
if(!$_GET||!$_GET['id'])
{
    echo "Hey, you want to convert some song? Go to the <a href='seznam'>seznam</a> first!";
    die();//Yes, commit suicide. So soon?
}
$ConvertID = $_GET['id'];

$baseUrl = $_SERVER['SERVER_NAME'];
require_once 'access.php';
use \Convertio\Exceptions\APIException;
use \Convertio\Exceptions\CURLException;

try {
    $API = getConvertioAPIObj();
    $API->__set('convert_id', $ConvertID);
    $API->status();
    switch($API->step)
    {
        case 'finish':
            http_response_code(201);
            $name = $API->result_public_url;
            preg_match("#https:\/\/(?:.*)\.convertio\.me\/p\/(?:.*)\/(.*?)(?:_[1234567890]*)?\.doc#",$name,$matches);
            $name = $matches[1];

            $dir = new DirectoryIterator("../usrContent/convertedSongs/$name/");
            $latestDate = (new DateTime())->setTimestamp(0);
            $latestFileName;
            $parts;
            foreach ($dir as $fileinfo)
            {
                if (!$fileinfo->isDot())
                {
                    $parts = explode(' ',
                                     substr($fileinfo->getFilename(),
                                            0,strlen($fileinfo->getFilename())-4)
                                    );//Remove .xxx extension and separate date and other part
                    $date = DateTime::createFromFormat('Y-m-d H.i.s', $parts[count($parts)-2].' '.$parts[count($parts)-1]);
                    if($date>$latestDate)
                    {
                        $latestFileName = $fileinfo->getFilename();
                        $latestDate = $date;
                    }
                }
            }
            echo 'https://'.$_SERVER['SERVER_NAME'].'/api/convert/'."../usrContent/convertedSongs/$name/$latestFileName";
            break;
        case 'convert':
            echo "Probíhá převod...";
            break;
        default:
            echo $API->step;
            echo $API->step_percent;
            break;
    }
} catch (APIException $e) {
    http_response_code(500);
    echo "Chyba: API Exception: " . $e->getMessage() . " [Code: ".$e->getCode()."]" . "\n";
} catch (CURLException $e) {
    http_response_code(500);
    echo "Chyba připojení: HTTP Connection Exception: " . $e->getMessage() . " [CURL Code: ".$e->getCode()."]" . "\n";
} catch (Exception $e) {
    http_response_code(500);
    echo "Jiná zajímavá chyba: Miscellaneous Exception occurred: " . $e->getMessage() . "\n";
}
