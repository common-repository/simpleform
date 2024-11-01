<?php
/**
 * File delegated to show the settings admin page.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/partials
 */

defined( 'ABSPATH' ) || exit;

$form          = isset( $_GET['form'] ) ? absint( $_GET['form'] ) : 1; // phpcs:ignore
$util          = new SimpleForm_Util();
$admin_notices = $util->get_sform_option( 1, 'settings', 'admin_notices', false );
$notice_class  = $admin_notices ? 'invisible' : '';
$version_alert = get_transient( 'sform_version_alert' );
$notice_class .= false !== $version_alert ? ' unseen' : '';
$wrap_class    = false !== $version_alert ? 'spaced' : '';
$notice        = '';
$color         = strval( $util->get_sform_option( 1, 'settings', 'admin_color', 'default' ) );
global $wpdb;
$page_forms   = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}sform_shortcodes WHERE widget = '0' AND status != 'trash' ORDER BY name ASC", 'ARRAY_A' ); // phpcs:ignore.
$widget_forms = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}sform_shortcodes WHERE widget != '0' AND status != 'trash' AND status != 'inactive' ORDER BY name ASC", 'ARRAY_A' ); // phpcs:ignore.
$page_ids     = array_column( $page_forms, 'id' );
$widget_ids   = array_column( $widget_forms, 'id' );
$forms        = array_map( 'intval', array_merge( $page_ids, $widget_ids ) );
$all_forms    = count( $page_forms ) + count( $widget_forms );
$page_options = $widget_forms ? '<optgroup label="' . esc_attr__( 'Embedded in page', 'simpleform' ) . '">' : '';
foreach ( $page_forms as $page_form ) {
	$selected_page = (int) $page_form['id'] === $form ? 'selected="selected"' : '';
	$page_options .= '<option value="' . $page_form['id'] . '" ' . $selected_page . '>' . $page_form['name'] . '</option>';
}
$page_options  .= $widget_forms ? '</optgroup>' : '';
$widget_options = $widget_forms ? '<optgroup label="' . esc_attr__( 'Embedded in widget area', 'simpleform' ) . '">' : '';
foreach ( $widget_forms as $widget_form ) {
	$selected_widget = (int) $widget_form['id'] === $form ? 'selected="selected"' : '';
	$widget_options .= '<option value="' . $widget_form['id'] . '" ' . $selected_widget . '>' . $widget_form['name'] . '</option>';
}
$widget_options  .= $widget_forms ? '</optgroup>' : '';
$forms_selector   = $all_forms > 1 ? '<div class="selector"><div id="wrap-selector" class="responsive">' . __( 'Select Form', 'simpleform' ) . ':</div><div class="form-selector"><select name="form" id="form" class="' . esc_attr( $color ) . '">' . $page_options . $widget_options . '</select></div></div>' : '';
$new_notice       = isset( $_GET['status'] ) && 'new' === $_GET['status'] && ! empty( get_transient( 'sform_action_newform' ) ) ? '<div class="notice notice-success is-dismissible"><p>' . __( 'The new contact form has been successfully created. Customize settings before you start using it!', 'simpleform' ) . '</p></div>' : ''; // phpcs:ignore
$editor_arg       = 1 !== $form ? '&form=' . $form : '';
$form_arg         = 1 !== $form ? '&id=' . $form : '';
$main_page_button = 1 !== $form ? '<a href="' . menu_page_url( 'sform-settings', false ) . '"><span class="dashicons dashicons-edit icon-button admin ' . esc_attr( $color ) . '"></span><span class="settings-page wp-core-ui button admin">' . __( 'Go to main settings for edit', 'simpleform' ) . '</span></a>' : '';
$disabled_class   = 1 !== $form ? 'disabled' : '';
$disabled_option  = 1 !== $form ? ' disabled="disabled"' : '';
$frontend_notice  = $util->get_sform_option( 1, 'settings', 'frontend_notice', true );
$extra_option     = '';
$ajax             = $util->get_sform_option( $form, 'settings', 'ajax_submission', false );
$ajax_class       = ! $ajax ? 'unseen' : '';
$spinner          = $util->get_sform_option( $form, 'settings', 'spinner', false );
$html5_validation = $util->get_sform_option( $form, 'settings', 'html5_validation', false );
$focus            = $util->get_sform_option( $form, 'settings', 'focus', 'field' );
$out_error        = $util->get_sform_option( $form, 'settings', 'outside_error', 'bottom' );
$focus_out        = 'none' === $out_error ? __( 'Do not move focus', 'simpleform' ) : __( 'Set focus to error message outside', 'simpleform' );
$form_style       = $util->get_sform_option( $form, 'settings', 'form_template', 'default' );
$style_notes      = 'customized' === $form_style ? __( 'Create a directory inside your active theme\'s directory, name it "simpleform", copy one of the template files, and name it "custom-template.php"', 'simpleform' ) : '&nbsp;';
$stylesheet       = $util->get_sform_option( 1, 'settings', 'stylesheet', false );
$css_class        = ! $stylesheet ? 'unseen' : '';
$cssfile          = $util->get_sform_option( 1, 'settings', 'stylesheet_file', false );
$javascript       = $util->get_sform_option( 1, 'settings', 'javascript', false );
$css_notes_on     = __( 'Create a directory inside your active theme\'s directory, name it "simpleform", add your CSS stylesheet file, and name it "custom-style.css"', 'simpleform' );
$css_notes_off    = __( 'Keep unchecked if you want to use your personal CSS code and include it somewhere in your theme\'s code without using an additional file', 'simpleform' );
$css_notes        = 'false' === $cssfile ? $css_notes_off : $css_notes_on;
$js_notes_on      = __( 'Create a directory inside your active theme\'s directory, name it "simpleform", add your JavaScript file, and name it "custom-script.js"', 'simpleform' );
$js_notes_off     = __( 'Keep unchecked if you want to use your personal JavaScript code and include it somewhere in your theme\'s code without using an additional file', 'simpleform' );
$js_notes         = ! $javascript ? $js_notes_off : $js_notes_on;
$uninstall        = $util->get_sform_option( 1, 'settings', 'deletion_data', true );
$multiple_spaces  = $util->get_sform_option( $form, 'settings', 'multiple_spaces', false );
switch ( $out_error ) {
	case 'top':
		$error_notes = __( 'Display an error message above the form in case of one or more errors in the fields', 'simpleform' );
		/* translators: Used in place of %s in the string: "Please enter an error message to be displayed on %s of the form" */
		$error_position  = __( 'top', 'simpleform' );
		$out_error_class = '';
		break;
	case 'bottom':
		$error_notes = __( 'Display an error message below the form in case of one or more errors in the fields', 'simpleform' );
		/* translators: Used in place of %s in the string: "Please enter an error message to be displayed on %s of the form" */
		$error_position  = __( 'bottom', 'simpleform' );
		$out_error_class = '';
		break;
	default:
		$error_notes     = '&nbsp;';
		$error_position  = '';
		$out_error_class = ' removed';
}

