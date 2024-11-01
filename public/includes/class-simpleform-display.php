<?php
/**
 * File delegated to the displaying of the form.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/public/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the form display.
 */
class SimpleForm_Display {

	/**
	 * Check if current admin page is Gutenberg editor
	 *
	 * @since 2.2.0
	 *
	 * @return bool True, if Gutenberg editor is enabled. False if is not enabled.
	 */
	public function block_editor() {

		$is_gb_editor = defined( 'REST_REQUEST' ) && REST_REQUEST;
		$block_editor = ! is_admin() && ! $is_gb_editor && ! is_customize_preview() ? false : true;

		/*
		// ALIAS CODE.
		// $use_widgets_block_editor = get_theme_support( 'widgets-block-editor' ); // phpcs:ignore
		// return $use_widgets_block_editor; // phpcs:ignore
		*/

		return $block_editor;
	}

	/**
	 * Check if the form can be viewed from user
	 *
	 * @since 2.2.0
	 *
	 * @param mixed[] $attributes Array of form attributes.
	 *
	 * @return bool True, if the form is hidden. False if the form is shown instead.
	 */
	public function form_display( $attributes ) {

		$users        = ! empty( $attributes['show_for'] ) ? $attributes['show_for'] : 'all';
		$role         = ! empty( $attributes['user_role'] ) ? $attributes['user_role'] : 'any';
		$current_user = wp_get_current_user();
		$hiding       = false;
		switch ( $users ) {
			case 'out':
				$hiding = is_user_logged_in() ? true : false;
				break;
			case 'in':
				$restricted = 'any' !== $role && ! in_array( $role, (array) $current_user->roles, true ) ? true : false;
				$hiding     = ! is_user_logged_in() ? true : $restricted;
				break;
			default:
				$hiding = false;
		}

		return $hiding;
	}

	/**
	 * Check if the form field must be loaded
	 *
	 * @since 2.2.0
	 *
	 * @param int    $form_id The ID of the form.
	 * @param string $field   The ID of the field.
	 * @param string $status The default status to return.
	 *
	 * @return bool True, if the form field is visible. False if the form field is hidden instead.
	 */
	public function field_visibility( $form_id, $field, $status ) {

		$util       = new SimpleForm_Util();
		$form_field = $util->get_sform_option( $form_id, 'attributes', $field, $status );
		$visibility = 'visible' === $form_field || ( 'registered' === $form_field && is_user_logged_in() ) || ( 'anonymous' === $form_field && ! is_user_logged_in() ) ? true : false;

		return $visibility;
	}

	/**
	 * Check which form template must be loaded
	 *
	 * @since 2.2.0
	 *
	 * @param mixed[] $settings Array of form settings.
	 *
	 * @return string The path of the template file.
	 */
	public function form_template( $settings ) {

		$template           = ! empty( $settings['form_template'] ) ? $settings['form_template'] : 'default';
		$template_directory = 'customized' === $template && file_exists( get_theme_file_path( '/simpleform/custom-template.php' ) ) ? get_theme_file_path( '/simpleform/custom-template.php' ) : 'partials/template.php';

		return $template_directory;
	}

	/**
	 * Display a notice when the form cannot be seen by the admin when visiting the front end
	 *
	 * @since 2.2.0
	 *
	 * @param int $form_id The form ID.
	 *
	 * @return string The admin message.
	 */
	public function admin_notice( $form_id ) {

		$util   = new SimpleForm_Util();
		$users  = $util->get_sform_option( $form_id, 'attributes', 'show_for', 'all' );
		$role   = $util->get_sform_option( $form_id, 'attributes', 'user_role', 'any' );
		$notice = $util->get_sform_option( $form_id, 'settings', 'frontend_notice', true );

		if ( 'out' === $users ) {
			$form_user      = '<b>' . __( 'logged-out users', 'simpleform' ) . '</b>';
			$form_user_role = '';
		} elseif ( 'in' === $users ) {
			$form_user      = '<b>' . __( 'logged-in users', 'simpleform' ) . '</b>';
			$form_user_role = '&nbsp;' . __( 'with the role of', 'simpleform' ) . '&nbsp;<b>' . translate_user_role( ucfirst( strval( $role ) ) ) . '</b>';
		} else {
			$form_user      = __( 'everyone', 'simpleform' );
			$form_user_role = '';
		}

		$admin_message = '<div id="sform-admin-message"><p class="heading">' . __( 'SimpleForm Admin Notice', 'simpleform' ) . '</p>' . __( 'The form is visible only for ', 'simpleform' ) . $form_user . $form_user_role . '. ' . __( 'Your role does not allow you to see it!', 'simpleform' ) . '</div>';

		// If widget cannot be displayed show an admin notice if option enabled.
		$message = current_user_can( 'edit_pages' ) && $notice ? $admin_message : '';

		return $message;
	}

