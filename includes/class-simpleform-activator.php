<?php
/**
 * File delegated to the plugin activation.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * The class instantiated during the plugin activation.
 */
class SimpleForm_Activator {

	/**
	 * Run default functionality during plugin activation.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param bool $network_wide Whether to enable the plugin for all sites in the network
	 *                           or just the current site. Multisite only. Default false.
	 *
	 * @return void
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				global $wpdb;
				$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" ); // phpcs:ignore

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::create_db();
					self::default_data_entry();
					self::sform_settings();
					self::sform_fields();
					self::enqueue_additional_code();
					restore_current_blog();
				}
			} else {
				self::create_db();
				self::default_data_entry();
				self::sform_settings();
				self::sform_fields();
				self::enqueue_additional_code();
			}
		} else {

			self::create_db();
			self::default_data_entry();
			self::sform_settings();
			self::sform_fields();
			self::enqueue_additional_code();

		}
	}

	/**
	 * Create custom tables.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function create_db() {

		$current_db_version = SIMPLEFORM_DB_VERSION;
		$installed_version  = strval( get_option( 'sform_db_version' ) );

		if ( $installed_version !== $current_db_version ) {

			global $wpdb;
			$shortcodes_table  = $wpdb->prefix . 'sform_shortcodes';
			$submissions_table = $wpdb->prefix . 'sform_submissions';
			$charset_collate   = $wpdb->get_charset_collate();

			$sql_shortcodes = "CREATE TABLE {$shortcodes_table} (
				id int(11) NOT NULL AUTO_INCREMENT,
				shortcode text NOT NULL,
				name text NOT NULL,
				area varchar(500) NOT NULL DEFAULT 'page',
				widget smallint(5) UNSIGNED NOT NULL DEFAULT '0',
				form_pages text NOT NULL,
				form_widgets text NOT NULL,
				target tinytext NOT NULL,
				role tinytext NOT NULL,
				creation datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				entries mediumint(9) NOT NULL DEFAULT '0',
				moved_entries mediumint(9) NOT NULL DEFAULT '0',
				relocation tinyint(1) NOT NULL DEFAULT '0',
				moveto int(11) NOT NULL DEFAULT '0',
				moved_to text NOT NULL,
				to_be_moved varchar(32) NOT NULL default '',
				onetime_moving tinyint(1) NOT NULL DEFAULT '1',
				deletion tinyint(1) NOT NULL DEFAULT '0',
				status tinytext NOT NULL,
				previous_status varchar(32) NOT NULL default '',
				storing tinyint(1) NOT NULL DEFAULT '1',
				override_settings tinyint(1) NOT NULL DEFAULT '0',
				PRIMARY KEY  (id) 
			) {$charset_collate};";

			$sql_submissions = "CREATE TABLE {$submissions_table} (
				id int(11) NOT NULL AUTO_INCREMENT,
				form int(7) NOT NULL DEFAULT '1',
				moved_from int(7) NOT NULL DEFAULT '0',
				requester_type tinytext NOT NULL,
				requester_id int(15) NOT NULL DEFAULT '0',
				date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				status tinytext NOT NULL,
				previous_status varchar(32) NOT NULL default '',
				trash_date datetime NULL,
				hidden tinyint(1) NOT NULL DEFAULT '0',
				notes text NULL,
				PRIMARY KEY  (id)
			) {$charset_collate};";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			dbDelta( $sql_shortcodes );
			dbDelta( $sql_submissions );

			// Drops unused column from database tables, if it exists.
			$drop_shortcode_pages = "ALTER TABLE {$shortcodes_table} DROP COLUMN shortcode_pages";
			$drop_block_pages     = "ALTER TABLE {$shortcodes_table} DROP COLUMN block_pages";
			$drop_widget_id       = "ALTER TABLE {$shortcodes_table} DROP COLUMN widget_id";

			if ( false !== $installed_version && version_compare( $installed_version, '2.2.0', '<' ) ) {

				self::drop_column( $shortcodes_table, 'shortcode_pages', $drop_shortcode_pages );
				self::drop_column( $shortcodes_table, 'block_pages', $drop_block_pages );
				self::drop_column( $shortcodes_table, 'widget_id', $drop_widget_id );

				self::widgets_checking();

			}

			update_option( 'sform_db_version', $current_db_version );

		}
	}

	/**
	 * Save default properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function default_data_entry() {

		global $wpdb;
		$form_data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sform_shortcodes" ); // phpcs:ignore

		if ( 0 === count( $form_data ) ) {

			$initial_data = array(
				'shortcode' => 'simpleform',
				'name'      => __( 'Contact Us Page', 'simpleform' ),
				'status'    => 'draft',
				'creation'  => gmdate( 'Y-m-d H:i:s' ),
			);

			$wpdb->insert( $wpdb->prefix . 'sform_shortcodes', $initial_data ); // phpcs:ignore

		} else {

			$entries_data = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions" ); // phpcs:ignore
			$entries      = $wpdb->get_var( "SELECT SUM(entries) as total_entries FROM {$wpdb->prefix}sform_shortcodes" ); // phpcs:ignore
			$moveto_col   = $wpdb->get_var( "SELECT COUNT(DISTINCT moveto) FROM {$wpdb->prefix}sform_shortcodes" ); // phpcs:ignore
			$moved_to_col = $wpdb->get_var( "SELECT COUNT(DISTINCT moved_to) FROM {$wpdb->prefix}sform_shortcodes" ); // phpcs:ignore		

			// Check if the counters has not updated.
			if ( $entries_data !== $entries || $moveto_col !== $moved_to_col ) {

				$util  = new SimpleForm_Util();
				$forms = $util->sform_ids();

				foreach ( $forms as $form ) {

					$where         = defined( 'SIMPLEFORM_SUBMISSIONS_NAME' ) ? " AND object != '' AND object != 'not stored' AND listable = '1'" : '';
					$entries       = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions WHERE form = %d {$where}", $form ) ); // phpcs:ignore
					$moved_entries = $wpdb->get_results( $wpdb->prepare( "SELECT form FROM {$wpdb->prefix}sform_submissions WHERE moved_from = %d {$where}", $form ) ); // phpcs:ignore

					$list = array();

					foreach ( $moved_entries as $row ) {
						$list[] = $row->form;
					}

					$moved_to = array_unique( $list );

					$updated_data = array(
						'entries'       => $entries,
						'moved_entries' => count( $moved_entries ),
						'moved_to'      => maybe_serialize( $moved_to ),
					);

					$wpdb->update( $wpdb->prefix . 'sform_shortcodes', $updated_data, array( 'id' => $form ) ); // phpcs:ignore

				}
			}
		}
	}

	/**
	 * Create a table whenever a new blog is created in a WordPress Multisite installation.
	 *
	 * @since 1.2.0
	 *
	 * @param WP_Site $new_site New site object.
	 *
	 * @return void
	 */
	public static function on_create_blog( $new_site ) {

		if ( is_plugin_active_for_network( 'simpleform/simpleform.php' ) ) {

			switch_to_blog( (int) $new_site->blog_id );
			self::create_db();
			self::default_data_entry();
			self::sform_settings();
			self::sform_fields();
			self::enqueue_additional_code();
			restore_current_blog();

		}
	}

