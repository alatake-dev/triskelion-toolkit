<?php

namespace Triskelion\Toolkit\Core;

use Triskelion\Toolkit\Admin\Admin;
use Triskelion\Toolkit\Modules\ModuleRegistry;


class Toolkit {
	private static array $loaded_instances = [];


	public static function init(): void {
		Logger::init();

		self::ignite_modules();
		add_action( 'init', [ self::class, 'register_core_assets' ], 1 );
		add_action( 'init', [ self::class, 'load_active_modules' ], 5 );
		add_action( 'admin_enqueue_scripts', [ self::class, 'register_vendor_assets' ] );
		Admin::init();

		add_filter( HOOK_REGISTER_STYLES, function ( $styles ) {
			$styles['tsk-admin-styles'] = [
				'src' => TSK_URL . 'assets/css/admin-layout.css',
				'ver' => TSK_VERSION
			];

			return $styles;
		} );
	}

	private static function ignite_modules(): void {
		$bootstrap_path = TSK_PATH . 'src/ServiceLayer/Modules/bootstrap.php';
		if ( ! file_exists( $bootstrap_path ) ) {
			return;
		}
		$modules_map = require $bootstrap_path;
		foreach ( $modules_map as $id => $data ) {
			ModuleRegistry::register( $id, $data );
		}
	}
	public static function register_core_assets(): void {
		wp_register_script(
			TRISKELION_TOOLKIT_CORE,
			false,
			['wp-i18n'],
			TSK_VERSION,
			true
		);

		wp_set_script_translations(
			TRISKELION_TOOLKIT_CORE,
			TSK_DOMAIN,
			TSK_PATH . 'languages'
		);
	}
	public static function get_modules(): array {
		return ModuleRegistry::get_all();
	}

	public static function register_vendor_assets(): void {
		$vendor_scripts = apply_filters( HOOK_REGISTER_SCRIPTS, [] );
		foreach ( $vendor_scripts as $handle => $data ) {
			if ( empty( $data['src'] ) ) {
				continue;
			}
			wp_register_script( $handle, $data['src'], $data['deps'] ?? [], $data['ver'] ?? TSK_VERSION, true );
		}

		$vendor_styles = apply_filters( HOOK_REGISTER_STYLES, [] );
		foreach ( $vendor_styles as $handle => $data ) {
			if ( empty( $data['src'] ) ) {
				continue;
			}
			wp_register_style( $handle, $data['src'], $data['deps'] ?? [], $data['ver'] ?? TSK_VERSION );
		}
	}

	public static function load_active_modules(): void {
		Logger::debug("Ejecutando carga de módulos desde ModuleRegistry.");

		$active_map = (array) get_option( TSK_ACTIVE_MODULES, [] );
		$modules    = self::get_modules();

		foreach ( $modules as $id => $data ) {
			if ( empty( $data['class'] ) || empty( $active_map[ $id ] ) || ! class_exists( $data['class'] ) ) {
				continue;
			}

			$class_name = $data['class'];
			$loader = new $class_name( $id );

			// Si es un bloque, su load() agendará el registro en Gutenberg
			if ( $loader instanceof AbstractModuleLoader ) {
				$loader->load();
				Logger::debug("Módulo cargado con éxito: " . $id);
			}
		}
	}

	public static function get_module_instance( string $id ) {
		// Si ya existe, la devolvemos
		if ( isset( self::$loaded_instances[ $id ] ) ) {
			return self::$loaded_instances[ $id ];
		}

		$modules = self::get_modules();
		if ( ! isset( $modules[ $id ] ) ) return null;

		$class = $modules[ $id ]['class'];

		// Verificamos si la clase existe antes de instanciar
		if ( class_exists( $class ) ) {
			self::$loaded_instances[ $id ] = new $class( $id);
			return self::$loaded_instances[ $id ];
		}

		return null;
	}
}