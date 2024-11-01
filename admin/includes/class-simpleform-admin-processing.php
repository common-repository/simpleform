<?php
/**
 * File delegated to process the admin options.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the admin option processing.
 */
class SimpleForm_Admin_Processing {

	/**
	 * Class constructor
	 *
	 * @since 2.2.0
	 */
	public function __construct() {

		// Form specifics updating.
		add_filter( 'sform_specifics_updating', array( $this, 'specifics_updating' ), 10, 8 );
		// Form attributes updating.
		add_filter( 'sform_attributes_updating', array( $this, 'attributes_updating' ), 10, 6 );
		// Form settings updating.
		add_filter( 'sform_settings_updating', array( $this, 'settings_updating' ), 10, 3 );
	}

	/**
	 * Update form specifics
	 *
	 * @since 2.2.0
	 *
	 * @param string $update             The result of admin options updating.
	 * @param string $newform            The sanitized value entered in the field.
	 * @param int    $form_id            The ID of the form.
	 * @param string $show_for           The user target of the form.
	 * @param string $user_role          The role of the user target.
	 * @param string $form_name          The sanitized value entered in the field.
	 * @param string $preassigned_name   The previous name of the form.
	 * @param string $preassigned_target The previous target of the form.
	 *
	 * @return string The result of admin options updating.
	 */
	public function specifics_updating( $update, $newform, $form_id, $show_for, $user_role, $form_name, $preassigned_name, $preassigned_target ) {

		global $wpdb;

		if ( empty( $newform ) ) {

			if ( $form_name !== $preassigned_name || $show_for !== $preassigned_target ) {

				$data_to_update = array(
					'name'   => $form_name,
					'target' => $show_for,
					'role'   => $user_role,
				);
				$updating       = $wpdb->update( $wpdb->prefix . 'sform_shortcodes', $data_to_update, array( 'id' => $form_id ) ); // phpcs:ignore.
				// Clear cache data.
				wp_cache_delete( 'form_data_' . $form_id );
				$update = $updating ? $form_id : '';

			}
		} else {

			$rows           = $wpdb->get_row( "SHOW TABLE STATUS LIKE '{$wpdb->prefix}sform_shortcodes'" ); // phpcs:ignore
			$newform_id     = $rows->Auto_increment; // phpcs:ignore
			$date           = gmdate( 'Y-m-d H:i:s' );
			$data_to_insert = array(
				'name'      => $form_name,
				'shortcode' => 'simpleform id="' . $newform_id . '"',
				'creation'  => $date,
				'target'    => $show_for,
				'role'      => $user_role,
				'status'    => 'draft',
			);
			$updating       = $wpdb->insert( $wpdb->prefix . 'sform_shortcodes', $data_to_insert ); // phpcs:ignore
			$update         = $updating ? $newform_id : '';

		}

		return $update;
	}

