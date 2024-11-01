<?php

/*
Plugin Name: Splendid Product Viewer
Plugin URI: http://www.splendid-wordpress-plugins.com
Description: Splendid Product Viewer presents your products in a userfriendly way and is very easy to maintain.
Version: 3.1.4
Author: Alberto D'Angelo
Author URI: http://www.splendid-wordpress-plugins.com
Text Domain: splendid-product-viewer 
License: GPLv2 or later
*/


include_once 'class_pv_backend.php';
include_once 'class_pv_frontend.php';


$pv_backend 	= new pv_backend( plugins_url() . '/splendid-product-viewer/', wp_upload_dir());
$pv_frontend 	= new pv_frontend();


// ON PLUGIN UNINSTALL

// clear all - also pv image folder when uninstall
register_uninstall_hook(__FILE__,  'myplugin_deactivate');

function myplugin_deactivate() {


	// clear all custom taxonomies
	global $wpdb;
		
	$wpdb->query( 
	$wpdb->prepare( 
		"
			DELETE FROM $wpdb->term_taxonomy
			WHERE taxonomy = %s
		",
			'pv_type' 
		)
	);

     	
	
	// clear all custom posts
	$args = array (
		'post_type' => 'pv',
		'nopaging' => true
	);
	$query = new WP_Query ($args);
	
	while ($query->have_posts ()) {
		$query->the_post ();
		$id = get_the_ID ();
		wp_delete_post ($id, true);
	}
	wp_reset_postdata ();

	

	
	// delete pv image folder
	$upload_dir = wp_upload_dir(); 
	$pv_img_path = $upload_dir['basedir'] . '/splendid-product-viewer';

	$folder_handler = dir($pv_img_path);
	while ($file = $folder_handler->read()) {
	if ($file == "." || $file == "..")
		continue;
	unlink($pv_img_path.'/'.$file);

	}
	$folder_handler->close();
	rmdir($pv_img_path);
	

}