<?php
/**
 * @file
 * @brief
 * Include this when you want to work with wordpress API
 */
define('WP_USE_THEMES', false);
require_once($_SERVER['DOCUMENT_ROOT']."/domains/dorostmladez.cz/wp-load.php");//Change this to your Wordpress root folder

@include '../wordpress-stubs.php';