<?php
/**
 * File delegated to manage the SimpleForm widget.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the widget.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.LongVariable)
 *
 * @template T of array<string, mixed>
 * @extends WP_Widget<T>
 */
class SimpleForm_Widget extends WP_Widget {

	/**
	 * Add compatibility with the legacy widget block for versions of WordPress prior to 5.8 release.
	 *
	 * @var bool $show_instance_in_rest Show the widget's instance settings in the REST API.
	 */
	public $show_instance_in_rest = true;

	/**
	 * Widget constructor.
	 *
	 * @since 1.10.0
	 */
	public function __construct() {

		if ( version_compare( $GLOBALS['wp_version'], '5.8', '<' ) ) {
			$widget_options = array(
				'classname'   => __FUNCTION__,
				'description' => __( 'Display a contact form.', 'simpleform' ),
			);
		} else {
			$widget_options = array(
				'classname'             => __FUNCTION__,
				'description'           => __( 'Display a contact form.', 'simpleform' ),
				'show_instance_in_rest' => true,
			);
		}

		parent::__construct( 'simpleform', __( 'SimpleForm', 'simpleform' ), $widget_options );

		// Update form data when updating sidebars.
		add_filter( 'pre_update_option_sidebars_widgets', array( $this, 'widget_data_updating' ), 10, 2 );
		// Delete the form after a widget has been marked for deletion.
		add_action( 'delete_widget', array( $this, 'cleanup_form_widget' ), 10, 3 );
		// Hide the widget from the legacy widget block.
		add_filter( 'widget_types_to_hide_from_legacy_widget_block', array( $this, 'hide_simpleform' ) );
		// Edit form usage data when updating block widgets.
		add_action( 'update_option_widget_block', array( $this, 'block_widgets_updating' ) );
		// Update forms data after inactive widgets have been cleared.
		add_action( 'update_option_sidebars_widgets', array( $this, 'inactive_widgets_clearing' ), 10, 2 );
	}

