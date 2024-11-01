<?php
/**
 * File delegated to manage the forms.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the forms management.
 */
class SimpleForm_Forms_Management extends SimpleForm_Forms_List {

	/**
	 * Class constructor
	 *
	 * @since  2.2.0
	 */
	public function __construct() {

		// Save screen options.
		add_filter( 'set_screen_option_forms_per_page', array( $this, 'save_screen_option' ), 10, 3 );
		// Register ajax callback for form deleting.
		add_action( 'wp_ajax_sform_delete_form', array( $this, 'sform_delete_form' ) );
		// Register ajax callback for form locks editing.
		add_action( 'wp_ajax_form_update', array( $this, 'form_update' ) );
		// Update the data relating to the use of the form.
		add_action( 'form_usage_updating', array( $this, 'form_usage_updating' ), 10, 3 );
		// Update pages list containing a form when a page is edited.
		add_action( 'save_post', array( $this, 'sform_pages_updating' ), 10, 2 );
		// Remove all unnecessary parameters leaving the original URL used before performing an action.
		add_action( 'current_screen', array( $this, 'url_cleanup' ) );
	}

	/**
	 * Save screen options.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed  $screen_option The value to save.
	 * @param string $option        The option name.
	 * @param int    $value         The option value.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @return int The screen option value.
	 */
	public function save_screen_option( $screen_option, $option, $value ) {

		if ( 'forms_per_page' === $option ) {
			return $value;
		}

		return intval( $screen_option );
	}

	/**
	 * Remove all unnecessary parameters leaving the original URL used before performing an action
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function url_cleanup() {

		global $sform_forms;
		$screen = get_current_screen();
		if ( ! is_object( $screen ) || $screen->id !== $sform_forms ) {
			return;
		}

		$sform_list_table = new SimpleForm_Forms_List();
		$doaction         = $sform_list_table->current_action();

		if ( $doaction ) {

			$type        = 'admin.php?page=sform-forms';
			$referer_url = $this->sform_get_referer( $type );
			$view        = isset( explode( '&view=', $referer_url )[1] ) ? explode( '&', explode( '&view=', $referer_url )[1] )[0] : 'all';

			$sform_list_table->prepare_items();

			$total_items = wp_cache_get( $view . '_items' );

			if ( false === $total_items ) {

				global $wpdb;
				$query = array(
					'all'       => $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_shortcodes WHERE status != 'trash'" ), // phpcs:ignore
					'published' => $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_shortcodes WHERE status = 'published'" ), // phpcs:ignore
					'draft'     => $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_shortcodes WHERE status = 'draft'" ), // phpcs:ignore
					'trash'     => $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_shortcodes WHERE status = 'trash'" ), // phpcs:ignore
				);

				$total_items = $query [ $view ];
				wp_cache_set( $view . '_items', $total_items );

			}

			$paged       = $sform_list_table->get_pagenum();
			$per_page    = $sform_list_table->get_items_per_page( 'forms_per_page', 10 );
			$total_pages = ceil( $total_items / $per_page );
			$pagenum     = $paged > $total_pages ? $total_pages : $paged;
			$url         = remove_query_arg( array( 'view', 'paged', 'action', 'action2', 'id', '_wpnonce', '_wp_http_referer' ), $referer_url );

			if ( $total_items > 0 ) {
				$url = add_query_arg( 'view', $view, $url );
			}

			if ( $pagenum > 1 ) {
				$url = add_query_arg( 'paged', $pagenum, $url );
			}

			wp_safe_redirect( $url );
			exit();

		}
	}

	/**
	 * Retrieve referer
	 *
	 * @since 2.2.0
	 *
	 * @param string $type The admin page ID.
	 *
	 * @return string The referer url.
	 */
	protected function sform_get_referer( $type ) {

		$referer_url = wp_get_referer();

		if ( ! $referer_url ) {
			$referer_url = admin_url( $type );
		}

		return $referer_url;
	}

