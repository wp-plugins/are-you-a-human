<?php

/**
 * Displays the settings page
 */
function ayah_get_settings_page() {
    wp_enqueue_style( 'myPluginStylesheet' );
    
    $page_opts = array( 'title' => 'PlayThru Settings',
                        'button' => 'Update Settings',
                        'flash_message' => '<h3>These are your current keys, they should not need to be changed once set.</h3>'
                        );
                   
	// Displayed if settings were saved     
    if (isset($_POST['ayah'])) {
        $page_opts['flash_message'] = "Your settings have been saved!";
    }
    
    switch ($_SESSION['ayah_page_action']) {
        case 'install':
            $page_opts = ayah_get_install_page();
            break;
        case 'upgrade':
            $page_opts = ayah_get_upgrade_page();
            break;
    }
    
    $ayah_options = $_SESSION['ayah_options']; 	
    
    echo "<div class='wrap'>";
    
    echo "<h2>Are You a Human " . $page_opts['title'] . "</h2>";
    
    if ($page_opts['flash_message']) {
        echo $page_opts['flash_message'];
    }
        
    echo ayah_get_form($page_opts['button']);
        
    echo "</div>";
}

/**
 * Sets the page options for the upgrade page
 */
function ayah_get_upgrade_page() {
    
    $page_opts = array();
    $page_opts['title'] = 'Upgrade';
    $page_opts['button'] = 'Complete Upgrade';
    $page_opts['flash_message'] = "Please take a moment to make sure the following is correct and complete your upgrade.";
    
    if ($_SESSION['ayah_page_action'] == 'upgrade' && isset($_POST['ayah'])) {
        $page_opts['flash_message'] = "Your plugin has been successfully upgraded.";
        $page_opts['button'] = 'Update Settings';
    }
    
    return $page_opts;
}

/**
 * Sets the page options for the install page
 */
function ayah_get_install_page() {

    $page_opts = array();
    $page_opts['title'] =  "Installation";
    $page_opts['button'] = "Complete Installation";
    $page_opts['flash_message'] = "<p>To complete your installation:</p><ol><li><a href='http://portal.areyouahuman.com/signup/wordpress'>Register for an account</a> on Are You a Human.</li><li>Copy and paste the Publisher and Scoring keys into the fields below.</li></ol>";
    
    if ($_SESSION['ayah_page_action'] == 'install' && isset($_POST['ayah'])) {
        $page_opts['flash_message'] = "<div class='alert valid'>The plugin has been successfully installed.</div>";
        $page_opts['button'] = 'Update Settings';
    }
    
    return $page_opts;
}

/**
 * Returns the settings page HTML with the settings inputs filled in
 */
function ayah_get_form($button) {
    
	$ayah_options = $_SESSION['ayah_options'];
	
	// set up our checkbox checked
	$chk_enable_register_form = ($ayah_options['enable_register_form'] == '1') ? 'checked' : '';
	$chk_enable_lost_password_form = ($ayah_options['enable_lost_password_form'] == '1') ? 'checked' : '';
	$chk_enable_comment_form = ($ayah_options['enable_comment_form'] == '1') ? 'checked' : '';
	$chk_hide_registered_users = ($ayah_options['hide_registered_users'] == '1') ? 'checked' : '';

    $form_html = "
            <form action=".$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']." method='POST' id='playthru-options'>
                <fieldset>
                    <label>Enable PlayThru on:</label>
                    <p><input type='checkbox' name='ayah[enable_register_form]' value='1' ".$chk_enable_register_form." /> Registration Form</p>
                    <p><input type='checkbox' name='ayah[enable_lost_password_form]' value='1' ".$chk_enable_lost_password_form." /> Lost Password Form</p>
                    <p><input type='checkbox' name='ayah[enable_comment_form]' value='1' ".$chk_enable_comment_form." /> Comment Form</p>
                </fieldset>
                <fieldset>
                    <label>Hide from registered users?</label>
                    <p><input type='checkbox' name='ayah[hide_registered_users]' value='1' ".$chk_hide_registered_users." /> Yes</p>
                </fieldset>
                <fieldset>
                    <label>Publisher Key:</label>
                    <input type='text' name='ayah[publisher_key]' value='".$ayah_options['publisher_key']."'/>
                </fieldset>
                <fieldset>
                    <label>Scoring Key:</label>
                    <input type='text' name='ayah[scoring_key]' value='".$ayah_options['scoring_key']."'/>
                </fieldset>
				<fieldset>
					<label>Submit Button ID (Advanced):</label>
					<input type='text' name='ayah[submit_id]' value='".$ayah_options['submit_id']."'/>
				</fieldset>
                <fieldset>
	                <input type='hidden' name='page' value='".$_GET['page']."' />
	                <input type='hidden' name='ayah[action]' value='".$_SESSION['ayah_page_action']."' />
	                <button type='submit' class='button-primary'>".$button."</button>
	            </fieldset>
            </form>
    ";
    
    return $form_html;
    
}

function ayah_clear_options_button() {
    return "
        <form action=".$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']." method='POST'>
            <input type='hidden' name='page' value='".$_GET['page']."' />
            <input type='hidden' name='ayah_clear_options' value='true' />
            <button type='submit' onclick='return confirm(\"Are you sure you want to clear the options?\");'>Clear Options</button>
        </form>
    ";
}