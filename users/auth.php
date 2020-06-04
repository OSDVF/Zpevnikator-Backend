<?php
/**
 * @file
 * @brief
 * Functionality for authenticating users by wordpress API
 */
/**
 * @returns WP_User object on succes, false on fail
 */
function authenticate($username, $password)
{
    require '../shared/loadWordpress.php';
    $user = get_user_by('login', $username);
    // COMPARE FORM PASSWORD WITH WORDPRESS PASSWORD
    if ($user !== false && wp_check_password($password, $user->data->user_pass, $user->ID)) {
        return $user;
    } else {
        return false;
    }
}
