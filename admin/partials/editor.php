<?php
/**
 * File delegated to show the editor admin page.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/partials
 */

defined( 'ABSPATH' ) || exit;

$form          = isset( $_GET['form'] ) ? absint( $_GET['form'] ) : 1; // phpcs:ignore
$util          = new SimpleForm_Util();
$admin_notices = $util->get_sform_option( 1, 'settings', 'admin_notices', false );
$notice_class  = $admin_notices ? 'invisible' : '';
$version_alert = get_transient( 'sform_version_alert' );
$notice_class .= false !== $version_alert ? ' unseen' : '';
$wrap_class    = false !== $version_alert ? 'spaced' : '';
$notice        = '';
$color         = strval( $util->get_sform_option( 1, 'settings', 'admin_color', 'default' ) );
$extra_option  = '';
global $wpdb;
$page_forms   = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}sform_shortcodes WHERE widget = '0' AND status != 'trash' ORDER BY name ASC", 'ARRAY_A' ); // phpcs:ignore.
$widget_forms = $wpdb->get_results( "SELECT id, name, widget FROM {$wpdb->prefix}sform_shortcodes WHERE widget != '0' AND status != 'trash' AND status != 'inactive' ORDER BY name ASC", 'ARRAY_A' ); // phpcs:ignore.
$page_ids     = array_map( 'intval', array_column( $page_forms, 'id' ) );
$widget_ids   = array_map( 'intval', array_column( $widget_forms, 'id' ) );
$forms        = array_merge( $page_ids, $widget_ids );
$all_forms    = count( $page_forms ) + count( $widget_forms );
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
$widget_options   .= $widget_forms ? '</optgroup>' : '';
$forms_selector    = $all_forms > 1 ? '<div class="selector"><div id="wrap-selector" class="responsive">' . __( 'Select Form', 'simpleform' ) . ':</div><div class="form-selector"><select name="form" id="form" class="' . esc_attr( $color ) . '">' . $page_options . $widget_options . '</select></div></div>' : '';
$settings_arg      = 1 !== $form ? '&form=' . $form : '';
$form_arg          = 1 !== $form ? '&id=' . $form : '';
$default_name      = strval( $util->form_property_value( $form, 'name', __( 'Contact Us Page', 'simpleform' ) ) );
$contact_form_name = $util->get_sform_option( $form, 'attributes', 'form_name', $default_name );

global $wp_roles;

// Contact forms embedded in page.
if ( in_array( $form, $page_ids, true ) ) {

	$widget_id           = 0;
	$for                 = $util->get_sform_option( $form, 'attributes', 'show_for', 'all' );
	$show_for            = isset( $_GET['showfor'] ) ? $_GET['showfor'] : $for; // phpcs:ignore
	$visible_position    = 'in' !== $show_for ? 'last' : '';
	$restricted_tr_class = 'in' !== $show_for ? 'trlevel unseen' : 'trlevel';
	$restricted_position = 'in' === $show_for ? 'last' : '';
	$user_role           = $util->get_sform_option( $form, 'attributes', 'user_role', 'any' );

	$visibility_data = '<td class="select ' . $visible_position . '"><select name="show_for" id="show_for" class="sform"><option value="all" ' . selected( $show_for, 'all', false ) . '>' . __( 'Everyone', 'simpleform' ) . '</option><option value="in" ' . selected( $show_for, 'in', false ) . '>' . __( 'Logged-in users', 'simpleform' ) . '</option><option value="out" ' . selected( $show_for, 'out', false ) . '>' . __( 'Logged-out users', 'simpleform' ) . '</option></select></td>';

	$role_options = '';
	foreach ( $wp_roles->roles as $wp_role => $details ) {
		$role_selected = $wp_role === $user_role ? 'selected="selected"' : '';
		$role_options .= '\n\t<option value="' . esc_attr( strval( $wp_role ) ) . '" ' . $role_selected . '>' . translate_user_role( $details['name'] ) . '</option>';
	}

	$role_selector    = '<select name="user_role" id="user_role" class="sform"><option value="any" ' . selected( $user_role, 'any', false ) . '>' . __( 'Any', 'simpleform' ) . '</option>' . $role_options . '</select>';
	$restriction_data = '<td class="select ' . $restricted_position . '">' . $role_selector . '</td>';

} else {

	$sform_widget = (array) get_option( 'widget_simpleform' );
	// Get the key for this value.
	$key = array_search( $form, $widget_ids, true );
	// Retrieve the widget ID.
	$widget_codes = array_column( $widget_forms, 'widget' );
	$widget_id    = absint( $widget_codes[ $key ] );
	$show_for     = is_array( $sform_widget ) && ! empty( $sform_widget[ $widget_id ]['show_for'] ) ? $sform_widget[ $widget_id ]['show_for'] : 'all';
	$user_role    = is_array( $sform_widget ) && ! empty( $sform_widget[ $widget_id ]['user_role'] ) ? $sform_widget[ $widget_id ]['user_role'] : 'any';
	$role_name    = 'any' === $user_role ? __( 'Any', 'simpleform' ) : translate_user_role( $wp_roles->roles[ $user_role ]['name'] );

	if ( 'out' === $show_for ) {
		$target = __( 'Logged-out users', 'simpleform' );
	} elseif ( 'in' === $show_for ) {
		$target = __( 'Logged-in users', 'simpleform' );
	} else {
		$target = __( 'Everyone', 'simpleform' );
	}

	$visible_position    = 'in' !== $show_for ? 'last' : '';
	$visibility_data     = '<td class="plaintext ' . $visible_position . '">' . $target . '</td>';
	$restricted_tr_class = 'in' !== $show_for ? 'trlevel unseen' : 'trlevel';
	$restricted_position = 'in' === $show_for ? 'last' : '';
	$restriction_data    = '<td class="plaintext ' . $restricted_position . '">' . $role_name . '</td>';

}

$default_text_above = '<p>' . __( 'Please fill out the form below and we will get back to you as soon as possible. Mandatory fields are marked with (*).', 'simpleform' ) . '</p>';
$text_above         = $util->get_sform_option( $form, 'attributes', 'introduction_text', $default_text_above );
$text_below         = $util->get_sform_option( $form, 'attributes', 'bottom_text', '' );

