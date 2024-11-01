<?php
/**
 * File delegated to the admin settings filtering.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the detecting admin settings validation errors.
 */
class SimpleForm_Admin_Errors {

	/**
	 * Class constructor
	 *
	 * @since 2.2.0
	 */
	public function __construct() {

		// Filter for form name validation.
		add_filter( 'form_name_validation', array( $this, 'form_name_validation' ), 10, 4 );
		// Filter for a text field length validation.
		add_filter( 'field_length_validation', array( $this, 'field_length_validation' ), 10, 5 );
		// Filter for fields alignment validation.
		add_filter( 'fields_alignment_validation', array( $this, 'fields_alignment_validation' ), 10, 9 );
		// Filter for fields alignment validation.
		add_filter( 'focus_validation', array( $this, 'focus_validation' ), 10, 3 );
		// Filter for fields alignment validation.
		add_filter( 'smtp_server_validation', array( $this, 'smtp_server_validation' ), 10, 2 );
		// Filter for fields alignment validation.
		add_filter( 'smtp_login_validation', array( $this, 'smtp_login_validation' ), 10, 2 );
		// Filter for form status validation.
		add_filter( 'form_status_validation', array( $this, 'form_status_validation' ), 10, 3 );
		// Filter for messages moving validation.
		add_filter( 'moving_validation', array( $this, 'moving_validation' ), 10, 5 );
	}

	/**
	 * Validate the form name
	 *
	 * @since 2.2.0
	 *
	 * @param string $error     The error found during options validation.
	 * @param string $newform   The sanitized value entered in the field.
	 * @param int    $form_id   The ID of the form.
	 * @param string $form_name The sanitized value entered in the field.
	 *
	 * @return string The validation error.
	 */
	public function form_name_validation( $error, $newform, $form_id, $form_name ) {

		if ( empty( $form_name ) ) {

			$error = __( 'Enter a name for this form', 'simpleform' );

		}

		if ( ! empty( $newform ) && empty( $form_name ) ) {

			$error = __( 'Enter a name for the new form', 'simpleform' );

		}

		$form_name_list = array();

		// Do a database query and save it to the cache if the there is no cache data with this key.
		if ( false === wp_cache_get( 'sform_shortcodes_names' ) ) {
			global $wpdb;
			$form_name_list = $wpdb->get_col( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}sform_shortcodes WHERE id != %d", $form_id ) ); // phpcs:ignore.
			wp_cache_set( 'sform_shortcodes_names', $form_name_list );
		}

		if ( in_array( $form_name, $form_name_list, true ) ) {

			$error = __( 'The name has already been used for another form, please use another one', 'simpleform' );
		}

		return $error;
	}

	/**
	 * Validate the length of a text field
	 *
	 * @since 2.2.0
	 *
	 * @param string $error     The error found during settings validation.
	 * @param string $field     The ID of the field.
	 * @param int    $minlength The minimum number of characters required in the text field.
	 * @param int    $maxlength The maximum number of characters required in the text field.
	 * @param string $required  The required attribute of the text field.
	 *
	 * @return string The validation error.
	 */
	public function field_length_validation( $error, $field, $minlength, $maxlength, $required ) {

		$maxlength_error = array(
			'name'     => __( 'The maximum name length must not be less than the minimum name length' ),
			'lastname' => __( 'The maximum last name length must not be less than the minimum last name length', 'simpleform' ),
			'subject'  => __( 'The maximum subject length must not be less than the minimum subject length', 'simpleform' ),
			'message'  => __( 'The maximum message length must not be less than the minimum message length', 'simpleform' ),
		);

		$minlength_error = array(
			'name'     => __( 'You cannot set up a minimum length equal to 0 if the name field is required', 'simpleform' ),
			'lastname' => __( 'You cannot set up a minimum length equal to 0 if the last name field is required', 'simpleform' ),
			'subject'  => __( 'You cannot set up a minimum length equal to 0 if the subject field is required', 'simpleform' ),
			'message'  => __( 'You cannot set up a minimum length equal to 0 since the message field is required', 'simpleform' ),
		);

		if ( $maxlength <= $minlength && 0 !== $maxlength ) {

			$error = $maxlength_error[ $field ];

		}

		if ( 0 === $minlength && 'required' === $required ) {

			$error = $minlength_error[ $field ];

		}

		return $error;
	}

	/**
	 * Validate the alignment of the fields
	 *
	 * @since 2.2.0
	 *
	 * @param string $error               The error found during options validation.
	 * @param string $name_visibility     The visibility for the name field label.
	 * @param string $lastname_visibility The visibility for the last name field label.
	 * @param string $email_visibility    The visibility for the email field label.
	 * @param string $phone_visibility    The visibility for the phone field label.
	 * @param string $subject_visibility  The visibility for the subject field label.
	 * @param string $message_visibility  The visibility for the message field label.
	 * @param string $label_position      The position of the labels.
	 * @param string $form_direction      The form direction.
	 *
	 * @return string The validation error.
	 */
	public function fields_alignment_validation( $error, $name_visibility, $lastname_visibility, $email_visibility, $phone_visibility, $subject_visibility, $message_visibility, $label_position, $form_direction ) {

		if ( ( 'hidden' === $name_visibility || 'hidden' === $lastname_visibility || 'hidden' === $email_visibility || 'hidden' === $phone_visibility || 'hidden' === $subject_visibility || 'hidden' === $message_visibility ) && 'inline' === $label_position ) {

			$error = 'ltr' === $form_direction ? __( 'Labels cannot be left aligned if you have set a field label as hidden', 'simpleform' ) : __( 'Labels cannot be right aligned if you have set a field label as hidden', 'simpleform' );

		}

		return $error;
	}

