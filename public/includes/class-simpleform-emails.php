<?php
/**
 * File delegated to the management of notifications
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/public/includes
 */

defined( 'ABSPATH' ) || exit;

// Import the PHPMailer class.
require_once ABSPATH . 'wp-includes/PHPMailer/PHPMailer.php';
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Defines the class that deals with the notification emails sending.
 */
class SimpleForm_Emails {

	/**
	 * Initialize the class and set its properties for later use.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Send alert email.
		add_filter( 'sform_alert', array( $this, 'alert_sending' ), 10, 9 );
		// Send auto-reply.
		add_action( 'sform_autoreply', array( $this, 'autoreply_sending' ), 10, 3 );
		// Register callback for enabling smtp server.
		add_action( 'check_smtp', array( $this, 'check_smtp_server' ) );
	}

	/**
	 * Enable SMTP server for outgoing emails
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function check_smtp_server() {

		$util        = new SimpleForm_Util();
		$server_smtp = $util->get_sform_option( 1, 'settings', 'server_smtp', false );

		if ( $server_smtp ) {
			add_action( 'phpmailer_init', array( $this, 'sform_enable_smtp_server' ) );
		} else {
			remove_action( 'phpmailer_init', 'sform_enable_smtp_server' );
		}
	}

	/**
	 * Save SMTP server configuration.
	 *
	 * Configure WP's PHP mailer to send emails from a SMTP account.
	 *
	 * @since 1.0.0
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/phpmailer_init
	 * @param PHPMailer $phpmailer The PHPMailer instance, passed by reference.
	 *
	 * @return void
	 */
	public function sform_enable_smtp_server( $phpmailer ) {

		$util                = new SimpleForm_Util();
		$smtp_host           = strval( $util->get_sform_option( 1, 'settings', 'smtp_host', '' ) );
		$smtp_encryption     = strval( $util->get_sform_option( 1, 'settings', 'smtp_encryption', '' ) );
		$smtp_port           = absint( $util->get_sform_option( 1, 'settings', 'smtp_port', '' ) );
		$smtp_authentication = (bool) $util->get_sform_option( 1, 'settings', 'smtp_authentication', '' );
		$smtp_username       = $util->get_sform_option( 1, 'settings', 'smtp_username', '' );
		$smtp_password       = $util->get_sform_option( 1, 'settings', 'smtp_password', '' );
		$username            = defined( 'SFORM_SMTP_USERNAME' ) ? SFORM_SMTP_USERNAME : $smtp_username;
		$password            = defined( 'SFORM_SMTP_PASSWORD' ) ? SFORM_SMTP_PASSWORD : $smtp_password;

		// Create a new PHPMailer object to access the class.
		$phpmailer = new PHPMailer();

		// Provide a SMTP configuration to PHPMailer.
		$phpmailer->isSMTP();
		$phpmailer->Host       = $smtp_host; // phpcs:ignore
		$phpmailer->SMTPSecure = $smtp_encryption; // phpcs:ignore
		$phpmailer->Port       = $smtp_port; // phpcs:ignore
		$phpmailer->SMTPAuth   = $smtp_authentication; // phpcs:ignore
		$phpmailer->Username   = $username; // phpcs:ignore
		$phpmailer->Password   = $password; // phpcs:ignore
	}

