<?php
/**
 * @file
 * Start a Convertio request
 * Depends on https://github.com/convertio/convertio-php
 */
if(!$_GET||!$_GET['id'])
{
    echo "Hey, you want to convert some song? Go to the <a href='seznam'>seznam</a> first!";
    die();//Yes, commit suicide. So soon?
}
$song_id = $_GET['id'];
$userID = $_GET['userID'];
$pushID = $_GET['pushID'];
require_once '../shared/convertio/0.4/autoload.php';
require_once '../shared/loadDB.php';

$mysqli = loadDB();
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    die;
}
  use \Convertio\Convertio;
  use \Convertio\Exceptions\APIException;
  use \Convertio\Exceptions\CURLException;
$baseUrl = $_SERVER['SERVER_NAME'];
  try {
      $API = new Convertio('8c6ba18f01b0e557be62d44c83fb8c57');               // You can obtain API Key here: https://convertio.co/api/
      $convID = $API->startFromURL("https://dorostmladez.cz/song/print/$song_id", 'doc', [ // Start HTML => DOC conversion
          "callback_url" => "https://$baseUrl/convert/callback.php?userID=$userID&pushID=$pushID"  // Defined publicly available callback URL
      ])->getConvertID();
      echo $convID;
      //file_put_contents("convertingIDs.txt", $pushID.'|'.$convID. "\n", FILE_APPEND);
  } catch (APIException $e) {
      echo "Chyba: API Exception: " . $e->getMessage() . " [Code: ".$e->getCode()."]" . "\n";
  } catch (CURLException $e) {
      echo "Chyba připojení: HTTP Connection Exception: " . $e->getMessage() . " [CURL Code: ".$e->getCode()."]" . "\n";
  } catch (Exception $e) {
      echo "Jiná zajímavá chyba: Miscellaneous Exception occurred: " . $e->getMessage() . "\n";
  }
?>