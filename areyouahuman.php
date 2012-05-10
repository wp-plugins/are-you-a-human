<?php
/**
 * @package Are You A Human
 * @version 1.1.0
 */
/*
Plugin Name: Are You A Human
Plugin URI:  http://wordpress.org/extend/plugins/are-you-a-human/
Description: Plugin Captcha intended to prove that the visitor is a human being and not a spam robot. Plugin asks the visitor to play a short game.
Author: Are You A Human
Author URI: http://www.areyouahuman.com/
Version: 1.1.0
*/

define('AYAH_VERSION', '1.0.0');

// Temporary for testing
define('AYAH_WEB_SERVICE_HOST', 'ws.areyouahuman.com');

require_once("ayah.php");
require_once("ayah_helpers.php");
require_once("ayah_pages.php");
require_once("ayah_functions.php");
require_once("ayah_forms.php");

// this sets our options to $_SESSION['ayah_options']
ayah_get_options();

// Add some styles
wp_register_style( 'myPluginStylesheet', plugins_url('ayah_styles.css', __FILE__) );

// Add our menu to the admin
add_action( 'admin_menu', 'ayah_add_admin_menu' );

// adds "Settings" link to the plugin action page
add_filter( 'plugin_action_links', 'ayah_plugin_action_links', 10, 2);

//Additional links on the plugin page
add_filter('plugin_row_meta', 'ayah_register_plugin_links', 10, 2);

// found in ayah_functions.php
// adds the playthru to the enabled forms
ayah_add_playthru();

function ayah_add_playthru() {
    
    $ayah_options = $_SESSION['ayah_options'];

    // for comments
    if( 1 == $ayah_options['enable_comment_form'] ) {
    	global $wp_version;
    	if( version_compare($wp_version,'3','>=') ) { // WP >3.0
    		add_action( 'comment_form_after_fields', 'ayah_comment_form');
    		add_action( 'comment_form_logged_in_after', 'ayah_comment_form');
    		add_filter( 'preprocess_comment', 'ayah_comment_post' );
    	} else { // for WP <3.0
    		add_action( 'comment_form', 'ayah_comment_form' );
    		add_filter( 'preprocess_comment', 'ayah_comment_post', 10, 3);	
    	}
    }
    
    if( 1 == $ayah_options['enable_register_form']) {
        add_action( 'register_form', 'ayah_register_form');
        add_action( 'register_post', 'ayah_register_post', 10, 3);
    }
    
    if( 1 == $ayah_options['enable_lost_password_form']) {
        add_action( 'lostpassword_form', 'ayah_lost_password_form');
        add_action( 'lostpassword_post', 'ayah_lost_password_post', 10, 3);
    }
}

function ayah_add_admin_menu() {
	add_options_page( "Are You a Human Options", "Are You a Human", 'manage_options',  __FILE__, 'ayah_run_controller' );

	//call register settings function when the admin settings page loads
	//add_action( 'admin_init', 'register_ayah_settings' );
}

function ayah_plugin_action_links( $links, $file ) {
		
	static $this_plugin; //Static so we don't call plugin_basename on every plugin row.
	
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if ( $file == $this_plugin ){
		 $settings_link = '<a href="options-general.php?page='.plugin_basename(__FILE__).'">' . __('Settings', 'captcha') . '</a>';
		 array_unshift( $links, $settings_link );
	}
	return $links;
}

function ayah_register_plugin_links($links, $file) {
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href="options-general.php?page=are-you-a-human/areyouahuman.php">' . __('Settings','captcha') . '</a>';
		$links[] = '<a href="http://support.areyouahuman.com" target="_blank">' . __('Support','captcha') . '</a>';
		$links[] = '<a href="http://www.areyouahuman.com/feedback">' . __('Feedback','captcha') . '</a>';
	}
	return $links;
}