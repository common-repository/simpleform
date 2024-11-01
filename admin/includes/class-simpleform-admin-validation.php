<?php
/**
 * File delegated to validate the selected admin options.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the form validation.
 */
class SimpleForm_Admin_Validation {

	/**
	 * Sanitize form data
	 *
	 * @since 2.2.0
	 *
	 * @param string $field The ID of input field.
	 * @param string $type  The type of input field.
	 *
	 * @return mixed The sanitized value.
	 */
	public function sanitized_input( $field, $type ) {

		if ( isset( $_POST[ $field ] ) && isset( $_POST['simpleform_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['simpleform_nonce'] ), 'simpleform_backend_update' ) ) {

			$sanitized_value = array(
				'form'       => absint( $_POST[ $field ] ),
				'number'     => absint( $_POST[ $field ] ),
				'textarea'   => sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ),
				'text'       => sanitize_text_field( wp_unslash( $_POST[ $field ] ) ),
				'hidden'     => 'hidden',
				'required'   => 'required',
				'all'        => sanitize_text_field( wp_unslash( $_POST[ $field ] ) ),
				'registered' => 'hidden',
				'anonymous'  => 'hidden',
				'target'     => sanitize_text_field( wp_unslash( $_POST[ $field ] ) ),
				'role'       => sanitize_text_field( wp_unslash( $_POST[ $field ] ) ),
				'label'      => sanitize_key( $_POST[ $field ] ),
				'alignment'  => sanitize_key( $_POST[ $field ] ),
				'button'     => sanitize_text_field( wp_unslash( $_POST[ $field ] ) ),
				'general'    => sanitize_text_field( wp_unslash( $_POST[ $field ] ) ),
				'sign'       => sanitize_key( $_POST[ $field ] ),
				'direction'  => sanitize_key( $_POST[ $field ] ),
				'css'        => wp_strip_all_tags( wp_unslash( $_POST[ $field ] ) ),
				'html'       => wp_kses_post( wp_unslash( $_POST[ $field ] ) ),
				'focus'      => sanitize_key( $_POST[ $field ] ),
				'message'    => sanitize_text_field( wp_unslash( $_POST[ $field ] ) ),
				'redirect'   => sanitize_key( $_POST[ $field ] ),
				'ssl'        => sanitize_key( $_POST[ $field ] ),
				'smtp'       => absint( $_POST[ $field ] ),
				'sender'     => sanitize_key( $_POST[ $field ] ),
				'subject'    => sanitize_key( $_POST[ $field ] ),
				'tickbox'    => true,
				'url'        => esc_url_raw( wp_unslash( $_POST[ $field ] ) ),
				'checkboxes' => explode( ',', sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) ),

			);

			$value = $sanitized_value[ $type ];

		} else {

			$visibility = 'lastname_field' === $field || 'phone_field' === $field || 'captcha_field' === $field ? 'hidden' : 'visible';

			$minimum = $this->field_minlength( $field );

			$default_value = array(
				'form'       => 1,
				'number'     => $minimum,
				'textarea'   => '',
				'text'       => '',
				'hidden'     => 'visible',
				'required'   => 'optional',
				'all'        => $visibility,
				'registered' => 'registered',
				'anonymous'  => 'anonymous',
				'target'     => 'all',
				'role'       => 'any',
				'label'      => 'top',
				'alignment'  => 'alone',
				'button'     => 'centred',
				'general'    => 'default',
				'sign'       => 'required',
				'direction'  => 'ltr',
				'css'        => '',
				'html'       => '',
				'focus'      => 'field',
				'message'    => 'bottom',
				'redirect'   => 'message',
				'ssl'        => 'ssl',
				'smtp'       => 465,
				'sender'     => 'requester',
				'subject'    => 'request',
				'tickbox'    => false,
				'url'        => '',
				'checkboxes' => array(),
			);

			$value = $default_value[ $type ];

		}

		return $value;
	}

