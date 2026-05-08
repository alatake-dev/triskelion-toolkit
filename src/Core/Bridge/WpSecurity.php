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
}
