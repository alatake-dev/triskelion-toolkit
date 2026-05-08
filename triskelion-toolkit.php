<?php
/**
 * Plugin Name: Triskelion Toolkit
 * Description: Modular utility suite for Triskelion.
 * Version:     1.2.1
 * Author:      Triskelion
 * License:     GPLv2 or later
 * Text Domain: triskelion-toolkit
 * Domain Path: /languages
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Triskelion\TriskelionToolkit
 */

use Triskelion\TriskelionToolkit\Core\Kernel;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * --- Path and File Constants ---
 */

/**
 * The full path to the main plugin file.
 *
 * @since 1.0.0
 */
define( 'TRISKELION_TOOLKIT_FILE', __FILE__ );

/**
 * The directory path for the plugin.
 *
 * @since 1.0.0
 */
define( 'TRISKELION_TOOLKIT_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The URL for the plugin directory.
 *
 * @since 1.0.0
 */
define( 'TRISKELION_TOOLKIT_URL', plugin_dir_url( __FILE__ ) );

/**
 * --- Version and Identifiers ---
 */

/**
 * The current version of the plugin.
 *
 * @since 1.0.0
 */
define( 'TRISKELION_TOOLKIT_VERSION', '1.2.1' );

/**
 * Core identifier for the toolkit.
 *
 * @since 1.0.0
 */
define( 'TRISKELION_TOOLKIT_CORE', 'triskelion-toolkit-core' );

/**
 * --- Database and Settings Keys ---
 */

/**
 * Option name for active modules.
 *
 * @since 1.0.0
 */
define( 'TRISKELION_TOOLKIT_ACTIVE_MODULES', 'triskelion_toolkit_active_modules' );

/**
 * Settings group identifier.
 *
 * @since 1.0.0
 */
define( 'TRISKELION_TOOLKIT_SETTINGS_GROUP', 'triskelion_toolkit_settings_group' );

/**
 * --- Framework Hooks ---
 */

/**
 * Action hook for registering vendor scripts.
 *
 * @since 1.0.0
 */
define( 'TRISKELION_TOOLKIT_REGISTER_VENDOR_SCRIPTS', 'triskelion_toolkit_register_vendor_scripts' );

/**
 * Action hook for registering core assets.
 *
 * @since 1.0.0
 */
define( 'TRISKELION_TOOLKIT_REGISTER_CORE_ASSETS', 'triskelion_toolkit_register_core_assets' );

/**
 * --- Autoloader and Execution ---
 */

// Load Composer autoloader.
if ( file_exists( TRISKELION_TOOLKIT_PATH . 'vendor/autoload.php' ) ) {
	require_once TRISKELION_TOOLKIT_PATH . 'vendor/autoload.php';
}

/**
 * Bootstrap the plugin kernel.
 *
 * @since 1.0.0
 */
add_action(
	'plugins_loaded',
	function () {
		new Kernel();
	}
);
