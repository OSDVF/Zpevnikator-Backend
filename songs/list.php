<?php

/**
 * @file
 * @brief Contains code for listing publicly-available songs
 */
require_once "song.php";
require_once "../shared/loadWordpress.php";

function ListSongs($formatted, $authorID)
{
	$songurls = array();
	if (isset($authorID)) {
		$usr = get_user_by('id', $authorID);

		$privateQuery = new WP_Query(array(
			'post_type' => 'song',
			'post_status' => array('publish', 'pending', 'draft', 'future', 'private'),
			'author' => $authorID,
			'nopaging' => true,
			'posts_per_page' => -1
		));
		while ($privateQuery->have_posts()) {
			$privateQuery->the_post();
			$p = get_post();
			//$link =  get_the_permalink();
			$title = $p->post_title;

			/*preg_match('#//dorostmladez.cz/song/(.*)/#', $link, $songurlpair);
                $song_id = $songurlpair[1];*/
			$nextSong = new Song();
			$song_id = $p->post_name;
			$postId = $p->ID;
			if (empty($song_id))
				$song_id = "_draft$postId";
			if (!$formatted)
				$nextSong->url = "$song_id&nospace=true";
			else
				$nextSong->url = $song_id;
			$nextSong->name = $title;
			$terms = get_the_terms($p, "songauthor");
			$nextSong->author = $terms[0]->name;
			for ($i = 1; is_array($terms) && $i < count($terms); $i++) {
				$nextSong->author .= ', ' . $terms[$i]->name;
			}
			$terms = get_the_terms($p, "songlanguage");
			$nextSong->language = $terms[0]->name;
			for ($i = 1; is_array($terms) && $i < count($terms); $i++) {
				$nextSong->language .= ', ' . $terms[$i]->name;
			}
			$vidLink = get_post_meta(get_the_ID(), 'video_link');
			if (sizeof($vidLink) > 0)
				$nextSong->video = $vidLink[0];
			$nextSong->status = $p->post_status;
			array_push($songurls, $nextSong);
		}
	}
	$wpsb_query = new WP_Query(array(
		'post_type' => 'song',
		'nopaging' => true,
		'author' => -$authorID,
		'posts_per_page' => -1
	));

	while ($wpsb_query->have_posts()) {
		$wpsb_query->the_post();
		$p = get_post();
		//$link =  get_the_permalink();
		$title = $p->post_title;

		/*preg_match('#//dorostmladez.cz/song/(.*)/#', $link, $songurlpair);
                $song_id = $songurlpair[1];*/
		$nextSong = new Song();
		$song_id = $p->post_name;
		$nextSong = new Song();
		if (!$formatted)
			$nextSong->url = "$song_id&nospace=true";
		else
			$nextSong->url = $song_id;
		$nextSong->name = $title;
		$terms = get_the_terms($p, "songauthor");
		$nextSong->author = $terms[0]->name;
		for ($i = 1; is_array($terms) && $i < count($terms); $i++) {
			$nextSong->author .= ', ' . $terms[$i]->name;
		}
		$terms = get_the_terms($p, "songlanguage");
		$nextSong->language = $terms[0]->name;
		for ($i = 1; is_array($terms) && $i < count($terms); $i++) {
			$nextSong->language .= ', ' . $terms[$i]->name;
		}
		$vidLink = get_post_meta(get_the_ID(), 'video_link');
		if (sizeof($vidLink) > 0)
			$nextSong->video = $vidLink[0];
		array_push($songurls, $nextSong);
	}
	//header('Content-type: application/json');
	return $songurls;
}
function ListAsJSON($formatted)
{
	return json_encode(ListSongs($formatted, null, null), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
//Do this as a default action when displaying this page
if (pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME) == pathinfo(__FILE__, PATHINFO_FILENAME)) //If this is the file which was directly requested
	echo ListAsJSON($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['formatted']) && $_GET['formatted'] == true);
