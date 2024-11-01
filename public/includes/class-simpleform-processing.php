<?php
/**
 * File delegated to process the data collected with the form.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/public/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the data processing.
 *
 * @since 2.1.7
 */
class SimpleForm_Processing {

	/**
	 * Class constructor
	 *
	 * @since 2.1.7
	 */
	public function __construct() {

		// Update the last message.
		add_action( 'sform_before_last_message_updating', array( $this, 'update_before_last_message' ), 10, 3 );
		// Update the last message.
		add_action( 'sform_last_message_updating', array( $this, 'update_last_message' ), 10, 5 );
		// Update the submissions number.
		add_action( 'sform_entries_updating', array( $this, 'update_entries' ), 10, 1 );
	}

	/**
	 * Check whether an alert must be sent after the form has been successfully submitted.
	 *
	 * @since 2.2.0
	 *
	 * @param int  $form_id           The ID of the form.
	 * @param bool $moving            Whether to move the message or not. Default false.
	 * @param int  $moveto            The ID of the form to move the message to.
	 * @param bool $override_settings Whether to use the notifications settings of the form to which message is moved. Default false.
	 *
	 * @return mixed[] Array of form settings to be applied.
	 */
	public function mail_settings( $form_id, $moving, $moveto, $override_settings ) {

		$util              = new SimpleForm_Util();
		$form              = $moving && $override_settings ? $moveto : $form_id;
		$mail_settings     = (array) $util->get_sform_option( $form, 'settings', '', '' );
		$form_settings     = (array) $util->get_sform_option( $form_id, 'settings', '', '' );
		$success_action    = ! empty( $form_settings['success_action'] ) ? $form_settings['success_action'] : 'message';
		$confirmation_img  = plugins_url( 'img/confirmation.png', __FILE__ );
		$thank_you_message = ! empty( $form_settings['success_message'] ) ? stripslashes( wp_kses_post( $form_settings['success_message'] ) ) : '<div class="form confirmation" tabindex="-1"><h4>' . __( 'We have received your request!', 'simpleform' ) . '</h4><br>' . __( 'Your message will be reviewed soon, and we\'ll get back to you as quickly as possible.', 'simpleform' ) . '</br><img src="' . $confirmation_img . '" alt="message received"></div>';
		$thanks_url        = ! empty( $form_settings['thanks_url'] ) ? esc_url( $form_settings['thanks_url'] ) : '';
		$server_error      = ! empty( $form_settings['server_error'] ) ? stripslashes( $form_settings['server_error'] ) : __( 'Error occurred during processing data. Please try again!', 'simpleform' );
		if ( 'message' === $success_action ) {
			$redirect     = false;
			$redirect_url = '';
		} else {
			$redirect     = true;
			$redirect_url = $thanks_url;
		}

		$settings = array(
			'success_action'         => $success_action,
			'success_message'        => $thank_you_message,
			'thanks_url'             => $thanks_url,
			'redirect'               => $redirect,
			'redirect_url'           => $redirect_url,
			'server_error'           => $server_error,
			'notification'           => $mail_settings['notification'],
			'notification_recipient' => $mail_settings['notification_recipient'],
			'submission_number'      => $mail_settings['submission_number'],
			'custom_subject'         => $mail_settings['custom_subject'],
			'notification_subject'   => $mail_settings['notification_subject'],
			'notification_reply'     => $mail_settings['notification_reply'],
			'bcc'                    => $mail_settings['bcc'],
			'autoresponder'          => $mail_settings['autoresponder'],
			'autoresponder_email'    => $mail_settings['autoresponder_email'],
			'autoresponder_subject'  => $mail_settings['autoresponder_subject'],
			'autoresponder_message'  => $mail_settings['autoresponder_message'],
			'autoresponder_reply'    => $mail_settings['autoresponder_reply'],
		);

		return $settings;
	}

