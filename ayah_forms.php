<?php

function ayah_comment_form() {
    ayah_get_options();
      
    // do not show if the user is logged and it is not enabled
    if ( is_user_logged_in() and 1 == $_SESSION['ayah_options']['hide_registered_users']) {
        return TRUE;
    }
    
    $ayah = ayah_load_library();
    
    echo $ayah->getPublisherHTML();
    
    return true;
}

function ayah_comment_post() {
    ayah_get_options();
      
    //skip if hidden for logged in users
	if ( is_user_logged_in() && 1 == $_SESSION['ayah_options']['hide_registered_users'] ) {
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
	
	$ayah = ayah_load_library();
	
	//Score the game
	if ( $ayah->scoreResult() ) {	
		return($comment); // captcha was matched
	} else {
		wp_die( __('We could not verify you as human. Press your browser\'s back button and try again.', 'ayah'));
	}
}

function ayah_load_library() {
    $ayah = new AYAH(array( 'publisher_key' => $_SESSION['ayah_options']['publisher_key'],
                            'scoring_key' => $_SESSION['ayah_options']['scoring_key'],
                            'web_service_host' => AYAH_WEB_SERVICE_HOST
                ));
    return $ayah;
}