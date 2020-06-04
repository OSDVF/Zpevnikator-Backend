<?php
/**
 * @file
 * @brief
 * API for conversion between ChordPro and HTML
 */

require_once '../shared/simple_html_dom.php';

function HtmlToChordPro($html,$includeHeader)
{
    $output = $includeHeader===true?'[chordsandlyrics]':'';//For use with WP plugin
    $dom = str_get_html($html);
    foreach($dom->find('p') as $p)
    {
        $output.= '<p>';
        $children = $p->children();
        foreach($children as $child)
        {
            switch($child->tag)
            {
                case 'span':
                    $clas = $child->getAttribute('class');
                    if($child->innertext != '')
                    {
                        if(strpos($clas, 'lyric') !== false)
                        {
                            $output .= $child->innertext;
                        }
                        else if(strpos($clas, 'chord') !== false)
                        {
                            $output .= '['.$child->innertext.']';
                        }
                        else
                        {
                            $output .= $child->outertext;
                        }
                    }
                    break;
                default:
                    $output .= $child->outertext;
            }
        }
        $output.= '</p>';
    }
    if($includeHeader===true)
        $output.= '[/chordsandlyrics]';
    return $output;
}
function ChordProToHtml($chordpro)
{
    $output = '';
    //Určitě nefunguje, jenom zkopírované z getsong.php
    $chordpro = str_replace('</p>', "", $chordpro);//DElet these ugly things
    $paragraphs = explode("<p>",$chordpro);
    for($p=0;$p<count($paragraphs);$p++)
    {
        $output.= '<p>';
        $lines = explode("<br />",$paragraphs[$p]);
        for($l=0;$l<count($lines);$l++)
        {
            preg_match_all('/\[([A-Z<(\/][^\[\]]*)\]/',$lines[$l],$chordOutput,PREG_OFFSET_CAPTURE);
            //And now start rozplácing the string
            $lastPos = 0;
            $printedNull = false;
            if(count($chordOutput[0])==0)
            {
                $onlyLyric = $lines[$l];
                $output.= "<span class='lyric chordless'>$onlyLyric</span>";//Absolutely chordless line
            }
            else
            {
                $firstLyric = substr($lines[$l],0,$chordOutput[0][0][1]);
                $output.= "<span class='lyric'>$firstLyric</span>";

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
                    $lastPos = $chordEndPos +strlen($line);
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
                    $output.= "<span class='chord' data-original='$chord'>$chord</span>";
                    if(!$printedNull)
                        $output.= "<span class='lyric'>$lyricAfter</span>";
                    else
                        $output.= "<span class='lyric chordless'>$lyricAfter</span>";
                }
            }
            $output.= "<br />";
        }
        $output.= '</p>';
    }//The worst code I've ever done
    $output.= '<div class="invisible" id="songTitle">'.$title.'</div>';
    return $output;
}

//Respond to potential user request for parsing
/*$id = $_GET['id'];
if($id)
{
    include "getnew.php";
    echo "\"$id\" Parser Preview:";
    $chordpro = GetSong($id);
    $lines = explode(PHP_EOL,$chordpro);
    array_shift($lines);
    array_shift($lines);//Delte first 2 lines (heading and space)
    $chordpro = implode($lines);
    echo ChordProToHtml($chordpro);
    echo HtmlToChordPro(ChordProToHtml($chordpro));
}*/
$content = $_GET['content'];
if($content)
{
    $ch = HtmlToChordPro($content,true);
    echo $ch;
    echo "<H1>AND HTML:</H1><br>";
    echo ChordProToHtml($ch,true);
}