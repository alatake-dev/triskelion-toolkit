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
class WpEvents extends AbstractWpBridge {

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

	/**
	 * Event Bridge: Localizes a registered script with data.
	 *
	 * Bridges server-side PHP data to client-side JavaScript by creating a
	 * global object. When the Feature Flag 'is_wp_disabled' is active,
	 * the execution is bypassed to allow unit testing of loaders without
	 * requiring the WordPress script enqueueing system.
	 *
	 * @param string $handle Name of the script to attach data to.
	 * @param string $object_name Name of the variable in the JS global scope.
	 * @param array  $data Associative array of data to be localized.
	 * @return bool True on success, false on failure or if FF is active.
	 */
	public function localize_script( string $handle, string $object_name, array $data ): bool {
		if ( $this->is_wp_disabled() ) {
			return ! empty( $data );
		}
		return wp_localize_script( $handle, $object_name, $data );
	}

	/**
	 * Event Bridge: Enqueues a CSS stylesheet.
	 *
	 * Registers and injects a stylesheet into the execution queue. When the
	 * Feature Flag is active, the operation is bypassed to maintain unit
	 * test isolation.
	 *
	 * @param string      $handle Name of the stylesheet.
	 * @param string      $src    Full URL of the stylesheet.
	 * @param string[]    $deps   Array of registered stylesheet handles this stylesheet depends on.
	 * @param bool|string $ver    String specifying stylesheet version number.
	 * @param string      $media  The media for which this stylesheet has been defined.
	 *
	 * @return void
	 */
	public function enqueue_style( string $handle, string $src = '', array $deps = array(), bool|string $ver = false, string $media = 'all' ): void {
		if ( $this->is_wp_disabled() ) {
			return;
		}
		wp_enqueue_style( $handle, $src, $deps, $ver, $media );
	}

	/**
	 * Enqueues a script file if WordPress integration is enabled.
	 *
	 * This is a bridge method for the native wp_enqueue_script function.
	 * It checks the 'is_wp_disabled' status (Feature Flag) before registering
	 * the asset in the WordPress queue.
	 *
	 * @param string          $handle    Name of the script. Should be unique.
	 * @param string          $src       Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param array           $deps      Optional. An array of registered script handles this script depends on.
	 * @param string|bool|int $ver       Optional. String specifying script version number, if it has one.
	 * @param array|bool      $args      Optional. An array of extra arguments, or a boolean for in_footer.
	 * * @return void
	 */
	public function enqueue_script(
		string $handle,
		string $src = '',
		array $deps = array(),
		$ver = false,
		$args = false
	): void {
		if ( $this->is_wp_disabled() ) {
			return;
		}

		wp_enqueue_script( $handle, $src, $deps, $ver, $args );
	}
}
