<?php
/**
 * File delegated to the uninstalling the plugin.
 *
 * @package SimpleForm
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$settings  = (array) get_option( 'sform_settings', array() );
$uninstall = isset( $settings['deletion_data'] ) && ! $settings['deletion_data'] ? false : true;

// Confirm user has decided to remove all data, otherwise stop.
if ( ! $uninstall ) {
	return;
}

if ( ! is_multisite() ) {

	// Search forms and remove them from content of any page or post.
	global $wpdb;
	$pages_list   = array();
	$plugin_table = $wpdb->prefix . 'sform_shortcodes';

	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $plugin_table ) ) === $plugin_table ) { // phpcs:ignore

		$form_pages = $wpdb->get_col( "SELECT form_pages FROM {$wpdb->prefix}sform_shortcodes" ); // phpcs:ignore
		foreach ( $form_pages as $form_shortcode_pages ) {
			$form_pages_ids = ! empty( $form_shortcode_pages ) ? explode( ',', $form_shortcode_pages ) : array();
			foreach ( $form_pages_ids as $shortcode_page_id ) {
				$pages_list[] = $shortcode_page_id;
			}
		}

		$ids                = array_unique( array_map( 'absint', $pages_list ) );
		$ids_count          = count( $ids );
		$placeholders_array = array_fill( 0, $ids_count, '%d' );
		$placeholders       = implode( ',', $placeholders_array );
		$posts_list         = ! empty( $ids ) ? $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_content FROM {$wpdb->posts} WHERE ID IN( {$placeholders} )", $ids ), 'ARRAY_A' ) : ''; // phpcs:ignore

		if ( $posts_list ) {
			foreach ( $posts_list as $post_item ) {
				$content = $post_item['post_content'];
				$postid  = $post_item['ID'];
				if ( has_blocks( $content ) ) {
					$plugin_block = '/<!-- wp:simpleform(.*)\/-->/';
					preg_match_all( $plugin_block, $content, $matches_block );
					if ( $matches_block ) {
						foreach ( $matches_block[0] as $block ) {
							$content = str_replace( $block, '', $content );
						}
					}
					$shortcode_block = '/<!-- wp:shortcode -->([^>]*)<!-- \/wp:shortcode -->/';
					preg_match_all( $shortcode_block, $content, $matches_shortcode );
					if ( $matches_shortcode ) {
						foreach ( $matches_shortcode[0] as $shortcode_block ) {
							if ( strpos( $shortcode_block, '[simpleform' ) !== false ) {
								$content = str_replace( $shortcode_block, '', $content );
							}
						}
					}
				}

				// Remove any shortcode not included in a block.
				$pattern = '/\[simpleform(.*?)\]/';
				preg_match_all( $pattern, $content, $matches_simpleform );
				if ( $matches_simpleform ) {
					foreach ( $matches_simpleform[0] as $shortcode ) {
						$content = str_replace( $shortcode, '', $content );
					}
				}

				$data = array(
					'ID'           => $postid,
					'post_content' => $content,
				);

				wp_update_post( $data );
			}
		}

		// Delete pre-built pages.
		$form_page_id         = isset( $settings['form_pageid'] ) ? intval( $settings['form_pageid'] ) : 0;
		$confirmation_page_id = isset( $settings['confirmation_pageid'] ) ? intval( $settings['confirmation_pageid'] ) : 0;
		if ( 0 !== $form_page_id && get_post_status( $form_page_id ) ) {
			wp_delete_post( $form_page_id, true );
		}
		if ( 0 !== $confirmation_page_id && get_post_status( $confirmation_page_id ) ) {
			wp_delete_post( $confirmation_page_id, true );
		}
		// Delete block widgets.
		global $sidebars_widgets;
		$widget_block      = get_option( 'widget_block' ) !== false ? array( get_option( 'widget_block' ) ) : array();
		$widget_simpleform = get_option( 'widget_simpleform' );
		if ( $widget_block ) {
			$pattern_block     = '/<!-- wp:simpleform(.*)\/-->/';
			$pattern_shortcode = '/\[simpleform(.*?)\]/';
			foreach ( $widget_block as $widget_key => $widget_value ) {
				if ( is_array( $widget_value ) && isset( $widget_value['content'] ) && ( preg_match( $pattern_block, $widget_value['content'] ) || preg_match( $pattern_shortcode, $widget_value['content'] ) ) ) {
					foreach ( $sidebars_widgets as $sidebar => $widgets ) {
						if ( is_array( $widgets ) ) {
							foreach ( $widgets as $key => $value ) {
								if ( strpos( $value, 'block-' . $widget_key ) !== false ) {
									unset( $sidebars_widgets[ $sidebar ][ $key ] );
									update_option( 'sidebars_widgets', $sidebars_widgets );
								}
							}
						}
					}
					unset( $widget_block[ $widget_key ] );
					update_option( 'widget_block', $widget_block );
				}
			}
		}
		// Delete options.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'sform\_%'" ); // phpcs:ignore
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'sform\-%'" ); // phpcs:ignore
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%\_sform\_%'" ); // phpcs:ignore
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name = 'widget_simpleform'" ); // phpcs:ignore
		// Remove any transients we've left behind.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE ( '%\_transient\_sform\_%')" ); // phpcs:ignore
		// Drop shortcodes table.
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sform_shortcodes' ); // phpcs:ignore
		// Drop submissions table.
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sform_submissions' ); // phpcs:ignore

	}
} else {

	global $wpdb;
	$blog_ids         = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" ); // phpcs:ignore
	$original_blog_id = get_current_blog_id();
	foreach ( $blog_ids as $blogid ) {
		switch_to_blog( $blogid );
		// Search forms and remove them from content of any page or post.
		$pages_list   = array();
		$plugin_table = $wpdb->prefix . 'sform_shortcodes';

		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $plugin_table ) ) === $plugin_table ) { // phpcs:ignore

			$form_pages = $wpdb->get_col( "SELECT form_pages FROM {$wpdb->prefix}sform_shortcodes" ); // phpcs:ignore
			foreach ( $form_pages as $form_shortcode_pages ) {
				$form_pages_ids = ! empty( $form_shortcode_pages ) ? explode( ',', $form_shortcode_pages ) : array();
				foreach ( $form_pages_ids as $shortcode_page_id ) {
					$pages_list[] = $shortcode_page_id;
				}
			}
			$ids                = array_unique( array_map( 'absint', $pages_list ) );
			$ids_count          = count( $ids );
			$placeholders_array = array_fill( 0, $ids_count, '%d' );
			$placeholders       = implode( ',', $placeholders_array );
			$posts_list         = ! empty( $ids ) ? $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_content FROM {$wpdb->posts} WHERE ID IN( $placeholders)", $ids ), 'ARRAY_A' ) : ''; // phpcs:ignore   
			if ( $posts_list ) {
				foreach ( $posts_list as $post_item ) {
					$content = $post_item['post_content'];
					$postid  = $post_item['ID'];
					if ( has_blocks( $content ) ) {
						$plugin_block = '/<!-- wp:simpleform(.*)\/-->/';
						preg_match_all( $plugin_block, $content, $matches_block );
						if ( $matches_block ) {
							foreach ( $matches_block[0] as $block ) {
								$content = str_replace( $block, '', $content );
							}
						}
						$shortcode_block = '/<!-- wp:shortcode -->([^>]*)<!-- \/wp:shortcode -->/';
						preg_match_all( $shortcode_block, $content, $matches_shortcode );
						if ( $matches_shortcode ) {
							foreach ( $matches_shortcode[0] as $shortcode_block ) {
								if ( strpos( $shortcode_block, '[simpleform' ) !== false ) {
									$content = str_replace( $shortcode_block, '', $content );
								}
							}
						}
					}
					// Remove any shortcode not included in a block.
					$pattern = '/\[simpleform(.*?)\]/';
					preg_match_all( $pattern, $content, $matches_simpleform );
					if ( $matches_simpleform ) {
						foreach ( $matches_simpleform[0] as $shortcode ) {
							$content = str_replace( $shortcode, '', $content );
						}
					}

					$data = array(
						'ID'           => $postid,
						'post_content' => $content,
					);

					wp_update_post( $data );
				}
			}

			// Delete pre-built pages.
			$form_page_id         = isset( $settings['form_pageid'] ) ? intval( $settings['form_pageid'] ) : 0;
			$confirmation_page_id = isset( $settings['confirmation_pageid'] ) ? intval( $settings['confirmation_pageid'] ) : 0;
			if ( 0 !== $form_page_id && get_post_status( $form_page_id ) ) {
				wp_delete_post( $form_page_id, true );
			}
			if ( 0 !== $confirmation_page_id && get_post_status( $confirmation_page_id ) ) {
				wp_delete_post( $confirmation_page_id, true );
			}
			// Delete block widgets.
			global $sidebars_widgets;
			$widget_block      = get_option( 'widget_block' ) !== false ? array( get_option( 'widget_block' ) ) : array();
			$widget_simpleform = get_option( 'widget_simpleform' );
			if ( $widget_block ) {
				$pattern_block     = '/<!-- wp:simpleform(.*)\/-->/';
				$pattern_shortcode = '/\[simpleform(.*?)\]/';
				foreach ( $widget_block as $widget_key => $widget_value ) {
					if ( is_array( $widget_value ) && isset( $widget_value['content'] ) && ( preg_match( $pattern_block, $widget_value['content'] ) || preg_match( $pattern_shortcode, $widget_value['content'] ) ) ) {
						foreach ( $sidebars_widgets as $sidebar => $widgets ) {
							if ( is_array( $widgets ) ) {
								foreach ( $widgets as $key => $value ) {
									if ( strpos( $value, 'block-' . $widget_key ) !== false ) {
										unset( $sidebars_widgets[ $sidebar ][ $key ] );
										update_option( 'sidebars_widgets', $sidebars_widgets );
									}
								}
							}
						}
						unset( $widget_block[ $widget_key ] );
						update_option( 'widget_block', $widget_block );
					}
				}
			}
			// Delete options.
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'sform\_%'" ); // phpcs:ignore
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'sform\-%'" ); // phpcs:ignore
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%\_sform\_%'" ); // phpcs:ignore
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name = 'widget_simpleform'" ); // phpcs:ignore
			// Remove any transients we've left behind.
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE ( '%\_transient\_sform\_%')" ); // phpcs:ignore
			// Drop shortcodes table.
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sform_shortcodes' ); // phpcs:ignore
			// Drop submissions table.
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sform_submissions' ); // phpcs:ignore

		}
	}

	switch_to_blog( $original_blog_id );

}
