<?php
/**
 * Main file for the admin functionality of the plugin.
 *
 * @package SimpleForm
 * @subpackage SimpleForm/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Core class used to implement the admin-specific functionality of the plugin.
 */
class SimpleForm_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of the plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The hook that was called.
	 *
	 * @return void
	 */
	public function enqueue_styles( $hook ) {

		wp_register_style( $this->plugin_name . '-admin', plugins_url( 'css/admin-min.css', __FILE__ ), array(), $this->version );

		global $sform_entries;
		global $sform_forms;
		global $sform_form;
		global $sform_editor;
		global $sform_new;
		global $sform_settings;
		global $sform_support;
		global $pagenow;

		$admin_pages = array(
			$sform_entries,
			$sform_forms,
			$sform_form,
			$sform_new,
			$sform_editor,
			$sform_settings,
			$sform_support,
			'widgets.php',
			'customize.php',
		);

		if ( in_array( $hook, $admin_pages, true ) || in_array( $pagenow, $admin_pages, true ) ) {

			wp_enqueue_style( $this->plugin_name . '-admin' );

		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The hook that was called.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {

		global $sform_entries;
		global $sform_forms;
		global $sform_form;
		global $sform_editor;
		global $sform_new;
		global $sform_settings;
		global $sform_support;
		global $pagenow;

		$admin_pages = array(
			$sform_entries,
			$sform_forms,
			$sform_form,
			$sform_new,
			$sform_editor,
			$sform_settings,
			$sform_support,
			'widgets.php',
			'customize.php',
		);

		if ( in_array( $hook, $admin_pages, true ) || in_array( $pagenow, $admin_pages, true ) ) {

			/* translators: Used in: %1$s or %2$s the page content. */
			$edit = __( 'Edit', 'simpleform' );
			/* translators: Used in: %1$s or %2$s the page content. */
			$view = __( 'view', 'simpleform' );
			/* translators: 1: Edit, 2: view. */
			$page_links = sprintf( __( '%1$s or %2$s the page content', 'simpleform' ), $edit, $view );
			/* translators: Used in: Please enter an error message to be displayed on %s of the form. */
			$top_position = __( 'top', 'simpleform' );
			/* translators: Used in: Please enter an error message to be displayed on %s of the form. */
			$bottom_position = __( 'bottom', 'simpleform' );
			$smtp_notes      = __( 'Uncheck if you want to use a dedicated plugin to take care of outgoing email', 'simpleform' );
			$storing_notice  = '<span style="margin-top: -7px; margin-bottom: -7px; color: #32373c; padding: 7px 20px 7px 10px; border-width: 0 0 0 5px; border-style: solid; background: #e5f5fa; border-color: #00a0d2;">' . __( 'The list of entries refers only to forms for which data storage has been enabled', 'simpleform' ) . '</span>';

			wp_enqueue_script( 'sform_saving_options', plugins_url( 'js/admin-min.js', __FILE__ ), array( 'jquery' ), $this->version, false );

			wp_localize_script(
				'sform_saving_options',
				'ajax_sform_settings_options_object',
				array(
					'ajaxurl'     => admin_url( 'admin-ajax.php' ),
					'copy'        => __( 'Copy shortcode', 'simpleform' ),
					'copied'      => __( 'Shortcode copied', 'simpleform' ),
					'saving'      => __( 'Saving data in progress', 'simpleform' ),
					'loading'     => __( 'Saving settings in progress', 'simpleform' ),
					'notes'       => __( 'Create a directory inside your active theme\'s directory, name it "simpleform", copy one of the template files, and name it "custom-template.php"', 'simpleform' ),
					'bottomnotes' => __( 'Display an error message on bottom of the form in case of one or more errors in the fields', 'simpleform' ),
					'topnotes'    => __( 'Display an error message above the form in case of one or more errors in the fields', 'simpleform' ),
					'nofocus'     => __( 'Do not move focus', 'simpleform' ),
					'focusout'    => __( 'Set focus to error message outside', 'simpleform' ),
					'builder'     => __( 'Change easily the way your contact form is displayed. Choose which fields to use and who should see them:', 'simpleform' ),
					'appearance'  => __( 'Tweak the appearance of your contact form to match it better to your site. ', 'simpleform' ),
					'adminurl'    => admin_url(),
					'pageurl'     => site_url(),
					'status'      => __( 'Page in draft status not yet published', 'simpleform' ),
					'publish'     => __( 'Publish now', 'simpleform' ),
					'edit'        => $edit,
					'view'        => $view,
					'pagelinks'   => $page_links,
					'show'        => __( 'Show Configuration Warnings', 'simpleform' ),
					'hide'        => esc_html__( 'Hide Configuration Warnings', 'simpleform' ),
					'cssenabled'  => __( 'Create a directory inside your active theme\'s directory, name it "simpleform", add your CSS stylesheet file, and name it "custom-style.css"', 'simpleform' ),
					'cssdisabled' => __( 'Keep unchecked if you want to use your personal CSS code and include it somewhere in your theme\'s code without using an additional file', 'simpleform' ),
					'jsenabled'   => __( 'Create a directory inside your active theme\'s directory, name it "simpleform", add your JavaScript file, and name it "custom-script.js"', 'simpleform' ),
					'jsdisabled'  => __( 'Keep unchecked if you want to use your personal JavaScript code and include it somewhere in your theme\'s code without using an additional file', 'simpleform' ),
					'showchars'   => __( 'Keep unchecked if you want to use a generic error message without showing the minimum number of required characters', 'simpleform' ),
					'hidechars'   => __( 'Keep checked if you want to show the minimum number of required characters and you want to make sure that\'s exactly the number you set for that specific field', 'simpleform' ),
					'privacy'     => __( 'privacy policy', 'simpleform' ),
					'top'         => $top_position,
					'bottom'      => $bottom_position,
					'smtpnotes'   => $smtp_notes,
					'required'    => __( '(required)', 'simpleform' ),
					'optional'    => __( '(optional)', 'simpleform' ),
					'storing_des' => $storing_notice,
				)
			);

		}
	}

	/**
	 * Form editor
	 *
	 * @since 1.0.0
	 * @since 2.2.0 Refactoring of code.
	 *
	 * @return void
	 */
	public function form_editor() {

		if ( ! current_user_can( 'manage_options' ) ) {
			die( 'Security checked!' );
		} else {

			$util               = new SimpleForm_Util();
			$validation         = new SimpleForm_Admin_Validation();
			$values             = $validation->sanitized_data();
			$newform            = $values['newform'];
			$form_id            = intval( $values['form'] );
			$widget_id          = intval( $values['widget_id'] );
			$embed_in           = intval( $values['embed_in'] );
			$form               = 0 === $widget_id ? $form_id : 0;
			$preassigned_name   = $util->get_sform_option( $form_id, 'attributes', 'form_name', '' );
			$preassigned_target = $util->get_sform_option( $form_id, 'attributes', 'show_for', 'all' );
			$form_name          = $values['form_name'];
			$target_values      = $validation->target_fields( $widget_id );
			$show_for           = $target_values['show_for'];
			$user_role          = $target_values['user_role'];

			// Make the selected options validation.
			$error = $validation->options_validation( $values );

			if ( ! empty( $error ) ) {

				$data = array(
					'error'   => true,
					'update'  => false,
					'message' => $error,
				);

			} else {

				$update                  = '';
				$form_fields             = array();
				$form_fields['name']     = $validation->targeted_form_fields( $show_for, 'name_field' );
				$form_fields['lastname'] = $validation->targeted_form_fields( $show_for, 'lastname_field' );
				$form_fields['email']    = $validation->targeted_form_fields( $show_for, 'email_field' );
				$form_fields['phone']    = $validation->targeted_form_fields( $show_for, 'phone_field' );
				$form_fields['website']  = $validation->targeted_form_fields( $show_for, 'website_field' );
				$form_fields['subject']  = $validation->targeted_form_fields( $show_for, 'subject_field' );
				$form_fields['consent']  = $validation->targeted_form_fields( $show_for, 'consent_field' );
				$form_fields['captcha']  = $validation->targeted_form_fields( $show_for, 'captcha_field' );

				// Detect a form specifics updating.
				$update = apply_filters( 'sform_specifics_updating', $update, $newform, $form_id, $show_for, $user_role, $form_name, $preassigned_name, $preassigned_target );

				// Detect a form attributes updating.
				$update = apply_filters( 'sform_attributes_updating', $update, $form, $show_for, $user_role, $form_fields, $values );

				if ( $update ) {

					if ( empty( $newform ) ) {

						$data = array(
							'error'   => false,
							'update'  => true,
							'message' => __( 'The contact form has been updated', 'simpleform' ),
						);

					} else {
						$post = 0 !== $embed_in ? '&post=' . $embed_in : '';
						$url  = admin_url( 'admin.php?page=sform-settings&form=' ) . $update . '&status=new' . $post;
						set_transient( 'sform_action_newform', $update, 30 );
						$data = array(
							'error'    => false,
							'update'   => true,
							'redirect' => true,
							'url'      => $url,
							'message'  => __( 'The contact form has been created', 'simpleform' ),
						);
					}
				} else {

					$data = array(
						'error'   => false,
						'update'  => false,
						'message' => __( 'The contact form has already been updated', 'simpleform' ),
					);

				}
			}

			echo wp_json_encode( $data );
			wp_die();

		}
	}

	/**
	 * Form settings
	 *
	 * @since 1.0.0
	 * @since 2.2.0 Refactoring of code.
	 *
	 * @return void
	 */
	public function form_settings() {

		if ( ! current_user_can( 'manage_options' ) ) {
			die( 'Security checked!' );
		} else {

			$validation = new SimpleForm_Admin_Validation();
			$values     = $validation->sanitized_settings();
			$form_id    = $values['form'];

			// Make the settings validation.
			$error = $validation->settings_validation( $values );

			// Validate SimpleForm Contact Form Submissions options.
			$error = apply_filters( 'submissions_settings_validation', $error );

			// Validate SimpleForm Akismet options.
			$error = apply_filters( 'akismet_settings_validation', $error );

			// Validate SimpleForm reCAPTCHA options.
			$error = apply_filters( 'recaptcha_settings_validation', $error );

			if ( ! empty( $error ) ) {

				$data = array(
					'error'   => true,
					'update'  => false,
					'message' => $error,
				);

			} else {

				$update = '';
				$update = apply_filters( 'sform_settings_updating', $update, $form_id, $values );

				if ( $update ) {

					$data = array(
						'error'   => false,
						'update'  => true,
						'message' => __( 'Settings were successfully saved', 'simpleform' ),
					);

				} else {

					$data = array(
						'error'   => false,
						'update'  => false,
						'message' => __( 'Settings have already been saved', 'simpleform' ),
					);

				}
			}

			echo wp_json_encode( $data );
			wp_die();

		}
	}

	/**
	 * Add the link to the Privacy Policy page in the consent label.
	 *
	 * @since 1.9.2
	 * @since 2.2.0 Refactoring of code.
	 *
	 * @return void
	 */
	public function privacy_page_setting() {

		if ( ! current_user_can( 'manage_options' ) ) {
			die( 'Security checked!' );
		} else {

			$validation       = new SimpleForm_Admin_Validation();
			$values           = $validation->sanitized_data();
			$page             = intval( $values['page_id'] );
			$consent_label    = strval( $values['consent_label'] );
			$target_attribute = $values['target'];

			if ( $page > 0 ) {

				$url               = get_page_link( $page );
				$pattern           = '# href="(.*?)"#i';
				$target            = $target_attribute ? ' target="_blank"' : '';
				$hyperlink         = ' href="' . $url . '"';
				$privacy_string    = __( 'privacy policy', 'simpleform' );
				$default_hyperlink = '<a' . $hyperlink . $target . '>' . $privacy_string . '</a>';

				// If an hyperlink already exists edit the href attribute, otherwise add a new hyperlink.
				if ( preg_match( $pattern, $consent_label ) ) {
					$label = preg_replace( $pattern, $hyperlink, html_entity_decode( $consent_label ) );
					if ( ! $target_attribute && strpos( html_entity_decode( $consent_label ), ' target="_blank"' ) !== false ) {
						$label = str_replace( ' target="_blank"', '', html_entity_decode( $consent_label ) );
					}
					if ( $target_attribute && strpos( html_entity_decode( $consent_label ), ' target="_blank"' ) === false ) {
						$label = str_replace( '">', '" target="_blank"> ', html_entity_decode( $consent_label ) );
					}
					echo wp_json_encode(
						array(
							'error' => false,
							'label' => $label,
						)
					);
					exit;
				} else {
					/* translators: %s: privacy policy, it can contain the hyperlink to the page */
					$label = sprintf( __( 'I have read and consent to the %s', 'simpleform' ), $default_hyperlink );
					echo wp_json_encode(
						array(
							'error' => false,
							'label' => $label,
						)
					);
					exit;
				}
			}

			die();
		}
	}

	/**
	 * Edit the target attribute to control what happens when the link to the Privacy Policy page is clicked.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function privacy_page_opening() {

		if ( ! current_user_can( 'manage_options' ) ) {
			die( 'Security checked!' );
		} else {

			$validation       = new SimpleForm_Admin_Validation();
			$values           = $validation->sanitized_data();
			$consent_label    = strval( $values['consent_label'] );
			$target_attribute = $values['target'];
			$pattern          = '#<a.*?>(.*?)</a>#i';

			if ( preg_match( $pattern, $consent_label ) ) {

				if ( $target_attribute ) {

					$label = strpos( html_entity_decode( $consent_label ), ' target="_blank"' ) === false ? str_replace( '">', '" target="_blank">', html_entity_decode( $consent_label ) ) : $consent_label;
					$data  = array(
						'target' => true,
						'label'  => $label,
					);

				} else {

					$label = strpos( html_entity_decode( $consent_label ), ' target="_blank"' ) !== false ? str_replace( ' target="_blank"', '', html_entity_decode( $consent_label ) ) : $consent_label;
					$data  = array(
						'target' => false,
						'label'  => $label,
					);

				}
			} else {

				$data = array(
					'target' => false,
					'label'  => $consent_label,
				);

			}

			echo wp_json_encode( $data );
			wp_die();

		}
	}

	/**
	 * Change admin color scheme.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function admin_color_scheme() {

		if ( ! current_user_can( 'manage_options' ) ) {
			die( 'Security checked!' );
		} else {

			$validation  = new SimpleForm_Admin_Validation();
			$values      = $validation->sanitized_settings();
			$admin_color = in_array( $values['admin_color'], array( 'default', 'light', 'modern', 'blue', 'coffee', 'ectoplasm', 'midnight', 'ocean', 'sunrise', 'foggy', 'polar' ), true ) ? $values['admin_color'] : 'default';

			$main_settings                = array( get_option( 'sform_settings' ) );
			$main_settings['admin_color'] = $admin_color;
			$update                       = update_option( 'sform_settings', $main_settings );

			if ( $update ) {

				$ids = wp_cache_get( 'sform_form_ids' );
				// Do a database query and save it to the cache if the there is no cache data with this key.
				if ( false === $ids ) {
					global $wpdb;
					$ids = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}sform_shortcodes WHERE id != '1'" ); // phpcs:ignore.
					wp_cache_set( 'sform_form_ids', $ids );
				}

				if ( $ids ) {
					foreach ( $ids as $id ) {
						$form_settings = get_option( 'sform_' . $id . '_settings' );
						if ( is_array( $form_settings ) ) {
							$form_settings['admin_color'] = $admin_color;
							update_option( 'sform_' . $id . '_settings', $form_settings );
						}
					}
				}
				$data = array(
					'error' => false,
					'color' => $admin_color,
				);
			} else {
				$data = array( 'error' => true );
			}

			echo wp_json_encode( $data );
			wp_die();

		}
	}

	/**
	 * Delete the plugin tables whenever a single site into a network is deleted.
	 *
	 * @since 1.2.0
	 *
	 * @param string[] $tables Array of names of the site tables to be dropped.
	 *
	 * @return string[] Array of names of the site tables to be dropped.
	 */
	public function on_delete_blog( $tables ) {

		global $wpdb;
		$tables[] = $wpdb->prefix . 'sform_shortcodes';
		$tables[] = $wpdb->prefix . 'sform_submissions';

		return $tables;
	}
}
