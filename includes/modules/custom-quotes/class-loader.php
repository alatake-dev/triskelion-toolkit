<?php

namespace Triskelion\Toolkit\Modules\custom_quotes;

use Triskelion\Toolkit\Abstract_Module_Loader;
/**
 * Loader para el módulo de Consola de Código.
 */
class Loader extends Abstract_Module_Loader{
	protected static $module_id = 'custom-quotes';
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
			error_log( 'Triskelion Toolkit: Módulo custom quotes cargado correctamente.' );
		}
	}


}