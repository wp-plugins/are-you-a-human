<div class='ayah-col-right'>
	<div class='ayah-box'>
		<div class='inside'>
			<h2>Contact Form 7 and Gravity Forms Integration</h2>
			<p>PlayThru now supports Contact Form 7 and Gravity Forms integration. Simply install the plugin and Are You A Human will appear as an option in the form builder!</p>
		</div>
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
		<div class='footer'><a href='http://twitter.com/areyouahuman' target='_blank'> <img src='<?php echo plugins_url('images/twitter.png', PLUGIN_BASENAME)?>' alt='Follow us on Twitter'></a> <a href='http://facebook.com/areyouahuman' target='_blank'> <img src='<?php echo plugins_url('images/facebook.png', PLUGIN_BASENAME)?>' alt='Like us on Facebook'></a> <a href='http://wordpress.org/extend/plugins/are-you-a-human/' target='_blank'> <img src='<?php echo plugins_url('images/wordpress.png', PLUGIN_BASENAME)?>'  alt='Rate us 5 stars!'></a></div>
	</div>
	
	<div class='ayah-box'>
		<div class='inside'>
			<h2>Need Help?</h2>
			<p>If you're having trouble getting PlayThru working on your site, let us know in the <a href="http://support.areyouahuman.com" target="_blank">Support Forums</a>.</p>
			
			<h2>Want to give us feedback?</h2>
			<p>We're always looking for excuses to make PlayThru better, so <a href="http://areyouahuman.com/feedback" target="_blank">send your feedback</a> our way! The good, the bad, and the ugly - we read it all!</p>
		</div>
	</div>
</div>

<div class='ayah-col-left'>
	<div class='ayah-box'>
		<div class='inside'>
			<h2>PlayThru Settings</h2>
			<p>Select where you'd like PlayThru to appear on your site.</p>
			<form action='<?php echo($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']); ?>' method='POST' id='playthru-options'>
				<fieldset>
					<label>Enable PlayThru on:</label>
					<p><input type='checkbox' name='ayah[enable_register_form]' value='1' <?php echo $chk_enable_register_form; ?> /> Registration Form</p>
					<p><input type='checkbox' name='ayah[enable_lost_password_form]' value='1' <?php echo $chk_enable_lost_password_form; ?> /> Lost Password Form</p>
					<p><input type='checkbox' name='ayah[enable_comment_form]' value='1' <?php echo $chk_enable_comment_form; ?> /> Comment Form</p>
				</fieldset>
				<fieldset>
					<label>Hide from registered users?</label>
					<p><input type='checkbox' name='ayah[hide_registered_users]' value='1' <?php echo $chk_hide_registered_users; ?> /> Yes</p>
				</fieldset>
				
				<?php if (!ayah_is_key_missing()): ?>
				<p>These are your current keys, they should not need to be changed once set.</p>
				<?php else: ?>
				<p>To get your Publisher and Scoring keys login to your account at <a href='http://portal.areyouahuman.com/login' target='_blank'>portal.areyouahuman.com</a> and paste them below.</p>
				<?php endif; ?>
				<fieldset>
					<label>Publisher Key:</label>
					<input type='text' name='ayah[publisher_key]' value='<?php echo $ayah_options['publisher_key']; ?>'/>
				</fieldset>
				<fieldset>
					<label>Scoring Key:</label>
					<input type='text' name='ayah[scoring_key]' value='<?php echo $ayah_options['scoring_key']; ?>'/>
				</fieldset>
				
				<h2>Advanced Settings</h2>
				<fieldset>
					<label>Comment Submit Button ID</label>
					<p>If PlayThru is showing up below your comment's submit button, enter the ID of that button here.</p>
					<p>Example:<br/>
					<pre><code>&lt;input name='submit_name' type='submit' id='submit_id' value='Submit Comment'&gt;</code></pre>
					The ID of the submit button is <strong>submit_id</strong>.</p>
					<input type='text' name='ayah[submit_id]' value='<?php echo $ayah_options['submit_id']; ?>' />
				</fieldset>
				<fieldset>
					<input type='hidden' name='page' value='<?php echo $_GET['page']; ?>' />
					<input type='hidden' name='ayah[action]' value='<?php echo $action; ?>' />
					<button type='submit' class='button-primary'><?php echo $button; ?></button>
				</fieldset>
			</form>
		</div>
	</div>
</div>
