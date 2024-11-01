<?php
/**
 * File delegated to extend the functions that takes care of listing the forms
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Integration Class that extends the SimpleForm_Forms_List subclass.
 */
class SimpleForm_Forms_List_Extender extends SimpleForm_Forms_List {

	/**
	 * Sanitize URL argument.
	 *
	 * @since 2.2.0
	 *
	 * @param string $key The key ID.
	 *
	 * @return mixed The sanitized value.
	 */
	public function sanitized_data( $key ) {

		switch ( $key ) {
			case 'orderby' === $key:
				// phpcs:ignore WordPress.Security.NonceVerification
				$conditions = isset( $_GET[ $key ] ) && in_array( $_GET[ $key ], array( 'name', 'creation' ), true );
				break;
			case 'order' === $key:
				// phpcs:ignore WordPress.Security.NonceVerification
				$conditions = isset( $_GET[ $key ] ) && in_array( $_GET[ $key ], array( 'asc', 'desc' ), true );
				break;
			default:
				// phpcs:ignore WordPress.Security.NonceVerification
				$conditions = isset( $_GET[ $key ] );
		}

		if ( $conditions ) {

			$sanitized_value = array(
				'view'     => sanitize_text_field( wp_unslash( $_GET[ $key ] ) ), // phpcs:ignore
				'orderby'  => ! is_array( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '', // phpcs:ignore
				'order'    => sanitize_text_field( wp_unslash( $_GET[ $key ] ) ), // phpcs:ignore
				'paged'    => absint( $_GET[ $key ] ), // phpcs:ignore
				'id'       => is_array( $_GET[ $key ] ) ? array_map( 'absint', $_GET[ $key ] ) : absint( $_GET[ $key ] ), // phpcs:ignore
				'_wpnonce' => sanitize_key( wp_unslash( $_GET[ $key ] ) ), // phpcs:ignore
				'action2'  => sanitize_text_field( wp_unslash( $_GET[ $key ] ) ), // phpcs:ignore
			);

			$value = $sanitized_value[ $key ];

		} else {

			$default_value = array(
				'view'     => 'all',
				'orderby'  => 'creation',
				'order'    => 'desc',
				'paged'    => 0,
				'id'       => '',
				'_wpnonce' => '',
				'action2'  => '',
			);

			$value = $default_value[ $key ];
		}

		return $value;
	}

	/**
	 * Delete form action
	 *
	 * @since 2.2.0
	 *
	 * @param int|int[] $form_id The ID of the form.
	 *
	 * @return void
	 */
	public function delete_form( $form_id ) {

		if ( ! empty( $form_id ) ) {

			global $wpdb;

			if ( ! is_array( $form_id ) ) {

				$form_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}sform_shortcodes WHERE id = %d", $form_id ) ); // phpcs:ignore.
				$success   = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}sform_shortcodes WHERE id = %d", $form_id ) ); // phpcs:ignore.
				wp_cache_delete( 'form_data_' . $form_id );

				if ( $success ) {

					$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}sform_submissions WHERE form = %d", $form_id ) ); // phpcs:ignore.
					wp_cache_delete( 'sform_submissions_' . $form_id );
					delete_option( 'sform_' . $form_id . '_attributes' );
					delete_option( 'sform_' . $form_id . '_settings' );
					$pattern = 'sform_%_' . $form_id . '_%';
					$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $pattern ) ); // phpcs:ignore.
					// Clear cache if options deleted.
					$wpdb->flush();
					/* translators: %s: The name of the form just deleted. */
					$action_notice = '<div class="notice notice-success is-dismissible"><p>' . sprintf( __( '%s permanently deleted', 'simpleform' ), $form_name ) . '</p></div>';
					set_transient( 'sform_form_action_notice', $action_notice, 5 );

				}
			} else {

				$ids = $form_id;
				// Count the number of values.
				$ids_count = count( $ids );
				// Prepare the right amount of placeholders in an array.
				$placeholders_array = array_fill( 0, $ids_count, '%s' );
				// Chains all the placeholders into a comma-separated string.
				$placeholders = implode( ',', $placeholders_array );

				$form_names = $wpdb->get_col( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}sform_shortcodes WHERE id IN($placeholders)", $ids ) ); // phpcs:ignore.
				$success    = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}sform_shortcodes WHERE id IN($placeholders)", $ids ) ); // phpcs:ignore.

				if ( $success ) {

					$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}sform_submissions WHERE form IN($placeholders)", $ids ) ); // phpcs:ignore.

					foreach ( $ids as $form ) {
						delete_option( 'sform_' . $form . '_attributes' );
						delete_option( 'sform_' . $form . '_settings' );
						$pattern = 'sform_%_' . $form . '_%';
						$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $pattern ) ); // phpcs:ignore.
						// Clear cache if options deleted.
						$wpdb->flush();
					}

					$forms = implode( ', ', $form_names );
					// Replace last comma with "and".
					$forms = count( $form_names ) > 1 ? substr_replace( $forms, __( ' and', 'simpleform' ), strrpos( $forms, ',' ), 1 ) : $forms;
					/* translators: %s: The names of the forms just deleted. */
					$action_notice = '<div class="notice notice-success is-dismissible"><p>' . sprintf( __( '%s permanently deleted', 'simpleform' ), $forms ) . '</p></div>';
					set_transient( 'sform_form_action_notice', $action_notice, 5 );

				}
			}
		}
	}

	/**
	 * Restore form action
	 *
	 * @since 2.2.0
	 *
	 * @param int|int[] $form_id The ID of the form.
	 *
	 * @return void
	 */
	protected function restore_form( $form_id ) {

		if ( ! empty( $form_id ) ) {

			if ( ! is_array( $form_id ) ) {

				global $wpdb;
				$success = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}sform_shortcodes SET status = 'draft', deletion = '0' WHERE id = %d", $form_id ) ); // phpcs:ignore.
				wp_cache_delete( 'form_data_' . $form_id );

				if ( $success ) {

					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}sform_submissions SET hidden = '0' WHERE form = %d", $form_id ) ); // phpcs:ignore.
					$wpdb->flush();
					$form_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}sform_shortcodes WHERE id = %d", $form_id ) ); // phpcs:ignore.
					/* translators: %s: The name of the form just restored. */
					$action_notice = '<div class="notice notice-success is-dismissible"><p>' . sprintf( __( '%s successfully restored from the Trash', 'simpleform' ), $form_name ) . '</p></div>';
					set_transient( 'sform_form_action_notice', $action_notice, 5 );

				}
			} else {

				$ids = $form_id;
				// Count the number of values.
				$ids_count = count( $ids );
				// Prepare the right amount of placeholders in an array.
				$placeholders_array = array_fill( 0, $ids_count, '%s' );
				// Chains all the placeholders into a comma-separated string.
				$placeholders = implode( ',', $placeholders_array );

				global $wpdb;
	            $success = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}sform_shortcodes SET status = 'draft', deletion = '0' WHERE id IN($placeholders)", $ids ) ); // phpcs:ignore	            

				if ( $success ) {

					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}sform_submissions SET hidden = '0' WHERE form IN($placeholders)", $ids ) ); // phpcs:ignore.            
					$form_names = $wpdb->get_col( $wpdb->prepare("SELECT name FROM {$wpdb->prefix}sform_shortcodes WHERE id IN($placeholders)", $ids ) ); // phpcs:ignore.
					$forms      = implode( ', ', $form_names );
					$forms      = count( $form_names ) > 1 ? substr_replace( $forms, __( ' and', 'simpleform' ), strrpos( $forms, ',' ), 1 ) : $forms;
					/* translators: %s: The names of the forms just restored. */
					$action_notice = '<div class="notice notice-success is-dismissible"><p>' . sprintf( __( '%s successfully restored from the Trash', 'simpleform' ), $forms ) . '</p></div>';
					set_transient( 'sform_form_action_notice', $action_notice, 5 );

				}
			}
		}
	}

	/**
	 * Display an admin notice whether the row/bulk action is successful
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function display_notice() {

		$transient_notice = get_transient( 'sform_form_action_notice' );
		$notice           = '' !== $transient_notice ? strval( $transient_notice ) : '';
		echo '<div class="submission-notice">' . wp_kses_post( $notice ) . '</div>';
	}

	/**
	 * Die when the nonce check fails
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function invalid_nonce_redirect() {

		wp_die(
			esc_html( __( 'Sorry, you are using an invalid nonce to proceed.', 'simpleform' ) ),
			esc_html( __( 'Error', 'simpleform' ) ),
			array(
				'response'  => 500,
				'back_link' => true,
			)
		);
	}
}
