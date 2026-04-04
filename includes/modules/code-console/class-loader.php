<?php
namespace Triskelion\Toolkit\Modules\CodeConsole;

use Triskelion\Toolkit\Abstract_Module_Loader;
/**
 * Loader para el módulo de Consola de Código.
 */
class Loader extends Abstract_Module_Loader{
	protected static $module_id = 'code-console';
	/**
	 * El ID único para este módulo.
	 * Debe coincidir con la carpeta en 'build/modules/'
	 */
	public static function get_module_id() {
		return static::$module_id;
	}


	public static function init() {
		// Por ahora solo un log para confirmar que el Autoloader lo encontró
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Triskelion Toolkit: Módulo Code Console cargado correctamente.' );
		}

		// Aquí irá el register_block_type de la consola más adelante
		add_action( 'init', [ __CLASS__, 'register_blocks' ] );
	}

	/**
	 * Sobreescribimos para cargar los estilos de Prism
	 * solo cuando este módulo esté activo.
	 */
	public static function enqueue_assets() {
		// Por ahora registramos, luego encolaremos selectivamente
		wp_register_style(
			'tsk-prism-theme',
			TSK_URL . 'assets/vendor/prism/prism.css',
			[],
			'1.30.0'
		);

		error_log("Triskelion: Assets de Consola registrados.");
	}

	public static function register_blocks() {
		// El espacio para el bloque de Gutenberg
	}
}