<?php
/**
 * File delegated to show the new form admin page.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/partials
 */

defined( 'ABSPATH' ) || exit;

$util              = new SimpleForm_Util();
$admin_notices     = $util->get_sform_option( 1, 'settings', 'admin_notices', false );
$color             = strval( $util->get_sform_option( 1, 'settings', 'admin_color', 'default' ) );
$notice_class      = $admin_notices ? 'invisible' : '';
$version_alert     = get_transient( 'sform_version_alert' );
$notice_class     .= false !== $version_alert ? ' unseen' : '';
$wrap_class        = false !== $version_alert ? 'spaced' : '';
$notice            = '';
$back_button       = '<a href="' . esc_url( get_admin_url( get_current_blog_id(), 'admin.php?page=sform-forms' ) ) . '"><span class="dashicons dashicons-list-view icon-button admin ' . esc_attr( $color ) . '"></span><span class="wp-core-ui button admin back-list ' . esc_attr( $color ) . '">' . __( 'Back to forms', 'simpleform' ) . '</span></a>';
$embed_in          = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : ''; // phpcs:ignore 
$show_for          = isset( $_GET['showfor'] ) ? sanitize_text_field( wp_unslash( $_GET['showfor'] ) ) : 'all'; // phpcs:ignore 
$field_description = 'out' === $show_for ? __( 'You have set the form as visible only for logged-out users', 'simpleform' ) : __( 'You have set the form as visible only for logged-in users', 'simpleform' );
$visible_position  = 'in' !== $show_for ? 'last' : '';
$restricted_class  = 'in' !== $show_for ? 'unseen' : '';
global $wp_roles;
$role_options = '';
foreach ( $wp_roles->roles as $wp_role => $details ) {
	$role_options .= '\n\t<option value="' . esc_attr( $wp_role ) . '" >' . translate_user_role( $details['name'] ) . '</option>';
}
$name_field        = 'out' === $show_for ? 'anonymous' : 'registered';
$name_data         = 'all' === $show_for ? '<td class="select"><select name="name_field" id="name_field" class="sform"><option value="visible" selected="selected">' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered">' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous">' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden">' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="name_field" id="name_field" parent="name" class="sform-switch cbfield" value="' . $name_field . '"><span></span></label><label for="name_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$lastname_data     = 'all' === $show_for ? '<td class="select"><select name="lastname_field" id="lastname_field" class="sform"><option value="visible">' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered">' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous">' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden" selected="selected">' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="lastname_field" id="lastname_field" parent="lastname" class="sform-switch cbfield" value="hidden" checked="checked"><span></span></label><label for="lastname_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$email_field       = 'out' === $show_for ? 'anonymous' : 'registered';
$email_data        = 'all' === $show_for ? '<td class="select"><select name="email_field" id="email_field" class="sform"><option value="visible" selected="selected">' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered">' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous">' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden">' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="email_field" id="email_field" parent="email" class="sform-switch cbfield" value="' . $email_field . '"><span></span></label><label for="email_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$phone_data        = 'all' === $show_for ? '<td class="select"><select name="phone_field" id="phone_field" class="sform"><option value="visible">' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered">' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous">' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden" selected="selected">' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="phone_field" id="phone_field" parent="phone" class="sform-switch cbfield" value="hidden" checked="checked"><span></span></label><label for="phone_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$website_data      = 'all' === $show_for ? '<td class="select"><select name="website_field" id="website_field" class="sform"><option value="visible">' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered">' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous">' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden" selected="selected">' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="website_field" id="website_field" parent="website" class="sform-switch cbfield" value="hidden" checked="checked"><span></span></label><label for="website_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$subject_field     = 'out' === $show_for ? 'anonymous' : 'registered';
$subject_data      = 'all' === $show_for ? '<td class="select"><select name="subject_field" id="subject_field" class="sform"><option value="visible" selected="selected">' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered">' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous">' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden">' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="subject_field" id="subject_field" parent="subject" class="sform-switch cbfield" value="' . $subject_field . '"><span></span></label><label for="subject_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$consent_field     = 'out' === $show_for ? 'anonymous' : 'registered';
$consent_data      = 'all' === $show_for ? '<td class="select"><select name="consent_field" id="consent_field" class="sform"><option value="visible" selected="selected">' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered">' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous">' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden">' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="consent_field" id="consent_field" parent="consent" class="sform-switch cbfield" value="' . $consent_field . '"><span></span></label><label for="consent_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$clickable_pages   = get_pages(
	array(
		'sort_column' => 'post_title',
		'sort_order'  => 'ASC',
		'post_type'   => 'page',
		'post_status' => array( 'publish', 'draft' ),
	)
);
$clickable_options = '';
if ( $clickable_pages ) {
	// $clickable_pages is array of object.
	foreach ( $clickable_pages as $clickable_page ) {
		if ( is_object( $clickable_page ) ) {
			$clickable_options .= '<option value="' . $clickable_page->ID . '"  tag="' . $clickable_page->post_status . '">' . $clickable_page->post_title . '</option>';
		}
	}
}
$pages_selector = '<select name="privacy_page" class="sform" id="privacy_page"><option value="">' . __( 'Select the page', 'simpleform' ) . '</option>' . $clickable_options . '</select>';
$captcha_data   = 'all' === $show_for ? '<td class="select"><select name="captcha_field" id="captcha_field" class="sform"><option value="visible">' . __( 'Display to all users', 'simpleform' ) . '</option><option value="registered">' . __( 'Display only to registered users', 'simpleform' ) . '</option><option value="anonymous">' . __( 'Display only to anonymous users', 'simpleform' ) . '</option><option value="hidden" selected="selected">' . __( 'Do not display', 'simpleform' ) . '</option></select></td>' : '<td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="captcha_field" id="captcha_field" parent="captcha" class="sform-switch cbfield" value="hidden" checked="checked"><span></span></label><label for="captcha_field" class="switch-label">' . __( 'Do not display', 'simpleform' ) . '</label></div><p class="description">' . $field_description . '</p></td>';
$util           = new SimpleForm_Util();
$allowed_tags   = $util->sform_allowed_tags();