	/**
	 * Output the widget admin form.
	 *
	 * @since 1.10.0
	 *
	 * @param mixed[] $instance Array of settings for the current widget.
	 *
	 * @return string The HTML markup for the form widget.
	 */
	public function form( $instance ): string {

		$title          = $this->get_widget_option( $instance, 'title' );
		$form_name      = $this->get_widget_option( $instance, 'form_name' );
		$audience       = $this->get_widget_option( $instance, 'show_for' );
		$role           = $this->get_widget_option( $instance, 'user_role' );
		$visibility     = $this->get_widget_option( $instance, 'display_in' );
		$hidden_pages   = $this->get_widget_option( $instance, 'hidden_pages' );
		$visible_pages  = $this->get_widget_option( $instance, 'visible_pages' );
		$widget_id      = $this->get_widget_option( $instance, 'id' );
		$class_selector = $this->get_widget_option( $instance, 'class' );
		$shortcode_id   = $this->get_widget_option( $instance, 'form' );
		$util           = new SimpleForm_Util();
		$color          = strval( $util->get_sform_option( 1, 'settings', 'admin_color', 'default' ) );
		$target_class   = 'in' !== $audience ? ' unseen' : '';
		$name_class     = $shortcode_id ? '' : 'name-alert';
		$allowed_html   = $util->sform_allowed_tags();
		global $wp_roles;
		$role_options = '';
		foreach ( $wp_roles->roles as $wp_role => $details ) {
			$role_selected = $wp_role === $role ? 'selected="selected"' : '';
			$role_options .= '\n\t<option value="' . esc_attr( $wp_role ) . '" ' . $role_selected . '>' . translate_user_role( $details['name'] ) . '</option>';
		}
		$role_selector = '<select name="' . $this->get_field_name( 'user_role' ) . '" id="' . $this->get_field_id( 'user_role' ) . '" class="widefat"><option value="any">' . __( 'Any', 'simpleform' ) . '</option>' . $role_options . '</select>';

		$all_selected = array(
			'all' => 'selected="selected"',
			'out' => '',
			'in'  => '',
		);

		$out_selected = array(
			'all' => '',
			'out' => 'selected="selected"',
			'in'  => '',
		);

		$in_selected = array(
			'all' => '',
			'out' => '',
			'in'  => 'selected="selected"',
		);

		$hidden_class = array(
			'all'     => ' unseen',
			'hidden'  => '',
			'visible' => ' unseen',
		);

		$visible_class = array(
			'all'     => ' unseen',
			'hidden'  => ' unseen',
			'visible' => '',
		);

		$always_selected = array(
			'all'     => 'selected="selected"',
			'hidden'  => '',
			'visible' => '',
		);

		$hidden_selected = array(
			'all'     => '',
			'hidden'  => 'selected="selected"',
			'visible' => '',
		);

		$visible_selected = array(
			'all'     => '',
			'hidden'  => '',
			'visible' => 'selected="selected"',
		);

		$widget = '<p><label for="' . $this->get_field_id( 'title' ) . '">' . __( 'Title:', 'simpleform' ) . '</label><input type="text" name="' . $this->get_field_name( 'title' ) . '" id="' . $this->get_field_id( 'title' ) . '" class="widefat" value="' . esc_attr( $title ) . '"></p>';

		$widget .= '<p><label for="' . $this->get_field_id( 'form_name' ) . '">' . __( 'Form name:', 'simpleform' ) . '</label><input type="text" name="' . $this->get_field_name( 'form_name' ) . '" id="' . $this->get_field_id( 'form_name' ) . '" class="widefat ' . $name_class . '" box="' . $this->number . '" value="' . esc_attr( $form_name ) . '" placeholder="' . __( 'Please provide a name for the form', 'simpleform' ) . '"></p>';

		$widget .= '<p><label for="' . $this->get_field_id( 'show_for' ) . '">' . __( 'Show for:', 'simpleform' ) . '</label><select name="' . $this->get_field_name( 'show_for' ) . '" id="' . $this->get_field_id( 'show_for' ) . '" class="widefat sform-target" parent="' . $this->number . '" ><option value="all" ' . $all_selected[ $audience ] . '>' . __( 'Everyone', 'simpleform' ) . '</option><option value="out" ' . $out_selected[ $audience ] . '>' . __( 'Logged-out users', 'simpleform' ) . '</option><option value="in" ' . $in_selected[ $audience ] . '>' . __( 'Logged-in users', 'simpleform' ) . '</option></select></p>';

		$widget .= '<p id="usertype" class="role-' . $this->number . $target_class . '"><label for="' . $this->get_field_id( 'user_role' ) . '">' . __( 'Role', 'simpleform' ) . ':</label>' . $role_selector . '</p>';

		$widget .= '<p id="visibility"><label for="' . $this->get_field_id( 'display_in' ) . '">' . __( 'Show/Hide on:', 'simpleform' ) . '</label><select name="' . $this->get_field_name( 'display_in' ) . '" id="' . $this->get_field_id( 'display_in' ) . '" class="widefat sfwidget" box="visibility-' . $this->number . '"><option value="all" ' . $always_selected[ $visibility ] . '>' . __( 'Show anywhere', 'simpleform' ) . '</option><option value="hidden" ' . $hidden_selected[ $visibility ] . '>' . __( 'Hide on selected', 'simpleform' ) . '</option><option value="visible" ' . $visible_selected[ $visibility ] . '>' . __( 'Show on selected', 'simpleform' ) . '</option></select></p>';

		$widget .= '<div id="sform-widget-hidden-pages" class="widget-pages visibility-' . $this->number . $hidden_class[ $visibility ] . '"><p class="first"><label for="' . $this->get_field_id( 'hidden_pages' ) . '">' . __( 'Selected pages', 'simpleform' ) . ':</label><input class="widefat" id="' . $this->get_field_id( 'hidden_pages' ) . '" name="' . $this->get_field_name( 'hidden_pages' ) . '" type="text" value="' . $hidden_pages . '"></p><p class="sform-widget-description">' . __( 'Use a comma-separated list of IDs for more than one page', 'simpleform' ) . '</p></div>';

		$widget .= '<div id="sform-widget-visible-pages" class="widget-pages visibility-' . $this->number . $visible_class[ $visibility ] . '"><p class="first"><label for="' . $this->get_field_id( 'visible_pages' ) . '">' . __( 'Selected pages', 'simpleform' ) . ':</label><input type="text" name="' . $this->get_field_name( 'visible_pages' ) . '" id="' . $this->get_field_id( 'visible_pages' ) . '" class="widefat" value="' . $visible_pages . '"></p><p class="sform-widget-description">' . __( 'Use a comma-separated list of IDs for more than one page', 'simpleform' ) . '</p></div>';

		$widget .= '<div class="sform-widget-boxes"><p><b style="font-size: 13px">' . __( 'Add CSS selectors to customize the widget:', 'simpleform' ) . '</b></p>';

		$widget .= '<p><label for="' . $this->get_field_id( 'id' ) . '">' . __( 'Custom ID:', 'simpleform' ) . '</label><input type="text" name="' . $this->get_field_name( 'id' ) . '" id="' . $this->get_field_id( 'id' ) . '" class="widefat" value="' . esc_attr( $widget_id ) . '"></p>';

		$widget .= '<p class="last"><label for="' . $this->get_field_id( 'class' ) . '">' . __( 'Custom Class:', 'simpleform' ) . '</label><input type="text" name="' . $this->get_field_name( 'class' ) . '" id="' . $this->get_field_id( 'class' ) . '" class="widefat" value="' . esc_attr( $class_selector ) . '"></p><p class="sform-widget-notes">' . __( 'Separate each class with a space', 'simpleform' ) . '</p></div>';

		$widget .= $shortcode_id ? '<div class="sform-widget-boxes buttons"><p><b style="font-size: 13px"> ' . __( 'Change how the form is displayed and operated:', 'simpleform' ) . '</b></p><p id="widget-buttons"><a href="' . admin_url( 'admin.php?page=sform-editor' ) . '&form=' . $shortcode_id . '" target="_blank"><span id="widget-button-editor" class="wp-core-ui button ' . $color . '">' . __( 'Open Editor', 'simpleform' ) . '</span></a><a href="' . admin_url( 'admin.php?page=sform-settings' ) . '&form=' . $shortcode_id . '" target="_blank"><span id="widget-button-settings" class="wp-core-ui button ' . $color . '">' . __( 'Open Settings', 'simpleform' ) . '</span></a></p></div>' : '<div id="alert-' . $this->number . '" class="widget-alert"><p><span><b style="font-size: 13px"> ' . __( 'Give a name to this form', 'simpleform' ) . '</span></b></p></div>';

		return print( wp_kses( $widget, $allowed_html ) );
	}