$out_class          = 'none' === $out_error ? 'removed' : '';
$empty_fields       = strval( $util->get_sform_option( $form, 'settings', 'empty_fields', __( 'There were some errors that need to be fixed', 'simpleform' ) ) );
$characters_length  = $util->get_sform_option( $form, 'settings', 'characters_length', false );
$chars_length_on    = __( 'Keep unchecked if you want to use a generic error message without showing the minimum number of required characters', 'simpleform' );
$chars_length_off   = __( 'Keep checked if you want to show the minimum number of required characters and you want to make sure that\'s exactly the number you set for that specific field', 'simpleform' );
$chars_notes        = $characters_length ? $chars_length_on : $chars_length_off;
$name_field         = $util->get_sform_option( $form, 'attributes', 'name_field', 'visible' );
$required_name      = $util->get_sform_option( $form, 'attributes', 'name_requirement', 'required' );
$name_length        = intval( $util->get_sform_option( $form, 'attributes', 'name_minlength', 2 ) );
$empty_name_class   = 'hidden' === $name_field || 'optional' === $required_name ? 'unseen' : '';
$empty_name         = $util->get_sform_option( $form, 'settings', 'empty_name', __( 'Please provide your name', 'simpleform' ) );
$name_tr_class      = 'hidden' === $name_field ? ' hidden' : '';
$numeric_name_class = 'hidden' === $name_field || 0 === $name_length || ! $characters_length ? 'unseen' : '';
/* translators: %d: minimum length of the name */
$name_numeric_error     = strval( $util->get_sform_option( $form, 'settings', 'numeric_name', sprintf( __( 'Please enter a name at least %d characters long', 'simpleform' ), $name_length ) ) );
$generic_name_class     = 'hidden' === $name_field || $characters_length ? 'unseen' : '';
$name_generic_error     = strval( $util->get_sform_option( $form, 'settings', 'generic_name', __( 'Please type your full name', 'simpleform' ) ) );
$name_class             = 'hidden' === $name_field ? 'unseen' : '';
$invalid_name           = $util->get_sform_option( $form, 'settings', 'invalid_name', __( 'The name contains invalid characters', 'simpleform' ) );
$name_error             = $util->get_sform_option( $form, 'settings', 'name_error', __( 'Error occurred validating the name', 'simpleform' ) );
$lastname_field         = $util->get_sform_option( $form, 'attributes', 'lastname_field', 'hidden' );
$required_lastname      = $util->get_sform_option( $form, 'attributes', 'lastname_requirement', 'optional' );
$lastname_length        = intval( $util->get_sform_option( $form, 'attributes', 'lastname_minlength', 2 ) );
$empty_lastname_class   = 'hidden' === $lastname_field || 'optional' === $required_lastname ? 'unseen' : '';
$empty_lastname         = $util->get_sform_option( $form, 'settings', 'empty_lastname', __( 'Please provide your last name', 'simpleform' ) );
$lastname_tr_class      = 'hidden' === $lastname_field ? ' hidden' : '';
$numeric_lastname_class = 'hidden' === $lastname_field || 0 === $lastname_length || ! $characters_length ? 'unseen' : '';
/* translators: %d: minimum length of the last name */
$lastname_numeric_error = strval( $util->get_sform_option( $form, 'settings', 'numeric_lastname', sprintf( __( 'Please enter a last name at least %d characters long', 'simpleform' ), $lastname_length ) ) );
$generic_lastname_class = 'hidden' === $lastname_field || $characters_length ? 'unseen' : '';
$lastname_generic_error = strval( $util->get_sform_option( $form, 'settings', 'generic_lastname', __( 'Please type your full last name', 'simpleform' ) ) );
$lastname_class         = 'hidden' === $lastname_field ? 'unseen' : '';
$invalid_lastname       = $util->get_sform_option( $form, 'settings', 'invalid_lastname', __( 'The last name contains invalid characters', 'simpleform' ) );
$lastname_error         = $util->get_sform_option( $form, 'settings', 'lastname_error', __( 'Error occurred validating the last name', 'simpleform' ) );
$email_field            = $util->get_sform_option( $form, 'attributes', 'email_field', 'visible' );
$required_email         = $util->get_sform_option( $form, 'attributes', 'email_requirement', 'required' );
$empty_email_class      = 'hidden' === $email_field || 'optional' === $required_email ? 'unseen' : '';
$empty_email            = $util->get_sform_option( $form, 'settings', 'empty_email', __( 'Please provide your email address', 'simpleform' ) );
$email_class            = 'hidden' === $email_field ? 'unseen' : '';
$invalid_email          = $util->get_sform_option( $form, 'settings', 'invalid_email', __( 'Please enter a valid email address', 'simpleform' ) );
$email_error            = $util->get_sform_option( $form, 'settings', 'email_error', __( 'Error occurred validating the email', 'simpleform' ) );
$phone_field            = $util->get_sform_option( $form, 'attributes', 'phone_field', 'hidden' );
$required_phone         = $util->get_sform_option( $form, 'attributes', 'phone_requirement', 'optional' );
$empty_phone_class      = 'hidden' === $phone_field || 'optional' === $required_phone ? 'unseen' : '';
$empty_phone            = $util->get_sform_option( $form, 'settings', 'empty_phone', __( 'Please provide your phone number', 'simpleform' ) );
$phone_class            = 'hidden' === $phone_field ? 'unseen' : '';
$invalid_phone          = $util->get_sform_option( $form, 'settings', 'invalid_phone', __( 'The phone number contains invalid characters', 'simpleform' ) );
$phone_error            = $util->get_sform_option( $form, 'settings', 'phone_error', __( 'Error occurred validating the phone number', 'simpleform' ) );
$website_field          = $util->get_sform_option( $form, 'attributes', 'website_field', 'hidden' );
$required_website       = $util->get_sform_option( $form, 'attributes', 'website_requirement', 'optional' );
$website_class          = 'hidden' === $website_field ? 'unseen' : '';
$invalid_website        = $util->get_sform_option( $form, 'settings', 'invalid_website', __( 'Please enter a valid URL', 'simpleform' ) );
$website_error          = $util->get_sform_option( $form, 'settings', 'website_error', __( 'Error occurred validating the URL', 'simpleform' ) );
$subject_field          = $util->get_sform_option( $form, 'attributes', 'subject_field', 'visible' );
$required_subject       = $util->get_sform_option( $form, 'attributes', 'subject_requirement', 'required' );
$empty_subject_class    = 'hidden' === $subject_field || 'optional' === $required_subject ? 'unseen' : '';
$empty_subject          = $util->get_sform_option( $form, 'settings', 'empty_subject', __( 'Please enter the request subject', 'simpleform' ) );
$subject_length         = intval( $util->get_sform_option( $form, 'attributes', 'subject_minlength', 5 ) );
$numeric_subject_class  = 'hidden' === $subject_field || 0 === $subject_length || ! $characters_length ? 'unseen' : '';
$subject_tr_class       = 'hidden' === $subject_field ? ' hidden' : '';
/* translators: %d: minimum length of the subject */
$subject_numeric_error = strval( $util->get_sform_option( $form, 'settings', 'numeric_subject', sprintf( __( 'Please enter a subject at least %d characters long', 'simpleform' ), $subject_length ) ) );
$generic_subject_class = 'hidden' === $subject_field || $characters_length ? 'unseen' : '';
$subject_generic_error = strval( $util->get_sform_option( $form, 'settings', 'generic_subject', __( 'Please type a short and specific subject', 'simpleform' ) ) );
$subject_class         = 'hidden' === $subject_field ? 'unseen' : '';
$invalid_subject       = $util->get_sform_option( $form, 'settings', 'invalid_subject', __( 'Enter only alphanumeric characters and punctuation marks', 'simpleform' ) );
$subject_error         = $util->get_sform_option( $form, 'settings', 'subject_error', __( 'Error occurred validating the subject', 'simpleform' ) );
$empty_message         = $util->get_sform_option( $form, 'settings', 'empty_message', __( 'Please enter your message', 'simpleform' ) );
$numeric_message_class = ! $characters_length ? 'unseen' : '';
$message_length        = intval( $util->get_sform_option( $form, 'attributes', 'message_minlength', 10 ) );
/* translators: %d: minimum length of the message */
$message_numeric_error    = strval( $util->get_sform_option( $form, 'settings', 'numeric_message', sprintf( __( 'Please enter a message at least %d characters long', 'simpleform' ), $message_length ) ) );
$generic_message_class    = $characters_length ? 'unseen' : '';
$message_generic_error    = strval( $util->get_sform_option( $form, 'settings', 'generic_message', __( 'Please type a clearer message so we can respond appropriately', 'simpleform' ) ) );
$captcha_field            = $util->get_sform_option( $form, 'attributes', 'captcha_field', 'hidden' );
$consent_field            = $util->get_sform_option( $form, 'attributes', 'consent_field', 'visible' );
$invalid_message_position = 'none' === $out_error && 'hidden' === $captcha_field && 'hidden' === $consent_field ? 'last' : '';
$invalid_message          = $util->get_sform_option( $form, 'settings', 'invalid_message', __( 'Enter only alphanumeric characters and punctuation marks', 'simpleform' ) );
$message_position         = 'hidden' === $captcha_field && 'hidden' === $consent_field ? 'last' : '';
$message_error            = $util->get_sform_option( $form, 'settings', 'message_error', __( 'Error occurred validating the message', 'simpleform' ) );
$consent_class            = 'hidden' === $consent_field ? 'unseen' : '';
$consent_position         = 'hidden' === $captcha_field ? 'last' : '';
$consent_error            = $util->get_sform_option( $form, 'settings', 'consent_error', __( 'Please accept our privacy policy before submitting form', 'simpleform' ) );
$captcha_class            = 'hidden' === $captcha_field ? 'unseen' : '';
$empty_captcha            = $util->get_sform_option( $form, 'settings', 'empty_captcha', __( 'Please enter an answer', 'simpleform' ) );
$captcha_position         = 'none' === $out_error ? 'last' : '';
$invalid_captcha          = $util->get_sform_option( $form, 'settings', 'invalid_captcha', __( 'Please enter a valid captcha value', 'simpleform' ) );
$captcha_error            = $util->get_sform_option( $form, 'settings', 'captcha_error', __( 'Error occurred validating the captcha', 'simpleform' ) );
$honeypot_error           = $util->get_sform_option( 1, 'settings', 'honeypot_error', __( 'Failed honeypot validation', 'simpleform' ) );
$duplicate                = $util->get_sform_option( 1, 'settings', 'duplicate', true );
$duplicate_class          = ! $duplicate ? 'unseen' : '';
$duplicate_error          = $util->get_sform_option( 1, 'settings', 'duplicate_error', __( 'The form has already been submitted. Thanks!', 'simpleform' ) );
$ajax_error               = $util->get_sform_option( $form, 'settings', 'ajax_error', __( 'Error occurred during AJAX request. Please contact support!', 'simpleform' ) );
$server_error             = $util->get_sform_option( 1, 'settings', 'server_error', __( 'Error occurred during processing data. Please try again!', 'simpleform' ) );
$success_action           = $util->get_sform_option( $form, 'settings', 'success_action', 'message' );
$success_message_class    = 'message' !== $success_action ? 'unseen' : '';
$confirmation_img         = plugin_dir_url( dirname( __DIR__ ) ) . 'public/img/confirmation.png';
$thank_string1            = __( 'We have received your request!', 'simpleform' );
$thank_string2            = __( 'Your message will be reviewed soon, and we\'ll get back to you as quickly as possible. ', 'simpleform' );
$thank_you_message        = $util->get_sform_option( $form, 'settings', 'success_message', '<div class="form confirmation" tabindex="-1"><h4>' . $thank_string1 . '</h4><br>' . $thank_string2 . '</br><img src="' . $confirmation_img . '" alt="message received"></div>' );
$success_redirect_class   = 'redirect' !== $success_action ? 'unseen' : '';
$confirmation_page        = intval( $util->get_sform_option( $form, 'settings', 'confirmation_page', 0 ) );
$edit_post_link           = '<strong><a href="' . get_edit_post_link( $confirmation_page ) . '" target="_blank" class="publish-link">' . __( 'Publish now', 'simpleform' ) . '</a></strong>';
/* translators: Used in place of %1$s in the string: "%1$s or %2$s the page content" */
$edit = __( 'Edit', 'simpleform' );
/* translators: Used in place of %2$s in the string: "%1$s or %2$s the page content" */
$view = __( 'view', 'simpleform' );
/* translators: 1: Edit, 2: view. */
$post_url   = 0 !== $confirmation_page ? sprintf( __( '%1$s or %2$s the page content', 'simpleform' ), '<strong><a href="' . get_edit_post_link( $confirmation_page ) . '" target="_blank" style="text-decoration: none;">' . $edit . '</a></strong>', '<strong><a href="' . get_page_link( $confirmation_page ) . '" target="_blank" style="text-decoration: none;">' . $view . '</a></strong>' ) : '&nbsp;';
$form_pages = get_pages(
	array(
		'sort_column' => 'post_title',
		'sort_order'  => 'ASC',
		'post_type'   => 'page',
		'post_status' => array(
			'publish',
			'draft',
		),
	)
);
$options    = '<option value="">' . __( 'Select the page to which the user is redirected when the form is sent', 'simpleform' ) . '</option>';
if ( $form_pages ) {
	// $form_pages is array of object.
	foreach ( $form_pages as $form_page ) {
		if ( is_object( $form_page ) ) {
			$options .= '<option value="' . $form_page->ID . '" tag="' . $form_page->post_status . '" ' . selected( $confirmation_page, $form_page->ID, false ) . '>' . $form_page->post_title . '</option>';
		}
	}
} else {
	$options .= '<option value="nopage" disabled="disabled">' . __( 'No page found', 'simpleform' ) . '</option>';
}
$pages_selector = $form_pages ? '<select name="redirect_page" id="redirect_page" class="sform">' . $options . '</select>' : '';
$post_status    = '' !== $confirmation_page && 'draft' === get_post_status( $confirmation_page ) ? __( 'Page in draft status not yet published', 'simpleform' ) . '&nbsp;-&nbsp;' . $edit_post_link : $post_url;
$smtp_button    = 1 === $form ? '<span class="notice-toggle"><span class="dashicons dashicons-editor-help icon-button ' . esc_attr( $color ) . '"></span><span id="smpt-warnings" class="text wp-core-ui button ' . esc_attr( $color ) . '">' . __( 'Show Configuration Warnings', 'simpleform' ) . '</span></span>' : '';
$username       = __( 'SMTP Username', 'simpleform' );
$password       = __( 'SMTP Password', 'simpleform' );
/* translators: 1: SMTP Username, 2: SMTP Password, 3: wp-config.php */
$smtp_credentials        = sprintf( __( 'The SMPT login credentials are stored in your website database. We highly recommend that you set up your login credentials in your WordPress configuration file for improved security. To do this, leave the %1$s field and the %2$s field blank and add the lines below to your %3$s file:', 'simpleform' ), '<i>' . $username . '</i>', '<i>' . $password . '</i>', '<code>wp-config.php</code>' );
$smtp                    = $util->get_sform_option( 1, 'settings', 'server_smtp', false );
$smtp_position           = ! $smtp ? 'last' : '';
$smtp_notes              = $smtp ? __( 'Uncheck if you want to use a dedicated plugin to take care of outgoing email', 'simpleform' ) : '&nbsp;';
$smtp_tr_class           = ! $smtp ? 'unseen' : '';
$smtp_host               = $util->get_sform_option( 1, 'settings', 'smtp_host', '' );
$smtp_encryption         = $util->get_sform_option( 1, 'settings', 'smtp_encryption', 'ssl' );
$smtp_port               = $util->get_sform_option( 1, 'settings', 'smtp_port', '465' );
$smtp_authentication     = $util->get_sform_option( 1, 'settings', 'smtp_authentication', true );
$authentication_position = ! $smtp_authentication ? 'last' : '';
$authentication_class    = ! $smtp || ! $smtp_authentication ? 'unseen' : '';
$username_placeholder    = defined( 'SFORM_SMTP_USERNAME' ) && ! empty( trim( SFORM_SMTP_USERNAME ) ) ? trim( SFORM_SMTP_USERNAME ) : __( 'Enter the username for SMTP authentication', 'simpleform' );
$smtp_username           = $util->get_sform_option( 1, 'settings', 'smtp_username', '' );
$password_placeholder    = defined( 'SFORM_SMTP_PASSWORD' ) && ! empty( trim( SFORM_SMTP_PASSWORD ) ) ? '•••••••••••••••' : __( 'Enter the password for SMTP authentication', 'simpleform' );
$smtp_password           = $util->get_sform_option( 1, 'settings', 'smtp_password', '' );
$notification            = $util->get_sform_option( $form, 'settings', 'notification', true );
$alert_position          = ! $notification ? 'last' : '';
$alert_tr_class          = ! $notification ? 'unseen' : '';
$notification_recipient  = $util->get_sform_option( $form, 'settings', 'notification_recipient', strval( get_option( 'admin_email' ) ) );
$bcc                     = $util->get_sform_option( $form, 'settings', 'bcc', '' );
$notification_email      = $util->get_sform_option( $form, 'settings', 'notification_email', strval( get_option( 'admin_email' ) ) );
$notification_name       = $util->get_sform_option( $form, 'settings', 'notification_name', 'requester' );
$alert_name_class        = ! $notification || 'custom' !== $notification_name ? 'unseen' : '';
$custom_sender           = $util->get_sform_option( $form, 'settings', 'custom_sender', get_bloginfo( 'name' ) );
$notification_subject    = $util->get_sform_option( $form, 'settings', 'notification_subject', 'request' );
$custom_subject          = $util->get_sform_option( $form, 'settings', 'custom_subject', __( 'New Contact Request', 'simpleform' ) );
$notification_reply      = $util->get_sform_option( $form, 'settings', 'notification_reply', true );
$submission_number       = $util->get_sform_option( $form, 'settings', 'submission_number', 'visible' );
$auto                    = $util->get_sform_option( $form, 'settings', 'autoresponder', false );
$autoreply_position      = ! $auto ? 'last' : '';
$autoreply_tr_class      = ! $auto ? 'unseen' : '';
$auto_email              = strval( $util->get_sform_option( $form, 'settings', 'autoresponder_email', strval( get_option( 'admin_email' ) ) ) );
$auto_name               = $util->get_sform_option( $form, 'settings', 'autoresponder_name', get_bloginfo( 'name' ) );
$auto_subject            = $util->get_sform_option( $form, 'settings', 'autoresponder_subject', __( 'Your request has been received. Thanks!', 'simpleform' ) );
$code_name               = '[name]';
/* translators: %s: name of the person who filled out the form */
$default_autoreply = sprintf( __( 'Hi %s', 'simpleform' ), $code_name ) . ',<p>' . __( 'We have received your request. It will be reviewed soon and we\'ll get back to you as quickly as possible. ', 'simpleform' ) . '<p>' . __( 'Thanks, ', 'simpleform' ) . '<br>' . __( 'The Support Team', 'simpleform' );
$auto_message      = $util->get_sform_option( $form, 'settings', 'autoresponder_message', $default_autoreply );
$auto_reply        = $util->get_sform_option( $form, 'settings', 'autoresponder_reply', $auto_email );
$allowed_tags      = $util->sform_allowed_tags();

