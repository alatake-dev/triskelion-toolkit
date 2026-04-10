<?php
namespace Triskelion\Toolkit\Core;

abstract class AbstractModuleLoader {

	abstract public function load() :void;

	// Cada módulo debe decirnos su ID (ej: 'CodeConsole')
	public static function get_module_id() {
		return '';
	}

	public static function get_module_version() {
		return TSK_VERSION;
	}
	public static function enqueue_module_assets() {
		$id = static::get_module_id();
		$version = static::get_module_version(); // <--- Aquí ocurre la "magia" static

		$relative_path = "build/modules/$id/style.css";

		if (file_exists(TSK_PATH . $relative_path)) {
			wp_enqueue_style(
				"tsk-module-$id",
				TSK_URL . $relative_path,
				[],
				$version
			);
		}
	}

	public static function init(): void {
		add_action('init', [static::class, 'register_blocks']);
		add_action('wp_enqueue_scripts', [static::class, 'enqueue_module_assets']);
		add_action('enqueue_block_editor_assets', [static::class, 'enqueue_module_assets']);
	}

	public static function register_blocks(): void {
		// Lógica por defecto para registrar el bloque en build/index.js
		$module_id = static::get_module_id();
		if (file_exists(TSK_PATH . "build/modules/$module_id/block.json")) {
			register_block_type(TSK_PATH . "build/modules/$module_id");
		}
	}

	// Método que los hijos pueden sobreescribir si necesitan assets especiales
	public static function enqueue_assets() {}

	// En la clase abstracta
	public static function enqueue_base_assets() {
		$id = static::get_module_id();
		$path = "build/modules/$id/index.css";

		if (file_exists(TSK_PATH . $path)) {
			wp_enqueue_style("tsk-module-$id", TSK_URL . $path, [], TSK_VERSION);
		}
	}

}