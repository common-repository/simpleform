<?php
/**
 * File delegated to manage the SimpleForm block.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the block.
 */
class SimpleForm_Block {

	/**
	 * The ID of this plugin.
	 *
	 * @since 2.0.4
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 2.0.4
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.0.4
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of the plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the block.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_block() {

		$asset_file = include plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

		wp_register_script(
			'sform-editor-script',
			plugins_url( 'build/index.js', __FILE__ ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true,
		);

		wp_register_style( $this->plugin_name . '-editor', plugins_url( 'build/index.css', __FILE__ ), array(), $this->version );

		// WP 5.8 loads metadata stored in the block.json file directly. Use as first parameter a path to the file.
		register_block_type(
			__DIR__ . '/block.json',
			array(
				'render_callback' => array( $this, 'sform_render_block' ),
				'editor_script'   => 'sform-editor-script',
				'editor_style'    => $this->plugin_name . '-editor',
			)
		);

		wp_set_script_translations( 'sform-editor-script', 'simpleform' );
	}

	/**
	 * Add additional functionality to the block editor
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function add_editor_features() {

		$form_data = wp_cache_get( 'forms_identifying_data' );

		// Do a database query and save it to the cache if the there is no cache data with this key.
		if ( false === $form_data ) {
			global $wpdb;
			$form_data = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}sform_shortcodes WHERE widget = '0' AND status != 'trash'", 'ARRAY_A' ); // phpcs:ignore.
			wp_cache_set( 'forms_identifying_data', $form_data );
		}

		$forms       = array_column( $form_data, 'id' );
		$empty_value = array(
			'id'   => '',
			'name' => __( 'Select an existing form', 'simpleform' ),
		);
		array_unshift( $form_data, $empty_value );
		$above_ids       = array();
		$below_ids       = array();
		$default_ids     = array();
		$basic_ids       = array();
		$rounded_ids     = array();
		$minimal_ids     = array();
		$transparent_ids = array();
		$highlighted_ids = array();
		$util            = new SimpleForm_Util();

		foreach ( $forms as $form ) {

			$text_above = $util->get_sform_option( $form, 'attributes', 'introduction_text', '' );
			$text_below = $util->get_sform_option( $form, 'attributes', 'bottom_text', '' );
			$form_style = strval( $util->get_sform_option( $form, 'settings', 'form_template', 'default' ) );

			if ( $text_above ) {
				array_push( $above_ids, $form );
			}

			if ( $text_below ) {
				array_push( $below_ids, $form );
			}

			$default_ids     = $this->block_form_style( $default_ids, 'default', $form, $form_style );
			$basic_ids       = $this->block_form_style( $basic_ids, 'basic', $form, $form_style );
			$rounded_ids     = $this->block_form_style( $rounded_ids, 'rounded', $form, $form_style );
			$minimal_ids     = $this->block_form_style( $minimal_ids, 'minimal', $form, $form_style );
			$transparent_ids = $this->block_form_style( $transparent_ids, 'transparent', $form, $form_style );
			$highlighted_ids = $this->block_form_style( $highlighted_ids, 'highlighted', $form, $form_style );

		}

		wp_localize_script(
			'sform-editor-script',
			'sformblockData',
			array(
				'forms'             => $form_data,
				'cover_url'         => plugins_url( 'img/block-preview.png', __FILE__ ),
				'logo_url'          => plugins_url( 'img/simpleform-icon.png', __FILE__ ),
				'above'             => $above_ids,
				'below'             => $below_ids,
				'default_style'     => $default_ids,
				'basic_style'       => $basic_ids,
				'rounded_style'     => $rounded_ids,
				'minimal_style'     => $minimal_ids,
				'transparent_style' => $transparent_ids,
				'highlighted_style' => $highlighted_ids,
			)
		);
	}

	/**
	 * Update the list of forms that use the same type of style
	 *
	 * @since 2.2.0
	 *
	 * @param int[]  $forms      Array of forms IDs that use the same type of style.
	 * @param string $type       The type of style.
	 * @param int    $form       The ID of the form.
	 * @param string $form_style The style used by the form.
	 *
	 * @return int[] Array of form IDs that use the same type of style.
	 */
	protected function block_form_style( $forms, $type, $form, $form_style ) {

		if ( $type === $form_style ) {
			array_push( $forms, $form );
		}

		return $forms;
	}

