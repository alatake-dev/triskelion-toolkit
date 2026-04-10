<?php
/**
 * Plugin Name: Triskelion Toolkit
 * Description: Modular utility suite for Triskelion.
 * Version:     0.0.1
 * Author:      Triskelion
 * License:     GPLv2 or later
 * Text Domain: triskelion-toolkit
 * Domain Path: /languages
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Si alguien intenta acceder directamente al archivo, adiós.
if ( ! defined( 'ABSPATH' ) ) exit;

// Definir constantes de ruta
define( 'TSK_PATH', plugin_dir_path( __FILE__ ) );
define( 'TSK_URL',  plugin_dir_url( __FILE__ ) );
const TSK_VERSION = '0.0.1';

require_once TSK_PATH . 'includes/class-autoloader.php';

/*
add_action( 'init', function() {
	load_plugin_textdomain( 'triskelion-toolkit', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
});
*/

add_action( 'init', function() {
	$domain = 'triskelion-toolkit';
	$locale = get_locale(); // Supongamos que es 'es_PE'

	// 1. Intentamos la carga estándar (buscará triskelion-toolkit-es_PE.mo)
	load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	// 2. Si WP falló (no cargó nada) y el idioma es español de cualquier país...
	if ( ! is_textdomain_loaded( $domain ) && strpos( $locale, 'es_' ) === 0 ) {
		$lang_base = substr( $locale, 0, 2 );
		$mofile = plugin_dir_path( __FILE__ ) . "languages/{$domain}-{$lang_base}.mo";

		if ( file_exists( $mofile ) ) {
			load_textdomain( $domain, $mofile );
		}
	}
}, 5 );

add_filter( 'plugin_locale', function( $locale, $domain ) {
	if ( 'triskelion-toolkit' === $domain ) {
		// Si el locale empieza con "es_" (es_MX, es_PE, es_ES, etc.)
		// forzamos a que busque simplemente "es"
		if ( 0 === strpos( $locale, 'es_' ) ) {
			return 'es';
		}
	}
	return $locale;
}, 10, 2 );

// Inicializar el Toolkit
\Triskelion\Toolkit\Toolkit::init();
