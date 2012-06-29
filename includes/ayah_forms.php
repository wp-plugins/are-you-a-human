<?php

function ayah_comment_form_after() {

	ayah_get_options();
	  
	// Do not show if the user is logged and it is not enabled
	if ( is_user_logged_in() and 1 == $_SESSION['ayah_options']['hide_registered_users']) {
		return TRUE;
	}
	
	echo "<div id='ayah-comment-after'></div>";

	return TRUE;
}

function ayah_comment_form() {

	ayah_get_options();
	  
	// Do not show if the user is logged and it is not enabled
	if ( is_user_logged_in() and 1 == $_SESSION['ayah_options']['hide_registered_users']) {
		return TRUE;
	}

	$ayah = ayah_load_library();

	$html = $ayah->getPublisherHTML();
	echo "<div id='ayah-comment' style='text-align: center'>" . $html .  "</div>";
	echo rearrange_elements($_SESSION['ayah_options']['submit_id']);
	return TRUE;
}

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
		$script .=		"else { 
							// If we don't find the submit button move the PlayThru up
							var afterDiv = document.getElementById('ayah-comment-after');
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

function ayah_comment_post($comment) {
    ayah_get_options();
      
    // Skip if hidden for logged in users
	if ( is_user_logged_in() && 1 == $_SESSION['ayah_options']['hide_registered_users'] ) {
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
		wp_die( __('We could not verify you as human. Press your browser\'s back button and try again.', 'ayah'));
	}
}

function ayah_register_form() {
    ayah_get_options();
      
    $ayah = ayah_load_library();
    
    echo $ayah->getPublisherHTML();
    
    return true;
}

function ayah_register_post($login, $email, $errors) {
    ayah_get_options();
    
    $ayah = ayah_load_library();
    
    if ( $ayah->scoreResult() ) {
        return;
    } else {
        $errors->add('playthru_wrong', '<strong>'.__('ERROR', 'ayah').'</strong>: '.__('Please complete the PlayThru again', 'ayah'));
    }
    return $errors;
}

function ayah_lost_password_form() {
    //same as the register form
    ayah_register_form();
}

function ayah_lost_password_post() {
    ayah_get_options();
    
    $ayah = ayah_load_library();
    
    if ( $ayah->scoreResult() ) {
        return;
    } else {
        wp_die(__('Please complete the PlayThru again. Press your browser\'s back button and try again.','ayah'));
    }
}

function ayah_load_library() {
    $ayah = new AYAH(array( 'publisher_key' => $_SESSION['ayah_options']['publisher_key'],
                            'scoring_key' => $_SESSION['ayah_options']['scoring_key'],
                            'web_service_host' => AYAH_WEB_SERVICE_HOST
                ));
    return $ayah;
}