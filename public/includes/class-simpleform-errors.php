<?php
/**
 * File delegated to the submitted data filtering.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/public/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the detecting form validation errors.
 */
class SimpleForm_Errors {

	/**
	 * Class constructor
	 *
	 * @since  2.2.0
	 */
	public function __construct() {

		// Filter for a text field validation.
		add_filter( 'text_field_validation', array( $this, 'text_field_validation' ), 10, 4 );
		// Filter for an email field validation.
		add_filter( 'email_field_validation', array( $this, 'email_field_validation' ), 10, 3 );
		// Filter for an website field validation.
		add_filter( 'website_field_validation', array( $this, 'website_field_validation' ), 10, 3 );
		// Filter for a checkbox field validation.
		add_filter( 'checkbox_field_validation', array( $this, 'checkbox_field_validation' ), 10, 3 );
		// Filter for the math captcha field validation.
		add_filter( 'captcha_field_validation', array( $this, 'captcha_field_validation' ), 10, 5 );
	}

	/**
	 * Validate a text value entered in the form
	 *
	 * @since 2.2.0
	 *
	 * @param string|mixed[] $errors  The errors found during form validation.
	 * @param int            $form_id The ID of the form.
	 * @param string         $field   The ID of the field.
	 * @param string         $value   The sanitized value entered in the form field.
	 *
	 * @return string|mixed[] The error found in text field validation.
	 */
	public function text_field_validation( $errors, $form_id, $field, $value ) {

		$default_view = array(
			'name'     => 'visible',
			'lastname' => 'hidden',
			'phone'    => 'hidden',
			'subject'  => 'visible',
			'message'  => 'visible',
		);

		$default_required = array(
			'name'     => 'required',
			'lastname' => 'optional',
			'phone'    => 'optional',
			'subject'  => 'required',
			'message'  => 'required',
		);

		$default_minlength = array(
			'name'     => '2',
			'lastname' => '2',
			'phone'    => '2',
			'subject'  => '5',
			'message'  => '10',
		);

		$field_regex = array(
			'name'     => '#[0-9]+#',
			'lastname' => '#[0-9]+#',
			'phone'    => '#[^\# 0-9\-\(\)\/\+]+#',
			'subject'  => '#[\#$%&=+*{}|<>]+#',
			'message'  => '#[\#$%&=+*{}|<>]+#',
		);

		$default_invalid = array(
			'name'     => __( 'The name contains invalid characters', 'simpleform' ),
			'lastname' => __( 'The last name contains invalid characters', 'simpleform' ),
			'phone'    => __( 'The phone number contains invalid characters', 'simpleform' ),
			'subject'  => __( 'Enter only alphanumeric characters and punctuation marks', 'simpleform' ),
			'message'  => __( 'Enter only alphanumeric characters and punctuation marks', 'simpleform' ),
		);

		$default_error = array(
			'name'     => __( 'Error occurred validating the name', 'simpleform' ),
			'lastname' => __( 'Error occurred validating the last name', 'simpleform' ),
			'phone'    => __( 'Error occurred validating the phone number', 'simpleform' ),
			'subject'  => __( 'Error occurred validating the subject', 'simpleform' ),
			'message'  => __( 'Error occurred validating the message', 'simpleform' ),
		);

		$display = new SimpleForm_Display();

		if ( $display->field_visibility( $form_id, $field . '_field', $default_view[ $field ] ) ) {

			$util           = new SimpleForm_Util();
			$required       = strval( $util->get_sform_option( $form_id, 'attributes', $field . '_requirement', $default_required[ $field ] ) );
			$minimum_length = absint( $util->get_sform_option( $form_id, 'attributes', $field . '_minlength', $default_minlength[ $field ] ) );
			$empty_phone    = $util->get_sform_option( $form_id, 'settings', 'empty_phone', __( 'Please provide your phone number', 'simpleform' ) );
			$length_error   = 'phone' !== $field ? $this->length_error( $form_id, $field, $minimum_length ) : $empty_phone;
			$invalid_error  = $util->get_sform_option( $form_id, 'settings', 'invalid_' . $field, $default_invalid[ $field ] );
			$error          = strval( $util->get_sform_option( $form_id, 'settings', $field . '_error', $default_error[ $field ] ) );
			$more_errors    = strval( $util->get_sform_option( $form_id, 'settings', 'empty_fields', __( 'There were some errors that need to be fixed', 'simpleform' ) ) );
			$outside_error  = $util->get_sform_option( $form_id, 'settings', 'outside_error', 'bottom' );
			$showerror      = $this->display_splitted_error( strval( $outside_error ) );

			if ( ! empty( $value ) && preg_match( $field_regex[ $field ], $value ) ) {
				$error_type = $invalid_error;
				$error_code = ';' . $field . '_invalid;';
			} else {
				$error_type = $length_error;
				$error_code = ';' . $field . ';';
			}

			if ( $this->text_field_error( $value, $required, $minimum_length, $field_regex[ $field ] ) ) {
				// $errors is an array if ajax enabled.
				if ( is_array( $errors ) ) {
					$errors['notice']    = $this->display_multiple_error( $errors, $error, $more_errors );
					$errors['error']     = true;
					$errors['showerror'] = $showerror;
					$errors[ $field ]    = $error_type;
					$errors['label']     = $error_type;
				} else {
					$errors .= $form_id . $error_code;
				}
			}
		}

		return $errors;
	}

