<?php
/**
Plugin Name: Pixelart Verifier
Plugin URI: http://pixelartdev.com
Description: Registration form with envato market purchase verification, and also saves the user information for later use
Version: 1.1.0
Author: Deniz Celebi (Pixelart)
Author URI: http://pixelartdev.com
**/


/** Prevent direct access **/
if ( !defined( 'ABSPATH' ) ) exit;

$plugin_path;
$dir = px_verify_dir();

@include_once "$dir/login.php";
@include_once "$dir/admin.php";
@include_once "$dir/bp-custom.php";
@include_once "$dir/users.php";

// Addons
$files = scandir("$dir/addons");
foreach($files as $file) {
	if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'php') {
		@include_once "$dir/addons/$file";
	}
}



function px_verify_dir() {
  if (defined('PX_VERIFY_DIR') && file_exists(PX_VERIFY_DIR)) {
    return PX_VERIFY_DIR;
  }else {
    return dirname(__FILE__);
  }
}

function px_verify_info() {
	global $dir;
	$plugin_path = plugin_dir_path($dir);
}

// define here your item names, roles, and role names
function px_verify_globals($item) {
	if($item == "1") {
		$pxverifyitem = array (
			'item1' => 'WordPress Blog Android App',
			'item2' => 'WP Google Cloud Messaging',
		);
		return $pxverifyitem;

	}else if($item == "2") {
		$pxverifyrole = array (
			'px_wpba_customer' => 'WPBA Customer',
			'px_wpgcm_customer' => 'WP GCM Customer',
		);
		return $pxverifyrole;
	}
}


add_action('parse_query', 'px_verify_globals');

?>
