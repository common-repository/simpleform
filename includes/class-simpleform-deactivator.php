<?php
/**
 * File delegated to deactivate the plugin.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class instantiated during the plugin's deactivation.
 */
class SimpleForm_Deactivator {

	/**
	 * Run during plugin deactivation.
	 *
	 * @since 1.6.1
	 *
	 * @return void
	 */
	public static function deactivate() {

		// Edit pre-built pages status for contact form and thank you message.
		$util                 = new SimpleForm_Util();
		$form_page_id         = intval( $util->get_sform_option( 1, 'settings', 'form_pageid', 0 ) );
		$confirmation_page_id = intval( $util->get_sform_option( 1, 'settings', 'confirmation_pageid', 0 ) );

		if ( 0 !== $form_page_id && get_post_status( $form_page_id ) ) {
			wp_update_post(
				array(
					'ID'          => $form_page_id,
					'post_status' => 'trash',
				)
			);
		}

		if ( 0 !== $confirmation_page_id && get_post_status( $confirmation_page_id ) ) {
			wp_update_post(
				array(
					'ID'          => $confirmation_page_id,
					'post_status' => 'trash',
				)
			);
		}
	}
}
