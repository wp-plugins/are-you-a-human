<?php
/**
 * @package Are You A Human
 * @version 0.3.1
 */
/*
Plugin Name: Are You A Human?
Plugin URI:  http://www.areyouahuman.com/
Description: Plugin Captcha intended to prove that the visitor is a human being and not a spam robot. Plugin asks the visitor to play a short game.
Author: Are You A Human
Version: 0.3.1
Author http://www.areyouahuman.com/
*/

require_once("ayah.php");

$solved = false;
$ayah_options;
$ayah;



// These fields for the 'Enable CAPTCHA on the' block which is located at the admin setting captcha page
$ayah_admin_fields_enable = array (
//	array( 'ayah_login_form', 'Login form', 'Login form' ),
	array( 'ayah_register_form', 'Register form', 'Register form' ),
	array( 'ayah_lost_password_form', 'Lost password form', 'Lost password form' ),
	array( 'ayah_comments_form', 'Comments form', 'Comments form' ),
	array( 'ayah_hide_register', 'Hide CAPTCHA for registered users', 'Hide CAPTCHA for registered users' ),		
);

add_action( 'admin_menu', 'add_ayah_admin_menu' );

function add_ayah_admin_menu() {
	add_options_page( "Are You A Human? Options", "Are You A Human?", 'manage_options',  __FILE__, 'ayah_settings_page' );

	//call register settings function when the admin settings page loads
	add_action( 'admin_init', 'register_ayah_settings' );
}

// register settings function
function register_ayah_settings() {
	global $wpmu;
	global $ayah_options;
	
	$ayah_option_defaults = array(
//		'ayah_login_form' => '1',
		'ayah_register_form' => '1',
		'ayah_lost_password_form' => '1',
		'ayah_comments_form' => '1',
		'ayah_hide_register' => '0',
//		'ayah_label_form' => '',
		'ayah_webservice_host' => 'ws1.areyouahuman.com',
		'ayah_publisher_key' => '',
		'ayah_scoring_key' => '',
  	);

  // install the option defaults depending on whether single site or blog network
	if ( 1 == $wpmu ) {
		if( !get_site_option( 'ayah_options' ) ) {
			add_site_option( 'ayah_options', $ayah_option_defaults, '', 'yes' ); // blog network
		}
	} else {
		if( !get_option( 'ayah_options' ) ){
			delete_option('ayah_options');
			add_option( 'ayah_options', $ayah_option_defaults, '', 'yes' ); // single site
		}
	}
  // get options from the database
	if ( 1 == $wpmu ){
		$ayah_options = get_site_option( 'ayah_options' ); // blog network
	} else {
 		$ayah_options = get_option( 'ayah_options' ); // single site
  		//$ayah_options = array_merge( $ayah_option_defaults, $ayah_options ); // array merge incase this version has added new options
	}
}

// Add global setting for Captcha
global $wpmu;
global $ayah_options;
global $ayah_active;

if ( 1 == $wpmu ){
	$ayah_options = get_site_option( 'ayah_options' ); // blog network
} else {
	$ayah_options = get_option( 'ayah_options' ); // single site
}


// Check if all options have been added to the database, display errors
function showMessage($message, $errormsg = false) {
	if ($errormsg) {
		echo '<div id="message" class="error">';
	}
	else {
		echo '<div id="message" class="updated fade">';
	}

	echo "<p><strong>$message</strong></p></div>";
}
function showAdminMessage() {
    // Only show to admins
    if (is_admin()) {
       showMessage("Are You A Human is missing information it needs to activate", true);
    }
}

$ayah_options_to_check = array(
	'ayah_webservice_host',
	'ayah_publisher_key',
	'ayah_scoring_key'
);
foreach( $ayah_options_to_check as $option ){
	if(!isset($ayah_options[$option])){
		$ayah_active = false;
		add_action('admin_notices', 'showAdminMessage');
	}
}