	/**
	 * Assign a value to block attributes
	 *
	 * @since 2.2.0
	 *
	 * @param string[]   $attributes Array of block attributes.
	 * @param string|int $form       The form used in the block.
	 * @param string     $attribute  The block attribute.
	 * @param string     $type       The ID of block attribute.
	 *
	 * @return mixed Value assigned to a block attribute.
	 */
	protected function block_attributes( $attributes, $form, $attribute, $type ) {

		if ( ! empty( $attributes[ $attribute ] ) ) {

			$filled_value = array(
				'form'              => $attributes[ $attribute ],
				'tickbox'           => true,
				'heading'           => $attributes[ $attribute ],
				'alignment'         => $attributes[ $attribute ],
				'background'        => '#form-wrap-' . $form . ' {background-color: ' . $attributes[ $attribute ] . ';}',
				'radius'            => '#form-wrap-' . $form . ' {border-radius: ' . absint( $attributes[ $attribute ] ) . 'px;}',
				'labels'            => '#form-wrap-' . $form . ' label.sform {color: ' . $attributes[ $attribute ] . ';}',
				'fieldsborder'      => '#form-' . $form . ':not(.highlighted) input, #form-' . $form . ':not(.highlighted) textarea, #form-' . $form . ':not(.highlighted) div.captcha, #form-' . $form . ':not(.highlighted) input.checkbox:not(:checked)+label .checkmark {border-color: ' . $attributes[ $attribute ] . ';} #form-' . $form . ' .rounded input.checkbox:not(:checked)+label .checkmark {background-color: ' . $attributes[ $attribute ] . ';}',
				'checkbox'          => '#form-' . $form . ' input.checkbox:checked+label .checkmark {border-color: ' . $attributes[ $attribute ] . '; background-color: ' . $attributes[ $attribute ] . ';}',
				'button'            => '#submission-' . $form . ' {background-color: ' . $attributes[ $attribute ] . ';}',
				'hoverbutton'       => '#submission-' . $form . ':hover {background-color: ' . $attributes[ $attribute ] . ';}',
				'buttonborder'      => '#submission-' . $form . ' {border-color: ' . $attributes[ $attribute ] . ';}',
				'hoverbuttonborder' => '#submission-' . $form . ':hover {border-color: ' . $attributes[ $attribute ] . ';}',
				'buttontext'        => '#submission-' . $form . ' {color: ' . $attributes[ $attribute ] . ';}',
				'hoverbuttontext'   => '#submission-' . $form . ':hover {color: ' . $attributes[ $attribute ] . ';}',
				'topmargin'         => '#form-wrap-' . $form . ' {margin-top: ' . absint( $attributes[ $attribute ] ) . 'px;}',
				'rightmargin'       => '#form-wrap-' . $form . ' {margin-right:' . absint( $attributes[ $attribute ] ) . 'px;}',
				'bottommargin'      => '#form-wrap-' . $form . ' {margin-bottom:' . absint( $attributes[ $attribute ] ) . 'px;}',
				'leftmargin'        => '#form-wrap-' . $form . ' {margin-left:' . absint( $attributes[ $attribute ] ) . 'px;}',
				'toppadding'        => '#form-wrap-' . $form . ' {padding-top:' . absint( $attributes[ $attribute ] ) . 'px;}',
				'rightpadding'      => '#form-wrap-' . $form . ' {padding-right:' . absint( $attributes[ $attribute ] ) . 'px;}',
				'bottompadding'     => '#form-wrap-' . $form . ' {padding-bottom:' . absint( $attributes[ $attribute ] ) . 'px;}',
				'leftpadding'       => '#form-wrap-' . $form . ' {padding-left:' . absint( $attributes[ $attribute ] ) . 'px;}',
				'anchor'            => '<span id="' . $attributes[ $attribute ] . '"></span>',
			);

			$value = $filled_value[ $type ];

		} else {

			$default_value = array(
				'form'              => '',
				'tickbox'           => false,
				'heading'           => 'h4',
				'alignment'         => 'align-left',
				'background'        => '',
				'radius'            => '',
				'labels'            => '',
				'fieldsborder'      => '',
				'checkbox'          => '',
				'button'            => '',
				'hoverbutton'       => '',
				'buttonborder'      => '',
				'hoverbuttonborder' => '',
				'buttontext'        => '',
				'hoverbuttontext'   => '',
				'topmargin'         => '',
				'rightmargin'       => '',
				'bottommargin'      => '',
				'leftmargin'        => '',
				'toppadding'        => '',
				'rightpadding'      => '',
				'bottompadding'     => '',
				'leftpadding'       => '',
				'anchor'            => '',
			);

			$value = $default_value[ $type ];

		}

		return $value;
	}

