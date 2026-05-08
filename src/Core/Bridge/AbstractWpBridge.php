<?php
/**
 * Abstract Base for WordPress Bridge Wrappers.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Bridge
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Bridge;

/**
 * Class AbstractWpBridge
 *
 * Provides a safety mechanism to disable WordPress core execution
 * during isolated unit testing.
 */
abstract class AbstractWpBridge {

	/**
	 * Checks if WordPress execution is disabled.
	 *
	 * @return bool True if disabled via constant.
	 */
	protected function is_wp_disabled(): bool {
		return defined( 'TRISKELION_TOOLKIT_WP_DISABLED' );
	}
}