	/**
	 * Specify the initial settings.
	 *
	 * @since 1.8.4
	 *
	 * @return void
	 */
	public static function sform_settings() {

		if ( ! get_option( 'sform_settings' ) ) {

			$form_page         = array(
				'post_type'    => 'page',
				'post_content' => '<!-- wp:simpleform/form-selector {"formId":"1","optionNew":"d-none","formOptions":"visible"} /-->',
				'post_title'   => __( 'Contact Us', 'simpleform' ),
				'post_status'  => 'draft',
			);
			$thank_you_message = '<div><h4>' . __( 'Thank you for contacting us.', 'simpleform' ) . '</h4><br>' . __( 'Your message will be reviewed soon, and we\'ll get back to you as quickly as possible.', 'simpleform' ) . '</br><img src="' . plugin_dir_url( __DIR__ ) . 'public/img/confirmation.png" alt="message received"></div>';
			$confirmation_page = array(
				'post_type'    => 'page',
				'post_content' => $thank_you_message,
				'post_title'   => __( 'Thanks!', 'simpleform' ),
				'post_status'  => 'draft',
			);

			$settings = array(
				'admin_notices'          => false,
				'frontend_notice'        => true,
				'admin_color'            => 'default',
				'ajax_submission'        => false,
				'html5_validation'       => false,
				'focus'                  => 'field',
				'form_template'          => 'default',
				'stylesheet'             => false,
				'javascript'             => false,
				'deletion_data'          => true,
				'multiple_spaces'        => false,
				'outside_error'          => 'bottom',
				'empty_fields'           => __( 'There were some errors that need to be fixed', 'simpleform' ),
				'characters_length'      => false,
				'empty_name'             => __( 'Please provide your name', 'simpleform' ),
				'generic_name'           => __( 'Please type your full name', 'simpleform' ),
				'invalid_name'           => __( 'The name contains invalid characters', 'simpleform' ),
				'name_error'             => __( 'Error occurred validating the name', 'simpleform' ),
				'empty_email'            => __( 'Please provide your email address', 'simpleform' ),
				'invalid_email'          => __( 'Please enter a valid email', 'simpleform' ),
				'email_error'            => __( 'Error occurred validating the email', 'simpleform' ),
				'empty_subject'          => __( 'Please enter the request subject', 'simpleform' ),
				'generic_subject'        => __( 'Please type a short and specific subject', 'simpleform' ),
				'invalid_subject'        => __( 'Enter only alphanumeric characters and punctuation marks', 'simpleform' ),
				'subject_error'          => __( 'Error occurred validating the subject', 'simpleform' ),
				'empty_message'          => __( 'Please enter your message', 'simpleform' ),
				'generic_message'        => __( 'Please type a clearer message so we can respond appropriately', 'simpleform' ),
				'invalid_message'        => __( 'Enter only alphanumeric characters and punctuation marks', 'simpleform' ),
				'message_error'          => __( 'Error occurred validating the message', 'simpleform' ),
				'consent_error'          => __( 'Please accept our privacy policy before submitting form', 'simpleform' ),
				'honeypot_error'         => __( 'Failed honeypot validation', 'simpleform' ),
				'server_error'           => __( 'Error occurred during processing data. Please try again!', 'simpleform' ),
				'duplicate_error'        => __( 'The form has already been submitted. Thanks!', 'simpleform' ),
				'success_action'         => 'message',
				'success_message'        => $thank_you_message,
				'redirect'               => false,
				'redirect_url'           => '',
				'server_smtp'            => false,
				'notification'           => true,
				'notification_recipient' => get_option( 'admin_email' ),
				'bcc'                    => '',
				'notification_email'     => get_option( 'admin_email' ),
				'notification_name'      => 'requester',
				'custom_sender'          => get_bloginfo( 'name' ),
				'notification_subject'   => 'request',
				'notification_reply'     => true,
				'submission_number'      => 'visible',
				'autoresponder'          => false,
				'duplicate'              => true,
				'form_pageid'            => wp_insert_post( $form_page ),
				'confirmation_pageid'    => wp_insert_post( $confirmation_page ),
			);

			add_option( 'sform_settings', $settings );

		} else {

			$util                 = new SimpleForm_Util();
			$form_page_id         = absint( $util->get_sform_option( 1, 'settings', 'form_pageid', 0 ) );
			$confirmation_page_id = absint( $util->get_sform_option( 1, 'settings', 'confirmation_pageid', 0 ) );

			if ( 'trash' === get_post_status( $form_page_id ) ) {
				wp_update_post(
					array(
						'ID'          => $form_page_id,
						'post_status' => 'draft',
					)
				);
			}
			if ( 'trash' === get_post_status( $confirmation_page_id ) ) {
				wp_update_post(
					array(
						'ID'          => $confirmation_page_id,
						'post_status' => 'draft',
					)
				);
			}
		}
	}

