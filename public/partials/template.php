<?php
/**
 * File delegated to provide a public-facing view for the plugin.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/public/partials
 *
 * @var string   $isuffix              The identifier used for the form.
 * @var string   $cform_id             The id attribute for the form.
 * @var string   $form_attribute       The novalidate attribute used if form-data must not be validated when submitted.
 * @var string   $form_class           The class attributes for the form.
 * @var string   $attribute_form       The form attribute for the form.
 * @var string   $top_error            The container to display the error message above the form.
 * @var string   $wrap_class           The class attributes for an input container
 * @var string   $name_field           The visibility of the name field.
 * @var string   $name_row_class       The class attributes for the name and lastname fields container.
 * @var string   $name_field_label     The text label container for the name input.
 * @var string   $name_class           The class attributes for the name input.
 * @var string   $name_value           The value entered in the name input field.
 * @var string   $name_placeholder     The placeholder for the name empty field.
 * @var string   $name_attribute       The attributes applied to the name input.
 * @var string   $name_field_error     The error message to display under the field in case of error in the name field.
 * @var string   $lastname_field       The visibility of the lastname field.
 * @var string   $lastname_field_label The text label container for the lastname input.
 * @var string   $lastname_class       The class attributes for the lastname input.
 * @var string   $lastname_value       The value entered in the lastname input field.
 * @var string   $lastname_placeholder The placeholder for the lastname empty field.
 * @var string   $lastname_attribute   The attributes applied to the lastname input.
 * @var string   $lastname_field_error The error message to display under the field in case of error in the lastname field.
 * @var string   $email_field          The visibility of the email field.
 * @var string   $email_row_class      The class attributes for the email and phone fields container.
 * @var string   $email_field_label    The text label container for the email input.
 * @var string   $email_class          The class attributes for the email input.
 * @var string   $email_value          The value entered in the email input field.
 * @var string   $email_placeholder    The placeholder for the email empty field.
 * @var string   $email_attribute      The attributes applied to the email input.
 * @var string   $email_field_error    The error message to display under the field in case of error in the email field.
 * @var string   $phone_field          The visibility of the phone field.
 * @var string   $phone_field_label    The text label container for the phone input.
 * @var string   $phone_class          The class attributes for the phone input.
 * @var string   $phone_value          The value entered in the phone input field.
 * @var string   $phone_placeholder    The placeholder for the phone empty field.
 * @var string   $phone_attribute      The attributes applied to the phone input.
 * @var string   $phone_field_error    The error message to display under the field in case of error in the phone field.
 * @var string   $website_field        The visibility of the website field.
 * @var string   $website_field_label  The text label container for the website input.
 * @var string   $website_class        The class attributes for the website input.
 * @var string   $website_value        The value entered in the website input field.
 * @var string   $website_placeholder  The placeholder for the website empty field.
 * @var string   $website_attribute    The attributes applied to the website input.
 * @var string   $website_field_error  The error message to display under the field in case of error in the website field.
 * @var string   $row_label            The class attribute for the remaining fields container.
 * @var string   $subject_field        The visibility of the subject field.
 * @var string   $subject_field_label  The text label container for the subject input.
 * @var string   $subject_class        The class attributes for the subject input.
 * @var string   $subject_value        The value entered in the subject input field.
 * @var string   $subject_placeholder  The placeholder for the subject empty field.
 * @var string   $subject_attribute    The attributes applied to the subject input.
 * @var string   $subject_field_error  The error message to display under the field in case of error in the subject field.
 * @var string   $message_field_label  The text label container for the message input.
 * @var string   $message_class        The class attributes for the message input.
 * @var string   $message_maxlength    The maximum number of characters allowed in the message input field.
 * @var string   $message_value        The value entered in the message input field.
 * @var string   $message_placeholder  The placeholder for the message empty field.
 * @var string   $message_attribute    The attributes applied to the message input.
 * @var string   $message_field_error  The error message to display under the field in case of error in the message field.
 * @var string   $consent_field        The visibility of the consent field.
 * @var string   $consent_field_class  The class attributes for the consent input.
 * @var string   $consent_value        The value entered in the consent input field.
 * @var string   $consent_attribute    The attributes applied to the consent input.
 * @var string   $consent_class        The class attributes for the consent input label.
 * @var string   $label_font_size      The class attributes for the label font size.
 * @var string   $consent_label        The text label for the consent input.
 * @var string   $consent_sign         The required sign applied to the consent input.
 * @var string   $captcha_field        The visibility of the captcha field.
 * @var string   $label_class          The class attributes applied to the captcha input label.
 * @var string   $captcha_label        The text label for the captcha input.
 * @var string   $captcha_sign         The required sign applied to the captcha input.
 * @var string   $captcha_class        The class attributes for the captcha input container.
 * @var string   $captcha_hidden       The hidden inputs used to verify the captcha value.
 * @var string   $captcha_question     The randomly generated question that appears in the captcha field.
 * @var string   $captcha_answer_class The class attributes for the captcha input.
 * @var string   $captcha_attribute    The attributes applied to the captcha input.
 * @var string   $captcha_value        The value entered in the captcha input field.
 * @var string   $captcha_error_class  The class attributes for display the captcha error message.
 * @var string   $captcha_field_error  The error message to display under the field in case of error in the captcha field.
 * @var mixed[]  $attributes           Array of form attributes.
 * @var mixed[]  $settings             Array of form settings.
 * @var mixed[]  $data                 The submitted data of the form.
 * @var string[] $error_class          The errors found during the form validation.
 * @var string   $hidden_fields        The hidden fields included in the form.
 * @var string   $bottom_error         The container to display the error message below the form.
 * @var string   $submit_class         The class attributes for the submit button container.
 * @var string   $button_class         The class attributes for the submit button.
 * @var string   $submit_label         The text label for the submit button.
 * @var string   $animation            The container for the submit button loading animation.
 * @var int      $form_id              The ID of the form.
 * @var string   $success_message      The container to display the success message.
 */

