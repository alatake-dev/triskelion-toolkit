<?php

namespace Triskelion\TriskelionToolkit\Core;

class Kernel {
	private static $active_services = [];

	public static function boot(): void {
		$settings = get_option('triskelion_settings', []);
		self::$active_services = $settings['modules'] ?? [];

		add_action('plugins_loaded', [self::class, 'init_services'], 1);
	}

	public static function init_services(): void {
		// Escaneamos la carpeta ServiceLayer
		$services_dir = dirname(__DIR__) . '/ServiceLayer';

		foreach (glob($services_dir . '/*', GLOB_ONLYDIR) as $dir) {
			$service_name = basename($dir); // Ej: "Showcase"
			$service_key = strtolower($service_name);

			if (!empty(self::$active_services[$service_key])) {
				$class = "Alatake\\Toolkit\\ServiceLayer\\{$service_name}\\{$service_name}Service";

				if (class_exists($class)) {
					new $class();
				}
			}
		}
	}
}