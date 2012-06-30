<?php
/**
 * @package Are You A Human
 * @version 1.1.1
 */
/*
Plugin Name: Are You A Human
Plugin URI:  http://wordpress.org/extend/plugins/are-you-a-human/
Description: The Are You a Human PlayThru plugin replaces obnoxious CAPTCHAs with fun, simple games.  Fight spam with fun
Author: Are You A Human
Author URI: http://www.areyouahuman.com/
Version: 1.1.1
*/

define('AYAH_VERSION', '1.0.1');
define('AYAH_WEB_SERVICE_HOST', 'ws.areyouahuman.com');
define('PLUGIN_BASENAME', plugin_basename(__FILE__));
define('PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));

require_once(PLUGIN_DIR_PATH . "includes/ayah.php");
require_once(PLUGIN_DIR_PATH . "includes/ayah_form_actions.php");
require_once(PLUGIN_DIR_PATH . "includes/ayah_functions.php");
require_once(PLUGIN_DIR_PATH . "includes/ayah_pages.php");

// Set our options to $_SESSION['ayah_options']
ayah_get_options();

// Register a style sheet that can be loaded later with wp_enqueue_style
wp_register_style( 'myPluginStylesheet', plugins_url('css/ayah_styles.css', __FILE__) );

// Adds a AYAH Options page link to the Settings admin menu
add_action( 'admin_menu', 'ayah_add_admin_menu' );

// Registers the custom plugin action links
add_filter( 'plugin_action_links_' . PLUGIN_BASENAME, 'ayah_register_plugin_action_links', 10, 1);

// Registers custom plugin meta links
add_filter('plugin_row_meta', 'ayah_register_plugin_meta_links', 10, 2);

ayah_add_playthru();

/**
 * Adds the playthru to the forms chosen in the options menu
 * This is achieved by attaching to the appropriate hooks
 * 
 * @link http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
 * @link http://codex.wordpress.org/Function_Reference/add_action
 */
function ayah_add_playthru() {
    
    $ayah_options = $_SESSION['ayah_options'];

    // If enable_comment_form is set in the options, attach to the comment hooks
    if( 1 == $ayah_options['enable_comment_form'] ) {
		add_action('comment_form_after_fields', 'ayah_comment_form_after');
		add_action('comment_form_logged_in_after', 'ayah_comment_form_after');
		add_action('comment_form', 'ayah_comment_form');
		add_filter('preprocess_comment', 'ayah_comment_post', 10, 1);
    }
    
	// If enable_register_form is set in the options, attach to the register hooks
    if( 1 == $ayah_options['enable_register_form']) {
        add_action('register_form', 'ayah_register_form');
        add_action('register_post', 'ayah_register_post', 10, 3);
    }

    // If enable_lost_password_form is set in the options, attach to the lost password hooks	
    if( 1 == $ayah_options['enable_lost_password_form']) {
        add_action('lostpassword_form', 'ayah_lost_password_form');
        add_action('lostpassword_post', 'ayah_lost_password_post');
    }
}

/**
 * Adds a AYAH Options page link to the Settings admin menu
 * 
 * @link http://codex.wordpress.org/Function_Reference/add_options_page
 */
function ayah_add_admin_menu() {
	add_options_page( "Are You a Human Options", "Are You a Human", 'manage_options',  __FILE__, 'ayah_run_controller' );
}

/**
 * Registers the settings action link
 * 
 * @link http://thematosoup.com/development/add-action-meta-links-wordpress-plugins/
 */
function ayah_register_plugin_action_links($links) {

	$settings_link = '<a href="options-general.php?page=' . PLUGIN_BASENAME . '">' . __('Settings', 'captcha') . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}

/**
 * Registers the settings, support, and feedback meta links
 * 
 * @link http://thematosoup.com/development/add-action-meta-links-wordpress-plugins/
 */
function ayah_register_plugin_meta_links($links, $file) {

	if ($file == PLUGIN_BASENAME) {

		$links[] = '<a href="options-general.php?page=are-you-a-human/areyouahuman.php">' . __('Settings','captcha') . '</a>';
		$links[] = '<a href="http://support.areyouahuman.com" target="_blank">' . __('Support','captcha') . '</a>';
		$links[] = '<a href="http://www.areyouahuman.com/feedback">' . __('Feedback','captcha') . '</a>';
	}
	return $links;
}