	/**
	 * Edit the form card
	 *
	 * @since 2.1.0
	 * @since 2.2.0 Refactoring of code.
	 *
	 * @return void
	 */
	public function form_update() {

		if ( ! current_user_can( 'manage_options' ) ) {
			die( 'Security checked!' );
		} else {

			$validation = new SimpleForm_Admin_Validation();
			$values     = $validation->sanitized_locks();
			$form_id    = absint( $values['form'] );
			$util       = new SimpleForm_Util();

			// Make the locks options validation.
			$form_status = strval( $util->form_property_value( $form_id, 'status', 'draft' ) );
			$error       = $validation->locks_validation( $values, $form_status );

			if ( ! empty( $error ) ) {

				$data = array(
					'error'    => true,
					'redirect' => __( 'The form has been permanently deleted', 'simpleform' ) === $error ? true : false,
					'url'      => admin_url( 'admin.php?page=sform-forms' ),
					'message'  => $error,
				);

			} else {

				$movement           = (bool) $values['movement'];
				$to_be_moved        = strval( $values['entries'] );
				$restoration        = (bool) $values['restore'];
				$entries_management = new SimpleForm_Entries_Management();

				if ( $movement && ! $restoration ) {

					if ( 'next' !== $to_be_moved ) {
						// Make an entry transfer and return the updating result.
						$data = $entries_management->entry_transfer( $values );

					} else {
						// Schedule an entry transfer and return the updating result.
						$data = $entries_management->entry_transfer_scheduling( $values );
					}
				} elseif ( $restoration ) {

					// Restore all entries to the form used for sending and return the updating result.
					$data = $entries_management->entry_restoration( $values );

				} else {

					// Update the form locks.
					$data = $entries_management->deletion_lock( $values );

				}
			}
		}

		echo wp_json_encode( $data );
		wp_die();
	}

	/**
	 * Delete form.
	 *
	 * @since 2.0.4
	 *
	 * @return void
	 */
	public function sform_delete_form() {

		if ( ! current_user_can( 'manage_options' ) ) {
			die( 'Security checked!' );
		} else {

			$util       = new SimpleForm_Util();
			$validation = new SimpleForm_Admin_Validation();
			$values     = $validation->sanitized_locks();
			$form_id    = absint( $values['form_id'] );
			$form_name  = strval( $values['form_name'] );

			global $wpdb;
			$where_submissions = defined( 'SIMPLEFORM_SUBMISSIONS_NAME' ) ? "AND object != '' AND object != 'not stored' AND listable = '1'" : '';
			$entries           = $util->form_submissions( $form_id, $where_submissions, false );
			$hidden_input      = '<input type="hidden" id="confirmation" name="confirmation" value="true">';
			$confirmation      = $values['confirmation'];
			/* translators: %s: The form submissions. */
			$entries_message = sprintf( _n( 'The form contains %s message. ', 'The form contains %s messages. ', $entries ), $entries ) . '&nbsp;' . __( 'By Proceeding, all messages will be hidden from list tables. ', 'simpleform' ) . '&nbsp;' . __( 'If it is permanently deleted, all messages will also be permanently deleted. ', 'simpleform' ) . '&nbsp;' . __( 'Are you sure you don’t want to move them to another form first?', 'simpleform' );

			if ( $entries && '' === $confirmation ) {

				$data = array(
					'error'   => true,
					'message' => $entries_message,
					'confirm' => $hidden_input,
				);

			} else {

				$deletion = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}sform_shortcodes SET relocation = '0', moveto = '0', to_be_moved = '', onetime_moving = '1', previous_status = status, status = 'trash', deletion = '1' WHERE id = %d", $form_id ) ); // phpcs:ignore.
				// Clear cache if data updated.
				wp_cache_delete( 'form_data_' . $form_id );

				if ( $deletion ) {

					$post_cleaning = '';
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}sform_submissions SET hidden = '1' WHERE form = %d", $form_id ) ); // phpcs:ignore.

					// Clean up the post content by removing the deleted form.
					$deleting      = new SimpleForm_Forms_Deleting();
					$post_cleaning = $deleting->cleaning_up_content( $post_cleaning, $form_id );

					if ( ! empty( $post_cleaning ) ) {
						$updated_values = array(
							'form_pages'      => '',
							'form_widgets'    => '',
							'widget'          => '0',
							'status'          => 'trash',
							'previous_status' => 'draft',
						);
						$wpdb->update( $wpdb->prefix . 'sform_shortcodes', $updated_values, array( 'id' => $form_id ) ); // phpcs:ignore.
						/* translators: %s: The form name. */
						$message = sprintf( __( 'Form "%s" moved to trash. All pages containing the form have been cleaned up. ', 'simpleform' ), $form_name );
						$data    = array(
							'error'        => false,
							'message'      => $message,
							'img'          => '<span class="dashicons dashicons-saved"></span>',
							'redirect_url' => admin_url( 'admin.php?page=sform-forms' ),
						);
					} else {
						/* translators: %s: The form name. */
						$message = sprintf( __( 'Form "%s" moved to trash', 'simpleform' ), $form_name );
						$data    = array(
							'error'        => false,
							'message'      => $message,
							'img'          => '<span class="dashicons dashicons-saved"></span>',
							'redirect_url' => admin_url( 'admin.php?page=sform-forms' ),
						);
					}
				} else {
					$data = array(
						'error'   => true,
						'message' => __( 'Oops!', 'simpleform' ) . '<br>' . __( 'Error occurred deleting the form. Try again!', 'simpleform' ),
					);
				}
			}

