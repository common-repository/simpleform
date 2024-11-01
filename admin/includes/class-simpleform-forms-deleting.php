<?php
/**
 * File delegated to clean up the content form the deleted form.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that takes care of the cleaning of the content.
 */
class SimpleForm_Forms_Deleting {

	/**
	 * Clean up the website content by removing the deleted form.
	 *
	 * @since 2.2.0
	 *
	 * @param string $post_cleaning The value to filter.
	 * @param int    $form_id       The ID of the deleted form.
	 *
	 * @return string The result of the website content cleanup operation.
	 */
	public function cleaning_up_content( $post_cleaning, $form_id ) {

		$util            = new SimpleForm_Util();
		$form_pages_list = strval( $util->form_property_value( $form_id, 'form_pages', '' ) );
		$widget          = intval( $util->form_property_value( $form_id, 'widget', 0 ) );
		$widget_id       = strval( $util->form_property_value( $form_id, 'form_widgets', '' ) );
		// Make sure the array is an array of integers.
		$form_pages = $form_pages_list ? array_map( 'intval', explode( ',', str_replace( ' ', '', $form_pages_list ) ) ) : array();

		if ( $form_pages ) {
			foreach ( $form_pages as $post_id ) {

				// Clean up the widgets.
				$post_cleaning = $this->cleaning_up_pages( $post_id, $form_id );

			}
		}

		if ( $widget ) {

			// Clean up the widgets.
			$post_cleaning = $this->cleaning_up_widgets( $widget );

		}

		if ( $widget_id ) {

			// Clean up the block widgets.
			$post_cleaning = $this->cleaning_up_block_widgets( $widget_id, $form_id );

		}

		return $post_cleaning;
	}

	/**
	 * Remove the block from post content.
	 *
	 * @since 2.2.0
	 *
	 * @param string $content The post content.
	 * @param string $pattern The block pattern to search for.
	 * @param string $search  The ID pattern to search for.
	 *
	 * @return string The cleaned content.
	 */
	public function block_cleaning( $content, $pattern, $search ) {

		preg_match_all( $pattern, $content, $matches_pattern );

		if ( $matches_pattern ) {
			foreach ( $matches_pattern[0] as $block ) {
				if ( strpos( $block, $search ) !== false ) {
					$content = str_replace( $block, '', $content );
				}
			}
		}

		return $content;
	}

	/**
	 * Clean up the content of pages by removing the deleted form.
	 *
	 * @since 2.2.0
	 *
	 * @param int $post_id The page ID.
	 * @param int $form_id The ID of the deleted form.
	 *
	 * @return string The result of the page content cleanup operation.
	 */
	public function cleaning_up_pages( $post_id, $form_id ) {

		$pages_cleaning = '';
		$post           = get_post( $post_id );
		$content        = isset( $post->post_content ) ? $post->post_content : '';

		if ( has_blocks( $content ) ) {

			$block_pattern = '/<!-- wp:simpleform(.*)\/-->/';
			$search_block  = '"formId":"' . $form_id . '"';
			$content       = $this->block_cleaning( $content, $block_pattern, $search_block );

			$shortcode_pattern = '/<!-- wp:shortcode([^>]*)-->(.*?)<!-- \/wp:shortcode -->/s';
			$search_shortcode  = '[simpleform id="' . $form_id . '"]';
			$content           = $this->block_cleaning( $content, $shortcode_pattern, $search_shortcode );

		}

		// Remove shortcode not included in a block.
		$search_shortcode = '[simpleform id="' . $form_id . '"]';
		if ( strpos( $content, $search_shortcode ) !== false ) {
			$content = str_replace( $search_shortcode, '', $content );
		}

		$data = array(
			'ID'           => $post_id,
			'post_content' => $content,
		);

		$cleaning = wp_update_post( $data );

		if ( $cleaning ) {
			// Clean the post in the cache if data updated.
			clean_post_cache( $post_id );
			$pages_cleaning .= 'done';
		}

		return $pages_cleaning;
	}

