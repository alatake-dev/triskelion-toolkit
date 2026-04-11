<?php
namespace Triskelion\Toolkit\Core;

use Triskelion\Toolkit\Admin\Admin;

class Toolkit {
	private static array $loaded_instances = [];

	const TSK_ACTIVE_MODULES    = 'tsk_active_modules';
	const HOOK_REGISTER_SCRIPTS = 'tsk_register_vendor_scripts';
	const HOOK_REGISTER_STYLES  = 'tsk_register_vendor_styles';
	const TSK_VERSION           = '1.0.0';

	public static function init() {
		// Registro de Assets Globales del Plugin
		add_action('admin_enqueue_scripts', [self::class, 'register_vendor_assets']);

		// Registrar el CSS del Admin Layout mediante nuestro propio sistema de filtros
		add_filter(self::HOOK_REGISTER_STYLES, function($styles) {
			$styles['tsk-admin-styles'] = [
				'src' => TSK_URL . 'assets/css/admin-layout.css',
				'ver' => self::TSK_VERSION
			];
			return $styles;
		});

		Admin::init();
		self::load_active_modules();
	}

	public static function get_modules() {
		return [
			'general' => [
				'name'         => __( 'General', 'triskelion-toolkit' ),
				'description'  => 'General settings for the plugin.',
				'class'        => null,
				'has_settings' => true,
				'icon'         => 'dashicons-admin-generic'
			],
		];
	}

	public static function register_vendor_assets() {
		$vendor_scripts = apply_filters(self::HOOK_REGISTER_SCRIPTS, []);
		foreach ($vendor_scripts as $handle => $data) {
			if (empty($data['src'])) continue;
			wp_register_script($handle, $data['src'], $data['deps'] ?? [], $data['ver'] ?? self::TSK_VERSION, true);
		}

		$vendor_styles = apply_filters(self::HOOK_REGISTER_STYLES, []);
		foreach ($vendor_styles as $handle => $data) {
			if (empty($data['src'])) continue;
			wp_register_style($handle, $data['src'], $data['deps'] ?? [], $data['ver'] ?? self::TSK_VERSION);
		}
	}

	private static function load_active_modules(): void {
		$active_map = (array) get_option(self::TSK_ACTIVE_MODULES, []);
		$modules    = self::get_modules();

		foreach ($modules as $id => $data) {
			if (empty($data['class']) || empty($active_map[$id]) || !class_exists($data['class'])) {
				continue;
			}

			if (!isset(self::$loaded_instances[$id])) {
				$class_name = $data['class'];
				$loader     = new $class_name();

				if ($loader instanceof AbstractModuleLoader) {
					$loader->load();
					self::$loaded_instances[$id] = $loader;
				}
			}
		}
	}

	public static function get_module_instance(string $id) {
		return self::$loaded_instances[$id] ?? null;
	}
}