	/**
	 * Validation of focus on the fields
	 *
	 * @since 2.2.0
	 *
	 * @param string $error The error found during settings validation.
	 * @param bool   $html5 The browser default form validation.
	 * @param string $focus The focus on form errors.
	 *
	 * @return string The validation error.
	 */
	public function focus_validation( $error, $html5, $focus ) {

		if ( ! $html5 && 'alert' === $focus ) {

			$error = __( 'Focus is automatically set to first invalid field if HTML5 validation is not disabled', 'simpleform' );

		}

		return $error;
	}

	/**
	 * Validation of SMTP server
	 *
	 * @since 2.2.0
	 *
	 * @param string   $error         The error found during settings validation.
	 * @param string[] $smtp_settings Array of SMTP server settings.
	 *
	 * @return string The validation error.
	 */
	public function smtp_server_validation( $error, $smtp_settings ) {

		$server_smtp = $smtp_settings['server_smtp'];
		$smtp_host   = $smtp_settings['smtp_host'];
		$smtp_port   = $smtp_settings['smtp_port'];

		if ( $server_smtp ) {

			if ( empty( $smtp_host ) ) {

				$error = __( 'Please enter the SMTP address', 'simpleform' );

			}

			if ( $smtp_port <= 0 || $smtp_port > 2525 ) {

				$error = __( 'Please enter a valid port to relay outgoing email to the SMTP server', 'simpleform' );

			}
		}

		return $error;
	}

	/**
	 * Validation of SMTP server login
	 *
	 * @since 2.2.0
	 *
	 * @param string   $error         The error found during settings validation.
	 * @param string[] $smtp_settings Array of SMTP server settings.
	 *
	 * @return string The validation error.
	 */
	public function smtp_login_validation( $error, $smtp_settings ) {

		$server_smtp         = $smtp_settings['server_smtp'];
		$smtp_authentication = $smtp_settings['smtp_authentication'];
		$username            = defined( 'SFORM_SMTP_USERNAME' ) ? SFORM_SMTP_USERNAME : $smtp_settings['smtp_username'];
		$password            = defined( 'SFORM_SMTP_PASSWORD' ) ? SFORM_SMTP_PASSWORD : $smtp_settings['smtp_password'];

		if ( $server_smtp && $smtp_authentication ) {

			if ( empty( $username ) ) {

				$error = __( 'Please enter username to log in to SMTP server', 'simpleform' );

			}

			if ( ! is_email( $username ) ) {

				$error = __( 'Please enter a valid email address to log in to SMTP server', 'simpleform' );

			}

			if ( empty( $password ) ) {

				$error = __( 'Please enter password to log in to SMTP server', 'simpleform' );

			}
		}

		return $error;
	}

	/**
	 * Validation of form status
	 *
	 * @since 2.2.0
	 *
	 * @param string $error       The error found during settings validation.
	 * @param int    $form_id     The form ID.
	 * @param string $form_status The form status.
	 *
	 * @return string The validation error.
	 */
	public function form_status_validation( $error, $form_id, $form_status ) {

		$util     = new SimpleForm_Util();
		$form_ids = $util->sform_ids();
		// Make sure the array is an array of integer.
		$form_ids = array_map( 'intval', $form_ids );

		if ( ! in_array( $form_id, $form_ids, true ) ) {
			$error = __( 'The form has been permanently deleted', 'simpleform' );
		} else {

			$unallowed_error = __( 'It is not allowed to make changes on a trashed form', 'simpleform' );

			$error = 'trash' === $form_status ? $unallowed_error : $error;

		}

		return $error;
	}

	/**
	 * Validation of SMTP server login
	 *
	 * @since 2.2.0
	 *
	 * @param string $error    The error found during settings validation.
	 * @param bool   $transfer The transfer of messages.
	 * @param int    $moveto   The form ID to move messages to.
	 * @param string $be_moved The type of messages to be moved.
	 * @param bool   $restore  The restore of moved messages.
	 *
	 * @return string The validation error.
	 */
	public function moving_validation( $error, $transfer, $moveto, $be_moved, $restore ) {

		if ( $transfer ) {
			if ( 0 === $moveto ) {
				$error = __( 'Select a form to move entries to', 'simpleform' );

			} else {
				if ( '' === $be_moved ) {
					$error = __( 'Select entries to be moved', 'simpleform' );
				}
				if ( '' !== $be_moved && 'next' !== $be_moved && $restore ) {
					$error = __( 'It is not allowed to transfer and restore entries at the same time', 'simpleform' );
				}
			}
		}

		return $error;
	}
}

new SimpleForm_Admin_Errors();
