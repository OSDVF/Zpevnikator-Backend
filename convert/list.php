<?php
/**
 * @file
 * Lists all versions of conversions of a particular song
 * Publicly visible
 */
if($_GET&&$_GET['id'])
{
    require_once '../shared/loadWordpress.php';

    $users = [];
    $resultArray = [];
    $songID = $_GET['id'];
    $folderName = "../usrContent/convertedSongs/$songID/";
    if (file_exists($folderName))
    {
        $dir = new DirectoryIterator($folderName);
        $i = 0;
        foreach ($dir as $fileinfo)
        {
            $i++;
            if($i>50)
                break;//Don't output so large lists
            if (!$fileinfo->isDot())
            {
                $result = new stdClass();
                $result->chords = null;
                $result->user = null;
                $filename = $fileinfo->getFilename();
                $parts = explode(' ',
                                 substr($filename,
                                        0,strlen($filename)-4)
                                );//Remove .xxx extension and separate date and other part
                $date = DateTime::createFromFormat('Y-m-d H.i.s', $parts[count($parts)-2].' '.$parts[count($parts)-1]);
                if(count($parts >3))//There is userID part
                {
                    $userID = $parts[count($parts)-3];
                    if(!array_key_exists($userID,$users))
                        $users[$userID] = get_user_by('id', $userID)->display_name;
                    $result->user = $users[$userID];
                }
                $result->date = '<span class="font-weight-bold">'.$date->format('d.m.Y').'</span> ' .$date->format('H:i:s');
                $result->href= "$songID/$filename";
                array_push($resultArray,$result);
            }
        }
    }
    echo json_encode($resultArray,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}