	/**
	 * Validate the email value entered in the form
	 *
	 * @since 2.2.0
	 *
	 * @param string|mixed[] $errors  The errors found during form validation.
	 * @param int            $form_id The ID of the form.
	 * @param string         $email   The email address entered in the form.
	 *
	 * @return string|mixed[] The error found in email validation.
	 */
	public function email_field_validation( $errors, $form_id, $email ) {

		$display = new SimpleForm_Display();

		if ( $display->field_visibility( $form_id, 'email_field', 'visible' ) ) {

			$util          = new SimpleForm_Util();
			$required      = $util->get_sform_option( $form_id, 'attributes', 'email_requirement', 'required' );
			$invalid_error = $util->get_sform_option( $form_id, 'settings', 'invalid_email', __( 'Please enter a valid email', 'simpleform' ) );
			$error         = strval( $util->get_sform_option( $form_id, 'settings', 'email_error', __( 'Error occurred validating the email', 'simpleform' ) ) );
			$more_errors   = strval( $util->get_sform_option( $form_id, 'settings', 'empty_fields', __( 'There were some errors that need to be fixed', 'simpleform' ) ) );
			$outside_error = $util->get_sform_option( $form_id, 'settings', 'outside_error', 'bottom' );
			$showerror     = $this->display_splitted_error( strval( $outside_error ) );

			if ( ( 'required' === $required && empty( $email ) ) || ( ! empty( $email ) && ! is_email( $email ) ) ) {
				// $errors is an array if ajax enabled.
				if ( is_array( $errors ) ) {
					$errors['notice']    = $this->display_multiple_error( $errors, $error, $more_errors );
					$errors['error']     = true;
					$errors['showerror'] = $showerror;
					$errors['email']     = $invalid_error;
				} else {
					$errors .= $form_id . ';email;';
				}
			}
		}

		return $errors;
	}

	/**
	 * Validate the url value entered in the form
	 *
	 * @since 2.2.0
	 *
	 * @param string|mixed[] $errors  The errors found during form validation.
	 * @param int            $form_id The ID of the form.
	 * @param string         $website The website address entered in the form.
	 *
	 * @return string|mixed[] The error found in url validation.
	 */
	public function website_field_validation( $errors, $form_id, $website ) {

		$display = new SimpleForm_Display();

		if ( $display->field_visibility( $form_id, 'website_field', 'hidden' ) ) {

			$util          = new SimpleForm_Util();
			$required      = $util->get_sform_option( $form_id, 'attributes', 'website_requirement', 'optional' );
			$invalid_error = $util->get_sform_option( $form_id, 'settings', 'invalid_website', __( 'Please enter a valid URL', 'simpleform' ) );
			$error         = strval( $util->get_sform_option( $form_id, 'settings', 'website_error', __( 'Error occurred validating the URL', 'simpleform' ) ) );
			$more_errors   = strval( $util->get_sform_option( $form_id, 'settings', 'empty_fields', __( 'There were some errors that need to be fixed', 'simpleform' ) ) );
			$outside_error = $util->get_sform_option( $form_id, 'settings', 'outside_error', 'bottom' );
			$showerror     = $this->display_splitted_error( strval( $outside_error ) );

			if ( ( 'required' === $required && empty( $website ) ) || ( ! empty( $website ) && ! wp_http_validate_url( $website ) ) ) {

				// $errors is an array if ajax enabled.
				if ( is_array( $errors ) ) {
					$errors['notice']    = $this->display_multiple_error( $errors, $error, $more_errors );
					$errors['error']     = true;
					$errors['showerror'] = $showerror;
					$errors['website']   = $invalid_error;
				} else {
					$errors .= $form_id . ';website;';
				}
			}
		}

		return $errors;
	}

