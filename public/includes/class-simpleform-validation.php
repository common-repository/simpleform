<?php
/**
 * File delegated to validate the form submitted.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/public/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the form validation.
 */
class SimpleForm_Validation {

	/**
	 * Class constructor
	 *
	 * @since  2.1.7
	 */
	public function __construct() {

		// Prevent duplicate form submission.
		add_filter( 'sform_block_duplicate', array( $this, 'block_duplicate' ), 10, 5 );
		// Apply the spam filter with Akismet if active.
		add_filter( 'sform_spam_filter', array( $this, 'spam_filter' ), 10, 4 );
	}

	/**
	 * Process the form data after submission with post callback function
	 *
	 * @since 1.0.0
	 * @version 2.1.7
	 *
	 * @param string $flagged  The result of Akismet check, empty if spam does not exist.
	 * @param string $name     The sanitized name value entered in the form.
	 * @param string $email    The sanitized email value entered in the form.
	 * @param string $message  The sanitized message value entered in the form.
	 *
	 * @return string|mixed[] The error found after Akismet validation.
	 */
	public function spam_filter( $flagged, $name, $email, $message ) {

		if ( has_filter( 'akismet_action' ) ) {
			$flagged = apply_filters( 'akismet_action', $flagged, $name, $email, $message );
		}

		return $flagged;
	}

