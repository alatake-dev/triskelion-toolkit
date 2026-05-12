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
	 *
	 * @return mixed Value set for the option.
	 */
	public function get_option(
		string $option,
		// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound
		mixed $default = false
	): mixed {
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

	/**
	 * Bridge for wp_upload_dir().
	 *
	 * @return array{path: string, url: string, ...} Directory data or mock paths if disabled.
	 */
	public function upload_dir(): array {
		if ( $this->is_wp_disabled() ) {
			return array(
				'path'    => '/tmp/triskelion-tests',
				'basedir' => '/tmp/triskelion-tests',
				'url'     => 'https://example.com/wp-content/uploads',
			);
		}
		return wp_upload_dir();
	}

	/**
	 * Normalizes a given path by replacing backslashes with forward slashes.
	 *
	 * @param string $path The path to normalize.
	 *
	 * @return string Normalized path.
	 */
	public function normalize_path( string $path ): string {
		if ( $this->is_wp_disabled() ) {
			return str_replace( '\\', '/', $path );
		}
		return wp_normalize_path( $path );
	}

	/**
	 * Registers a setting and its sanitization callback.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_setting/
	 *
	 * @param string $option_group A settings group name.
	 * @param string $option_name  The name of an option to sanitize and save.
	 * @param array  $args         Data used to describe the setting when registered.
	 *
	 * @return void
	 */
	public function register_setting( string $option_group, string $option_name, array $args = array() ): void {
		if ( $this->is_wp_disabled() ) {
			return;
		}
		register_setting( $option_group, $option_name, $args );
	}

	/**
	 * Gets the basename of a plugin.
	 *
	 * @param string $file The filename of the plugin.
	 *
	 * @return string The basename of the plugin.
	 */
	public function plugin_basename( string $file ): string {
		if ( $this->is_wp_disabled() ) {
			return basename( $file, '.php' );
		}
		return plugin_basename( $file );
	}

	/**
	 * Retrieves the URL to the admin area for the current site.
	 *
	 * @param string      $path   Optional. Path relative to the admin URL.
	 * @param string|null $scheme The scheme to use. Default is 'admin'.
	 *
	 * @return string Admin area URL with path appended.
	 */
	public function admin_url( string $path = '', ?string $scheme = 'admin' ): string {
		if ( $this->is_wp_disabled() ) {
			return 'https://example.com/wp-admin/' . ltrim( $path, '/' );
		}
		return admin_url( $path, $scheme );
	}

	/**
	 * Gets the URL directory path (with trailing slash) for the plugin.
	 *
	 * @param string $file The filename of the plugin (usually TRISKELION_TOOLKIT_FILE).
	 *
	 * @return string The URL for the plugin directory.
	 */
	public function plugin_dir_url( string $file ): string {
		if ( $this->is_wp_disabled() ) {
			return 'https://example.com/wp-content/plugins/triskelion-toolkit/';
		}
		return plugin_dir_url( $file );
	}

	/**
	 * Gets the filesystem directory path (with trailing slash) for the plugin.
	 *
	 * Used primarily for including files or locating assets on the server.
	 * In a decoupled environment, it returns a generic path based on the filename
	 * to prevent path disclosure or filesystem errors during testing.
	 *
	 * @see https://developer.wordpress.org/reference/functions/plugin_dir_path/
	 *
	 * @param string $file The filename of the plugin.
	 *
	 * @return string The filesystem path for the plugin directory.
	 */
	public function plugin_dir_path( string $file ): string {
		if ( $this->is_wp_disabled() ) {
			return '/var/www/html/wp-content/plugins/' . basename( $file, '.php' ) . '/';
		}

		return plugin_dir_path( $file );
	}
}
