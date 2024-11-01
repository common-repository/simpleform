<?php
/**
 * File delegated to register all the hooks for the plugin.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with all actions and filters for the plugin.
 */
class SimpleForm_Loader {

	/**
	 *
	 * The array of actions registered with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    mixed[]  $actions The actions registered with WordPress to fire when the plugin loads.
	 */

	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    mixed[]  $filters The filters registered with WordPress to fire when the plugin loads.
	 */

	protected $filters;

	/**
	 * The array of shortcodes registered with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    mixed[]  $shortcodes The shortcodes registered with WordPress to fire when the plugin loads.
	 */

	protected $shortcodes;

	/**
	 * Initialize the collections used to maintain the actions, filters and shortcodes.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->actions    = array();
		$this->filters    = array();
		$this->shortcodes = array();
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook          The name of the WordPress action that is being registered.
	 * @param object $component     A reference to the instance of the object on which the action is defined.
	 * @param string $callback      The name of the function definition on the $component.
	 * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
	 * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 *
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook          The name of the WordPress filter that is being registered.
	 * @param object $component     A reference to the instance of the object on which the filter is defined.
	 * @param string $callback      The name of the function definition on the $component.
	 * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
	 * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 *
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new shortcode to the collection to be registered with WordPress
	 *
	 * @since 1.0.0
	 *
	 * @param string $tag           The name of the new shortcode.
	 * @param object $component     A reference to the instance of the object on which the shortcode is defined.
	 * @param string $callback      The name of the function that defines the shortcode.
	 * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
	 * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 *
	 * @return void
	 */
	public function add_shortcode( $tag, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->shortcodes = $this->add( $this->shortcodes, $tag, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single collection.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  mixed[] $hooks         The collection of hooks that is being registered (that is, actions, filters or shortcodes).
	 * @param  string  $hook          The name of the WordPress filter that is being registered.
	 * @param  object  $component     A reference to the instance of the object on which the filter is defined.
	 * @param  string  $callback      The name of the function definition on the $component.
	 * @param  int     $priority      The priority at which the function should be fired.
	 * @param  int     $accepted_args The number of arguments that should be passed to the $callback.
	 *
	 * @return mixed[] The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;
	}

	/**
	 * Make callable a parameter of the functions.
	 *
	 * @param mixed $component A reference to the instance of the object on which the filter is defined.
	 * @param mixed $callback  The name of the function definition on the $component.
	 *
	 * @since 2.2.0
	 *
	 * @return callable|mixed
	 */
	public function callable_for( $component, $callback ) {

		return array( $component, $callback );
	}

	/**
	 * Register the actions, filters and shortcodes with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], $this->callable_for( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], $this->callable_for( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->shortcodes as $hook ) {
			add_shortcode( $hook['hook'], $this->callable_for( $hook['component'], $hook['callback'] ) );
		}
	}
}