	/**
	 * Send alert email
	 *
	 * @since 2.1.8
	 *
	 * @param bool     $mailing          The result of sending the notification email.
	 * @param mixed[]  $settings         Array of form settings.
	 * @param int      $form_id          The ID of the form.
	 * @param bool     $moving           Whether to move the message or not. Default false.
	 * @param int      $moveto           The ID of the form to move the message to.
	 * @param string   $submission_date  The date of the inserted object.
	 * @param int      $reference_number The id of the inserted object.
	 * @param string[] $data             The submitted data.
	 * @param string   $flagged          The result of Akismet check, empty if spam does not exist.
	 *
	 * @return bool True, if the email was sent successfully. False otherwise.
	 */
	public function alert_sending( $mailing, $settings, $form_id, $moving, $moveto, $submission_date, $reference_number, $data, $flagged ) {

		if ( $settings['notification'] ) {

			// Retrieves the submission date in localized format according to the timezone of the site.
			$date_format   = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			$entry_date    = date_i18n( $date_format, strtotime( get_date_from_gmt( $submission_date ) ) );
			$submitter     = $data['submitter'];
			$email         = $data['email'];
			$phone         = $data['phone'];
			$website       = $data['website'];
			$subject       = $data['subject'];
			$message       = $data['message'];
			$processing    = new SimpleForm_Processing();
			$alert_message = $processing->alert_message( $submitter, $email, $phone, $website, $entry_date, $flagged, $subject, $message );
			$alert_to      = ! empty( $settings['notification_recipient'] ) ? explode( ',', $settings['notification_recipient'] ) : strval( get_option( 'admin_email' ) );
			$alert_number  = 'visible' === $settings['submission_number'] ? '#' . $reference_number . ' ' : '';
			$subject_text  = ! empty( $settings['custom_subject'] ) ? stripslashes( $settings['custom_subject'] ) : __( 'New Contact Request', 'simpleform' );
			$email_subject = ! empty( $subject ) ? $subject : __( 'No Subject', 'simpleform' );
			$admin_subject = 'request' === $settings['notification_subject'] ? $email_subject : $subject_text;
			$alert_subject = $alert_number . $flagged . $admin_subject;
			$headers       = 'Content-Type: text/html; charset=UTF-8 \r\n';
			$headers      .= $settings['notification_reply'] ? 'Reply-To: ' . $submitter . ' <' . $email . '>\r\n' : '';
			$headers      .= ! empty( $settings['bcc'] ) ? 'Bcc: ' . $settings['bcc'] . '\r\n' : '';

			do_action( 'check_smtp' );
			add_filter( 'wp_mail_from_name', array( $this, 'alert_name' ) );
			add_filter( 'wp_mail_from', array( $this, 'alert_email' ) );
			$mailing = wp_mail( $alert_to, $alert_subject, $alert_message, $headers );
			remove_filter( 'wp_mail_from_name', array( $this, 'alert_name' ) );
			remove_filter( 'wp_mail_from', array( $this, 'alert_email' ) );

			$last_message = $processing->last_message( $submitter, $email, $phone, $website, $entry_date, $flagged, $subject, $message );
			do_action( 'sform_before_last_message_updating', $form_id, $moving, $moveto );
			do_action( 'sform_last_message_updating', $form_id, $moving, $moveto, $submission_date, $last_message );

		}

		return $mailing;
	}

	/**
	 * Send auto-reply
	 *
	 * @since 2.1.8
	 *
	 * @param mixed[]  $settings         Array of form settings.
	 * @param int      $reference_number The id of the inserted object.
	 * @param string[] $data             The submitted data.
	 *
	 * @return void
	 */
	public function autoreply_sending( $settings, $reference_number, $data ) {

		$email = $data['email'];

		if ( $settings['autoresponder'] && ! empty( $email ) ) {

			$processing        = new SimpleForm_Processing();
			$autoresponder     = $processing->autoresponder_message( $settings, $reference_number, $data );
			$from              = ! empty( $settings['autoresponder_email'] ) ? $settings['autoresponder_email'] : strval( get_option( 'admin_email' ) );
			$autoreply_subject = ! empty( $settings['autoresponder_subject'] ) ? stripslashes( $settings['autoresponder_subject'] ) : __( 'Your request has been received. Thanks!', 'simpleform' );
			$reply_to          = ! empty( $settings['autoresponder_reply'] ) ? $settings['autoresponder_reply'] : $from;
			$headers           = 'Content-Type: text/html; charset=UTF-8 \r\n';
			$headers          .= 'Reply-To: <' . $reply_to . '>\r\n';
			do_action( 'check_smtp' );
			add_filter( 'wp_mail_from_name', array( $this, 'autoreply_name' ) );
			add_filter( 'wp_mail_from', array( $this, 'autoreply_email' ) );
			wp_mail( $email, $autoreply_subject, $autoresponder, $headers );
			remove_filter( 'wp_mail_from_name', array( $this, 'autoreply_name' ) );
			remove_filter( 'wp_mail_from', array( $this, 'autoreply_email' ) );

		}
	}

	/**
	 * Retrieve the submitter name.
	 *
	 * @since 2.2.0
	 *
	 * @return string The submitter name.
	 */
	public function submitter_name() {

		$submitter_name = __( 'Anonymous', 'simpleform' );

		if ( is_user_logged_in() ) {
			global $current_user;
			$name           = ! empty( $current_user->user_name ) ? $current_user->user_name : $current_user->display_name;
			$lastname       = ! empty( $current_user->user_lastname ) ? ' ' . $current_user->user_lastname : '';
			$submitter_name = trim( $name . $lastname );
		}

		return $submitter_name;
	}