	/**
	 * Minimum length for field
	 *
	 * @since 2.2.0
	 *
	 * @param string $field The ID of input field.
	 *
	 * @return int The minimum value.
	 */
	public function field_minlength( $field ) {

		switch ( $field ) {
			case 'name_minlength' === $field:
			case 'lastname_minlength' === $field:
				$minimum = 2;
				break;
			case 'subject_minlength' === $field:
				$minimum = 5;
				break;
			case 'message_minlength' === $field:
				$minimum = 10;
				break;
			default:
				$minimum = 0;
		}

		return $minimum;
	}

	/**
	 * Sanitize values entered to update form attributes
	 *
	 * @since 2.2.0
	 *
	 * @return mixed[] Array of sanitized values.
	 */
	public function sanitized_data() {

		$values                         = array();
		$values['newform']              = $this->sanitized_input( 'newform', 'text' );
		$values['form']                 = $this->sanitized_input( 'form_id', 'form' );
		$values['embed_in']             = $this->sanitized_input( 'embed_in', 'number' );
		$values['widget_id']            = $this->sanitized_input( 'widget_id', 'number' );
		$values['form_name']            = $this->sanitized_input( 'form_name', 'text' );
		$values['text_above']           = $this->sanitized_input( 'text_above', 'html' );
		$values['text_below']           = $this->sanitized_input( 'text_below', 'html' );
		$values['name_visibility']      = $this->sanitized_input( 'name_visibility', 'hidden' );
		$values['name_label']           = $this->sanitized_input( 'name_label', 'text' );
		$values['name_placeholder']     = $this->sanitized_input( 'name_placeholder', 'text' );
		$values['name_minlength']       = $this->sanitized_input( 'name_minlength', 'number' );
		$values['name_maxlength']       = $this->sanitized_input( 'name_maxlength', 'number' );
		$values['name_required']        = $this->sanitized_input( 'name_required', 'required' );
		$values['lastname_visibility']  = $this->sanitized_input( 'lastname_visibility', 'hidden' );
		$values['lastname_label']       = $this->sanitized_input( 'lastname_label', 'text' );
		$values['lastname_placeholder'] = $this->sanitized_input( 'lastname_placeholder', 'text' );
		$values['lastname_minlength']   = $this->sanitized_input( 'lastname_minlength', 'number' );
		$values['lastname_maxlength']   = $this->sanitized_input( 'lastname_maxlength', 'number' );
		$values['lastname_required']    = $this->sanitized_input( 'lastname_required', 'required' );
		$values['email_visibility']     = $this->sanitized_input( 'email_visibility', 'hidden' );
		$values['email_label']          = $this->sanitized_input( 'email_label', 'text' );
		$values['email_placeholder']    = $this->sanitized_input( 'email_placeholder', 'text' );
		$values['email_required']       = $this->sanitized_input( 'email_required', 'required' );
		$values['phone_visibility']     = $this->sanitized_input( 'phone_visibility', 'hidden' );
		$values['phone_label']          = $this->sanitized_input( 'phone_label', 'text' );
		$values['phone_placeholder']    = $this->sanitized_input( 'phone_placeholder', 'text' );
		$values['phone_required']       = $this->sanitized_input( 'phone_required', 'required' );
		$values['website_visibility']   = $this->sanitized_input( 'website_visibility', 'hidden' );
		$values['website_label']        = $this->sanitized_input( 'website_label', 'text' );
		$values['website_placeholder']  = $this->sanitized_input( 'website_placeholder', 'text' );
		$values['website_required']     = $this->sanitized_input( 'website_required', 'required' );
		$values['subject_visibility']   = $this->sanitized_input( 'subject_visibility', 'hidden' );
		$values['subject_label']        = $this->sanitized_input( 'subject_label', 'text' );
		$values['subject_placeholder']  = $this->sanitized_input( 'subject_placeholder', 'text' );
		$values['subject_minlength']    = $this->sanitized_input( 'subject_minlength', 'number' );
		$values['subject_maxlength']    = $this->sanitized_input( 'subject_maxlength', 'number' );
		$values['subject_required']     = $this->sanitized_input( 'subject_required', 'required' );
		$values['message_visibility']   = $this->sanitized_input( 'message_visibility', 'hidden' );
		$values['message_label']        = $this->sanitized_input( 'message_label', 'text' );
		$values['message_placeholder']  = $this->sanitized_input( 'message_placeholder', 'text' );
		$values['message_minlength']    = $this->sanitized_input( 'message_minlength', 'number' );
		$values['message_maxlength']    = $this->sanitized_input( 'message_maxlength', 'number' );
		$values['consent_label']        = $this->sanitized_input( 'consent_label', 'html' );
		$values['privacy_link']         = $this->sanitized_input( 'privacy_link', 'tickbox' );
		$values['privacy_page']         = $this->sanitized_input( 'privacy_page', 'number' );
		$values['page_id']              = $this->sanitized_input( 'page_id', 'number' );
		$values['target']               = $this->sanitized_input( 'target', 'tickbox' );
		$values['consent_required']     = $this->sanitized_input( 'consent_required', 'required' );
		$values['captcha_label']        = $this->sanitized_input( 'captcha_label', 'text' );
		$values['submit_label']         = $this->sanitized_input( 'submit_label', 'text' );
		$values['label_position']       = $this->sanitized_input( 'label_position', 'label' );
		$values['lastname_alignment']   = $this->sanitized_input( 'lastname_alignment', 'alignment' );
		$values['phone_alignment']      = $this->sanitized_input( 'phone_alignment', 'alignment' );
		$values['submit_position']      = $this->sanitized_input( 'submit_position', 'button' );
		$values['label_size']           = $this->sanitized_input( 'label_size', 'general' );
		$values['required_sign']        = $this->sanitized_input( 'required_sign', 'tickbox' );
		$values['required_word']        = $this->sanitized_input( 'required_word', 'text' );
		$values['word_position']        = $this->sanitized_input( 'word_position', 'sign' );
		$values['form_direction']       = $this->sanitized_input( 'form_direction', 'direction' );
		$values['additional_css']       = $this->sanitized_input( 'additional_css', 'css' );

		return $values;
	}