	/**
	 * Sanitize form data
	 *
	 * @since 2.2.0
	 *
	 * @param string $field The ID of input field.
	 * @param string $type  The type of input field.
	 *
	 * @return mixed The sanitized value entered in the form field.
	 */
	public function sanitized_input( $field, $type ) {

		if ( isset( $_POST[ $field ] ) && isset( $_POST['sform_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['sform_nonce'] ), 'sform_nonce_action' ) ) {

			$sanitized_value = array(
				'form'     => absint( $_POST[ $field ] ),
				'submit'   => true,
				'number'   => absint( $_POST[ $field ] ),
				'checkbox' => true,
				'textarea' => sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ),
				'text'     => sanitize_text_field( wp_unslash( $_POST[ $field ] ) ),
				'website'  => esc_url_raw( wp_unslash( $_POST[ $field ] ) ),
			);

			$value = $sanitized_value[ $type ];

		} else {

			$default_value = array(
				'form'     => 1,
				'submit'   => false,
				'number'   => '0',
				'checkbox' => false,
				'textarea' => '',
				'text'     => '',
				'website'  => '',
			);

			$value = $default_value[ $type ];

		}

		return $value;
	}

	/**
	 * Sanitize form data
	 *
	 * @since 2.1.7
	 *
	 * @return mixed[] Array of sanitized values entered in the form.
	 */
	public function sanitized_data() {

		$values                   = array();
		$values['form']           = $this->sanitized_input( 'form-id', 'form' );
		$values['submission']     = $this->sanitized_input( 'submission', 'submit' );
		$values['name']           = $this->sanitized_input( 'sform-name', 'text' );
		$values['lastname']       = $this->sanitized_input( 'sform-lastname', 'text' );
		$values['email']          = $this->sanitized_input( 'sform-email', 'text' );
		$values['phone']          = $this->sanitized_input( 'sform-phone', 'text' );
		$values['website']        = $this->sanitized_input( 'sform-website', 'website' );
		$values['subject']        = $this->sanitized_input( 'sform-subject', 'text' );
		$values['message']        = $this->sanitized_input( 'sform-message', 'textarea' );
		$values['consent']        = $this->sanitized_input( 'sform-consent', 'checkbox' );
		$values['captcha_one']    = $this->sanitized_input( 'captcha_one', 'number' );
		$values['captcha_two']    = $this->sanitized_input( 'captcha_two', 'number' );
		$values['captcha_answer'] = $this->sanitized_input( 'sform-captcha', 'number' );
		$values['honeyurl']       = $this->sanitized_input( 'url-site', 'text' );
		$values['honeytel']       = $this->sanitized_input( 'hobbies', 'text' );
		$values['honeycheck']     = $this->sanitized_input( 'contact-phone', 'checkbox' );

		return $values;
	}

	/**
	 * Extract submitter data
	 *
	 * @since 2.1.7
	 *
	 * @param string $name     The sanitized name value entered in the form.
	 * @param string $lastname The sanitized lastname value entered in the form.
	 * @param string $email    The sanitized email value entered in the form.
	 *
	 * @return string[] The submitter data.
	 */
	public function submitter_data( $name, $lastname, $email ) {

		$submitter_data = array();

		if ( is_user_logged_in() ) {

			$current_user = wp_get_current_user();
			$user_name    = ! empty( $current_user->user_firstname ) ? $current_user->user_firstname : $current_user->user_login;
			$name         = empty( $name ) ? $user_name : $name;
			$lastname     = empty( $lastname ) ? $current_user->user_lastname : $lastname;
			$email        = empty( $email ) ? $current_user->user_email : $email;

		}

		$name      = empty( $name ) ? __( 'Anonymous', 'simpleform' ) : $name;
		$submitter = __( 'Anonymous', 'simpleform' ) !== $name && ! empty( trim( $name . ' ' . $lastname ) ) ? trim( $name . ' ' . $lastname ) : __( 'Anonymous', 'simpleform' );

		$submitter_data['name']      = $name;
		$submitter_data['lastname']  = $lastname;
		$submitter_data['submitter'] = $submitter;
		$submitter_data['email']     = $email;

		return $submitter_data;
	}

	/**
	 * Prevent duplicate form submission
	 *
	 * @since 2.1.7
	 *
	 * @param mixed[] $errors    Array of errors found during form validation.
	 * @param int     $form_id   The ID of the form.
	 * @param string  $submitter The submitter data.
	 * @param string  $email     The sanitized email value entered in the form.
	 * @param string  $message   The sanitized message value entered in the form.
	 *
	 * @return string|mixed[] The error found after form submission.
	 */
	public function block_duplicate( $errors, $form_id, $submitter, $email, $message ) {

		$util         = new SimpleForm_Util();
		$ajax         = $util->get_sform_option( $form_id, 'settings', 'ajax_submission', false );
		$duplicate    = $util->get_sform_option( $form_id, 'settings', 'duplicate', true );
		$last_request = false !== get_option( 'sform_last_message' ) ? strval( get_option( 'sform_last_message' ) ) : '';

		if ( $duplicate && $last_request ) {

			$separator        = '<tr><td class="message">' . __( 'Message', 'simpleform' ) . ':</td><td>';
			$previous_request = isset( explode( $separator, $last_request )[1] ) ? str_replace( '</td></tr></tbody></table>', '', explode( $separator, $last_request )[1] ) : '';

			if ( $previous_request === $message ) {

				$string1        = '<table class="table-msg"><tbody><tr><td>' . __( 'From', 'simpleform' ) . ':</td><td>';
				$string2        = '</td></tr>';
				$submitter_data = explode( $string2, str_replace( $string1, '', explode( $separator, $last_request )[0] ) )[0];

				if ( strpos( $submitter_data, $submitter ) !== false && strpos( $submitter_data, $email ) !== false ) {

					$error = $util->get_sform_option( 1, 'settings', 'duplicate_error', __( 'The form has already been submitted. Thanks!', 'simpleform' ) );

					if ( $ajax ) {
						$errors['error']       = true;
						$errors['showerror']   = true;
						$errors['field_focus'] = false;
						$errors['notice']      = $error;
					} else {
						$errors = $form_id . ';duplicate_form;';
					}
				}
			}
		}

		return $errors;
	}

	/**
	 * Form fields validation
	 *
	 * @since 2.1.7
	 *
	 * @param mixed[] $values Array of sanitized values entered in the form.
	 *
	 * @return string|mixed[] The error found after form fields validation.
	 */
	public function fields_validation( $values ) {

		$util           = new SimpleForm_Util();
		$form_id        = intval( $values['form'] );
		$name           = $values['name'];
		$lastname       = $values['lastname'];
		$email          = $values['email'];
		$phone          = $values['phone'];
		$website        = $values['website'];
		$subject        = $values['subject'];
		$message        = $values['message'];
		$consent        = $values['consent'];
		$captcha_one    = $values['captcha_one'];
		$captcha_two    = $values['captcha_two'];
		$captcha_answer = $values['captcha_answer'];
		$honeyurl       = $values['honeyurl'];
		$honeytel       = $values['honeytel'];
		$honeycheck     = $values['honeycheck'];
		$ajax           = $util->get_sform_option( $form_id, 'settings', 'ajax_submission', false );
		$errors         = ! $ajax ? '' : array();

		// Make honeypot fields validation first.
		if ( ! empty( $honeyurl ) || ! empty( $honeytel ) || true === $honeycheck ) {
			$message = $util->get_sform_option( 1, 'settings', 'honeypot_error', __( 'Error occurred during processing data', 'simpleform' ) );
			// $errors is an array if $ajax enabled
			if ( is_array( $errors ) ) {
				$errors['error']       = true;
				$errors['showerror']   = true;
				$errors['field_focus'] = false;
				$errors['notice']      = $message;
			} else {
				$errors = $form_id . ';form_honeypot;';
			}
		}

		// Continue with the fields validation.
		if ( empty( $errors ) ) {

			// Name validation.
			$errors = apply_filters( 'text_field_validation', $errors, $form_id, 'name', $name );

			// Lastname validation.
			$errors = apply_filters( 'text_field_validation', $errors, $form_id, 'lastname', $lastname );

			// Email validation.
			$errors = apply_filters( 'email_field_validation', $errors, $form_id, $email );

			// Phone validation.
			$errors = apply_filters( 'text_field_validation', $errors, $form_id, 'phone', $phone );

			// Website validation.
			$errors = apply_filters( 'website_field_validation', $errors, $form_id, $website );

			// Subject validation.
			$errors = apply_filters( 'text_field_validation', $errors, $form_id, 'subject', $subject );

			// Message validation.
			$errors = apply_filters( 'text_field_validation', $errors, $form_id, 'message', $message );

			// Consent validation.
			$errors = apply_filters( 'checkbox_field_validation', $errors, $form_id, $consent );

			// Captcha validation.
			$errors = apply_filters( 'captcha_field_validation', $errors, $form_id, $captcha_one, $captcha_two, $captcha_answer );

		}

		return $errors;
	}
}

new SimpleForm_Validation();
