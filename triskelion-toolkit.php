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
define( 'TSK_FILE', __FILE__ );
define( 'TSK_PATH', plugin_dir_path( __FILE__ ) );
define( 'TSK_URL', plugin_dir_url( __FILE__ ) );

// --- Identificadores y Versión (Estáticos) ---
define( 'TSK_VERSION', '1.2.1' );
define( 'TRISKELION_TOOLKIT_CORE', 'triskelion-toolkit-core' );

// --- Base de Datos y Settings ---
define( 'TSK_ACTIVE_MODULES', 'tsk_active_modules' );
define( 'TSK_SETTINGS_GROUP', 'tsk_settings_group' );

// --- Hooks del Framework ---
define( 'HOOK_REGISTER_SCRIPTS', 'tsk_register_vendor_scripts' );
define( 'HOOK_REGISTER_STYLES', 'tsk_register_vendor_styles' );


/* Autoloader (PSR-4 Style) */
/*

add_action( 'init', function() {
	$domain = 'triskelion-toolkit';
	$locale = get_locale(); // Supongamos que es 'es_PE'

	load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	if ( ! is_textdomain_loaded( $domain ) && str_starts_with( $locale, 'es_' ) ) {
		$lang_base = substr( $locale, 0, 2 );
		$mofile = plugin_dir_path( __FILE__ ) . "languages/$domain-$lang_base.mo";

		if ( file_exists( $mofile ) ) {
			load_textdomain( $domain, $mofile );
		}
	}
}, 5 );

add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	// Solo actuamos sobre nuestro dominio
	if ( 'triskelion-toolkit' !== $domain ) {
		return $file;
	}

	$locale = determine_locale();

	// Si es cualquier español (es_MX, es_ES, es_AR), forzamos a buscar el archivo 'es'
	if ( str_starts_with( $locale, 'es_' ) && file_exists( $file ) === false ) {
		// Reemplazamos es_MX (o lo que sea) por "es" en la ruta del archivo
		$new_file = str_replace( "-$locale-", "-es-", $file );

		if ( file_exists( $new_file ) ) {
			return $new_file;
		}
	}

	return $file;
}, 10, 3 );

add_filter( 'plugin_locale', function( $locale, $domain ) {
	error_log( 'Plugin Locale: ' . $domain . ' | ' . $locale );
	if ( 'triskelion-toolkit' === $domain ) {
		error_log( "TRISKELION DEBUG: Locale detectado -> $locale" );
		// Si es 'es' o 'es_MX' o 'es_ES', forzamos 'es'
		if ( $locale === 'es' || str_starts_with( $locale, 'es_' ) ) {
			return 'es';
		}
	}
	return $locale;
}, 10, 2 );
*/


if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	return;
}
require_once __DIR__ . '/vendor/autoload.php';

add_action( 'plugins_loaded', function () {
	$tsk_kernel = new Kernel();
	$tsk_kernel->boot();
}, 5 );

error_log( 'Terminó la carga del plugin' );