// Page wrap: opening tag.
$newform_page = '<div id="sform-wrap" class="sform ' . esc_attr( $wrap_class ) . '">';

// Admin notice.
$newform_page .= '<div id="new-release" class="' . esc_attr( $notice_class ) . '">' . wp_kses_post( apply_filters( 'sform_update', $notice ) ) . '&nbsp;</div>';

// Page heading.
$newform_page .= '<div class="full-width-bar ' . esc_attr( $color ) . '"><h1 class="title ' . esc_attr( $color ) . '"><span class="dashicons dashicons-plus-alt responsive"></span>' . __( 'Add New', 'simpleform' ) . $back_button . '</h1></div>';

// Description page.
$newform_page .= '<div id="page-description"><p>' . __( 'Adding a new form is quick and easy. Do it whenever you need it!', 'simpleform' ) . '</p></div>';

// Tabs.
$newform_page .= '<div id="editor-tabs"><a class="nav-tab nav-tab-active" id="builder">' . __( 'Form Builder', 'simpleform' ) . '</a><a class="nav-tab" id="appearance">' . __( 'Form Appearance', 'simpleform' ) . '</a></div>';

// Form opening tag.
$newform_page .= '<form id="attributes" method="post" class="' . esc_attr( $color ) . '">';

// Hidden form data.
$newform_page .= '<input type="hidden" id="newform" name="newform" value="true">';
$newform_page .= '<input type="hidden" id="embed_in" name="embed_in" value="' . $embed_in . '">';

// Form Builder Tab opening.
$newform_page .= '<div id="tab-builder" class="navtab">';