	/**
	 *  Specify the initial attributes.
	 *
	 * @since 1.8.4
	 *
	 * @return void
	 */
	public static function sform_fields() {

		if ( ! get_option( 'sform_attributes' ) ) {

			$attributes = array(
				'form_name'           => __( 'Contact Us Page', 'simpleform' ),
				'show_for'            => 'all',
				'introduction_text'   => '<p>' . __( 'Please fill out the form below and we will get back to you as soon as possible. Mandatory fields are marked with (*).', 'simpleform' ) . '</p>',
				'name_field'          => 'visible',
				'name_visibility'     => 'visible',
				'name_label'          => __( 'Name', 'simpleform' ),
				'name_minlength'      => '2',
				'name_maxlength'      => '0',
				'name_requirement'    => 'required',
				'lastname_field'      => 'hidden',
				'email_field'         => 'visible',
				'email_visibility'    => 'visible',
				'email_label'         => __( 'Email', 'simpleform' ),
				'email_requirement'   => 'required',
				'phone_field'         => 'hidden',
				'subject_field'       => 'visible',
				'subject_visibility'  => 'visible',
				'subject_label'       => __( 'Subject', 'simpleform' ),
				'subject_minlength'   => '5',
				'subject_maxlength'   => '0',
				'subject_requirement' => 'required',
				'message_visibility'  => 'visible',
				'message_label'       => __( 'Message', 'simpleform' ),
				'message_minlength'   => '10',
				'message_maxlength'   => '0',
				'consent_field'       => 'visible',
				'consent_label'       => __( 'I have read and consent to the privacy policy', 'simpleform' ),
				'privacy_link'        => false,
				'consent_requirement' => 'required',
				'captcha_field'       => 'hidden',
				'submit_label'        => __( 'Submit', 'simpleform' ),
				'label_position'      => 'top',
				'submit_position'     => 'centred',
				'required_sign'       => true,
				'form_direction'      => 'ltr',
			);

			add_option( 'sform_attributes', $attributes );

		}
	}

