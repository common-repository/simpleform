<?php
/**
 * File delegated to list the most commonly used functions.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the the general utilities class.
 */
class SimpleForm_Util {

	/**
	 * Search all shortcodes ids
	 *
	 * @since 2.0.2
	 *
	 * @return int[] All IDs of created forms.
	 */
	public function sform_ids() {

		$form_ids = wp_cache_get( 'sform_ids' );

		// Do a database query and save it to the cache if the there is no cache data with this key.
		if ( false === $form_ids ) {
			global $wpdb;
			$form_ids = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}sform_shortcodes" ); // phpcs:ignore.
			wp_cache_set( 'sform_ids', $form_ids );
		}

		return $form_ids;
	}

	/**
	 * Retrieve the number of submissions by form ID
	 *
	 * @since 2.2.0
	 *
	 * @param int    $form_id The form ID.
	 * @param string $where   The SQL where clause used to filter the results.
	 * @param bool   $filter  The need for a new query.
	 *
	 * @return int The number of form submissions.
	 */
	public function form_submissions( $form_id, $where, $filter ) {

		$caching = $filter ? false : wp_cache_get( 'sform_submissions_' . $form_id );

		// Do a database query if the there is no cache data with this key.
		if ( false === $caching ) {

			global $wpdb;
			$caching = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions WHERE form = %d {$where}", $form_id ) ); // phpcs:ignore

			// Save query result it to the cache only if a filter is not used.
			if ( ! $filter ) {
				wp_cache_set( 'sform_submissions_' . $form_id, $caching );
			}
		}

		return $caching;
	}

	/**
	 * Retrieve the number of submissions by form ID which have been moved
	 *
	 * @since 2.2.0
	 *
	 * @param int  $form_id The form ID.
	 * @param bool $filter  The need for a new query.
	 *
	 * @return int The number of form submissions which have been moved.
	 */
	public function form_moved_submissions( $form_id, $filter ) {

		$caching = $filter ? false : wp_cache_get( 'sform_moved_submissions_' . $form_id );

		// Do a database query if the there is no cache data with this key.
		if ( false === $caching ) {
			global $wpdb;
			$caching = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions WHERE moved_from = %d", $form_id ) ); // phpcs:ignore.
			// Save query result it to the cache only if SQL where clause is not used.
			wp_cache_set( 'sform_moved_submissions_' . $form_id, $caching );
		}

		return $caching;
	}

	/**
	 * Search for all forms used in the post content
	 *
	 * @since 2.0.5
	 *
	 * @param string $content The content of the post.
	 * @param string $type    The type of the option.
	 *
	 * @return int[] Array of forms IDs.
	 */
	public function used_forms( $content, $type ) {

		$used_forms = array();

		// Search for any use of SimpleForm as shortcode.
		$last_pos  = 0;
		$positions = array();
		while ( ( $last_pos = strpos( $content, '[simpleform', $last_pos ) ) !== false ) { // phpcs:ignore
			$positions[] = $last_pos;
			$last_pos    = $last_pos + strlen( '[simpleform' );
		}

		foreach ( $positions as $value ) {
			$split     = substr( $content, $value );
			$shortcode = explode( ']', $split )[0];
			if ( '[simpleform' === $shortcode ) {
				$form_id = 1;
			} else {
				$form_id = 0 !== (int) filter_var( $shortcode, FILTER_SANITIZE_NUMBER_INT ) ? (int) filter_var( $shortcode, FILTER_SANITIZE_NUMBER_INT ) : '';
			}
			$used_forms[] = $form_id;
		}

		// Search for SimpleForm blocks.
		if ( 'all' === $type ) {
			if ( has_blocks( $content ) ) {
				$block_class = new SimpleForm_Block( SIMPLEFORM_NAME, SIMPLEFORM_VERSION );
				$ids         = $block_class->get_sform_block_ids( $content );
				$used_forms  = array_merge( $used_forms, $ids );
			}
		}

		// Return an array containing only integers.
		return array_map( 'intval', $used_forms );
	}

	/**
	 * Get a pages list that use simpleform in the post content
	 *
	 * @since 2.0.2
	 * @since 2.1.3 The `$form_id` parameter was added.
	 * @since 2.2.0 The `$form_id` parameter was removed.
	 *
	 * @return int[] Array of pages IDs.
	 */
	public function form_pages() {

		global $wpdb;

		$form_pages = wp_cache_get( 'form_pages' );

		// Do a database query and save it to the cache if the there is no cache data with this key.
		if ( false === $form_pages ) {

			$form_pages = array();
			$lists      = $wpdb->get_col( "SELECT form_pages FROM {$wpdb->prefix}sform_shortcodes" ); // phpcs:ignore.

			foreach ( $lists as $list ) {
				if ( ! empty( $list ) ) {
					$form_pages = array_unique( array_merge( $form_pages, explode( ',', $list ) ) );
				}
			}

			wp_cache_set( 'form_pages', $form_pages );
			update_option( 'sform_pages', $form_pages );

		}

		// Make sure the page list contains only integers.
		$form_pages = array_map( 'intval', (array) $form_pages );

		return $form_pages;
	}

	/**
	 * Update additional style to enqueue.
	 *
	 * @since 2.2.0
	 *
	 * @param int    $form_id The ID of the form.
	 * @param string $option  The name of the option to update.
	 * @param string $style   The style to enqueue.
	 *
	 * @return void
	 */
	public function enqueue_additional_styles( $form_id, $option, $style ) {

		if ( $style ) {

			$style_option = strval( get_option( $option ) );

			if ( $style_option ) {
				$style_start = '/*' . $form_id . '*/';
				$split_style = explode( $style_start, $style_option );
				if ( isset( $split_style[1] ) ) {
					$style_end        = '/* END ' . $form_id . '*/';
					$split_form_style = explode( $style_end, $split_style[1] );
					$previous_style   = isset( $split_form_style[1] ) ? $split_style[0] . $split_form_style[1] : $split_style[0];
				} else {
					$previous_style = $style_option;
				}
			} else {
				$previous_style = '';
			}

			$form_style  = '/*' . $form_id . '*/' . $style . '/* END ' . $form_id . '*/';
			$forms_style = $previous_style . $form_style;

			update_option( $option, $forms_style );

		}
	}

	/**
	 * Update additional scripts to enqueue.
	 *
	 * @since 2.1.8
	 *
	 * @param int $form_id The ID of the form.
	 *
	 * @return void
	 */
	public function enqueue_additional_scripts( $form_id ) {

		$ajax            = $this->get_sform_option( $form_id, 'settings', 'ajax_submission', false );
		$ajax_error      = strval( $this->get_sform_option( $form_id, 'settings', 'ajax_error', __( 'Error occurred during AJAX request. Please contact support!', 'simpleform' ) ) );
		$outside_error   = $this->get_sform_option( $form_id, 'settings', 'outside_error', 'bottom' );
		$outside         = 'none' !== $outside_error ? 'true' : 'false';
		$multiple_spaces = $this->get_sform_option( $form_id, 'settings', 'multiple_spaces', false );

		$script  = '';
		$script .= $multiple_spaces ? 'jQuery(document).ready(function(){jQuery("input[parent=\'' . $form_id . '\'],textarea[parent=\'' . $form_id . '\']").on("input",function(){jQuery(this).val(jQuery(this).val().replace(/\s\s+/g," "));});});' : '';
		$script .= $ajax ? 'var outside' . $form_id . ' = "' . $outside . '"; var ajax_error' . $form_id . ' = "' . $ajax_error . '";' : '';

		if ( ! empty( $script ) ) {

			$current_block_script = strval( get_option( 'sform_additional_script' ) );

			if ( $current_block_script ) {

				$pattern_starting = '\/\*' . $form_id . '\*\/';
				$pattern_closing  = '\/\* END ' . $form_id . '\*\/';
				$pattern          = '/' . $pattern_starting . '(.*?)' . $pattern_closing . '/';
				$previous_script  = preg_replace( $pattern, '', $current_block_script );

			} else {

				$previous_script = '';

			}

			$block_script       = '/*' . $form_id . '*/' . $script . '/* END ' . $form_id . '*/';
			$additional_scripts = $previous_script . $block_script;
			update_option( 'sform_additional_script', $additional_scripts );

		}
	}

	/**
	 * Retrieve the form properties value.
	 *
	 * @since 2.2.0
	 *
	 * @param int    $form_id       The ID of the form.
	 * @param string $type          The type of data.
	 * @param mixed  $default_value The default value to return if the property value does not exist.
	 *
	 * @return mixed The property value to return.
	 */
	public function form_property_value( $form_id, $type, $default_value ) {

		$form_data = wp_cache_get( 'form_data_' . $form_id );

		// Do a database query and save it to the cache if the there is no cache data with this key.
		if ( false === $form_data ) {
			global $wpdb;
			$form_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}sform_shortcodes WHERE id = %d", $form_id ) ); // phpcs:ignore.
			wp_cache_set( 'form_data_' . $form_id, $form_data );
		}

		$property_value = isset( $form_data->$type ) ? $form_data->$type : $default_value;

		return $property_value;
	}

	/**
	 * Retrieve the option value.
	 *
	 * @since 2.2.0
	 *
	 * @param int             $form_id       The ID of the form.
	 * @param string          $type          The type of the option.
	 * @param string          $key           The key of the option.
	 * @param bool|string|int $default_value The default value to return if the option does not exist.
	 *
	 * @return mixed The option value or array of values.
	 */
	public function get_sform_option( $form_id, $type, $key, $default_value ) {

		if ( 1 === (int) $form_id ) {
			$option = (array) get_option( 'sform_' . $type );
		} else {
			$option = false !== get_option( 'sform_' . $form_id . '_' . $type ) ? (array) get_option( 'sform_' . $form_id . '_' . $type ) : (array) get_option( 'sform_' . $type );
		}

		if ( $key ) {
			if ( isset( $option[ $key ] ) ) {
				if ( is_bool( $option[ $key ] ) ) {
					$value = $option[ $key ] ? true : false;
				} else {
					$value = ! empty( $option[ $key ] ) ? $option[ $key ] : $default_value;
				}
			} else {
				$value = $default_value;
			}
		} else {
			$value = $option;
		}

		return $value;
	}

	/**
	 * Retrieve the form ID from block content.
	 *
	 * @since 2.2.0
	 *
	 * @param string $block_content The content of the block.
	 *
	 * @return string|int The form ID.
	 */
	public function block_form_id( $block_content ) {

		$form = '';

		if ( strpos( $block_content, 'wp:simpleform/form-selector' ) !== false ) {

				$splitting = explode( 'formId":"', $block_content );
				$form      = isset( $splitting[1] ) ? (int) explode( '"', $splitting[1] )[0] : '';
		}

		if ( strpos( $block_content, '[simpleform' ) !== false ) {

				$splitting = explode( '[simpleform', $block_content );
				$number    = isset( $splitting[1] ) ? explode( ']', $splitting[1] )[0] : '';
				$form      = empty( $number ) ? 1 : (int) filter_var( $number, FILTER_SANITIZE_NUMBER_INT );

		}

		return $form;
	}

	/**
	 * Expand the list of allowed HTML tags and their allowed attributes.
	 *
	 * @since 2.2.0
	 *
	 * @return array<string, mixed[]> Multidimensional array of allowed HTML tags.
	 */
	public function sform_allowed_tags() {

		$allowed_tags = wp_kses_allowed_html( 'post' );

		$allowed_tags['div']['tabindex'] = true;

		$allowed_tags['form'] = array(
			'id'     => true,
			'method' => true,
			'class'  => true,
			'form'   => true,
		);

		$allowed_tags['input'] = array(
			'type'        => true,
			'id'          => true,
			'name'        => true,
			'class'       => true,
			'value'       => true,
			'checked'     => true,
			'placeholder' => true,
			'min'         => true,
			'max'         => true,
			'box'         => true,
			'parent'      => true,
			'disabled'    => true,
			'readonly'    => true,
			'tabindex'    => true,
		);

		$allowed_tags['noscript'] = true;

		$allowed_tags['optgroup'] = array(
			'label' => true,
		);

		$allowed_tags['option'] = array(
			'value'    => true,
			'selected' => true,
			'tag'      => true,
			'disabled' => true,
		);

		$allowed_tags['select'] = array(
			'id'       => true,
			'name'     => true,
			'class'    => true,
			'style'    => true,
			'field'    => true,
			'box'      => true,
			'parent'   => true,
			'disabled' => true,
		);

		$allowed_tags['svg'] = array(
			'xmlns'   => true,
			'viewBox' => true,
			'path'    => true,
		);

		$allowed_tags['textarea']['placeholder'] = true;
		$allowed_tags['textarea']['parent']      = true;

		return $allowed_tags;
	}
}
