<?php
namespace Triskelion\Toolkit;

class Toolkit {

	const TSK_ACTIVE_MODULES = 'tsk_active_modules';

	public static function get_modules() {
		// cualquier nuevo módulo debe agregar una entrada aquí
		return [
			'code-console'   => [
				'name'  => __( 'Code Console', 'triskelion-toolkit' ),
				'class' => 'Triskelion\\Toolkit\\Modules\\CodeConsole\\Loader'
			],
			'custom-quotes'  => [
				'name'  => __( 'Custom Quotes', 'triskelion-toolkit' ),
				'class' => 'Triskelion\\Toolkit\\Modules\\CustomQuotes\\Loader'
			],
		];
	}

    public static function init() {
	    error_log("Toolkit activado");
	    Admin::init();
	    self::load_active_modules();

    }

	private static function load_active_modules() {
		// 1. Obtenemos lo que el usuario guardó en el Admin
		// Ojo: Asegúrate de que el nombre coincida con el que pusiste en Admin::register_settings
		$active_map = (array) get_option(Toolkit::TSK_ACTIVE_MODULES, []);

		// 2. Obtenemos la lista de módulos disponibles
		$modules = self::get_modules();

		// 3. El ciclo de carga (puedes usar el foreach o el array_walk que vimos)
		foreach ( $modules as $id => $data ) {
			// Si está checkeado en el admin y la clase existe físicamente...
			if ( !empty($active_map[$id]) && class_exists($data['class']) ) {
				// ...llamamos al init() del Loader del módulo (ej: CodeConsole\Loader::init)
				$data['class']::init();
			}
		}
	}
	public static function register_vendor_assets() {
		// Creamos un array que actuará como nuestro "HashMap"
		$vendor_scripts = [];

		// Lanzamos un filtro para que otros módulos llenen este mapa
		// 'tsk_register_vendor_assets' es el nombre de nuestro evento personalizado
		$vendor_scripts = apply_filters('tsk_register_vendor_assets', $vendor_scripts);

		// Ahora iteramos nuestro "HashMap" para registrarlos en WP
		foreach ($vendor_scripts as $handle => $data) {
			wp_register_script($handle, $data['src'], $data['deps'] ?? [], $data['ver'] ?? '1.0', true);
		}
	}


}