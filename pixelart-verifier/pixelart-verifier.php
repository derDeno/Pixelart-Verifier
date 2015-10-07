<?php
/**
Plugin Name: Pixelart Verifier
Plugin URI: http://pixelartdev.com
Description: Registration form with envato market purchase verification, and also saves the user information for later use
Version: 1.0.1
Author: Deniz Celebi (Pixelart)
Author URI: http://pixelartdev.com
**/


/** Prevent direct access **/
if ( !defined( 'ABSPATH' ) ) exit;

$plugin_path;
$file = px_verify_dir();

@include_once "$file/login.php";
@include_once "$file/admin.php";
@include_once "$file/bp-custom.php";
@include_once "$file/users.php";


function px_verify_dir() {
  if (defined('PX_VERIFY_DIR') && file_exists(PX_VERIFY_DIR)) {
    return PX_VERIFY_DIR;
  }else {
    return dirname(__FILE__);
  }
}

function px_verify_info() {
	global $file;
	$plugin_path = plugin_dir_path($file);
}

add_action('init', 'px_verify_info');
?>