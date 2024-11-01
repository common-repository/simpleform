<?php
/**
 * File delegated to show the form admin page.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/partials
 */

defined( 'ABSPATH' ) || exit;

$util          = new SimpleForm_Util();
$admin_notices = $util->get_sform_option( 1, 'settings', 'admin_notices', false );
$color         = strval( $util->get_sform_option( 1, 'settings', 'admin_color', 'default' ) );
$notice_class  = $admin_notices ? 'invisible' : '';
$version_alert = get_transient( 'sform_version_alert' );
$notice_class .= false !== $version_alert ? ' unseen' : '';
$wrap_class    = false !== $version_alert ? 'spaced' : '';
$notice        = '';
$form          = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 1; // phpcs:ignore 
$form_ids      = $util->sform_ids();
$form_ids      = array_map( 'intval', $form_ids );
$view          = isset( $_GET['view'] ) && ! empty( $_GET['view'] ) ? '&view=' . sanitize_text_field( wp_unslash( $_GET['view'] ) ) : '&view=all'; // phpcs:ignore
$pagenum       = isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ? '&paged=' . absint( $_GET['paged'] ) : ''; // phpcs:ignore
$order         = isset( $_GET['order'] ) && in_array( $_GET['order'], array( 'asc', 'desc' ), true ) ? '&order=' . sanitize_text_field( wp_unslash( $_GET['order'] ) ) : ''; // phpcs:ignore
$orderby       = isset( $_GET['orderby'] ) && in_array( $_GET['orderby'], array( 'subject', 'email', 'date' ), true ) ? '&orderby=' . sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore
$back_button   = '<a href="' . esc_url( get_admin_url( get_current_blog_id(), 'admin.php?page=sform-forms' ) . $view . $pagenum . $order . $orderby ) . '"><span class="dashicons dashicons-list-view icon-button admin ' . esc_attr( $color ) . '"></span><span class="wp-core-ui button admin back-list ' . esc_attr( $color ) . '">' . __( 'Back to forms', 'simpleform' ) . '</span></a>';
$allowed_tags  = $util->sform_allowed_tags();

// Page wrap: opening tag.
$form_page = '<div id="sform-wrap" class="sform ' . esc_attr( $wrap_class ) . '">';

// Admin notice.
$form_page .= '<div id="new-release" class="' . esc_attr( $notice_class ) . '">' . wp_kses_post( apply_filters( 'sform_update', $notice ) ) . '&nbsp;</div>';

// Page heading.
$form_page .= '<div class="full-width-bar ' . esc_attr( $color ) . '"><h1 class="title ' . esc_attr( $color ) . '"><span class="dashicons dashicons-tag responsive"></span>' . __( 'Form', 'simpleform' ) . $back_button . '</h1></div>';