	/**
	 * Render a form given the specified attributes
	 *
	 * @since 2.0.0
	 *
	 * @param string[] $attributes Array of block attributes.
	 *
	 * @return string String of rendered HTML.
	 */
	public function sform_render_block( $attributes ) {

		$form_id = intval( $this->block_attributes( $attributes, '', 'formId', 'form' ) );

		if ( 0 === $form_id ) {
			return '';
		}

		$util              = new SimpleForm_Util();
		$form_attributes   = (array) $util->get_sform_option( $form_id, 'attributes', '', '' );
		$title             = $this->block_attributes( $attributes, '', 'displayTitle', 'tickbox' );
		$heading           = $this->block_attributes( $attributes, '', 'titleHeading', 'heading' );
		$alignment         = $this->block_attributes( $attributes, '', 'titleAlignment', 'alignment' );
		$description       = $this->block_attributes( $attributes, '', 'formDescription', 'tickbox' );
		$ending            = $this->block_attributes( $attributes, '', 'formEnding', 'tickbox' );
		$background        = $this->block_attributes( $attributes, $form_id, 'bgColor', 'background' );
		$borderradius      = $this->block_attributes( $attributes, $form_id, 'borderRadius', 'radius' );
		$labelcolor        = $this->block_attributes( $attributes, $form_id, 'labelColor', 'labels' );
		$fieldsborder      = $this->block_attributes( $attributes, $form_id, 'fieldsBorderColor', 'fieldsborder' );
		$checked           = $this->block_attributes( $attributes, $form_id, 'checkedColor', 'checkbox' );
		$button            = $this->block_attributes( $attributes, $form_id, 'buttonColor', 'button' );
		$hoverbutton       = $this->block_attributes( $attributes, $form_id, 'hoverButtonColor', 'hoverbutton' );
		$buttonborder      = $this->block_attributes( $attributes, $form_id, 'buttonBorderColor', 'buttonborder' );
		$hoverbuttonborder = $this->block_attributes( $attributes, $form_id, 'hoverButtonBorderColor', 'hoverbuttonborder' );
		$buttontext        = $this->block_attributes( $attributes, $form_id, 'buttonTextColor', 'buttontext' );
		$hoverbuttontext   = $this->block_attributes( $attributes, $form_id, 'hoverButtonTextColor', 'hoverbuttontext' );
		$topmargin         = $this->block_attributes( $attributes, $form_id, 'topMargin', 'topmargin' );
		$rightmargin       = $this->block_attributes( $attributes, $form_id, 'rightMargin', 'rightmargin' );
		$bottommargin      = $this->block_attributes( $attributes, $form_id, 'bottomMargin', 'bottommargin' );
		$leftmargin        = $this->block_attributes( $attributes, $form_id, 'leftMargin', 'leftmargin' );
		$toppadding        = $this->block_attributes( $attributes, $form_id, 'topPadding', 'toppadding' );
		$rightpadding      = $this->block_attributes( $attributes, $form_id, 'rightPadding', 'rightpadding' );
		$bottompadding     = $this->block_attributes( $attributes, $form_id, 'bottomPadding', 'bottompadding' );
		$leftpadding       = $this->block_attributes( $attributes, $form_id, 'leftPadding', 'leftpadding' );
		$anchor            = $this->block_attributes( $attributes, '', 'formAnchor', 'anchor' );

		$css_settings  = $background . $borderradius . $labelcolor . $fieldsborder . $checked . $button . $hoverbutton . $buttonborder . $hoverbuttonborder . $buttontext . $hoverbuttontext;
		$css_settings .= $topmargin . $rightmargin . $bottommargin . $leftmargin . $toppadding . $rightpadding . $bottompadding . $leftpadding;
		wp_add_inline_style( $this->plugin_name . '-public', $css_settings );

		// Check if form components can be shown.
		$display       = new SimpleForm_Display();
		$form_name     = $display->form_components( $form_id, $form_attributes, 'form_name', true );
		$above_form    = $display->form_components( $form_id, $form_attributes, 'introduction_text', (bool) $description );
		$below_form    = $display->form_components( $form_id, $form_attributes, 'bottom_text', (bool) $ending );
		$success_class = $display->form_components( $form_id, $form_attributes, 'success_class', true );
		$form_title    = $title ? '<' . $heading . ' class="sform align-' . $alignment . '">' . $form_name . '</' . $heading . '>' : '';

		// Edit the shortcode for the block form.
		$shortcode = 1 !== $form_id ? '[simpleform id="' . $form_id . '" type="block"]' : '[simpleform type="block"]';

		$is_gb_editor = $display->block_editor();

		if ( $is_gb_editor ) {

			$additional_style = strval( $util->get_sform_option( $form_id, 'attributes', 'additional_css', '' ) );
			$contact_form     = '<div id="form-wrap-' . $form_id . '" class="form-wrap">' . $form_title . $above_form . '<fieldset disabled>' . do_shortcode( $shortcode ) . '</fieldset>' . $below_form . '</div><style>' . $css_settings . $additional_style . '</style>';

		} else {

				$form         = do_shortcode( $shortcode );
				$contact_form = strpos( $form, __( 'SimpleForm Admin Notice', 'simpleform' ) ) !== false ? $form : '<div id="form-wrap-' . $form_id . '" class="form-wrap ' . $success_class . '">' . $anchor . $form_title . $above_form . $form . $below_form . '</div>';

		}

		return $contact_form;
	}

