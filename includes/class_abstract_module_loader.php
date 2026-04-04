<?php
namespace Triskelion\Toolkit;

abstract class Abstract_Module_Loader {

	// Cada módulo debe decirnos su ID (ej: 'code-console')
	public static function get_module_id() {
		return '';
	}

	public static function init() {
		// Registro de bloques (Gutenberg)
		add_action('init', [static::class, 'register_blocks']);

		// Carga de assets (Frontend/Editor)
		add_action('wp_enqueue_scripts', [static::class, 'enqueue_assets']);
		add_action('enqueue_block_editor_assets', [static::class, 'enqueue_assets']);
	}

	public static function register_blocks() {
		// Lógica por defecto para registrar el bloque en build/index.js
		$module_id = static::get_module_id();
		if (file_exists(TSK_PATH . "build/modules/$module_id/block.json")) {
			register_block_type(TSK_PATH . "build/modules/$module_id");
		}
	}

	// Método que los hijos pueden sobreescribir si necesitan assets especiales
	public static function enqueue_assets() {}
}