if ( 'out' === $show_for ) {
	$name_field        = $util->get_sform_option( $form, 'attributes', 'name_field', 'anonymous' );
	$lastname_field    = $util->get_sform_option( $form, 'attributes', 'lastname_field', 'hidden' );
	$email_field       = $util->get_sform_option( $form, 'attributes', 'email_field', 'anonymous' );
	$phone_field       = $util->get_sform_option( $form, 'attributes', 'phone_field', 'hidden' );
	$website_field     = $util->get_sform_option( $form, 'attributes', 'website_field', 'hidden' );
	$subject_field     = $util->get_sform_option( $form, 'attributes', 'subject_field', 'anonymous' );
	$consent_field     = $util->get_sform_option( $form, 'attributes', 'consent_field', 'anonymous' );
	$captcha_field     = $util->get_sform_option( $form, 'attributes', 'captcha_field', 'hidden' );
	$field_description = __( 'You have set the form as visible only for logged-out users', 'simpleform' );
} elseif ( 'in' === $show_for ) {
	$name_field        = $util->get_sform_option( $form, 'attributes', 'name_field', 'registered' );
	$lastname_field    = $util->get_sform_option( $form, 'attributes', 'lastname_field', 'hidden' );
	$email_field       = $util->get_sform_option( $form, 'attributes', 'email_field', 'registered' );
	$phone_field       = $util->get_sform_option( $form, 'attributes', 'phone_field', 'hidden' );
	$website_field     = $util->get_sform_option( $form, 'attributes', 'website_field', 'hidden' );
	$subject_field     = $util->get_sform_option( $form, 'attributes', 'subject_field', 'registered' );
	$consent_field     = $util->get_sform_option( $form, 'attributes', 'consent_field', 'registered' );
	$captcha_field     = $util->get_sform_option( $form, 'attributes', 'captcha_field', 'hidden' );
	$field_description = __( 'You have set the form as visible only for logged-in users', 'simpleform' );
} else {
	$name_field        = $util->get_sform_option( $form, 'attributes', 'name_field', 'visible' );
	$lastname_field    = $util->get_sform_option( $form, 'attributes', 'lastname_field', 'hidden' );
	$email_field       = $util->get_sform_option( $form, 'attributes', 'email_field', 'visible' );
	$phone_field       = $util->get_sform_option( $form, 'attributes', 'phone_field', 'hidden' );
	$website_field     = $util->get_sform_option( $form, 'attributes', 'website_field', 'hidden' );
	$subject_field     = $util->get_sform_option( $form, 'attributes', 'subject_field', 'visible' );
	$consent_field     = $util->get_sform_option( $form, 'attributes', 'consent_field', 'visible' );
	$captcha_field     = $util->get_sform_option( $form, 'attributes', 'captcha_field', 'hidden' );
	$field_description = '';
}