	/**
	 * Update the widget settings.
	 *
	 * @since 1.10.0
	 *
	 * @param mixed[] $new_instance Array of new settings for the current widget.
	 * @param mixed[] $old_instance Array of old settings for the current widget.
	 *
	 * @return mixed[] Array of settings for the current widget.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$form_name                 = $this->sanitized_widget_option( $instance, $new_instance, 'form_name' );
		$instance['title']         = $this->sanitized_widget_option( $instance, $new_instance, 'title' );
		$instance['show_for']      = $this->sanitized_widget_option( $instance, $new_instance, 'show_for' );
		$instance['user_role']     = $this->sanitized_widget_option( $instance, $new_instance, 'user_role' );
		$instance['display_in']    = $this->sanitized_widget_option( $instance, $new_instance, 'display_in' );
		$instance['hidden_pages']  = $this->sanitized_widget_option( $instance, $new_instance, 'hidden_pages' );
		$instance['visible_pages'] = $this->sanitized_widget_option( $instance, $new_instance, 'visible_pages' );
		$instance['id']            = $this->sanitized_widget_option( $instance, $new_instance, 'id' );
		$instance['class']         = $this->sanitized_widget_option( $instance, $new_instance, 'class' );
		$instance['area']          = '';
		$instance['status']        = '';

		global $wpdb;

		// If the widget has just been created.
		if ( ! isset( $instance['form'] ) ) {

			$form_id = wp_cache_get( 'sform_auto_increment' );

			// Do a database query and save it to the cache if the there is no cache data with this key.
			if ( false === $form_id ) {
				$rows    = $wpdb->get_row( "SHOW TABLE STATUS LIKE '{$wpdb->prefix}sform_shortcodes'" ); // phpcs:ignore.
				$form_id = $rows->Auto_increment; // phpcs:ignore
				wp_cache_set( 'sform_auto_increment', $form_id );
			}

			$shortcode = 'simpleform id="' . $form_id . '"';

			$update_data = array(
				'shortcode' => $shortcode,
				'name'      => __( 'Unnamed', 'simpleform' ),
				'widget'    => $this->number,
				'creation'  => gmdate( 'Y-m-d H:i:s' ),
				'target'    => 'all',
			);

			$update = $wpdb->insert( $wpdb->prefix . 'sform_shortcodes', $update_data ); // phpcs:ignore.

			if ( $update ) {
				$instance['form']             = $wpdb->insert_id;
				$attributes                   = (array) get_option( 'sform_attributes' );
				$settings                     = get_option( 'sform_settings' );
				$attributes['label_position'] = 'top';
				$attributes['form_name']      = __( 'Unnamed', 'simpleform' );
				add_option( 'sform_' . $instance['form'] . '_attributes', $attributes );
				add_option( 'sform_' . $instance['form'] . '_settings', $settings );
			}

			$instance['form_name'] = '';

			// Clear cache if data updated.
			$wpdb->flush();

		} else {

			// If no form name is provided, assign a form name.
			$instance['form_name'] = empty( $form_name ) ? $this->widget_form_naming( intval( $this->number ) ) : $form_name;

			if ( ( $old_instance['form_name'] !== $instance['form_name'] ) || ( $old_instance['show_for'] !== $instance['show_for'] ) || ( $old_instance['user_role'] !== $instance['user_role'] ) ) {

				$update_data = array(
					'name'   => $instance['form_name'],
					'target' => $instance['show_for'],
					'role'   => $instance['user_role'],
				);

				$update = $wpdb->update( $wpdb->prefix . 'sform_shortcodes', $update_data, array( 'id' => $instance['form'] ) ); // phpcs:ignore.

				if ( $update ) {
					$attributes              = (array) get_option( 'sform_' . $instance['form'] . '_attributes' );
					$attributes['form_name'] = $instance['form_name'];
					$attributes['show_for']  = $instance['show_for'];
					$attributes['user_role'] = $instance['user_role'];
					update_option( 'sform_' . $instance['form'] . '_attributes', $attributes );
				}

				// Clear cache if data updated.
				$wpdb->flush();

			}
		}

		return $instance;
	}

	/**
	 * Assign a widget form name
	 *
	 * @since 2.2.0
	 *
	 * @param int $number The ID number of the current instance.
	 *
	 * @return string The widget form name.
	 */
	protected function widget_form_naming( $number ) {

		$form_name = '';

		global $wp_registered_sidebars, $wpdb;
		$sidebars_widgets = (array) get_option( 'sidebars_widgets', array() );

		foreach ( $sidebars_widgets as $sidebar => $widgets ) {

			$widget_id = 'simpleform-' . $number;

			if ( is_array( $widgets ) && in_array( $widget_id, $widgets, true ) ) {

				if ( 'wp_inactive_widgets' !== $sidebar ) {
					$widget_area = $wp_registered_sidebars[ $sidebar ]['name'];
				} else {
					$widget_area = __( 'Inactive', 'simpleform' );
				}

				global $wpdb;
				$usage_counter = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}sform_shortcodes WHERE area = %s", $widget_area ) ); // phpcs:ignore

				$name_suffix = $usage_counter <= 1 ? '' : ' ' . ( $usage_counter++ );
				$form_name   = $widget_area . ' ' . __( 'Form', 'simpleform' ) . $name_suffix;

				break;

			}
		}

