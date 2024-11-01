<?php
/**
 * File delegated to show the forms admin page.
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
$new_button    = '<a href="' . esc_url( get_admin_url( get_current_blog_id(), 'admin.php?page=sform-new' ) ) . '"><span class="dashicons dashicons-plus-alt icon-button admin ' . esc_attr( $color ) . '"></span><span class="wp-core-ui button admin back-list ' . esc_attr( $color ) . '">' . __( 'Add New', 'simpleform' ) . '</span></a>';
$util          = new SimpleForm_Util();
$allowed_tags  = $util->sform_allowed_tags();

// Page wrap: opening tag.
$top_forms_page = '<div id="sform-wrap" class="sform ' . esc_attr( $wrap_class ) . '">';

// Admin notice.
$top_forms_page .= '<div id="new-release" class="' . esc_attr( $notice_class ) . '">' . wp_kses_post( apply_filters( 'sform_update', $notice ) ) . '&nbsp;</div>';

// Page heading.
$top_forms_page .= '<div class="full-width-bar ' . esc_attr( $color ) . '"><h1 class="title ' . esc_attr( $color ) . '"><span class="dashicons dashicons-info responsive"></span>' . __( 'Forms', 'simpleform' ) . $new_button . '</h1></div>';

// Table wrap.
$top_forms_page .= '<div id="forms-wrap" class="submissions-list">';

// Form opening tag.
$top_forms_page .= '<form id="forms-table" method="get">';
// Ensure that the form posts back to current page.
$top_forms_page .= '<input type="hidden" name="page" value="sform-forms" />';

echo wp_kses( $top_forms_page, $allowed_tags );

// Display Forms List.
$table = new SimpleForm_Forms_List();
$table->prepare_items();
$table->views();
$table->display();

// Form closing tag.
$bottom_forms_page = '</form>';

// Table wrap: closing tag.
$bottom_forms_page .= '</div>';

// Page wrap: closing tag.
$bottom_forms_page .= '</div>';

echo wp_kses( $bottom_forms_page, $allowed_tags );
