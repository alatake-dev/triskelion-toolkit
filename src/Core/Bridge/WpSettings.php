<?php
/**
 * Wrapper for WordPress Settings and Options functions.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Bridge
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Bridge;

/**
 * Class WpSettings
 *
 * Concentrates all WordPress option-related calls.
 *
 * @package Triskelion\TriskelionToolkit\Core\Bridge
 */
class WpSettings extends AbstractWpBridge {

	/**
	 * Retrieves an option value based on an option name.
	 * FF Logic: Returns the default param to allow tests to verify hook-dependent logic.
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_option/
	 *
	 * @param string $option  Name of the option to retrieve.
	 * @param mixed  $default Optional. Default value to return if the option does not exist.
	 * @return mixed Value set for the option.
	 */
	public function get_option( string $option, $default = false ) {
		if ( $this->is_wp_disabled() ) {
			return $default;
		}
		return get_option( $option, $default );
	}

	/**
	 * Updates the value of an option that was already added.
	 * FF Logic: Returns true to allow tests to verify hook-dependent logic.
	 *
	 * @see https://developer.wordpress.org/reference/functions/update_option/
	 *
	 * @param string      $option   Name of the option to update.
	 * @param mixed       $value    The new value of the option.
	 * @param string|bool $autoload Optional. Whether to load the option when WordPress starts up.
	 * @return bool True if the value was updated, false otherwise.
	 */
	public function update_option( string $option, mixed $value, string|bool $autoload = null ): bool {
		if ( $this->is_wp_disabled() ) {
			return true;
		}
		return update_option( $option, $value, $autoload );
	}

	/**
	 * Bridge: Merges user-defined arguments with defaults.
	 *
	 * @param array|string|object $args     Value to merge with $defaults.
	 * @param array               $defaults The list of defaults.
	 * @return array
	 */
	public function parse_args( $args, array $defaults = array() ): array {
		if ( $this->is_wp_disabled() ) {
			// En modo Test, simulamos el merge básico de arrays.
			return array_merge( $defaults, (array) $args );
		}
		return wp_parse_args( $args, $defaults );
	}
}
