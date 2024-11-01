<?php
/**
 * File delegated to manage the entries relocation.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the entries management.
 */
class SimpleForm_Entries_Management {

	/**
	 * Class constructor.
	 *
	 * @since 2.2.0
	 */
	public function __construct() {

		// Update last moved messages when move entries from one form to another.
		add_action( 'moved_messages_updating', array( $this, 'moved_messages_updating' ), 10, 3 );
	}

	/**
	 * Set the sql conditions.
	 *
	 * @since 2.2.0
	 *
	 * @param string $to_be_moved The type of messages to be moved.
	 * @param string $type        The type of value to be returned.
	 *
	 * @return int|string The search condition.
	 */
	public function sql_conditions( $to_be_moved, $type ) {

		$where     = defined( 'SIMPLEFORM_SUBMISSIONS_NAME' ) ? " AND object != '' AND object != 'not stored' AND listable = '1'" : '';
		$timestamp = '';

		switch ( $to_be_moved ) {
			case 'lastyear' === $to_be_moved:
				$where    .= ' AND date >= UTC_TIMESTAMP() - INTERVAL 1 YEAR';
				$timestamp = strtotime( '-1 year' );
				break;
			case 'lastmonth' === $to_be_moved:
				$where    .= ' AND date >= UTC_TIMESTAMP() - INTERVAL 30 DAY';
				$timestamp = strtotime( '-1 month' );
				break;
			case 'lastweek' === $to_be_moved:
				$where    .= ' AND date >= UTC_TIMESTAMP() - INTERVAL 7 DAY';
				$timestamp = strtotime( '-1 week' );
				break;
			case 'lastday' === $to_be_moved:
				$where    .= ' AND date >= UTC_TIMESTAMP() - INTERVAL 24 HOUR';
				$timestamp = strtotime( '-1 day' );
				break;
			default:
				$where    .= '';
				$timestamp = '';
		}

		$value = 'where' === $type ? $where : $timestamp;

		return $value;
	}

	/**
	 * Restore all entries to the form used for sending.
	 *
	 * @since 2.2.0
	 *
	 * @param mixed[] $values Array of sanitized values used to update form locks.
	 *
	 * @return mixed[] Array of data to encode as JSON.
	 */
	public function entry_restoration( $values ) {

		global $wpdb;
		$form_id      = absint( $values['form'] );
		$relocation   = (bool) $values['relocation'];
		$moveto       = absint( $values['moveto'] );
		$to_be_moved  = strval( $values['be_moved'] );
		$onetime      = (bool) $values['onetime'];
		$deletion     = (bool) $values['deletion_form'];
		$settings     = (bool) $values['settings'];
		$form_to_name = strval( $values['form_to'] );
		$util         = new SimpleForm_Util();
		$moved_to     = (array) maybe_unserialize( strval( $util->form_property_value( $form_id, 'moved_to', array() ) ) );
		$where        = strval( $this->sql_conditions( 'all', 'where' ) );

		$updated_values = array(
			'form'       => $form_id,
			'moved_from' => '0',
		);

		$restore = $wpdb->update( $wpdb->prefix . 'sform_submissions', $updated_values, array( 'moved_from' => $form_id ) ); // phpcs:ignore.

		if ( $restore ) {

			// Delete cache and update the data of the forms from which the entries have been restored.
			foreach ( $moved_to as $form ) {
				wp_cache_delete( 'sform_submissions_' . $form );
				wp_cache_delete( 'form_data_' . $form );
				$form_entries = $util->form_submissions( absint( $form ), $where, false );
				$wpdb->update( $wpdb->prefix . 'sform_shortcodes', array( 'entries' => $form_entries ), array( 'id' => absint( $form ) ) ); // phpcs:ignore.
			}

			// Delete cache and update the data of the form to which the entries have been restored.
			wp_cache_delete( 'sform_submissions_' . $form_id );
			wp_cache_delete( 'form_data_' . $form_id );
			$entries = $util->form_submissions( $form_id, $where, false );

			if ( 'next' !== $to_be_moved ) {
				$transfer = false;
				$message  = __( 'Entries restored successfully', 'simpleform' );
				$moved_to = array();
			} else {
				$transfer = true;
				/* translators: %s: The form name. */
				$message  = sprintf( __( 'Entries restored and transfer to %s successfully scheduled', 'simpleform' ), $form_to_name );
				$moved_to = array( $moveto );
			}

			$updated_values = array(
				'entries'           => $entries,
				'moved_entries'     => 0,
				'relocation'        => $relocation,
				'moveto'            => $moveto,
				'moved_to'          => maybe_serialize( $moved_to ),
				'to_be_moved'       => $to_be_moved,
				'onetime_moving'    => $onetime,
				'override_settings' => $settings,
				'deletion'          => $deletion,
			);

			$wpdb->update( $wpdb->prefix . 'sform_shortcodes', $updated_values, array( 'id' => $form_id ) ); // phpcs:ignore.

			// Rebuild the entries selector.
			$select = $this->entries_selector( $form_id, $entries );

			$data = array(
				'error'    => false,
				'update'   => true,
				'moving'   => $transfer,
				'select'   => $select,
				'onetime'  => false,
				'restore'  => true,
				'messages' => $entries,
				'moved'    => 0,
				'message'  => $message,
			);

		} else {

			$empty_data = array(
				'error'    => false,
				'update'   => false,
				'restored' => true,
				'message'  => 'next' === $to_be_moved ? __( 'Entries already restored and transfer scheduled', 'simpleform' ) : __( 'Messages have already been restored', 'simpleform' ),
			);

			$found_data = array(
				'error'   => true,
				'message' => __( 'Error occurred while restoring the entries. Try again!', 'simpleform' ),
			);

			// Check if there are still entries to move.
			$data = 0 === intval( $util->form_moved_submissions( $form_id, true ) ) ? $empty_data : $found_data;

		}

		return $data;
	}