	/**
	 * Update form attributes
	 *
	 * @since 2.2.0
	 *
	 * @param string   $update      The result of admin options updating.
	 * @param int      $form        The value to use when updating options.
	 * @param string   $show_for    The user target of the form.
	 * @param string   $user_role   The role of the user target.
	 * @param string[] $form_fields Array of form fields.
	 * @param string[] $values      Array of form attributes.
	 *
	 * @return string|int The result of admin options updating.
	 */
	public function attributes_updating( $update, $form, $show_for, $user_role, $form_fields, $values ) {

		if ( $values['privacy_link'] && 0 !== $values['privacy_page'] ) {
			$consent_label = $values['consent_label'];
			$target        = $values['target'];
		} else {
			$consent_label = preg_replace( '#<a.*?>(.*?)</a>#i', '\1', $values['consent_label'] );
			$target        = false;
		}

		$attributes = array(
			'form'                 => $form,
			'form_name'            => $values['form_name'],
			'show_for'             => $show_for,
			'user_role'            => $user_role,
			'introduction_text'    => $values['text_above'],
			'bottom_text'          => $values['text_below'],
			'name_field'           => $form_fields['name'],
			'name_visibility'      => $values['name_visibility'],
			'name_label'           => $values['name_label'],
			'name_placeholder'     => $values['name_placeholder'],
			'name_minlength'       => $values['name_minlength'],
			'name_maxlength'       => $values['name_maxlength'],
			'name_requirement'     => $values['name_required'],
			'lastname_field'       => $form_fields['lastname'],
			'lastname_visibility'  => $values['lastname_visibility'],
			'lastname_label'       => $values['lastname_label'],
			'lastname_placeholder' => $values['lastname_placeholder'],
			'lastname_minlength'   => $values['lastname_minlength'],
			'lastname_maxlength'   => $values['lastname_maxlength'],
			'lastname_requirement' => $values['lastname_required'],
			'email_field'          => $form_fields['email'],
			'email_visibility'     => $values['email_visibility'],
			'email_label'          => $values['email_label'],
			'email_placeholder'    => $values['email_placeholder'],
			'email_requirement'    => $values['email_required'],
			'phone_field'          => $form_fields['phone'],
			'phone_visibility'     => $values['phone_visibility'],
			'phone_label'          => $values['phone_label'],
			'phone_placeholder'    => $values['phone_placeholder'],
			'phone_requirement'    => $values['phone_required'],
			'website_field'        => $form_fields['website'],
			'website_visibility'   => $values['website_visibility'],
			'website_label'        => $values['website_label'],
			'website_placeholder'  => $values['website_placeholder'],
			'website_requirement'  => $values['website_required'],
			'subject_field'        => $form_fields['subject'],
			'subject_visibility'   => $values['subject_visibility'],
			'subject_label'        => $values['subject_label'],
			'subject_placeholder'  => $values['subject_placeholder'],
			'subject_minlength'    => $values['subject_minlength'],
			'subject_maxlength'    => $values['subject_maxlength'],
			'subject_requirement'  => $values['subject_required'],
			'message_visibility'   => $values['message_visibility'],
			'message_label'        => $values['message_label'],
			'message_placeholder'  => $values['message_placeholder'],
			'message_minlength'    => $values['message_minlength'],
			'message_maxlength'    => $values['message_maxlength'],
			'consent_field'        => $form_fields['consent'],
			'consent_label'        => $consent_label,
			'privacy_link'         => 0 === (int) $values['privacy_page'] ? false : $values['privacy_link'],
			'privacy_page'         => ! $values['privacy_link'] ? 0 : $values['privacy_page'],
			'target'               => $target,
			'consent_requirement'  => $values['consent_required'],
			'captcha_field'        => $form_fields['captcha'],
			'captcha_label'        => $values['captcha_label'],
			'submit_label'         => $values['submit_label'],
			'label_position'       => $values['label_position'],
			'lastname_alignment'   => $values['lastname_alignment'],
			'phone_alignment'      => $values['phone_alignment'],
			'submit_position'      => $values['submit_position'],
			'label_size'           => $values['label_size'],
			'required_sign'        => $values['required_sign'],
			'required_word'        => $values['required_word'],
			'word_position'        => $values['word_position'],
			'form_direction'       => $values['form_direction'],
			'additional_css'       => htmlspecialchars( $values['additional_css'], ENT_HTML5 | ENT_NOQUOTES | ENT_SUBSTITUTE, 'utf-8' ),
		);

		$sform_attributes = array_merge( $attributes, apply_filters( 'recaptcha_attributes_storing', array( 'extra_fields' => '' ) ) );

		if ( empty( $values['newform'] ) ) {
			$form_id           = (int) $values['form'];
			$update_attributes = 1 === $form_id ? update_option( 'sform_attributes', $sform_attributes ) : update_option( 'sform_' . $form_id . '_attributes', $sform_attributes );
		} else {
			$form_id           = (int) $update;
			$update_attributes = update_option( 'sform_' . $update . '_attributes', $sform_attributes );
		}

		if ( $update_attributes ) {
			$update = $form_id;
			$util   = new SimpleForm_Util();
			$util->enqueue_additional_styles( $form_id, 'sform_additional_style', $values['additional_css'] );
		}

		return $update;
	}

	/**
	 * Retrieve the setting value
	 *
	 * @since 2.2.0
	 *
	 * @param string[] $values     Array of values.
	 * @param string   $setting_id The ID of the setting.
	 *
	 * @return mixed The value to assign to the form's attribute.
	 */
	public function setting_value( $values, $setting_id ) {

		$main_settings = (array) get_option( 'sform_settings' );

		if ( 1 !== (int) $values['form'] ) {

			$value = $main_settings[ $setting_id ];

		} else {

			$value = $values[ $setting_id ];

		}

		return $value;
	}

