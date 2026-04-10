<?php

namespace Triskelion\Toolkit\Modules\CustomQuotes;

use Triskelion\Toolkit\Core\AbstractModuleLoader;
/**
 * Loader para el módulo de Consola de Código.
 */
class CustomQuotesLoader extends AbstractModuleLoader{
	protected static string $module_id = 'CustomQuotes';
	/**
	 * El ID único para este módulo.
	 * Debe coincidir con la carpeta en 'build/modules/'
	 */
	public static function get_module_id(): string {
		return static::$module_id;
	}


	public function load(): void {
		// Por ahora solo un log para confirmar que el Autoloader lo encontró
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Triskelion Toolkit: Módulo custom quotes cargado correctamente.' );
		}
	}
}