	/**
	 * Make an entry transfer.
	 *
	 * @since 2.2.0
	 *
	 * @param mixed[] $values Array of sanitized values used to update form locks.
	 *
	 * @return mixed[] Array of data to encode as JSON.
	 */
	public function entry_transfer( $values ) {

		global $wpdb;
		$form_id      = absint( $values['form'] );
		$moveto       = absint( $values['moveto'] );
		$to_be_moved  = strval( $values['entries'] );
		$onetime      = (bool) $values['onetime'];
		$form_to_name = strval( $values['form_to'] );
		$where        = strval( $this->sql_conditions( $to_be_moved, 'where' ) );
		$util         = new SimpleForm_Util();
		$moved_to     = (array) maybe_unserialize( strval( $util->form_property_value( $form_id, 'moved_to', array() ) ) );

		// Check if entries that are about to be moved have already been moved before and retrieve the form ID they came from.
		$entries_from = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT moved_from FROM {$wpdb->prefix}sform_submissions WHERE form = %d AND moved_from != '0' {$where}", $form_id ) ); // phpcs:ignore

		$moving = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}sform_submissions SET form = %d, moved_from = %d WHERE form = %d {$where}", $moveto, $form_id, $form_id ) ); // phpcs:ignore

		if ( $moving ) {

			$where_clause = defined( 'SIMPLEFORM_SUBMISSIONS_NAME' ) ? " AND object != '' AND object != 'not stored' AND listable = '1'" : '';
			// Delete cache and update the data of the form to which the entries have been moved.
			wp_cache_delete( 'sform_submissions_' . $moveto );
			wp_cache_delete( 'form_data_' . $moveto );
			$entries_form_to = $util->form_submissions( $moveto, $where_clause, false );
			$wpdb->update( $wpdb->prefix . 'sform_shortcodes', array( 'entries' => $entries_form_to ), array( 'id' => $moveto ) ); // phpcs:ignore.
			// Delete cache and update the data of the form from which the entries have been moved.
			wp_cache_delete( 'sform_submissions_' . $form_id );
			wp_cache_delete( 'form_data_' . $form_id );
			$entries       = $util->form_submissions( $form_id, $where_clause, false );
			$moved_entries = $util->form_moved_submissions( $form_id, true );
			// Update the moved entries counter of the form from which the entries have already been moved.
			foreach ( $entries_from as $from ) {
				wp_cache_delete( 'sform_moved_submissions_' . $from );
				$updated_moved = $util->form_moved_submissions( $from, true );
				$wpdb->update( $wpdb->prefix . 'sform_shortcodes', array( 'moved_entries' => $updated_moved ), array( 'id' => absint( $from ) ) ); // phpcs:ignore.
			}

			if ( $onetime ) {
				$relocation_value = false;
				$moveto_value     = 0;
				/* translators: %s: The form name. */
				$message = sprintf( __( 'Entries transferred successfully to %s', 'simpleform' ), $form_to_name );
			} else {
				$relocation_value = true;
				$moveto_value     = $moveto;
				/* translators: %s: The form name. */
				$message = sprintf( __( 'Entries transferred to %s and successfully scheduled', 'simpleform' ), $form_to_name );
			}

			$updated_values = array(
				'entries'           => $entries,
				'moved_entries'     => $moved_entries,
				'relocation'        => $relocation_value,
				'moveto'            => $moveto_value,
				'moved_to'          => maybe_serialize( array_unique( array_merge( $moved_to, array( $moveto ) ) ) ),
				'to_be_moved'       => strval( $values['be_moved'] ),
				'onetime_moving'    => $onetime,
				'override_settings' => $values['settings'],
				'deletion'          => $values['deletion_form'],
			);

			$wpdb->update( $wpdb->prefix . 'sform_shortcodes', $updated_values, array( 'id' => $form_id ) ); // phpcs:ignore.

			do_action( 'moved_messages_updating', $form_id, $moveto, $to_be_moved );

			// Rebuild the entries selector.
			$select = $this->entries_selector( $form_id, $entries );

			$data = array(
				'error'       => false,
				'update'      => true,
				'moving'      => true,
				'transferred' => $moved_entries > 0 ? true : false,
				'select'      => $select,
				'onetime'     => $onetime,
				'messages'    => $entries,
				'moved'       => $moved_entries,
				'message'     => $message,
			);

		} else {

			$empty_data = array();

			$found_data = array(
				'error'   => true,
				'message' => __( 'Error occurred while transferring the entries. Try again!', 'simpleform' ),
			);

			// Check if there are still entries to move.
			$data = intval( $util->form_submissions( $form_id, $where, false ) ) > 0 ? $found_data : $empty_data;

		}

