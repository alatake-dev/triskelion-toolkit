<?php
/**
 * Wrapper for WordPress Security and Context functions.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Bridge
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Bridge;

use Exception;
use WP_Error;

/**
 * Class WpSecurity
 *
 * Handles WordPress context checks like is_admin or nonce validations.
 *
 * @package Triskelion\TriskelionToolkit\Core\Bridge
 */
class WpSecurity extends AbstractWpBridge {

	/**
	 * Internal filesystem instance.
	 *
	 * @var object|null
	 */

	/**
	 * Checks if the current user has a specific capability.
	 *
	 * @see https://developer.wordpress.org/reference/functions/current_user_can/
	 *
	 * @param string $capability Capability name.
	 *
	 * @return bool
	 */
	public function current_user_can( string $capability ): bool {
		if ( $this->is_wp_disabled() ) {
			return true;
		}

		return current_user_can( $capability );
	}

	/**
	 * Verifies an admin network signaling nonce.
	 *
	 * @see https://developer.wordpress.org/reference/functions/check_admin_referer/
	 *
	 * @param string|int $action Role-specific action name.
	 * @param string     $query_arg Optional. Name of the query variable.
	 *
	 * @return int|false 1 if valid, false on failure.
	 */
	public function check_admin_referer( string|int $action = - 1, string $query_arg = '_wpnonce' ): false|int {
		if ( $this->is_wp_disabled() ) {
			return 1;
		}

		return check_admin_referer( $action, $query_arg );
	}

	/**
	 * Determines whether the current request is for an administrative interface page.
	 *
	 * @see https://developer.wordpress.org/reference/functions/is_admin/
	 *
	 * @return bool True if inside WordPress administration pages, false otherwise.
	 */
	public function is_admin(): bool {
		if ( $this->is_wp_disabled() ) {
			return true;
		}

		return is_admin();
	}

	/**
	 * Renders or returns a nonce HTML form field.
	 *
	 * Used to prevent Cross-Site Request Forgery (CSRF) attacks. When the
	 * TRISKELION_TOOLKIT_WP_DISABLED constant is true, it returns a mock hidden field.
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_nonce_field/
	 *
	 * @param string|int $action Optional. Action name. Default -1.
	 * @param string     $name Optional. Nonce name. Default '_wpnonce'.
	 * @param bool       $referrer Optional. Whether to set the referrer field for validation. Default true.
	 * @param bool       $echo Optional. Whether to display or return hidden form field. Default true.
	 *
	 * @return string The nonce native HTML form field or mock if WP is disabled.
	 */
	public function nonce_field(
		$action = - 1,
		string $name = '_wpnonce',
		bool $referrer = true,
		// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.echoFound
		bool $echo = true
	): string {
		if ( $this->is_wp_disabled() ) {
			$mock = '<input type="hidden" name="' . esc_attr( $name ) . '" value="mock_nonce" />';
			if ( $echo ) {
				echo $mock; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			return $mock;
		}

		return wp_nonce_field( $action, $name, $referrer, $echo );
	}

	/**
	 * Outputs nonce, action, and option_page fields for a settings page.
	 *
	 * This is a wrapper for the native WordPress settings_fields function.
	 * When the TRISKELION_TOOLKIT_WP_DISABLED constant is true, it renders
	 * a mock hidden field for testing purposes.
	 *
	 * @see https://developer.wordpress.org/reference/functions/settings_fields/
	 *
	 * @param string $option_group A settings group name. This should match the
	 * group name used in register_setting().
	 *
	 * @return void
	 */
	public function settings_fields( string $option_group ): void {
		if ( $this->is_wp_disabled() ) {
			echo '<input type="hidden" name="option_page" value="' . esc_attr( $option_group ) . '" />';

			return;
		}

		settings_fields( $option_group );
	}

	/**
	 * Determines the current locale for the environment.
	 *
	 * This method provides a decoupled way to retrieve the locale (e.g., 'en_US', 'es_MX').
	 * It is essential for internationalization (i18n) and for loading localized
	 * assets or external documentation links.
	 *
	 * When the TRISKELION_TOOLKIT_WP_DISABLED constant is true, it defaults to 'en_US'
	 * to ensure a predictable environment for unit testing.
	 *
	 * @see https://developer.wordpress.org/reference/functions/determine_locale/
	 *
	 * @return string The determined locale.
	 */
	public function determine_locale(): string {
		if ( $this->is_wp_disabled() ) {
			return 'en_US';
		}

		return determine_locale();
	}

	/**
	 * Loads a translation file into the textdomain.
	 *
	 * This is critical for internationalization (i18n). In a decoupled environment,
	 * it returns true to simulate a successful translation load without hitting
	 * the filesystem for .mo files.
	 *
	 * @see https://developer.wordpress.org/reference/functions/load_textdomain/
	 *
	 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
	 * @param string $mofile Path to the .mo file.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function load_textdomain( string $domain, string $mofile ): bool {
		if ( $this->is_wp_disabled() ) {
			return true;
		}

		return load_textdomain( $domain, $mofile );
	}

	/**
	 * Loads the plugin's translated strings.
	 *
	 * A wrapper for load_plugin_textdomain, which is the standard way to load
	 * translations for a WordPress plugin.
	 *
	 * @see https://developer.wordpress.org/reference/functions/load_plugin_textdomain/
	 *
	 * @param string       $domain   Unique identifier for retrieving translated strings.
	 * @param string|false $deprecated Deprecated. Use false.
	 * @param string|false $plugin_rel_path Optional. Relative path to the directory containing the .mo files.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function load_plugin_textdomain( string $domain, $deprecated = false, $plugin_rel_path = false ): bool {
		if ( $this->is_wp_disabled() ) {
			return true;
		}
		// phpcs:ignore WordPress.WP.DeprecatedParameters.Load_plugin_textdomainParam2Found
		return load_plugin_textdomain( $domain, $deprecated, $plugin_rel_path );
	}

	/**
	 * Kills WordPress execution and displays HTML error message.
	 *
	 * This is the bridge for the native wp_die function. In test mode, it
	 * throws an exception instead of terminating the script, allowing the
	 * test suite to catch and verify that the "die" condition was met.
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_die/
	 *
	 * @param string|WP_Error $message Optional. Error message.
	 * @param string          $title   Optional. Error title.
	 * @param string|array    $args    Optional. Arguments to control behavior.
	 *
	 * @throws \Exception In test mode, it throws an exception to avoid script termination.
	 * @return void
	 */
	public function wp_die( $message = '', string $title = '', $args = array() ): void {
		if ( $this->is_wp_disabled() ) {
			// Hardening: No usamos die() en tests para no matar el proceso de PHPUnit.
			throw new Exception( esc_html( 'WordPress Die Triggered: ' . $message ) );
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die( $message, $title, $args );
	}

	/**
	 * Sanitizes a string key.
	 *
	 * Keys are used for internal identifiers, settings, and CSS classes.
	 *
	 * @param string $key The key to be sanitized.
	 * @return string The sanitized key.
	 */
	public function sanitize_key( string $key ): string {
		if ( $this->is_wp_disabled() ) {
			return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );

		}
		return sanitize_key( $key );
	}
}