	/**
	 * Assign a default value to the fields of a targeted form
	 *
	 * @since 2.2.0
	 *
	 * @param string $show_for The target of the form.
	 * @param string $field    The ID of input field.
	 *
	 * @return string The sanitized value.
	 */
	public function targeted_form_fields( $show_for, $field ) {

		if ( 'out' === $show_for ) {
			$value = strval( $this->sanitized_input( $field, 'anonymous' ) );
		} elseif ( 'in' === $show_for ) {
			$value = strval( $this->sanitized_input( $field, 'registered' ) );
		} else {
			$value = strval( $this->sanitized_input( $field, 'all' ) );
		}

		return $value;
	}

	/**
	 * Check if form is embedded in a widget area and return default values for the target
	 *
	 * @since 2.2.0
	 *
	 * @param int $widget_id The ID of the widget.
	 *
	 * @return string[] Array of sanitized values.
	 */
	public function target_fields( $widget_id ) {

		$values = array();

		if ( 0 === $widget_id ) {
			$values['show_for']  = $this->sanitized_input( 'show_for', 'target' );
			$values['user_role'] = $this->sanitized_input( 'user_role', 'role' );
		} else {
			$sform_widget = get_option( 'widget_simpleform' );
			if ( is_array( $sform_widget ) && in_array( $widget_id, array_keys( $sform_widget ), true ) ) {
				$values['show_for']  = ! empty( $sform_widget[ $widget_id ]['show_for'] ) ? $sform_widget[ $widget_id ]['show_for'] : 'all';
				$values['user_role'] = ! empty( $sform_widget[ $widget_id ]['user_role'] ) ? $sform_widget[ $widget_id ]['user_role'] : 'any';
			}
		}

		return $values;
	}