defined( 'ABSPATH' ) || exit;

// Confirmation message after ajax submission.
$form = '<div id="sform-confirmation' . $isuffix . '" class="sform-confirmation" tabindex="-1"></div>';

// Contact Form starts here!
$form .= '<form ' . $cform_id . ' method="post" ' . $form_attribute . ' class="sform d-block ' . $form_class . '" ' . $attribute_form . '>';

// Contact Form top error message.
$form .= $top_error;

// Name field.
$name_input = 'visible' === $name_field || ( 'registered' === $name_field && is_user_logged_in() ) || ( 'anonymous' === $name_field && ! is_user_logged_in() ) ? '<div class="sform-field-group name ' . $row_label . $name_row_class . '">' . $name_field_label . '<div class="' . $wrap_class . '"><input type="text" name="sform-name" id="sform-name' . $isuffix . '" class="' . $name_class . '" value="' . $name_value . '" placeholder="' . $name_placeholder . '" ' . $name_attribute . '><div id="name-error' . $isuffix . '" class="error-des"><span>' . $name_field_error . '</span></div></div></div>' : '';

// Last Name field.
$lastname_input = 'visible' === $lastname_field || ( 'registered' === $lastname_field && is_user_logged_in() ) || ( 'anonymous' === $lastname_field && ! is_user_logged_in() ) ? '<div class="sform-field-group lastname ' . $row_label . $name_row_class . '">' . $lastname_field_label . '<div class="' . $wrap_class . '"><input type="text" name="sform-lastname" id="sform-lastname' . $isuffix . '" class="' . $lastname_class . '" value="' . $lastname_value . '" placeholder="' . $lastname_placeholder . '" ' . $lastname_attribute . '><div id="lastname-error' . $isuffix . '" class="error-des"><span>' . $lastname_field_error . '</span></div></div></div>' : '';

// Email field.
$email_input = 'visible' === $email_field || ( 'registered' === $email_field && is_user_logged_in() ) || ( 'anonymous' === $email_field && ! is_user_logged_in() ) ? '<div class="sform-field-group email ' . $row_label . $email_row_class . '">' . $email_field_label . '<div class="' . $wrap_class . '"><input type="email" name="sform-email" id="sform-email' . $isuffix . '" class="' . $email_class . '" value="' . $email_value . '" placeholder="' . $email_placeholder . '" ' . $email_attribute . ' ><div id="email-error' . $isuffix . '" class="error-des"><span>' . $email_field_error . '</span></div></div></div>' : '';

// Phone field.
$phone_input = 'visible' === $phone_field || ( 'registered' === $phone_field && is_user_logged_in() ) || ( 'anonymous' === $phone_field && ! is_user_logged_in() ) ? '<div class="sform-field-group phone ' . $row_label . $email_row_class . '">' . $phone_field_label . '<div class="' . $wrap_class . '"><input type="tel" name="sform-phone" id="sform-phone' . $isuffix . '" class="' . $phone_class . '" value="' . $phone_value . '" placeholder="' . $phone_placeholder . '" ' . $phone_attribute . '><div id="phone-error' . $isuffix . '" class="error-des"><span>' . $phone_field_error . '</span></div></div></div>' : '';

