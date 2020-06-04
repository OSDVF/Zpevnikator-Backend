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
        echo "Failed to run query: (" . $mysqli->errno . ") " . $mysqli->error;
        die;
    }

    while ($row = $res->fetch_assoc()) {
       echo '<pre>' . var_export($row, true) . '</pre>';
    }
}
else if($_SERVER['REQUEST_METHOD']=='DELETE')
{
    $params = json_decode(file_get_contents("php://input"));
    if($query = $mysqli->prepare("DELETE FROM push_submissions WHERE ID=? AND endpoint=? AND p256dh=? AND auth=?"))
    {
        $query->bind_param('isss', $params->id,$params->subs->endpoint, $params->subs->keys->p256dh, $params->subs->keys->auth);
        if ($query->execute()/*&&$mysqli->affected_rows>0*/) {
            echo '{"status":"success","message":"deleted","affected":'.$mysqli->affected_rows.'}';
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