	/**
	 * Form attributes validation
	 *
	 * @since 2.1.7
	 *
	 * @param mixed[] $values Array of sanitized values used to update form attributes.
	 *
	 * @return string The validation error.
	 */
	public function options_validation( $values ) {

		$error               = '';
		$newform             = $values['newform'];
		$form_id             = $values['form'];
		$form_name           = $values['form_name'];
		$name_visibility     = $values['name_visibility'];
		$name_minlength      = absint( $values['name_minlength'] );
		$name_maxlength      = absint( $values['name_maxlength'] );
		$name_required       = $values['name_required'];
		$lastname_visibility = $values['lastname_visibility'];
		$lastname_minlength  = absint( $values['lastname_minlength'] );
		$lastname_maxlength  = absint( $values['lastname_maxlength'] );
		$lastname_required   = $values['lastname_required'];
		$email_visibility    = $values['email_visibility'];
		$phone_visibility    = $values['phone_visibility'];
		$subject_visibility  = $values['subject_visibility'];
		$subject_minlength   = absint( $values['subject_minlength'] );
		$subject_maxlength   = absint( $values['subject_maxlength'] );
		$subject_required    = $values['subject_required'];
		$message_visibility  = $values['message_visibility'];
		$message_minlength   = absint( $values['message_minlength'] );
		$message_maxlength   = absint( $values['message_maxlength'] );
		$label_position      = $values['label_position'];
		$form_direction      = $values['form_direction'];

		// Form name validation.
		$error = apply_filters( 'form_name_validation', $error, $newform, $form_id, $form_name );

		// Name length validation.
		$error = apply_filters( 'field_length_validation', $error, 'name', $name_minlength, $name_maxlength, $name_required );

		// Lastname length validation.
		$error = apply_filters( 'field_length_validation', $error, 'lastname', $lastname_minlength, $lastname_maxlength, $lastname_required );

		// Subject length validation.
		$error = apply_filters( 'field_length_validation', $error, 'subject', $subject_minlength, $subject_maxlength, $subject_required );

		// Message length validation.
		$error = apply_filters( 'field_length_validation', $error, 'message', $message_minlength, $message_maxlength, 'required' );

		// Fields alignment validation.
		$error = apply_filters( 'fields_alignment_validation', $error, $name_visibility, $lastname_visibility, $email_visibility, $phone_visibility, $subject_visibility, $message_visibility, $label_position, $form_direction );

		return $error;
	}

