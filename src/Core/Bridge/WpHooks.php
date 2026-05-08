<?php
/**
 * Wrapper for WordPress Hook system (Actions and Filters).
 *
 * @package    Triskelion\TriskelionToolkit\Core\Bridge
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Bridge;

/**
 * Class WpHooks
 *
 * Provides a formal bridge for WordPress action and filter registrations.
 * Using this bridge allows for full unit test coverage of the initialization logic.
 *
 * @package Triskelion\TriskelionToolkit\Core\Bridge
 */
class WpHooks extends AbstractWpBridge {

	/**
	 * Hooks a function on to a specific action.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_action/
	 *
	 * @param string   $hook          The name of the action to which the $callback is hooked.
	 * @param callable $callback      The callback to be run when the action is fired.
	 * @param int      $priority      Optional. Used to specify the order in which functions are executed. Default 10.
	 * @param int      $accepted_args Optional. The number of arguments the function accepts. Default 1.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function add_action( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
		if ( $this->is_wp_disabled() ) {
			return;
		}
		add_action( $hook, $callback, $priority, $accepted_args );
	}

	/**
	 * Hooks a function on to a specific filter.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_filter/
	 *
	 * @param string   $hook          The name of the filter to which the $callback is hooked.
	 * @param callable $callback      The callback to be run when the filter is applied.
	 * @param int      $priority      Optional. Used to specify the order in which functions are executed. Default 10.
	 * @param int      $accepted_args Optional. The number of arguments the function accepts. Default 1.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function add_filter( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
		if ( $this->is_wp_disabled() ) {
			return;
		}
		add_filter( $hook, $callback, $priority, $accepted_args );
	}
}