		return $form_name;
	}

	/**
	 * Display the widget on the site.
	 *
	 * @since 1.10.0
	 *
	 * @param string[] $args     Array of widget arguments.
	 * @param mixed[]  $instance Array of settings for the current widget.
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {

		global $post;

		if ( is_object( $post ) ) {

			$title          = $this->get_widget_option( $instance, 'title' );
			$id_selector    = $this->get_widget_option( $instance, 'id' );
			$class_selector = $this->get_widget_option( $instance, 'class' );
			$shortcode_id   = $this->get_widget_option( $instance, 'form' );
			$post_id        = isset( $post->ID ) ? $post->ID : '';

			$form  = $args['before_widget'] . '<div id="' . $id_selector . '" class="sforms-widget ' . $class_selector . '">';
			$form .= $title ? $args['before_title'] . $title . $args['after_title'] : '';
			$form .= do_shortcode( '[simpleform id="' . $shortcode_id . '"]' );
			$form .= '</div>' . $args['after_widget'];

			$audience      = $this->get_widget_option( $instance, 'show_for' );
			$role          = $this->get_widget_option( $instance, 'user_role' );
			$visibility    = $this->get_widget_option( $instance, 'display_in' );
			$hidden_pages  = $this->get_widget_option( $instance, 'hidden_pages' );
			$visible_pages = $this->get_widget_option( $instance, 'visible_pages' );
			$util          = new SimpleForm_Util();
			$notice        = $util->get_sform_option( (int) $shortcode_id, 'settings', 'frontend_notice', true );

			$target = array(
				'all' => __( 'everyone', 'simpleform' ),
				'in'  => '<b>' . __( 'logged-in users', 'simpleform' ) . '</b>',
				'out' => '<b>' . __( 'logged-out users', 'simpleform' ) . '</b>',
			);

			$form_user = $target[ $audience ];

			$role_notice = array(
				'all' => '',
				'in'  => ' ' . __( 'with the role of', 'simpleform' ) . ' <b>' . translate_user_role( ucfirst( $role ) ) . '</b>',
				'out' => '',
			);

			$form_user_role = $role_notice[ $audience ];

			$role_message = '<div id="sform-admin-message" style="font-size: 0.8em; border: 1px solid; margin-top: 20px; padding: 20px 15px; height: -webkit-fit-content; height: -moz-fit-content; height: fit-content;"><p class="heading" style="font-weight: 600; margin-bottom: 10px;">' . __( 'SimpleForm Admin Notice', 'simpleform' ) . '</p>' . __( 'The form is visible only for ', 'simpleform' ) . $form_user . $form_user_role . '. ' . __( 'Your role does not allow you to see it!', 'simpleform' ) . '</div>';

			$page_message = '<div id="sform-admin-message" style="font-size: 0.8em; border: 1px solid; margin-top: 20px; padding: 20px 15px; height: -webkit-fit-content; height: -moz-fit-content; height: fit-content;"><p class="heading" style="font-weight: 600; margin-bottom: 10px;">' . __( 'SimpleForm Admin Notice', 'simpleform' ) . '</p>' . __( 'The form cannot be viewed in this page due to visibility settings setted!', 'simpleform' ) . '</div>';

			$hidden_to_users = $this->display_for_users( $audience, $role );
			$hidden_to_pages = $this->display_for_pages( $visibility, $post_id, $hidden_pages, $visible_pages );
			$admin_notice    = $hidden_to_users ? $role_message : $page_message;

			if ( $hidden_to_users || $hidden_to_pages ) {

				// If widget cannot be displayed show an admin notice if option enabled.
				$form = current_user_can( 'edit_pages' ) && $notice ? $args['before_widget'] . $admin_notice . $args['after_widget'] : '';

			}

			$allowed_html = $util->sform_allowed_tags();
			echo wp_kses( $form, $allowed_html );

		}
	}

	/**
	 * Update form data when updating sidebars.
	 *
	 * @since 2.2.0
	 *
	 * @param mixed[] $sidebars_widgets The new option value for sidebars and their widgets.
	 * @param mixed[] $old_value        The old option value for sidebars and their widgets.
	 *
	 * @return mixed[] The option value for sidebars and their widgets.
	 */
	public function widget_data_updating( $sidebars_widgets, $old_value ) {

		if ( $sidebars_widgets !== $old_value ) {

			// Get the IDs of the widgets currently in use and exclude _multiwidget value 0.
			$sform_widget = (array) get_option( 'widget_simpleform', array() );
			$widget_ids   = array_diff( array_map( 'intval', array_keys( $sform_widget ) ), array( '0' ) );
			global $wp_registered_sidebars, $wpdb;

			foreach ( $widget_ids as $number ) {

				foreach ( $sidebars_widgets as $sidebar => $widgets ) {

					$widget_id = 'simpleform-' . $number;

					if ( is_array( $widgets ) && in_array( $widget_id, $widgets, true ) ) {

						if ( 'wp_inactive_widgets' !== $sidebar ) {
							$widget_area = $wp_registered_sidebars[ $sidebar ]['name'];
							$status      = 'published';
						} else {
							$widget_area = __( 'Inactive', 'simpleform' );
							$status      = 'inactive';
						}

						$widget_settings = (array) $sform_widget[ $number ];
						$form_id         = $widget_settings['form'];
						$form_area       = $widget_settings['area'];
						$form_status     = $widget_settings['status'];

						if ( $status !== $form_status || $widget_area !== $form_area ) {

							$update_data = array(
								'area'   => $widget_area,
								'status' => $status,
							);

							$wpdb->update( $wpdb->prefix . 'sform_shortcodes', $update_data, array( 'id' => $form_id ) ); // phpcs:ignore

							$widget_settings['status'] = $status;
							$widget_settings['area']   = $widget_area;
							$sform_widget[ $number ]   = $widget_settings;
							update_option( $this->option_name, $sform_widget );

						}

						break;

					}
				}
			}
		}

		return $sidebars_widgets;
	}

	/**
	 * Update forms data after inactive widgets have been cleared
	 *
	 * @since 2.2.0
	 *
	 * @param mixed[] $old_value The old option value.
	 * @param mixed[] $value     The new option value.
	 *
	 * @return void
	 */
	public function inactive_widgets_clearing( $old_value, $value ) {

		if ( is_array( $value['wp_inactive_widgets'] ) ) {

			// Delete the form and related options when clearing inactive widgets.
			if ( empty( $value['wp_inactive_widgets'] ) ) {

				if ( ! empty( $old_value['wp_inactive_widgets'] ) ) {

					global $wpdb;
					$query    = "SELECT id FROM {$wpdb->prefix}sform_shortcodes WHERE widget != '0' AND status = 'inactive'";
					$form_ids = $wpdb->get_col( $query ); // phpcs:ignore

					if ( $form_ids ) {

						foreach ( $form_ids as $form_id ) {

							$wpdb->delete( $wpdb->prefix . 'sform_shortcodes', array( 'id' => $form_id ) ); // phpcs:ignore
							$wpdb->delete( $wpdb->prefix . 'sform_submissions', array( 'form' => $form_id ) ); // phpcs:ignore
							delete_option( 'sform_' . $form_id . '_attributes' );
							delete_option( 'sform_' . $form_id . '_settings' );
							$pattern = 'sform_%_' . $form_id . '_%';
							$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $pattern ) ); // phpcs:ignore
							// Clear database query cache if options deleted.
							$wpdb->flush();

						}
					}
				}
			}
		}
	}

	/**
	 * Delete the form and related options after a widget has been marked for deletion.
	 *
	 * @since 1.10.0
	 *
	 * @param string $widget_id  The ID of the widget marked for deletion.
	 * @param string $sidebar_id The ID of the sidebar the widget was deleted from.
	 * @param string $id_base    The base ID for the widget.
	 *
	 * @return void
	 */
	public function cleanup_form_widget( $widget_id, $sidebar_id, $id_base ) {

		if ( $sidebar_id && 'simpleform' === $id_base ) {

			$widget_number = explode( 'simpleform-', $widget_id )[1];
			$form_id       = wp_cache_get( 'form_widget_' . $widget_number );
			global $wpdb;

			// Do a database query and save it to the cache if the there is no cache data with this key.
			if ( false === $form_id ) {
				$form_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}sform_shortcodes WHERE widget = %d", $widget_number ) ); // phpcs:ignore.
			}

			$wpdb->delete( $wpdb->prefix . 'sform_shortcodes', array( 'id' => $form_id ) ); // phpcs:ignore.
			wp_cache_delete( 'form_data_' . $form_id );
			$wpdb->delete( $wpdb->prefix . 'sform_submissions', array( 'form' => $form_id ) ); // phpcs:ignore.
			wp_cache_delete( 'sform_submissions_' . $form_id );
			delete_option( 'sform_' . $form_id . '_attributes' );
			delete_option( 'sform_' . $form_id . '_settings' );
			$pattern = 'sform_%_' . $form_id . '_%';
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $pattern ) ); // phpcs:ignore.
			// Clear cache if options deleted.
			$wpdb->flush();
			$sform_widget = (array) get_option( 'widget_simpleform' );
			unset( $sform_widget[ $widget_number ] );
			update_option( 'widget_simpleform', $sform_widget );

		}
	}

	/**
	 * Hide the widget from the legacy widget block dropdown and from the block inserter when the widgets block editor is enabled
	 *
	 * @since 2.0.3
	 *
	 * @param string[] $widget_types The list of excluded widget-type IDs.
	 *
	 * @return string[] The list of excluded widget-type IDs.
	 */
	public function hide_simpleform( $widget_types ) {

		$widget_types[] = 'simpleform';
		$widget_types[] = 'simpleform-';

		return $widget_types;
	}

	/**
	 * Edit form usage data when updating block widgets and the widget block editor is enabled
	 *
	 * @since 2.1.7
	 *
	 * @return void
	 */
	public function block_widgets_updating() {

		$widget_block = (array) get_option( 'widget_block' );

		if ( $widget_block ) {

			// Clear form widgets data while updating block widgets so that the data always remains up to date.
			global $wpdb;
			$wpdb->query( "UPDATE {$wpdb->prefix}sform_shortcodes SET form_widgets = '' WHERE widget != '0'" ); // phpcs:ignore.

			$pattern_block     = '/<!-- wp:simpleform(.*)\/-->/';
			$pattern_shortcode = '/\[simpleform(.*?)\]/';

			foreach ( $widget_block as $key => $value ) {

				$block_id = 'block-' . $key;

				if ( is_array( $value ) && isset( $value['content'] ) ) {

					if ( preg_match( $pattern_block, $value['content'] ) ) {

						$split_id = explode( 'formId":"', $value['content'] );

						if ( isset( $split_id[1] ) ) {
							$form = (int) explode( '"', $split_id[1] )[0];
							$this->form_widgets_updating( $block_id, $form );
						}
					}

					if ( preg_match( $pattern_shortcode, $value['content'] ) ) {

						$form_id = (int) filter_var( $value['content'], FILTER_SANITIZE_NUMBER_INT );
						$form    = 0 === $form_id ? 1 : $form_id;
						$this->form_widgets_updating( $block_id, $form );

					}
				}
			}
		}
	}

	/**
	 * Update the form data related to the use of the widget block
	 *
	 * @since 2.2.0
	 *
	 * @param string $block_id The ID of the block widget.
	 * @param int    $form     The ID of the form.
	 *
	 * @return void
	 */
	protected function form_widgets_updating( $block_id, $form ) {

		$util     = new SimpleForm_Util();
		$form_ids = $util->sform_ids();
		global $wpdb;

		foreach ( $form_ids as $form_id ) {

			// Get data about the block usage.
			$form_widgets = wp_cache_get( 'form_widgets_' . $form_id );

			// Do a database query and save it to the cache if the there is no cache data with this key.
			if ( false === $form_widgets ) {

				$form_widgets = $wpdb->get_var( $wpdb->prepare( "SELECT form_widgets FROM {$wpdb->prefix}sform_shortcodes WHERE id = %d", $form_id ) ); // phpcs:ignore.
				wp_cache_set( 'form_widgets_' . $form_id, $form_widgets );

			}

			$form_widgets_array = ! empty( $form_widgets ) ? explode( ',', $form_widgets ) : array();

			if ( (int) $form_id === $form ) {

				if ( ! in_array( $block_id, $form_widgets_array, true ) ) {

					$block_id_array   = array( $block_id );
					$new_form_widgets = implode( ',', array_unique( array_merge( $block_id_array, $form_widgets_array ) ) );

					$update_data = array(
						'form_widgets' => $new_form_widgets,
						'status'       => 'published',
					);

					$wpdb->update( $wpdb->prefix . 'sform_shortcodes', $update_data, array( 'id' => $form_id ) ); // phpcs:ignore.
					// Clear cache if options updated.
					wp_cache_delete( 'form_data_' . $form_id );

				}
			} elseif ( in_array( $block_id, $form_widgets_array, true ) ) {

				// Check if $block_id has been used with other forms and clean data.

					$block_id_array   = array( $block_id );
					$new_form_widgets = implode( ',', array_diff( $form_widgets_array, $block_id_array ) );

					$wpdb->update( $wpdb->prefix . 'sform_shortcodes', array( 'form_widgets' => $new_form_widgets ), array( 'id' => $form_id ) ); // phpcs:ignore.
					// Clear cache if options updated.
					wp_cache_delete( 'form_data_' . $form_id );

			}
		}
	}

	/**
	 * Sanitize widget settings values.
	 *
	 * @since 2.2.0
	 *
	 * @param mixed[] $instance     Array of settings for the current widget.
	 * @param mixed[] $new_instance Array of new settings values just sent to be saved.
	 * @param string  $option       The option ID.
	 *
	 * @return string The sanitized widget setting value.
	 */
	protected function sanitized_widget_option( $instance, $new_instance, $option ) {

		global $wp_roles;
		$roles       = $wp_roles->roles;
		$all_roles   = array( 'any' );
		$role_values = array_merge( array_keys( $roles ), $all_roles );

		switch ( $option ) {
			case 'show_for' === $option:
				$conditions = isset( $new_instance[ $option ] ) && in_array( $new_instance[ $option ], array( 'all', 'out', 'in' ), true );
				break;
			case 'user_role' === $option:
				$conditions = 'in' === $instance['show_for'] && isset( $new_instance[ $option ] ) && in_array( $new_instance[ $option ], $role_values, true );
				break;
			case 'display_in' === $option:
				$conditions = isset( $new_instance[ $option ] ) && in_array( $new_instance[ $option ], array( 'all', 'hidden', 'visible' ), true );
				break;
			default:
				$conditions = isset( $new_instance[ $option ] );
		}

		if ( $conditions ) {

			$sanitized_value = array(
				'form_name'     => sanitize_text_field( strval( $new_instance[ $option ] ) ),
				'title'         => sanitize_text_field( strval( $new_instance[ $option ] ) ),
				'show_for'      => sanitize_text_field( strval( $new_instance[ $option ] ) ),
				'user_role'     => sanitize_text_field( strval( $new_instance[ $option ] ) ),
				'display_in'    => sanitize_text_field( strval( $new_instance[ $option ] ) ),
				'hidden_pages'  => $this->widget_selected_pages( strval( $new_instance[ $option ] ) ),
				'visible_pages' => $this->widget_selected_pages( strval( $new_instance[ $option ] ) ),
				'id'            => sanitize_text_field( strval( $new_instance[ $option ] ) ),
				'class'         => sanitize_text_field( strval( $new_instance[ $option ] ) ),
			);

			$value = $sanitized_value[ $option ];

		} else {

			$default_value = array(
				'form_name'     => '',
				'title'         => '',
				'show_for'      => 'all',
				'user_role'     => 'any',
				'display_in'    => 'all',
				'hidden_pages'  => '',
				'visible_pages' => '',
				'id'            => '',
				'class'         => '',
			);

			$value = $default_value[ $option ];

		}

		return $value;
	}

	/**
	 * Clean up the selected pages
	 *
	 * @since 2.2.0
	 *
	 * @param string $pages_instance The pages just added to be saved.
	 *
	 * @return string The sanitized value.
	 */
	protected function widget_selected_pages( $pages_instance ) {

		$checked = preg_match( '/^[0-9, ]+$/', $pages_instance ) ? str_replace( ' ', '', $pages_instance ) : '';

		if ( $checked ) {

			$pages_array = explode( ',', $checked );

			foreach ( $pages_array as $key => $post ) {
				if ( empty( $post ) || get_post_status( (int) $post ) === false ) {
					unset( $pages_array[ $key ] );
				}
			}

			$pages = ! empty( $pages_array ) ? implode( ',', array_unique( $pages_array ) ) : '';

		} else {
			$pages = '';
		}

		return $pages;
	}

	/**
	 * Retrieve the widget setting value.
	 *
	 * @since 2.2.0
	 *
	 * @param mixed[] $instance Array of settings for the current widget.
	 * @param string  $key      The key used to identify the value of the setting.
	 *
	 * @return string The widget setting value.
	 */
	protected function get_widget_option( $instance, $key ) {

		if ( ! empty( $instance[ $key ] ) ) {

			$option_value = array(
				'form_name'     => $instance[ $key ],
				'title'         => $instance[ $key ],
				'show_for'      => $instance[ $key ],
				'user_role'     => $instance[ $key ],
				'form'          => $instance[ $key ],
				'display_in'    => $instance[ $key ],
				'hidden_pages'  => $instance[ $key ],
				'visible_pages' => $instance[ $key ],
				'id'            => $instance[ $key ],
				'class'         => $instance[ $key ],
				'status'        => $instance[ $key ],
				'area'          => $instance[ $key ],

			);

			$value = $option_value[ $key ];

		} else {

			$option_value = array(
				'form_name'     => '',
				'title'         => '',
				'show_for'      => 'all',
				'user_role'     => 'any',
				'form'          => '',
				'display_in'    => 'all',
				'hidden_pages'  => '',
				'visible_pages' => '',
				'id'            => '',
				'class'         => '',
				'status'        => '',
				'area'          => '',

			);

			$value = $option_value[ $key ];

		}

		return strval( $value );
	}

	/**
	 * Check if the widget can be viewed from user
	 *
	 * @since 2.2.0
	 *
	 * @param string $audience The user type setting for the current widget.
	 * @param string $role     The user role setting for the current widget.
	 *
	 * @return bool True, if the widget is hidden. False if the widget is shown instead.
	 */
	protected function display_for_users( $audience, $role ) {

		$current_user = wp_get_current_user();

		switch ( $audience ) {
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
	 * Check if the widget can be viewed in the page
	 *
	 * @since 2.2.0
	 *
	 * @param string     $visibility    The visibility setting for the current widget.
	 * @param string|int $post_id       The page ID.
	 * @param string     $hidden_pages  The list of pages where the current widget is not displayed.
	 * @param string     $visible_pages The list of pages where the current widget is displayed.
	 *
	 * @return bool True, if the widget is hidden. False if the widget is shown instead.
	 */
	protected function display_for_pages( $visibility, $post_id, $hidden_pages, $visible_pages ) {

		$hiding = false;

		if ( 'hidden' === $visibility && ! empty( $hidden_pages ) ) {

			$pages  = array_map( 'intval', explode( ',', $hidden_pages ) );
			$hiding = ! empty( $post_id ) && in_array( absint( $post_id ), $pages, true ) ? true : false;

		}

		if ( 'visible' === $visibility ) {

			$pages  = array_map( 'intval', explode( ',', $visible_pages ) );
			$hiding = empty( $visible_pages ) || ( ! empty( $post_id ) && ! in_array( absint( $post_id ), $pages, true ) ) ? true : false;

		}

		return $hiding;
	}
}
