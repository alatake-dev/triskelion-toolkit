<?php
namespace Triskelion\Toolkit\Core;

use Triskelion\Toolkit\Admin\Admin;
use Triskelion\Toolkit\Modules\CodeShowcase\CodeShowcaseLoader;
use Triskelion\Toolkit\Modules\InsightCards\InsightCardsLoader;

class Toolkit {
	private static array $loaded_instances = [];

	const TSK_ACTIVE_MODULES    = 'tsk_active_modules';
	const HOOK_REGISTER_SCRIPTS = 'tsk_register_vendor_scripts';
	const HOOK_REGISTER_STYLES  = 'tsk_register_vendor_styles';
	const TSK_VERSION           = '1.0.0';

	public static function init(): void {
		// 1. Logs de rigor
		error_log("DEBUG TSK_PATH: " . TSK_PATH);

		// 2. Agendar la carga de módulos para el momento CORRECTO
		// No la llames directo, espera al hook 'init'
		add_action('init', [self::class, 'load_active_modules']);

		// 3. Assets y Admin (esto puede quedarse aquí o en hooks)
		add_action('admin_enqueue_scripts', [self::class, 'register_vendor_assets']);
		Admin::init();

		// Filtros de estilos
		add_filter(self::HOOK_REGISTER_STYLES, function($styles) {
			$styles['tsk-admin-styles'] = [
				'src' => TSK_URL . 'assets/css/admin-layout.css',
				'ver' => self::TSK_VERSION
			];
			return $styles;
		});
	}

	public static function get_modules(): array {
		return [
			'general' => [
				'name'         => __( 'General', 'triskelion-toolkit' ),
				'description'  => 'General settings for the plugin.',
				'class'        => null,
				'has_settings' => true,
				'icon'         => 'dashicons-admin-generic'
			],
			'code_showcase' => [
				'name'         => __( 'Code Showcase', 'triskelion-toolkit' ),
				'description' => __( 'Display code snippets in a beautiful macOS-style terminal with multiple tabs and syntax highlighting.', 'triskelion-toolkit' ),
				'class'        => CodeShowcaseLoader::class,
				'has_settings' => false,
				'icon'         => 'dashicons-editor-code'
			],
			'insight_cards' => [
				'name'         => __( 'Insight Cards', 'triskelion-toolkit' ),
				'description' => __( 'Transform standard quotes into visual callouts for Tips, Warnings, and Ideas with custom styles.', 'triskelion-toolkit' ),
				'class'        => InsightCardsLoader::class,
				'has_settings' => true,
				'icon'         => 'dashicons-format-quote'
			],

		];
	}

	public static function register_vendor_assets(): void {
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
/*
	public static function load_active_modules(): void {
		error_log("📦 TRISKELION: Ejecutando carga de módulos...");

		$active_map = (array) get_option(self::TSK_ACTIVE_MODULES, []);
		$modules    = self::get_modules();

		foreach ($modules as $id => $data) {
			// 1. Validaciones de seguridad
			if (empty($data['class']) || empty($active_map[$id]) || !class_exists($data['class'])) {
				continue;
			}

			if (!isset(self::$loaded_instances[$id])) {
				$class_name = $data['class'];
				$loader     = new $class_name();

				if ($loader instanceof AbstractModuleLoader) {
					// 2. EJECUCIÓN DIRECTA
					// Aquí es donde CodeShowcaseLoader llamará a register_block_type
					$loader->load();

					self::$loaded_instances[$id] = $loader;
					error_log("✅ Módulo cargado: " . $id);
				}
			}
		}

		// 3. DEBUG POST-CARGA (Opcional, solo para confirmar en logs)
		$registry = \WP_Block_Type_Registry::get_instance()->get_block_type('triskelion/code-showcase');
		if ( $registry ) {
			error_log("📦 HANDLES FINALES: " . print_r($registry->editor_script_handles, true));
		} else {
			error_log("🚨 ERROR: Al finalizar la carga, 'triskelion/code-showcase' no está registrado.");
		}
	}
*/public static function load_active_modules(): void {
	error_log("📦 TRISKELION: Ejecutando carga de módulos...");

	$active_map = (array) get_option(self::TSK_ACTIVE_MODULES, []);
	$modules    = self::get_modules();

	foreach ($modules as $id => $data) {
		if (empty($data['class']) || empty($active_map[$id]) || !class_exists($data['class'])) {
			continue;
		}

		$class_name = $data['class'];
		if (class_exists($class_name)) {
			$loader = new $class_name();
			if ($loader instanceof AbstractModuleLoader) {
				// ESTO es lo que registra el bloque
				$loader->load();
				error_log("✅ Módulo instanciado y cargado: " . $id);
			}
		}
	}

	// NO PONGAS NADA MÁS AQUÍ QUE USE WP_Block_Type_Registry
}
	public static function get_module_instance(string $id) {
		return self::$loaded_instances[$id] ?? null;
	}
}