$name_data            = 'all' === $show_for ? '<td class="select"><select name="name_field" id="name_field" class="sform"><option value="visible" ' . selected( $name_field, 'visible', false ) . '>' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered" ' . selected( $name_field, 'registered', false ) . '>' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous" ' . selected( $name_field, 'anonymous', false ) . '>' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden" ' . selected( $name_field, 'hidden', false ) . '>' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="name_field" id="name_field" parent="name" class="sform-switch cbfield" value="' . $name_field . '" ' . checked( $name_field, 'hidden', false ) . '><span></span></label><label for="name_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$name_field_class     = 'hidden' === $name_field ? 'unseen' : '';
$name_visibility      = $util->get_sform_option( $form, 'attributes', 'name_visibility', 'visible' );
$name_label_class     = 'hidden' === $name_field || 'hidden' === $name_visibility ? 'unseen' : '';
$name_label           = $util->get_sform_option( $form, 'attributes', 'name_label', __( 'Name', 'simpleform' ) );
$name_placeholder     = $util->get_sform_option( $form, 'attributes', 'name_placeholder', '' );
$name_minlength       = $util->get_sform_option( $form, 'attributes', 'name_minlength', 2 );
$name_maxlength       = $util->get_sform_option( $form, 'attributes', 'name_maxlength', 0 );
$name_requirement     = $util->get_sform_option( $form, 'attributes', 'name_requirement', 'required' );
$lastname_data        = 'all' === $show_for ? '<td class="select"><select name="lastname_field" id="lastname_field" class="sform"><option value="visible" ' . selected( $lastname_field, 'visible', false ) . '>' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered" ' . selected( $lastname_field, 'registered', false ) . '>' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous" ' . selected( $lastname_field, 'anonymous', false ) . '>' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden" ' . selected( $lastname_field, 'hidden', false ) . '>' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="lastname_field" id="lastname_field" parent="lastname" class="sform-switch cbfield" value="' . $lastname_field . '" ' . checked( $lastname_field, 'hidden', false ) . '><span></span></label><label for="lastname_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$lastname_field_class = 'hidden' === $lastname_field ? 'unseen' : '';
$lastname_visibility  = $util->get_sform_option( $form, 'attributes', 'lastname_visibility', 'visible' );
$lastname_label_class = 'hidden' === $lastname_field || 'hidden' === $lastname_visibility ? 'unseen' : '';
$lastname_label       = $util->get_sform_option( $form, 'attributes', 'lastname_label', __( 'Last Name', 'simpleform' ) );
$lastname_placeholder = $util->get_sform_option( $form, 'attributes', 'lastname_placeholder', '' );
$lastname_minlength   = $util->get_sform_option( $form, 'attributes', 'lastname_minlength', 2 );
$lastname_maxlength   = $util->get_sform_option( $form, 'attributes', 'lastname_maxlength', 0 );
$lastname_requirement = $util->get_sform_option( $form, 'attributes', 'lastname_requirement', 'optional' );
$email_data           = 'all' === $show_for ? '<td class="select"><select name="email_field" id="email_field" class="sform"><option value="visible" ' . selected( $email_field, 'visible', false ) . '>' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered" ' . selected( $email_field, 'registered', false ) . '>' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous" ' . selected( $email_field, 'anonymous', false ) . '>' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden" ' . selected( $email_field, 'hidden', false ) . '>' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="email_field" id="email_field" parent="email" class="sform-switch cbfield" value="' . $email_field . '" ' . checked( $email_field, 'hidden', false ) . '><span></span></label><label for="email_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$email_field_class    = 'hidden' === $email_field ? 'unseen' : '';
$email_visibility     = $util->get_sform_option( $form, 'attributes', 'email_visibility', 'visible' );
$email_label_class    = 'hidden' === $email_field || 'hidden' === $email_visibility ? 'unseen' : '';
$email_label          = $util->get_sform_option( $form, 'attributes', 'email_label', __( 'Email', 'simpleform' ) );
$email_placeholder    = $util->get_sform_option( $form, 'attributes', 'email_placeholder', '' );
$email_requirement    = $util->get_sform_option( $form, 'attributes', 'email_requirement', 'required' );
$phone_data           = 'all' === $show_for ? '<td class="select"><select name="phone_field" id="phone_field" class="sform"><option value="visible" ' . selected( $phone_field, 'visible', false ) . '>' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered" ' . selected( $phone_field, 'registered', false ) . '>' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous" ' . selected( $phone_field, 'anonymous', false ) . '>' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden" ' . selected( $phone_field, 'hidden', false ) . '>' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="phone_field" id="phone_field" parent="phone" class="sform-switch cbfield" value="' . $phone_field . '" ' . checked( $phone_field, 'hidden', false ) . '><span></span></label><label for="phone_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$phone_field_class    = 'hidden' === $phone_field ? 'unseen' : '';
$phone_visibility     = $util->get_sform_option( $form, 'attributes', 'phone_visibility', 'visible' );
$phone_label_class    = 'hidden' === $phone_field || 'hidden' === $phone_visibility ? 'unseen' : '';
$phone_label          = $util->get_sform_option( $form, 'attributes', 'phone_label', __( 'Phone', 'simpleform' ) );
$phone_placeholder    = $util->get_sform_option( $form, 'attributes', 'phone_placeholder', '' );
$phone_requirement    = $util->get_sform_option( $form, 'attributes', 'phone_requirement', 'optional' );
$website_data         = 'all' === $show_for ? '<td class="select"><select name="website_field" id="website_field" class="sform"><option value="visible" ' . selected( $website_field, 'visible', false ) . '>' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered" ' . selected( $website_field, 'registered', false ) . '>' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous" ' . selected( $website_field, 'anonymous', false ) . '>' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden" ' . selected( $website_field, 'hidden', false ) . '>' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="website_field" id="website_field" parent="website" class="sform-switch cbfield" value="' . $website_field . '" ' . checked( $website_field, 'hidden', false ) . '><span></span></label><label for="website_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$website_field_class  = 'hidden' === $website_field ? 'unseen' : '';
$website_visibility   = $util->get_sform_option( $form, 'attributes', 'website_visibility', 'visible' );
$website_label_class  = 'hidden' === $website_field || 'hidden' === $website_visibility ? 'unseen' : '';
$website_label        = $util->get_sform_option( $form, 'attributes', 'website_label', __( 'Website', 'simpleform' ) );
$website_placeholder  = $util->get_sform_option( $form, 'attributes', 'website_placeholder', '' );
$website_requirement  = $util->get_sform_option( $form, 'attributes', 'website_requirement', 'optional' );
$subject_data         = 'all' === $show_for ? '<td class="select"><select name="subject_field" id="subject_field" class="sform"><option value="visible" ' . selected( $subject_field, 'visible', false ) . '>' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered" ' . selected( $subject_field, 'registered', false ) . '>' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous" ' . selected( $subject_field, 'anonymous', false ) . '>' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden" ' . selected( $subject_field, 'hidden', false ) . '>' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="subject_field" id="subject_field" parent="subject" class="sform-switch cbfield" value="' . $subject_field . '" ' . checked( $subject_field, 'hidden', false ) . '><span></span></label><label for="subject_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$subject_field_class  = 'hidden' === $subject_field ? 'unseen' : '';
$subject_visibility   = $util->get_sform_option( $form, 'attributes', 'subject_visibility', 'visible' );
$subject_label_class  = 'hidden' === $subject_field || 'hidden' === $subject_visibility ? 'unseen' : '';
$subject_label        = $util->get_sform_option( $form, 'attributes', 'subject_label', __( 'Subject', 'simpleform' ) );
$subject_placeholder  = $util->get_sform_option( $form, 'attributes', 'subject_placeholder', '' );
$subject_minlength    = $util->get_sform_option( $form, 'attributes', 'subject_minlength', 5 );
$subject_maxlength    = $util->get_sform_option( $form, 'attributes', 'subject_maxlength', 0 );
$subject_requirement  = $util->get_sform_option( $form, 'attributes', 'subject_requirement', 'required' );
$message_visibility   = $util->get_sform_option( $form, 'attributes', 'message_visibility', 'visible' );
$message_label_class  = 'hidden' === $message_visibility ? 'unseen' : '';
$message_label        = $util->get_sform_option( $form, 'attributes', 'message_label', __( 'Message', 'simpleform' ) );
$message_placeholder  = $util->get_sform_option( $form, 'attributes', 'message_placeholder', '' );
$message_minlength    = $util->get_sform_option( $form, 'attributes', 'message_minlength', 10 );
$message_maxlength    = $util->get_sform_option( $form, 'attributes', 'message_maxlength', 0 );
$consent_data         = 'all' === $show_for ? '<td class="select"><select name="consent_field" id="consent_field" class="sform"><option value="visible" ' . selected( $consent_field, 'visible', false ) . '>' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered" ' . selected( $consent_field, 'registered', false ) . '>' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous" ' . selected( $consent_field, 'anonymous', false ) . '>' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden" ' . selected( $consent_field, 'hidden', false ) . '>' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="consent_field" id="consent_field" parent="consent" class="sform-switch cbfield" value="' . $consent_field . '" ' . checked( $consent_field, 'hidden', false ) . '><span></span></label><label for="consent_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$consent_field_class  = 'hidden' === $consent_field ? 'unseen' : '';
$consent_label        = $util->get_sform_option( $form, 'attributes', 'consent_label', __( 'I have read and consent to the privacy policy', 'simpleform' ) );
$clickable_pages      = get_pages(
	array(
		'sort_column' => 'post_title',
		'sort_order'  => 'ASC',
		'post_type'   => 'page',
		'post_status' => array( 'publish', 'draft' ),
	)
);
$privacy_link         = $util->get_sform_option( $form, 'attributes', 'privacy_link', false );
$privacy_page_class   = 'hidden' === $consent_field || ! $privacy_link ? 'unseen' : '';
$privacy_page         = intval( $util->get_sform_option( $form, 'attributes', 'privacy_page', 0 ) );
$clickable_options    = '';
if ( $clickable_pages ) {
	// $clickable_pages is array of object.
	foreach ( $clickable_pages as $clickable_page ) {
		if ( is_object( $clickable_page ) ) {
			$clickable_options .= '<option value="' . $clickable_page->ID . '"  tag="' . $clickable_page->post_status . '" ' . selected( $privacy_page, $clickable_page->ID, false ) . '>' . $clickable_page->post_title . '</option>';
		}
	}
}
$pages_selector = '<select name="privacy_page" class="sform" id="privacy_page"><option value="">' . __( 'Select the page', 'simpleform' ) . '</option>' . $clickable_options . '</select>';
$edit_page      = '<a href="' . get_edit_post_link( $privacy_page ) . '" target="_blank" style="text-decoration: none; color: #9ccc79;">' . __( 'Publish now', 'simpleform' ) . '</a>';
$edit           = _x( 'Edit', 'present simple: Edit or view the page content', 'simpleform' );
$view           = _x( 'view', 'present simple: Edit or view the page content', 'simpleform' );
/* translators: %1$s: Edit, %2$s: view */
$post_url              = 0 !== $privacy_page ? sprintf( __( '%1$s or %2$s the page content', 'simpleform' ), '<strong><a href="' . get_edit_post_link( $privacy_page ) . '" target="_blank" style="text-decoration: none;">' . $edit . '</a></strong>', '<strong><a href="' . get_page_link( $privacy_page ) . '" target="_blank" style="text-decoration: none;">' . $view . '</a></strong>' ) : '&nbsp;';
$privacy_status        = 0 !== $privacy_page && 'draft' === get_post_status( $privacy_page ) ? __( 'Page in draft status not yet published', 'simpleform' ) . '&nbsp;-&nbsp;' . $edit_page : $post_url;
$link_opening_class    = 'hidden' === $consent_field || ! $privacy_link || 0 === $privacy_page ? 'unseen' : '';
$target_attribute      = $util->get_sform_option( $form, 'attributes', 'target', false );
$consent_requirement   = $util->get_sform_option( $form, 'attributes', 'consent_requirement', 'required' );
$captcha_data          = 'all' === $show_for ? '<td class="select"><select name="captcha_field" id="captcha_field" class="sform"><option value="visible" ' . selected( $captcha_field, 'visible', false ) . '>' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered" ' . selected( $captcha_field, 'registered', false ) . '>' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous" ' . selected( $captcha_field, 'anonymous', false ) . '>' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden" ' . selected( $captcha_field, 'hidden', false ) . '>' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="captcha_field" id="captcha_field" parent="captcha" class="sform-switch cbfield" value="' . $captcha_field . '" ' . checked( $captcha_field, 'hidden', false ) . '><span></span></label><label for="captcha_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$captcha_label_class   = 'hidden' === $captcha_field ? 'unseen' : '';
$captcha_label_class   = apply_filters( 'hide_captcha_label', $captcha_label_class, $form );
$math_captcha_label    = $util->get_sform_option( $form, 'attributes', 'captcha_label', __( 'I\'m not a robot', 'simpleform' ) );
$submit_label          = $util->get_sform_option( $form, 'attributes', 'submit_label', __( 'Submit', 'simpleform' ) );
$label_position        = $util->get_sform_option( $form, 'attributes', 'label_position', 'top' );
$column_lastname_class = 'hidden' === $name_field || 'hidden' === $lastname_field ? 'unseen' : '';
$lastname_alignment    = $util->get_sform_option( $form, 'attributes', 'lastname_alignment', 'name' );
$column_phone_class    = 'hidden' === $email_field || 'hidden' === $phone_field ? 'unseen' : '';
$phone_alignment       = $util->get_sform_option( $form, 'attributes', 'phone_alignment', 'email' );
$submit_position       = $util->get_sform_option( $form, 'attributes', 'submit_position', 'centred' );
$label_size            = $util->get_sform_option( $form, 'attributes', 'label_size', 'default' );
$required_sign         = $util->get_sform_option( $form, 'attributes', 'required_sign', true );
$required_sign_class   = $required_sign ? 'unseen' : '';
$required_word         = $util->get_sform_option( $form, 'attributes', 'required_word', __( '(required)', 'simpleform' ) );
$word_position         = $util->get_sform_option( $form, 'attributes', 'word_position', 'required' );
$form_direction        = $util->get_sform_option( $form, 'attributes', 'form_direction', 'ltr' );
$additional_css        = $util->get_sform_option( $form, 'attributes', 'additional_css', '' );
$allowed_tags          = $util->sform_allowed_tags();

