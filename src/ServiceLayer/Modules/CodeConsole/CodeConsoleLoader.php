<?php
namespace Triskelion\Toolkit\Modules\CodeConsole;

use Triskelion\Toolkit\Core\AbstractModuleLoader;
/**
 * Loader para el módulo de Consola de Código.
 */
class CodeConsoleLoader extends AbstractModuleLoader{
	protected static string $module_id = 'CodeConsole';
	/**
	 * El ID único para este módulo.
	 * Debe coincidir con la carpeta en 'build/modules/'
	 */
	public static function get_module_id(): string {
		return static::$module_id;
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

	public static function register_blocks(): void {
		// El espacio para el bloque de Gutenberg
	}

	public function load(): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Triskelion Toolkit: Módulo Code Console cargado correctamente.' );
		}
		add_action( 'init', [ $this, 'register_blocks' ] );
		add_filter('tsk_register_vendor_assets', [$this, 'add_prism_to_map']);
	}
}