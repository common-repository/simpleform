<?php
/**
 * File delegated to retrieve the variables used in the public-facing view for the plugin.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/public/partials
 */

defined( 'ABSPATH' ) || exit;

$util = new SimpleForm_Util();

// General variables.
$empty          = '';
$nonce          = wp_nonce_field( 'sform_nonce_action', 'sform_nonce', true, false );
$data           = array(
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
$data           = apply_filters( 'sform_validation', $data );
$form_error     = array_slice( $data, 15, 1 );
$errors_string  = $form_error ? implode( ' ', $form_error ) : '';
$errors_list    = ! empty( $errors_string ) ? array_filter( explode( ';', $errors_string ) ) : array();
$invalid_form   = isset( $errors_list[0] ) ? (int) $errors_list[0] : '';
$form_id        = isset( $atts_array['id'] ) ? (int) $atts_array['id'] : 1;
$errors_counter = $form_id === $invalid_form ? count( $errors_list ) - 1 : 0;
$error_class    = ! empty( $data['error'] ) && ( $form_id === $invalid_form ) ? array_flip( $errors_list ) : array();
$isuffix        = '-' . $form_id;
$chars_length   = $util->get_sform_option( $form_id, 'settings', 'characters_length', false );

// Form properties.
$cform_id         = 'id="form-' . $form_id . '"';
$html5_validation = $util->get_sform_option( $form_id, 'settings', 'html5_validation', false );
$form_attribute   = $html5_validation ? ' novalidate' : '';
$form_direction   = $util->get_sform_option( $form_id, 'attributes', 'form_direction', 'ltr' );
$form_validation  = $html5_validation && empty( $invalid_form ) ? 'needs-validation' : '';
$form_validation .= $form_id === $invalid_form ? 'was-validated' : '';
$focus            = $util->get_sform_option( $form_id, 'settings', 'focus', 'field' );
$form_focus       = 'alert' !== $focus ? ' needs-focus' : '';
$form_template    = $util->get_sform_option( $form_id, 'settings', 'form_template', 'default' );
$form_style       = ' ' . $form_template;
$ajax             = $util->get_sform_option( $form_id, 'settings', 'ajax_submission', false );
$ajax_form        = $ajax ? ' ajax' : '';
$form_class       = 'rtl' === $form_direction ? 'rtl ' . $form_validation . $form_focus . $form_style . $ajax_form : $form_validation . $form_focus . $form_style . $ajax_form;
$attribute_form   = 'form="' . $form_id . '"';

// Required fields.
$required_sign = $util->get_sform_option( $form_id, 'attributes', 'required_sign', true );
$required_word = $util->get_sform_option( $form_id, 'attributes', 'required_word', '' );
$word_position = $util->get_sform_option( $form_id, 'attributes', 'word_position', 'required' );

// Form labels.
$label_position  = $util->get_sform_option( $form_id, 'attributes', 'label_position', 'inline' );
$row_label       = 'inline' === $label_position ? 'row' : '';
$label_size      = $util->get_sform_option( $form_id, 'attributes', 'label_size', 'default' );
$small_label     = 'smaller' === $label_size ? ' smaller' : '';
$large_label     = 'larger' === $label_size ? ' larger' : '';
$label_font_size = $small_label . $large_label;
$label_class     = 'inline' === $label_position ? 'class="sform col-sm-2' . $label_font_size . '"' : 'class="sform ' . $label_font_size . '"';

// Form field variables.
$name_field     = $util->get_sform_option( $form_id, 'attributes', 'name_field', 'visible' );
$lastname_field = $util->get_sform_option( $form_id, 'attributes', 'lastname_field', 'hidden' );
$email_field    = $util->get_sform_option( $form_id, 'attributes', 'email_field', 'visible' );
$phone_field    = $util->get_sform_option( $form_id, 'attributes', 'phone_field', 'hidden' );
$website_field  = $util->get_sform_option( $form_id, 'attributes', 'website_field', 'hidden' );
$subject_field  = $util->get_sform_option( $form_id, 'attributes', 'subject_field', 'visible' );
$consent_field  = $util->get_sform_option( $form_id, 'attributes', 'consent_field', 'visible' );
$captcha_field  = $util->get_sform_option( $form_id, 'attributes', 'captcha_field', 'hidden' );

// Variables related to the alignment of fields.
$lastname_alignment = $util->get_sform_option( $form_id, 'attributes', 'lastname_alignment', 'name' );
$phone_alignment    = $util->get_sform_option( $form_id, 'attributes', 'phone_alignment', 'email' );
$name_row_class     = 'name' === $lastname_alignment && ( 'visible' === $name_field || ( 'registered' === $name_field && is_user_logged_in() ) || ( 'anonymous' === $name_field && ! is_user_logged_in() ) ) && ( 'visible' === $lastname_field || ( 'registered' === $lastname_field && is_user_logged_in() ) || ( 'anonymous' === $lastname_field && ! is_user_logged_in() ) ) && 'inline' !== $label_position ? ' half' : '';
$email_row_class    = 'email' === $phone_alignment && ( 'visible' === $email_field || ( 'registered' === $email_field && is_user_logged_in() ) || ( 'anonymous' === $email_field && ! is_user_logged_in() ) ) && ( 'visible' === $phone_field || ( 'registered' === $phone_field && is_user_logged_in() ) || ( 'anonymous' === $phone_field && ! is_user_logged_in() ) ) && 'inline' !== $label_position ? ' half' : '';
$wrap_class         = 'inline' === $label_position ? 'col-sm-10' : '';
$inline_class       = 'inline' === $label_position ? 'col-sm-10' : '';
$class_direction    = 'rtl' === $form_direction ? 'rtl' : '';

// Variables related to the name field.
$name_label             = $util->get_sform_option( $form_id, 'attributes', 'name_label', __( 'Name', 'simpleform' ) );
$name_requirement       = $util->get_sform_option( $form_id, 'attributes', 'name_requirement', 'required' );
$name_field_requirement = 'required' === $name_requirement ? true : false;
$required_name_sign     = ( ! isset( $error_class['name'] ) && ! isset( $error_class['name_invalid'] ) && ! empty( esc_attr( $data['name'] ) ) ) || 'required' !== $name_requirement || ! $required_sign ? '' : '&lowast;';
$required_name_word     = ( ! isset( $error_class['name'] ) && ! isset( $error_class['name_invalid'] ) && ! empty( esc_attr( $data['name'] ) ) ) || ( ! $required_sign && 'required' === $name_requirement && 'required' !== $word_position ) || ( ! $required_sign && 'required' !== $name_requirement && 'required' === $word_position ) || $required_sign ? '' : $required_word;
$required_name          = ( $required_sign && $name_field_requirement ) || ( ! $required_sign && $name_field_requirement && 'required' === $word_position ) || ( ! $required_sign && ! $name_field_requirement && 'required' !== $word_position ) ? '' : 'd-none';
$required_name_label    = $required_sign ? '<span class="required-symbol mark ' . $required_name . '">' . $required_name_sign . '</span>' : '<span class="required-symbol word ' . $required_name . '">' . $required_name_word . '</span>';
$name_visibility        = $util->get_sform_option( $form_id, 'attributes', 'name_visibility', 'visible' );
$name_field_label       = 'hidden' === $name_visibility ? '' : '<label for="sform-name' . $isuffix . '" ' . $label_class . '>' . $name_label . $required_name_label . '</label>';
$name_focused           = ! $ajax && ! empty( $error_class ) && 'field' === $focus && ( isset( $error_class['name'] ) || isset( $error_class['name_invalid'] ) ) ? ' input-focused' : '';
$name_class             = isset( $error_class['name'] ) || isset( $error_class['name_invalid'] ) ? 'sform-field is-invalid' . $name_focused : 'sform-field' . $name_focused;
$name_value             = absint( $form_id ) === $data['form'] ? esc_attr( $data['name'] ) : '';
$name_placeholder       = $util->get_sform_option( $form_id, 'attributes', 'name_placeholder', '' );
$name_maxlength         = intval( $util->get_sform_option( $form_id, 'attributes', 'name_maxlength', 0 ) );
$name_maxlength         = 0 !== $name_maxlength ? ' maxlength="' . $name_maxlength . '"' : '';
$name_attribute         = 'required' === $name_requirement ? 'required' : '';
$name_attribute        .= $name_maxlength;
$name_attribute        .= ' parent="' . $form_id . '"';
$name_length            = intval( $util->get_sform_option( $form_id, 'attributes', 'name_minlength', 2 ) );
/* translators: %d: minimum length of the name */
$name_numeric_error = strval( $util->get_sform_option( $form_id, 'settings', 'numeric_name', sprintf( __( 'Please enter a name at least %d characters long', 'simpleform' ), $name_length ) ) );
$name_generic_error = strval( $util->get_sform_option( $form_id, 'settings', 'generic_name', __( 'Please type your full name', 'simpleform' ) ) );
$length_name_error  = $chars_length ? $name_numeric_error : $name_generic_error;
$invalid_name_error = $util->get_sform_option( $form_id, 'settings', 'invalid_name', __( 'The name contains invalid characters', 'simpleform' ) );
$empty_name_error   = $util->get_sform_option( $form_id, 'settings', 'empty_name', __( 'Please provide your name', 'simpleform' ) );
$name_field_error   = isset( $error_class['name'] ) ? $length_name_error : '';
$name_field_error  .= isset( $error_class['name_invalid'] ) ? $invalid_name_error : '';
$name_field_error  .= ! isset( $error_class['name'] ) && ! isset( $error_class['name_invalid'] ) ? $empty_name_error : '';

// Variables related to the last name field.
$lastname_label             = $util->get_sform_option( $form_id, 'attributes', 'lastname_label', __( 'Last Name', 'simpleform' ) );
$lastname_requirement       = $util->get_sform_option( $form_id, 'attributes', 'lastname_requirement', 'optional' );
$lastname_field_requirement = 'required' === $lastname_requirement ? true : false;
$required_lastname          = ( $required_sign && $lastname_field_requirement ) || ( ! $required_sign && $lastname_field_requirement && 'required' === $word_position ) || ( ! $required_sign && ! $lastname_field_requirement && 'required' !== $word_position ) ? '' : 'd-none';
$required_lastname_sign     = ( ! isset( $error_class['lastname'] ) && ! isset( $error_class['lastname_invalid'] ) && ! empty( esc_attr( $data['lastname'] ) ) ) || 'required' !== $lastname_requirement || ! $required_sign ? '' : '&lowast;';
$required_lastname_word     = ( ! isset( $error_class['lastname'] ) && ! isset( $error_class['lastname_invalid'] ) && ! empty( esc_attr( $data['lastname'] ) ) ) || ( ! $required_sign && 'required' === $lastname_requirement && 'required' !== $word_position ) || ( ! $required_sign && 'required' !== $lastname_requirement && 'required' === $word_position ) || $required_sign ? '' : $required_word;
$lastname_visibility        = $util->get_sform_option( $form_id, 'attributes', 'lastname_visibility', 'visible' );
$lastname_label_style       = 'name' === $lastname_alignment && 'hidden' === $lastname_visibility && 'hidden' !== $name_visibility ? '<label for="sform-lastname' . $isuffix . '">&nbsp;</label>' : '';
$required_lastname_label    = $required_sign ? '<span class="required-symbol mark ' . $required_lastname . '">' . $required_lastname_sign . '</span>' : '<span class="required-symbol word ' . $required_lastname . '">' . $required_lastname_word . '</span>';
$lastname_field_label       = 'hidden' === $lastname_visibility ? $lastname_label_style : '<label for="sform-lastname' . $isuffix . '" ' . $label_class . '>' . $lastname_label . $required_lastname_label . '</label>';
$lastname_focused           = ! $ajax && ! empty( $error_class ) && 'field' === $focus && ( isset( $error_class['lastname'] ) || isset( $error_class['lastname_invalid'] ) ) ? ' input-focused' : '';
$lastname_class             = isset( $error_class['lastname'] ) || isset( $error_class['lastname_invalid'] ) ? 'sform-field is-invalid' . $lastname_focused : 'sform-field' . $lastname_focused;
$lastname_value             = absint( $form_id ) === $data['form'] ? esc_attr( $data['lastname'] ) : '';
$lastname_placeholder       = $util->get_sform_option( $form_id, 'attributes', 'lastname_placeholder', '' );
$lastname_length            = intval( $util->get_sform_option( $form_id, 'attributes', 'lastname_minlength', 2 ) );
$lastname_maxlength         = intval( $util->get_sform_option( $form_id, 'attributes', 'lastname_maxlength', 0 ) );
$lastname_maxlength         = 0 !== $lastname_maxlength ? ' maxlength="' . $lastname_maxlength . '"' : '';
$lastname_attribute         = 'required' === $lastname_requirement ? 'required' : '';
$lastname_attribute        .= $lastname_maxlength;
$lastname_attribute        .= ' parent="' . $form_id . '"';
/* translators: %d: minimum length of the last name */
$lastname_numeric_error = strval( $util->get_sform_option( $form_id, 'settings', 'numeric_lastname', sprintf( __( 'Please enter a last name at least %d characters long', 'simpleform' ), $lastname_length ) ) );
$lastname_generic_error = strval( $util->get_sform_option( $form_id, 'settings', 'generic_lastname', __( 'Please type your full last name', 'simpleform' ) ) );
$length_lastname_error  = $chars_length ? $lastname_numeric_error : $lastname_generic_error;
$empty_lastname_error   = $util->get_sform_option( $form_id, 'settings', 'empty_lastname', __( 'Please provide your last name', 'simpleform' ) );
$invalid_lastname_error = $util->get_sform_option( $form_id, 'settings', 'invalid_lastname', __( 'The last name contains invalid characters', 'simpleform' ) );
$lastname_field_error   = isset( $error_class['lastname'] ) ? $length_lastname_error : '';
$lastname_field_error  .= isset( $error_class['lastname_invalid'] ) ? $invalid_lastname_error : '';
$lastname_field_error  .= ! isset( $error_class['lastname'] ) && ! isset( $error_class['lastname_invalid'] ) ? $empty_lastname_error : '';

// Variables related to the email field.
$email_label             = $util->get_sform_option( $form_id, 'attributes', 'email_label', __( 'Email', 'simpleform' ) );
$email_requirement       = $util->get_sform_option( $form_id, 'attributes', 'email_requirement', 'required' );
$email_field_requirement = 'required' === $email_requirement ? true : false;
$required_email_word     = ( ! isset( $error_class['email'] ) && ! empty( esc_attr( $data['email'] ) ) ) || ( ! $required_sign && 'required' === $email_requirement && 'required' !== $word_position ) || ( ! $required_sign && 'required' !== $email_requirement && 'required' === $word_position ) || $required_sign ? '' : $required_word;
$required_email          = ( $required_sign && $email_field_requirement ) || ( ! $required_sign && $email_field_requirement && 'required' === $word_position ) || ( ! $required_sign && ! $email_field_requirement && 'required' !== $word_position ) ? '' : 'd-none';
$required_email_sign     = ( ! isset( $error_class['email'] ) && ! empty( esc_attr( $data['email'] ) ) ) || 'required' !== $email_requirement || ! $required_sign ? '' : '&lowast;';
$required_email_label    = $required_sign ? '<span class="required-symbol mark ' . $required_email . '">' . $required_email_sign . '</span>' : '<span class="required-symbol word ' . $required_email . '">' . $required_email_word . '</span>';
$email_visibility        = $util->get_sform_option( $form_id, 'attributes', 'email_visibility', 'visible' );
$email_field_label       = 'hidden' === $email_visibility ? '' : '<label for="sform-email' . $isuffix . '" ' . $label_class . '>' . $email_label . $required_email_label . '</label>';
$email_focused           = ! $ajax && ! empty( $error_class ) && 'field' === $focus && isset( $error_class['email'] ) ? ' input-focused' : '';
$email_class             = isset( $error_class['email'] ) ? 'sform-field is-invalid' . $email_focused : 'sform-field' . $email_focused;
$email_value             = absint( $form_id ) === $data['form'] ? esc_attr( $data['email'] ) : '';
$email_placeholder       = $util->get_sform_option( $form_id, 'attributes', 'email_placeholder', '' );
$email_attribute         = 'required' === $email_requirement ? 'required' : '';
$email_attribute        .= ' parent="' . $form_id . '"';
$empty_email_error       = $util->get_sform_option( $form_id, 'settings', 'empty_email', __( 'Please provide your email address', 'simpleform' ) );
$invalid_email_error     = $util->get_sform_option( $form_id, 'settings', 'invalid_email', __( 'Please enter a valid email address', 'simpleform' ) );
$email_field_error       = isset( $error_class['email'] ) ? $invalid_email_error : '';
$email_field_error      .= ! isset( $error_class['email'] ) ? $empty_email_error : '';

// Variables related to the phone field.
$phone_visibility        = $util->get_sform_option( $form_id, 'attributes', 'phone_visibility', 'visible' );
$phone_label_style       = 'email' === $phone_alignment && 'hidden' === $phone_visibility && 'hidden' !== $email_visibility ? '<label for="sform-phone' . $isuffix . '">&nbsp;</label>' : '';
$phone_label             = $util->get_sform_option( $form_id, 'attributes', 'phone_label', __( 'Phone', 'simpleform' ) );
$phone_requirement       = $util->get_sform_option( $form_id, 'attributes', 'phone_requirement', 'optional' );
$phone_field_requirement = 'required' === $phone_requirement ? true : false;
$required_phone          = ( $required_sign && $phone_field_requirement ) || ( ! $required_sign && $phone_field_requirement && 'required' === $word_position ) || ( ! $required_sign && ! $phone_field_requirement && 'required' !== $word_position ) ? '' : 'd-none';
$required_phone_sign     = ( ! isset( $error_class['phone'] ) && ! isset( $error_class['phone_invalid'] ) && ! empty( esc_attr( $data['phone'] ) ) ) || 'required' !== $phone_requirement || ! $required_sign ? '' : '&lowast;';
$required_phone_word     = ( ! isset( $error_class['phone'] ) && ! isset( $error_class['phone_invalid'] ) && ! empty( esc_attr( $data['phone'] ) ) ) || ( ! $required_sign && 'required' === $phone_requirement && 'required' !== $word_position ) || ( ! $required_sign && 'required' !== $phone_requirement && 'required' === $word_position ) || $required_sign ? '' : $required_word;
$required_phone_label    = $required_sign ? '<span class="required-symbol mark ' . $required_phone . '">' . $required_phone_sign . '</span>' : '<span class="required-symbol word ' . $required_phone . '">' . $required_phone_word . '</span>';
$phone_field_label       = 'hidden' === $phone_visibility ? $phone_label_style : '<label for="sform-phone' . $isuffix . '" ' . $label_class . '>' . $phone_label . $required_phone_label . '</label>';
$phone_focused           = ! $ajax && ! empty( $error_class ) && 'field' === $focus && ( isset( $error_class['phone'] ) || isset( $error_class['phone_invalid'] ) ) ? ' input-focused' : '';
$phone_class             = isset( $error_class['phone'] ) || isset( $error_class['phone_invalid'] ) ? 'sform-field is-invalid' . $phone_focused : 'sform-field' . $phone_focused;
$phone_value             = absint( $form_id ) === $data['form'] ? esc_attr( $data['phone'] ) : '';
$phone_placeholder       = $util->get_sform_option( $form_id, 'attributes', 'phone_placeholder', '' );
$phone_attribute         = 'required' === $phone_requirement ? 'required' : '';
$phone_attribute        .= ' parent="' . $form_id . '"';
$empty_phone_error       = $util->get_sform_option( $form_id, 'settings', 'empty_phone', __( 'Please provide your phone number', 'simpleform' ) );
$invalid_phone_error     = $util->get_sform_option( $form_id, 'settings', 'invalid_phone', __( 'The phone number contains invalid characters', 'simpleform' ) );
$phone_field_error       = isset( $error_class['phone'] ) ? $empty_phone_error : '';
$phone_field_error      .= isset( $error_class['phone_invalid'] ) ? $invalid_phone_error : '';
$phone_field_error      .= ! isset( $error_class['phone'] ) && ! isset( $error_class['phone_invalid'] ) ? $empty_phone_error : '';

// Variables related to the website field.
$website_visibility        = $util->get_sform_option( $form_id, 'attributes', 'website_visibility', 'visible' );
$website_label_style       = $website_visibility && 'hidden' === $website_visibility ? '<label for="sform-website' . $isuffix . '">&nbsp;</label>' : '';
$website_label             = $util->get_sform_option( $form_id, 'attributes', 'website_label', __( 'Website', 'simpleform' ) );
$website_requirement       = $util->get_sform_option( $form_id, 'attributes', 'website_requirement', 'optional' );
$website_field_requirement = 'required' === $website_requirement ? true : false;
$required_website          = ( $required_sign && $website_field_requirement ) || ( ! $required_sign && $website_field_requirement && 'required' === $word_position ) || ( ! $required_sign && ! $website_field_requirement && 'required' !== $word_position ) ? '' : 'd-none';
$required_website_sign     = ( ! isset( $error_class['website'] ) && ! empty( esc_attr( $data['website'] ) ) ) || 'required' !== $website_requirement || ! $required_sign ? '' : '&lowast;';
$required_website_word     = ( ! isset( $error_class['website'] ) && ! empty( esc_attr( $data['website'] ) ) ) || ( ! $required_sign && 'required' === $website_requirement && 'required' !== $word_position ) || ( ! $required_sign && 'required' !== $website_requirement && 'required' === $word_position ) || $required_sign ? '' : $required_word;
$required_website_label    = $required_sign ? '<span class="required-symbol mark ' . $required_website . '">' . $required_website_sign . '</span>' : '<span class="required-symbol word ' . $required_website . '">' . $required_website_word . '</span>';
$website_field_label       = 'hidden' === $website_visibility ? $website_label_style : '<label for="sform-website' . $isuffix . '" ' . $label_class . '>' . $website_label . $required_website_label . '</label>';
$website_focused           = ! $ajax && ! empty( $error_class ) && 'field' === $focus && isset( $error_class['website'] ) ? ' input-focused' : '';
$website_class             = isset( $error_class['website'] ) ? 'sform-field is-invalid' . $website_focused : 'sform-field' . $website_focused;
$website_value             = absint( $form_id ) === $data['form'] ? esc_attr( $data['website'] ) : '';
$website_placeholder       = $util->get_sform_option( $form_id, 'attributes', 'website_placeholder', '' );
$website_attribute         = 'required' === $website_requirement ? 'required' : '';
$website_attribute        .= ' parent="' . $form_id . '"';
$invalid_website_error     = $util->get_sform_option( $form_id, 'settings', 'invalid_website', __( 'Please enter a valid URL', 'simpleform' ) );
$website_field_error       = isset( $error_class['website'] ) ? $invalid_website_error : '';

// Variables related to the subject field.
$subject_label             = $util->get_sform_option( $form_id, 'attributes', 'subject_label', __( 'Subject', 'simpleform' ) );
$subject_requirement       = $util->get_sform_option( $form_id, 'attributes', 'subject_requirement', 'required' );
$subject_field_requirement = 'required' === $subject_requirement ? true : false;
$required_subject          = ( $required_sign && $subject_field_requirement ) || ( ! $required_sign && $subject_field_requirement && 'required' === $word_position ) || ( ! $required_sign && ! $subject_field_requirement && 'required' !== $word_position ) ? '' : 'd-none';
$required_subject_word     = ( ( ! isset( $error_class['subject'] ) && ! isset( $error_class['subject_invalid'] ) && ! empty( esc_attr( $data['subject'] ) ) ) ) || ( 'required' === $subject_requirement && 'required' !== $word_position ) || ( ! $required_sign && 'required' !== $subject_requirement && 'required' === $word_position ) || ( ! $required_sign && 'required' === $subject_requirement && 'required' !== $word_position ) || $required_sign ? '' : $required_word;
$required_subject_sign     = ( ! isset( $error_class['subject'] ) && ! isset( $error_class['subject_invalid'] ) && ! empty( esc_attr( $data['subject'] ) ) ) || 'required' !== $subject_requirement || ! $required_sign ? '' : '&lowast;';
$required_subject_label    = $required_sign ? '<span class="required-symbol mark ' . $required_subject . '">' . $required_subject_sign . '</span>' : '<span class="required-symbol word ' . $required_subject . '">' . $required_subject_word . '</span>';
$subject_visibility        = $util->get_sform_option( $form_id, 'attributes', 'subject_visibility', 'visible' );
$subject_field_label       = 'hidden' === $subject_visibility ? '' : '<label for="sform-subject' . $isuffix . '" ' . $label_class . '>' . $subject_label . $required_subject_label . '</label>';
$subject_focused           = ! $ajax && ! empty( $error_class ) && 'field' === $focus && ( isset( $error_class['subject'] ) || isset( $error_class['subject_invalid'] ) ) ? ' input-focused' : '';
$subject_class             = isset( $error_class['subject'] ) || isset( $error_class['subject_invalid'] ) ? 'sform-field is-invalid' . $subject_focused : 'sform-field' . $subject_focused;
$subject_maxlength         = intval( $util->get_sform_option( $form_id, 'attributes', 'subject_maxlength', 0 ) );
$subject_maxlength         = 0 !== $subject_maxlength ? ' maxlength="' . $subject_maxlength . '"' : '';
$subject_attribute         = 'required' === $subject_requirement ? 'required' : '';
$subject_attribute        .= $subject_maxlength;
$subject_attribute        .= ' parent="' . $form_id . '"';
$subject_value             = absint( $form_id ) === $data['form'] ? esc_attr( $data['subject'] ) : '';
$subject_placeholder       = $util->get_sform_option( $form_id, 'attributes', 'subject_placeholder', '' );
$subject_length            = intval( $util->get_sform_option( $form_id, 'attributes', 'subject_length', 5 ) );
/* translators: %d: minimum length of the subject */
$subject_numeric_error = strval( $util->get_sform_option( $form_id, 'settings', 'numeric_subject', sprintf( __( 'Please enter a subject at least %d characters long', 'simpleform' ), $subject_length ) ) );
$subject_generic_error = strval( $util->get_sform_option( $form_id, 'settings', 'generic_subject', __( 'Please type a short and specific subject', 'simpleform' ) ) );
$length_subject_error  = $chars_length ? $subject_numeric_error : $subject_generic_error;
$invalid_subject_error = $util->get_sform_option( $form_id, 'settings', 'invalid_subject', __( 'Enter only alphanumeric characters and punctuation marks', 'simpleform' ) );
$subject_field_error   = isset( $error_class['subject'] ) ? $length_subject_error : '';
$subject_field_error  .= isset( $error_class['subject_invalid'] ) ? $invalid_subject_error : '';
$empty_subject_error   = $util->get_sform_option( $form_id, 'settings', 'empty_subject', __( 'Please enter the request subject', 'simpleform' ) );
$subject_field_error  .= ! isset( $error_class['subject'] ) && ! isset( $error_class['subject_invalid'] ) ? $empty_subject_error : '';

// Variables related to the message field.
$message_label          = $util->get_sform_option( $form_id, 'attributes', 'message_label', __( 'Message', 'simpleform' ) );
$required_message_sign  = ! isset( $error_class['message'] ) && ! isset( $error_class['message_invalid'] ) && ( ! empty( esc_attr( $data['message'] ) ) || ! $required_sign ) ? '' : '&lowast;';
$required_message_word  = ( ! isset( $error_class['message'] ) && ! isset( $error_class['message_invalid'] ) && ! empty( esc_attr( $data['message'] ) ) ) || ( ! $required_sign && 'required' !== $word_position ) || $required_sign ? '' : $required_word;
$required_message_label = $required_sign ? '<span class="required-symbol mark">' . $required_message_sign . '</span>' : '<span class="required-symbol word">' . $required_message_word . '</span>';
$message_visibility     = $util->get_sform_option( $form_id, 'attributes', 'message_visibility', 'visible' );
$message_field_label    = 'hidden' === $message_visibility ? '' : '<label for="sform-message' . $isuffix . '" ' . $label_class . '>' . $message_label . $required_message_label . '</label>';
$message_focused        = ! $ajax && ! empty( $error_class ) && 'field' === $focus && ( isset( $error_class['message'] ) || isset( $error_class['message_invalid'] ) ) ? ' input-focused' : '';
$message_class          = isset( $error_class['message'] ) || isset( $error_class['message_invalid'] ) ? 'sform-field is-invalid' . $message_focused : 'sform-field' . $message_focused;
$message_maxlength      = intval( $util->get_sform_option( $form_id, 'attributes', 'message_maxlength', 0 ) );
$message_maxlength      = 0 !== $message_maxlength ? 'maxlength="' . $message_maxlength . '"' : '';
$message_placeholder    = $util->get_sform_option( $form_id, 'attributes', 'message_placeholder', '' );
$message_attribute      = 'parent="' . $form_id . '"';
$message_value          = absint( $form_id ) === $data['form'] ? esc_textarea( $data['message'] ) : '';
$message_length         = intval( $util->get_sform_option( $form_id, 'attributes', 'message_length', 10 ) );
/* translators: %d: minimum length of the message */
$message_numeric_error = strval( $util->get_sform_option( $form_id, 'settings', 'numeric_message', sprintf( __( 'Please enter a message at least %d characters long', 'simpleform' ), $message_length ) ) );
$message_generic_error = strval( $util->get_sform_option( $form_id, 'settings', 'generic_message', __( 'Please type a clearer message so we can respond appropriately', 'simpleform' ) ) );
$length_message_error  = $chars_length ? $message_numeric_error : $message_generic_error;
$invalid_message_error = $util->get_sform_option( $form_id, 'settings', 'invalid_message', __( 'Enter only alphanumeric characters and punctuation marks', 'simpleform' ) );
$empty_message_error   = $util->get_sform_option( $form_id, 'settings', 'empty_message', __( 'Please enter your message', 'simpleform' ) );
$message_field_error   = isset( $error_class['message'] ) ? $length_message_error : '';
$message_field_error  .= isset( $error_class['message_invalid'] ) ? $invalid_message_error : '';
$message_field_error  .= ! isset( $error_class['message'] ) && ! isset( $error_class['message_invalid'] ) ? $empty_message_error : '';

// Variables related to the consent field.
$consent_class        = isset( $error_class['consent'] ) ? 'sform checkbox is-invalid' : 'sform checkbox';
$consent_field_class  = 'sform-field ' . $consent_class;
$consent_value        = ! empty( esc_attr( $data['consent'] ) ) && ( absint( $form_id ) === $data['form'] ) ? esc_attr( $data['consent'] ) : false;
$consent_requirement  = $util->get_sform_option( $form_id, 'attributes', 'consent_requirement', 'required' );
$consent_attribute    = checked( $consent_value, true, false );
$consent_attribute   .= $consent_requirement;
$consent_attribute   .= ' parent="' . $form_id . '"';
$consent_label        = $util->get_sform_option( $form_id, 'attributes', 'consent_label', __( 'I have read and consent to the privacy policy', 'simpleform' ) );
$checkbox_requirement = $util->get_sform_option( $form_id, 'attributes', 'consent_requirement', 'required' );
$required_consent     = ( ! isset( $error_class['consent'] ) && esc_attr( $data['consent'] ) ) || ! $required_sign || 'required' !== $checkbox_requirement ? 'd-none' : '';
$consent_sign         = '<span class="required-symbol mark ' . $required_consent . '">&lowast;</span>';

// Variables related to the captcha field.
$captcha_label         = $util->get_sform_option( $form_id, 'attributes', 'captcha_label', __( 'I\'m not a robot', 'simpleform' ) );
$required_captcha_sign = ( ! isset( $error_class['captcha'] ) && ! empty( esc_attr( $data['captcha'] ) ) ) || ! $required_sign ? '' : '&lowast;';
$captcha_sign          = '<span class="required-symbol mark">' . $required_captcha_sign . '</span>';
$captcha_class         = isset( $error_class['captcha'] ) ? 'captcha  is-invalid' : 'captcha ';
$math_one              = ! isset( $error_class['captcha'] ) && esc_attr( $data['captcha_one'] ) > 0 && ! empty( esc_attr( $data['captcha'] ) ) ? esc_attr( $data['captcha_one'] ) : wp_rand( 10, 99 );
$math_two              = ! isset( $error_class['captcha'] ) && esc_attr( $data['captcha_two'] ) > 0 && ! empty( esc_attr( $data['captcha'] ) ) ? esc_attr( $data['captcha_two'] ) : wp_rand( 1, 9 );
$captcha_hidden        = '<input id="captcha_one-' . $form_id . '" type="hidden" name="captcha_one" value="' . $math_one . '"/><input id="captcha_two-' . $form_id . '" type="hidden" name="captcha_two" value="' . $math_two . '"/>';
$captcha_question      = $math_one . '&nbsp;&nbsp;+&nbsp;&nbsp;' . $math_two . '&nbsp;&nbsp;=';
$captcha_focused       = ! $ajax && ! empty( $error_class ) && 'field' === $focus && isset( $error_class['captcha'] ) ? ' input-focused' : '';
$captcha_answer_class  = 'sform-field ' . $captcha_class . $captcha_focused;
$captcha_attribute     = ' min="11" max="108" data-maxlength="3" oninput="this.value=this.value.slice(0,this.dataset.maxlength)" parent="' . $form_id . '" required';
$captcha_attribute    .= ' parent="' . $form_id . '"';
$captcha_value         = absint( $form_id ) === $data['form'] && ! isset( $error_class['captcha'] ) && ! empty( esc_attr( $data['captcha'] ) ) ? esc_attr( $data['captcha'] ) : '';
$captcha_error_class   = isset( $error_class['captcha'] ) ? 'd-block' : '';
$error_captcha_label   = $util->get_sform_option( $form_id, 'settings', 'invalid_captcha', __( 'Please enter a valid captcha value', 'simpleform' ) );
$empty_captcha         = $util->get_sform_option( $form_id, 'settings', 'empty_captcha', __( 'Please enter an answer', 'simpleform' ) );
$captcha_field_error   = isset( $error_class['captcha'] ) ? $error_captcha_label : '';
$captcha_field_error  .= ! isset( $error_class['captcha'] ) ? $empty_captcha : '';

// Hidden fields.
$form_input    = '<input type="hidden" id="form-id-' . $form_id . '" name="form-id" value="' . $form_id . '" />';
$empty_fields  = $util->get_sform_option( $form_id, 'settings', 'empty_fields', __( 'There were some errors that need to be fixed', 'simpleform' ) );
$hidden_fields = '<div class="d-none">' . $nonce . $form_input . '<input type="hidden" name="multiple-errors" value="' . $empty_fields . '" /></div><div class="carrots"><label for="url-site' . $isuffix . '">Fake Url</label><input type="text" id="url' . $isuffix . '" name="url-site" value="" tabindex="-1" autocomplete="off" /><br><label for="hobbies' . $isuffix . '">Fake Hobbies</label><input type="text" id="hobbies' . $isuffix . '" name="hobbies" value="" tabindex="-1" autocomplete="off" /><br><input type="checkbox" id="contact-phone' . $isuffix . '" name="contact-phone" value="false" tabindex="-1" autocomplete="off"><label for="contact-phone">Contact me by telephone</label></div>';

// Form error message.
$outside_error         = $util->get_sform_option( $form_id, 'settings', 'outside_error', 'bottom' );
$alert_class           = ! empty( $error_class ) ? 'v-visible' : '';
$name_error            = $util->get_sform_option( $form_id, 'settings', 'name_error', __( 'Error occurred validating the name', 'simpleform' ) );
$lastname_error        = $util->get_sform_option( $form_id, 'settings', 'lastname_error', __( 'Error occurred validating the last name', 'simpleform' ) );
$email_error           = $util->get_sform_option( $form_id, 'settings', 'email_error', __( 'Error occurred validating the email', 'simpleform' ) );
$phone_error           = $util->get_sform_option( $form_id, 'settings', 'phone_error', __( 'Error occurred validating the phone number', 'simpleform' ) );
$website_error         = $util->get_sform_option( $form_id, 'settings', 'website_error', __( 'Error occurred validating the URL', 'simpleform' ) );
$subject_error         = $util->get_sform_option( $form_id, 'settings', 'subject_error', __( 'Error occurred validating the subject', 'simpleform' ) );
$message_error         = $util->get_sform_option( $form_id, 'settings', 'message_error', __( 'Error occurred validating the message', 'simpleform' ) );
$consent_error         = $util->get_sform_option( $form_id, 'settings', 'consent_error', __( 'Please accept our privacy policy before submitting form', 'simpleform' ) );
$captcha_error         = $util->get_sform_option( $form_id, 'settings', 'captcha_error', __( 'Error occurred validating the captcha', 'simpleform' ) );
$duplicate_error       = $util->get_sform_option( 1, 'settings', 'duplicate_error', __( 'The form has already been submitted. Thanks!', 'simpleform' ) );
$honeypot_error        = $util->get_sform_option( 1, 'settings', 'honeypot_error', __( 'Failed honeypot validation', 'simpleform' ) );
$akismet_error         = apply_filters( 'akismet_error_detection', '', $error_class );
$recaptcha_error       = apply_filters( 'recaptcha_error_detection', '', $error_class );
$server_error          = $util->get_sform_option( 1, 'settings', 'server_error', __( 'Error occurred during processing data. Please try again!', 'simpleform' ) );
$error_fields_message  = ( ( isset( $error_class['name'] ) || isset( $error_class['name_invalid'] ) ) && 'none' !== $outside_error && $errors_counter < 2 ? $name_error : '' ) . '' . ( ( isset( $error_class['lastname'] ) || isset( $error_class['lastname_invalid'] ) ) && 'none' !== $outside_error && $errors_counter < 2 ? $lastname_error : '' ) . '' . ( isset( $error_class['email'] ) && 'none' !== $outside_error && $errors_counter < 2 ? $email_error : '' ) . '' . ( ( isset( $error_class['phone'] ) || isset( $error_class['phone_invalid'] ) ) && 'none' !== $outside_error && $errors_counter < 2 ? $phone_error : '' ) . '' . ( isset( $error_class['website'] ) && 'none' !== $outside_error && $errors_counter < 2 ? $website_error : '' ) . '' . ( ( isset( $error_class['subject'] ) || isset( $error_class['subject_invalid'] ) ) && 'none' !== $outside_error && $errors_counter < 2 ? $subject_error : '' ) . '' . ( ( isset( $error_class['message'] ) || isset( $error_class['message_invalid'] ) ) && 'none' !== $outside_error && $errors_counter < 2 ? $message_error : '' ) . '' . ( isset( $error_class['consent'] ) && 'none' !== $outside_error && $errors_counter < 2 ? $consent_error : '' ) . '' . ( isset( $error_class['captcha'] ) && 'none' !== $outside_error && $errors_counter < 2 ? $captcha_error : '' ) . '' . ( isset( $error_class['duplicate_form'] ) ? $duplicate_error : '' ) . ( isset( $error_class['form_honeypot'] ) && ! isset( $error_class['duplicate_form'] ) ? $honeypot_error : '' ) . $akismet_error . $recaptcha_error . ( isset( $error_class['server_error'] ) && $errors_counter < 2 ? $server_error : '' );
$error_fields_message .= empty( $error_class ) || ( $errors_counter > 1 && ! isset( $error_class['form_honeypot'] ) && ! isset( $error_class['duplicate_form'] ) && empty( $akismet_error ) && empty( $recaptcha_error ) && ! isset( $error_class['server_error'] ) ) ? $empty_fields : '&nbsp;';
$field_focused         = ! $ajax && ! empty( $error_class ) && 'field' === $focus && ! isset( $error_class['form_honeypot'] ) && ! isset( $error_class['server_error'] ) && ! isset( $error_class['duplicate_form'] ) && empty( $akismet_error ) && empty( $recaptcha_error ) ? ' input-focused' : '';
$outside_focused       = ! $ajax && ( ( ! empty( $error_class ) && 'alert' === $focus ) || isset( $error_class['server_error'] ) || isset( $error_class['form_honeypot'] ) || isset( $error_class['duplicate_form'] ) || ! empty( $akismet_error ) || ! empty( $recaptcha_error ) ) ? ' outside-focused' : '';
$noscript              = ! empty( $error_class ) ? '' : '<noscript><div class="noscript"><span id="error-message-' . $form_id . '" class="message v-visible">' . __( 'This form needs JavaScript activated to work properly. Please activate it. Thanks!', 'simpleform' ) . '</span></div></noscript>';
$top_error             = 'top' === $outside_error ? '<div id="errors-' . $form_id . '" tabindex="-1" class="msgoutside top ' . $outside_focused . '"><span id="error-message-' . $form_id . '" class="message ' . $alert_class . '">' . $error_fields_message . '</span>' . $noscript . '</div>' : '';
$bottom_class          = 'none' === $outside_error && ! isset( $error_class['form_honeypot'] ) && ! isset( $error_class['duplicate_form'] ) && empty( $akismet_error ) && empty( $recaptcha_error ) && ! isset( $error_class['server_error'] ) ? 'v-invisible ' . $alert_class : $alert_class;
$bottom_error          = 'top' !== $outside_error ? '<div id="errors-' . $form_id . '" tabindex="-1" class="msgoutside ' . $wrap_class . $outside_focused . '"><span id="error-message-' . $form_id . '" class="message ' . $bottom_class . '">' . $error_fields_message . '</span>' . $noscript . '</div>' : '';

// Variables related to the submit button.
$submit_label    = $util->get_sform_option( $form_id, 'attributes', 'submit_label', __( 'Submit', 'simpleform' ) );
$submit_position = $util->get_sform_option( $form_id, 'attributes', 'submit_position', 'centred' );
switch ( $submit_position ) {
	case 'left':
		$submit_class  = 'submit-wrap left ' . $wrap_class;
		$spinner_class = 'sform spinner left';
		$button_class  = 'sform';
		break;
	case 'right':
		$submit_class  = 'submit-wrap right ' . $wrap_class;
		$spinner_class = 'sform spinner right';
		$button_class  = 'sform';
		break;
	case 'full':
		$submit_class  = 'submit-wrap full ' . $wrap_class;
		$spinner_class = 'sform spinner center';
		$button_class  = 'sform fullwidth';
		break;
	default:
		$submit_class  = 'submit-wrap center ' . $wrap_class;
		$spinner_class = 'sform spinner center';
		$button_class  = 'sform';
}
$button_class .= ' ' . $label_font_size;
$spinner       = $util->get_sform_option( $form_id, 'settings', 'spinner', false );
$animation     = $ajax && $spinner ? '<div id="spinner-' . $form_id . '" class="d-none ' . $spinner_class . '"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div><div class="bounce4"></div><div class="bounce5"></div></div>' : '';

// Success message.
$success_img     = esc_url( plugin_dir_url( __DIR__ ) ) . 'img/confirmation.png';
$thank_string1   = __( 'We have received your request!', 'simpleform' );
$thank_string2   = __( 'Your message will be reviewed soon, and we\'ll get back to you as quickly as possible. ', 'simpleform' );
$success_message = '<div id="success-message-' . $form_id . '" class="form confirmation" tabindex="-1"><h4>' . $thank_string1 . '</h4><br>' . $thank_string2 . '</br><img src="' . $success_img . '" alt="message received"></div>';

// Always make sure the form is displayed when the block editor is loaded.
$is_gb_editor   = defined( 'REST_REQUEST' ) && REST_REQUEST;
$name_field     = 'anonymous' === $name_field && $is_gb_editor ? 'visible' : $name_field;
$lastname_field = 'anonymous' === $lastname_field && $is_gb_editor ? 'visible' : $lastname_field;
$email_field    = 'anonymous' === $email_field && $is_gb_editor ? 'visible' : $email_field;
$phone_field    = 'anonymous' === $phone_field && $is_gb_editor ? 'visible' : $phone_field;
$website_field  = 'anonymous' === $website_field && $is_gb_editor ? 'visible' : $website_field;
$subject_field  = 'anonymous' === $subject_field && $is_gb_editor ? 'visible' : $subject_field;
$consent_field  = 'anonymous' === $consent_field && $is_gb_editor ? 'visible' : $consent_field;
$captcha_field  = 'anonymous' === $captcha_field && $is_gb_editor ? 'visible' : $captcha_field;