// Specifics options.
$newform_page .= '<h2 id="h2-specifics" class="options-heading"><span class="heading" data-section="specifics">' . __( 'Specifics', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 specifics"></span></span></h2><div class="section specifics"><table class="form-table specifics"><tbody>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Form Name', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="form_name" id="form_name" class="sform" placeholder="' . esc_attr__( 'Enter a name for this Form', 'simpleform' ) . '" value=""></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Show for', 'simpleform' ) . '</span></th><td class="select ' . $visible_position . '"><select name="show_for" id="show_for" class="sform"><option value="all" ' . selected( $show_for, 'all', false ) . '>' . __( 'Everyone', 'simpleform' ) . '</option><option value="in" ' . selected( $show_for, 'in', false ) . '>' . __( 'Logged-in users', 'simpleform' ) . '</option><option value="out" ' . selected( $show_for, 'out', false ) . '>' . __( 'Logged-out users', 'simpleform' ) . '</option></select></td></tr>';

$newform_page .= '<tr class="trlevel ' . $restricted_class . '"><th class="option"><span>' . __( 'Role', 'simpleform' ) . '</span></th><td class="last select"><select name="user_role" id="user_role" class="sform"><option value="any" selected="selected">' . __( 'Any', 'simpleform' ) . '</option>' . $role_options . '</select></td></tr></tbody></table></div>';

// Description options.
$newform_page .= '<h2 id="h2-formdescription" class="options-heading"><span class="heading" data-section="formdescription">' . __( 'Description', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 formdescription"></span></span></h2><div class="section formdescription"><table class="form-table formdescription"><tbody>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Text above Form', 'simpleform' ) . '</span></th><td class="textarea"><textarea name="text_above" id="text_above" class="sform description" placeholder="' . esc_attr__( 'Enter the text that must be displayed above the form. It can be used to provide a description or instructions for filling in the form.', 'simpleform' ) . '" ><p>' . __( 'Please fill out the form below and we will get back to you as soon as possible. Mandatory fields are marked with (*).', 'simpleform' ) . '</p></textarea><p class="description">' . __( 'The HTML tags for formatting message are allowed', 'simpleform' ) . '</p></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Text below Form', 'simpleform' ) . '</span></th><td class="textarea last"><textarea name="text_below" id="text_below" class="sform description" placeholder="' . esc_attr__( 'Enter the text that must be displayed below the form. It can be used to provide additional information.', 'simpleform' ) . '" ></textarea><p class="description">' . __( 'The HTML tags for formatting message are allowed', 'simpleform' ) . '</p></td></tr></tbody></table></div>';

// Fields options.
$newform_page .= '<h2 id="h2-formfields" class="options-heading"><span class="heading" data-section="formfields">' . __( 'Fields', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 formfields"></span></span></h2><div class="section formfields"><table class="form-table formfields"><tbody>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Name Field', 'simpleform' ) . '</span></th>' . $name_data . '</tr>';

$newform_page .= '<tr class="trname"><th class="option"><span>' . __( 'Name Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="name_visibility" id="namelabel" class="sform-switch field-label" value="visible"><span></span></label><label for="namelabel" class="switch-label">' . __( 'Hide label for name field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr class="trname namelabel"><th class="option"><span>' . __( 'Name Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="name_label" id="name_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the name field', 'simpleform' ) . '" value="' . esc_attr__( 'Name', 'simpleform' ) . '"></td></tr>';

$newform_page .= '<tr class="trname"><th class="option"><span>' . __( 'Name Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="name_placeholder" id="name_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the name field. If blank, it will not be used!', 'simpleform' ) . '" value=""></td></tr>';

$newform_page .= '<tr class="trname"><th class="option"><span>' . __( 'Name\'s Minimum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="name_minlength" id="name_minlength" class="sform" value="2" min="0" max="80"><span class="description left">' . __( 'Notice that 0 means no minimum limit', 'simpleform' ) . '</span></td></tr>';

$newform_page .= '<tr class="trname"><th class="option"><span>' . __( 'Name\'s Maximum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="name_maxlength" id="name_maxlength" class="sform" value="0" min="0" max="80"><span class="description left">' . __( 'Notice that 0 means no maximum limit', 'simpleform' ) . '</span></td></tr>';

$newform_page .= '<tr class="trname"><th class="option"><span>' . __( 'Name Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="name_required" id="name_required" class="sform-switch" value="required" checked="checked"><span></span></label><label for="name_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Last Name Field', 'simpleform' ) . '</span></th>' . $lastname_data . '</tr>';

$newform_page .= '<tr class="trlastname unseen"><th class="option"><span>' . __( 'Last Name Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="lastname_visibility" id="lastnamelabel" class="sform-switch field-label" value="visible"><span></span></label><label for="lastnamelabel" class="switch-label">' . __( 'Hide label for last name field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr class="trlastname lastnamelabel unseen"><th class="option"><span>' . __( 'Last Name Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="lastname_label" id="lastname_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the last name field', 'simpleform' ) . '" value="' . esc_attr__( 'Last Name', 'simpleform' ) . '"></td></tr>';

$newform_page .= '<tr class="trlastname unseen"><th class="option"><span>' . __( 'Last Name Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="lastname_placeholder" id="lastname_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the last name field. If blank, it will not be used!', 'simpleform' ) . '" value=""></td></tr>';

$newform_page .= '<tr class="trlastname unseen"><th class="option"><span>' . __( 'Last Name\'s Minimum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="lastname_minlength" id="lastname_minlength" class="sform" value="2" min="0" max="80"><span class="description left">' . __( 'Notice that 0 means no minimum limit', 'simpleform' ) . '</span></td></tr>';

$newform_page .= '<tr class="trlastname unseen"><th class="option"><span>' . __( 'Last Name\'s Maximum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="lastname_maxlength" id="lastname_maxlength" class="sform" value="0" min="0" max="80"><span class="description left">' . __( 'Notice that 0 means no maximum limit', 'simpleform' ) . '</span></td></tr>';

$newform_page .= '<tr class="trlastname unseen"><th class="option"><span>' . __( 'Last Name Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="lastname_required" id="lastname_required" class="sform-switch" value="optional"><span></span></label><label for="lastname_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Email Field', 'simpleform' ) . '</span></th>' . $email_data . '</tr>';

$newform_page .= '<tr class="tremail"><th class="option"><span>' . __( 'Email Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="email_visibility" id="emaillabel" class="sform-switch field-label" value="visible"><span></span></label><label for="emaillabel" class="switch-label">' . __( 'Hide label for email field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr class="tremail emaillabel"><th class="option"><span>' . __( 'Email Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="email_label" id="email_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the email field', 'simpleform' ) . '" value="' . esc_attr__( 'Email', 'simpleform' ) . '"></td></tr>';

$newform_page .= '<tr class="tremail"><th class="option"><span>' . __( 'Email Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="email_placeholder" id="email_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the email field. If blank, it will not be used!', 'simpleform' ) . '" value=""></td></tr>';

$newform_page .= '<tr class="tremail"><th class="option"><span>' . __( 'Email Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="email_required" id="email_required" class="sform-switch" value="required" checked="checked"><span></span></label><label for="email_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Phone Field', 'simpleform' ) . '</span></th>' . $phone_data . '</tr>';

$newform_page .= '<tr class="trphone unseen"><th class="option"><span>' . __( 'Phone Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="phone_visibility" id="phonelabel" class="sform-switch field-label" value="visible"><span></span></label><label for="phonelabel" class="switch-label">' . __( 'Hide label for phone field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr class="trphone phonelabel unseen"><th class="option"><span>' . __( 'Phone Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="phone_label" id="phone_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the phone field', 'simpleform' ) . '" value="' . esc_attr__( 'Phone', 'simpleform' ) . '"></td></tr>';

$newform_page .= '<tr class="trphone unseen"><th class="option"><span>' . __( 'Phone Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="phone_placeholder" id="phone_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the phone field. If blank, it will not be used!', 'simpleform' ) . '" value=""></td></tr>';

$newform_page .= '<tr class="trphone unseen"><th class="option"><span>' . __( 'Phone Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="phone_required" id="phone_required" class="sform-switch" value="optional"><span></span></label><label for="phone_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Website Field', 'simpleform' ) . '</span></th>' . $website_data . '</tr>';

$newform_page .= '<tr class="trwebsite unseen"><th class="option"><span>' . __( 'Website Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="website_visibility" id="websitelabel" class="sform-switch field-label" value="visible"><span></span></label><label for="websitelabel" class="switch-label">' . __( 'Hide label for website field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr class="trwebsite websitelabel unseen"><th class="option"><span>' . __( 'Website Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="website_label" id="website_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the website field', 'simpleform' ) . '" value="' . esc_attr__( 'Website', 'simpleform' ) . '"></td></tr>';

$newform_page .= '<tr class="trwebsite unseen"><th class="option"><span>' . __( 'Website Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="website_placeholder" id="website_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the website field. If blank, it will not be used!', 'simpleform' ) . '" value=""></td></tr>';

$newform_page .= '<tr class="trwebsite unseen"><th class="option"><span>' . __( 'Website Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="website_required" id="website_required" class="sform-switch" value="optional"><span></span></label><label for="website_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Subject Field', 'simpleform' ) . '</span></th>' . $subject_data . '</tr>';

$newform_page .= '<tr class="trsubject"><th class="option"><span>' . __( 'Subject Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="subject_visibility" id="subjectlabel" class="sform-switch field-label" value="visible"><span></span></label><label for="subjectlabel" class="switch-label">' . __( 'Hide label for subject field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr class="trsubject subjectlabel"><th class="option"><span>' . __( 'Subject Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="subject_label" id="subject_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the subject field', 'simpleform' ) . '" value="' . esc_attr__( 'Subject', 'simpleform' ) . '"></td></tr>';

$newform_page .= '<tr class="trsubject"><th class="option"><span>' . __( 'Subject Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="subject_placeholder" id="subject_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the subject field. If blank, it will not be used!', 'simpleform' ) . '" value=""></td></tr>';

$newform_page .= '<tr class="trsubject"><th class="option"><span>' . __( 'Subject\'s Minimum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="subject_minlength" id="subject_minlength" class="sform" value="5" min="0" max="80"><span class="description left">' . __( 'Notice that 0 means no minimum limit', 'simpleform' ) . '</span></td></tr>';

$newform_page .= '<tr class="trsubject"><th class="option"><span>' . __( 'Subject\'s Maximum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="subject_maxlength" id="subject_maxlength" class="sform" value="0" min="0" max="80"><span class="description left">' . __( 'Notice that 0 means no maximum limit', 'simpleform' ) . '</span></td></tr>';

$newform_page .= '<tr class="trsubject"><th class="option"><span>' . __( 'Subject Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="subject_required" id="subject_required" class="sform-switch" value="required" checked="checked"><span></span></label><label for="subject_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Message Field Label Visibility', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="message_visibility" id="messagelabel" class="sform-switch field-label" value="visible"><span></span></label><label for="messagelabel" class="switch-label">' . __( 'Hide label for message field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr class="messagelabel><th class="option"><span>' . __( 'Message Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="message_label" id="message_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the message field', 'simpleform' ) . '" value="' . esc_attr__( 'Message', 'simpleform' ) . '"></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Message Field Placeholder', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="message_placeholder" id="message_placeholder" class="sform" placeholder="' . esc_attr__( 'Enter a placeholder for the message field. If blank, it will not be used!', 'simpleform' ) . '" value=""></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Message\'s Minimum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="message_minlength" id="message_minlength" class="sform" value="10" min="5" max="200"></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Message\'s Maximum Length', 'simpleform' ) . '</span></th><td class="text"><input type="number" name="message_maxlength" id="message_maxlength" class="sform" value="0" min="0" max="1000"><span class="description left">' . __( 'Notice that 0 means no maximum limit', 'simpleform' ) . '</span></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Consent Field', 'simpleform' ) . '</span></th>' . $consent_data . '</tr>';

$newform_page .= '<tr class="trconsent" ><th class="option"><span>' . __( 'Consent Field Label', 'simpleform' ) . '</span></th><td class="textarea"><textarea name="consent_label" id="consent_label" class="sform labels" placeholder="' . esc_attr__( 'Enter a label for the consent field', 'simpleform' ) . '" >' . __( 'I have read and consent to the privacy policy', 'simpleform' ) . '</textarea><p class="description">' . __( 'The HTML tags for formatting the consent field label are allowed', 'simpleform' ) . '</p></td></tr>';

$newform_page .= $clickable_pages ? '<tr class="trconsent"><th class="option"><span>' . __( 'Link to Privacy Policy', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="privacy_link" id="privacy_link" class="sform-switch" value="false"><span></span></label><label for="privacy_link" class="switch-label">' . __( 'Insert a link to the Privacy Policy page in the consent field label', 'simpleform' ) . '</label></div></td></tr>' : '';

$newform_page .= $clickable_pages ? '<tr class="trconsent trpage unseen"><th class="option"><span>' . __( 'Privacy Policy Page', 'simpleform' ) . '</span><span id="label-error-top"></span></th><td class="select notes">' . $pages_selector . '<input type="hidden" id="page_id" name="page_id" value=""><input type="submit" name="submit" id="set-page" class="privacy-setting button unseen" value="' . esc_attr__( 'Use This Page', 'simpleform' ) . '" page="0"><span id="label-error"></span><p id="post-status" class="description">&nbsp;</p><span id="set-page-icon" class="privacy-setting dashicons dashicons-plus unseen ' . $color . '" page="0"></span></td></tr>' : '';

$newform_page .= $clickable_pages ? '<tr class="trconsent trpage trtarget unseen"><th class="option"><span>' . __( 'Link Opening', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="target" id="target" class="sform-switch" value="false"><span></span></label><label for="target" class="switch-label">' . __( 'Open the link to privacy policy in a new tab', 'simpleform' ) . '</label></div></td></tr>' : '';

$newform_page .= '<tr class="trconsent"><th class="option"><span>' . __( 'Consent Field Requirement', 'simpleform' ) . '</span></th><td class="checkbox-switch notes"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="consent_required" id="consent_required" class="sform-switch" value="required" checked="checked"><span></span></label><label for="consent_required" class="switch-label">' . __( 'Make this a required field', 'simpleform' ) . '</label></div><p class="description">' . __( 'If you\'re collecting personal data, this field is required for requesting the user\'s explicit consent', 'simpleform' ) . '</p></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Captcha Field', 'simpleform' ) . '</span></th>' . $captcha_data . '</tr>';

// reCAPTCHA option.
$newform_page .= apply_filters( 'recaptcha_editor_fields', '', 1 );

$newform_page .= '<tr class="trcaptcha clabel unseen"><th class="option"><span>' . __( 'Captcha Field Label', 'simpleform' ) . '</span></th><td class="text"><input type="text" name="captcha_label" id="captcha_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the captcha field', 'simpleform' ) . '" value="' . __( 'I\'m not a robot', 'simpleform' ) . '"></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Submit Button Label', 'simpleform' ) . '</span></th><td class="text last"><input type="text" name="submit_label" id="submit_label" class="sform" placeholder="' . esc_attr__( 'Enter a label for the submit field', 'simpleform' ) . '" value="' . esc_attr__( 'Submit', 'simpleform' ) . '"></td></tr></tbody></table></div>';

// Form Builder Tab closing.
$newform_page .= '</div>';

// Form Appearance Tab opening.
$newform_page .= '<div id="tab-appearance" class="navtab unseen">';

// Layout options.
$newform_page .= '<h2 id="h2-layout" class="options-heading"><span class="heading" data-section="layout">' . __( 'Layout', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 layout"></span></span></h2><div class="section layout"><table class="form-table layout"><tbody>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Label Position', 'simpleform' ) . '</span></th><td class="radio"><fieldset><label for="top-position"><input type="radio" name="label_position" id="top-position" value="top" checked="checked">' . __( 'Top', 'simpleform' ) . '</label><label for="inline-position"><input type="radio" name="label_position" id="inline-position" value="inline">' . __( 'Inline', 'simpleform' ) . '</label></fieldset></td></tr>';

$newform_page .= '<tr class="trname trlastname unseen"><th class="option"><span>' . __( 'Single Column Last Name Field', 'simpleform' ) . '</span></th><td class="radio"><fieldset><label for="single-line-lastname"><input type="radio" name="lastname_alignment" id="single-line-lastname" value="alone">' . __( 'Place on a single line', 'simpleform' ) . '</label><label for="name-line"><input type="radio" name="lastname_alignment" id="name-line" value="name" checked="checked"> ' . __( 'Place next to name field on the same line', 'simpleform' ) . '</label></fieldset></td></tr>';

$newform_page .= '<tr class="tremail trphone unseen"><th class="option"><span>' . __( 'Single Column Phone Field', 'simpleform' ) . '</span></th><td class="radio"><fieldset><label for="single-line-phone"><input type="radio" name="phone_alignment" id="single-line-phone" value="alone">' . __( 'Place on a single line', 'simpleform' ) . '</label><label for="email-line"><input type="radio" name="phone_alignment" id="email-line" value="email" checked="checked"> ' . __( 'Place next to email field on the same line', 'simpleform' ) . '</label></fieldset></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Submit Button Position', 'simpleform' ) . '</span></th><td class="select last"><select name="submit_position" id="submit_position" class="sform"><option value="left">' . __( 'Left', 'simpleform' ) . '</option><option value="right">' . __( 'Right', 'simpleform' ) . '</option><option value="centred" selected="selected">' . __( 'Centred', 'simpleform' ) . '</option><option value="full">' . __( 'Full Width', 'simpleform' ) . '</option></select></td></tr></tbody></table></div>';

// Style options.
$newform_page .= '<h2 id="h2-style" class="options-heading"><span class="heading" data-section="style">' . __( 'Style', 'simpleform' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 style"></span></span></h2><div class="section style"><table class="form-table style"><tbody>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Label Size', 'simpleform' ) . '</span></th><td class="select"><select name="label_size" id="label_size" class="sform"><option value="smaller">' . __( 'Smaller', 'simpleform' ) . '</option><option value="default" selected="selected">' . __( 'Default', 'simpleform' ) . '</option><option value="larger">' . __( 'Larger', 'simpleform' ) . '</option></select></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Required Field Symbol', 'simpleform' ) . '</span></th><td class="checkbox-switch"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="required_sign" id="required_sign" class="sform-switch field-label" value="true" checked="checked"><span></span></label><label for="required_sign" class="switch-label">' . __( 'Use an asterisk at the end of the label to mark a required field', 'simpleform' ) . '</label></div></td></tr>';

$newform_page .= '<tr class="trsign unseen"><th class="option"><span>' . __( 'Replacement Word', 'simpleform' ) . '</span></th><td class="text notes"><input type="text" name="required_word" id="required_word" class="sform" placeholder="' . esc_attr__( 'Enter a word to mark a required field or an optional field', 'simpleform' ) . '" value="' . esc_attr__( '(required)', 'simpleform' ) . '" \><p class="description">' . __( 'The replacement word will be placed at the end of the field label, except for the consent and captcha fields. If you hide the label, remember to include it in the placeholder!', 'simpleform' ) . '</p></td></tr>';

$newform_page .= '<tr class="trsign unseen"><th class="option"><span>' . __( 'Required/Optional Field Labelling', 'simpleform' ) . '</span></th><td class="radio"><fieldset><label for="required-labelling"><input type="radio" name="word_position" id="required-labelling" value="required" checked="checked">' . __( 'Use the replacement word to mark a required field', 'simpleform' ) . '</label><label for="optional-labelling"><input type="radio" name="word_position" id="optional-labelling" value="optional"> ' . __( 'Use the replacement word to mark an optional field', 'simpleform' ) . '</label></fieldset></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Form Direction', 'simpleform' ) . '</span></th><td class="radio"><fieldset><label for="ltr-direction"><input type="radio" name="form_direction" id="ltr-direction" value="ltr" checked="checked">' . __( 'Left to Right', 'simpleform' ) . '</label><label for="rtl-direction"><input type="radio" name="form_direction" id="rtl-direction" value="rtl"> ' . __( 'Right to Left', 'simpleform' ) . '</label></fieldset></td></tr>';

$newform_page .= '<tr><th class="option"><span>' . __( 'Additional CSS', 'simpleform' ) . '</span></th><td class="textarea last"><textarea name="additional_css" id="additional_css" class="sform" placeholder="' . esc_attr__( 'Add your own CSS code to customize the appearance of your form', 'simpleform' ) . '" ></textarea><p class="description">' . __( 'Be careful to correctly identify the form elements using their id, otherwise the CSS rules apply to all your forms!', 'simpleform' ) . '</p></td></tr></tbody></table></div>';

// Form Appearance Tab closing.
$newform_page .= '</div>';

// Save changes button.
$newform_page .= '<div id="submit-wrap"><div id="alert-wrap"><noscript><div id="noscript">' . __( 'You need JavaScript enabled to edit form. Please activate it. Thanks!', 'simpleform' ) . '</div></noscript><div id="message-wrap" class="message"></div></div><input type="submit" name="save-attributes" id="save-attributes" class="submit-button" value="' . esc_attr__( 'Create Form', 'simpleform' ) . '">' . wp_nonce_field( 'simpleform_backend_update', 'simpleform_nonce', false, false ) . '</div>';

// Form closing tag.
$newform_page .= '</form>';

// Page wrap: closing tag.
$newform_page .= '</div>';

echo wp_kses( $newform_page, $allowed_tags );
