<?php
/**
 * Wrapper for WordPress Security and Context functions.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Bridge
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Bridge;

/**
 * Class WpSecurity
 *
 * Handles WordPress context checks like is_admin or nonce validations.
 *
 * @package Triskelion\TriskelionToolkit\Core\Bridge
 */
class WpSecurity extends AbstractWpBridge {
	/**
	 * Checks if the current user has a specific capability.
	 *
	 * @see https://developer.wordpress.org/reference/functions/current_user_can/
	 *
	 * @param string $capability Capability name.
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
	 * @param string|int $action    Role-specific action name.
	 * @param string     $query_arg Optional. Name of the query variable.
	 * @return int|false 1 if valid, false on failure.
	 */
	public function check_admin_referer( string|int $action = -1, string $query_arg = '_wpnonce' ): false|int {
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
	 * @param string|int $action  Optional. Action name. Default -1.
	 * @param string     $name    Optional. Nonce name. Default '_wpnonce'.
	 * @param bool       $referrer Optional. Whether to set the referrer field for validation. Default true.
	 * @param bool       $echo     Optional. Whether to display or return hidden form field. Default true.
	 * @return string The nonce native HTML form field or mock if WP is disabled.
	 */
	public function nonce_field( $action = -1, string $name = '_wpnonce', bool $referrer = true, bool $echo = true ): string {
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
	 * @return void
	 */
	public function settings_fields( string $option_group ): void {
		if ( $this->is_wp_disabled() ) {
			echo '<input type="hidden" name="option_page" value="' . esc_attr( $option_group ) . '" />';
			return;
		}

		settings_fields( $option_group );
	}
}
