<?php

namespace Triskelion\TriskelionToolkit\Core;

use Triskelion\TriskelionToolkit\Core\Bridge\WpBridge;
use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Core\Interfaces\NeedsModuleCollectionInterface;

/**
 * Kernel: El cerebro del plugin.
 * Registra el Autoloader y levanta los módulos activos.
 */
class Kernel {
	private ModuleCollection $modules;
	private array $loaded_modules = array();

	private WpBridge $wp;

	public function __construct() {
		$this->wp      = new WpBridge();
		$this->modules = new ModuleCollection();
	}

	public function boot(): void {
		$this->init_i18n();
		$this->setup();
	}

	/**
	 * Carga el dominio de traducción principal.
	 */
	public function init_i18n(): void {
		$domain = 'triskelion-toolkit';
		$locale = determine_locale(); // Detecta el idioma actual del WP (es_MX, en_US, es, etc.)

		// EXCEPCIÓN: Normalización del Español
		// Si es cualquier variante de español (es_MX, es_ES, es_AR, o solo es)
		if ( str_starts_with( $locale, 'es' ) ) {
			$mo_file = TRISKELION_TOOLKIT_PATH . 'languages/' . $domain . '-es.mo';

			if ( file_exists( $mo_file ) ) {
				load_textdomain( $domain, $mo_file );

				return;
			}
		}

		// LÓGICA GENERAL: Para otros idiomas (inglés, francés, etc.)
		// Intentamos cargar el archivo específico del locale actual
		$specific_mo = TRISKELION_TOOLKIT_PATH . "languages/$domain-$locale.mo";

		if ( file_exists( $specific_mo ) ) {
			load_textdomain( $domain, $specific_mo );
		} else {
			// FALLBACK: Si no hay traducción, cargamos .pot (inglés) por defecto
			load_plugin_textdomain( $domain, false, dirname( plugin_basename( TRISKELION_TOOLKIT_FILE ) ) . '/languages' );
		}

		add_filter(
			'load_script_translation_file',
			function ( $file, $handle, $current_domain ) use ( $domain ) {
				// Solo afectamos a nuestro plugin
				if ( $domain !== $current_domain ) {
					return $file;
				}

				$locale = determine_locale();

				// Si el idioma es español (ej. es_MX, es_AR) pero NO es el "es" base
				if ( $locale !== 'es' && str_starts_with( $locale, 'es' ) ) {

					// $file contiene la ruta que WP está intentando cargar (ej. .../triskelion-toolkit-es_MX-hash.json)
					// Reemplazamos "-es_MX-" por "-es-" en la ruta del archivo
					$fallback_file = str_replace( '-' . $locale . '-', '-es-', $file );

					// Si nuestro archivo base 'es' existe, obligamos a WP a usarlo
					if ( file_exists( $fallback_file ) ) {
						return $fallback_file;
					}
				}

				return $file;
			},
			10,
			3
		);
	}

	public function setup(): void {
		$this->load_active_modules();

		if ( is_admin() ) {
			$admin = new AdminManager( $this->modules, $this->loaded_modules );
			$admin->init();
		}
	}

	private function load_active_modules(): void {

		$db_settings  = $this->wp->settings->get_option( 'triskelion_toolkit_general_settings', array() );
		$loader_files = glob( TRISKELION_TOOLKIT_PATH . 'src/Modules/*/*Loader.php' );

		// Discovery.
		foreach ( $loader_files as $file ) {
			$clazz = $this->resolve_namespace( $file );
			if ( class_exists( $clazz ) ) {
				$this->modules->add( $clazz::get_config() );
				Logger::debug( "Triskelion Debug: Loading module $clazz", 'Kernel' );
			}
		}
		// Creation.
		foreach ( $this->modules->get_all() as $config ) {
			if ( $config->is_core || in_array( $config->id, $db_settings, true ) ) {

				$clazz    = $config->clazz;
				$instance = new $clazz();
				Logger::debug( "Triskelion Debug: Inyectando colección de módulos en $clazz", 'Kernel' );

				// Inyección de la bolsa completa
				if ( $instance instanceof NeedsModuleCollectionInterface ) {
					$instance->set_module_collection( $this->modules );
				}

				$this->loaded_modules[ $config->id ] = $instance;
			}
		}
	}

	private function resolve_namespace( string $file_path ): string {

		$relative_path = str_replace( array( TRISKELION_TOOLKIT_PATH . 'src/', '.php', '/' ), array( '', '', '\\' ), $file_path );

		return 'Triskelion\\TriskelionToolkit\\' . $relative_path;
	}

	/**
	 * Devuelve los módulos que están corriendo actualmente.
	 * Útil para que la Capa de Visualización del Admin sepa qué pestañas pintar.
	 */
	public function get_active_modules(): array {
		return $this->loaded_modules;
	}
}