// Add captcha into login form
/*
if ( 1 == $ayah_options['ayah_login_form'] ) {
	add_action( 'login_form', 'ayah_login_form' );
	add_filter( 'login_errors', 'ayah_login_post' );
	add_filter( 'login_redirect', 'ayah_login_check', 10, 3 ); 
}
*/

// Add captcha into comments form
if( 1 == $ayah_options['ayah_comments_form'] ) {
	global $wp_version;
	if( version_compare($wp_version,'3','>=') ) { // WP >3.0
		add_action( 'comment_form_after_fields', 'ayah_comment_form');
		add_action( 'comment_form_logged_in_after', 'ayah_comment_form');
		add_filter( 'preprocess_comment', 'ayah_comment_post' );
	} else { // for WP <3.0
		add_action( 'comment_form', 'ayah_comment_form' );
		add_filter( 'preprocess_comment', 'ayah_comment_post' );	
	}
}

// Add captcha in the register form
if( 1 == $ayah_options['ayah_register_form'] ) {
	add_action( 'register_form', 'ayah_register_form' );
	add_action( 'register_post', 'ayah_register_post', 10, 3 );
}

// Add captcha into lost password form
if( 1 == $ayah_options['ayah_lost_password_form'] ) {
	add_action( 'lostpassword_form', 'ayah_register_form' );
	add_action( 'lostpassword_post', 'ayah_lostpassword_post', 10, 3 );
}

// adds "Settings" link to the plugin action page
add_filter( 'plugin_action_links', 'ayah_plugin_action_links', 10, 2);

//Additional links on the plugin page
add_filter('plugin_row_meta', 'ayah_register_plugin_links', 10, 2);

function ayah_plugin_action_links( $links, $file ) {
		
	static $this_plugin; //Static so we don't call plugin_basename on every plugin row.
	
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if ( $file == $this_plugin ){
		 $settings_link = '<a href="options-general.php?page=are-you-a-human/areyouahuman.php">' . __('Settings', 'captcha') . '</a>';
		 array_unshift( $links, $settings_link );
	}
	return $links;
}

function ayah_register_plugin_links($links, $file) {
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href="options-general.php?page=are-you-a-human/areyouahuman.php">' . __('Settings','captcha') . '</a>';
		$links[] = '<a href="http://support.areyouahuman.com" target="_blank">' . __('FAQ','captcha') . '</a>';
		$links[] = '<a href="Mailto:humans@areyouahuman.com">' . __('Support','captcha') . '</a>';
	}
	return $links;
}