	/**
	 * Add additional styles and scripts to enqueue.
	 *
	 * @since 2.1.8
	 *
	 * @return void
	 */
	public static function enqueue_additional_code() {

		global $wpdb;
		$style_options = $wpdb->get_results( "SELECT * FROM $wpdb->options WHERE option_name LIKE 'sform_additional_s%'" ); // phpcs:ignore

		if ( 0 === count( $style_options ) ) {

			$util               = new SimpleForm_Util();
			$forms              = $util->sform_ids();
			$additional_styles  = '';
			$additional_scripts = '';

			foreach ( $forms as $form ) {

				$form_style      = strval( $util->get_sform_option( $form, 'attributes', 'additional_css', '' ) );
				$ajax            = $util->get_sform_option( $form, 'settings', 'ajax_submission', false );
				$ajax_error      = strval( $util->get_sform_option( $form, 'settings', 'ajax_error', __( 'Error occurred during AJAX request. Please contact support!', 'simpleform' ) ) );
				$outside_error   = $util->get_sform_option( $form, 'settings', 'outside_error', 'bottom' );
				$outside         = 'none' === $outside_error ? 'false' : 'true';
				$multiple_spaces = $util->get_sform_option( $form, 'settings', 'multiple_spaces', false );

				if ( $form_style ) {

					$additional_styles .= '/*' . $form . '*/' . $form_style . '/* END ' . $form . '*/';

				}

				if ( $multiple_spaces || $ajax ) {

					$spaces_script       = $multiple_spaces ? 'jQuery(document).ready(function(){jQuery( "input[parent=\'' . $form . '\'],textarea[parent=\'' . $form . '\']" ).on( "input",function(){jQuery(this).val(jQuery(this).val().replace(/\s\s+/g," " ) );});});' : '';
					$ajax_script         = $ajax ? 'var outside' . $form . ' = "' . $outside . '"; var ajax_error' . $form . ' = "' . $ajax_error . '";' : '';
					$additional_scripts .= '/*' . $form . '*/' . $spaces_script . $ajax_script . '/* END ' . $form . '*/';

				}
			}

			update_option( 'sform_additional_style', $additional_styles );
			update_option( 'sform_additional_script', $additional_scripts );

		}
	}

	/**
	 * Drops column from database table, if it exists.
	 *
	 * @since 2.2.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $table_name  Database table name.
	 * @param string $column_name Table column name.
	 * @param string $drop_ddl    SQL statement to drop column.
	 *
	 * @return bool True on success or if the column doesn't exist. False on failure.
	 */
	private static function drop_column( $table_name, $column_name, $drop_ddl ) {

		global $wpdb;
		$query = wp_cache_get( 'sform_columns_dropping' );

		// Do a database query and save it to the cache if the there is no cache data with this key.
		if ( false === $query ) {
			$query = $wpdb->get_col( "DESC $table_name", 0 ); // phpcs:ignore
			wp_cache_set( 'sform_columns_dropping', $query );
		}

		foreach ( $query as $column ) {

			if ( $column === $column_name ) {

				// Found it, so try to drop it.
				$wpdb->query( $drop_ddl ); // // phpcs:ignore

				// We cannot directly tell that whether this succeeded!
				foreach ( $query as $column ) {
					if ( $column === $column_name ) {
						return false;
					}
				}
			}
		}

		// Else didn't find it.
		return true;
	}