	/**
	 * Force "From Name" in alert email
	 *
	 * @since 1.0.0
	 * @since 2.1.7 Refactoring of code.
	 *
	 * @return string The from name used in alert email.
	 */
	public function alert_name() {

		$validation        = new SimpleForm_Validation();
		$values            = $validation->sanitized_data();
		$form_id           = intval( $values['form'] );
		$name              = $values['name'];
		$lastname          = $values['lastname'];
		$full_name         = trim( $name . $lastname );
		$util              = new SimpleForm_Util();
		$form_name         = strval( $util->form_property_value( $form_id, 'name', __( 'Contact Us Page', 'simpleform' ) ) );
		$moveto            = intval( $util->form_property_value( $form_id, 'moveto', 0 ) );
		$to_be_moved       = strval( $util->form_property_value( $form_id, 'to_be_moved', '' ) );
		$override_settings = (bool) $util->form_property_value( $form_id, 'override_settings', false );
		$form              = 0 !== $moveto && 'next' === $to_be_moved && $override_settings ? $moveto : $form_id;
		$sender            = strval( $util->get_sform_option( $form, 'settings', 'notification_name', 'requester' ) );
		$custom_sender     = strval( $util->get_sform_option( $form, 'settings', 'custom_sender', get_bloginfo( 'name' ) ) );

		if ( 'requester' === $sender ) {
			$sender_name = ! empty( $full_name ) ? $full_name : $this->submitter_name();
		} elseif ( 'custom' === $sender ) {
			$sender_name = $custom_sender;
		} else {
			$sender_name = $form_name;
		}

		return $sender_name;
	}

	/**
	 * Force "From Email" in alert email
	 *
	 * @since 1.0.0
	 * @since 2.1.7 Refactoring of code.
	 *
	 * @return string The from email address used in alert email.
	 */
	public function alert_email() {

		$util               = new SimpleForm_Util();
		$validation         = new SimpleForm_Validation();
		$values             = $validation->sanitized_data();
		$form_id            = intval( $values['form'] );
		$moveto             = intval( $util->form_property_value( $form_id, 'moveto', 0 ) );
		$to_be_moved        = strval( $util->form_property_value( $form_id, 'to_be_moved', '' ) );
		$override_settings  = (bool) $util->form_property_value( $form_id, 'override_settings', false );
		$form               = 0 !== $moveto && 'next' === $to_be_moved && $override_settings ? $moveto : $form_id;
		$notification_email = strval( $util->get_sform_option( $form, 'settings', 'notification_email', strval( get_option( 'admin_email' ) ) ) );

		return $notification_email;
	}

	/**
	 * Force "From Name" in auto-reply email
	 *
	 * @since 1.0.0
	 * @since 2.1.7 Refactoring of code.
	 *
	 * @return string The from name used in auto-reply email.
	 */
	public function autoreply_name() {

		$util              = new SimpleForm_Util();
		$validation        = new SimpleForm_Validation();
		$values            = $validation->sanitized_data();
		$form_id           = intval( $values['form'] );
		$moveto            = intval( $util->form_property_value( $form_id, 'moveto', 0 ) );
		$to_be_moved       = strval( $util->form_property_value( $form_id, 'to_be_moved', '' ) );
		$override_settings = (bool) $util->form_property_value( $form_id, 'override_settings', false );
		$form              = 0 !== $moveto && 'next' === $to_be_moved && $override_settings ? $moveto : $form_id;
		$sender_name       = strval( $util->get_sform_option( $form, 'settings', 'autoresponder_name', get_bloginfo( 'name' ) ) );

		return $sender_name;
	}

	/**
	 * Force "From Email" in auto-reply email
	 *
	 * @since 1.0.0
	 * @since 2.1.7 Refactoring of code.
	 *
	 * @return string The from email address used in auto-reply email.
	 */
	public function autoreply_email() {

		$util              = new SimpleForm_Util();
		$validation        = new SimpleForm_Validation();
		$values            = $validation->sanitized_data();
		$form_id           = intval( $values['form'] );
		$moveto            = intval( $util->form_property_value( $form_id, 'moveto', 0 ) );
		$to_be_moved       = strval( $util->form_property_value( $form_id, 'to_be_moved', '' ) );
		$override_settings = (bool) $util->form_property_value( $form_id, 'override_settings', false );
		$form              = 0 !== $moveto && 'next' === $to_be_moved && $override_settings ? $moveto : $form_id;
		$from              = strval( $util->get_sform_option( $form, 'settings', 'autoresponder_email', strval( get_option( 'admin_email' ) ) ) );

		return $from;
	}
}

new SimpleForm_Emails();
