<?php
// The error message when it is displayed on its own page
$AYAH_ERROR_MESSAGE = 'Sorry, but we could not verify that you are a human. Please press your browser\'s back button and try again.';

// The error message when displayed on a form after a failed PlayThru
$AYAH_ERROR_ON_FORMS = 'Sorry, but we could not verify that you are a human. Please complete the PlayThru again';
/**
 * Action attached to comment_form_after_fields and comment_form_logged_in_after hooks
 * Prints a div that allows us to move the PlayThru above the comment form if necessary
 */
function ayah_comment_form_after() {
	
	$options = ayah_get_options();
	  
	// Do not show if the user is logged and it is not enabled for logged in users
	if (is_user_logged_in() && 1 == $options['hide_registered_users']) {
		return;
	}
	
	// Print a div to which the PlayThru can be moved to if necessary
	echo "<div id='ayah-comment-after'></div>";
}

/**
 * Action attached to comment_form hook. Displays the PlayThru before the
 * comment form submit button
 */
function ayah_comment_form() {
	
	$options = ayah_get_options();
	  
	// Do not show if the user is logged and it is not enabled for logged in users
	if (is_user_logged_in() && 1 == $options['hide_registered_users']) {
		return;
	}

	// Display the PlayThru
	$ayah = ayah_load_library();

	$html = $ayah->getPublisherHTML();
	echo "<div id='ayah-comment' style='text-align: center'>" . $html .  "</div>";
	echo ayah_rearrange_elements($options['submit_id']);
}

/**
 * Moves the submit button to the bottom and moves the PlayThru above the
 * comment form if necessary
 *
 * TODO: It might be a good idea to use wp_enqueue_script instead, but there
 * might be issues with dynamically inserting the button
 */
function ayah_rearrange_elements($button_id = 'submit') {
	if ($button_id == '') {
		$button_id = 'submit';
	}
	
	$script = 	"<script type='text/javascript'>
					// This ensures the code is executed in the right order
					if (AYAH.divIDChanged == true) {
						rearrange_form_elements();
					} else {
						// TODO: This may not be long enough. The best way to do this is to
						// check again after 100 milliseconds (or shorter) until divIdChanged is true
						setTimeout('rearrange_form_elements()', 1000);
					}
					
					function rearrange_form_elements() {
						var button = document.getElementById('" . $button_id . "');
						if (button != null) {
							button.parentNode.removeChild(button);
							document.getElementById('ayah-comment').appendChild(button);
							
							var el = document.getElementById('ayah-comment-after');
							el.parentNode.removeChild(el);					
						}";
	
	// If the other playthru hook was called, we may need to remove this playthru
	if (did_action('comment_form_logged_in_after') != 0 || did_action('comment_form_after_fields') != 0) {
		$script .=		"else { 
							// If we don't find the submit button move the PlayThru up
							var afterDiv = document.getElementById('ayah-comment-after');
							// But only if we can move it up
							if (afterDiv != null) {
								var playThruDiv = document.getElementById('AYAH');
								if (playThruDiv == null) {
									var ss = document.getElementsByName('session_secret');
									playThruDiv = document.getElementById('AYAH' + ss[1].value);
								}
								playThruDiv.parentNode.removeChild(playThruDiv);
								afterDiv.appendChild(playThruDiv);
							}								
						}";
	}

	// Add the ending curly brace for the rearrange_form_elements() function.
	$script .= "}";
	
	// Add the closing script tage.
	$script .= "</script>";
			  
	// Return the script.
	return $script;
}

/**
 * Action attached to preprocess_comment. Validates PlayThru result.
 */
function ayah_comment_post($comment) {
    $options = ayah_get_options();
      
    // Do not show if the user is logged and it is not enabled for logged in users
	if ( is_user_logged_in() && 1 == $options['hide_registered_users'] ) {
		return $comment;
	}

	// Skip for comment replies from the admin menu
	if ( isset( $_POST['action'] ) && $_POST['action'] == 'replyto-comment' &&
				( check_ajax_referer( 'replyto-comment', '_ajax_nonce', false ) || 
				check_ajax_referer( 'replyto-comment', '_ajax_nonce-replyto-comment', false ) ) ) {
		return $comment;
	}

	// Skip for trackback or pingback
	if ( $comment['comment_type'] != '' && $comment['comment_type'] != 'comment' ) {
		return $comment;
	}
	
	$ayah = ayah_load_library();
	
	// Score the game
	if ( $ayah->scoreResult() ) {	
		return $comment;
	} else {
		global $AYAH_ERROR_MESSAGE;
		wp_die( __($AYAH_ERROR_MESSAGE, 'ayah'));
	}
}

/**
 * Attached to register_form. Displays the PlayThru during new user registration.
 */
function ayah_register_form() {
     
    $ayah = ayah_load_library();
    
    echo $ayah->getPublisherHTML();
    
    return true;
}

/**
 * Attached to register_post. Validates PlayThru result.
 * This function receives three parameters; we only need the WP_Error $errors variable, which we pass to a common
 * function responsible for checking the score and appending an error.
 *
 * @param string    $foo    (unused)
 * @param string    $bar    (unused)
 * @param WP_Error  $errors The WP_Error object to which we will append an error if the PlayThru did not succeed.
 */
function ayah_register_post($foo, $bar, $errors) {
    ayah_score_playthru_and_append_error($errors);
}

/**
 * Attached to wpmu_validate_user_signup (WP multi-site only). Validates PlayThru result.
 * Receives one parameter which looks like this:
 *    $result = array('user_name' => $user_name, 'orig_username' => $orig_username, 'user_email' => $user_email,
 *                    'errors' => $errors);
 * We only care about the WP_Error object $errors, so we pull it out and use a common function to perform the score
 * check and append an error if necessary.
 *
 * @param array $result
 *
 * @return array The $result array.
 */
function ayah_wpmu_validate_user_signup($result) {
    // Skip this for the 'blog' step
    global $stage;
    if($stage == "validate-blog-signup") return $result;

    /** @var WP_Error $errors */
    $errors = $result['errors'];
    ayah_score_playthru_and_append_error($errors, 'generic');

    return $result;
}

    /**
     * This function checks the PlayThru score and, if unsuccessful, appends an error message to the list of errors.
     *
     * @param WP_Error $errors
     * @param string   $error_code (optional) error code to assign
     */
function ayah_score_playthru_and_append_error($errors, $error_code = 'playthru_wrong'){

    // Load the Are You A Human library
    $ayah = ayah_load_library();

    // Check the score result
    if ($ayah->scoreResult()) {
        return;
    } else {
        global $AYAH_ERROR_ON_FORMS;
        $errors->add($error_code, '<strong>' . __('ERROR', 'ayah') . '</strong>: ' . __($AYAH_ERROR_ON_FORMS, 'ayah'));
    }
}

/**
 * Attached to lostpassword_form. Displays the PlayThru on the lost password form
 */
function ayah_lost_password_form() {
    // Same as the register form
    ayah_register_form();
}

/**
 * Attached to lostpassword_post. Validates PlayThru result.
 */
function ayah_lost_password_post() {
  
    $ayah = ayah_load_library();
    
    if ( $ayah->scoreResult() ) {
        return;
    } else {
		global $AYAH_ERROR_MESSAGE;
        wp_die(__($AYAH_ERROR_MESSAGE,'ayah'));
    }
}
