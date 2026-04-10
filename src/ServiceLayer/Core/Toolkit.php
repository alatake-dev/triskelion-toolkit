<?php
namespace Triskelion\Toolkit\Core;

use Triskelion\Toolkit\Admin\Admin;

class Toolkit {

	private static $loaded_instances = [];
	const TSK_ACTIVE_MODULES = 'tsk_active_modules';

	public static function get_modules() {
		return [
			'general' => [
				'name'     => __( 'General Settings', 'triskelion-toolkit' ),
				'class'    => null,
				'has_settings' => true,
				'icon'     => 'dashicons-admin-generic'
			],
			'code_console' => [
				'name'     => __( 'Code Console', 'triskelion-toolkit' ),
				'class'    => \Triskelion\Toolkit\Modules\CodeConsole\CodeConsoleLoader::class,
				'has_settings' => true, // Este llevará su propia config
				'icon'     => 'dashicons-editor-code'
			],
			'custom_quotes' => [
				'name'     => __( 'Custom Quotes', 'triskelion-toolkit' ),
				'class'    => \Triskelion\Toolkit\Modules\CustomQuotes\CustomQuotesLoader::class,
				'has_settings' => false, // Quizás este solo se enciende y ya
				'icon'     => 'dashicons-format-quote'
			],
		];
	}

    public static function init() {
	    error_log("Toolkit activado");
	    Admin::init();
	    self::load_active_modules();

    }

	private static function load_active_modules(): void {
		$active_map = (array) get_option( self::TSK_ACTIVE_MODULES, [] );
		$modules = self::get_modules(); // Tu universo de módulos

		foreach ( $modules as $id => $data ) {
			if ( empty( $active_map[$id] ) || ! class_exists( $data['class'] ) ) {
				continue;
			}

			if ( ! isset( self::$loaded_instances[$id] ) ) {
				$class_name = $data['class'];
				$loader = new $class_name();

				if ( $loader instanceof \Triskelion\Toolkit\Core\AbstractModuleLoader ) {
					$loader->load();
					self::$loaded_instances[$id] = $loader; // Guardamos la referencia
				}
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