	/**
	 * Sanitize values entered to update form settings
	 *
	 * @since 2.2.0
	 *
	 * @return mixed[] Array of sanitized values.
	 */
	public function sanitized_settings() {

		$main_settings                 = (array) get_option( 'sform_settings' );
		$values                        = array();
		$values['form']                = $this->sanitized_input( 'form_id', 'form' );
		$values['admin_notices']       = $this->sanitized_input( 'admin_notices', 'tickbox' );
		$values['frontend_notice']     = $this->sanitized_input( 'frontend_notice', 'tickbox' );
		$values['admin_color']         = $this->sanitized_input( 'admin_color', 'general' );
		$values['ajax_submission']     = $this->sanitized_input( 'ajax_submission', 'tickbox' );
		$spinner                       = $this->sanitized_input( 'spinner', 'tickbox' );
		$values['spinner']             = $values['ajax_submission'] ? $spinner : false;
		$values['html5']               = $this->sanitized_input( 'html5', 'tickbox' );
		$values['focus']               = $this->sanitized_input( 'focus', 'focus' );
		$values['form_style']          = $this->sanitized_input( 'form_style', 'general' );
		$values['stylesheet']          = $this->sanitized_input( 'stylesheet', 'tickbox' );
		$values['stylesheet_file']     = $this->sanitized_input( 'stylesheet_file', 'tickbox' );
		$values['javascript']          = $this->sanitized_input( 'javascript', 'tickbox' );
		$values['deletion_data']       = $this->sanitized_input( 'deletion_data', 'tickbox' );
		$values['multiple_spaces']     = $this->sanitized_input( 'multiple_spaces', 'tickbox' );
		$values['outside_error']       = $this->sanitized_input( 'outside_error', 'message' );
		$values['empty_fields']        = $this->sanitized_input( 'empty_fields', 'text' );
		$values['characters_length']   = $this->sanitized_input( 'characters_length', 'tickbox' );
		$values['empty_name']          = $this->sanitized_input( 'empty_name', 'text' );
		$values['numeric_name']        = $this->sanitized_input( 'numeric_name', 'text' );
		$values['generic_name']        = $this->sanitized_input( 'generic_name', 'text' );
		$values['invalid_name']        = $this->sanitized_input( 'invalid_name', 'text' );
		$values['name_error']          = $this->sanitized_input( 'name_error', 'text' );
		$values['empty_lastname']      = $this->sanitized_input( 'empty_lastname', 'text' );
		$values['numeric_lastname']    = $this->sanitized_input( 'numeric_lastname', 'text' );
		$values['generic_lastname']    = $this->sanitized_input( 'generic_lastname', 'text' );
		$values['invalid_lastname']    = $this->sanitized_input( 'invalid_lastname', 'text' );
		$values['lastname_error']      = $this->sanitized_input( 'lastname_error', 'text' );
		$values['empty_email']         = $this->sanitized_input( 'empty_email', 'text' );
		$values['invalid_email']       = $this->sanitized_input( 'invalid_email', 'text' );
		$values['email_error']         = $this->sanitized_input( 'email_error', 'text' );
		$values['empty_phone']         = $this->sanitized_input( 'empty_phone', 'text' );
		$values['invalid_phone']       = $this->sanitized_input( 'invalid_phone', 'text' );
		$values['phone_error']         = $this->sanitized_input( 'phone_error', 'text' );
		$values['empty_website']       = $this->sanitized_input( 'empty_website', 'text' );
		$values['invalid_website']     = $this->sanitized_input( 'invalid_website', 'text' );
		$values['website_error']       = $this->sanitized_input( 'website_error', 'text' );
		$values['empty_subject']       = $this->sanitized_input( 'empty_subject', 'text' );
		$values['numeric_subject']     = $this->sanitized_input( 'numeric_subject', 'text' );
		$values['generic_subject']     = $this->sanitized_input( 'generic_subject', 'text' );
		$values['invalid_subject']     = $this->sanitized_input( 'invalid_subject', 'text' );
		$values['subject_error']       = $this->sanitized_input( 'subject_error', 'text' );
		$values['empty_message']       = $this->sanitized_input( 'empty_message', 'text' );
		$values['numeric_message']     = $this->sanitized_input( 'numeric_message', 'text' );
		$values['generic_message']     = $this->sanitized_input( 'generic_message', 'text' );
		$values['invalid_message']     = $this->sanitized_input( 'invalid_message', 'text' );
		$values['message_error']       = $this->sanitized_input( 'message_error', 'text' );
		$values['consent_error']       = $this->sanitized_input( 'consent_error', 'text' );
		$values['empty_captcha']       = $this->sanitized_input( 'empty_captcha', 'text' );
		$values['invalid_captcha']     = $this->sanitized_input( 'invalid_captcha', 'text' );
		$values['captcha_error']       = $this->sanitized_input( 'captcha_error', 'text' );
		$values['honeypot_error']      = $this->sanitized_input( 'honeypot_error', 'text' );
		$values['duplicate_error']     = $this->sanitized_input( 'duplicate_error', 'text' );
		$values['ajax_error']          = $this->sanitized_input( 'ajax_error', 'text' );
		$values['server_error']        = $this->sanitized_input( 'server_error', 'text' );
		$values['success_action']      = $this->sanitized_input( 'success_action', 'redirect' );
		$values['success_message']     = $this->sanitized_input( 'success_message', 'html' );
		$redirect_page                 = $this->sanitized_input( 'redirect_page', 'text' );
		$values['redirect_page']       = 'message' === $values['success_action'] ? '' : $redirect_page;
		$values['server_smtp']         = $this->sanitized_input( 'server_smtp', 'tickbox' );
		$values['smtp_host']           = $this->sanitized_input( 'smtp_host', 'text' );
		$values['smtp_encryption']     = $this->sanitized_input( 'smtp_encryption', 'ssl' );
		$values['smtp_port']           = $this->sanitized_input( 'smtp_port', 'smtp' );
		$values['smtp_authentication'] = $this->sanitized_input( 'smtp_authentication', 'tickbox' );
		$values['smtp_username']       = $this->sanitized_input( 'smtp_username', 'text' );
		$values['smtp_password']       = $this->sanitized_input( 'smtp_password', 'text' );
		$values['notification']        = $this->sanitized_input( 'notification', 'tickbox' );
		$values['recipients']          = $this->sanitized_input( 'recipients', 'text' );
		$bcc                           = strval( $this->sanitized_input( 'bcc', 'text' ) );
		$values['bcc']                 = str_replace( ' ', '', $bcc );
		$values['alert_from']          = $this->sanitized_input( 'alert_from', 'text' );
		$values['alert_name']          = $this->sanitized_input( 'alert_name', 'sender' );
		$values['alert_sender']        = $this->sanitized_input( 'alert_sender', 'text' );
		$values['alert_subject']       = $this->sanitized_input( 'alert_subject', 'subject' );
		$values['custom_subject']      = $this->sanitized_input( 'custom_subject', 'text' );
		$values['alert_reply']         = $this->sanitized_input( 'alert_reply', 'tickbox' );
		$values['submission_number']   = $this->sanitized_input( 'submission_number', 'hidden' );
		$values['autoresponder']       = $this->sanitized_input( 'autoresponder', 'tickbox' );
		$values['autoreply_email']     = $this->sanitized_input( 'autoreply_email', 'text' );
		$values['autoreply_name']      = $this->sanitized_input( 'autoreply_name', 'text' );
		$values['autoreply_subject']   = $this->sanitized_input( 'autoreply_subject', 'text' );
		$values['autoreply_message']   = $this->sanitized_input( 'autoreply_message', 'html' );
		$values['autoreply_reply']     = $this->sanitized_input( 'autoreply_reply', 'text' );
		$values['form_pageid']         = ! empty( $main_settings['form_pageid'] ) && get_post_status( absint( $main_settings['form_pageid'] ) ) ? absint( $main_settings['form_pageid'] ) : '';
		$values['confirmation_pageid'] = ! empty( $main_settings['confirmation_pageid'] ) && get_post_status( absint( $main_settings['confirmation_pageid'] ) ) ? absint( $main_settings['confirmation_pageid'] ) : '';
		$values['duplicate']           = $this->sanitized_input( 'duplicate', 'tickbox' );

		return $values;
	}

