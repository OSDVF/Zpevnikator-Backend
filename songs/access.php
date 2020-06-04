<?php
/**
 * @file
 * @brief
 * API for accessing one particular song's content
 * 
 * @note
 * Does not require authentification
 */
require_once '../shared/loadWordpress.php';

function GetSong($bywhat,$criteria)
{
    if($bywhat == 'ID')
        $content_post = array(get_post($criteria));
    else
        $content_post = get_posts( array( $bywhat => $criteria, 'post_type'   => 'song' ,'post_status' => array('publish', 'private') ));
    if( count($content_post) )
    {
        $title = apply_filters( 'the_title', $content_post[0]->post_title, $content_post[0]->ID );
        $modified = get_the_modified_date('Y-m-d H.i.s',$content_post[0]);
        $text = html_entity_decode(strip_tags($content_post[0]->post_content,'<p><small><i><br><ol><li>'));
        $start = strpos($text,'[chordsandlyrics]')+17;
        //Eliminating white characters
        if(substr($text,$start,4)=='<br>') $start+=4;
        else if(substr($text,$start,6)=='<br />') $start+=6;
        else if(substr($text,$start,5)=='<br/>') $start+=5;
        else if(substr($text,$start,4)=='</p>') $start+=4;

        $length = strpos($text,'[/chordsandlyrics]') - $start;
        $content = substr($text,$start,$length);
        return "$modified\n$content";
        // do whatever you want
        return $content;
    }
    else
        return false;
}
