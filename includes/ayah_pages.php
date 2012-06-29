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
    		<div class='ayah-col-left'>
                <div class='ayah-box'>
        			<div class='inside'>
        				<h2>PlayThru Settings</h2>
                			<p>Select where you'd like PlayThru to appear on your site.</p>
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
				                
				                <p>To get your Publisher and Scoring keys login to your account at <a href='http://portal.areyouahuman.com/login' target='_blank'>portal.areyouahuman.com</a> and paste them below.</p>
				                <fieldset>
				                    <label>Publisher Key:</label>
				                    <input type='text' name='ayah[publisher_key]' value='".$ayah_options['publisher_key']."'/>
				                </fieldset>
				                <fieldset>
				                    <label>Scoring Key:</label>
				                    <input type='text' name='ayah[scoring_key]' value='".$ayah_options['scoring_key']."'/>
				                </fieldset>
				            <h2>Advanced Settings</h2>
								<fieldset>
									<label>Comment Submit Button ID</label>
									<p>If PlayThru is showing up below your comment's submit button, enter the ID of that button here. If you do not know how to find the ID of your submit button, please read our FAQs.</p>
									<p>You can find the submit button ID by looking at the source code of the page. For example, if the submit button looks like,
<code><pre>&lt;input name='submit_name' type='submit' id='submit_id' value='Submit Comment'&gt;</pre></code>
The ID of the submit button is <strong>submit_id</strong>.</p>
									<input type='text' name='ayah[submit_id]' value='".$ayah_options['submit_id']."'/>
								</fieldset>
				                <fieldset>
					                <input type='hidden' name='page' value='".$_GET['page']."' />
					                <input type='hidden' name='ayah[action]' value='".$_SESSION['ayah_page_action']."' />
					                <button type='submit' class='button-primary'>".$button."</button>
					            </fieldset>
				            </form>
				           </div>
                	</div>
                </div>
    ";
echo "<div class='ayah-col-right'>
		<div class='ayah-box'>
        	<div class='inside'>
        		<h2>PlayThru &amp; Contact Form 7</h2>
        			<p>PlayThru is now available for Contact Form 7 integration. To get PlayThru working with your Contact Form 7 Plugin, follow the instructions on our <a href='http://portal.areyouahuman.com/installation/wordpress#cf7' target='_blank'>installation page</a>.
        	</div>
        	<div class='footer'><a href='http://wordpress.org/extend/plugins/are-you-a-human-cf7-extension/' target='_blank'>View Are You a Human PlayThru extension for CF7 &raquo;</a></div>
        </div>
        
        
        	<div class='ayah-box'>
        		<div class='inside'>
        		<h2>Help Stop the Bots! </h2>
        			<p>Want to assist in making the web more usable for humans rather than bots?  All donations are used to improve this plugin, so donate $10, $25, $50 (or more) right now!</p>
        			<p><form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
					<input type='hidden' name='cmd' value='_s-xclick'>
					<input type='hidden' name='hosted_button_id' value='GLF5DV7VCP2XU'>
					<input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'>
					<img alt='' border='0' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif' width='1' height='1'>
					</form></p>
        		</div>
        		<div class='footer'><a href='http://twitter.com/areyouahuman' target='_blank'> <img src='".plugins_url('images/twitter.png', PLUGIN_BASENAME)."' alt='Follow us on Twitter'></a> <a href='http://facebook.com/areyouahuman' target='_blank'> <img src='".plugins_url('images/facebook.png', PLUGIN_BASENAME)."' alt='Like us on Facebook'></a> <a href='http://wordpress.org/extend/plugins/are-you-a-human/' target='_blank'> <img src='".plugins_url('images/wordpress.png', PLUGIN_BASENAME)."'  alt='Rate us 5 stars!'></a></div>
        	</div>
       </div>";         
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