	/**
	 * Form settings validation
	 *
	 * @since 2.1.7
	 *
	 * @param mixed[] $values Array of sanitized values used to update form settings.
	 *
	 * @return string The validation error.
	 */
	public function settings_validation( $values ) {

		$error = '';
		$html5 = (bool) $values['html5'];
		$focus = strval( $values['focus'] );
		$alert = $values['notification'];
		// SMTP server settings.
		$smtp_settings                        = array();
		$smtp_settings['server_smtp']         = $values['server_smtp'];
		$smtp_settings['smtp_host']           = $values['smtp_host'];
		$smtp_settings['smtp_port']           = $values['smtp_port'];
		$smtp_settings['smtp_authentication'] = $values['smtp_authentication'];
		$smtp_settings['smtp_username']       = $values['smtp_username'];
		$smtp_settings['smtp_password']       = $values['smtp_password'];

		// Validation of focus on the fields.
		$error = apply_filters( 'focus_validation', $error, $html5, $focus );

		// Validation of SMTP server.
		$error = apply_filters( 'smtp_server_validation', $error, $smtp_settings );

		// Validation of SMTP login.
		$error = apply_filters( 'smtp_login_validation', $error, $smtp_settings );

		// Check if alert email has been enabled.
		$error = ! $alert ? __( 'You need to enable the alert email', 'simpleform' ) : $error;

		return $error;
	}

