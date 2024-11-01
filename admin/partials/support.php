<?php
/**
 * File delegated to show the support admin page.
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
$wplang        = get_locale();
$lang          = strlen( $wplang ) > 0 ? explode( '_', $wplang )[0] : 'en';
$country       = isset( explode( '_', $wplang )[1] ) ? strtolower( explode( '_', $wplang )[1] ) : '';
$slug          = isset( explode( '_', $wplang )[2] ) ? explode( '_', $wplang )[2] : 'default';
$lang_code     = $lang === $country || '' === $country ? $lang : $lang . '-' . $country;
$language_pack = $lang_code . '/' . $slug;
$url           = 'https://translate.wordpress.org/locale/' . $language_pack . '/wp-plugins/simpleform/';
/* translators: %1$s: native language name, %2$s: URL to translate.wordpress.org */
$message             = __( 'SimpleForm is not translated into %1$s yet. <a href="%2$s">Help translate it!</a>', 'simpleform' );
$translation_message = __( 'Help improve the translation', 'simpleform' );

// Page wrap: opening tag.
$support_page = '<div id="sform-wrap" class="sform ' . esc_attr( $wrap_class ) . '">';

// Admin notice.
$support_page .= '<div id="new-release" class="' . esc_attr( $notice_class ) . '">' . wp_kses_post( apply_filters( 'sform_update', $notice ) ) . '&nbsp;</div>';

// Page heading.
$support_page .= '<div class="full-width-bar ' . esc_attr( $color ) . '"><h1 class="title ' . esc_attr( $color ) . '"><span class="dashicons dashicons-groups responsive"></span>' . __( 'Support', 'simpleform' ) . '</h1></div><div class="row">';

// Support channels column.
$support_page .= '<div class="columns-wrap"><div class="columns-body"><h2>' . __( 'Support channels ', 'simpleform' ) . '</h2><h4>' . __( 'FAQs', 'simpleform' ) . '</h4>' . __( 'Are you having trouble getting started? Get started from the FAQs that cover everything you need to know about SimpleForm.', 'simpleform' ) . ' <a href="https://wordpress.org/plugins/simpleform/#faq" target="_blank" rel="noopener nofollow">' . __( 'Have a look at the FAQs', 'simpleform' ) . ' →</a><h4>' . __( 'Forum', 'simpleform' ) . '</h4>' . __( 'Didn\'t find the information you were looking for? Go to the WordPress.org plugin repository to get started, and log into your account. Click on “Support” and, in the “Search this forum” field, type a keyword about the issue you’re experiencing. Read topics that are similar to your issue to see if the topic has been resolved previously. If your issue remains after reading past topics, please create a new topic and fill out the form. We\'ll be happy to answer any additional questions!', 'simpleform' ) . ' <a href="https://wordpress.org/support/plugin/simpleform/" target="_blank" rel="noopener noreferrer nofollow">' . __( 'View the support forum', 'simpleform' ) . ' →</a><h4>' . __( 'SimpleForm needs your support', 'simpleform' ) . '</h4>' . __( 'It is hard to continue development and support for this plugin without contributions from users like you. If you enjoy using SimpleForm and find it useful, please consider making a donation. Your donation will help encourage and support the plugin\'s continued development and better user support.', 'simpleform' ) . ' <a href="https://www.paypal.com/paypalme/simpleformdonation" target="_blank" rel="noopener noreferrer nofollow">' . __( 'Make a donation for this plugin', 'simpleform' ) . ' →</a></div></div>';

// Report column.
$support_page .= '<div class="columns-wrap"><div class="columns-body"><h2>' . __( 'Report bugs, errors, and typos', 'simpleform' ) . '</h2><p>' . __( 'We need your help to make SimpleForm even better for you. If you notice any bugs, errors, or typos, please notify us as soon as possible. Report everything that you find. An issue might be glaringly obvious to you, but if you don’t report it, we may not even know about it. You can use the support forum in the WordPress.org plugin repository, or send an email. Your feedback will be greatly appreciated!', 'simpleform' ) . '</p><a href="mailto:simpleform.reports@gmail.com?subject=Plugin Errors Report" class="sform support button ' . esc_attr( $color ) . '">' . __( 'Report now', 'simpleform' ) . '</a></div></div>';

// Page wrap: closing tag.
$support_page .= '</div></div>';

echo wp_kses_post( $support_page );