	/**
	 * Update form settings
	 *
	 * @since 2.2.0
	 *
	 * @param string   $update  The result of admin settings updating.
	 * @param int      $form_id The ID of the form.
	 * @param string[] $values  Array of values.
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 *
	 * @return string|int The result of admin options updating.
	 */
	public function settings_updating( $update, $form_id, $values ) {

		$settings = array(
			'admin_notices'          => $this->setting_value( $values, 'admin_notices' ),
			'frontend_notice'        => $this->setting_value( $values, 'frontend_notice' ),
			'admin_color'            => $this->setting_value( $values, 'admin_color' ),
			'ajax_submission'        => $values['ajax_submission'],
			'spinner'                => $values['spinner'],
			'html5_validation'       => $values['html5'],
			'focus'                  => $values['focus'],
			'form_template'          => $values['form_style'],
			'stylesheet'             => $this->setting_value( $values, 'stylesheet' ),
			'stylesheet_file'        => $this->setting_value( $values, 'stylesheet_file' ),
			'javascript'             => $this->setting_value( $values, 'javascript' ),
			'deletion_data'          => $this->setting_value( $values, 'deletion_data' ),
			'multiple_spaces'        => $values['multiple_spaces'],
			'outside_error'          => $values['outside_error'],
			'empty_fields'           => $values['empty_fields'],
			'characters_length'      => $values['characters_length'],
			'empty_name'             => $values['empty_name'],
			'numeric_name'           => $values['numeric_name'],
			'generic_name'           => $values['generic_name'],
			'invalid_name'           => $values['invalid_name'],
			'name_error'             => $values['name_error'],
			'empty_lastname'         => $values['empty_lastname'],
			'numeric_lastname'       => $values['numeric_lastname'],
			'generic_lastname'       => $values['generic_lastname'],
			'invalid_lastname'       => $values['invalid_lastname'],
			'lastname_error'         => $values['lastname_error'],
			'empty_email'            => $values['empty_email'],
			'invalid_email'          => $values['invalid_email'],
			'email_error'            => $values['email_error'],
			'empty_phone'            => $values['empty_phone'],
			'invalid_phone'          => $values['invalid_phone'],
			'phone_error'            => $values['phone_error'],
			'empty_website'          => $values['empty_website'],
			'invalid_website'        => $values['invalid_website'],
			'website_error'          => $values['website_error'],
			'empty_subject'          => $values['empty_subject'],
			'numeric_subject'        => $values['numeric_subject'],
			'generic_subject'        => $values['generic_subject'],
			'invalid_subject'        => $values['invalid_subject'],
			'subject_error'          => $values['subject_error'],
			'empty_message'          => $values['empty_message'],
			'numeric_message'        => $values['numeric_message'],
			'generic_message'        => $values['generic_message'],
			'invalid_message'        => $values['invalid_message'],
			'message_error'          => $values['message_error'],
			'consent_error'          => $values['consent_error'],
			'empty_captcha'          => $values['empty_captcha'],
			'invalid_captcha'        => $values['invalid_captcha'],
			'captcha_error'          => $values['captcha_error'],
			'honeypot_error'         => $this->setting_value( $values, 'honeypot_error' ),
			'duplicate_error'        => $this->setting_value( $values, 'duplicate_error' ),
			'ajax_error'             => $values['ajax_error'],
			'server_error'           => $this->setting_value( $values, 'server_error' ),
			'success_action'         => $values['success_action'],
			'success_message'        => $values['success_message'],
			'confirmation_page'      => $values['redirect_page'],
			'thanks_url'             => ! empty( $values['redirect_page'] ) ? esc_url_raw( get_the_guid( (int) $values['redirect_page'] ) ) : '',
			'server_smtp'            => $this->setting_value( $values, 'server_smtp' ),
			'smtp_host'              => $this->setting_value( $values, 'smtp_host' ),
			'smtp_encryption'        => $this->setting_value( $values, 'smtp_encryption' ),
			'smtp_port'              => $this->setting_value( $values, 'smtp_port' ),
			'smtp_authentication'    => $this->setting_value( $values, 'smtp_authentication' ),
			'smtp_username'          => $this->setting_value( $values, 'smtp_username' ),
			'smtp_password'          => $this->setting_value( $values, 'smtp_password' ),
			'notification'           => $values['notification'],
			'notification_recipient' => str_replace( ' ', '', $values['recipients'] ),
			'bcc'                    => $values['bcc'],
			'notification_email'     => $values['alert_from'],
			'notification_name'      => $values['alert_name'],
			'custom_sender'          => $values['alert_sender'],
			'notification_subject'   => $values['alert_subject'],
			'custom_subject'         => $values['custom_subject'],
			'notification_reply'     => $values['alert_reply'],
			'submission_number'      => $values['submission_number'],
			'autoresponder'          => $values['autoresponder'],
			'autoresponder_email'    => $values['autoreply_email'],
			'autoresponder_name'     => $values['autoreply_name'],
			'autoresponder_subject'  => $values['autoreply_subject'],
			'autoresponder_message'  => $values['autoreply_message'],
			'autoresponder_reply'    => $values['autoreply_reply'],
			'duplicate'              => $this->setting_value( $values, 'duplicate' ),
			'form_pageid'            => $values['form_pageid'],
			'confirmation_pageid'    => $values['confirmation_pageid'],
		);

		$entries_settings    = array_merge( $settings, apply_filters( 'submissions_settings_storing', array( 'additional_fields' => '' ) ) );
		$additional_settings = array_merge( $entries_settings, apply_filters( 'akismet_settings_storing', array( 'additional_fields' => '' ) ) );
		$extra_settings      = array_merge( $additional_settings, apply_filters( 'recaptcha_settings_storing', array( 'additional_fields' => '' ) ) );
		$update_settings     = 1 === $form_id ? update_option( 'sform_settings', $extra_settings ) : update_option( "sform_{$form_id}_settings", $extra_settings );

		if ( $update_settings ) {
			$update = $form_id;
			$util   = new SimpleForm_Util();
			$util->enqueue_additional_scripts( $form_id );
		}

		return $update;
	}
}

new SimpleForm_Admin_Processing();