// Website field.
$website_input = 'visible' === $website_field || ( 'registered' === $website_field && is_user_logged_in() ) || ( 'anonymous' === $website_field && ! is_user_logged_in() ) ? '<div class="sform-field-group ' . $row_label . '">' . $website_field_label . '<div class="' . $wrap_class . '"><input type="tel" name="sform-website" id="sform-website' . $isuffix . '" class="' . $website_class . '" value="' . $website_value . '" placeholder="' . $website_placeholder . '" ' . $website_attribute . '><div id="website-error' . $isuffix . '" class="error-des"><span>' . $website_field_error . '</span></div></div></div>' : '';

// Subject field.
$subject_input = 'visible' === $subject_field || ( 'registered' === $subject_field && is_user_logged_in() ) || ( 'anonymous' === $subject_field && ! is_user_logged_in() ) ? '<div class="sform-field-group ' . $row_label . '">' . $subject_field_label . '<div class="' . $wrap_class . '"><input type="text" name="sform-subject" id="sform-subject' . $isuffix . '" class="' . $subject_class . '" ' . $subject_attribute . ' value="' . $subject_value . '" placeholder="' . $subject_placeholder . '" ><div id="subject-error' . $isuffix . '" class="error-des"><span>' . $subject_field_error . '</span></div></div></div>' : '';

// Message field.
$message_input = '<div class="sform-field-group ' . $row_label . '">' . $message_field_label . '<div class="' . $wrap_class . '"><textarea name="sform-message" id="sform-message' . $isuffix . '" rows="10" type="textarea" class="' . $message_class . '" required ' . $message_maxlength . ' placeholder="' . $message_placeholder . '" ' . $message_attribute . '>' . $message_value . '</textarea><div id="message-error' . $isuffix . '" class="error-des"><span>' . $message_field_error . '</span></div></div></div>';

// Consent field.
$consent_input = 'visible' === $consent_field || ( 'registered' === $consent_field && is_user_logged_in() ) || ( 'anonymous' === $consent_field && ! is_user_logged_in() ) ? '<div class="sform-field-group checkbox ' . $row_label . '"><input type="checkbox" name="sform-consent" id="sform-consent' . $isuffix . '" class="' . $consent_field_class . '" value="' . $consent_value . '" ' . $consent_attribute . '><label for="sform-consent' . $isuffix . '" class="' . $consent_class . '"><span class="checkmark"></span><span class="' . $label_font_size . '">' . $consent_label . '</span>' . $consent_sign . '</label></div>' : '';

// Captcha field.
$captcha_input = 'visible' === $captcha_field || ( 'registered' === $captcha_field && is_user_logged_in() ) || ( 'anonymous' === $captcha_field && ! is_user_logged_in() ) ? '<div class="sform-field-group ' . $row_label . '" id="captcha-container' . $isuffix . '"><label for="sform-captcha' . $isuffix . '" ' . $label_class . '>' . $captcha_label . $captcha_sign . '</label><div><div id="captcha-field' . $isuffix . '" class="' . $captcha_class . '">' . $captcha_hidden . '<input id="captcha-question' . $isuffix . '" type="text" class="sform-field question" readonly="readonly" tabindex="-1" value="' . $captcha_question . '" /><input type="number" id="sform-captcha' . $isuffix . '" name="sform-captcha" class="' . $captcha_answer_class . '" ' . $captcha_attribute . ' value="' . $captcha_value . '" /></div><div id="captcha-error' . $isuffix . '" class="captcha-error error-des ' . $row_label . '"><span class="' . $captcha_error_class . '">' . $captcha_field_error . '</span></div></div></div>' : '';

$captcha_input = apply_filters( 'recaptcha_usage', $captcha_input, $form_id, $error_class );

// Form fields assembling.
$form .= $name_input . $lastname_input . $email_input . $phone_input . $website_input . $subject_input . $message_input . $consent_input . $captcha_input . $hidden_fields;

// Contact Form bottom error message.
$form .= $bottom_error;

// Submit field.
$form .= '<div id="sform-submit-wrap' . $isuffix . '" class="' . $submit_class . '"><button name="submission" id="submission' . $isuffix . '" type="submit" class="' . $button_class . '">' . $submit_label . '</button>' . $animation . '</div></form>';

// Switch from displaying contact form to displaying success message if ajax submission is disabled.
$contact_form = isset( $_GET['sending'] ) && 'success' === $_GET['sending'] && isset( $_GET['form'] ) && (int) $_GET['form'] === $form_id ? $success_message : $form; // phpcs:ignore