// Page wrap: opening tag.
$settings_page = '<div id="sform-wrap" class="sform ' . esc_attr( $wrap_class ) . '">';

// Admin notice.
$settings_page .= '<div id="new-release" class="' . esc_attr( $notice_class ) . '">' . wp_kses_post( apply_filters( 'sform_update', $notice ) ) . '&nbsp;</div>';

// Page heading.
$settings_page .= '<div class="full-width-bar ' . esc_attr( $color ) . '"><h1 class="title ' . esc_attr( $color ) . '"><span class="dashicons dashicons-admin-settings responsive"></span>' . __( 'Settings', 'simpleform' ) . $forms_selector . '</h1></div>';

if ( in_array( $form, $forms, true ) ) {

	// Description page.
	$settings_page .= '<div id="page-description">' . $new_notice . '<p>' . __( 'Customize messages and whatever settings you want to better match your needs:', 'simpleform' ) . '</p></div>';

	// Tabs.
	$settings_page .= '<div id="settings-tabs"><a class="nav-tab nav-tab-active" id="general">' . __( 'General', 'simpleform' ) . '</a><a class="nav-tab" id="messages">' . __( 'Validation', 'simpleform' ) . '</a><a class="nav-tab" id="email">' . __( 'Notifications', 'simpleform' ) . '</a><a class="nav-tab" id="spam">' . __( 'Anti-Spam', 'simpleform' ) . '</a>' . apply_filters( 'sform_itab', $extra_option ) . '<a class="form-button last ' . esc_attr( $color ) . '" href="' . admin_url( 'admin.php?page=sform-editor' ) . $editor_arg . '" target="_blank"><span><span class="dashicons dashicons-editor-table"></span><span class="text">' . __( 'Editor', 'simpleform' ) . '</span></span></a><a class="form-button form-page ' . esc_attr( $color ) . '" href="' . admin_url( 'admin.php?page=sform-form' ) . esc_attr( $form_arg ) . '" target="_blank"><span><span class="dashicons dashicons-tag"></span><span class="text">' . __( 'Specifics', 'simpleform' ) . '</span></span></a></div>';

	// Form opening tag.
	$settings_page .= '<form id="settings" method="post" class="' . esc_attr( $color ) . '">';

	// Current form ID.
	$settings_page .= '<input type="hidden" id="form_id" name="form_id" value="' . $form . '">';

	// General Tab opening.
	$settings_page .= '<div id="tab-general" class="navtab">';

	// Management preferences options.
	$settings_page .= '<h2 id="h2-admin" class="options-heading"><span class="heading" data-section="admin">' . __( 'Management Preferences', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 admin"></span></span>' . $main_page_button . '</h2><div class="section admin"><table class="form-table admin"><tbody>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Admin Notices', 'simpleform' ) . '</span></th><td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="admin_notices" id="admin_notices" class="sform-switch" value="' . $admin_notices . '" ' . checked( $admin_notices, true, false ) . $disabled_option . '><span></span></label><label for="admin_notices" class="switch-label ' . esc_attr( $disabled_class ) . '">' . __( 'Never display notices on the SimpleForm related admin pages', 'simpleform' ) . '</label></div><p class="description">' . __( 'Admin notices may include, but are not limited to, reminders, update notifications, calls to action, and links to documentation', 'simpleform' ) . '</p></td></tr>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Front-end Admin Notice', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="frontend_notice" id="frontend_notice" class="sform-switch" value="' . $frontend_notice . '" ' . checked( $frontend_notice, true, false ) . $disabled_option . '><span></span></label><label for="frontend_notice" class="switch-label ' . esc_attr( $disabled_class ) . '">' . __( 'Display an admin notice when the form cannot be seen by the admin when visiting the website\'s front end', 'simpleform' ) . '</label></div></td></tr>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Admin Color Scheme', 'simpleform' ) . '</span></th><td class="last select"><select name="admin_color" id="admin_color" class="sform" ' . $disabled_option . '><option value="default" ' . selected( $color, 'default', false ) . '>' . __( 'Default', 'simpleform' ) . '</option><option value="light" ' . selected( $color, 'light', false ) . '>' . __( 'Light', 'simpleform' ) . '</option><option value="modern" ' . selected( $color, 'modern', false ) . '>' . __( 'Modern', 'simpleform' ) . '</option><option value="blue" ' . selected( $color, 'blue', false ) . '>' . __( 'Blue', 'simpleform' ) . '</option><option value="coffee" ' . selected( $color, 'coffee', false ) . '>' . __( 'Coffee', 'simpleform' ) . '</option><option value="ectoplasm" ' . selected( $color, 'ectoplasm', false ) . '>' . __( 'Ectoplasm', 'simpleform' ) . '</option><option value="midnight" ' . selected( $color, 'midnight', false ) . '>' . __( 'Midnight', 'simpleform' ) . '</option><option value="ocean" ' . selected( $color, 'ocean', false ) . '>' . __( 'Ocean', 'simpleform' ) . '</option><option value="sunrise" ' . selected( $color, 'sunrise', false ) . '>' . __( 'Sunrise', 'simpleform' ) . '</option><option value="foggy" ' . selected( $color, 'foggy', false ) . '>' . __( 'Foggy', 'simpleform' ) . '</option><option value="polar" ' . selected( $color, 'polar', false ) . '>' . __( 'Polar', 'simpleform' ) . '</option></select></td></tr></tbody></table></div>';

	// SimpleForm Contact Form Submissions options.
	$settings_page .= apply_filters( 'submissions_settings_fields', $extra_option, $form );

	// Form submission options.
	$settings_page .= '<h2 id="h2-submission" class="options-heading"><span class="heading" data-section="submission">' . __( 'Form Submission', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 submission"></span></span></h2><div class="section submission"><table class="form-table submission"><tbody>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'AJAX Submission', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="ajax_submission" id="ajax_submission" class="sform-switch" value="' . $ajax . '" ' . checked( $ajax, true, false ) . '><span></span></label><label for="ajax_submission" class="switch-label">' . __( 'Perform form submission via AJAX instead of a standard HTML request', 'simpleform' ) . '</label></div></td></tr>';

	$settings_page .= '<tr class="trajax ' . $ajax_class . '"><th class="option"><span>' . __( 'Loading Spinner', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="spinner" id="spinner" class="sform-switch" value="' . $spinner . '" ' . checked( $spinner, true, false ) . '><span></span></label><label for="spinner" class="switch-label">' . __( 'Use a CSS animation to let users know that their request is being processed', 'simpleform' ) . '</label></div></td></tr>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'HTML5 Browser Validation', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="html5" id="html5" class="sform-switch" value="' . $html5_validation . '" ' . checked( $html5_validation, true, false ) . '><span></span></label><label for="html5" class="switch-label">' . __( 'Disable the browser default form validation', 'simpleform' ) . '</label></div></td></tr>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Focus on Form Errors', 'simpleform' ) . '</span></th><td class="last radio"><fieldset><label for="field" class="radio"><input id="field" type="radio" name="focus" value="field" ' . checked( $focus, 'field', false ) . '>' . __( 'Set focus to first invalid field', 'simpleform' ) . '</label><label id="focusout" for="alert" class="radio"><input id="alert" type="radio" name="focus" value="alert" ' . checked( $focus, 'alert', false ) . '>' . $focus_out . '</label></fieldset></td></tr></tbody></table></div>';

	// Form style options.
	$settings_page .= '<h2 id="h2-formstyle" class="options-heading"><span class="heading" data-section="formstyle">' . __( 'Form Style', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 formstyle"></span></span></h2><div class="section formstyle"><table class="form-table formstyle"><tbody>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Style', 'simpleform' ) . '</span></th><td class="last select notes"><select name="form_style" id="form_style" class="sform"><option value="default" ' . selected( $form_style, 'default', false ) . '>' . __( 'Default', 'simpleform' ) . '</option><option value="basic" ' . selected( $form_style, 'basic', false ) . '>' . __( 'Basic', 'simpleform' ) . '</option><option value="rounded" ' . selected( $form_style, 'rounded', false ) . '>' . __( 'Rounded', 'simpleform' ) . '</option><option value="minimal" ' . selected( $form_style, 'minimal', false ) . '>' . __( 'Minimal', 'simpleform' ) . '</option><option value="transparent" ' . selected( $form_style, 'transparent', false ) . '>' . __( 'Transparent', 'simpleform' ) . '</option><option value="highlighted" ' . selected( $form_style, 'highlighted', false ) . '>' . __( 'Highlighted', 'simpleform' ) . '</option><option value="customized" ' . selected( $form_style, 'customized', false ) . '>' . __( 'Customized', 'simpleform' ) . '</option></select><p id="template-notice" class="description">' . $style_notes . '</p></td></tr></tbody></table></div>';

	// Customization options.
	$settings_page .= '<h2 id="h2-custom" class="options-heading"><span class="heading" data-section="custom">' . __( 'Customization', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 custom"></span></span>' . $main_page_button . '</h2><div class="section custom"><table class="form-table custom"><tbody>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Form CSS Stylesheet', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="stylesheet" id="stylesheet" class="sform-switch" value="' . $stylesheet . '" ' . checked( $stylesheet, true, false ) . $disabled_option . '><span></span></label><label for="stylesheet" class="switch-label ' . $disabled_class . '">' . __( 'Disable the SimpleForm CSS stylesheet and use your own CSS stylesheet', 'simpleform' ) . '</label></div></td></tr>';

	$settings_page .= '<tr class="trstylesheet ' . $css_class . '"><th class="option"><span>' . __( 'CSS Stylesheet File', 'simpleform' ) . '</span></th><td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="stylesheet_file" id="stylesheet_file" class="sform-switch" value="' . $cssfile . '" ' . checked( $cssfile, true, false ) . $disabled_option . '><span></span></label><label for="stylesheet_file" class="switch-label ' . $disabled_class . '">' . __( 'Include custom CSS code in a separate file', 'simpleform' ) . '</label></div><p id="stylesheet-description" class="description">' . $css_notes . '</p></td></tr>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Custom JavaScript Code', 'simpleform' ) . '</span></th><td class="checkbox-switch last notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="javascript" id="javascript" class="sform-switch" value="' . $javascript . '" ' . checked( $javascript, true, false ) . $disabled_option . '><span></span></label><label for="javascript" class="switch-label ' . $disabled_class . '">' . __( 'Add your custom JavaScript code to your form', 'simpleform' ) . '</label></div><p id="javascript-description" class="description">' . $js_notes . '</p></td></tr></tbody></table></div>';

	// Uninstall options.
	$settings_page .= '<h2 id="h2-uninstall" class="options-heading"><span class="heading" data-section="uninstall">' . __( 'Uninstall', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 uninstall"></span></span>' . $main_page_button . '</h2><div class="section uninstall"><table class="form-table uninstall"><tbody>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Data Deletion', 'simpleform' ) . '</span></th><td class="checkbox-switch last"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="deletion_data" id="deletion_data" class="sform-switch" value="' . $uninstall . '" ' . checked( $uninstall, true, false ) . $disabled_option . '><span></span></label><label for="deletion_data" class="switch-label ' . $disabled_class . '">' . __( 'Delete all data and settings when the plugin is uninstalled', 'simpleform' ) . '</label></div></td></tr></tbody></table></div>';

	// General Tab closing.
	$settings_page .= '</div>';

	// Validation Tab opening.
	$settings_page .= '<div id="tab-messages" class="navtab unseen">';

	// Fields validation rules options.
	$settings_page .= '<h2 id="h2-rules" class="options-heading"><span class="heading" data-section="rules">' . __( 'Fields Validation Rules', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 rules"></span></span></h2><div class="section rules"><table class="form-table rules"><tbody>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Multiple spaces', 'simpleform' ) . '</span></th><td class="checkbox-switch last"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="multiple_spaces" id="multiple_spaces" class="sform-switch" value="' . $multiple_spaces . '" ' . checked( $multiple_spaces, true, false ) . '><span></span></label><label for="multiple_spaces" class="switch-label">' . __( 'Prevent the user from entering multiple white spaces in the fields', 'simpleform' ) . '</label></div></td></tr></tbody></table></div>';

	// Fields error messages options.
	$settings_page .= '<h2 id="h2-fields" class="options-heading"><span class="heading" data-section="fields">' . __( 'Fields Error Messages', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 fields"></span></span></h2><div class="section fields"><table class="form-table fields"><tbody>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Error Message Outside', 'simpleform' ) . '</span></th><td class="select notes"><select name="outside_error" id="outside_error" class="sform"><option value="top" ' . selected( $out_error, 'top', false ) . '>' . __( 'Above the form', 'simpleform' ) . '</option><option value="bottom" ' . selected( $out_error, 'bottom', false ) . '>' . __( 'Below the form', 'simpleform' ) . '</option><option value="none" ' . selected( $out_error, 'none', false ) . '>' . __( 'Do not display', 'simpleform' ) . '</option></select><p id="outside-notice" class="description">' . $error_notes . '</p></td></tr>';

	$settings_page .= '<tr class="trout ' . $out_class . '"><th class="option"><span>' . __( 'Multiple Fields Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="empty_fields" name="empty_fields" class="sform out" placeholder="' . esc_attr__( 'Please enter an error message to be displayed on bottom of the form in case of multiple empty fields', 'simpleform' ) . '" value="' . esc_attr( $empty_fields ) . '" \></td></tr>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Fields Length Error', 'simpleform' ) . '</span></th><td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="characters_length" id="characters_length" class="sform-switch" value="' . $characters_length . '" ' . checked( $characters_length, true, false ) . '><span></span></label><label for="characters_length" class="switch-label">' . __( 'Include the minimum number of required characters in length error message', 'simpleform' ) . '</label></div><p id="characters-description" class="description">' . $chars_notes . '</p></td></tr>';

	$settings_page .= '<tr class="' . $empty_name_class . '"><th class="option"><span>' . __( 'Empty Name Field Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="empty_name" name="empty_name" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the name field is empty', 'simpleform' ) . '" value="' . $empty_name . '" \></td></tr>';

	$settings_page .= '<tr class="trnumeric ' . $numeric_name_class . $name_tr_class . '"><th class="option"><span>' . __( 'Name Numeric Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="numeric_name" name="numeric_name" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the name is not long enough', 'simpleform' ) . '" value="' . $name_numeric_error . '" \></td></tr>';

	$settings_page .= '<tr class="trgeneric ' . $generic_name_class . $name_tr_class . '"><th class="option"><span>' . __( 'Name Generic Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="generic_name" name="generic_name" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the name is not long enough', 'simpleform' ) . '" value="' . $name_generic_error . '" \></td></tr>';

	$settings_page .= '<tr class="' . $name_class . '"><th class="option"><span>' . __( 'Invalid Name Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="invalid_name" name="invalid_name" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field in case of an invalid name', 'simpleform' ) . '" value="' . $invalid_name . '" \></td></tr>';

	$settings_page .= '<tr class="trout ' . $name_class . $out_error_class . '"><th class="option"><span>' . __( 'Name Field Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="name_error" name="name_error" class="sform out" placeholder="' . esc_attr__( 'Please enter an error message to be displayed on bottom of the form in case of an error in the name field', 'simpleform' ) . '" value="' . $name_error . '" \></td></tr>';

	$settings_page .= '<tr class="' . $empty_lastname_class . '"><th class="option"><span>' . __( 'Empty Last Name Field Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="empty_lastname" name="empty_lastname" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the last name field is empty', 'simpleform' ) . '" value="' . $empty_lastname . '" \></td></tr>';

	$settings_page .= '<tr class="trnumeric ' . $numeric_lastname_class . $lastname_tr_class . '"><th class="option"><span>' . __( 'Last Name Numeric Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="numeric_lastname" name="numeric_lastname" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the last name is not long enough', 'simpleform' ) . '" value="' . $lastname_numeric_error . '" \></td></tr>';

	$settings_page .= '<tr class="trgeneric ' . $generic_lastname_class . $lastname_tr_class . '"><th class="option"><span>' . __( 'Last Name Generic Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="generic_lastname" name="generic_lastname" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the last name is not long enough', 'simpleform' ) . '" value="' . $lastname_generic_error . '" \></td></tr>';

	$settings_page .= '<tr class="' . $lastname_class . '"><th class="option"><span>' . __( 'Invalid Last Name Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="invalid_lastname" name="invalid_lastname" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field in case of an invalid last name', 'simpleform' ) . '" value="' . $invalid_lastname . '" \></td></tr>';

	$settings_page .= '<tr class="trout ' . $lastname_class . $out_error_class . '"><th class="option"><span>' . __( 'Last Name Field Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="lastname_error" name="lastname_error" class="sform out" placeholder="' . esc_attr__( 'Please enter an error message to be displayed on bottom of the form in case of an error in the last name field', 'simpleform' ) . '" value="' . $lastname_error . '" \></td></tr>';

	$settings_page .= '<tr class="' . $empty_email_class . '"><th class="option"><span>' . __( 'Empty Email Field Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="empty_email" name="empty_email" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the email field is empty', 'simpleform' ) . '" value="' . $empty_email . '" \></td></tr>';

	$settings_page .= '<tr class="' . $email_class . '"><th class="option"><span>' . __( 'Invalid Email Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="invalid_email" name="invalid_email" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field in case of an invalid email address', 'simpleform' ) . '" value="' . $invalid_email . '" \></td></tr>';

	$settings_page .= '<tr class="trout ' . $email_class . $out_error_class . '"><th class="option"><span>' . __( 'Email Field Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="email_error" name="email_error" class="sform out" placeholder="' . esc_attr__( 'Please enter an error message to be displayed on bottom of the form in case of an error in the email field', 'simpleform' ) . '" value="' . $email_error . '" \></td></tr>';

	$settings_page .= '<tr class="' . $empty_phone_class . '"><th class="option"><span>' . __( 'Empty Phone Field Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="empty_phone" name="empty_phone" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the phone field is empty', 'simpleform' ) . '" value="' . $empty_phone . '" \></td></tr>';

	$settings_page .= '<tr class="' . $phone_class . '"><th class="option"><span>' . __( 'Invalid Phone Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="invalid_phone" name="invalid_phone" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field in case of an invalid phone number', 'simpleform' ) . '" value="' . $invalid_phone . '" \></td></tr>';

	$settings_page .= '<tr class="trout ' . $phone_class . $out_error_class . '"><th class="option"><span>' . __( 'Phone Field Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="phone_error" name="phone_error" class="sform out" placeholder="' . esc_attr__( 'Please enter an error message to be displayed on bottom of the form in case of an error in the phone field', 'simpleform' ) . '" value="' . $phone_error . '" \></td></tr>';

	$settings_page .= '<tr class="' . $website_class . '"><th class="option"><span>' . __( 'Invalid Website Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="invalid_website" name="invalid_website" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field in case of an invalid website address', 'simpleform' ) . '" value="' . $invalid_website . '" \></td></tr>';

	$settings_page .= '<tr class="trout ' . $website_class . $out_error_class . '"><th class="option"><span>' . __( 'Website Field Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="website_error" name="website_error" class="sform out" placeholder="' . esc_attr__( 'Please enter an error message to be displayed on bottom of the form in case of an error in the website field', 'simpleform' ) . '" value="' . $website_error . '" \></td></tr>';

	$settings_page .= '<tr class="' . $empty_subject_class . '"><th class="option"><span>' . __( 'Empty Subject Field Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="empty_subject" name="empty_subject" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the subject field is empty', 'simpleform' ) . '" value="' . $empty_subject . '" \></td></tr>';

	$settings_page .= '<tr class="trnumeric ' . $numeric_subject_class . $subject_tr_class . '"><th class="option"><span>' . __( 'Subject Numeric Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="numeric_subject" name="numeric_subject" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the subject is not long enough', 'simpleform' ) . '" value="' . $subject_numeric_error . '" \></td></tr>';

	$settings_page .= '<tr class="trgeneric ' . $generic_subject_class . $subject_tr_class . '"><th class="option"><span>' . __( 'Subject Generic Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="generic_subject" name="generic_subject" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the subject is not long enough', 'simpleform' ) . '" value="' . $subject_generic_error . '" \></td></tr>';

	$settings_page .= '<tr class="' . $subject_class . '"><th class="option"><span>' . __( 'Invalid Subject Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="invalid_subject" name="invalid_subject" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field in case of an invalid subject', 'simpleform' ) . '" value="' . $invalid_subject . '" \></td></tr>';

	$settings_page .= '<tr class="trout ' . $subject_class . $out_error_class . '"><th class="option"><span>' . __( 'Subject Field Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="subject_error" name="subject_error" class="sform out" placeholder="' . esc_attr__( 'Please enter an error message to be displayed on bottom of the form in case of an error in the subject field', 'simpleform' ) . '" value="' . $subject_error . '" \></td></tr>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Empty Message Field Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="empty_message" name="empty_message" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the message field is empty', 'simpleform' ) . '" value="' . $empty_message . '" \></td></tr>';

	$settings_page .= '<tr class="trnumeric ' . $numeric_message_class . '"><th class="option"><span>' . __( 'Message Numeric Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="numeric_message" name="numeric_message" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the message is not long enough', 'simpleform' ) . '" value="' . $message_numeric_error . '" \></td></tr>';

	$settings_page .= '<tr class="trgeneric ' . $generic_message_class . '"><th class="option"><span>' . __( 'Message Generic Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="generic_message" name="generic_message" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field if the message is not long enough', 'simpleform' ) . '" value="' . $message_generic_error . '" \></td></tr>';

	$settings_page .= '<tr><th class="messagecell option"><span>' . __( 'Invalid Message Error', 'simpleform' ) . '</span></th><td class="messagecell text ' . $invalid_message_position . '"><input type="text" id="invalid_message" name="invalid_message" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field in case of an invalid message', 'simpleform' ) . '" value="' . $invalid_message . '" \></td></tr>';

	$settings_page .= '<tr class="trout ' . $out_error_class . '"><th class="option"><span>' . __( 'Message Field Error', 'simpleform' ) . '</span></th><td class="text ' . $message_position . '"><input type="text" id="message_error" name="message_error" class="sform out" placeholder="' . esc_attr__( 'Please enter an error message to be displayed on bottom of the form in case of an error in the message field', 'simpleform' ) . '" value="' . $message_error . '" \></td></tr>';

	$settings_page .= '<tr class="trout ' . $consent_class . $out_error_class . '"><th class="option"><span>' . __( 'Consent Field Error', 'simpleform' ) . '</span></th><td class="text ' . $consent_position . '"><input type="text" id="consent_error" name="consent_error" class="sform out" placeholder="' . esc_attr__( 'Please enter an error message to be displayed on bottom of the form in case the consent is not provided', 'simpleform' ) . '" value="' . $consent_error . '" \></td></tr>';

	$settings_page .= '<tr class="' . $captcha_class . '"><th class="option"><span>' . __( 'Empty Captcha Field Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="empty_captcha" name="empty_captcha" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field in case of an empty captcha field', 'simpleform' ) . '" value="' . $empty_captcha . '" \></td></tr>';

	$settings_page .= '<tr id="trcaptcha" class="' . $captcha_class . '"><th class="captchacell option"><span>' . __( 'Invalid Captcha Error', 'simpleform' ) . '</span></th><td class="captchacell text ' . $captcha_position . '"><input type="text" id="invalid_captcha" name="invalid_captcha" class="sform" placeholder="' . esc_attr__( 'Please enter an inline error message to be displayed below the field in case of invalid captcha value', 'simpleform' ) . '" value="' . $invalid_captcha . '" \></td></tr>';

	$settings_page .= '<tr class="trout ' . $captcha_class . $out_error_class . '"><th class="option"><span>' . __( 'Captcha Field Error', 'simpleform' ) . '</span></th><td class="text last"><input type="text" id="captcha_error" name="captcha_error" class="sform out" placeholder="' . esc_attr__( 'Please enter an error message to be displayed on bottom of the form in case of error in captcha field', 'simpleform' ) . '" value="' . $captcha_error . '" \></td></tr></tbody></table></div>';

	// Submission error messages options.
	$settings_page .= '<h2 id="h2-sending" class="options-heading"><span class="heading" data-section="sending">' . __( 'Submission Error Messages', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 sending"></span></span>' . $main_page_button . '</h2><div class="section sending"><table class="form-table sending"><tbody>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Honeypot Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="honeypot_error" name="honeypot_error" class="sform" placeholder="' . esc_attr__( 'Please enter an error message to be displayed in case a honeypot field is filled in', 'simpleform' ) . '" value="' . $honeypot_error . '"' . $disabled_option . ' \></td></tr>';

	$settings_page .= '<tr class="trduplicate ' . $duplicate_class . '"><th class="option"><span>' . __( 'Duplicate Submission Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="duplicate_error" name="duplicate_error" class="sform" placeholder="' . esc_attr__( 'Please enter an error message to be displayed in case of duplicate form submission', 'simpleform' ) . '" value="' . $duplicate_error . '"' . $disabled_option . ' \></td></tr>';

	// Akismet options.
	$settings_page .= apply_filters( 'akismet_validation_message', $extra_option, $form );

	// reCaptcha options.
	$settings_page .= apply_filters( 'recaptcha_validation_messages', $extra_option, $form );

	$settings_page .= '<tr class="trajax ' . $ajax_class . '"><th class="option"><span>' . __( 'AJAX Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="ajax_error" name="ajax_error" class="sform" placeholder="' . esc_attr__( 'Please enter an error message to be displayed when the AJAX request fails', 'simpleform' ) . '" value="' . $ajax_error . '"\></td></tr>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Server Error', 'simpleform' ) . '</span></th><td class="text"><input type="text" id="server_error" name="server_error" class="sform" placeholder="' . esc_attr__( 'Please enter an error message to be displayed in case an error occurs during data processing', 'simpleform' ) . '" value="' . $server_error . '"' . $disabled_option . ' \></td></tr></tbody></table></div>';

	// Success messages options.
	$settings_page .= '<h2 id="h2-success" class="options-heading"><span class="heading" data-section="success">' . __( 'Success Message', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 success"></span></span></h2><div class="section success"><table class="form-table success"><tbody>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Action After Submission', 'simpleform' ) . '</span></th><td class="radio"><fieldset><label for="confirmation-message" class="radio"><input type="radio" id="confirmation-message" name="success_action" value="message" ' . checked( $success_action, 'message', false ) . '>' . __( 'Display confirmation message', 'simpleform' ) . '</label><label for="success-redirect" class="radio"><input type="radio" id="success-redirect" name="success_action" value="redirect" ' . checked( $success_action, 'redirect', false ) . '>' . __( 'Redirect to confirmation page', 'simpleform' ) . '</label></fieldset></td></tr>';

	$settings_page .= '<tr class="trsuccessmessage ' . $success_message_class . '"><th class="option"><span>' . __( 'Confirmation Message', 'simpleform' ) . '</span></th><td class="textarea"><textarea name="success_message" id="success_message" class="sform" placeholder="' . esc_attr__( 'Please enter a thank you message when the form is submitted', 'simpleform' ) . '" >' . $thank_you_message . '</textarea><p class="description">' . __( 'The HTML tags for formatting message are allowed', 'simpleform' ) . '</p></td></tr>';

	$settings_page .= '<tr class="trsuccessredirect ' . $success_redirect_class . '"><th class="option"><span>' . __( 'Confirmation Page', 'simpleform' ) . '</span></th><td class="last select notes">' . $pages_selector . '<p id="post-status" class="description">' . $post_status . '</p></td></tr></tbody></table></div>';

	// Validation Tab closing.
	$settings_page .= '</div>';

	// Notifications Tab opening.
	$settings_page .= '<div id="tab-email" class="navtab unseen">';

	// SMTP configuration options.
	$settings_page .= '<h2 id="h2-smtp" class="options-heading"><span class="heading" data-section="smtp">' . __( 'SMTP Server Configuration', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 smtp"></span></span>' . $main_page_button . $smtp_button . '</h2><div class="section smtp"><table class="form-table smtp"><tbody>';

	// SMTP warnings.
	$settings_page .= '<tr class="smtp smpt-warnings unseen"><td colspan="2"><div class="description"><h4>' . __( 'Improve the email deliverability from your website by configuring WordPress to work with an SMTP server', 'simpleform' ) . '</h4>' . __( 'By default, WordPress uses the PHP mail() function to send emails; a basic feature in built-in PHP. However, if your own website is hosted on a shared server, it is very likely that the mail() function has been disabled by your own hosting provider, due to the abuse risk it presents. If you are experiencing problems with email reception, that may be exactly the reason why you\'re not receiving emails. The best and recommended solution is to use an SMTP server to send all outgoing emails; a dedicated machine that takes care of the whole email delivery process. One important function of the SMTP server is to prevent spam, by using authentication mechanisms that only allow authorized users to deliver emails. So, using an SMTP server for outgoing email makes it less likely that email sent out from your website is marked as spam, and lowers the risk of email getting lost somewhere. As the sender, you have a choice of multiple SMTP servers to forward your emails: you can choose your internet service provider, your email provider, your hosting service provider, you can use a specialized provider, or you can even use your personal SMTP server. Obviously, the best option would be the specialized provider, but it is not necessary to subscribe to a paid service to have a good service, especially if you do not have any special needs, and you do not need to send marketing or transactional emails. We suggest you use your own hosting service provider\'s SMTP server, or your own email provider, initially. If you have a hosting plan, you just need to create a new email account that uses your domain name, if you haven\'t done so already. Then use the configuration information that your hosting provider gives you to connect to its own SMTP server, by filling all the fields in this section. If you haven\'t got a hosting plan yet, and your website is still running on a local host, you can use your preferred email address to send email; just enter the data provided by your email provider (Gmail, Yahoo, Hotmail, etc...). Don\'t forget to enable less secure apps on your email account. Furthermore, be careful to enter only your email address for that account, or an alias, into the "From Email" and the "Reply To" fields, since public SMTP servers have particularly strong spam filters, and do not allow you to override the email headers. Always remember to change the configuration data as soon as your website is put online, because your hosting provider may block outgoing SMTP connections. If you want to continue using your preferred email address, ask your hosting provider if the port used is open for outbound traffic. ', 'simpleform' ) . '<p>' . $smtp_credentials . '</p><pre>define( \'SFORM_SMTP_USERNAME\', \'email\' ); // ' . __( 'Your full email address ( e.g. name@domain.com)', 'simpleform' ) . '<br>define( \'SFORM_SMTP_PASSWORD\', \'password\' ); // ' . __( 'Your account\'s password', 'simpleform' ) . '</pre>' . __( 'Anyway, this section is optional. Ignore it and do not enter data if you want to use a dedicated plugin to take care of outgoing email or if you don\'t have to. ', 'simpleform' ) . '</p></div></td></tr>';

	$settings_page .= '<tr id="trsmtpon" class="smtp smpt-settings"><th class="option"><span>' . __( 'SMTP Server', 'simpleform' ) . '</span></th><td id="tdsmtp" class="checkbox-switch notes ' . $smtp_position . '"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="server_smtp" id="server_smtp" class="sform-switch" value="' . $smtp . '" ' . checked( $smtp, true, false ) . $disabled_option . '><span></span></label><label for="server_smtp" class="switch-label ' . $disabled_class . '">' . __( 'Enable an SMTP server for outgoing email, if you haven\'t done so already', 'simpleform' ) . '</label></div><p id="smtp-notice" class="description">' . $smtp_notes . '</p></td></tr>';

	$settings_page .= '<tr class="smtp smpt-settings trsmtp ' . $smtp_tr_class . '"><th class="option"><span>' . __( 'SMTP Host Address', 'simpleform' ) . '</span></th><td class="text notes"><input type="text" name="smtp_host" id="smtp_host" class="sform" placeholder="' . esc_attr__( 'Enter the server address for outgoing email', 'simpleform' ) . '" value="' . $smtp_host . '" ' . $disabled_option . ' \><p class="description">' . __( 'Your outgoing email server address', 'simpleform' ) . '</p></td></tr>';

	$settings_page .= '<tr class="smtp smpt-settings trsmtp ' . $smtp_tr_class . '"><th class="option"><span>' . __( 'Type of Encryption', 'simpleform' ) . '</span></th><td class="radio notes"><fieldset><label for="no-encryption" class="radio ' . $disabled_class . '"><input type="radio" name="smtp_encryption" id="no-encryption" value="none" ' . checked( $smtp_encryption, 'none', false ) . $disabled_option . ' \>' . __( 'None', 'simpleform' ) . '</label><label for="ssl-encryption" class="radio ' . $disabled_class . '"><input type="radio" name="smtp_encryption" id="ssl-encryption"  value="ssl" ' . checked( $smtp_encryption, 'ssl', false ) . $disabled_option . ' \>' . __( 'SSL', 'simpleform' ) . '</label><label for="tls-encryption" class="radio ' . $disabled_class . '"><input type="radio" name="smtp_encryption" id="tls-encryption" value="tls" ' . checked( $smtp_encryption, 'tls', false ) . $disabled_option . ' \>' . __( 'TLS', 'simpleform' ) . '</label></fieldset><p class="description">' . __( 'If your SMTP provider supports both SSL and TLS options, we recommend using TLS encryption', 'simpleform' ) . '</p></td></tr>';

	$settings_page .= '<tr class="smtp smpt-settings trsmtp ' . $smtp_tr_class . '"><th class="option"><span>' . __( 'SMTP Port', 'simpleform' ) . '</span></th><td class="text notes"><input type="number" name="smtp_port" id="smtp_port" class="sform" value="' . $smtp_port . '" min="0" max="2525" ' . $disabled_option . ' \><p class="description">' . __( 'The port that will be used to relay outgoing email to your email server', 'simpleform' ) . '</p></td></tr>';

	$settings_page .= '<tr class="smtp smpt-settings trsmtp ' . $smtp_tr_class . '"><th class="option"><span>' . __( 'SMTP Authentication', 'simpleform' ) . '</span></th><td id="tdauthentication" class="checkbox-switch notes ' . $authentication_position . '"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="smtp_authentication" id="smtp_authentication" class="sform-switch" value="' . $smtp_authentication . '" ' . checked( $smtp_authentication, true, false ) . $disabled_option . '><span></span></label><label for="smtp_authentication" class="switch-label ' . $disabled_class . '">' . __( 'Enable SMTP Authentication', 'simpleform' ) . '</label></div><p class="description">' . __( 'This option should always be checked', 'simpleform' ) . '</p></td></tr>';

	$settings_page .= '<tr class="smtp smpt-settings trsmtp trauthentication ' . $authentication_class . '"><th class="option"><span>' . $username . '</span></th><td class="text notes"><input type="text" name="smtp_username" id="smtp_username" class="sform" placeholder="' . esc_attr( $username_placeholder ) . '"  value="' . $smtp_username . '" ' . $disabled_option . ' \><p class="description">' . __( 'The username to log in to the SMTP email server (your email). Please read the above warnings for improved security', 'simpleform' ) . '</p></td></tr>';

	$settings_page .= '<tr class="smtp smpt-settings trsmtp trauthentication ' . $authentication_class . '"><th class="option"><span>' . $password . '</span></th><td class="last text notes"><input type="text" name="smtp_password" id="smtp_password" class="sform" placeholder="' . esc_attr( $password_placeholder ) . '"  value="' . $smtp_password . '" ' . $disabled_option . ' \><p class="description">' . __( 'The password to log in to the SMTP email server (your password). Please read the above warnings for improved security', 'simpleform' ) . '</p></td></tr></tbody></table></div>';

	// Contact alert options.
	$settings_page .= '<h2 id="h2-notification" class="options-heading"><span class="heading" data-section="notification">' . __( 'Contact Alert', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 notification"></span></span></h2><div class="section notification"><table class="form-table notification"><tbody>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Alert Email', 'simpleform' ) . '</span></th><td id="tdnotification" class="checkbox-switch ' . $alert_position . '"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="notification" id="notification" class="sform-switch" value="' . $notification . '" ' . checked( $notification, true, false ) . '><span></span></label><label for="notification" class="switch-label">' . __( 'Send email to alert the admin, or person responsible for managing contacts, when the form has been successfully submitted', 'simpleform' ) . '</label></div></td></tr>';

	$settings_page .= '<tr class="trnotification ' . $alert_tr_class . '"><th class="option"><span>' . __( 'Send To', 'simpleform' ) . '</span></th><td class="text notes"><input type="text" name="recipients" id="recipients" class="sform" placeholder="' . esc_attr__( 'Enter the email address to which the admin notification is sent', 'simpleform' ) . '" value="' . $notification_recipient . '" \><p class="description">' . __( 'Use a comma-separated list of email addresses to send to more than one address', 'simpleform' ) . '</p></td></tr>';

	$settings_page .= '<tr class="trnotification ' . $alert_tr_class . '"><th class="option"><span>' . __( 'BCC', 'simpleform' ) . '</span></th><td class="text notes"><input type="text" name="bcc" id="bcc" class="sform" placeholder="' . esc_attr__( 'Enter the email address to which a copy of the admin notification is sent', 'simpleform' ) . '" value="' . $bcc . '" \><p class="description">' . __( 'Use a comma-separated list of email addresses to send to more than one address', 'simpleform' ) . '</p></td></tr>';

	$settings_page .= '<tr class="trnotification ' . $alert_tr_class . '"><th class="option"><span>' . __( 'From Email', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="alert_from" id="alert_from" class="sform" placeholder="' . esc_attr__( 'Enter the email address from which the admin notification is sent', 'simpleform' ) . '" value="' . $notification_email . '" \></td></tr>';

	$settings_page .= '<tr class="trnotification ' . $alert_tr_class . '"><th class="option"><span>' . __( 'From Name', 'simpleform' ) . '</span></th><td class="radio"><fieldset><label for="requester-name" class="radio"><input type="radio" name="alert_name" id="requester-name" value="requester" ' . checked( $notification_name, 'requester', false ) . ' \>' . __( 'Use submitter name', 'simpleform' ) . '</label><label for="form-name" class="radio"><input type="radio" name="alert_name" id="form-name"  value="form" ' . checked( $notification_name, 'form', false ) . ' \>' . __( 'Use contact form name', 'simpleform' ) . '</label><label for="custom-name" class="radio "><input type="radio" name="alert_name" id="custom-name" value="custom" ' . checked( $notification_name, 'custom', false ) . ' \>' . __( 'Use default name', 'simpleform' ) . '</label></fieldset></td></tr>';

	$settings_page .= '<tr class="trnotification trcustomname ' . $alert_name_class . '"><th class="option"><span>' . __( 'Default Name', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="alert_sender" id="alert_sender" class="sform" placeholder="' . esc_attr__( 'Enter the name from which the admin notification is sent', 'simpleform' ) . '" value="' . $custom_sender . '" \></td></tr>';

	$settings_page .= '<tr class="trnotification ' . $alert_tr_class . '"><th class="option"><span>' . __( 'Email Subject', 'simpleform' ) . '</span></th><td class="radio"><fieldset><label for="request-subject" class="radio"><input type="radio" name="alert_subject" id="request-subject" value="request" ' . checked( $notification_subject, 'request', false ) . '>' . __( 'Use submission subject', 'simpleform' ) . '</label><label for="default-subject" class="radio"><input type="radio" name="alert_subject" id="default-subject" value="custom" ' . checked( $notification_subject, 'custom', false ) . '>' . __( 'Use default subject', 'simpleform' ) . '</label></fieldset></td></tr>';

	$settings_page .= '<tr class="trnotification trcustomsubject ' . $alert_name_class . '"><th class="option"><span>' . __( 'Default Subject', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="custom_subject" id="custom_subject" class="sform" placeholder="' . esc_attr__( 'Enter the subject with which the admin notification is sent', 'simpleform' ) . '" value="' . $custom_subject . '" \></td></tr>';

	$settings_page .= '<tr class="trnotification ' . $alert_tr_class . '"><th class="option"><span>' . __( 'Reply To', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="alert_reply" id="alert_reply" class="sform-switch" value="' . $notification_reply . '" ' . checked( $notification_reply, true, false ) . '><span></span></label><label for="alert_reply" class="switch-label">' . __( 'Use the email address of the person who filled in the form for reply-to if email is provided', 'simpleform' ) . '</label></div></td></tr>';

	$settings_page .= '<tr class="trnotification ' . $alert_tr_class . '"><th class="option"><span>' . __( 'Submission ID', 'simpleform' ) . '</span></th><td class="checkbox-switch last"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="submission_number" id="submission_number" class="sform-switch" value="' . $submission_number . '" ' . checked( $submission_number, 'hidden', false ) . '><span></span></label><label for="submission_number" class="switch-label">' . __( 'Hide submission ID inside email subject', 'simpleform' ) . '</label></div></td></tr></tbody></table></div>';

	// Auto responder options.
	$settings_page .= '<h2 id="h2-auto" class="options-heading"><span class="heading" data-section="auto">' . __( 'Auto Responder', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 auto"></span></span></h2><div class="section auto"><table class="form-table auto"><tbody>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Auto-Reply Email', 'simpleform' ) . '</span></th><td id="tdconfirmation" class="checkbox-switch ' . $autoreply_position . '"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="autoresponder" id="autoresponder" class="sform-switch" value="' . $auto . '" ' . checked( $auto, true, false ) . '><span></span></label><label for="autoresponder" class="switch-label">' . __( 'Send a confirmation email to users who have successfully submitted the form', 'simpleform' ) . '</label></div></td></tr>';

	$settings_page .= '<tr class="trauto ' . $autoreply_tr_class . '"><th class="option"><span>' . __( 'From Email', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="autoreply_email" id="autoreply_email" class="sform" placeholder="' . esc_attr__( 'Enter the email address from which the auto-reply is sent', 'simpleform' ) . '" value="' . $auto_email . '" \></td></tr>';

	$settings_page .= '<tr class="trauto ' . $autoreply_tr_class . '"><th class="option"><span>' . __( 'From Name', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="autoreply_name" id="autoreply_name" class="sform" placeholder="' . esc_attr__( 'Enter the name from which the auto-reply is sent', 'simpleform' ) . '" value="' . $auto_name . '" \></td></tr>';

	$settings_page .= '<tr class="trauto ' . $autoreply_tr_class . '"><th class="option"><span>' . __( 'Email Subject', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="autoreply_subject" id="autoreply_subject" class="sform" placeholder="' . esc_attr__( 'Enter the subject with which auto-reply is sent', 'simpleform' ) . '" value="' . $auto_subject . '" \></td></tr>';

	$settings_page .= '<tr class="trauto ' . $autoreply_tr_class . '"><th class="option"><span>' . __( 'Email Message', 'simpleform' ) . '</span></th><td class="textarea"><textarea name="autoreply_message" id="autoreply_message" class="sform" placeholder="' . esc_attr__( 'Enter the content for your auto-reply message', 'simpleform' ) . '" >' . $auto_message . '</textarea><p class="description">' . __( 'You are able to use HTML tags and the following smart tags:', 'simpleform' ) . '  [name], [lastname], [email], [phone], [website], [subject], [message], [submission_id]</p></td></tr>';

	$settings_page .= '<tr class="trauto ' . $autoreply_tr_class . '"><th class="option"><span>' . __( 'Reply To', 'simpleform' ) . '</span></th><td class="last text notes"><input type="text" name="autoreply_reply" id="autoreply_reply" class="sform" placeholder="' . esc_attr__( 'Enter the email address to use for reply-to', 'simpleform' ) . '" value="' . $auto_reply . '" \><p class="description">' . __( 'Leave it blank to use From Email as the Reply-To value', 'simpleform' ) . '</p></td></tr></tbody></table></div>';

	// Notifications Tab closing.
	$settings_page .= '</div>';

	// Anti-Spam Tab opening.
	$settings_page .= '<div id="tab-spam" class="navtab unseen">';

	// Basic protection options.
	$settings_page .= '<h2 id="h2-spam" class="options-heading"><span class="heading" data-section="spam">' . __( 'Basic Protection', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 spam"></span></span>' . $main_page_button . '</h2><div class="section spam"><table class="form-table spam"><tbody>';

	$settings_page .= '<tr><th class="option"><span>' . __( 'Duplicate Submission', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="duplicate" id="duplicate" class="sform-switch" value="' . $duplicate . '" ' . checked( $duplicate, true, false ) . $disabled_option . '><span></span></label><label for="duplicate" class="switch-label ' . $disabled_class . '">' . __( 'Prevent duplicate form submission', 'simpleform' ) . '</label></div></td></tr></tbody></table></div>';

	// Akismet options.
	$settings_page .= apply_filters( 'akismet_settings_fields', $extra_option, $form );

	// reCaptcha options.
	$settings_page .= apply_filters( 'recaptcha_settings_fields', $extra_option, $form );

	// Anti-Spam Tab closing.
	$settings_page .= '</div>';

	// Save changes button.
	$settings_page .= '<div id="submit-wrap"><div id="alert-wrap"><noscript><div id="noscript">' . __( 'You need JavaScript enabled to save settings. Please activate it. Thanks!', 'simpleform' ) . '</div></noscript><div id="message-wrap" class="message"></div></div><input type="submit" class="submit-button" id="save-settings" name="save-settings" value="' . esc_attr__( 'Save Changes', 'simpleform' ) . '">' . wp_nonce_field( 'simpleform_backend_update', 'simpleform_nonce', false, false ) . '</div>';

	// Form closing tag.
	$settings_page .= '</form>';

} else {

	$settings_page .= '<div id="page-description"><p>' . __( 'It seems the form is no longer available!', 'simpleform' ) . '</p></div><div id="page-buttons"><span class="wp-core-ui button unavailable ' . esc_attr( $color ) . '"><a href="' . esc_url( menu_page_url( 'sform-settings', false ) ) . '">' . __( 'Reload the Settings page', 'simpleform' ) . '</a></span><span class="wp-core-ui button unavailable ' . esc_attr( $color ) . '"><a href="' . esc_url( menu_page_url( 'sform-new', false ) ) . '">' . __( 'Add New Form', 'simpleform' ) . '</a></span><span class="wp-core-ui button unavailable ' . esc_attr( $color ) . '"><a href="' . esc_url( self_admin_url( 'widgets.php' ) ) . '">' . __( 'Activate SimpleForm Contact Form Widget', 'simpleform' ) . '</a></span></div>';

}

// Page wrap: closing tag.
$settings_page .= '</div>';

echo wp_kses( $settings_page, $allowed_tags );

delete_transient( 'sform_action_newform' );