	/**
	 * Remove data of any deleted widgets and update data of widgets currently in use
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public static function widgets_checking() {

		global $wpdb;

		// Get the widgets currently in use.
		$widget_instances = (array) get_option( 'widget_sform_widget', array() );
		$sidebars_widgets = (array) get_option( 'sidebars_widgets', array() );
		// Return the integers widgets keys.
		$widget_keys = array_filter( array_keys( $widget_instances ), 'is_int' );

		if ( ! empty( $widget_keys ) ) {

			$util = new SimpleForm_Util();

			foreach ( $widget_keys as $widget_id ) {

				// Rename the widget settings.
				$widget_instances[ $widget_id ] = (array) $widget_instances[ $widget_id ];

				if ( isset( $widget_instances[ $widget_id ]['shortcode_id'] ) ) {
					$form      = $widget_instances[ $widget_id ]['shortcode_id'];
					$form_name = strval( $util->get_sform_option( $form, 'attributes', 'form_name', __( 'Unnamed', 'simpleform' ) ) );
				} else {
					$form      = '';
					$form_name = '';
				}

				$widget_instances[ $widget_id ]['form']          = $form;
				$widget_instances[ $widget_id ]['title']         = $widget_instances[ $widget_id ]['sform_widget_title'];
				$widget_instances[ $widget_id ]['form_name']     = $form_name;
				$widget_instances[ $widget_id ]['show_for']      = $widget_instances[ $widget_id ]['sform_widget_audience'];
				$widget_instances[ $widget_id ]['user_role']     = $widget_instances[ $widget_id ]['sform_widget_role'];
				$widget_instances[ $widget_id ]['display_in']    = $widget_instances[ $widget_id ]['sform_widget_visibility'];
				$widget_instances[ $widget_id ]['visible_pages'] = $widget_instances[ $widget_id ]['sform_widget_visible_pages'];
				$widget_instances[ $widget_id ]['hidden_pages']  = $widget_instances[ $widget_id ]['sform_widget_hidden_pages'];
				$widget_instances[ $widget_id ]['id']            = $widget_instances[ $widget_id ]['sform_widget_id'];
				$widget_instances[ $widget_id ]['class']         = $widget_instances[ $widget_id ]['sform_widget_class'];
				unset( $widget_instances[ $widget_id ]['shortcode_id'] );
				unset( $widget_instances[ $widget_id ]['sform_widget_title'] );
				unset( $widget_instances[ $widget_id ]['sform_widget_audience'] );
				unset( $widget_instances[ $widget_id ]['sform_widget_role'] );
				unset( $widget_instances[ $widget_id ]['sform_widget_visibility'] );
				unset( $widget_instances[ $widget_id ]['sform_widget_visible_pages'] );
				unset( $widget_instances[ $widget_id ]['sform_widget_hidden_pages'] );
				unset( $widget_instances[ $widget_id ]['sform_widget_id'] );
				unset( $widget_instances[ $widget_id ]['sform_widget_class'] );

				foreach ( $sidebars_widgets as $sidebar => $widgets ) {

					$widget = 'sform_widget-' . $widget_id;

					if ( is_array( $widgets ) && in_array( $widget, $widgets, true ) ) {

						if ( 'wp_inactive_widgets' === $sidebar ) {
							$wpdb->update( $wpdb->prefix . 'sform_shortcodes', array( 'status' => 'inactive' ), array( 'widget' => $widget_id ) ); // phpcs:ignore
							$widget_instances[ $widget_id ]['area']   = __( 'Inactive', 'simpleform' );
							$widget_instances[ $widget_id ]['status'] = 'inactive';
						} else {
							$widget_instances[ $widget_id ]['area']   = '';
							$widget_instances[ $widget_id ]['status'] = 'published';
						}

						// Rename the widget ID.
						$sidebars_widgets[ $sidebar ]         = (array) $sidebars_widgets[ $sidebar ];
						$key                                  = array_search( $widget, $widgets, true );
						$sidebars_widgets[ $sidebar ][ $key ] = 'simpleform-' . $widget_id;
					}
				}
			}

			update_option( 'widget_simpleform', $widget_instances );
			update_option( 'sidebars_widgets', $sidebars_widgets );
			// Clear object cache after updating to avoid using old options.
			wp_cache_flush();

		}

		// Get all widgets forms that have been created.
		$forms_data   = $wpdb->get_results( "SELECT id, widget FROM {$wpdb->prefix}sform_shortcodes WHERE widget != '0'", 'ARRAY_A' ); // phpcs:ignore
		$form_ids     = array_column( $forms_data, 'id' );
		$widget_forms = array_column( $forms_data, 'widget' );
		// Search undeleted widget forms.
		$undeleted_widgets = array_keys( array_diff( $widget_forms, $widget_keys ) );

		foreach ( $undeleted_widgets as $key ) {

			$form_id = $form_ids[ $key ];
			$wpdb->delete( $wpdb->prefix . 'sform_shortcodes', array( 'id' => $form_id ) ); // phpcs:ignore
			$wpdb->delete( $wpdb->prefix . 'sform_submissions', array( 'form' => $form_id ) ); // phpcs:ignore
			delete_option( 'sform_' . $form_id . '_attributes' );
			delete_option( 'sform_' . $form_id . '_settings' );
			$pattern = 'sform_%_' . $form_id . '_%';
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $pattern ) ); // phpcs:ignore

		}
	}
}