// Page wrap: opening tag.
$editor_page = '<div id="sform-wrap" class="sform ' . esc_attr( $wrap_class ) . '">';

// Admin notice.
$editor_page .= '<div id="new-release" class="' . esc_attr( $notice_class ) . '">' . wp_kses_post( apply_filters( 'sform_update', $notice ) ) . '&nbsp;</div>';

// Page heading.
$editor_page .= '<div class="full-width-bar ' . esc_attr( $color ) . '"><h1 class="title ' . esc_attr( $color ) . '"><span class="dashicons dashicons-editor-table responsive"></span>' . __( 'Editor', 'simpleform' ) . $forms_selector . '</h1></div>';

if ( in_array( $form, $page_ids, true ) || in_array( $form, $widget_ids, true ) ) {

	// Description page.
	$editor_page .= '<div id="page-description"><p>' . __( 'Change easily the way your contact form is displayed. Choose which fields to use and who should see them:', 'simpleform' ) . '</p></div>';

	// Tabs.
	$editor_page .= '<div id="editor-tabs"><a class="nav-tab nav-tab-active" id="builder">' . __( 'Form Builder', 'simpleform' ) . '</a><a class="nav-tab" id="appearance">' . __( 'Form Appearance', 'simpleform' ) . '</a><a class="form-button last ' . esc_attr( $color ) . '" href="' . admin_url( 'admin.php?page=sform-settings' ) . $settings_arg . '" target="_blank"><span><span class="dashicons dashicons-admin-settings"></span><span class="text">' . __( 'Settings', 'simpleform' ) . '</span></span></a><a class="form-button form-page ' . esc_attr( $color ) . '" href="' . admin_url( 'admin.php?page=sform-form' ) . $form_arg . '" target="_blank"><span><span class="dashicons dashicons-tag"></span><span class="text">' . __( 'More specifics', 'simpleform' ) . '</span></span></a></div>';

	// Form opening tag.
	$editor_page .= '<form id="attributes" method="post" class="' . esc_attr( $color ) . '">';

	// Current form ID.
	$editor_page .= '<input type="hidden" id="form_id" name="form_id" value="' . $form . '">';
	$editor_page .= '<input type="hidden" id="widget_id" name="widget_id" value="' . $widget_id . '">';

	// Form Builder Tab opening.
	$editor_page .= '<div id="tab-builder" class="navtab">';

	// Specifics options.
	$editor_page .= '<h2 id="h2-specifics" class="options-heading"><span class="heading" data-section="specifics">' . __( 'Specifics', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 specifics"></span></span></h2><div class="section specifics"><table class="form-table specifics"><tbody>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Form Name', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="form_name" id="form_name" class="sform" placeholder="' . esc_attr__( 'Enter a name for this Form', 'simpleform' ) . '" value="' . $contact_form_name . '"></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Visible to', 'simpleform' ) . '</span></th>' . $visibility_data . '</tr>';

	$editor_page .= '<tr class="' . $restricted_tr_class . '"><th class="option"><span>' . __( 'Restricted to', 'simpleform' ) . '</span></th>' . $restriction_data . '</tr></tbody></table></div>';

	// Description options.
	$editor_page .= '<h2 id="h2-formdescription" class="options-heading"><span class="heading" data-section="formdescription">' . __( 'Description', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 formdescription"></span></span></h2><div class="section formdescription"><table class="form-table formdescription"><tbody>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Text above Form', 'simpleform' ) . '</span></th><td class="textarea"><textarea name="text_above" id="text_above" class="sform description" placeholder="' . esc_attr__( 'Enter the text that must be displayed above the form. It can be used to provide a description or instructions for filling in the form.', 'simpleform' ) . '" >' . $text_above . '</textarea><p class="description">' . __( 'The HTML tags for formatting message are allowed', 'simpleform' ) . '</p></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Text below Form', 'simpleform' ) . '</span></th><td class="textarea last"><textarea name="text_below" id="text_below" class="sform description" placeholder="' . esc_attr__( 'Enter the text that must be displayed below the form. It can be used to provide additional information.', 'simpleform' ) . '" >' . $text_below . '</textarea><p class="description">' . __( 'The HTML tags for formatting message are allowed', 'simpleform' ) . '</p></td></tr></tbody></table></div>';

	// Fields options.
	$editor_page .= '<h2 id="h2-formfields" class="options-heading"><span class="heading" data-section="formfields">' . __( 'Fields', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 formfields"></span></span></h2><div class="section formfields"><table class="form-table formfields"><tbody>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Name Field', 'simpleform' ) . '</span></th>' . $name_data . '</tr>';

	$editor_page .= '<tr class="trname ' . $name_field_class . '"><th class="option"><span>' . __( 'Name Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="name_visibility" id="namelabel" class="sform-switch field-label" value="' . $name_visibility . '" ' . checked( $name_visibility, 'hidden', false ) . '><span></span></label><label for="namelabel" class="switch-label">' . __( 'Hide label for name field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr class="trname namelabel ' . $name_label_class . '" ><th class="option"><span>' . __( 'Name Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="name_label" id="name_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the name field', 'simpleform' ) . '" value="' . $name_label . '"></td></tr>';

	$editor_page .= '<tr class="trname ' . $name_field_class . '" ><th class="option"><span>' . __( 'Name Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="name_placeholder" id="name_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the name field. If blank, it will not be used!', 'simpleform' ) . '" value="' . $name_placeholder . '"></td></tr>';

	$editor_page .= '<tr class="trname ' . $name_field_class . '" ><th class="option"><span>' . __( 'Name\'s Minimum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="name_minlength" id="name_minlength" class="sform" value="' . $name_minlength . '" min="0" max="80"><span class="description left">' . __( 'Notice that 0 means no minimum limit', 'simpleform' ) . '</span></td></tr>';

	$editor_page .= '<tr class="trname ' . $name_field_class . '" ><th class="option"><span>' . __( 'Name\'s Maximum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="name_maxlength" id="name_maxlength" class="sform" value="' . $name_maxlength . '" min="0" max="80"><span class="description left">' . __( 'Notice that 0 means no maximum limit', 'simpleform' ) . '</span></td></tr>';

	$editor_page .= '<tr class="trname ' . $name_field_class . '"><th class="option"><span>' . __( 'Name Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="name_required" id="name_required" class="sform-switch" value="' . $name_requirement . '" ' . checked( $name_requirement, 'required', false ) . '><span></span></label><label for="name_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Last Name Field', 'simpleform' ) . '</span></th>' . $lastname_data . '</tr>';

	$editor_page .= '<tr class="trlastname ' . $lastname_field_class . '"><th class="option"><span>' . __( 'Last Name Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="lastname_visibility" id="lastnamelabel" class="sform-switch field-label" value="' . $lastname_visibility . '" ' . checked( $lastname_visibility, 'hidden', false ) . '><span></span></label><label for="lastnamelabel" class="switch-label">' . __( 'Hide label for last name field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr class="trlastname lastnamelabel ' . $lastname_label_class . '" ><th class="option"><span>' . __( 'Last Name Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="lastname_label" id="lastname_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the last name field', 'simpleform' ) . '" value="' . $lastname_label . '"></td></tr>';

	$editor_page .= '<tr class="trlastname ' . $lastname_field_class . '" ><th class="option"><span>' . __( 'Last Name Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="lastname_placeholder" id="lastname_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the last name field. If blank, it will not be used!', 'simpleform' ) . '" value="' . $lastname_placeholder . '"></td></tr>';

	$editor_page .= '<tr class="trlastname ' . $lastname_field_class . '" ><th class="option"><span>' . __( 'Last Name\'s Minimum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="lastname_minlength" id="lastname_minlength" class="sform" value="' . $lastname_minlength . '" min="0" max="80"><span class="description left">' . __( 'Notice that 0 means no minimum limit', 'simpleform' ) . '</span></td></tr>';

	$editor_page .= '<tr class="trlastname ' . $lastname_field_class . '" ><th class="option"><span>' . __( 'Last Name\'s Maximum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="lastname_maxlength" id="lastname_maxlength" class="sform" value="' . $lastname_maxlength . '" min="0" max="80"><span class="description left">' . __( 'Notice that 0 means no maximum limit', 'simpleform' ) . '</span></td></tr>';

	$editor_page .= '<tr class="trlastname ' . $lastname_field_class . '"><th class="option"><span>' . __( 'Last Name Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="lastname_required" id="lastname_required" class="sform-switch" value="' . $lastname_requirement . '" ' . checked( $lastname_requirement, 'required', false ) . '><span></span></label><label for="lastname_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Email Field', 'simpleform' ) . '</span></th>' . $email_data . '</tr>';

	$editor_page .= '<tr class="tremail ' . $email_field_class . '"><th class="option"><span>' . __( 'Email Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="email_visibility" id="emaillabel" class="sform-switch field-label" value="' . $email_visibility . '" ' . checked( $email_visibility, 'hidden', false ) . '><span></span></label><label for="emaillabel" class="switch-label">' . __( 'Hide label for email field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr class="tremail emaillabel ' . $email_label_class . '" ><th class="option"><span>' . __( 'Email Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="email_label" id="email_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the email field', 'simpleform' ) . '" value="' . $email_label . '"></td></tr>';

	$editor_page .= '<tr class="tremail ' . $email_field_class . '" ><th class="option"><span>' . __( 'Email Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="email_placeholder" id="email_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the email field. If blank, it will not be used!', 'simpleform' ) . '" value="' . $email_placeholder . '"></td></tr>';

	$editor_page .= '<tr class="tremail ' . $email_field_class . '"><th class="option"><span>' . __( 'Email Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="email_required" id="email_required" class="sform-switch" value="' . $email_requirement . '" ' . checked( $email_requirement, 'required', false ) . '><span></span></label><label for="email_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Phone Field', 'simpleform' ) . '</span></th>' . $phone_data . '</tr>';

	$editor_page .= '<tr class="trphone ' . $phone_field_class . '"><th class="option"><span>' . __( 'Phone Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="phone_visibility" id="phonelabel" class="sform-switch field-label" value="' . $phone_visibility . '" ' . checked( $phone_visibility, 'hidden', false ) . '><span></span></label><label for="phonelabel" class="switch-label">' . __( 'Hide label for phone field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr class="trphone phonelabel ' . $phone_label_class . '" ><th class="option"><span>' . __( 'Phone Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="phone_label" id="phone_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the phone field', 'simpleform' ) . '" value="' . $phone_label . '"></td></tr>';

	$editor_page .= '<tr class="trphone ' . $phone_field_class . '" ><th class="option"><span>' . __( 'Phone Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="phone_placeholder" id="phone_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the phone field. If blank, it will not be used!', 'simpleform' ) . '" value="' . $phone_placeholder . '"></td></tr>';

	$editor_page .= '<tr class="trphone ' . $phone_field_class . '"><th class="option"><span>' . __( 'Phone Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="phone_required" id="phone_required" class="sform-switch" value="' . $phone_requirement . '" ' . checked( $phone_requirement, 'required', false ) . '><span></span></label><label for="phone_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Website Field', 'simpleform' ) . '</span></th>' . $website_data . '</tr>';

	$editor_page .= '<tr class="trwebsite ' . $website_field_class . '"><th class="option"><span>' . __( 'Website Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="website_visibility" id="websitelabel" class="sform-switch field-label" value="' . $website_visibility . '" ' . checked( $website_visibility, 'hidden', false ) . '><span></span></label><label for="websitelabel" class="switch-label">' . __( 'Hide label for website field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr class="trwebsite websitelabel ' . $website_label_class . '" ><th class="option"><span>' . __( 'Website Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="website_label" id="website_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the website field', 'simpleform' ) . '" value="' . $website_label . '"></td></tr>';

	$editor_page .= '<tr class="trwebsite ' . $website_field_class . '" ><th class="option"><span>' . __( 'Website Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="website_placeholder" id="website_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the website field. If blank, it will not be used!', 'simpleform' ) . '" value="' . $website_placeholder . '"></td></tr>';

	$editor_page .= '<tr class="trwebsite ' . $website_field_class . '"><th class="option"><span>' . __( 'Website Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="website_required" id="website_required" class="sform-switch" value="' . $website_requirement . '" ' . checked( $website_requirement, 'required', false ) . '><span></span></label><label for="website_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Subject Field', 'simpleform' ) . '</span></th>' . $subject_data . '</tr>';

	$editor_page .= '<tr class="trsubject ' . $subject_field_class . '"><th class="option"><span>' . __( 'Subject Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="subject_visibility" id="subjectlabel" class="sform-switch field-label" value="' . $subject_visibility . '" ' . checked( $subject_visibility, 'hidden', false ) . '><span></span></label><label for="subjectlabel" class="switch-label">' . __( 'Hide label for subject field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr class="trsubject subjectlabel ' . $subject_label_class . '" ><th class="option"><span>' . __( 'Subject Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="subject_label" id="subject_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the subject field', 'simpleform' ) . '" value="' . $subject_label . '"></td></tr>';

	$editor_page .= '<tr class="trsubject ' . $subject_field_class . '" ><th class="option"><span>' . __( 'Subject Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="subject_placeholder" id="subject_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the subject field. If blank, it will not be used!', 'simpleform' ) . '" value="' . $subject_placeholder . '"></td></tr>';

	$editor_page .= '<tr class="trsubject ' . $subject_field_class . '" ><th class="option"><span>' . __( 'Subject\'s Minimum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="subject_minlength" id="subject_minlength" class="sform" value="' . $subject_minlength . '" min="0" max="80"><span class="description left">' . __( 'Notice that 0 means no minimum limit', 'simpleform' ) . '</span></td></tr>';

	$editor_page .= '<tr class="trsubject ' . $subject_field_class . '" ><th class="option"><span>' . __( 'Subject\'s Maximum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="subject_maxlength" id="subject_maxlength" class="sform" value="' . $subject_maxlength . '" min="0" max="80"><span class="description left">' . __( 'Notice that 0 means no maximum limit', 'simpleform' ) . '</span></td></tr>';

	$editor_page .= '<tr class="trsubject ' . $subject_field_class . '"><th class="option"><span>' . __( 'Subject Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="subject_required" id="subject_required" class="sform-switch" value="' . $subject_requirement . '" ' . checked( $subject_requirement, 'required', false ) . '><span></span></label><label for="subject_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Message Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="message_visibility" id="messagelabel" class="sform-switch field-label" value="' . $message_visibility . '" ' . checked( $message_visibility, 'hidden', false ) . '><span></span></label><label for="messagelabel" class="switch-label">' . __( 'Hide label for message field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr class="messagelabel ' . $message_label_class . '" ><th class="option"><span>' . __( 'Message Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="message_label" id="message_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the message field', 'simpleform' ) . '" value="' . $message_label . '"></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Message Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="message_placeholder" id="message_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the message field. If blank, it will not be used!', 'simpleform' ) . '" value="' . $message_placeholder . '"></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Message\'s Minimum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="message_minlength" id="message_minlength" class="sform" value="' . $message_minlength . '" min="5" max="200"></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Message\'s Maximum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="message_maxlength" id="message_maxlength" class="sform" value="' . $message_maxlength . '" min="0" max="1000"><span class="description left">' . __( 'Notice that 0 means no maximum limit', 'simpleform' ) . '</span></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Consent Field', 'simpleform' ) . '</span></th>' . $consent_data . '</tr>';

	$editor_page .= '<tr class="trconsent ' . $consent_field_class . '" ><th class="option"><span>' . __( 'Consent Field Label', 'simpleform' ) . '</span></th><td class="textarea"><textarea name="consent_label" id="consent_label" class="sform labels" placeholder="' . esc_attr__( 'Enter a label for the consent field', 'simpleform' ) . '" >' . $consent_label . '</textarea><p class="description">' . __( 'The HTML tags for formatting the consent field label are allowed', 'simpleform' ) . '</p></td></tr>';

	$editor_page .= $clickable_pages ? '<tr class="trconsent ' . $consent_field_class . '"><th class="option"><span>' . __( 'Link to Privacy Policy', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="privacy_link" id="privacy_link" class="sform-switch" value="' . $privacy_link . '" ' . checked( $privacy_link, true, false ) . '><span></span></label><label for="privacy_link" class="switch-label">' . __( 'Insert a link to the Privacy Policy page in the consent field label', 'simpleform' ) . '</label></div></td></tr>' : '';

	$editor_page .= $clickable_pages ? '<tr class="trconsent trpage ' . $privacy_page_class . '"><th class="option"><span>' . __( 'Privacy Policy Page', 'simpleform' ) . '</span><span id="label-error-top"></span></th><td class="select notes">' . $pages_selector . '<input type="hidden" id="page_id" name="page_id" value=""><input type="submit" name="submit" id="set-page" class="privacy-setting button unseen" value="' . esc_attr__( 'Use This Page', 'simpleform' ) . '" page="' . $privacy_page . '"><span id="label-error"></span><p id="post-status" class="description">' . $privacy_status . '</p><span id="set-page-icon" class="privacy-setting dashicons dashicons-plus unseen ' . $color . '" page="' . $privacy_page . '"></span></td></tr>' : '';

	$editor_page .= $clickable_pages ? '<tr class="trconsent trpage trtarget ' . $link_opening_class . '" ><th class="option"><span>' . __( 'Link Opening', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="target" id="target" class="sform-switch" value="' . $target_attribute . '" ' . checked( $target_attribute, true, false ) . '><span></span></label><label for="target" class="switch-label">' . __( 'Open the link to privacy policy in a new tab', 'simpleform' ) . '</label></div></td></tr>' : '';

	$editor_page .= '<tr class="trconsent ' . $consent_field_class . '"><th class="option"><span>' . __( 'Consent Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="consent_required" id="consent_required" class="sform-switch" value="' . $consent_requirement . '" ' . checked( $consent_requirement, 'required', false ) . '><span></span></label><label for="consent_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div><p class="description">' . __( 'If you\'re collecting personal data, this field is required for requesting the user\'s explicit consent', 'simpleform' ) . '</p></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Captcha Field', 'simpleform' ) . '</span></th>' . $captcha_data . '</tr>';

	// reCaptcha options.
	$editor_page .= apply_filters( 'recaptcha_editor_fields', $extra_option, $form );

	$editor_page .= '<tr class="trcaptcha clabel ' . $captcha_label_class . '"><th class="option"><span>' . __( 'Captcha Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="captcha_label" id="captcha_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the captcha field', 'simpleform' ) . '" value="' . $math_captcha_label . '"></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Submit Button Label', 'simpleform' ) . '</span></th><td class="text last"><input type="text" name="submit_label" id="submit_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the submit field', 'simpleform' ) . '" value="' . $submit_label . '"></td></tr></tbody></table></div>';

	// Form Builder Tab closing.
	$editor_page .= '</div>';

	// Form Appearance Tab opening.
	$editor_page .= '<div id="tab-appearance" class="navtab unseen">';

	// Layout options.
	$editor_page .= '<h2 id="h2-layout" class="options-heading"><span class="heading" data-section="layout">' . __( 'Layout', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 layout"></span></span></h2><div class="section layout"><table class="form-table layout"><tbody>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Label Position', 'simpleform' ) . '</span></th><td class="radio"><fieldset><label for="top-position"><input type="radio" name="label_position" id="top-position" value="top" ' . checked( $label_position, 'top', false ) . '>' . __( 'Top', 'simpleform' ) . '</label><label for="inline-position"><input type="radio" name="label_position" id="inline-position" value="inline" ' . checked( $label_position, 'inline', false ) . '> ' . __( 'Inline', 'simpleform' ) . '</label></fieldset></td></tr>';

	$editor_page .= '<tr class="trname trlastname ' . $column_lastname_class . '"><th class="option"><span>' . __( 'Single Column Last Name Field', 'simpleform' ) . '</span></th><td class="radio"><fieldset><label for="single-line-lastname"><input type="radio" name="lastname_alignment" id="single-line-lastname" value="alone" ' . checked( $lastname_alignment, 'alone', false ) . '>' . __( 'Place on a single line', 'simpleform' ) . '</label><label for="name-line"><input type="radio" name="lastname_alignment" id="name-line" value="name" ' . checked( $lastname_alignment, 'name', false ) . '> ' . __( 'Place next to name field on the same line', 'simpleform' ) . '</label></fieldset></td></tr>';

	$editor_page .= '<tr class="tremail trphone ' . $column_phone_class . '"><th class="option"><span>' . __( 'Single Column Phone Field', 'simpleform' ) . '</span></th><td class="radio"><fieldset><label for="single-line-phone"><input type="radio" name="phone_alignment" id="single-line-phone" value="alone" ' . checked( $phone_alignment, 'alone', false ) . '>' . __( 'Place on a single line', 'simpleform' ) . '</label><label for="email-line"><input type="radio" name="phone_alignment" id="email-line" value="email" ' . checked( $phone_alignment, 'email', false ) . '> ' . __( 'Place next to email field on the same line', 'simpleform' ) . '</label></fieldset></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Submit Button Position', 'simpleform' ) . '</span></th><td class="select last"><select name="submit_position" id="submit_position" class="sform"><option value="left" ' . selected( $submit_position, 'left', false ) . '>' . __( 'Left', 'simpleform' ) . '</option><option value="right" ' . selected( $submit_position, 'right', false ) . '>' . __( 'Right', 'simpleform' ) . '</option><option value="centred" ' . selected( $submit_position, 'centred', false ) . '>' . __( 'Centred', 'simpleform' ) . '</option><option value="full" ' . selected( $submit_position, 'full', false ) . '>' . __( 'Full Width', 'simpleform' ) . '</option></select></td></tr></tbody></table></div>';

	// Style options.
	$editor_page .= '<h2 id="h2-style" class="options-heading"><span class="heading" data-section="style">' . __( 'Style', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 style"></span></span></h2><div class="section style"><table class="form-table style"><tbody>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Label Size', 'simpleform' ) . '</span></th><td class="select"><select name="label_size" id="label_size" class="sform"><option value="smaller" ' . selected( $label_size, 'smaller', false ) . '>' . __( 'Smaller', 'simpleform' ) . '</option><option value="default" ' . selected( $label_size, 'default', false ) . '>' . __( 'Default', 'simpleform' ) . '</option><option value="larger" ' . selected( $label_size, 'larger', false ) . '>' . __( 'Larger', 'simpleform' ) . '</option></select></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Required Field Symbol', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="required_sign" id="required_sign" class="sform-switch field-label" value="' . $required_sign . '" ' . checked( $required_sign, true, false ) . '><span></span></label><label for="required_sign" class="switch-label">' . __( 'Use an asterisk at the end of the label to mark a required field', 'simpleform' ) . '</label></div></td></tr>';

	$editor_page .= '<tr class="trsign ' . $required_sign_class . '"><th class="option"><span>' . __( 'Replacement Word', 'simpleform' ) . '</span></th><td class="text notes"><input type="text" name="required_word" id="required_word" class="sform" placeholder="' . esc_attr__( 'Enter a word to mark a required field or an optional field', 'simpleform' ) . '" value="' . $required_word . '" \><p class="description">' . __( 'The replacement word will be placed at the end of the field label, except for the consent and captcha fields. If you hide the label, remember to include it in the placeholder!', 'simpleform' ) . '</p></td></tr>';

	$editor_page .= '<tr class="trsign ' . $required_sign_class . '"><th class="option"><span>' . __( 'Required/Optional Field Labelling', 'simpleform' ) . '</span></th><td class="radio"><fieldset><label for="required-labelling"><input type="radio" name="word_position" id="required-labelling" value="required" ' . checked( $word_position, 'required', false ) . '>' . __( 'Use the replacement word to mark a required field', 'simpleform' ) . '</label><label for="optional-labelling"><input type="radio" name="word_position" id="optional-labelling" value="optional" ' . checked( $word_position, 'optional', false ) . '> ' . __( 'Use the replacement word to mark an optional field', 'simpleform' ) . '</label></fieldset></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Form Direction', 'simpleform' ) . '</span></th><td class="radio"><fieldset><label for="ltr-direction"><input type="radio" name="form_direction" id="ltr-direction" value="ltr" ' . checked( $form_direction, 'ltr', false ) . '>' . __( 'Left to Right', 'simpleform' ) . '</label><label for="rtl-direction"><input type="radio" name="form_direction" id="rtl-direction" value="rtl" ' . checked( $form_direction, 'rtl', false ) . '> ' . __( 'Right to Left', 'simpleform' ) . '</label></fieldset></td></tr>';

	$editor_page .= '<tr><th class="option"><span>' . __( 'Additional CSS', 'simpleform' ) . '</span></th><td class="textarea last"><textarea name="additional_css" id="additional_css" class="sform" placeholder="' . esc_attr__( 'Add your own CSS code to customize the appearance of your form', 'simpleform' ) . '" >' . $additional_css . '</textarea><p class="description">' . __( 'Be careful to correctly identify the form elements using their id, otherwise the CSS rules apply to all your forms!', 'simpleform' ) . '</p></td></tr></tbody></table></div>';

	// Form Appearance Tab closing.
	$editor_page .= '</div>';

	// Save changes button.
	$editor_page .= '<div id="submit-wrap"><div id="alert-wrap"><noscript><div id="noscript">' . __( 'You need JavaScript enabled to edit form. Please activate it. Thanks!', 'simpleform' ) . '</div></noscript><div id="message-wrap" class="message"></div></div><input type="submit" name="save-attributes" id="save-attributes" class="submit-button" value="' . esc_attr__( 'Save Changes', 'simpleform' ) . '">' . wp_nonce_field( 'simpleform_backend_update', 'simpleform_nonce', false, false ) . '</div>';

	// Form closing tag.
	$editor_page .= '</form>';

} else {

	$editor_page .= '<div id="page-description"><p>' . __( 'It seems the form is no longer available!', 'simpleform' ) . '</p></div><div id="page-buttons"><span class="wp-core-ui button unavailable ' . esc_attr( $color ) . '"><a href="' . esc_url( menu_page_url( 'sform-editor', false ) ) . '">' . __( 'Reload the Editor page', 'simpleform' ) . '</a></span><span class="wp-core-ui button unavailable ' . esc_attr( $color ) . '"><a href="' . esc_url( menu_page_url( 'sform-new', false ) ) . '">' . __( 'Add New Form', 'simpleform' ) . '</a></span><span class="wp-core-ui button unavailable ' . esc_attr( $color ) . '"><a href="' . esc_url( self_admin_url( 'widgets.php' ) ) . '">' . __( 'Activate SimpleForm Contact Form Widget', 'simpleform' ) . '</a></span></div>';

}

// Page wrap: closing tag.
$editor_page .= '</div>';

echo wp_kses( $editor_page, $allowed_tags );
