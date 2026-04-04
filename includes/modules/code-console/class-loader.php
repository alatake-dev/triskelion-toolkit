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
		add_action( 'init', [ __CLASS__, 'register_blocks' ] );
		add_filter('tsk_register_vendor_assets', [__CLASS__, 'add_prism_to_map']);
	}

	public static function add_prism_to_map($scripts) {
		// Agregamos nuestra entrada al "HashMap"
		$scripts['tsk-prism'] = [
			'src'  => TSK_URL . 'assets/vendor/prism/prism.js',
			'ver'  => '1.30.0',
			'deps' => []
		];
		return $scripts;
	}

	public static function register_blocks() {
		// El espacio para el bloque de Gutenberg
	}
}