	/**
	 * Retrieve the form ID from nested blocks ( There is a need to have a more efficient solution! )
	 *
	 * @since 2.0.0
	 *
	 * @param string $content The post content.
	 *
	 * @return int[] Array of form IDs that were found in the content.
	 */
	public function get_sform_block_ids( $content ) {

		$ids    = array();
		$blocks = parse_blocks( $content );

		if ( $blocks ) {
			foreach ( $blocks as $block ) {
				$ids = $this->get_innerblock_form( $ids, $block );
				foreach ( $block['innerBlocks'] as $innerblock ) {
					$ids = $this->get_innerblock_form( $ids, $innerblock );
					foreach ( $innerblock['innerBlocks'] as $innerblock2 ) {
						$ids = $this->get_innerblock_form( $ids, $innerblock2 );
						foreach ( $innerblock2['innerBlocks'] as $innerblock3 ) {
							$ids = $this->get_innerblock_form( $ids, $innerblock3 );
							foreach ( $innerblock3['innerBlocks'] as $innerblock4 ) {
								$ids = $this->get_innerblock_form( $ids, $innerblock4 );
								foreach ( $innerblock4['innerBlocks'] as $innerblock5 ) {
									$ids = $this->get_innerblock_form( $ids, $innerblock5 );
									foreach ( $innerblock5['innerBlocks'] as $innerblock6 ) {
										$ids = $this->get_innerblock_form( $ids, $innerblock6 );
									}
								}
							}
						}
					}
				}
			}
		}

		return $ids;
	}

