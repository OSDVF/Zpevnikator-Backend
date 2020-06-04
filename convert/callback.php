<?php
/**
 * @file
 * @brief Called by Convertio on conversion status update
 */
  require_once '../shared/convertio/0.4/autoload.php';
  require_once '../notifications/send.php';

  use \Convertio\Convertio;
  use \Convertio\Exceptions\APIException;
  use \Convertio\Exceptions\CURLException;
date_default_timezone_set ( 'Europe/Prague' );
$dateString = (new \DateTime())->format('Y-m-d H.i.s');
$log = '';
  try {
      $API = new Convertio('8c6ba18f01b0e557be62d44c83fb8c57');        // You can obtain API Key here: https://convertio.co/api/
      $conv_id = $_GET['id'];
      $pushID = $_GET['pushID'];
      $userID = isset($_GET['userID'] ) ? $_GET['userID'] : 'anonymous';
      $API->__set('convert_id', $conv_id);        // Set Conversion ID

      $apiObject = $API->status();
      $name = $apiObject->result_public_url;
      $log .=  $name ."\n";

      preg_match("#https:\/\/(?:.*)\.convertio\.me\/p\/(?:.*)\/(.*?)(?:_[1234567890]*)?\.doc#",$name,$matches);
      $name = $matches[1];
       //$log .= $matches[0]."\n".$matches[1];
      $folderName = "../usrContent/convertedSongs/$name/";
      if($userID != 'anonymous')
          $fileName = $folderName . "$name $userID $dateString.doc";
      else
          $fileName = $folderName . "$name $dateString.doc";
      if (!file_exists($folderName)) {
          mkdir($folderName, 0755, true);
      }

      if ($_GET['step'] == 'finished') {             // If conversion finished
          $API->download($fileName)          // Download result into local file
              ->delete();                            // Delete it from conversion server
          $mess = array('type' => 'conversion_completed', 'url' => 'https://'.$_SERVER['SERVER_NAME'].'/api/convert/'.$fileName);
          $sent = false;
          if(!isset($pushID))
          {
              echo "Push ID not present in query";
              $log .= "Push ID not present in query";
          }
          else
          {
              $sent = true;
              $sndStatus = PushJSONMessage($pushID,$mess);
              $log .= "Sent conversionID $conv_id to client $userID with pushID $pushID";
              echo "Sent conversionID $conv_id to client $userID with pushID $pushID";

              $log .= "\n".$sndStatus;
              echo $sndStatus;
          }
          if(!$sent)
          {
              echo "Could not found any client with pushID \"$ids[0]\"";
              $log .= "Could not found any client with pushID \"$ids[0]\"";
          }
      } else {                                       // Otherwise handle error in appropriate way
          $log .= "Conversion failed. Step is: $_GET[step]" . "\n";
          echo "Conversion failed. Step is: $_GET[step]";
      }
  } catch (APIException $e) {
      $log .= "API Exception: " . $e->getMessage() . " [Code: ".$e->getCode()."]" . "\n";
  } catch (CURLException $e) {
      $log .= "HTTP Connection Exception: " . $e->getMessage() . " [CURL Code: ".$e->getCode()."]" . "\n";
  } catch (Exception $e) {
      $log .= "Miscellaneous Exception occurred: " . $e->getMessage() . "\n";
  }
file_put_contents('../usrContent/conversionLog.txt',$log);
?>