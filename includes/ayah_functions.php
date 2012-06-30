<?php

// TODO: Clean this page up

function ayah_load_library() {
    $ayah = new AYAH(array( 'publisher_key' => $_SESSION['ayah_options']['publisher_key'],
                            'scoring_key' => $_SESSION['ayah_options']['scoring_key'],
                            'web_service_host' => AYAH_WEB_SERVICE_HOST
                ));
    return $ayah;
}

// TODO: Rename
function ayah_run_controller() {
    
    // special code to clear the AYAH settings from the db
    if ($_POST['ayah_clear_options'] == 'true') {
        ayah_delete_options();
    }

	// Check to see if we have existing options
	ayah_get_options();

    // check to see if we are processing a form post
    switch ($_POST['ayah']['action']) {
        case TRUE:
            $_SESSION['ayah_page_action'] = $_POST['ayah']['action'];
        case 'install':
            ayah_install_plugin();
            break;
        case 'upgrade':
            ayah_upgrade_plugin();
            break;
        case 'settings':
            ayah_update_settings();
            break;
        default:
            $_SESSION['ayah_page_action'] = ayah_check_for_upgrade_or_install();
            break;
    }
	
    ayah_get_settings_page();
}

function ayah_update_settings() {
    // this does the same thing as install right now
    ayah_install_plugin();
}

function ayah_install_plugin() {
    $options = ayah_get_settings_post();

    // store publisher key and scoring key
    ayah_set_options($options);
    ayah_get_options();
	
}

function ayah_upgrade_plugin() {
    $options = $_SESSION['ayah_options'];
    $options['version'] = AYAH_VERSION;
    
    ayah_set_options($options);
    ayah_get_options();
}

// TODO: Add submit_id
function ayah_upgrade_legacy_plugin() {
    
    // Get the previous settings
    $ayah_options = $_SESSION['ayah_options'];
    $pub_key = $ayah_options['ayah_publisher_key'];
    $scoring_key = $ayah_options['ayah_scoring_key'];
    $enable_register_form = $ayah_options['ayah_register_form'];
    $enable_lost_password_form = $ayah_options['ayah_lost_password_form'];
    $enable_comment_form = $ayah_options['ayah_comments_form'];
    $hide_registered_users = $ayah_options['ayah_hide_register'];
    
    // Clear the options out
    ayah_delete_options();
    
    // Set new options
    $options = array(   'version' => AYAH_VERSION,
                        'publisher_key' => $pub_key,
                        'scoring_key' => $scoring_key,
                        'enable_register_form' => $enable_register_form,
                        'enable_lost_password_form' => $enable_lost_password_form,
                        'enable_comment_form' => $enable_comment_form,
                        'hide_registered_users' => $hide_registered_users
                    );
    
    ayah_set_options($options);   
    ayah_get_options(); 
}

function ayah_check_for_upgrade_or_install() {

	$ayah_options = $_SESSION['ayah_options'];

    // check for previous installation
    if (isset($ayah_options['version'])) {
	
        // there's a previous installation, check for upgrade
        if (ayah_upto_date($ayah_options['version'])) {
        
            // version is up to date, move on to settings page
            return 'settings';
        
        } else {
            // version is not up to date, time to upgrade
            ayah_upgrade_plugin();
            return 'upgrade';
        }
    // either legacy plugin is installed or this is a fresh install
    } else {
    
        // check for legacy version of plugin
        if ($ayah_options['ayah_webservice_host']) {
        
            // legacy installed, upgrade
            ayah_upgrade_legacy_plugin();
            return 'upgrade';
        
        } else {
        
            // no plugin detected, fresh install
            return 'install';
        }
    }
}

function ayah_set_options($options) {
    global $wpmu;
    
	$options_allowed = array(   'publisher_key',
	                            'scoring_key',
	                            'version',
	                            'enable_register_form',
                                'enable_lost_password_form',
                                'hide_registered_users',
                                'enable_comment_form',
								'submit_id');
	$new_options = array();
	foreach($options_allowed as $optal) {
	    if(isset($options[$optal])) {
	        $new_options[$optal] = $options[$optal];
	    }
	}
	
	if ( 1 == $wpmu ) {
	    update_site_option( 'ayah_options', $new_options );
	} else {
	    update_option( 'ayah_options', $new_options );
	}
	
	return $new_options;
}

/** 
 * Checks if the database contains user settings and stores them in the $_SESSION variable
 * 
 * TODO: According to documentation a check isn't needed
 * @link http://codex.wordpress.org/Function_Reference/get_site_option
 */

function ayah_get_options() {
    global $wpmu;
    
    if ( 1 == $wpmu ){
    	$ayah_options = get_site_option( 'ayah_options', array() ); // blog network
    } else {
    	$ayah_options = get_option( 'ayah_options', array() ); // single site
    }

	$_SESSION['ayah_options'] = $ayah_options;
}

function ayah_delete_options() {
    global $wpmu;
    
    if ( 1 == $wpmu) {
        delete_site_option('ayah_options');
    } else {
        delete_option('ayah_options');
    }
}

function ayah_upto_date($install_version) {
	$iv = str_replace('.', '', $install_version);
	$cv = str_replace('.', '', AYAH_VERSION);
	
	return ($iv >= $cv);
}

/**
 * Get the options from the form data
 */
function ayah_get_settings_post() {

    $options = array(   'version' => AYAH_VERSION,
                        'publisher_key' => $_POST['ayah']['publisher_key'],
                        'scoring_key' => $_POST['ayah']['scoring_key'],
                        'enable_register_form' => $_POST['ayah']['enable_register_form'],
                        'enable_lost_password_form' => $_POST['ayah']['enable_lost_password_form'],
                        'enable_comment_form' => $_POST['ayah']['enable_comment_form'],
                        'hide_registered_users' => $_POST['ayah']['hide_registered_users'],
						'submit_id' => $_POST['ayah']['submit_id']
                    );
    return $options;
}

?>