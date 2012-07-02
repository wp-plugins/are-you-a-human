<?php

$ERROR_MESSAGE = 'Please complete the PlayThru again. Press your browser\'s back button and try again.';

/**
 * Action attached to comment_form_after_fields and comment_form_logged_in_after hooks
 * Prints a div that allows us to move the PlayThru above the comment form if necessary
 */
function ayah_comment_form_after() {
	
	$options = ayah_get_options();
	  
	// Do not show if the user is logged and it is not enabled for logged in users
	if (is_user_logged_in() and 1 == $options['hide_registered_users']) {
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
	if (is_user_logged_in() and 1 == $options['hide_registered_users']) {
		return;
	}

	// Display the PlayThru
	$ayah = ayah_load_library();

	$html = $ayah->getPublisherHTML();
	echo "<div id='ayah-comment' style='text-align: center'>" . $html .  "</div>";
	echo rearrange_elements($options['submit_id']);
}

/**
 * Moves the submit button to the bottom and moves the PlayThru above the
 * comment form if necessary
 *
 * TODO: It might be a good idea to use wp_enqueue_script instead, but there
 * might be issues with dynamically inserting the button
 */
function rearrange_elements($button_id = 'submit') {
	$script = 	"<script type='text/javascript'>
					var button = document.getElementById('" . $button_id . "');
					if (button != null) {
						button.parentNode.removeChild(button);
						document.getElementById('ayah-comment').appendChild(button);
						
						var el = document.getElementById('ayah-comment-after');
						el.parentNode.removeChild(el);					
					}";
	
	// If the other playthru hook was called, we may need to remove this playthru
	if (did_action('comment_form_logged_in_after') != 0 || did_action('comment_form_after_fields') != 0) {
		$script .=	"else { 
						// If we don't find the submit button move the PlayThru up
						var afterDiv = document.getElementById('ayah-comment-after');
						// But only if we can move it up
						if (afterDiv != null) {
							var playThruDiv = document.getElementById('AYAH');
							playThruDiv.parentNode.removeChild(playThruDiv);
							afterDiv.appendChild(playThruDiv);
						}								
					}";
	}
	
	$script .= "</script>";
			  
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
		global $ERROR_MESSAGE;
		wp_die( __($ERROR_MESSAGE, 'ayah'));
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
 */
function ayah_register_post($login, $email, $errors) {
   
    $ayah = ayah_load_library();
    
    if ( $ayah->scoreResult() ) {
        return;
    } else {
        $errors->add('playthru_wrong', '<strong>'.__('ERROR', 'ayah').'</strong>: '.__('Please complete the PlayThru again', 'ayah'));
    }
    return $errors;
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
		global $ERROR_MESSAGE;
        wp_die(__($ERROR_MESSAGE,'ayah'));
    }
}