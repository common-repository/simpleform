<?php
/**
 * Main file for the frontend functionality of the plugin.
 *
 * @package SimpleForm
 * @subpackage SimpleForm/public
 */

defined( 'ABSPATH' ) || exit;

/**
 * Core class used to implement the frontend functionality of the plugin.
 */
class SimpleForm_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties for later use.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of the plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Modify header information.
		add_action( 'template_redirect', array( $this, 'ob_start_cache' ) );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_styles() {

		wp_register_style( $this->plugin_name . '-public', plugins_url( 'css/public-min.css', __FILE__ ), array(), $this->version );
		$util             = new SimpleForm_Util();
		$stylesheet       = $util->get_sform_option( 1, 'settings', 'stylesheet', false );
		$cssfile          = $util->get_sform_option( 1, 'settings', 'stylesheet_file', false );
		$additional_style = get_option( 'sform_additional_style' ) !== false ? strval( get_option( 'sform_additional_style' ) ) : '';
		$block_style      = get_option( 'sform_block_style' ) !== false ? strval( get_option( 'sform_block_style' ) ) : '';

		// Attach extra styles.
		if ( ! $stylesheet ) {
			wp_add_inline_style( $this->plugin_name . '-public', $additional_style );
			wp_add_inline_style( $this->plugin_name . '-public', $block_style );
		} else {
			wp_register_style( $this->plugin_name, plugins_url( 'css/simpleform-min.css', __FILE__ ), array(), $this->version );
			wp_add_inline_style( $this->plugin_name, $additional_style );
			wp_add_inline_style( $this->plugin_name, $block_style );
			// Register custom style and attach extra styles.
			if ( $cssfile && file_exists( get_theme_file_path( '/simpleform/custom-style.css' ) ) ) {
				wp_register_style( 'sform-custom-style', get_theme_file_uri( '/simpleform/custom-style.css' ), array(), $this->version );
			}
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		wp_register_script( 'sform_form_script', plugins_url( 'js/script-min.js', __FILE__ ), array( 'jquery' ), $this->version, false );
		wp_register_script( 'sform_public_script', plugins_url( 'js/public-min.js', __FILE__ ), array( 'jquery' ), $this->version, false );
		wp_localize_script( 'sform_public_script', 'ajax_sform_processing', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		// Register custom script.
		$util       = new SimpleForm_Util();
		$javascript = $util->get_sform_option( 1, 'settings', 'javascript', false );
		if ( $javascript && file_exists( get_theme_file_path( '/simpleform/custom-script.js' ) ) ) {
			wp_register_script( 'sform-custom-script', get_theme_file_uri( '/simpleform/custom-script.js' ), array( 'jquery' ), $this->version, false );
		}

		// Attach extra scripts.
		$additional_script = get_option( 'sform_additional_script' ) !== false ? strval( get_option( 'sform_additional_script' ) ) : '';

		if ( ! empty( $additional_script ) ) {
			wp_add_inline_script( 'sform_form_script', $additional_script, 'before' );
		}
	}

	/**
	 * Apply shortcode and return the form.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed[] $atts Array of shortcode attributes.
	 *
	 * @return string The HTML markup for the shortcode.
	 */
	public function sform_shortcode( $atts ) {

		$form       = '';
		$atts_array = shortcode_atts(
			array(
				'id'   => 1,
				'type' => '',
			),
			$atts
		);
		$util       = new SimpleForm_Util();
		$settings   = (array) $util->get_sform_option( (int) $atts_array['id'], 'settings', '', '' );
		$attributes = (array) $util->get_sform_option( (int) $atts_array['id'], 'attributes', '', '' );
		$display    = new SimpleForm_Display();
		$hiding     = $display->form_display( $attributes );

		if ( ! $hiding || current_user_can( 'manage_options' ) ) {

			include 'partials/form-variables.php';
			$ajax          = $util->get_sform_option( (int) $atts_array['id'], 'settings', 'ajax_submission', false );
			$admin_message = $display->admin_notice( (int) $atts_array['id'] );
			$above_form    = $display->form_components( (int) $atts_array['id'], $attributes, 'introduction_text', true );
			$below_form    = $display->form_components( (int) $atts_array['id'], $attributes, 'bottom_text', true );
			$block_editor  = $display->block_editor();

			// The output of the form.
			$contact_form = '';

			// Form template.
			$template_directory = $display->form_template( $settings );
			include $template_directory;

			// Form style.
			wp_enqueue_style( $this->plugin_name . '-public' );
			wp_enqueue_style( $this->plugin_name );
			wp_enqueue_style( 'sform-custom-style' );

			// Form scripts.
			wp_enqueue_script( 'sform_form_script' );
			if ( $ajax ) {
				wp_enqueue_script( 'sform_public_script' );
			}
			wp_enqueue_script( 'sform-custom-script' );

			// Display form.
			if ( $hiding && ! $block_editor ) {
				$form = $admin_message;
			} else {
				// Allow block settings to control the text above and the text below.
				$form = '' === $atts_array['type'] ? $above_form . $contact_form . $below_form : $contact_form;
			}
		}

		return $form;
	}

	/**
	 * Validate the form data after submission without Ajax
	 *
	 * @since 1.0.0
	 * @version 2.1.7
	 *
	 * @param mixed[] $data The submitted data of the form.
	 *
	 * @return mixed[] Array of submitted data of the form.
	 */
	public function formdata_validation( $data ) {

		$validation = new SimpleForm_Validation();
		$values     = $validation->sanitized_data();
		$util       = new SimpleForm_Util();
		$ajax       = $util->get_sform_option( intval( $values['form'] ), 'settings', 'ajax_submission', false );

		if ( $values['submission'] && ! $ajax ) {

			$errors = '';

			// Make form validation.
			$errors = $validation->fields_validation( $values );

			// Detect spam submission.
			$errors  = apply_filters( 'akismet_spam_detection', $errors, $values['form'], $values['name'], $values['email'], $values['message'], 'block-spam' );
			$flagged = apply_filters( 'akismet_spam_detection', '', $values['form'], $values['name'], $values['email'], $values['message'], 'mark-spam' );

			// Process the submitted data.
			$errors = apply_filters( 'sform_processing', $errors, $values, $flagged );

			// Remove duplicate Form ID.
			$errors = implode( ';', array_unique( explode( ';', $errors ) ) );

			$data = array(
				'form'         => $values['form'],
				'name'         => $values['name'],
				'lastname'     => $values['lastname'],
				'email'        => $values['email'],
				'phone'        => $values['phone'],
				'website'      => $values['website'],
				'subject'      => $values['subject'],
				'message'      => $values['message'],
				'consent'      => $values['consent'],
				'captcha'      => $values['captcha_answer'],
				'captcha_one'  => $values['captcha_one'],
				'captcha_two'  => $values['captcha_two'],
				'url'          => $values['honeyurl'],
				'telephone'    => $values['honeytel'],
				'fakecheckbox' => $values['honeycheck'],
				'error'        => $errors,
			);

		} else {

			$data = array(
				'form'         => '',
				'name'         => '',
				'lastname'     => '',
				'email'        => '',
				'phone'        => '',
				'website'      => '',
				'subject'      => '',
				'message'      => '',
				'consent'      => '',
				'captcha'      => '',
				'captcha_one'  => '',
				'captcha_two'  => '',
				'url'          => '',
				'telephone'    => '',
				'fakecheckbox' => '',
			);

		}

		return $data;
	}

	/**
	 * Process the form data after submission with post callback function
	 *
	 * @since 1.0.0
	 * @version 2.1.7
	 *
	 * @param mixed[] $errors   Array of errors found during form validation.
	 * @param mixed[] $values   Array of sanitized data.
	 * @param string  $flagged  The result of Akismet check, empty if spam does not exist.
	 *
	 * @return mixed[] Array of errors found during form validation.
	 */
	public function formdata_processing( $errors, $values, $flagged ) {

		$util               = new SimpleForm_Util();
		$validation         = new SimpleForm_Validation();
		$settings           = (array) $util->get_sform_option( absint( $values['form'] ), 'settings', '', '' );
		$submitter_data     = $validation->submitter_data( strval( $values['name'] ), strval( $values['lastname'] ), strval( $values['email'] ) );
		$submitter_name     = $submitter_data['name'];
		$submitter_lastname = $submitter_data['lastname'];
		$submitter          = $submitter_data['submitter'];
		$email              = $submitter_data['email'];

		// Prevent double submission: change of name or email allowed.
		$errors = apply_filters( 'sform_block_duplicate', $errors, absint( $values['form'] ), $submitter, $email, strval( $values['message'] ) );

		if ( empty( $errors ) ) {

			global $wpdb;
			$mailing         = false;
			$display         = new SimpleForm_Display();
			$redirect_to     = $display->redirect_page( absint( $values['form'] ), $settings );
			$submission_date = gmdate( 'Y-m-d H:i:s' );
			if ( is_user_logged_in() ) {
				$requester_type = 'registered';
				$user_ID        = get_current_user_id();
			} else {
				$requester_type = 'anonymous';
				$user_ID        = '0';
			}
			$moveto            = intval( $util->form_property_value( absint( $values['form'] ), 'moveto', 0 ) );
			$to_be_moved       = strval( $util->form_property_value( absint( $values['form'] ), 'to_be_moved', '' ) );
			$override_settings = (bool) $util->form_property_value( absint( $values['form'] ), 'override_settings', false );
			$moving            = $this->message_moving( $moveto, $to_be_moved );
			if ( $moving ) {
				$save_as    = $moveto;
				$moved_from = absint( $values['form'] );
			} else {
				$save_as    = absint( $values['form'] );
				$moved_from = '0';
			}
			$sform_default_values = array(
				'form'           => $save_as,
				'moved_from'     => $moved_from,
				'requester_type' => $requester_type,
				'requester_id'   => $user_ID,
				'date'           => $submission_date,
			);
			$sform_extra_values   = array_merge( $sform_default_values, apply_filters( 'sform_storing_values', array( 'notes' => '' ), $save_as, $submitter_data, $values, $flagged ) );
			$success              = $wpdb->insert( $wpdb->prefix . 'sform_submissions', $sform_extra_values ); // phpcs:ignore.
			$data                 = array(
				'name'      => $submitter_name,
				'lastname'  => $submitter_lastname,
				'submitter' => $submitter,
				'email'     => $email,
				'phone'     => strval( $values['phone'] ),
				'website'   => strval( $values['website'] ),
				'subject'   => strval( $values['subject'] ),
				'message'   => strval( $values['message'] ),
			);

			if ( $success ) {

				$reference_number = $wpdb->insert_id;
				$processing       = new SimpleForm_Processing();
				$mail_settings    = $processing->mail_settings( absint( $values['form'] ), $moving, $moveto, $override_settings );
				do_action( 'sform_entries_updating', absint( $values['form'] ) );
				$mailing = apply_filters( 'sform_alert', $mailing, $mail_settings, absint( $values['form'] ), $moving, $moveto, $submission_date, $reference_number, $data, $flagged );
				do_action( 'sform_autoreply', $mail_settings, $reference_number, $data );

				if ( ! has_filter( 'sform_post_message' ) ) {
					if ( $mailing ) {
						header( 'Location: ' . $redirect_to );
						ob_end_flush();
						exit();
					} else {
						$errors = absint( $values['form'] ) . ';server_error';
						wp_add_inline_script( 'sform_form_script', 'document.getElementById("errors-' . absint( $values['form'] ) . '").focus();', 'before' );
					}
				} else {
					$errors = apply_filters( 'sform_post_message', $errors, absint( $values['form'] ), $mailing );
					if ( '' === $errors ) {
						header( 'Location: ' . $redirect_to );
						ob_end_flush();
						exit();
					}
				}
			} else {
				$errors = absint( $values['form'] ) . ';server_error';
				wp_add_inline_script( 'sform_form_script', 'document.getElementById("errors-' . absint( $values['form'] ) . '").focus();', 'before' );
			}
		}

		return $errors;
	}

	/**
	 * Process the form data after submission with Ajax callback function.
	 *
	 * @since 1.0.0
	 * @version 2.1.7
	 *
	 * @return void
	 */
	public function formdata_ajax_processing() {

		$validation = new SimpleForm_Validation();
		$values     = $validation->sanitized_data();
		$errors     = array();

		// Make the form validation.
		$submitter_data = $validation->submitter_data( strval( $values['name'] ), strval( $values['lastname'] ), strval( $values['email'] ) );
		$errors         = $validation->fields_validation( $values );

		// Detect spam submission.
		$errors  = apply_filters( 'akismet_spam_detection', $errors, $values['form'], $values['name'], $values['email'], $values['message'], 'block-spam' );
		$flagged = apply_filters( 'akismet_spam_detection', '', $values['form'], $values['name'], $values['email'], $values['message'], 'mark-spam' );

		// Prevent double submission: change of name or email allowed.
		$errors = apply_filters( 'sform_block_duplicate', $errors, $values['form'], $submitter_data['submitter'], $submitter_data['email'], $values['message'] );

		if ( empty( $errors ) ) {

			global $wpdb;
			$mailing           = false;
			$submission_date   = gmdate( 'Y-m-d H:i:s' );
			$util              = new SimpleForm_Util();
			$processing        = new SimpleForm_Processing();
			$moveto            = intval( $util->form_property_value( intval( $values['form'] ), 'moveto', 0 ) );
			$to_be_moved       = strval( $util->form_property_value( intval( $values['form'] ), 'to_be_moved', '' ) );
			$override_settings = (bool) $util->form_property_value( intval( $values['form'] ), 'override_settings', false );
			$moving            = $this->message_moving( $moveto, $to_be_moved );
			$settings          = $processing->mail_settings( intval( $values['form'] ), $moving, $moveto, $override_settings );
			if ( $moving ) {
				$save_as    = $moveto;
				$moved_from = $values['form'];
			} else {
				$save_as    = $values['form'];
				$moved_from = '0';
			}
			$sform_default_values = array(
				'form'           => $save_as,
				'moved_from'     => $moved_from,
				'requester_type' => is_user_logged_in() ? 'registered' : 'anonymous',
				'requester_id'   => is_user_logged_in() ? get_current_user_id() : '0',
				'date'           => $submission_date,
			);
			$sform_extra_values   = array_merge( $sform_default_values, apply_filters( 'sform_storing_values', array( 'notes' => '' ), $save_as, $submitter_data, $values, $flagged ) );
			$sform_added_values   = array_merge( $sform_extra_values, apply_filters( 'sform_testing', array( 'notes' => '' ) ) );
			$success              = $wpdb->insert( $wpdb->prefix . 'sform_submissions', $sform_added_values ); // phpcs:ignore.
			$data                 = array(
				'name'      => $submitter_data['name'],
				'lastname'  => $values['lastname'],
				'submitter' => $submitter_data['submitter'],
				'email'     => $submitter_data['email'],
				'phone'     => $values['phone'],
				'website'   => $values['website'],
				'subject'   => $values['subject'],
				'message'   => $values['message'],
			);

			if ( $success ) {

				$reference_number = $wpdb->insert_id;
				do_action( 'sform_entries_updating', $values['form'] );
				$mailing = apply_filters( 'sform_alert', $mailing, $settings, $values['form'], $moving, $moveto, $submission_date, $reference_number, $data, $flagged );
				do_action( 'sform_autoreply', $settings, $reference_number, $data );

				if ( ! has_filter( 'sform_ajax_message' ) ) {
					if ( $mailing ) {
						$errors = array(
							'error'        => false,
							'redirect'     => $settings['redirect'],
							'redirect_url' => $settings['redirect_url'],
							'notice'       => $settings['success_message'],
						);
					} else {
						$errors = array(
							'error'       => true,
							'showerror'   => true,
							'field_focus' => false,
							'notice'      => $settings['server_error'],
						);
					}
				} else {
					$errors = apply_filters( 'sform_ajax_message', $errors, $values['form'], $mailing, $settings['redirect'], $settings['redirect_url'], $settings['success_message'], $settings['server_error'] );
				}
			} else {
				$errors = array(
					'error'       => true,
					'showerror'   => true,
					'field_focus' => false,
					'notice'      => $settings['server_error'],
				);
			}
		}

		echo wp_json_encode( $errors );
		wp_die();
	}

	/**
	 * Check if the message moving has been scheduled.
	 *
	 * @since 2.2.0
	 *
	 * @param int    $moveto      The ID of the form to move the message to.
	 * @param string $to_be_moved Whether to move the messages of the form.
	 *
	 * @return bool True, if a moving has been scheduled. False if no moving is planned.
	 */
	public function message_moving( $moveto, $to_be_moved ) {

		$moving = 0 !== $moveto && 'next' === $to_be_moved ? true : false;

		return $moving;
	}

	/**
	 * Modify the HTTP response header (buffer the output so that nothing gets written until you explicitly tell to do it)
	 *
	 * @since 1.8.1
	 *
	 * @param mixed[] $errors Array of errors found during form validation.
	 *
	 * @return void
	 */
	public function ob_start_cache( $errors ) {

		if ( $errors ) {
			return;
		}

		$validation = new SimpleForm_Validation();
		$values     = $validation->sanitized_data();
		$form_id    = intval( $values['form'] );
		$submission = $values['submission'];
		$util       = new SimpleForm_Util();
		$ajax       = $util->get_sform_option( $form_id, 'settings', 'ajax_submission', false );

		if ( $submission && ! $ajax ) {
			ob_start();
		}
	}
}
