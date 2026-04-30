<?php

namespace Triskelion\TriskelionToolkit\Core;

class Init {
	public static function run() {
		add_action('plugins_loaded', [self::class, 'boot_modules'], 1);
	}

	public static function boot_modules() {
		$active_modules = get_option('triskelion_active_modules', []);

		$modules_path = dirname(__DIR__) . '/Modules';

		foreach (glob($modules_path . '/*.php') as $file) {
			$module_name = basename($file, '.php'); // Ej: "Showcase"

			$module_key = strtolower($module_name);

			if (isset($active_modules[$module_key]) && $active_modules[$module_key] === true) {

				$class = "Triskelion\\Toolkit\\Modules\\$module_name";

				if (class_exists($class)) {
					new $class();
				}
			}
		}
	}
}