<?php
/**
 * File delegated to show the entries admin page.
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
$form          = isset( $_GET['form'] ) ? absint( $_GET['form'] ) : ''; // phpcs:ignore 
$form_ids      = $util->sform_ids();
$form_ids      = array_map( 'intval', $form_ids );
global $wpdb;
$page_forms   = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}sform_shortcodes WHERE widget = '0' AND status != 'trash' ORDER BY name ASC", 'ARRAY_A' ); // phpcs:ignore.
$widget_forms = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}sform_shortcodes WHERE widget != '0' AND status != 'trash' AND status != 'inactive' ORDER BY name ASC", 'ARRAY_A' ); // phpcs:ignore.
$page_ids     = array_map( 'intval', array_column( $page_forms, 'id' ) );
$widget_ids   = array_map( 'intval', array_column( $widget_forms, 'id' ) );
$forms        = array_merge( $page_ids, $widget_ids );
$all_forms    = count( $page_forms ) + count( $widget_forms );
$note_button  = $all_forms > 1 ? wp_kses_post( apply_filters( 'hidden_submissions', $notice, $form ) ) : '';
$page_options = $widget_forms ? '<optgroup label="' . esc_attr__( 'Embedded in page', 'simpleform' ) . '">' : '';
foreach ( $page_forms as $page_form ) {
	$selected_page = (int) $page_form['id'] === $form ? 'selected="selected"' : '';
	$page_options .= '<option value="' . $page_form['id'] . '" ' . $selected_page . '>' . $page_form['name'] . '</option>';
}
$page_options  .= $widget_forms ? '</optgroup>' : '';
$widget_options = $widget_forms ? '<optgroup label="' . esc_attr__( 'Embedded in widget area', 'simpleform' ) . '">' : '';
foreach ( $widget_forms as $widget_form ) {
	$selected_widget = (int) $widget_form['id'] === $form ? 'selected="selected"' : '';
	$widget_options .= '<option value="' . $widget_form['id'] . '" ' . $selected_widget . '>' . $widget_form['name'] . '</option>';
}
$widget_options .= $widget_forms ? '</optgroup>' : '';
$forms_selector  = $all_forms > 1 ? '<div class="selector"><div id="wrap-selector" class="responsive">' . __( 'Select Form', 'simpleform' ) . ':</div><div class="form-selector"><select name="form" id="form" class="' . esc_attr( $color ) . '"><option value="" ' . selected( $form, '', false ) . '>' . __( 'All Forms', 'simpleform' ) . '</option>' . $page_options . $widget_options . '</select></div></div>' : '';

$allowed_tags = $util->sform_allowed_tags();

// Page wrap: opening tag.
$entries_page = '<div id="sform-wrap" class="sform ' . esc_attr( $wrap_class ) . '">';

// Admin notice.
$entries_page .= '<div id="new-release" class="' . esc_attr( $notice_class ) . '">' . wp_kses_post( apply_filters( 'sform_update', $notice ) ) . '&nbsp;</div>';

// Page heading.
$entries_page .= '<div class="full-width-bar entries ' . esc_attr( $color ) . '"><h1 class="title ' . esc_attr( $color ) . '"><span class="dashicons dashicons-email-alt responsive"></span>' . __( 'Entries', 'simpleform' ) . $note_button . $forms_selector . '</h1></div>';

if ( empty( $form ) ) {

	$where_form          = "WHERE form != '0'";
	$before_last_message = get_option( 'sform_before_last_message' ) !== false ? get_option( 'sform_before_last_message' ) : '';
	$message_type        = isset( $_GET['message'] ) && 'before' === $_GET['message'] ? 'before' : ''; // phpcs:ignore 
	$last_message        = $before_last_message && $message_type ? $before_last_message : get_option( 'sform_last_message' );
	$before_button       = $before_last_message ? true : false;

} else {

	$where_form                                = "WHERE form = '" . $form . "'";
	$last_date                                 = $wpdb->get_var( $wpdb->prepare( "SELECT date FROM {$wpdb->prefix}sform_submissions WHERE form = %d ORDER BY date DESC LIMIT 1", $form ) ); // phpcs:ignore
	$before_last_date                          = $last_date ? $wpdb->get_var( $wpdb->prepare( "SELECT date FROM {$wpdb->prefix}sform_submissions WHERE form = %d ORDER BY date DESC LIMIT 1 OFFSET 1", $form ) ) : ''; // phpcs:ignore
	$form_last_message                         = get_option( 'sform_last_' . $form . '_message' ) !== false ? explode( '#', strval( get_option( 'sform_last_' . $form . '_message' ) ) ) : '';
	$before_last_message                       = get_option( 'sform_before_last_' . $form . '_message' ) !== false ? explode( '#', strval( get_option( 'sform_before_last_' . $form . '_message' ) ) ) : '';
	$forwarded_last_message                    = get_option( 'sform_forwarded_last_' . $form . '_message' ) !== false ? explode( '#', strval( get_option( 'sform_forwarded_last_' . $form . '_message' ) ) ) : '';
	$forwarded_before_last_message             = get_option( 'sform_forwarded_before_last_' . $form . '_message' ) !== false ? explode( '#', strval( get_option( 'sform_forwarded_before_last_' . $form . '_message' ) ) ) : '';
	$direct_last_message                       = get_option( 'sform_direct_last_' . $form . '_message' ) !== false ? explode( '#', strval( get_option( 'sform_direct_last_' . $form . '_message' ) ) ) : '';
	$direct_before_last_message                = get_option( 'sform_direct_before_last_' . $form . '_message' ) !== false ? explode( '#', strval( get_option( 'sform_direct_before_last_' . $form . '_message' ) ) ) : '';
	$moved_last_message                        = get_option( 'sform_moved_last_' . $form . '_message' ) !== false ? explode( '#', strval( get_option( 'sform_moved_last_' . $form . '_message' ) ) ) : '';
	$moved_before_last_message                 = get_option( 'sform_moved_before_last_' . $form . '_message' ) !== false ? explode( '#', strval( get_option( 'sform_moved_before_last_' . $form . '_message' ) ) ) : '';
	$last_timestamp                            = $form_last_message && is_numeric( $form_last_message[0] ) ? $form_last_message[0] : '';
	$before_last_timestamp                     = $before_last_message && is_numeric( $before_last_message[0] ) ? $before_last_message[0] : '';
	$forwarded_last_timestamp                  = $forwarded_last_message && is_numeric( $forwarded_last_message[0] ) ? $forwarded_last_message[0] : '';
	$forwarded_before_last_timestamp           = $forwarded_before_last_message && is_numeric( $forwarded_before_last_message[0] ) ? $forwarded_before_last_message[0] : '';
	$direct_last_timestamp                     = $direct_last_message && is_numeric( $direct_last_message[0] ) ? $direct_last_message[0] : '';
	$direct_before_last_timestamp              = $direct_before_last_message && is_numeric( $direct_before_last_message[0] ) ? $direct_before_last_message[0] : '';
	$moved_last_timestamp                      = $moved_last_message && is_numeric( $moved_last_message[0] ) ? $moved_last_message[0] : '';
	$moved_before_last_timestamp               = $moved_before_last_message && is_numeric( $moved_before_last_message[0] ) ? $moved_before_last_message[0] : '';
	$dates                                     = array();
	$dates[ $last_timestamp ]                  = $last_timestamp && isset( $form_last_message[1] ) ? $form_last_message[1] : '';
	$dates[ $before_last_timestamp ]           = $before_last_timestamp && isset( $before_last_message[1] ) ? $before_last_message[1] : '';
	$dates[ $forwarded_last_timestamp ]        = $forwarded_last_timestamp && isset( $forwarded_last_message[1] ) ? $forwarded_last_message[1] : '';
	$dates[ $forwarded_before_last_timestamp ] = $forwarded_before_last_timestamp && isset( $forwarded_before_last_message[1] ) ? $forwarded_before_last_message[1] : '';
	$dates[ $direct_last_timestamp ]           = $direct_last_timestamp && isset( $direct_last_message[1] ) ? $direct_last_message[1] : '';
	$dates[ $direct_before_last_timestamp ]    = $direct_before_last_timestamp && isset( $direct_before_last_message[1] ) ? $direct_before_last_message[1] : '';
	$dates[ $moved_last_timestamp ]            = $moved_last_timestamp && isset( $moved_last_message[1] ) ? $moved_last_message[1] : '';
	$dates[ $moved_before_last_timestamp ]     = $moved_before_last_timestamp && isset( $moved_before_last_message[1] ) ? $moved_before_last_message[1] : '';

	// Remove empty array elements.
	$dates         = array_filter( $dates );
	$message_type  = isset( $_GET['message'] ) && 'before' === $_GET['message'] ? 'before' : ''; // phpcs:ignore 
	$before_button = $before_last_date && strtotime( $before_last_date ) && array_key_exists( strtotime( $before_last_date ), $dates ) ? true : false;

	if ( $last_date && array_key_exists( strtotime( $last_date ), $dates ) ) {
		$last_message = $message_type ? $dates[ strtotime( $before_last_date ) ] : $dates[ strtotime( $last_date ) ];
	} else {
		$last_message = $last_date ? '<div style="line-height:18px;">' . __( 'Data not available due to entries moved to other form', 'simpleform' ) . '</div>' : '';
	}
}

if ( empty( $form ) || in_array( $form, $forms, true ) ) {

	global $wpdb;
	$where_day         = 'AND date >= UTC_TIMESTAMP() - INTERVAL 24 HOUR';
	$where_week        = 'AND date >= UTC_TIMESTAMP() - INTERVAL 7 DAY';
	$where_month       = 'AND date >= UTC_TIMESTAMP() - INTERVAL 30 DAY';
	$where_year        = 'AND date >= UTC_TIMESTAMP() - INTERVAL 1 YEAR';
	$where_submissions = defined( 'SIMPLEFORM_SUBMISSIONS_NAME' ) ? "AND object != '' AND object != 'not stored' AND listable = '1'" : '';
	$count_all         = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions {$where_form} {$where_submissions}" ); // phpcs:ignore
	$count_last_year   = $count_all ? $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions {$where_form} {$where_year} {$where_submissions}" ) : '0'; // phpcs:ignore
	$count_last_month  = $count_last_year ? $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions {$where_form} {$where_month} {$where_submissions}" ) : '0'; // phpcs:ignore
	$count_last_week   = $count_last_month ? $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions {$where_form} {$where_week} {$where_submissions}" ) : '0'; // phpcs:ignore
	$count_last_day    = $count_last_week ? $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions {$where_form} {$where_day} {$where_submissions}" ) : '0'; // phpcs:ignore
	$total_received    = $count_all;
	$string1           = __( 'Submissions data is not stored in the WordPress database by default. ', 'simpleform' );
	$string2           = __( 'Submissions data is not stored in the WordPress database', 'simpleform' );
	$string3           = __( 'We have designed SimpleForm to be a minimal, lightweight, fast and privacy-respectful plugin, so that it does not interfere with your site performance and can be easily managed. ', 'simpleform' );
	$string4           = __( 'You can enable this feature with the <b>SimpleForm Contact Form Submissions</b> addon activation. ', 'simpleform' );
	$string5           = __( 'If you want to keep a copy of your messages, you can add this feature with the <b>SimpleForm Contact Form Submissions</b> addon. ', 'simpleform' );
	$string6           = __( 'You can find it in the WordPress.org plugin repository. ', 'simpleform' );
	$string7           = __( 'By default, only the last message is temporarily stored. ', 'simpleform' );
	$string8           = __( 'Therefore, it is recommended to verify the correct SMTP server configuration in case of use, and always keep the notification email enabled, if you want to be sure to receive messages. ', 'simpleform' );
	$string9           = __( 'You can enable this feature by activating the <b>SimpleForm Contact Form Submissions</b> addon. ', 'simpleform' );
	$string10          = __( ' Go to the Plugins page. ', 'simpleform' );
	$moved_from        = " WHERE moved_from = '" . $form . "'";
	$count_moved_all   = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions {$moved_from} {$where_submissions}" ); // phpcs:ignore
	$entries_data      = '<div><ul id="submissions-data"><li class="type"><span class="label">' . __( 'All', 'simpleform' ) . '</span><span class="value">' . $total_received . '</span></li><li class="type"><span class="label">' . __( 'This Year', 'simpleform' ) . '</span><span class="value">' . $count_last_year . '</span></li><li class="type"><span class="label">' . __( 'Last Month', 'simpleform' ) . '</span><span class="value">' . $count_last_month . '</span></li><li class="type"><span class="label">' . __( 'Last Week', 'simpleform' ) . '</span><span class="value">' . $count_last_week . '</span></li><li><span class="label">' . __( 'Last Day', 'simpleform' ) . '</span><span class="value">' . $count_last_day . '</span></li></ul></div>';

	$plugin_file = defined( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR . '/simpleform-contact-form-submissions/simpleform-submissions.php' : '';
	$admin_url   = is_network_admin() ? network_admin_url( 'plugins.php' ) : admin_url( 'plugins.php' );

	if ( $last_message ) {

		$split_mail        = explode( '&nbsp;&nbsp;&lt;&nbsp;', strval( $last_message ) );
		$email             = isset( $split_mail[1] ) ? explode( '&nbsp;&gt;', $split_mail[1] )[0] : '';
		$subject_separator = __( 'Subject', 'simpleform' ) . ':</td><td>';
		$split_subject     = explode( $subject_separator, strval( $last_message ) );
		$subject           = isset( $split_subject[1] ) ? explode( '</td>', $split_subject[1] )[0] : '';
		$separator         = ! empty( $subject ) ? '?' : '';
		/* translators: indicates a "reply" to a message */
		$mailsubject     = ! empty( $subject ) ? 'subject=' . __( 'Re: ', 'simpleform' ) . str_replace( ' ', '%20', $subject ) : '';
		$reply           = ! empty( $email ) ? '<span id="reply-message" class="' . $color . '"><a href="mailto:' . wp_strip_all_tags( $email ) . $separator . $mailsubject . '"><span class="icon dashicons dashicons-share-alt2"></span><span class="text unseen"> ' . __( 'Reply', 'simpleform' ) . '</span></a></span>' : '';
		$message_heading = $message_type ? __( 'Message Before Last', 'simpleform' ) : __( 'Last Message', 'simpleform' );
		$form_arg        = isset( $_GET['form'] ) && ! empty( $_GET['form'] ) ? '&form=' . absint( $_GET['form'] ) : ''; // phpcs:ignore 
		$before_arg      = $message_type ? '' : '&message=before';
		$prev_button     = ! $message_type ? '<div id="before-link"><a class="' . $color . '" href="' . admin_url( 'admin.php?page=sform-entries' ) . $form_arg . $before_arg . '"><span class="icon dashicons dashicons-arrow-left-alt2"></span><span class="text unseen"> ' . __( 'Before Last', 'simpleform' ) . '</span></a></div>' : '';
		$next_button     = $message_type ? '<div id="last-link"><a class="' . $color . '" href="' . admin_url( 'admin.php?page=sform-entries' ) . $form_arg . $before_arg . '"><span class="icon dashicons dashicons-arrow-right-alt2"></span><span class="text unseen"> ' . __( 'Last', 'simpleform' ) . '</span></a></div>' : '';
		$messages_nav    = $before_button ? '<div id="navigation-buttons">' . $prev_button . $next_button . '</div>' : '';

		$entries_data .= '<div id="last-submission"><h3><span class="dashicons dashicons-buddicons-pm"></span>' . $message_heading . $reply . '</h3>' . stripslashes( wpautop( strval( $last_message ) ) ) . $messages_nav . '</div>';

		if ( ! file_exists( $plugin_file ) ) {

			$entries_data .= '<div id="submissions-notice" class="unseen"><h3><span class="dashicons dashicons-editor-help"></span>' . __( 'Before you go crazy looking for the received messages', 'simpleform' ) . '</h3>' . __( 'Submissions data is not stored in the WordPress database. We have designed SimpleForm to be a minimal, lightweight, fast and privacy-respectful plugin, so that it does not interfere with your site performance and can be easily managed. If you want to keep a copy of your messages, you can add this feature with the <b>SimpleForm Contact Form Submissions</b> addon. You can find it in the WordPress.org plugin repository. By default, only the last message is temporarily stored. Therefore, it is recommended to verify the correct SMTP server configuration in case of use, and always keep the notification email enabled, if you want to be sure to receive messages. ', 'simpleform' ) . '</div>';

		} else {

			$activation_message = '<div id="submissions-notice" class="unseen"><h3><span class="dashicons dashicons-editor-help"></span>' . __( 'Before you go crazy looking for the received messages', 'simpleform' ) . '</h3>' . __( 'Submissions data is not stored in the WordPress database by default. We have designed SimpleForm to be a minimal, lightweight, fast and privacy-respectful plugin, so that it does not interfere with your site performance and can be easily managed. You can enable this feature by activating the <b>SimpleForm Contact Form Submissions</b> addon. Go to the Plugins page. ', 'simpleform' ) . '</div>';

			$entries_data .= ! class_exists( 'SimpleForm_Submissions' ) ? $activation_message : '';

		}
	} else {

		/* translators: %s: the number of messages that have been moved */
		$empty_message = $count_moved_all ? sprintf( _n( '%s message has been moved to other form', '%s messages have been moved to other form', $count_moved_all, 'simpleform' ), $count_moved_all ) : __( 'So far, no message has been received yet!', 'simpleform' );

		if ( ! file_exists( $plugin_file ) ) {

			/* translators: %s: link to WordPress.org plugin repository */
			$entries_data .= '<div id="empty-submission"><h3><span class="dashicons dashicons-info"></span>' . __( 'Empty Inbox', 'simpleform' ) . '</h3><b>' . $empty_message . '</b><p>' . sprintf( __( 'Please note that submissions data is not stored in the WordPress database by default. We have designed SimpleForm to be a minimal, lightweight, fast and privacy-respectful plugin, so that it does not interfere with your site performance and can be easily managed. If you want to keep a copy of your messages, you can add this feature with the <a href="%s" target="_blank">SimpleForm Contact Form Submissions</a> addon. You can find it in the WordPress.org plugin repository. ', 'simpleform' ), esc_url( 'https://wordpress.org/plugins/simpleform-contact-form-submissions/' ) ) . '</div>';

		} else {

			/* translators: %s: link to plugins admin page */
			$courtesy_message = '<div id="empty-submission"><h3><span class="dashicons dashicons-info"></span>' . __( 'Empty Inbox', 'simpleform' ) . '</h3>' . $empty_message . '<p>' . sprintf( __( 'Submissions data is not stored in the WordPress database by default. We have designed SimpleForm to be a minimal, lightweight, fast and privacy-respectful plugin, so that it does not interfere with your site performance and can be easily managed. You can enable this feature with the <b>SimpleForm Contact Form Submissions</b> addon activation. Go to the <a href="%s">Plugins</a> page. ', 'simpleform' ), esc_url( $admin_url ) ) . '</div>';

			$entries_data .= ! class_exists( 'SimpleForm_Submissions' ) ? $courtesy_message : '';

		}
	}

	// Add entries data if SimpleForm Contact Form Submissions plugin has been activated.
	if ( has_action( 'display_entries_data' ) ) {

		echo wp_kses( $entries_page, $allowed_tags );
		do_action( 'display_entries_data', absint( $form ), $forms, $last_message, $entries_data );

		// Page wrap: closing tag.
		echo '</div>';

	} else {

		$entries_page .= $entries_data . '</div>';
		echo wp_kses( $entries_page, $allowed_tags );

	}
} else {

	$entries_page .= '<div id="page-description" class="noentries"><p>' . __( 'It seems the form is no longer available!', 'simpleform' ) . '</p></div><div id="page-buttons"><span class="wp-core-ui button unavailable ' . esc_attr( $color ) . '"><a href="' . esc_url( menu_page_url( 'sform-entries', false ) ) . '">' . __( 'Reload the Entries page', 'simpleform' ) . '</a></span><span class="wp-core-ui button unavailable ' . esc_attr( $color ) . '"><a href="' . esc_url( menu_page_url( 'sform-new', false ) ) . '">' . __( 'Add New Form', 'simpleform' ) . '</a></span><span class="wp-core-ui button unavailable ' . esc_attr( $color ) . '"><a href="' . esc_url( self_admin_url( 'widgets.php' ) ) . '">' . __( 'Activate SimpleForm Contact Form Widget', 'simpleform' ) . '</a></span></div>';

	$entries_page .= '</div>';

	echo wp_kses( $entries_page, $allowed_tags );

}
