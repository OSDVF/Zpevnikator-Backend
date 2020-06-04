<?php
require_once '../shared/loadDB.php';
$mysqli = loadDB();
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    die;
}
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $res = mysqli_query($mysqli, "SELECT * FROM push_submissions");
    if (!$res) {
        http_response_code(500);
        echo '{"status":"fail","message":"Failed to connect to MySQL: ' . $mysqli->connect_error.'"}';
        die;
    }

    while ($row = $res->fetch_assoc()) {
       echo '<pre>' . var_export($row, true) . '</pre>';
    }
}
else if($_SERVER['REQUEST_METHOD']=='POST')
{
    $params = json_decode(file_get_contents("php://input"));
    /*if(!$params->keys->p256dh)//No payload support
        var_dump($params);
    http_response_code(500);
    die;*/
    $exists = false;
    if($query = $mysqli->prepare("SELECT ID FROM push_submissions WHERE endpoint=?"))
    {
        $query->bind_param('s',$params->endpoint);
        $query->bind_result($idcko);
        if ($query->execute()) {
            if($query->fetch())
            {
                echo '{"status":"success","message":"exists","id":'.$idcko.'}';
                $exists = true;
            }
        }
    }
    $query->close();
    if(!$exists)
    {
        if($query = $mysqli->prepare("INSERT INTO push_submissions VALUES (default,?,?,?)"))
        {
            $query->bind_param('sss', $params->endpoint, $params->keys->p256dh, $params->keys->auth);
            if ($query->execute()) {
                echo '{"status":"success","message":"new","id":'.$mysqli->insert_id.'}';
            }
            else
            {
                http_response_code(500);
                echo '{"status":"fail", "message":"Failed to run query: (' . $mysqli->errno . ') '.$mysqli->error.'"}';
                die;
            }
        }
        else
        {
            http_response_code(500);
            echo '{"status":"fail", "message":"Failed to run query: (' . $mysqli->errno . ') '.$mysqli->error.'"}';
            die;
        }
        $query->close();
    }
}