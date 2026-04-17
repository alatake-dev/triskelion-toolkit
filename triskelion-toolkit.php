<?php
/**
 * Plugin Name: Triskelion Toolkit
 * Description: Modular utility suite for Triskelion.
 * Version:     1.1.0
 * Author:      Triskelion
 * License:     GPLv2 or later
 * Text Domain: triskelion-toolkit
 * Domain Path: /languages
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Si alguien intenta acceder directamente al archivo, adiós.
if ( ! defined( 'ABSPATH' ) ) exit;

// --- Rutas y Archivos (Dinámicos) ---
define( 'TSK_FILE',    __FILE__ );
define( 'TSK_PATH',    plugin_dir_path( __FILE__ ) );
define( 'TSK_URL',     plugin_dir_url( __FILE__ ) );

// --- Identificadores y Versión (Estáticos) ---
define( 'TSK_DOMAIN',               'triskelion-toolkit' );
define( 'TSK_VERSION',              '1.1.0' ); // Súbele a 1.1.0 por el refactor
define( 'TRISKELION_TOOLKIT_CORE',  'triskelion-toolkit-core' );

// --- Base de Datos y Settings ---
define( 'TSK_ACTIVE_MODULES',       'tsk_active_modules' );
define( 'TSK_SETTINGS_GROUP',       'tsk_settings_group' );

// --- Hooks del Framework ---
define( 'HOOK_REGISTER_SCRIPTS',    'tsk_register_vendor_scripts' );
define( 'HOOK_REGISTER_STYLES',     'tsk_register_vendor_styles' );

define( 'TSK_LOG_LEVEL', 'debug' );
//define( 'TSK_LOG_ENABLED', true );

/* Autoloader (PSR-4 Style) */
spl_autoload_register(function ($class) {
	$prefix = 'Triskelion\\Toolkit\\';
	$base_dir = TSK_PATH . 'src/ServiceLayer/';

	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) return;

	$relative_class = substr($class, $len);

	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

	if (file_exists($file)) {
		require $file;
	}
});



add_action( 'init', function() {
	$domain = TSK_DOMAIN;
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
	if ( TSK_DOMAIN !== $domain ) {
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
	if ( TSK_DOMAIN === $domain ) {
		// Si el locale empieza con "es_" (es_MX, es_PE, es_ES, etc.)
		// forzamos a que busque simplemente "es"
		if ( str_starts_with( $locale, 'es_' ) ) {
			return 'es';
		}
	}
	return $locale;
}, 10, 2 );

add_filter( 'block_categories_all', function( $categories ) {
	return array_merge(
		$categories,
		[
			[
				'slug'  => 'triskelion',
				'title' => 'Triskelion Assets',
				'icon'  => 'shield', // Puedes usar cualquier Dashicon de WP
			],
		]
	);
} );


// Inicializar el Toolkit
Triskelion\Toolkit\Core\Toolkit::init();
