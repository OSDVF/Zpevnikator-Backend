<?php
/**
 * @file
 * @brief
 * Respond to user query for a song
 */
if (!function_exists('getallheaders')) {
    function getallheaders() {
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
    }
}
if(!$_GET||$_GET['id']==""||empty($_GET))
{
    echo "Hey, you want to display some song? Go to the <a href='seznam'>seznam</a> first!";
    die();//Yes, commit suicide. So soon?
}
require_once "access.php";

//Thyz yz song displaying page, so get the id of song we're displaying
$song_id = $_GET['id'];
if($_GET['raw'])
{
    echo GetSong('name',$song_id);
}
if(substr($song_id,0,6)=='_draft')
{
    $song_id = substr($song_id,6,strlen($song_id)-6);
    $chordpro = GetSong('ID',$song_id);
}
else
    $chordpro = GetSong('name',$song_id);
$dospaces = ($_GET['format'] &&$_GET['format'] =='true');

if(substr($song_id,0,8)=='offline:')
{
    http_response_code(420);//To differentiate between 404 not found this page
    echo "This song ought to be avialbe offline on your device :(";
    die();
}
if($chordpro === false)//Non-existent song ID
{
    http_response_code(410);//To differentiate between 404 not found this page
    die();
}
$lines = explode(PHP_EOL,$chordpro);
$modified = DateTime::createFromFormat('Y-m-d H.i.s',$lines[0]);
$modified->sub(new DateInterval('PT1H'));//Udělá GMT+1 -> GMT
$LastModifiedHeader = $modified->format ('D, d M Y H:i:s');
if($_COOKIE['badBrowser'] != 'true')
    header("Last-Modified: $LastModifiedHeader GMT");
if($_SERVER['HTTP_IF_MODIFIED_SINCE'])
{
    $clientModified = DateTime::createFromFormat('D, d M Y H:i:s',$_SERVER['HTTP_IF_MODIFIED_SINCE']);
    if($clientModified==$modified)
    {
        http_response_code(304);//Not modified
        die(304);
    }
}
if($_GET['dump']&&$_GET['dump']==='true')
	var_dump(getallheaders());
array_shift($lines);//Delte first 2 lines (heading and space)
$chordpro = implode($lines);
/*foreach($html->find('.post') as $post)
{
    $title = $post->children(0)->children(0)->innertext;
    //$chordsNlyrics = $post->children(1)->find('.cnl_page',0);
    $text = $post->children(1);
   // $deletthis = $notes->find('.cnl-page',0);   //Some drafts
    //$deletthis->outertext='';
    //$html->save();
}
$chordOutput[0][$i][0] = akord včetně []
$chordOutput[0][$i][1] = index akordu včetně []
$chordOutput[1][$i][0] = akord
$chordOutput[1][$i][1] = index
*/
$chordpro = str_replace('</p>', "", $chordpro);//DElet these ugly things
$paragraphs = explode("<p>",$chordpro);
for($p=0;$p<count($paragraphs);$p++)
{
    $lines = explode("<br />",$paragraphs[$p]);
    if(trim( $lines[0])==''&&count($lines)==1)
    {
        continue;//Do not output empty
    }
    echo '<p>';

    for($l=0;$l<count($lines);$l++)
    {
         preg_match_all('/\[([A-Z<(\/][^\[\]]*)\]/',$lines[$l],$chordOutput,PREG_OFFSET_CAPTURE);
        //And now start rozplácing the string
        $lastPos = 0;
        $printedNull = false;
        if(count($chordOutput[0])==0)
        {
            $onlyLyric = $lines[$l];
            echo "<span class='lyric chordless'>$onlyLyric<br></span>";//Absolutely chordless line
        }
        else
        {
            $firstLyric = substr($lines[$l],0,$chordOutput[0][0][1]);
            echo "<span class='lyric'>$firstLyric</span>";

            for($i=0;$i< count($chordOutput[0]);$i++)
            {
                $printedNull = false;
                $chord = $chordOutput[1][$i][0];
                $chordEndPos = $chordOutput[0][$i][1]
                    + strlen($chordOutput[0][$i][0]);
                $nextChordPos = $chordOutput[0][$i+1][1];
                $nextChordEnd = $chordOutput[0][$i+1][1]
                    + strlen($chordOutput[0][$i+1][0]);
                $thisChordLength = strlen($chordOutput[1][$i][0]);
                $nextChordLength = strlen($chordOutput[1][$i+1][0]);
                $adjacentChordsLength = $thisChordLength+$nextChordLength;
                $lyricAfter = substr($lines[$l],$chordEndPos, $nextChordPos - $chordEndPos);
                $lastPos = $chordEndPos;
                if($nextChordPos === NULL)
                {
                    $lyricAfter = substr($lines[$l],$lastPos);
                    $printedNull = true;
                }
                $lyricLength = strlen($lyricAfter);
                if($dospaces&&$adjacentChordsLength>=$lyricLength&&strpos($lyricAfter,'<br') === false&&$lyricLength>0&&$thisChordLength>=$nextChordLength)
                {
                    if(preg_match('/[A-Za-z;&]/u',$lyricAfter)==1)//If next few lettes are real text, split it by '-', instead only by spaces
                    {
                        $lyricAfter .= '<i class="space" style="--length:'.$adjacentChordsLength/2 .'ex">- </i>';
                    }
                    else
                    {
                        $lyricAfter .= ' <i class="space" style="--length:'.$adjacentChordsLength.'ex"></i>';
                    }
                }
                echo "<span class='chord'>$chord</span>";

                if($i==count($chordOutput[0])-1&&$l!=count($lines)-1)
                    $lyricAfter.='<br />';
                if(!$printedNull)
                    echo "<span class='lyric'>$lyricAfter</span>";
                else
                    echo "<span class='lyric chordless'>$lyricAfter</span>";
            }
        }
    }
    echo '</p>';
}//The worst code I've ever written