	/**
	 * Clean up the widgets by removing those that contain the deleted form.
	 *
	 * @since 2.2.0
	 *
	 * @param int $widget The widget ID.
	 *
	 * @return string The result of the widgets cleanup operation.
	 */
	public function cleaning_up_widgets( $widget ) {

		$widgets_cleaning = '';
		$sform_widget     = (array) get_option( 'widget_simpleform' );
		unset( $sform_widget[ $widget ] );
		update_option( 'widget_simpleform', $sform_widget );
		$sidebars_widgets = (array) get_option( 'sidebars_widgets' );

		foreach ( $sidebars_widgets as $sidebar => $widgets ) {
			if ( is_array( $widgets ) ) {
				foreach ( $widgets as $index => $widget_id ) {
					if ( is_array( $sidebars_widgets[ $sidebar ] ) && 'simpleform-' . $widget === $widget_id ) {
						unset( $sidebars_widgets[ $sidebar ][ $index ] );
						$cleaning = update_option( 'sidebars_widgets', $sidebars_widgets );
						if ( $cleaning ) {
							$widgets_cleaning .= 'done';
						}
					}
				}
			}
		}

		return $widgets_cleaning;
	}

	/**
	 * Clean up the block widgets by removing those that contain the deleted form.
	 *
	 * @since 2.2.0
	 *
	 * @param string $widget_id The block widget ID.
	 * @param int    $form_id   The ID of the deleted form.
	 *
	 * @return string The result of the block widgets cleanup operation.
	 */
	public function cleaning_up_block_widgets( $widget_id, $form_id ) {

		$widgets_cleaning = '';
		$widget_block     = (array) get_option( 'widget_block', array() );

		foreach ( $widget_block as $key => $value ) {
			if ( is_array( $value ) ) {
				$this->widget_block_updating( $widget_block, $key, $value, $form_id );
			}
		}

		$sidebars_widgets = (array) get_option( 'sidebars_widgets' );

		foreach ( $sidebars_widgets as $sidebar => $widgets ) {
			if ( is_array( $widgets ) ) {
				foreach ( $widgets as $key => $value ) {
					if ( is_array( $sidebars_widgets[ $sidebar ] ) && $value === $widget_id ) {
						unset( $sidebars_widgets[ $sidebar ][ $key ] );
						$cleaning = update_option( 'sidebars_widgets', $sidebars_widgets );
						if ( $cleaning ) {
							$widgets_cleaning .= 'done';
						}
					}
				}
			}
		}

		return $widgets_cleaning;
	}

	/**
	 * Update the widget block option.
	 *
	 * @since 2.2.0
	 *
	 * @param mixed[]  $widget_block Array of current active block widgets.
	 * @param int      $key          The key of the array element.
	 * @param string[] $value        Array of block attributes.
	 * @param int      $form_id      The ID of the deleted form.
	 *
	 * @return void
	 */
	public function widget_block_updating( $widget_block, $key, $value, $form_id ) {

		$form   = 0;
		$string = implode( '', $value );

		if ( strpos( $string, 'wp:simpleform/form-selector' ) !== false ) {
			$split_content = explode( 'formId":"', $string );
			$form          = isset( $split_content[1] ) ? (int) explode( '"', $split_content[1] )[0] : 0;
		}

		if ( ( strpos( $string, 'wp:shortcode' ) && strpos( $string, '[simpleform' ) ) !== false ) {
			$split_content = explode( '[simpleform', $string );
			$form          = isset( $split_content[1] ) ? filter_var( explode( ']', $split_content[1] )[0], FILTER_SANITIZE_NUMBER_INT ) : 0;
		}

		if ( $form === $form_id ) {
			unset( $widget_block[ $key ] );
			update_option( 'widget_block', $widget_block );
		}
	}
}

new SimpleForm_Forms_Deleting();
