<?php
/**
 * Integration with WP-Members plugin.
 */

/**
 * Adds the PlayThru code to the hidden fields section
 * of the WP-Members registration and password reset forms.
 *
 * Filters handled: wpmem_register_hidden_fields
 *                  wpmem_login_hidden_fields
 *
 * @param string $hidden The HTML code of hidden form fields.
 * @param null   $action Action being performed. [new|edit]
 *
 * @return string The resulting hidden form field HTML code.
 */
function ayahwpm_playthru($hidden, $action = null)
{
    // Load AYAH options
    $options = ayah_get_options();

    // Disable Are You A Human functionality when in 'edit' mode.
    // Also disable AYAH if the user is logged in and we should hide the PlayThru for registered users
    if($action == 'edit' || (is_user_logged_in() && 1 == $options['hide_registered_users'])) {
        // Simply return unmodified HTML
        return $hidden;
    }

    // Initialize AYAH library
    $ayah = ayah_load_library();

    // Append the PlayThru code for inclusion in the registration form
    $hidden .= $ayah->getPublisherHTML();

    // Return the modified HTML code for hidden fields
    return $hidden;
}

    /**
     * Validates Are You A Human game result.
     *
     * This is a filter handler for:
     * wpmem_pre_validate_form,
     * wpmem_pwdreset_args
     *
     * @param array $fields Submitted form fields.
     *
     * @return array Form fields, post-processed.
     */
    function ayahwpm_validate($fields = array())
    {
        // Initialize AYAH library
        $ayah = ayah_load_library();

        // Load AYAH options
        $options = ayah_get_options();

        // Disable AYAH if the user is logged in and we should hide the PlayThru for registered users
        if(is_user_logged_in() && 1 == $options['hide_registered_users']) {
            // Simply return unmodified form fields
            return $fields;
        }

        // If the the PlayThru was successful
        if ($ayah->scoreResult()) {
            // Return the form fields given to us
            return $fields;
        } else {
            // If PlayThru failed, display an error message
            global $AYAH_ERROR_MESSAGE;

            // Display the error and die
            wp_die( __($AYAH_ERROR_MESSAGE, 'ayah'));
        }
    }

/**
 * This function begins buffering the output of WP-Members admin screen.
 * It should be called with the highest priority. We will then call a second
 * hook to capture the buffered output and to remove CAPTCHA settings from
 * it.
 *
 * @param string $tab            Which tab is the user on? We only care about the "options" tab.
 * @param array  $wpmem_settings Settings array.
 */
function ayahwpm_settings_page_pre($tab, $wpmem_settings) {

    // If the user is on the options tab
    if($tab == 'options') {
        // Begin output buffering so we can capture the settings form for modification
        ob_start();
    }
}

/**
 * This function stops output buffering of the WP-Members admin page and then processes the HTML
 * to remove the CAPTCHA setting, replacing it with a hidden form input (which always disables CAPTCHA)
 * and showing a link to the AYAH settings screen.
 *
 * @param string $tab           Which tab we are on.
 * @param array $wpmem_settings The settings.
 */
function ayahwpm_settings_page_post($tab, $wpmem_settings) {
    if($tab == 'options') {
        // Stop output buffering and grab contents
        $form = ob_get_clean();

        // Compose a status message, redirecting users to AYAH settings page
        $status = <<<EOT
<label>CAPTCHA Settings</label><span class="description" style="display: table">The <a href="?page=are-you-a-human">Are You A Human plugin</a> is active, replacing annoyances like CAPTCHA with games people love; to configure when and where they are shown, go to <a href="?page=are-you-a-human">PlayThru settings</a>.</span>
EOT;

        // Compose a hidden form input that disables CAPTCHA in WP-Members
        $disable_captcha = <<<EOT
<input type="hidden" name="wpmem_settings_captcha" value="0">
EOT;

        // Replace CAPTCHA option with a hidden input
        $form = preg_replace('|<label>[^<]+</label>\s*<select name="wpmem_settings_captcha">.*?</select>|sm', $status . $disable_captcha, $form);

        // Echo the modified code.
        echo $form;
    }
}

    /**
     * Disables the built-in CAPTCHA setting in WP-Members
     *
     * @param $wpm_settings array WP-Members settings array
     *
     * @return array
     */
function ayahwpm_settings($wpm_settings) {
    $wpm_settings[6] = 0;
    return $wpm_settings;
}