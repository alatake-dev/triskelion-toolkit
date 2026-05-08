<?php
/**
 * Interface for modules that can be registered within the toolkit.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Interfaces
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Interfaces;

use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;

/**
 * Interface RegistrableModuleInterface
 *
 * Defines the contract for any module that needs to provide its own
 * configuration and internationalization strings to the core system.
 *
 * @package Triskelion\TriskelionToolkit\Core\Interfaces
 */
interface RegistrableModuleInterface {

	/**
	 * Retrieves the static configuration for the module.
	 *
	 * @return ModuleConfig The data object containing module metadata.
	 * @since 1.0.0
	 */
	public static function get_config(): ModuleConfig;

	/**
	 * Configuration of strings for internationalization (i18n).
	 *
	 * This method serves a triple purpose:
	 * 1. Acts as an "anchor" for tools like wp-cli (make-pot) to find literal
	 * strings and add them to the .pot file.
	 * 2. Provides a safe place to define UI labels, ensuring they are invoked
	 * only AFTER the 'init' or 'plugins_loaded' hooks have correctly loaded .mo files.
	 * 3. Bypasses JIT-related issues (The root cause of this specific implementation).
	 *
	 * @see https://developer.wordpress.org/plugins/internationalization/
	 *
	 * @return array<string, string> Map of translatable strings associated with the module.
	 * @since 1.0.0
	 */
	public static function i18n_config(): array;
}