	/**
	 * Validate the consent value entered in the form
	 *
	 * @since 2.2.0
	 *
	 * @param string|mixed[] $errors  The errors found during form validation.
	 * @param int            $form_id The ID of the form.
	 * @param bool           $value   The value entered in the checkbox field.
	 *
	 * @return string|mixed[] The error found in checkbox field validation.
	 */
	public function checkbox_field_validation( $errors, $form_id, $value ) {

		$display = new SimpleForm_Display();

		if ( $display->field_visibility( $form_id, 'consent_field', 'visible' ) ) {

			$util          = new SimpleForm_Util();
			$required      = $util->get_sform_option( $form_id, 'attributes', 'consent_requirement', 'required' );
			$error         = strval( $util->get_sform_option( $form_id, 'settings', 'consent_error', __( 'Please accept our privacy policy before submitting form', 'simpleform' ) ) );
			$more_errors   = strval( $util->get_sform_option( $form_id, 'settings', 'empty_fields', __( 'There were some errors that need to be fixed', 'simpleform' ) ) );
			$outside_error = $util->get_sform_option( $form_id, 'settings', 'outside_error', 'bottom' );
			$showerror     = $this->display_splitted_error( strval( $outside_error ) );

			if ( 'required' === $required && ! $value ) {
				// $errors is an array if ajax enabled.
				if ( is_array( $errors ) ) {
					$errors['notice']    = $this->display_multiple_error( $errors, $error, $more_errors );
					$errors['error']     = true;
					$errors['showerror'] = $showerror;
					$errors['consent']   = $error;
				} else {
					$errors .= $form_id . ';consent;';
				}
			}
		}

		return $errors;
	}

	/**
	 * Validate the captcha value entered in the form
	 *
	 * @since 2.2.0
	 *
	 * @param string|mixed[] $errors         The errors found during form validation.
	 * @param int            $form_id        The ID of the form.
	 * @param int            $captcha_one    The first number to sum.
	 * @param int            $captcha_two    The second number to sum.
	 * @param int            $captcha_answer The value entered in the captcha field.
	 *
	 * @return string|mixed[] The error found in captcha field validation.
	 */
	public function captcha_field_validation( $errors, $form_id, $captcha_one, $captcha_two, $captcha_answer ) {

		$display = new SimpleForm_Display();

		if ( $display->field_visibility( $form_id, 'captcha_field', 'hidden' ) ) {

			$captcha_question = $captcha_one + $captcha_two;

			if ( has_filter( 'recaptcha_spam_detection' ) ) {

				$errors = apply_filters( 'recaptcha_spam_detection', $errors, $form_id, $captcha_question, $captcha_answer );

			} else {

				$util          = new SimpleForm_Util();
				$captcha_error = $util->get_sform_option( $form_id, 'settings', 'invalid_captcha', __( 'Please enter a valid captcha value', 'simpleform' ) );
				$error         = strval( $util->get_sform_option( $form_id, 'settings', 'captcha_error', __( 'Error occurred validating the captcha', 'simpleform' ) ) );
				$more_errors   = strval( $util->get_sform_option( $form_id, 'settings', 'empty_fields', __( 'There were some errors that need to be fixed', 'simpleform' ) ) );
				$outside_error = $util->get_sform_option( $form_id, 'settings', 'outside_error', 'bottom' );
				$showerror     = $this->display_splitted_error( strval( $outside_error ) );

				if ( $captcha_question !== $captcha_answer ) {
					// $errors is an array if ajax enabled.
					if ( is_array( $errors ) ) {
						$errors['notice']    = $this->display_multiple_error( $errors, $error, $more_errors );
						$errors['error']     = true;
						$errors['showerror'] = $showerror;
						$errors['captcha']   = $captcha_error;
					} else {
						$errors .= $form_id . ';captcha;';
					}
				}
			}
		}

		return $errors;
	}