	/**
	 * Get the ID of nested form.
	 *
	 * @since 2.2.0
	 *
	 * @param int[]   $ids   Array of form IDs that were used in the content.
	 * @param mixed[] $block Array of block structure.
	 *
	 * @return int[] Array of form IDs that were used in the content.
	 */
	protected function get_innerblock_form( $ids, $block ) {

		if ( 'simpleform/form-selector' === (string) $block['blockName'] ) {
			if ( isset( $block['attrs']['formId'] ) ) {
				$ids[] = (int) $block['attrs']['formId'];
			}
		}

		return $ids;
	}

	/**
	 * Hide widget blocks if the form already appears in the post content.
	 *
	 * @since   2.0.4
	 * @version 2.1.3
	 *
	 * @param mixed[] $sidebars_widgets Array of sidebars and their widgets.
	 *
	 * @return mixed[] Array of visible sidebar widgets.
	 */
	public function hide_widgets( $sidebars_widgets ) {

		global $post;

		if ( ! is_object( $post ) ) {
			return $sidebars_widgets;
		}

		foreach ( $sidebars_widgets as $sidebar => $widgets ) {

			if ( is_array( $widgets ) && 'wp_inactive_widgets' !== $sidebar ) {

				foreach ( $widgets as $key => $value ) {

					$sidebars_widgets = $this->unset_block_widget( $sidebars_widgets, $value, $sidebar, $key );

				}
			}
		}

		return $sidebars_widgets;
	}

	/**
	 * Return cleaned sidebars if the form already appears in the post content.
	 *
	 * @version 2.2.0
	 *
	 * @param mixed[] $sidebars_widgets Array of sidebars and their widgets.
	 * @param string  $value            The unique ID of the widget.
	 * @param string  $sidebar          The ID of the dynamic sidebar.
	 * @param int     $key              The index of the dynamic sidebar.
	 *
	 * @return mixed[] Array of allowed sidebar widgets.
	 */
	public function unset_block_widget( $sidebars_widgets, $value, $sidebar, $key ) {

		if ( is_array( $sidebars_widgets ) ) {

			$util       = new SimpleForm_Util();
			$used_forms = $util->used_forms( get_the_content(), 'all' );

			if ( strpos( $value, 'block-' ) !== false ) {

				$block_id     = intval( substr( $value, 6 ) );
				$widget_block = (array) get_option( 'widget_block', array() );

				foreach ( $widget_block as $block_key => $block_value ) {

					if ( $block_key === $block_id ) {

						$block_content = implode( '', (array) $block_value );
						// Search the form ID in the block.
						$form = $util->block_form_id( $block_content );
						if ( in_array( $form, $used_forms, true ) ) {
							unset( $sidebars_widgets[ $sidebar ][ $key ] );
						}
					}
				}
			}

			if ( strpos( $value, 'simpleform-' ) !== false ) {

				$widget_id    = intval( substr( $value, 11 ) );
				$sform_widget = get_option( 'widget_simpleform' );

				if ( is_array( $sform_widget ) && in_array( $sform_widget[ $widget_id ]['form'], $used_forms, true ) ) {
					unset( $sidebars_widgets[ $sidebar ][ $key ] );
				}
			}
		}

		return $sidebars_widgets;
	}

	/**
	 * Add the theme support to load the form's stylesheet in the editor
	 *
	 * @since 2.1.8.1
	 *
	 * @return void
	 */
	public function editor_styles_support() {

		add_theme_support( 'editor-styles' );
	}

	/**
	 * Add the form's stylesheet to use in the editor
	 *
	 * @since 2.1.8.1
	 *
	 * @return void
	 */
	public function add_editor_styles() {

		$util       = new SimpleForm_Util();
		$stylesheet = $util->get_sform_option( 1, 'settings', 'stylesheet', false );
		$cssfile    = $util->get_sform_option( 1, 'settings', 'stylesheet_file', false );

		if ( ! $stylesheet ) {
			add_editor_style( plugins_url( 'simpleform/public/css/public-min.css' ) );
		} else {
			add_editor_style( plugins_url( 'simpleform/public/css/simpleform-style.css' ) );
			if ( $cssfile && file_exists( get_theme_file_path( '/simpleform/custom-style.css' ) ) ) {
				add_editor_style( get_theme_file_uri( 'simpleform/custom-style.css' ) );
			}
		}
	}
}