	/**
	 * Update the submissions number
	 *
	 * @since 2.1.7
	 *
	 * @param int $form_id The ID of the form.
	 *
	 * @return void
	 */
	public static function update_entries( $form_id ) {

		$util           = new SimpleForm_Util();
		$relocation     = (bool) $util->form_property_value( $form_id, 'relocation', false );
		$moveto         = intval( $util->form_property_value( $form_id, 'moveto', 0 ) );
		$to_be_moved    = strval( $util->form_property_value( $form_id, 'to_be_moved', '' ) );
		$onetime_moving = (bool) $util->form_property_value( $form_id, 'onetime_moving', true );
		$moved_entries  = intval( $util->form_property_value( $form_id, 'moved_entries', 0 ) );
		$moving         = $relocation && 0 !== $moveto && 'next' === $to_be_moved && ! $onetime_moving ? true : false;
		global $wpdb;

		if ( $moving ) {

			$entries        = intval( $util->form_property_value( $moveto, 'entries', 0 ) );
			$update_entries = $entries + 1;
			$wpdb->update( $wpdb->prefix . 'sform_shortcodes', array( 'entries' => $update_entries ), array( 'id' => $moveto ) ); // phpcs:ignore.
			$update_moved = $moved_entries + 1;
			$wpdb->update( $wpdb->prefix . 'sform_shortcodes', array( 'moved_entries' => $update_moved ), array( 'id' => $form_id ) ); // phpcs:ignore.
			// Clear cache if data updated.
			wp_cache_delete( 'form_data_' . $moveto );
			wp_cache_delete( 'form_data_' . $form_id );
		} else {
			$entries        = intval( $util->form_property_value( $form_id, 'entries', 0 ) );
			$update_entries = $entries + 1;
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}sform_shortcodes SET entries = %d WHERE id = %d", $update_entries, $form_id ) ); // phpcs:ignore.
			// Clear cache if data updated.
			wp_cache_delete( 'form_data_' . $form_id );
		}
	}

	/**
	 * Build the alert message
	 *
	 * @since 2.1.7
	 *
	 * @param string $submitter  The submitter data.
	 * @param string $email      The email of submitter.
	 * @param string $phone      The phone of submitter.
	 * @param string $website    The website address of submitter.
	 * @param string $entry_date The date of the inserted object.
	 * @param string $flagged    The result of Akismet check, empty if spam does not exist.
	 * @param string $subject    The subject of the message.
	 * @param string $message    The message.
	 *
	 * @return string The alert message.
	 */
	public static function alert_message( $submitter, $email, $phone, $website, $entry_date, $flagged, $subject, $message ) {

		$from  = '<b>' . __( 'From', 'simpleform' ) . ':</b>&nbsp;&nbsp;' . $submitter;
		$from .= ! empty( $email ) ? '&nbsp;&nbsp;&lt;&nbsp;' . $email . '&nbsp;&gt;' : '';
		$from .= ! empty( $phone ) ? '<br><b>' . __( 'Phone', 'simpleform' ) . ':</b>&nbsp;&nbsp;' . $phone : '';
		$from .= ! empty( $website ) ? '<br><b>' . __( 'Website', 'simpleform' ) . ':</b>&nbsp;&nbsp;' . $website : '';

		$flagged_subject    = ! empty( $flagged ) ? '<br><b>' . __( 'Subject', 'simpleform' ) . ':</b>&nbsp;&nbsp;' . $flagged : '';
		$submission_subject = ! empty( $subject ) ? '<br><b>' . __( 'Subject', 'simpleform' ) . ':</b>&nbsp;&nbsp;' . $flagged . $subject : $flagged_subject;
		$alert_message      = '<div style="">' . $from . '<br><b>' . __( 'Sent', 'simpleform' ) . ':</b>&nbsp;&nbsp;' . $entry_date . $submission_subject . '<p>' . nl2br( $message ) . '</p></div>';

		return $alert_message;
	}

	/**
	 * Assemble submission data to show the last message
	 *
	 * @since 2.1.7
	 *
	 * @param string $submitter  The submitter data.
	 * @param string $email      The email of submitter.
	 * @param string $phone      The phone of submitter.
	 * @param string $website    The website address of submitter.
	 * @param string $entry_date The date of the inserted object.
	 * @param string $flagged    The result of Akismet check, empty if spam does not exist.
	 * @param string $subject    The subject of the message.
	 * @param string $message    The message.
	 *
	 * @return string The last message.
	 */
	public static function last_message( $submitter, $email, $phone, $website, $entry_date, $flagged, $subject, $message ) {

		$mail_data       = ! empty( $email ) ? '&nbsp;&nbsp;&lt;&nbsp;<a href="mailto:' . $email . '">' . $email . '</a>&nbsp;&gt;' : '';
		$phone_data      = ! empty( $phone ) ? '<tr><td>' . __( 'Phone', 'simpleform' ) . ':</td><td>' . $phone . '</td></tr>' : '';
		$website_data    = ! empty( $website ) ? '<tr><td>' . __( 'Website', 'simpleform' ) . ':</td><td>' . $website . '</td></tr>' : '';
		$flagged_subject = ! empty( $flagged ) ? '<tr><td>' . __( 'Subject', 'simpleform' ) . ':</td><td>' . $flagged . '</td></tr>' : '';
		$message_subject = ! empty( $subject ) ? '<tr><td>' . __( 'Subject', 'simpleform' ) . ':</td><td>' . $flagged . $subject . '</td></tr>' : $flagged_subject;
		$last_message    = '<table class="table-msg"><tbody><tr><td>' . __( 'From', 'simpleform' ) . ':</td><td>' . $submitter . $mail_data . '</td></tr>';
		$last_message   .= $phone_data;
		$last_message   .= $website_data;
		$last_message   .= '<tr><td>' . __( 'Date', 'simpleform' ) . ':</td><td>' . $entry_date . '</td></tr>';
		$last_message   .= $message_subject;
		$last_message   .= '<tr><td class="message">' . __( 'Message', 'simpleform' ) . ':</td><td>' . $message . '</td></tr></tbody></table>';

		$current_last_message = get_option( 'sform_last_message' ) !== false ? get_option( 'sform_last_message' ) : '';
		if ( ! empty( $current_last_message ) ) {
			update_option( 'sform_before_last_message', $current_last_message );
		}

		return $last_message;
	}

	/**
	 * Update the before last message
	 *
	 * @since 2.1.7
	 *
	 * @param int  $form_id The ID of the form.
	 * @param bool $moving  Whether to move the message or not. Default false.
	 * @param int  $moveto  The ID of the form to move the message to.
	 *
	 * @return void
	 */
	public static function update_before_last_message( $form_id, $moving, $moveto ) {

		// Check if a forwarding is in progress.
		if ( $moving ) {
			$direct_message = get_option( 'sform_direct_last_' . $form_id . '_message' ) !== false ? get_option( "sform_direct_last_{$form_id}_message" ) : '';
			if ( ! empty( $direct_message ) ) {
				update_option( 'sform_direct_before_last_' . $form_id . '_message', $direct_message );
			}

			$last_message = get_option( 'sform_moved_last_' . $moveto . '_message' ) !== false ? get_option( "sform_moved_last_{$moveto}_message" ) : '';
			if ( ! empty( $last_message ) ) {
				update_option( 'sform_moved_before_last_' . $moveto . '_message', $last_message );
			}
		} else {
			$last_message = get_option( "sform_last_{$form_id}_message" ) !== false ? get_option( "sform_last_{$form_id}_message" ) : '';
			if ( ! empty( $last_message ) ) {
				update_option( 'sform_before_last_' . $form_id . '_message', $last_message );
			}
		}
	}

	/**
	 * Update the last message
	 *
	 * @since 2.1.7
	 *
	 * @param int    $form_id         The ID of the form.
	 * @param bool   $moving          Whether to move the message or not. Default false.
	 * @param int    $moveto          The ID of the form to move the message to.
	 * @param string $submission_date The date of the inserted object.
	 * @param string $last_message    The last submitted message.
	 *
	 * @return void
	 */
	public static function update_last_message( $form_id, $moving, $moveto, $submission_date, $last_message ) {

		update_option( 'sform_last_message', $last_message );

		$timestamp    = strtotime( $submission_date );
		$message_data = $timestamp . '#' . $last_message;

		// Check if a forwarding is in progress.
		if ( $moving ) {
			update_option( 'sform_direct_last_' . $form_id . '_message', $message_data );
			update_option( 'sform_moved_last_' . $moveto . '_message', $message_data );
		} else {
			update_option( 'sform_last_' . $form_id . '_message', $message_data );
		}
	}

	/**
	 * Build the auto responder message
	 *
	 * @since 2.1.7
	 *
	 * @param mixed[]  $settings         Array of form settings.
	 * @param int      $reference_number The id of the inserted object.
	 * @param string[] $data             The submitted data.
	 *
	 * @return string The auto responder message.
	 */
	public static function autoresponder_message( $settings, $reference_number, $data ) {

		$name      = $data['name'];
		$lastname  = $data['lastname'];
		$email     = $data['email'];
		$phone     = $data['phone'];
		$website   = $data['website'];
		$subject   = $data['subject'];
		$message   = $data['message'];
		$code_name = '[name]';
		/* translators: %s: Name */
		$autoreply     = ! empty( $settings['autoresponder_message'] ) ? stripslashes( wp_kses_post( strval( $settings['autoresponder_message'] ) ) ) : printf( esc_html__( 'Hi %s', 'simpleform' ), esc_html( $code_name ) ) . ',<p>' . __( 'We have received your request. It will be reviewed soon and we\'ll get back to you as quickly as possible.', 'simpleform' ) . __( 'Thanks,', 'simpleform' ) . __( 'The Support Team', 'simpleform' );
		$tags          = array( '[name]', '[lastname]', '[email]', '[phone]', '[website]', '[subject]', '[message]', '[submission_id]' );
		$values        = array( $name, $lastname, $email, $phone, $website, $subject, $message, $reference_number );
		$autoresponder = str_replace( $tags, $values, $autoreply );

		return $autoresponder;
	}
}

new SimpleForm_Processing();
