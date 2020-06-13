<?php

/**
 * @file
 * @brief
 * Performs a redirection of user to various Wordpress admin pages
 * 
 * Navigate to this page with these GET parameters to do a redirection:
 * @param string edit: %Song url slug (Redirect to %Song edit page by its name in url)
 * @param int editProfile: User ID (Redirect to User Profile edit page by its id)
 */
require_once 'loadWordpress.php';

function profileEditRedirect()
{
    $url = get_edit_profile_url($_GET['id']);
    header("Location: $url");
    http_response_code(303); //Temorary redirect
    die;
}
function songEditRedirect()
{
    $id = get_post_id_by_slug($_GET['edit']);
    if ($id !== NULL) {
        header("Location: https://dorostmladez.cz/wp-admin/post.php?post=$id&action=edit");
        http_response_code(303); //Temorary redirect
        die();
    } else {
        echo "Neznámá píseň. Pokud vás sem odkázala aplikace, je možné že jde o píseň, kteoru máte uloženou pouze offline.";
    }
}
/**
 * Gets ID of a song from it's slug url
 */
function get_post_id_by_slug($slug, $post_type = "song")
{
    $query = new WP_Query(
        array(
            'name'   => $slug,
            'post_type'   => $post_type,
            'numberposts' => 1,
            'fields'      => 'ids',
        )
    );
    $posts = $query->get_posts();
    return array_shift($posts);
}

if ($_GET['edit']) {
    songEditRedirect();
}
if ($_GET['editProfile']) {
    profileEditRedirect();
}