			echo wp_json_encode( $data );
			wp_die();

		}
	}

	/**
	 * Update the usage data for the form.
	 *
	 * @since 2.0.5
	 * @since 2.1.3 Refactoring of code.
	 *
	 * @param int   $post_id    The post ID.
	 * @param int[] $used_forms The forms used in the post content.
	 * @param int   $form_id    The form ID.
	 *
	 * @return void
	 */
	public function form_usage_updating( $post_id, $used_forms, $form_id ) {

		$array_id = array( $post_id );
		global $wpdb;

		// Retrieves the cache content from the cache.
		$form_pages = wp_cache_get( 'form_pages_' . $form_id );

		// Do a database query and save it to the cache if the there is no cache data with this key.
		if ( false === $form_pages ) {
			$form_pages = $wpdb->get_row( $wpdb->prepare( "SELECT form_pages, form_widgets FROM {$wpdb->prefix}sform_shortcodes WHERE id = %d", $form_id ) ); // phpcs:ignore.
			wp_cache_set( 'form_pages_' . $form_id, $form_pages );
		}

		// Convert the comma-delimited pages list into an array of integers.
		$form_pages_ids = ! empty( $form_pages->form_pages ) ? array_map( 'intval', explode( ',', str_replace( ' ', '', $form_pages->form_pages ) ) ) : array();
		$form_status    = ! empty( $form_pages->form_pages ) || ! empty( $form_pages->form_widgets ) ? 'published' : 'draft';

		// If the post content contains the form add it to the form pages list, otherwise remove it from the form pages list.
		if ( in_array( (int) $form_id, $used_forms, true ) ) {
			if ( ! in_array( $post_id, $form_pages_ids, true ) ) {
				$new_form_pages = implode( ',', array_unique( array_merge( $array_id, $form_pages_ids ) ) );
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}sform_shortcodes SET form_pages = %s, status = 'published' WHERE id = %d", $new_form_pages, $form_id ) ); // phpcs:ignore.
			}
		} elseif ( in_array( $post_id, $form_pages_ids, true ) ) {
				$updated_form_pages = array_diff( $form_pages_ids, $array_id );
				$new_form_pages     = implode( ',', $updated_form_pages );
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}sform_shortcodes SET form_pages = %s, status = %s WHERE id = %d", $new_form_pages, $form_status, $form_id ) ); // phpcs:ignore.
		}
	}

	/**
	 * Update pages list containing a form when a page is edited
	 *
	 * @since 2.0.5
	 * @since 2.1.3 Refactoring of code.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 *
	 * @return void
	 */
	public function sform_pages_updating( $post_id, $post ) {

		// Return if not yet published.
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		// If this is a revision, get real post ID.
		$parent_id = wp_is_post_revision( $post_id );
		$post_id   = $parent_id ? intval( $parent_id ) : intval( $post_id );
		$array_id  = array( $post_id );

		// Search simpleform in the post content and retrieves all forms data.
		$util        = new SimpleForm_Util();
		$used_forms  = $util->used_forms( $post->post_content, 'all' );
		$form_ids    = $util->sform_ids();
		$sform_pages = get_option( 'sform_pages' ) !== false ? (array) get_option( 'sform_pages' ) : $util->form_pages();
		// Make sure the array contains only integers.
		$sform_pages = array_map( 'intval', $sform_pages );

		// If the post contains the form, make sure the post ID is on the form pages list.
		// Otherwise, if the post ID is included in the form pages list and remove it from the list.
		if ( ! empty( $used_forms ) ) {

			foreach ( $form_ids as $form_id ) {
				do_action( 'form_usage_updating', $post_id, $used_forms, $form_id );
			}
			if ( ! in_array( $post_id, $sform_pages, true ) ) {
				$updated_sform_pages = array_unique( array_merge( $array_id, $sform_pages ) );
				update_option( 'sform_pages', $updated_sform_pages );
			}
		} elseif ( in_array( $post_id, $sform_pages, true ) ) {
			foreach ( $form_ids as $form_id ) {
				do_action( 'form_usage_updating', $post_id, $used_forms, $form_id );
			}
			$updated_sform_pages = array_diff( $sform_pages, $array_id );
			update_option( 'sform_pages', $updated_sform_pages );
		}
	}
}

new SimpleForm_Forms_Management();