if ( in_array( $form, $form_ids, true ) ) {

	$form_arg      = 1 !== $form ? '&form=' . $form : '';
	$form_name     = strval( $util->form_property_value( $form, 'name', __( 'Contact Us Page', 'simpleform' ) ) );
	$widget_id     = intval( $util->form_property_value( $form, 'widget', 0 ) );
	$entries       = intval( $util->form_property_value( $form, 'entries', 0 ) );
	$moved_entries = intval( $util->form_property_value( $form, 'moved_entries', 0 ) );
	$form_status   = strval( $util->form_property_value( $form, 'status', 'draft' ) );
	$form_creation = strval( $util->form_property_value( $form, 'creation', '' ) );
	$area          = strval( $util->form_property_value( $form, 'area', 'page' ) );
	$move_to       = intval( $util->form_property_value( $form, 'moveto', 0 ) );
	$form_widgets  = strval( $util->form_property_value( $form, 'form_widgets', '' ) );
	$sform_widget  = get_option( 'widget_simpleform' ) !== false ? (array) get_option( 'widget_simpleform' ) : array();
	$shortcode     = 1 === $form ? 'simpleform' : 'simpleform id="' . $form . '"';
	$icon          = plugin_dir_url( __DIR__ ) . 'img/copy_icon.png';
	$edit          = _x( 'Edit', 'present simple: Edit or view the page content', 'simpleform' );
	$view          = _x( 'view', 'present simple: Edit or view the page content', 'simpleform' );

	// Get a pages list sorted by name where the form is used.
	global $wpdb;
	$post_types      = wp_is_block_theme() ? "( 'attachment', 'revision' )" : "( 'attachment', 'revision', 'wp_template', 'wp_template_part' )";
	$allpagesid      = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}posts WHERE post_type NOT IN $post_types AND post_status != 'trash' AND post_title != '' AND post_content != '' ORDER BY post_title ASC" );  // phpcs:ignore
	$form_pages      = '';
	$inactive_widget = true;
	$show_for        = '';
	$user_role       = '';

	// Check if the form is embedded in a classic widget.
	if ( 0 !== $widget_id ) {

		$shortcode_data = '<td class="plaintext">' . __( 'Unavailable for widgets', 'simpleform' ) . '</td>';

		if ( in_array( (int) $widget_id, array_keys( $sform_widget ), true ) ) {

			$show_for  .= is_array( $sform_widget[ $widget_id ] ) && ! empty( $sform_widget[ $widget_id ]['show_for'] ) ? $sform_widget[ $widget_id ]['show_for'] : 'all';
			$user_role .= is_array( $sform_widget[ $widget_id ] ) && ! empty( $sform_widget[ $widget_id ]['user_role'] ) ? $sform_widget[ $widget_id ]['user_role'] : 'any';

			global $wp_registered_sidebars, $sidebars_widgets;

			foreach ( $sidebars_widgets as $sidebar => $widgets ) {
				if ( is_array( $widgets ) && 'wp_inactive_widgets' !== $sidebar ) {
					foreach ( $widgets as $key => $value ) {
						if ( strpos( $value, 'simpleform-' . $widget_id ) !== false ) {
							$area_name       = isset( $wp_registered_sidebars[ $sidebar ]['name'] ) ? $wp_registered_sidebars[ $sidebar ]['name'] : '';
							$widget_area     = $area_name ? $area_name . '&nbsp;' . __( 'widget area', 'simpleform' ) . '&nbsp;[&nbsp;<a href="' . self_admin_url( 'widgets.php' ) . '" target="_blank" style="text-decoration: none"><b>' . __( 'Edit widget', 'simpleform' ) . '</b></a>&nbsp;]<br>' : '';
							$inactive_widget = false;
						}
					}
				} elseif ( 'wp_inactive_widgets' === $sidebar ) {
					foreach ( $widgets as $key => $value ) {
						if ( strpos( $value, 'simpleform-' . $widget_id ) !== false ) {
							$widget_area = __( 'Inactive widgets area', 'simpleform' ) . '&nbsp;[&nbsp;<a href="' . self_admin_url( 'widgets.php' ) . '" target="_blank" style="text-decoration: none"><b>' . __( 'Edit widget', 'simpleform' ) . '</b></a>&nbsp;]<br>';
						}
					}
				} else {
					$widget_area = '';
				}
			}

			$widget_visibility = is_array( $sform_widget[ $widget_id ] ) && ! empty( $sform_widget[ $widget_id ]['display_in'] ) ? $sform_widget[ $widget_id ]['display_in'] : 'all';
			$hidden_pages      = is_array( $sform_widget[ $widget_id ] ) && ! empty( $sform_widget[ $widget_id ]['hidden_pages'] ) ? $sform_widget[ $widget_id ]['hidden_pages'] : '';
			$visible_pages     = is_array( $sform_widget[ $widget_id ] ) && ! empty( $sform_widget[ $widget_id ]['visible_pages'] ) ? $sform_widget[ $widget_id ]['visible_pages'] : '';

			if ( 'hidden' === $widget_visibility ) {

				if ( ! empty( $hidden_pages ) ) {
					$form_pages_array    = explode( ', ', $hidden_pages );
					$ordered_pages_array = array_intersect( $allpagesid, $form_pages_array );
					$hidden_list         = '';
					foreach ( $ordered_pages_array as $array_item ) {
						if ( 'draft' === get_post_status( $array_item ) || 'publish' === get_post_status( $array_item ) ) {
							$publish_link = '<strong><a href="' . get_edit_post_link( $array_item ) . '" target="_blank" class="publish-link">' . __( 'Publish now', 'simpleform' ) . '</a></strong>';
							/* translators: %1$s: link to edit the page, %2$s: link to view the page */
							$post_status  = 'draft' === get_post_status( $array_item ) ? __( 'Page in draft status not yet published', 'simpleform' ) . '&nbsp;-&nbsp;' . $publish_link : sprintf( __( '%1$s or %2$s', 'simpleform' ), '<strong><a href="' . get_edit_post_link( $array_item ) . '" target="_blank" style="text-decoration: none;">' . $edit . '</a></strong>', '<strong><a href="' . get_page_link( $array_item ) . '" target="_blank" style="text-decoration: none;">' . $view . '</a></strong>' );
							$hidden_list .= '<span>"' . get_the_title( $array_item ) . '"&nbsp;' . __( 'page', 'simpleform' ) . '</span><span class="">&nbsp;[&nbsp;' . $post_status . '&nbsp;]<br>';
						}
					}
					$form_pages .= '<span>' . __( 'Visible in all pages where the widget area is present except for the pages listed:', 'simpleform' ) . '</span><br>' . $hidden_list;
				} else {
					$form_pages .= __( 'Visible in all pages where the widget area is present', 'simpleform' );
				}
			} elseif ( 'visible' === $widget_visibility ) {

				if ( ! empty( $visible_pages ) ) {
					$form_pages_array    = explode( ', ', $visible_pages );
					$ordered_pages_array = array_intersect( $allpagesid, $form_pages_array );
					$visible_list        = '';
					foreach ( $ordered_pages_array as $array_item ) {
						if ( 'draft' === get_post_status( $array_item ) || 'publish' === get_post_status( $array_item ) ) {
							$publish_link = '<strong><a href="' . get_edit_post_link( $array_item ) . '" target="_blank" class="publish-link">' . __( 'Publish now', 'simpleform' ) . '</a></strong>';
							/* translators: %1$s: link to edit the page, %2$s: link to view the page */
							$post_status   = 'draft' === get_post_status( $array_item ) ? __( 'Page in draft status not yet published', 'simpleform' ) . '&nbsp;-&nbsp;' . $publish_link : sprintf( __( '%1$s or %2$s', 'simpleform' ), '<strong><a href="' . get_edit_post_link( $array_item ) . '" target="_blank" style="text-decoration: none;">' . $edit . '</a></strong>', '<strong><a href="' . get_page_link( $array_item ) . '" target="_blank" style="text-decoration: none;">' . $view . '</a></strong>' );
							$visible_list .= '<span>"' . get_the_title( $array_item ) . '"&nbsp;' . __( 'page', 'simpleform' ) . '</span><span class="">&nbsp;[&nbsp;' . $post_status . '&nbsp;]<br>';
						}
					}
					$form_pages .= __( 'Visible only in the listed pages where the widget area is present:', 'simpleform' ) . '<br>' . $visible_list;
				} else {
					$form_pages .= __( 'No page selected yet where the widget area is present', 'simpleform' );
				}
			} else {
				$form_pages .= __( 'Visible in all pages where the widget area is present', 'simpleform' );
			}
		} else {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}sform_shortcodes WHERE id = %d", $form ) ); // phpcs:ignore.
			delete_option( 'sform_' . $form . '_attributes' );
			delete_option( 'sform_' . $form . '_settings' );
			$pattern = 'sform_%_' . $form . '_%';
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $pattern ) ); // phpcs:ignore.
		}

		$deletion_note = __( 'In order to delete a widget form, please go to the widgets page', 'simpleform' );

	} else {

		// Check if the form is embedded in a page or block.
		$shortcode_data = '<td class="plaintext icon"><span id="shortcode">[' . $shortcode . ']</span><button id="shortcode-copy"><img src="' . $icon . '"></button><span id="shortcode-tooltip" class="unseen">' . __( 'Copy shortcode', 'simpleform' ) . '</span></td>';
		$show_for      .= $util->get_sform_option( $form, 'attributes', 'show_for', 'all' );
		$user_role     .= $util->get_sform_option( $form, 'attributes', 'user_role', 'any' );
		$widget_area    = '';

		$forms_list   = strval( $util->form_property_value( $form, 'form_pages', '' ) );
		$forms_array  = explode( ',', $forms_list );
		$ordered_list = array_intersect( $allpagesid, $forms_array );
		if ( ! empty( $ordered_list ) ) {
			foreach ( $ordered_list as $array_item ) {
				if ( 'draft' === get_post_status( $array_item ) || 'publish' === get_post_status( $array_item ) ) {
					$publish_link = '<strong><a href="' . get_edit_post_link( $array_item ) . '" target="_blank" class="publish-link">' . __( 'Publish now', 'simpleform' ) . '</a></strong>';
					/* translators: %1$s: link to edit the page, %2$s: link to view the page */
					$post_status = 'draft' === get_post_status( $array_item ) ? __( 'Page in draft status not yet published', 'simpleform' ) . '&nbsp;-&nbsp;' . $publish_link : sprintf( __( '%1$s or %2$s', 'simpleform' ), '<strong><a href="' . get_edit_post_link( $array_item ) . '" target="_blank" style="text-decoration: none;">' . $edit . '</a></strong>', '<strong><a href="' . get_page_link( $array_item ) . '" target="_blank" style="text-decoration: none;">' . $view . '</a></strong>' );
					switch ( get_post_type( $array_item ) ) {
						case 'wp_template' === get_post_type( $array_item ):
							/* translators: %s: Template title */
							$page_type = sprintf( __( '"%s" template', 'simpleform' ), get_the_title( $array_item ) );
							$page_link = '<strong><a href="' . admin_url( 'site-editor.php?postType=wp_template' ) . '" target="_blank" style="text-decoration: none;">' . $edit . '</a></strong>';
							break;
						case 'wp_template_part' === get_post_type( $array_item ):
							/* translators: %s: Template area title */
							$page_type = sprintf( __( '"%s" area', 'simpleform' ), get_the_title( $array_item ) );
							$page_link = '<strong><a href="' . admin_url( 'site-editor.php?postType=wp_template_part' ) . '" target="_blank" style="text-decoration: none;">' . $edit . '</a></strong>';
							break;
						default:
							/* translators: %s: Page title */
							$page_type = sprintf( __( '"%s" page', 'simpleform' ), get_the_title( $array_item ) );
							$page_link = $post_status;
					}

					$form_pages .= '<span>' . $page_type . '</span><span class="">&nbsp;[&nbsp;' . $page_link . '&nbsp;]<br>';

				}
			}
		}

		$widget_block = get_option( 'widget_block' ) !== false ? get_option( 'widget_block' ) : array();

		if ( ! empty( $widget_block ) ) {
			$block_id_array = ! empty( $form_widgets ) ? explode( ',', $form_widgets ) : array();

			if ( $block_id_array ) {

				foreach ( $block_id_array as $item ) {
					$split_key = ! empty( $item ) ? explode( 'block-', $item ) : '';
					$block_key = isset( $split_key[1] ) ? $split_key[1] : '0';
					global $wp_registered_sidebars, $sidebars_widgets;

					foreach ( $sidebars_widgets as $sidebar => $widgets ) {

						if ( is_array( $widgets ) ) {

							if ( 'wp_inactive_widgets' !== $sidebar ) {
								foreach ( $widgets as $key => $value ) {
									if ( 'block-' . $block_key === $value ) {
										$block_widget_area = isset( $wp_registered_sidebars[ $sidebar ]['name'] ) ? $wp_registered_sidebars[ $sidebar ]['name'] : '';
										$form_pages       .= $block_widget_area ? $block_widget_area . '&nbsp;' . __( 'widget area', 'simpleform' ) . '&nbsp;[&nbsp;<a href="' . self_admin_url( 'widgets.php' ) . '" target="_blank" style="text-decoration: none"><b>' . __( 'Edit widget', 'simpleform' ) . '</b></a>&nbsp;]<br>' : '';
										$inactive_widget   = false;
									}
								}
							} elseif ( in_array( 'block-' . $block_key, $widgets, true ) && 'wp_inactive_widgets' === $sidebar ) {

								$inactive_area = __( 'Inactive widgets area', 'simpleform' );
								$form_pages   .= strpos( $form_pages, $inactive_area ) === false ? $inactive_area . '&nbsp;[&nbsp;<a href="' . self_admin_url( 'widgets.php' ) . '" target="_blank" style="text-decoration: none"><b>' . __( 'Edit widget', 'simpleform' ) . '</b></a>&nbsp;]<br>' : '';
							} else {
								$form_pages     .= '';
								$inactive_widget = false;
							}
						}
					}
				}
			}
		}

		$deletion_note = __( 'The default form cannot be deleted', 'simpleform' );

	}

	if ( empty( $form_pages ) ) {
		if ( 1 === $form ) {
			$prebuilt_page            = '<b>' . __( 'pre-built page', 'simpleform' ) . '</b>';
			$form_page_id             = intval( $util->get_sform_option( 1, 'settings', 'form_pageid', 0 ) );
			$prebuilt_page_link       = 0 !== $form_page_id ? '<a href="' . get_edit_post_link( $form_page_id ) . '" target="_blank" style="text-decoration: none;">' . $prebuilt_page . '</a>' : '';
			$default_message_starting = __( 'The form has not yet been published', 'simpleform' );
			$default_message_ending   = ! empty( $prebuilt_page_link ) && 1 === $form ? '.&nbsp;' . sprintf( 'Get started with the %s!', $prebuilt_page_link ) : '';
			$form_pages               = '<span>' . $default_message_starting . $default_message_ending . '</span>';
		} else {
			$form_pages = __( 'No page selected yet where the form is present', 'simpleform' );
		}
	}

	if ( 'out' === $show_for ) {
		$target = __( 'Logged-out users', 'simpleform' );
	} elseif ( 'in' === $show_for ) {
		$target = __( 'Logged-in users', 'simpleform' );
	} else {
		$target = __( 'Everyone', 'simpleform' );
	}

	global $wp_roles;
	$role_name = 'any' === $user_role ? __( 'Any', 'simpleform' ) : translate_user_role( $wp_roles->roles[ $user_role ]['name'] );

	if ( 'published' === $form_status ) {
		$form_type = _x( 'Published', 'Singular noun', 'simpleform' );
	} elseif ( 'draft' === $form_status ) {
		$form_type = __( 'Draft', 'simpleform' );
	} else {
		$form_type = _x( 'Trashed', 'Singular noun', 'simpleform' );
	}

	// Display the date in localized format according to the date format and timezone of the site.
	$creation_date       = date_i18n( strval( get_option( 'date_format' ) ), strtotime( get_date_from_gmt( $form_creation ) ) );
	$moved_entries_class = 0 === $entries && 0 === $moved_entries ? 'unseen' : '';
	$restricted_to_class = 'in' !== $show_for ? 'unseen' : '';
	$visible_to_class    = 'trash' === $form_status && 'in' !== $show_for ? 'last' : '';

	$disabled_class  = 'trash' === $form_status ? 'disabled' : '';
	$disabled_option = 'trash' === $form_status ? ' disabled="disabled"' : '';
	$relocation      = (bool) $util->form_property_value( $form, 'relocation', false );
	$to_be_moved     = strval( $util->form_property_value( $form, 'to_be_moved', '' ) );
	$onetime_moving  = (bool) $util->form_property_value( $form, 'onetime_moving', true );
	$notifications   = (bool) $util->form_property_value( $form, 'override_settings', false );
	$deletion        = (bool) $util->form_property_value( $form, 'deletion', false );
	$storing         = (bool) $util->form_property_value( $form, 'storing', true );
	$moveto_class    = ! $relocation ? ' unseen' : '';
	$moveto          = $move_to;
	$moving_options  = '';
	$forms           = $wpdb->get_results( $wpdb->prepare( "SELECT id, name FROM {$wpdb->prefix}sform_shortcodes WHERE id != %d AND status != 'trash' ORDER BY name ASC", $form ), 'ARRAY_A' ); // phpcs:ignore.
	foreach ( $forms as $form_value ) {
		$selected_option = '' !== $to_be_moved && ! $onetime_moving && intval( $form_value['id'] ) === $moveto ? 'selected="selected"' : '';
		$moving_options .= '\n\t<option value="' . esc_attr( $form_value['id'] ) . '" ' . $selected_option . '>' . $form_value['name'] . '</option>';
	}
	$to_be_moved_class = ! $relocation || ( 0 === $entries && 'next' !== $to_be_moved ) || 0 === $moveto || $onetime_moving ? ' unseen' : '';
	// Include the storing value if the addon is enabled.
	$where              = defined( 'SIMPLEFORM_SUBMISSIONS_NAME' ) && $storing ? "AND object != '' AND object != 'not stored' AND listable = '1'" : '';
	$where_day          = ' AND date >= UTC_TIMESTAMP() - INTERVAL 24 HOUR';
	$where_week         = ' AND date >= UTC_TIMESTAMP() - INTERVAL 7 DAY';
	$where_month        = ' AND date >= UTC_TIMESTAMP() - INTERVAL 30 DAY';
	$where_year         = ' AND date >= UTC_TIMESTAMP() - INTERVAL 1 YEAR';
	$count_all          = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions WHERE form = {$form} {$where}" ); // phpcs:ignore
	$count_last_day     = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions WHERE form = {$form} {$where} {$where_day}" ); // phpcs:ignore
	$count_last_week    = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions WHERE form = {$form} {$where} {$where_week}" ); // phpcs:ignore
	$count_last_month   = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions WHERE form = {$form} {$where} {$where_month}" ); // phpcs:ignore
	$count_last_year    = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions WHERE form = {$form} {$where} {$where_year}" ); // phpcs:ignore
	$moved_options      = '<select name="be_moved" id="be_moved" class="sform ' . esc_attr( $color ) . '"><option value="">' . __( 'Select entries', 'simpleform' ) . '</option>';
	$moved_options     .= $count_all !== $count_last_year ? '<option value="all">' . __( 'All', 'simpleform' ) . '</option>' : '';
	$moved_options     .= $count_last_year > 0 && $count_last_year !== $count_last_month ? '<option value="lastyear">' . __( 'Last year', 'simpleform' ) . '</option>' : '';
	$moved_options     .= $count_last_month > 0 && $count_last_month !== $count_last_week ? '<option value="lastmonth">' . __( 'Last month', 'simpleform' ) . '</option>' : '';
	$moved_options     .= $count_last_week > 0 && $count_last_week !== $count_last_day ? '<option value="lastweek">' . __( 'Last week', 'simpleform' ) . '</option>' : '';
	$moved_options     .= $count_last_day > 0 ? '<option value="lastday">' . __( 'Last day', 'simpleform' ) . '</option>' : '';
	$moved_options     .= '<option value="next" ' . selected( $to_be_moved, 'next', false ) . '>' . __( 'Not received yet', 'simpleform' ) . '</option></select>';
	$settings_class     = ! $relocation || 0 === $moveto || '' === $to_be_moved || ( 'next' !== $to_be_moved && $onetime_moving ) ? ' unseen' : '';
	$settings_note      = $notifications ? 'invisible' : '';
	$restore_class      = 0 === $moved_entries ? 'unseen' : '';
	$deletion_class     = 1 === $form || 0 !== $widget_id ? 'last notes default' : '';
	$delete_message     = __( 'Deleting a form is permanent. Once a form is deleted, it can\'t be restored. All submissions to that form are permanently deleted too.', 'simpleform' );
	$disabled_class     = 'trash' === $form_status ? 'disabled' : '';
	$disabled_deletion  = 1 === $form || 'trash' === $form_status || 0 !== $widget_id ? ' disabled="disabled"' : '';
	$deletion_label     = 1 === $form || 'trash' === $form_status || 0 !== $widget_id ? 'disabled' : '';
	$form_deletion_note = 1 === $form || 0 !== $widget_id ? '<p class="description invisible">' . $deletion_note . '</p>' : '';
	$button_class       = ! $deletion ? 'unseen ' : '';
	$delete_button      = 'trash' !== $form_status ? '<span id="deletion-toggle" style="margin-left: 30px; float: none !important; padding: 10px 12px !important; border-radius: 8px !important; line-height: 2.15384615;" class="submit-button ' . $button_class . esc_attr( $color ) . '">' . __( 'Delete Form', 'simpleform' ) . '</span>' : '';
	$to_name            = 0 !== $moveto && '' !== $to_be_moved && ! $onetime_moving ? $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}sform_shortcodes WHERE id = %d", $move_to ) ) : ''; // phpcs:ignore

	// Description page.
	$form_page .= '<div id="page-description"><p>' . __( 'Full details on the form, including locks. You can move entries to another form, restore entries or delete the form:', 'simpleform' ) . '</p></div>';

	// Tabs.
	$form_page .= '<div id="editor-tabs"><a class="form-button last ' . esc_attr( $color ) . '" href="' . admin_url( 'admin.php?page=sform-settings' ) . $form_arg . '" target="_blank"><span><span class="dashicons dashicons-admin-settings"></span><span class="text">' . __( 'Settings', 'simpleform' ) . '</span></span></a><a class="form-button form-page ' . esc_attr( $color ) . '" href="' . admin_url( 'admin.php?page=sform-editor' ) . $form_arg . '" target="_blank"><span><span class="dashicons dashicons-editor-table"></span><span class="text">' . __( 'Editor', 'simpleform' ) . '</span></span></a></div>';

	// Form opening tag.
	$form_page .= '<form id="card" method="post" class="' . esc_attr( $color ) . '">';

	// Specifics data.
	$form_page .= count( $form_ids ) > 1 ? '<h2 id="h2-specifics" class="options-heading"><span class="heading" data-section="specifics">' . __( 'Specifics', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 specifics"></span></span></h2><div class="section specifics"><table class="form-table specifics"><tbody>' : '<h2 class="options-heading">' . __( 'Specifics', 'simpleform' ) . '</h2><div class="section specifics"><table class="form-table specifics"><tbody>';

	$form_page .= '<tr><th class="option"><span>' . __( 'Form Name', 'simpleform' ) . '</span></th><td class="plaintext">' . esc_html( $form_name ) . '</td></tr>';

	$form_page .= '<tr><th class="option"><span>' . __( 'Form ID', 'simpleform' ) . '</span></th><td class="plaintext">' . $form . '</td></tr>';

	$form_page .= '<tr><th class="option"><span>' . __( 'Shortcode', 'simpleform' ) . '</span></th>' . $shortcode_data . '</tr>';

	$form_page .= '<tr><th class="option"><span>' . __( 'Status', 'simpleform' ) . '</span></th><td class="plaintext">' . esc_html( $form_type ) . '</td></tr>';

	$form_page .= '<tr><th class="option"><span>' . __( 'Creation Date', 'simpleform' ) . '</span></th><td class="plaintext">' . esc_html( $creation_date ) . '</td></tr>';

	$form_page .= '<tr><th class="option"><span>' . __( 'Entries', 'simpleform' ) . '</span></th><td id="tdentries" class="plaintext"><span id="entries">' . $entries . '</span></td></tr>';

	$form_page .= '<tr class="trmoved ' . $moved_entries_class . '"><th class="option"><span>' . __( 'Moved Entries', 'simpleform' ) . '</span></th><td class="plaintext"><span id="moved-entries">' . $moved_entries . '</span></td></tr>';

	$form_page .= '<tr><th class="option"><span>' . __( 'Visible to', 'simpleform' ) . '</span></th><td class="plaintext ' . $visible_to_class . '">' . esc_html( $target ) . '</td></tr>';

	$form_page .= '<tr class="trlevel ' . $restricted_to_class . '"><th class="option"><span>' . __( 'Restricted to', 'simpleform' ) . '</span></th><td class="plaintext">' . esc_html( $role_name ) . '</td></tr>';

	$form_page .= ! empty( $widget_area ) && 'trash' !== $form_status ? '<tr><th class="option"><span>' . __( 'Used in', 'simpleform' ) . '</span></th><td class="plaintext widget">' . $widget_area . '</td></tr>' : '';

	$form_page .= ! empty( $widget_area ) && ! $inactive_widget && 'trash' !== $form_status ? '<tr><th class="option"><span>' . __( 'Widget Visibility Rules', 'simpleform' ) . '</span></th><td class="used-page last">' . $form_pages . '</td></tr>' : '';

	$form_page .= empty( $widget_area ) && 'trash' !== $form_status ? '<tr><th class="option"><span>' . __( 'Used in', 'simpleform' ) . '</span></th><td class="used-page last">' . $form_pages . '</td></tr>' : '';

	$form_page .= '</tbody></table></div>';

	// Locks options.
	$form_page .= count( $form_ids ) > 1 ? '<h2 id="h2-admin" class="options-heading"><span class="heading" data-section="admin">' . __( 'Locks', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 admin"></span></span></h2><div class="section admin"><table class="form-table admin"><tbody>' : '';

	$form_page .= count( $form_ids ) > 1 ? '<tr><th class="option"><span>' . __( 'Moving', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="relocation" id="relocation" class="sform-switch" value="' . $relocation . '" ' . checked( $relocation, true, false ) . $disabled_option . '><span></span></label><label for="relocation" class="switch-label ' . $disabled_class . '">' . __( 'Allow the entries to be moved from one form to another', 'simpleform' ) . '</label></div></td></tr>' : '';

	$form_page .= count( $form_ids ) > 1 ? '<tr class="trmoving ' . $moveto_class . '"><th class="option"><span>' . __( 'Move To', 'simpleform' ) . '</span></th><td class="select"><select name="moveto" id="moveto" class="sform ' . esc_attr( $color ) . '"><option value="">' . __( 'Select a form to move entries to', 'simpleform' ) . '</option>' . $moving_options . '</select><span class="message unseen"></span></td></tr>' : '';

	$form_page .= count( $form_ids ) > 1 ? '<tr class="trmoveto ' . $to_be_moved_class . '"><th class="option"><span>' . __( 'Entries to be moved', 'simpleform' ) . '</span></th><td class="select">' . $moved_options . '<span class="message unseen"></span></td></tr>' : '';

	$form_page .= count( $form_ids ) > 1 ? '<tr class="tronetime unseen"><th class="option"><span>' . __( 'One-time', 'simpleform' ) . '</span></th><td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="onetime" id="onetime" class="sform-switch" value="' . $onetime_moving . '" ' . checked( $onetime_moving, true, false ) . '><span></span></label><label for="onetime" class="switch-label">' . __( 'Disable moving after entries have been moved', 'simpleform' ) . '</label></div><p class="description onetime invisible">' . __( 'The moving is kept active for next entries that will be received', 'simpleform' ) . '</p></td></tr>' : '';

	$form_page .= count( $form_ids ) > 1 ? '<tr class="trsettings ' . $settings_class . '"><th class="option"><span>' . __( 'Notifications', 'simpleform' ) . '</span></th><td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="settings" id="settings" class="sform-switch" value="' . $notifications . '" ' . checked( $notifications, true, false ) . '><span></span></label><label for="settings" class="switch-label">' . __( 'Use the notifications settings of form to which entries are moved', 'simpleform' ) . '</label></div><p class="description settings ' . $settings_note . '">' . __( 'By default, the moved entries comply with the notifications settings of form from which are moved', 'simpleform' ) . '</p></td></tr>' : '';

	$form_page .= count( $form_ids ) > 1 ? '<tr class="trrestore ' . $restore_class . '"><th class="option"><span>' . __( 'Restore entries', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="restore" id="restore" class="sform-switch" value="false"><span></span></label><label for="restore" class="switch-label">' . __( 'Restore the moved entries', 'simpleform' ) . '</label></div></td></tr>' : '';

	$form_page .= count( $form_ids ) > 1 ? '<tr><th class="option"><span>' . __( 'Deletion', 'simpleform' ) . '</span></th><td class="checkbox-switch ' . $deletion_class . '"><div class="switch-box"><label class="switch-input ' . $deletion_label . '"><input type="checkbox" name="deletion_form" id="deletion_form" class="sform-switch" value="' . $deletion . '" ' . checked( $deletion, true, false ) . $disabled_deletion . '><span></span></label><label for="deletion_form" class="switch-label ' . $deletion_label . '">' . __( 'Allow the form to be deleted', 'simpleform' ) . '</label></div>' . $form_deletion_note . '</td></tr></tbody></table></div>' : '';

	// Editing buttons opening.
	$form_page .= count( $form_ids ) > 1 ? '<div id="card-submit-wrap"><div id="alert-wrap"><noscript><div id="noscript">' . __( 'You need JavaScript enabled to edit form. Please activate it. Thanks!', 'simpleform' ) . '</div></noscript><div id="message-wrap" class="message"></div></div><div id="form-buttons"><input type="submit" name="save-card" id="save-card" class="submit-button" value="' . esc_attr__( 'Save Changes', 'simpleform' ) . '">' . $delete_button . '</div></div>' : '';

	// Hidden form data.
	$form_page .= count( $form_ids ) > 1 ? '<input type="hidden" id="form" name="form" value="' . $form . '">' : '';
	$form_page .= count( $form_ids ) > 1 ? '<input type="hidden" id="form_to" name="form_to" value="' . $to_name . '">' : '';
	$form_page .= count( $form_ids ) > 1 ? '<input type="hidden" id="cache" name="cache" value="">' : '';
	$form_page .= count( $form_ids ) > 1 ? wp_nonce_field( 'simpleform_backend_update', 'simpleform_nonce', false, false ) : '';

	$form_page .= count( $form_ids ) > 1 && 'trash' !== $form_status ? '<div id="deletion-notice" class="unseen"><input type="hidden" id="form_id" name="form_id" value="' . $form . '" ><input type="hidden" id="form_name" name="form_name" value="' . esc_attr( $form_name ) . '"><div id="hidden-confirm"></div><h3 class="deletion"><span class="dashicons dashicons-trash"></span>' . __( 'Delete Form', 'simpleform' ) . ':&nbsp;' . $form_name . '</h3><div class="disclaimer"><span id="default">' . __( 'Deleting a form involves its permanent removal from pages and widgets, and its moving to trash.', 'simpleform' ) . '&nbsp;' . __( 'That gives you a chance to restore the form in case you change your mind, but you\'ll need to re-insert it into a page or widget to make it visible again.', 'simpleform' ) . '</span><span id="confirm"></span></div><div id="deletion-buttons"><div class="delete cancel">' . __( 'Cancel', 'simpleform' ) . '</div><input type="submit" class="delete" id="deletion-confirm" name="deletion-confirm" value="' . esc_attr__( 'Continue with deletion', 'simpleform' ) . '"></div></div>' : '';

	// Editing buttons closing.
	$form_page .= count( $form_ids ) > 1 ? '</div>' : '';

	// Form closing tag.
	$form_page .= '</form>';

} else {

	$form_page .= '<div id="page-description"><p>' . __( 'It seems the form is no longer available!', 'simpleform' ) . '</p></div><div id="page-buttons"><span class="wp-core-ui button unavailable ' . esc_attr( $color ) . '"><a href="' . esc_url( menu_page_url( 'sform-forms', false ) ) . '">' . __( 'Reload the forms page', 'simpleform' ) . '</a></span></div>';

}

// Page wrap: closing tag.
$form_page .= '</div>';

echo wp_kses( $form_page, $allowed_tags );
