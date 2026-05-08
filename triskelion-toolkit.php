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
 */

// Si alguien intenta acceder directamente al archivo, adiós.
use Triskelion\TriskelionToolkit\Core\Kernel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// --- Rutas y Archivos (Dinámicos) ---
define( 'TRISKELION_TOOLKIT_FILE', __FILE__ );
define( 'TRISKELION_TOOLKIT_PATH', plugin_dir_path( __FILE__ ) );
define( 'TRISKELION_TOOLKIT_URL', plugin_dir_url( __FILE__ ) );

// --- Identificadores y Versión (Estáticos) ---
define( 'TRISKELION_TOOLKIT_VERSION', '1.2.1' );
define( 'TRISKELION_TOOLKIT_CORE', 'triskelion-toolkit-core' );

// --- Base de Datos y Settings ---
define( 'TRISKELION_TOOLKIT_ACTIVE_MODULES', 'triskelion_toolkit_active_modules' );
define( 'TRISKELION_TOOLKIT_SETTINGS_GROUP', 'triskelion_toolkit_settings_group' );

// --- Hooks del Framework ---
define( 'TRISKELION_TOOLKIT_REGISTER_VENDOR_SCRIPTS', 'triskelion_toolkit_register_vendor_scripts' );
define( 'TRISKELION_TOOLKIT_REGISTER_VENDOR_STYLES', 'triskelion_toolkit_register_vendor_styles' );

if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	return;
}
require_once __DIR__ . '/vendor/autoload.php';

add_action(
	'plugins_loaded',
	function () {
		$triskelion_toolkit_kernel = new Kernel();
		$triskelion_toolkit_kernel->boot();
	},
	5
);