	/**
	 * Redirect to confirmation page after form submission.
	 *
	 * @since 2.2.0
	 *
	 * @param int     $form_id    The ID of the form.
	 * @param mixed[] $settings Array of form settings.
	 *
	 * @return string The sanitized URL for redirect usage.
	 */
	public function redirect_page( $form_id, $settings ) {

		$success_action = ! empty( $settings['success_action'] ) ? $settings['success_action'] : 'message';
		$thanks_url     = ! empty( $settings['thanks_url'] ) ? esc_url( $settings['thanks_url'] ) : '';
		$redirect_array = array(
			'sending' => 'success',
			'form'    => $form_id,
		);
		$current_url    = isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) ) : '';
		$redirect_to    = 'message' !== $success_action && ! empty( $thanks_url ) ? esc_url_raw( $thanks_url ) : esc_url_raw( add_query_arg( $redirect_array, $current_url ) );

		return $redirect_to;
	}

	/**
	 * Hide form descriptions on success.
	 *
	 * @since 2.2.0
	 *
	 * @param int     $form_id    The ID of the form.
	 * @param mixed[] $attributes Array of form attributes.
	 * @param string  $type       The type of component.
	 * @param bool    $visibility The component visibility.
	 *
	 * @return string The HTML attribute value.
	 */
	public function form_components( $form_id, $attributes, $type, $visibility ) {

		$submission = $this->detect_submission( $form_id );

		if ( ! $submission ) {

			$direction_class = isset( $attributes['form_direction'] ) && 'rtl' === $attributes['form_direction'] ? ' rtl' : '';

			if ( isset( $attributes[ $type ] ) ) {

				$components = array(
					'form_name'         => $attributes[ $type ],
					'introduction_text' => $visibility ? '<div id="sform-introduction-' . $form_id . '" class="sform-introduction' . $direction_class . '">' . stripslashes( wp_kses_post( $attributes[ $type ] ) ) . '</div>' : '',
					'bottom_text'       => $visibility ? '<div id="sform-bottom-' . $form_id . '" class="sform-bottom' . $direction_class . '">' . stripslashes( wp_kses_post( $attributes[ $type ] ) ) . '</div>' : '',
					'success_class'     => '',
				);

			} else {

				$components = array(
					'form_name'         => '',
					'introduction_text' => '',
					'bottom_text'       => '',
					'success_class'     => '',
				);

			}

			$data = $components[ $type ];

		} else {

			$components = array(
				'form_name'         => '',
				'introduction_text' => '',
				'bottom_text'       => '',
				'success_class'     => 'success',
			);

			$data = $components[ $type ];

		}

		return $data;
	}

	/**
	 * Check if the form has been submitted.
	 *
	 * @since 2.2.0
	 *
	 * @param int $form_id The ID of the submitted form.
	 *
	 * @return bool True, if the form has been submitted. False otherwise.
	 */
	public function detect_submission( $form_id ) {

		$submission = isset( $_GET['sending'] ) && 'success' === $_GET['sending'] && isset( $_GET['form'] ) && $form_id === $_GET['form'] ? true : false; // phpcs:ignore

		return $submission;
	}
}

new SimpleForm_Display();
