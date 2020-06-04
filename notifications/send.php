<?php
/**
 * @file
 * @brief
 * API for sending push messages to subscribed clients
 * Depends on https://github.com/web-push-libs/web-push-php
 */
require_once($_SERVER["DOCUMENT_ROOT"].'/subdom/shared/vendor/autoload.php');
use Minishlink\WebPush\WebPush;
function PushJSONMessage($id,$message)
{
    $returnmessage = '';
    $vapid = array(
        'GCM' => 'AIzaSyBHTyAFJf1GEhfy7XYJN1wgswMqM3TUwME', // deprecated and optional, it's here only for compatibility reasons
        'VAPID' => array(
            'subject' => "https://$_SERVER[SERVER_NAME]",
            'publicKey' => 'BPeXT5bKp1GVzD8SZVr18DoLyf10V34YiUb2ZQCzq4z0zoXh-27rVrUBs5rwAPjarXWlJbmfx6eY6VgMSloDU10',
            'privateKey' => 'Cmf1nuBLf9z4rpW5huFVOxIAbHUiRWUOZ9ThooZlLHs', // in the real world, this would be in a secret file
        ),
    );
    $mysqli = new mysqli("wm113.wedos.net", "w126701_b3cfe5d", "VchSV3nG", "d126701_b3cfe5d");//S omezenymi pravy
    if ($mysqli->connect_errno) {
        http_response_code(500);
        $returnmessage .= "Failed to connect to MySQL: " . $mysqli->connect_error;
        die;
    }
    if($query = $mysqli->prepare("SELECT endpoint,p256dh,auth FROM push_submissions WHERE ID=?"))
    {
        $query->bind_param('i', $id);
        $query->bind_result($endpoint, $p256dh,$auth);
        if ($query->execute()) {
            while ($query->fetch()) {
                //printf ("%s (%s) [%s]\n", $endpoint, $p256dh,$auth);
                // THE SENDING
                //exit($subscriber['endpoint'].' : '.$subscriber['auth'].' : '.$subscriber['p256dh']);
                $webPush = new WebPush($vapid);
                $webPush->setAutomaticPadding(false);// disable automatic padding in tests to speed these up
                //this code was modified from the tutorial to make it more dynamic.
                //hardcoding the serviceworker push notification would not be a great practice in a real-world application
                $res = $webPush->sendNotification(
                    $endpoint,
                    //'{"title":"hello","msg":"yes it works","icon":"images/icon-192.png","badge":"images/icon-72.png","url":"https://$_SERVER[SERVER_NAME]"}',
                    json_encode($message),
                    $p256dh,
                    $auth,true
                );
                if($res !== true)
                {
                    $returnmessage .= ($res['message']);
                    //$returnmessage .= json_encode($res);
                    if($res['expired']==true)
                    {
                        if($query = $mysqli->prepare("DELETE FROM push_submissions WHERE ID=?"))
                        {
                            $query->bind_param('i',$params->$id);
                            if ($query->execute()) {
                                $returnmessage .= 'Subsciption expired ';
                            }
                        }
                        $query->close();
                    }
                }
                else
                    $returnmessage .= 'Successfully sent.';
            }
        }
        else
        {
            http_response_code(500);
            $returnmessage .= "Failed to run query: (" . $mysqli->errno . ") " . $mysqli->error;
            die;
        }
    }
    else
    {
        http_response_code(500);
        $returnmessage .= "Failed to run query: (" . $mysqli->errno . ") " . $mysqli->error;
        die;
    }
    return $returnmessage;
}
?>