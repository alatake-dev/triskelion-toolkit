<?php

namespace Triskelion\Toolkit\Core;

use Triskelion\Toolkit\Admin\Admin;


class Toolkit {
	private static array $loaded_instances = [];

	const TSK_ACTIVE_MODULES = 'tsk_active_modules';
	const HOOK_REGISTER_SCRIPTS = 'tsk_register_vendor_scripts';
	const HOOK_REGISTER_STYLES = 'tsk_register_vendor_styles';
	const TSK_VERSION = '1.0.0';

	public static function init(): void {

		add_action( 'init', [ self::class, 'register_core_assets' ], 1 );

		add_action( 'init', [ self::class, 'load_active_modules' ], 5 );

		add_action( 'admin_enqueue_scripts', [ self::class, 'register_vendor_assets' ] );
		Admin::init();

		add_filter( self::HOOK_REGISTER_STYLES, function ( $styles ) {
			$styles['tsk-admin-styles'] = [
				'src' => TSK_URL . 'assets/css/admin-layout.css',
				'ver' => self::TSK_VERSION
			];

			return $styles;
		} );
	}
	public static function register_core_assets(): void {
		wp_register_script(
			'triskelion-toolkit-core',
			false,
			['wp-i18n'],
			self::TSK_VERSION,
			true
		);

		wp_set_script_translations(
			'triskelion-toolkit-core',
			TSK_DOMAIN,
			TSK_PATH . 'languages'
		);
	}
	public static function get_modules(): array {
		return [
			'general_settings' => [
				'name'         => __( 'General Settings', TSK_DOMAIN ),
				'description'  => 'General settings for the plugin.',
				'class'        => \Triskelion\Toolkit\Modules\GeneralSettings\GeneralSettingsLoader::class,
				'has_settings' => true,
				'is_core'      => true,
				'priority'     => 0,
				'icon'         => 'dashicons-admin-generic'
			],
			'code_showcase' => [
				'name'         => __( 'Code Showcase', TSK_DOMAIN ),
				'description'  => __( 'Display code snippets in a beautiful macOS-style terminal with multiple tabs and syntax highlighting.', TSK_DOMAIN ),
				'class'        => \Triskelion\Toolkit\Modules\CodeShowcase\CodeShowcaseBlockLoader::class,
				'has_settings' => true,
				'is_core'      => false,
				'priority'     => 100,
				'icon'         => 'dashicons-editor-code'
			],
			'insight_cards' => [
				'name'         => __( 'Insight Cards', TSK_DOMAIN ),
				'description'  => __( 'Transform standard quotes into visual callouts for Tips, Warnings, and Ideas with custom styles.', TSK_DOMAIN ),
				'class'        => \Triskelion\Toolkit\Modules\InsightCards\InsightCardsLoader::class,
				'has_settings' => true,
				'is_core'      => false,
				'priority'     => 101,
				'icon'         => 'dashicons-format-quote'
			],
/*			'diagnostic' => [
				'name'         => __( 'Logs & Diagnostic', TSK_DOMAIN ),
				'description'  => __( 'Monitor system health, view activity logs, and troubleshoot module performance.', TSK_DOMAIN ),
				'class'        => DiagnosticLoader::class,
				'has_settings' => true,
				'is_core'      => true,
				'priority'     => 900,
				'icon'         => 'dashicons-rest-api' // O 'dashicons-visibility'
			],
*/
		];
	}

	public static function register_vendor_assets(): void {
		$vendor_scripts = apply_filters( self::HOOK_REGISTER_SCRIPTS, [] );
		foreach ( $vendor_scripts as $handle => $data ) {
			if ( empty( $data['src'] ) ) {
				continue;
			}
			wp_register_script( $handle, $data['src'], $data['deps'] ?? [], $data['ver'] ?? self::TSK_VERSION, true );
		}

		$vendor_styles = apply_filters( self::HOOK_REGISTER_STYLES, [] );
		foreach ( $vendor_styles as $handle => $data ) {
			if ( empty( $data['src'] ) ) {
				continue;
			}
			wp_register_style( $handle, $data['src'], $data['deps'] ?? [], $data['ver'] ?? self::TSK_VERSION );
		}
	}

	public static function load_active_modules(): void {
		error_log( "📦 TRISKELION: Ejecutando carga de módulos..." );

		$active_map = (array) get_option( self::TSK_ACTIVE_MODULES, [] );
		$modules    = self::get_modules();

		foreach ( $modules as $id => $data ) {
			if ( empty( $data['class'] ) || empty( $active_map[ $id ] ) || ! class_exists( $data['class'] ) ) {
				continue;
			}

			$class_name = $data['class'];
			$loader = new $class_name();

			// Si es un bloque, su load() agendará el registro en Gutenberg
			if ( $loader instanceof AbstractModuleLoader ) {
				$loader->load();
				error_log( "✅ Módulo instanciado y cargado: " . $id );
			}
		}
	}

	public static function log( string $message, string $level = 'info', array $context = [] ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		$prefix = "[Triskelion Toolkit][$level]";
		$extra  = ! empty( $context ) ? ' | Context: ' . json_encode( $context ) : '';

		error_log( "$prefix: $message$extra" );
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
			self::$loaded_instances[ $id ] = new $class();
			return self::$loaded_instances[ $id ];
		}

		return null;
	}

	public static function get_sorted_modules(): array {
		$modules = self::get_modules();
		uasort( $modules, function( $a, $b ) {
			if ( $a['priority'] !== $b['priority'] ) {
				return $a['priority'] <=> $b['priority'];
			}
			return strcasecmp( $a['name'], $b['name'] );
		});
		return $modules;
	}
}