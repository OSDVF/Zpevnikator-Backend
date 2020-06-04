<?php
/**
 * @file Handles redirection of user to Wordpress admin page for song editing
 */
require_once 'loadWordpress.php';

if($_GET['edit'])
{
    $id = get_post_id_by_slug($_GET['edit']);
    if($id!==NULL)
    {
        header("Location: https://dorostmladez.cz/wp-admin/post.php?post=$id&action=edit");
        http_response_code(303);//Temorary redirect
        die();
    }
    else
    {
        echo "Neznámá píseň. Pokud vás sem odkázala aplikace, je možné že jde o píseň, kteoru máte uloženou pouze offline.";
    }
}
function get_post_id_by_slug( $slug, $post_type = "song" ) {
    $query = new WP_Query(
        array(
            'name'   => $slug,
            'post_type'   => $post_type,
            'numberposts' => 1,
            'fields'      => 'ids',
        ) );
    $posts = $query->get_posts();
    return array_shift( $posts );
}