	/**
	 * Sanitize form deletion data
	 *
	 * @since 2.2.0
	 *
	 * @param string $field The ID of input field.
	 * @param string $type  The type of input field.
	 *
	 * @return bool|int|string The sanitized value.
	 */
	public function sanitized_locks_input( $field, $type ) {

		if ( isset( $_POST[ $field ] ) && isset( $_POST['simpleform_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['simpleform_nonce'] ), 'simpleform_backend_update' ) ) {

			$sanitized_value = array(
				'form'     => absint( $_POST[ $field ] ),
				'text'     => sanitize_text_field( wp_unslash( $_POST[ $field ] ) ),
				'number'   => absint( $_POST[ $field ] ),
				'checkbox' => true,
			);

			$value = $sanitized_value[ $type ];

		} else {

			$default_value = array(
				'form'     => 1,
				'text'     => '',
				'number'   => 0,
				'checkbox' => false,
			);

			$value = $default_value[ $type ];

		}

		return $value;
	}

	/**
	 * Sanitize values entered to delete the form
	 *
	 * @since 2.2.0
	 *
	 * @return mixed[] Array of sanitized values.
	 */
	public function sanitized_locks() {

		$values                  = array();
		$values['form']          = $this->sanitized_locks_input( 'form', 'form' );
		$values['form_name']     = $this->sanitized_locks_input( 'form_name', 'text' );
		$values['confirmation']  = $this->sanitized_locks_input( 'confirmation', 'text' );
		$values['relocation']    = $this->sanitized_locks_input( 'relocation', 'checkbox' );
		$moveto                  = $this->sanitized_locks_input( 'moveto', 'number' );
		$values['entries']       = $this->sanitized_locks_input( 'be_moved', 'text' );
		$values['moveto']        = ! $values['relocation'] ? 0 : $moveto;
		$values['movement']      = $values['relocation'] && 0 !== $values['moveto'] && '' !== $values['entries'] ? true : false;
		$onetime                 = $this->sanitized_locks_input( 'onetime', 'checkbox' );
		$scheduling              = 'next' === $values['entries'] || ! $onetime ? true : false;
		$values['restore']       = $this->sanitized_locks_input( 'restore', 'checkbox' );
		$values['deletion_form'] = $this->sanitized_locks_input( 'deletion_form', 'checkbox' );
		$settings                = $this->sanitized_locks_input( 'settings', 'checkbox' );
		$settings_value          = ! $values['movement'] ? false : $settings;
		$values['form_to']       = $this->sanitized_locks_input( 'form_to', 'text' );
		$values['form_id']       = $this->sanitized_locks_input( 'form_id', 'form' );

		if ( true === $scheduling ) {
			$values['onetime']  = false;
			$values['be_moved'] = 'next';
			$values['settings'] = $settings_value;
		} else {
			$values['onetime']  = true;
			$values['be_moved'] = '';
			$values['settings'] = false;
		}

		return $values;
	}

	/**
	 * Form locks validation
	 *
	 * @since 2.1.7
	 *
	 * @param mixed[] $values Array of sanitized values used to update form locks.
	 * @param string  $form_status The form status.
	 *
	 * @return string The validation error.
	 */
	public function locks_validation( $values, $form_status ) {

		$error    = '';
		$form_id  = $values['form'];
		$transfer = $values['relocation'];
		$moveto   = $values['moveto'];
		$be_moved = $values['entries'];
		$restore  = $values['restore'];

		// Form status validation.
		$error = apply_filters( 'form_status_validation', $error, $form_id, $form_status );

		// Moving validation.
		$error = apply_filters( 'moving_validation', $error, $transfer, $moveto, $be_moved, $restore );

		return $error;
	}
}

new SimpleForm_Admin_Validation();
