<?php
/**
 * File delegated to list the forms that have been created.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the subclass that extends the WP_List_Table class.
 */
class SimpleForm_Forms_List extends WP_List_Table {

	/**
	 * Override the parent constructor to pass our own arguments
	 *
	 * @since 2.1.0
	 */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => 'sform-form',
				'plural'   => 'sform-forms',
				'ajax'     => false,
			)
		);

		$this->includes();
	}

	/**
	 * Set a list of views available on this table.
	 *
	 * @since 2.1.0
	 *
	 * @return string[] Array of views that can be used in the table.
	 */
	protected function get_views() {

		$views          = array();
		$extender_class = new SimpleForm_Forms_List_Extender();
		$view           = $extender_class->sanitized_data( 'view' );

		global $wpdb;
		$status_array    = $wpdb->get_col( "SELECT status FROM {$wpdb->prefix}sform_shortcodes" ); // phpcs:ignore.
		$count_published = (int) count( array_keys( $status_array, 'published', true ) );
		$count_drafts    = (int) count( array_keys( $status_array, 'draft', true ) );
		$count_trashed   = (int) count( array_keys( $status_array, 'trash', true ) );
		$count_inactive  = (int) count( array_keys( $status_array, 'inactive', true ) );
		$count_unused    = $count_trashed + $count_inactive;
		$count_all       = count( $status_array ) - $count_unused;

		$all_class = array(
			'all'       => 'class="current"',
			'published' => '',
			'draft'     => '',
			'trash'     => '',
		);

		$published_class = array(
			'all'       => '',
			'published' => 'class="current"',
			'draft'     => '',
			'trash'     => '',
		);

		$draft_class = array(
			'all'       => '',
			'published' => '',
			'draft'     => 'class="current"',
			'trash'     => '',
		);

		$trash_class = array(
			'all'       => '',
			'published' => '',
			'draft'     => '',
			'trash'     => 'class="current"',
		);

		$old_query_or_uri = remove_query_arg( 'paged' );

		$all_url      = remove_query_arg( array( 'view', 'paged' ) );
		$views['all'] = '<a id="view-all" href="' . $all_url . '" ' . $all_class[ $view ] . '>' . __( 'All', 'simpleform' ) . '</a> (' . $count_all . ')';

		$published_url = add_query_arg( 'view', 'published', $old_query_or_uri );
		if ( $count_published > 0 ) {
			$views['published'] = '<a id="view-published" href="' . $published_url . '" ' . $published_class[ $view ] . '>' . _x( 'Published', 'Plural noun', 'simpleform' ) . '</a> (' . $count_published . ')';
		}

		$drafts_url = add_query_arg( 'view', 'draft', $old_query_or_uri );
		if ( $count_drafts > 0 ) {
			$views['draft'] = '<a id="view-draft" href="' . $drafts_url . '" ' . $draft_class[ $view ] . '>' . __( 'Drafts', 'simpleform' ) . '</a> (' . $count_drafts . ')';
		}

		$trashed_url = add_query_arg( 'view', 'trash', $old_query_or_uri );
		if ( $count_trashed > 0 ) {
			$views['trash'] = '<a id="view-trash" href="' . $trashed_url . '" ' . $trash_class[ $view ] . '>' . __( 'Trash', 'simpleform' ) . '</a> (' . $count_trashed . ')';
		}

		$extender_class->display_notice();

		return $views;
	}

	/**
	 * Set a list of columns.
	 *
	 * @since 2.1.0
	 *
	 * @return string[] Array of columns that can be used in the table.
	 */
	public function get_columns() {

		$columns = array(
			'name'         => __( 'Name', 'simpleform' ),
			'target'       => __( 'Visibility', 'simpleform' ),
			'locks'        => __( 'Locks', 'simpleform' ),
			'entries'      => __( 'Entries', 'simpleform' ),
			'movedentries' => __( 'Moved', 'simpleform' ),
			'forwarding'   => __( 'Forwarding', 'simpleform' ),
			'creation'     => __( 'Creation Date', 'simpleform' ),
		);

		$column_cb      = array( 'cb' => '<input type="checkbox" />' );
		$extender_class = new SimpleForm_Forms_List_Extender();
		$view           = $extender_class->sanitized_data( 'view' );

		if ( 'trash' === $view ) {

			$total_items = wp_cache_get( 'trash_items' );

			if ( false === $total_items ) {
				global $wpdb;
				$total_items = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_shortcodes WHERE status = 'trash'" ); // phpcs:ignore.
				wp_cache_set( 'trash_items', $total_items );
			}

			$columns = $total_items > 1 ? $column_cb + $columns : $columns;

		}

		return $columns;
	}

	/**
	 * Render the bulk checkbox column.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed[] $item Array of database data.
	 *
	 * @return string The HTML for the item in the checkbox column.
	 */
	protected function column_cb( $item ) {

		return sprintf( '<input type="checkbox" name="id[]" value="%s" />', absint( $item['id'] ) );
	}

	/**
	 * Render the name column with actions
	 *
	 * @since 2.1.0
	 *
	 * @param mixed[] $item Array of database data.
	 *
	 * @return string The HTML for the item in the name column.
	 */
	protected function column_name( $item ) {

		$current_page = $this->get_pagenum();
		$per_page     = $this->get_items_per_page( 'forms_per_page', 10 );

		if ( 'trash' === $item['status'] ) {

			$total_items = $this->_pagination_args['total_items'];
			$total_pages = (int) ceil( ( $total_items - 1 ) / $per_page );
			$page        = $this->page_parameter( $current_page, $total_pages );
			$current_url = remove_query_arg( 'paged' );

			$restored_args = array(
				'action'   => 'restore',
				'id'       => $item['id'],
				'_wpnonce' => wp_create_nonce( 'restore_nonce' ),
			);
			$restore_link  = esc_url( add_query_arg( $restored_args, $current_url ) . $page );

			$deleted_args = array(
				'action'   => 'delete',
				'id'       => $item['id'],
				'_wpnonce' => wp_create_nonce( 'delete_nonce' ),
			);
			$delete_link  = esc_url( add_query_arg( $deleted_args, $current_url ) . $page );
			$actions      = array(
				'restore' => '<a href="' . $restore_link . '">' . __( 'Restore', 'simpleform' ) . '</a>',
				'delete'  => '<a href="' . $delete_link . '">' . __( 'Delete Permanently', 'simpleform' ) . '</a>',
			);

		} else {

			$managed_args = array(
				'page' => $this->_args['singular'],
				'id'   => $item['id'],
			);
			$manage_link  = esc_url( add_query_arg( $managed_args ) );

			$actions = array( 'view' => '<a href="' . $manage_link . '">' . __( 'Manage', 'simpleform' ) . '</a>' );

		}

		return sprintf( '%s %s', $item['name'], $this->row_actions( $actions ) );
	}

	/**
	 * Define the parameter that inserts the page into the URL
	 *
	 * @since 2.2.0
	 *
	 * @param int $current_page The current page number.
	 * @param int $total_pages  The total pages for the current views.
	 *
	 * @return string The page parameter.
	 */
	public function page_parameter( $current_page, $total_pages ) {

		$pagenum = $current_page > $total_pages ? $total_pages : $current_page;
		$page    = 1 < $pagenum ? '&paged=' . $pagenum : '';

		return $page;
	}

	/**
	 * Render the target column.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed[] $item Array of database data.
	 *
	 * @return string The HTML for the item in the target column.
	 */
	protected function column_target( $item ) {

		$show_for = $item['target'] ? strval( $item['target'] ) : 'all';

		$visibility = array(
			'all' => __( 'Everyone', 'simpleform' ),
			'in'  => __( 'Logged-in users', 'simpleform' ),
			'out' => __( 'Logged-out users', 'simpleform' ),
		);

		$target = $visibility[ $show_for ];

		return $target;
	}

	/**
	 * Render the locks column.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed[] $item Array of database data.
	 *
	 * @return string The HTML for the item in the locks column.
	 */
	protected function column_locks( $item ) {

		if ( $item['deletion'] && $item['relocation'] ) {

			$lock = '<span class="dashicons dashicons-unlock"></span><span class="lock notes invisible">' . __( 'Deletion and moving allowed', 'simpleform' ) . '</span>';

		} else {

			if ( $item['relocation'] ) {
				$classlock = 'orange';
				$notes     = __( 'Deletion not allowed', 'simpleform' );
			} elseif ( $item['deletion'] ) {
				$classlock = 'orange';
				$notes     = __( 'Moving not allowed', 'simpleform' );
			} else {
				$classlock = 'red';
				$notes     = __( 'Deletion and moving not allowed', 'simpleform' );
			}

			$lock = '<span class="dashicons dashicons-lock ' . $classlock . '"></span><span class="lock notes invisible ' . $classlock . '">' . $notes . '</span>';

		}

		return $lock;
	}

	/**
	 * Render the entries column.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed[] $item Array of database data.
	 *
	 * @return int The HTML for the item in the entries column.
	 */
	protected function column_entries( $item ) {

		return absint( $item['entries'] );
	}

	/**
	 * Render the moved column.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed[] $item Array of database data.
	 *
	 * @return int The HTML for the item in the moved column.
	 */
	protected function column_movedentries( $item ) {

		return absint( $item['moved_entries'] );
	}

	/**
	 * Render the forwarding column.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed[] $item Array of database data.
	 *
	 * @return string The HTML for the item in the forwarding column.
	 */
	protected function column_forwarding( $item ) {

		if ( 0 !== $item['moveto'] && 'next' === $item['to_be_moved'] && ! $item['onetime_moving'] ) {

			$moveto = absint( $item['moveto'] );
			$util   = new SimpleForm_Util();
			$name   = strval( $util->form_property_value( $moveto, 'name', '-' ) );

		} else {

			$name = '-';

		}

		return $name;
	}

	/**
	 * Render the date column.
	 *
	 * @since 2.1.0
	 * @since 2.2.0 Refactoring of code.
	 *
	 * @param mixed[] $item Array of database data.
	 *
	 * @return string The HTML for the item in the date column.
	 */
	protected function column_creation( $item ) {

		// Display the date in localized format according to the date format and timezone of the site.
		return date_i18n( strval( get_option( 'date_format' ) ), strtotime( get_date_from_gmt( strval( $item['creation'] ) ) ) );
	}

	/**
	 * Set a list of sortable columns.
	 *
	 * @since 2.1.0
	 *
	 * @return mixed[] Array of sortable columns.
	 */
	protected function get_sortable_columns() {

		$sortable_columns = array(
			'name'     => array( 'name', true ),
			'creation' => array( 'creation', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Retrieves the list of bulk actions available for this table.
	 *
	 * @since 2.1.0
	 *
	 * @return string[] Array of bulk actions available for this table.
	 */
	protected function get_bulk_actions() {

		$extender_class = new SimpleForm_Forms_List_Extender();
		$view           = $extender_class->sanitized_data( 'view' );
		$total_items    = $this->_pagination_args['total_items'];
		$actions        = array();

		if ( 'trash' === $view && $total_items > 1 ) {

			$actions = array(
				'bulk-restore' => 'Restore',
				'bulk-delete'  => 'Delete permanently',
			);

		}

		return $actions;
	}

	/**
	 * Process the bulk actions.
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	protected function process_bulk_action() {

		$extender_class = new SimpleForm_Forms_List_Extender();
		$current_action = $this->current_action();
		$nonce          = strval( $extender_class->sanitized_data( '_wpnonce' ) );

		$nonce_action = array(
			'delete'       => 'delete_nonce',
			'restore'      => 'restore_nonce',
			'bulk-delete'  => 'bulk-' . $this->_args['plural'],
			'bulk-restore' => 'bulk-' . $this->_args['plural'],
		);

		if ( $current_action && ! wp_verify_nonce( $nonce, $nonce_action [ $current_action ] ) ) {

			$extender_class->invalid_nonce_redirect();

		} else {

			if ( 'delete' === $current_action ) {

				$form_id = absint( $extender_class->sanitized_data( 'id' ) );
				$extender_class->delete_form( $form_id );
			}

			if ( 'restore' === $current_action ) {

				$form_id = absint( $extender_class->sanitized_data( 'id' ) );
				$extender_class->restore_form( $form_id );

			}

			if ( 'bulk-delete' === $current_action ) {

				$form_id = array_map( 'absint', (array) $extender_class->sanitized_data( 'id' ) );
				$extender_class->delete_form( $form_id );

			}

			if ( 'bulk-restore' === $current_action ) {

				$form_id = array_map( 'absint', (array) $extender_class->sanitized_data( 'id' ) );
				$extender_class->restore_form( $form_id );

			}
		}
	}

	/**
	 * Prepare the list of items for displaying.
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function prepare_items() {

		$extender_class = new SimpleForm_Forms_List_Extender();
		$view           = $extender_class->sanitized_data( 'view' );
		$orderby        = strval( $extender_class->sanitized_data( 'orderby' ) );
		$order          = strval( $extender_class->sanitized_data( 'order' ) );
		$where          = 'all' === $view ? " WHERE status != 'trash' AND status != 'inactive'" : " WHERE status = '" . $view . "'";
		$per_page       = $this->get_items_per_page( 'forms_per_page', 10 );
		$current_page   = $this->get_pagenum();
		$offset         = 1 < $current_page ? $per_page * ( $current_page - 1 ) : 0;

		$this->process_bulk_action();

		global $wpdb;
		$sql1  = "SELECT * FROM {$wpdb->prefix}sform_shortcodes {$where} ORDER BY {$orderby} {$order} LIMIT {$per_page} OFFSET {$offset}";
		$sql2  = "SELECT COUNT(id) FROM {$wpdb->prefix}sform_shortcodes {$where}";
		$items = $wpdb->get_results( $sql1, ARRAY_A ); // phpcs:ignore

		$total_items = wp_cache_get( $view . '_items' );
		if ( false === $total_items ) {
			$total_items = $wpdb->get_var( $sql2 ); // phpcs:ignore
			wp_cache_set( $view . '_items', $total_items );
		}

		$this->_column_headers = $this->get_column_info();

		$this->items = $items;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Include functions that extends the SimpleForm_Forms_List subclass.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function includes() {

		include_once plugin_dir_path( __DIR__ ) . 'includes/class-simpleform-forms-list-extender.php';
	}
}