// Function for display captcha settings page in the admin area
function ayah_settings_page() {
	global $ayah_admin_fields_enable;
	global $ayah_admin_fields_actions;
	global $ayah_options;

	$error = "";
	
	$checkboxes = array(
//		'ayah_login_form',
		'ayah_register_form',
		'ayah_lost_password_form',
		'ayah_comments_form',
		'ayah_hide_register'
	);
	
	function error($key){
		$error_message = array(
			'ayah_webservice_host' => 'server URL',
			'ayah_publisher_key' => 'publisher key',
			'ayah_scoring_key' => 'scoring key'
		);
		return $error_message[$key];
	}
		
	// Save data for settings page
	if( isset( $_REQUEST['ayah_form_submit'] ) ) {
		$ayah_request_options = array();
		
		foreach( $ayah_options as $key => $val ) {
			
			if( isset( $_REQUEST[$key] ) ) {
				//process checkboxes differently
				if( in_array( $key, $checkboxes ) ){
					$ayah_request_options[$key] = 1;
				} else {					
					if($_REQUEST[$key] == '' && $key != 'ayah_label_form'){
						$error .= 'Error: Enter '. error($key) .'<br>';
					} else {
						$ayah_request_options[$key] = $_REQUEST[$key];
					}
				}
				
			} else {
				//process checkboxes differently
				if( in_array( $key, $checkboxes ) ){
					$ayah_request_options[$key] = 0;
				} else {
					if($_REQUEST[$key] == ''){
						$error .= 'Error: Enter '. error($key) .'<br>';
					}
				}	
			}	
		}

		// Update options in the database
		if( $error == ""){
			
			// array merge incase this version has added new options
			$ayah_options = array_merge( $ayah_options, $ayah_request_options );
			
			update_option( 'ayah_options', $ayah_request_options, '', 'yes' );
			$message = "Options saved.";
			
		} else {
			$message = "there are errors";
		}
		
	}

// Display form on the setting page
?>
<div class="wrap">
	<style>
		input.recaptcha {
			width: 310px;
		}
		input.gameid {
			width: 25px;
		}
		input.publisherkey {
			width: 300px;
		}
		input.server {
			
		}
	</style>
	<div class="icon32" id="icon-options-general"><br></div>
	<h2>Captcha Options</h2>
	<div class="updated fade" <?php if( ! isset( $_REQUEST['ayah_form_submit'] ) || $error != "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
	<div class="error" <?php if( "" == $error ) echo "style=\"display:none\""; ?>><p><strong><?php echo $error; ?></strong></p></div>
	<form method="post" action="options-general.php?page=are-you-a-human/areyouahuman.php">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Enable Are You A Human? on: </th>
				<td>
			<?php foreach( $ayah_admin_fields_enable as $fields ) { ?>
					<input type="checkbox" name="<?php echo $fields[0]; ?>" value="<?php echo $fields[0]; ?>" <?php if( 1 == $ayah_options[$fields[0]] ) echo "checked=\"checked\""; ?> /><label for="<?php echo $fields[0]; ?>"><?php echo $fields[1]; ?></label><br />
			<?php } 
			$active_plugins = get_option('active_plugins');
			?>
				</td>
			</tr>
			<?php /* ?>
			<tr valign="top">
				<th scope="row">Label for CAPTCHA in form</th>
				<td><input type="text" name="ayah_label_form" value="<?php echo $ayah_options['ayah_label_form']; ?>" <?php if( 1 == $ayah_options['ayah_label_form'] ) echo "checked=\"checked\""; ?> /></td>
			</tr>
			<?php */?>
			<tr valign="top">
				<th scope="row">Server URL</th>
				<td><input class="server" type="text" name="ayah_webservice_host" value="<?php echo $ayah_options['ayah_webservice_host']; ?>"/></td>
			</tr>
			<tr valign="top">
				<th scope="row">Publisher Key</th>
				<td><input class="publisherkey" type="text" name="ayah_publisher_key" value="<?php echo $ayah_options['ayah_publisher_key']; ?>"/></td>
			</tr>
			<tr valign="top">
				<th scope="row">Scoring Key</th>
				<td><input class="publisherkey" type="text" name="ayah_scoring_key" value="<?php echo $ayah_options['ayah_scoring_key']; ?>"/></td>
			</tr>
		</table>    
		<input type="hidden" name="ayah_form_submit" value="submit" />
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
	
</div>
<?php }

/**
 * implements action hook 'login_form'
 * Inserts playthru markup
 *
 * @return boolean true - dunno why
 */
/*
function ayah_login_form() {	

	$ayah = ayah_init();
		
	echo $ayah->getPublisherHTML();
	
	echo '
	<style>
		#login {
			width: 380px !important;
		}
		#__AYAH__ayah {
			margin-bottom: 10px;
		}
		#__AYAH__recaptcha {
			margin-bottom: 10px;
		}
	</style>
	';
		
	return true;
}
*/

/**
 * implements filter hook 'login_errors'
 *
 * @param string $errors - generated by wordpress
 * @return string $errors
 */
/*
function ayah_login_post($errors) {

	$ayah = ayah_init();
	
	if( $_REQUEST['action'] == 'register' ){
		return($errors);
	}

	if ( $ayah->scoreResult() ) {
		// captcha was matched						
	} else {
		return $errors.'<strong>'. __('ERROR', 'ayah') .'</strong>: '. __('That CAPTCHA was incorrect.', 'ayah');
	}
  return($errors);

return
}
*/

/**
 * implements filter hook 'login_redirect'
 *
 * @param string $url 
 */
/*
function ayah_login_check($url) {	

	$ayah = ayah_init();
	
	if ( $ayah->scoreResult() ) {
		// captcha was matched
		
		echo "passed";
		//return $url;							
	} else {
		// Redirect to wp-login.php
		
		echo "failed";
		//return $_SERVER["REQUEST_URI"];
	}
}*/

/**
 * implements action hook 'comment_form'
 *
 * @return boolean - true (dunno why)
 */
function ayah_comment_form() {
	
	// skip captcha if user is logged in and the settings allow
	if ( is_user_logged_in() && 1 == $ayah_options['ayah_hide_register'] ) {
		return true;
	}
	
	//make a new integration object
	$ayah = ayah_init();
	
	//insert game markup
	echo $ayah->getPublisherHTML();
	
	//CSS format changes 
	echo ayah_css();
	
	return true;
}

/**
 * Implements action hook: preprocess_comment
 * Scores game on comment submission
 * Kills WP to prevent comment submission on game failure
 *
 * @param string $comment - WP array with comment form values
 * @return string $comment
 */
function ayah_comment_post($comment) {	
	
	//skip if hidden for logged in users
	if ( is_user_logged_in() && 1 == $ayah_options['ayah_hide_register'] ) {
		return $comment;
	}

	//skip for comment replies from the admin menu
	if ( isset( $_POST['action'] ) && $_POST['action'] == 'replyto-comment' &&
				( check_ajax_referer( 'replyto-comment', '_ajax_nonce', false ) || 
				check_ajax_referer( 'replyto-comment', '_ajax_nonce-replyto-comment', false ) ) ) {
		return $comment;
	}

	//skip for trackback or pingback
	if ( $comment['comment_type'] != '' && $comment['comment_type'] != 'comment' ) {
		// skip captcha
		return $comment;
	}
	
	//Make a new integration object
	$ayah = ayah_init();
	
	//Score the game
	if ( $ayah->scoreResult() ) {	
		return($comment); // captcha was matched
	} else {
		wp_die( __('We could not verify you as human. Press your browser\'s back button and try again.', 'ayah'));
	}
}

// Inserts playthru into Register form
function ayah_register_form() {
	
	//Make a new integration library object
	$ayah = ayah_init();
	
	//Add some CSS that we use for every form
	echo ayah_css();
	
	//Insert the game markup
	echo $ayah->getPublisherHTML();
	
	//Allow wordpress to continue processing
	return true;
}

// this function checks captcha posted with registration
function ayah_register_post($login,$email,$errors) {
	$ayah = ayah_init();

	if ( $ayah->scoreResult() ) {
		// captcha was matched						
	} else {
		$errors->add('captcha_wrong', '<strong>'.__('ERROR', 'ayah').'</strong>: '.__('Please complete the PlayThru again', 'ayah'));
	}
  return($errors);
}

// this function checks the captcha posted with lostpassword form
function ayah_lostpassword_post() {
	$ayah = ayah_init();

	// If field 'user login' is empty - return
	if( "" == $_POST['user_login'] ){
		return;
	}
		
	// Check entered captcha
	if ( $ayah->scoreResult() ) {
		return;
	} else {
		wp_die( __('Please complete the PlayThru again. Press your browser\'s back button and try again.', 'ayah'));
	}
}

function ayah_css(){
	return '
	<style>
		#login {
			width: 460px !important;
		}
		#AYAH {
			margin: 10px 0;
		}
	</style>
	';
}
function ayah_init() {
	global $ayah_options;
	return new AYAH(array(
		'publisher_key' => $ayah_options['ayah_publisher_key'],
		'scoring_key' => $ayah_options['ayah_scoring_key'],
		'webservice_host' => $ayah_options['ayah_webservice_host'],
	));
}
?>