		return $data;
	}

	/**
	 * Schedule an entry transfer.
	 *
	 * @since 2.2.0
	 *
	 * @param mixed[] $values Array of sanitized values used to update form locks.
	 *
	 * @return mixed[] Array of data to encode as JSON.
	 */
	public function entry_transfer_scheduling( $values ) {

		global $wpdb;
		$form_id       = absint( $values['form'] );
		$moveto        = absint( $values['moveto'] );
		$be_moved      = strval( $values['be_moved'] );
		$deletion      = (bool) $values['deletion_form'];
		$transfer_type = (bool) $values['onetime'];
		$settings      = (bool) $values['settings'];
		$form_to_name  = strval( $values['form_to'] );
		$util          = new SimpleForm_Util();
		$moved_to      = (array) maybe_unserialize( strval( $util->form_property_value( $form_id, 'moved_to', array() ) ) );

		// Update the list of form IDs where entries that are about to be moved.
		$moved_to_updated = array_unique( array_merge( $moved_to, array( $moveto ) ) );

		$updated_values = array(
			'relocation'        => true,
			'moveto'            => $moveto,
			'moved_to'          => maybe_serialize( $moved_to_updated ),
			'to_be_moved'       => 'next',
			'onetime_moving'    => false,
			'override_settings' => $settings,
			'deletion'          => $deletion,
		);

		$scheduling = $wpdb->update( $wpdb->prefix . 'sform_shortcodes', $updated_values, array( 'id' => $form_id ) ); // phpcs:ignore

		if ( $scheduling ) {

			/* translators: %s: The form name. */
			$message = sprintf( __( 'Transfer scheduled successfully to %s', 'simpleform' ), $form_to_name );

			$data = array(
				'error'   => false,
				'update'  => true,
				'moving'  => false,
				'onetime' => false,
				'message' => $message,
			);

		} else {

			$moved_to = intval( $util->form_property_value( $form_id, 'moveto', 0 ) );
			$be_moved = strval( $util->form_property_value( $form_id, 'to_be_moved', '' ) );
			$onetime  = (bool) $util->form_property_value( $form_id, 'onetime_moving', false );
			/* translators: %s: The form name. */
			$message = sprintf( __( 'Transfer already scheduled to %s', 'simpleform' ), $form_to_name );

			if ( $moveto === $moved_to && 'next' === $be_moved && $transfer_type === $onetime ) {
				$data = array(
					'error'   => false,
					'update'  => false,
					'message' => $message,
				);

			} else {
				$data = array(
					'error'   => true,
					'message' => __( 'Error occurred while scheduling the transfer. Try again!', 'simpleform' ),
				);
			}
		}

		return $data;
	}

	/**
	 * Update the form locks.
	 *
	 * @since 2.2.0
	 *
	 * @param mixed[] $values Array of sanitized values used to update form locks.
	 *
	 * @return mixed[] Array of data to encode as JSON.
	 */
	public function deletion_lock( $values ) {

		$form_id        = absint( $values['form'] );
		$deletion       = (bool) $values['deletion_form'];
		$util           = new SimpleForm_Util();
		$moved_entries  = intval( $util->form_property_value( $form_id, 'moved_entries', 0 ) );
		$moved_to       = (array) maybe_unserialize( strval( $util->form_property_value( $form_id, 'moved_to', array() ) ) );
		$moved_to_value = $moved_entries > 0 ? $moved_to : maybe_serialize( array() );

		$updated_values = array(
			'relocation'     => false,
			'moveto'         => 0,
			'to_be_moved'    => '',
			'moved_to'       => $moved_to_value,
			'onetime_moving' => true,
			'deletion'       => $deletion,
		);

		global $wpdb;
		$update = $wpdb->update( $wpdb->prefix . 'sform_shortcodes', $updated_values, array( 'id' => $form_id ) ); // phpcs:ignore.

		// Clear cache if data updated.
		wp_cache_delete( 'form_data_' . $form_id );

		if ( $update ) {
			$data = array(
				'error'   => false,
				'update'  => true,
				'message' => __( 'Form locks were successfully saved', 'simpleform' ),
			);
		} else {
			$data = array(
				'error'   => false,
				'update'  => false,
				'message' => __( 'Form locks have already been saved', 'simpleform' ),
				'check'   => true,
			);
		}

		return $data;
	}

	/**
	 * Rebuild the entries selector every time there was an entry transfer.
	 *
	 * @since 2.2.0
	 *
	 * @param int $form_id The form ID.
	 * @param int $entries The number of entries of the form.
	 *
	 * @return string String used to define the options in the drop-down list.
	 */
	public function entries_selector( $form_id, $entries ) {

		$util         = new SimpleForm_Util();
		$where_year   = strval( $this->sql_conditions( 'lastyear', 'where' ) );
		$where_month  = strval( $this->sql_conditions( 'lastmonth', 'where' ) );
		$where_week   = strval( $this->sql_conditions( 'lastweek', 'where' ) );
		$where_day    = strval( $this->sql_conditions( 'lastday', 'where' ) );
		$last_year    = $util->form_submissions( $form_id, $where_year, true );
		$last_month   = $util->form_submissions( $form_id, $where_month, true );
		$last_week    = $util->form_submissions( $form_id, $where_week, true );
		$last_day     = $util->form_submissions( $form_id, $where_day, true );
		$option_all   = $entries !== $last_year ? '<option value="all">' . __( 'All', 'simpleform' ) . '</option>' : '';
		$option_year  = $last_year > 0 && $last_year !== $last_month ? '<option value="lastyear">' . __( 'Last year', 'simpleform' ) . '</option>' : '';
		$option_month = $last_month > 0 && $last_month !== $last_week ? '<option value="lastmonth">' . __( 'Last month', 'simpleform' ) . '</option>' : '';
		$option_week  = $last_week > 0 && $last_week !== $last_day ? '<option value="lastweek">' . __( 'Last week', 'simpleform' ) . '</option>' : '';
		$option_day   = $last_day > 0 ? '<option value="lastday">' . __( 'Last day', 'simpleform' ) . '</option>' : '';
		$select       = '<option value="" selected="selected">' . __( 'Select messages', 'simpleform' ) . '</option>' . $option_all . $option_year . $option_month . $option_week . $option_day . '<option value="next">' . __( 'Not received yet', 'simpleform' ) . '</option>';

		return $select;
	}

	/**
	 * Update the moved last messages when move entries from one form to another.
	 *
	 * @since 2.2.0
	 *
	 * @param int    $form_id     The form ID.
	 * @param int    $moveto      The form ID to move entries to.
	 * @param string $to_be_moved The type of messages to be moved.
	 *
	 * @return void
	 */
	public function moved_messages_updating( $form_id, $moveto, $to_be_moved ) {

		$last_from        = strval( get_option( "sform_last_{$form_id}_message" ) );
		$last_from_time   = ! empty( $last_from ) ? explode( '#', $last_from )[0] : '';
		$before_from      = strval( get_option( "sform_before_last_{$form_id}_message" ) );
		$before_from_time = ! empty( $before_from ) ? explode( '#', $before_from )[0] : '';
		$last_to          = strval( get_option( "sform_moved_last_{$moveto}_message" ) );
		$last_to_time     = ! empty( $last_to ) ? explode( '#', $last_to )[0] : '';
		$before_to        = strval( get_option( "sform_moved_before_last_{$moveto}_message" ) );
		$before_to_time   = ! empty( $before_to ) ? explode( '#', $before_to )[0] : '';

		if ( $last_from_time > $last_to_time ) {
			update_option( "sform_moved_last_{$moveto}_message", $last_from );
		}

		// Search through moved last messages the max timestamp compatible with the transfer range.
		$time_msg = $this->sql_conditions( $to_be_moved, 'timestamp' );
		$max_time = max( array( $time_msg, $before_to_time, $last_to_time ) );

		// Update only if the date is more recent.
		if ( $before_from_time > $max_time ) {
			update_option( "sform_moved_before_last_{$moveto}_message", $before_from );
		} elseif ( $last_to_time ) {
			// Move the last to before last.
			update_option( "sform_moved_before_last_{$moveto}_message", $last_to );
		}
	}
}

new SimpleForm_Entries_Management();
