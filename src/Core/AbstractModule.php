<?php
/**
 * Abstract Base Module class.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Abstracts
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core;

use Triskelion\TriskelionToolkit\Core\Bridge\WpBridge;

/**
 * Class AbstractModule
 *
 * @package Triskelion\TriskelionToolkit\Core\Abstracts
 */
abstract class AbstractModule {

	/**
	 * WordPress Bridge instance.
	 *
	 * @var WpBridge|null
	 */
	protected ?WpBridge $wp = null;

	/**
	 * Retrieves the WordPress Bridge instance.
	 * * Ensures the bridge is instantiated only once (Singleton-like behavior within the module context).
	 *
	 * @return WpBridge The initialized WordPress Bridge.
	 * @since 1.0.0
	 */
	protected function wp(): WpBridge {
		if ( null === $this->wp ) {
			$this->wp = new WpBridge();
		}

		return $this->wp;
	}

	/**
	 * AbstractModule constructor.
	 *
	 * The constructor serves as the entry point for the module lifecycle.
	 * It automatically triggers the internal registration process that
	 * every concrete module must implement.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->wp();
		$this->register();
	}

	/**
	 * Registers the module's core functionality.
	 *
	 * This abstract method must be implemented by concrete classes to define
	 * their specific hooks, actions, filters, or initialization logic.
	 * It is called automatically by the constructor.
	 *
	 * @see https://developer.wordpress.org/plugins/hooks/
	 *
	 * @return void
	 * @since  1.0.0
	 */
	abstract protected function register(): void;

	/**
	 * Sets the WordPress Bridge instance.
	 *
	 * This setter allows for injecting a mock or a specific instance of the WpBridge,
	 * which is essential for decoupling the manager from global WordPress functions
	 * during unit testing.
	 *
	 * @param WpBridge $wp The WordPress Bridge instance.
	 * @since 1.0.0
	 */
	public function set_wp( WpBridge $wp ): void {
		$this->wp = $wp;
	}
}