	/**
	 * Validate the value entered in a text field
	 *
	 * @since 2.2.0
	 *
	 * @param string $value          The entered value into the text field to be validated.
	 * @param string $required       The required attribute of the text field.
	 * @param int    $minimum_length The minimum number of characters required in the text field.
	 * @param string $regex          The pattern describing which characters can be entered into the text field.
	 *
	 * @return bool True, if an error has been found in the text field. False otherwise.
	 */
	public function text_field_error( $value, $required, $minimum_length, $regex ) {

		$validation_error = false;

		if ( empty( $value ) && 'required' === $required ) {
			$validation_error = true;
		}

		if ( ! empty( $value ) && ( strlen( $value ) < $minimum_length || preg_match( $regex, $value ) ) ) {
			$validation_error = true;
		}

		return $validation_error;
	}

	/**
	 * Displaying of secondary error message
	 *
	 * @since 2.2.0
	 *
	 * @param string $outside_error The error display location.
	 *
	 * @return bool True, if an error message should be displayed. False otherwise.
	 */
	protected function display_splitted_error( $outside_error ) {

		$showerror = 'none' === $outside_error ? false : true;

		return $showerror;
	}

	/**
	 * Displaying of multiple errors message
	 *
	 * @since 2.2.0
	 *
	 * @param mixed[] $errors      The errors found during form validation.
	 * @param string  $error       The error message to show.
	 * @param string  $more_errors The multiple errors message.
	 *
	 * @return string The error message to show.
	 */
	protected function display_multiple_error( $errors, $error, $more_errors ) {

		if ( ! isset( $errors['error'] ) ) {
			$error_to_show = $error;
		} else {
			$error_to_show = $more_errors;
		}

		return $error_to_show;
	}

	/**
	 * Display the correct field length error
	 *
	 * @since 2.2.0
	 *
	 * @param int    $form_id        The ID of the form.
	 * @param string $field          The ID of the field.
	 * @param int    $minimum_length The minimum number of characters required in the field.
	 *
	 * @return string The error message.
	 */
	public function length_error( $form_id, $field, $minimum_length ) {

		$util                = new SimpleForm_Util();
		$fields_length_error = $util->get_sform_option( $form_id, 'settings', 'characters_length', true );
		$numeric_error       = '';
		$generic_error       = '';

		switch ( $field ) {
			case 'name' === $field:
				/* translators: %d: minimum length of the name */
				$numeric_error = strval( $util->get_sform_option( $form_id, 'settings', 'numeric_name', sprintf( __( 'Please enter a name at least %d characters long', 'simpleform' ), $minimum_length ) ) );
				$generic_error = strval( $util->get_sform_option( $form_id, 'settings', 'generic_name', __( 'Please type your full name', 'simpleform' ) ) );
				break;
			case 'lastname' === $field:
				/* translators: %d: minimum length of the lastname */
				$numeric_error = strval( $util->get_sform_option( $form_id, 'settings', 'numeric_lastname', sprintf( __( 'Please enter a last name at least %d characters long', 'simpleform' ), $minimum_length ) ) );
				$generic_error = strval( $util->get_sform_option( $form_id, 'settings', 'generic_lastname', __( 'Please type your full last name', 'simpleform' ) ) );
				break;
			case 'subject' === $field:
				/* translators: %d: minimum length of the subject */
				$numeric_error = strval( $util->get_sform_option( $form_id, 'settings', 'numeric_subject', sprintf( __( 'Please enter a subject at least %d characters long', 'simpleform' ), $minimum_length ) ) );
				$generic_error = strval( $util->get_sform_option( $form_id, 'settings', 'generic_subject', __( 'Please type a short and specific subject', 'simpleform' ) ) );
				break;
			case 'message' === $field:
				/* translators: %d: minimum length of the message */
				$numeric_error = strval( $util->get_sform_option( $form_id, 'settings', 'numeric_message', sprintf( __( 'Please enter a message at least %d characters long', 'simpleform' ), $minimum_length ) ) );
				$generic_error = strval( $util->get_sform_option( $form_id, 'settings', 'generic_message', __( 'Please type a clearer message so we can respond appropriately', 'simpleform' ) ) );
				break;
		}

		$error = $fields_length_error ? $numeric_error : $generic_error;

		return $error;
	}
}

new SimpleForm_Errors();
