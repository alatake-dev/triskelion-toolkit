<?php

namespace Triskelion\TriskelionToolkit\Core;

use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Core\Interfaces\NeedsModuleCollectionInterface;

/**
 * Kernel: El cerebro del plugin.
 * Registra el Autoloader y levanta los módulos activos.
 */
class Kernel {
	private ModuleCollection $modules;
	private array $loaded_modules = [];

	public function __construct() {
		$this->modules = new ModuleCollection();
	}

	public function boot(): void {
		$this->init_i18n();
		$this->setup();
	}

	public function setup(): void {
		$this->load_active_modules();

		if ( is_admin() ) {
			$admin = new AdminManager( $this->modules, $this->loaded_modules );
			$admin->init();
		}

	}

	private function load_active_modules(): void {

		$db_settings  = get_option( 'tsk_active_modules', [] );
		$loader_files = glob( TSK_PATH . 'src/Modules/*/*Loader.php' );

		// Discovery
		foreach ( $loader_files as $file ) {
			$class = $this->resolve_namespace( $file );
			if ( class_exists( $class ) ) {
				$this->modules->add( $class::get_config() );
				Logger::debug( "Triskelion Debug: Loading module {$class}", "Kernel" );
			}
		}
		// Creation
		foreach ( $this->modules->get_all() as $config ) {
			if ( $config->is_core || in_array( $config->id, $db_settings, true ) ) {

				$class    = $config->class;
				$instance = new $class();
				Logger::debug( "Triskelion Debug: Inyectando colección de módulos en {$class}", "Kernel" );

				// Inyección de la bolsa completa
				if ( $instance instanceof NeedsModuleCollectionInterface ) {
					$instance->set_module_collection( $this->modules );
				}

				$this->loaded_modules[ $config->id ] = $instance;
			}
		}
	}

	private function resolve_namespace( string $file_path ): string {

		$relative_path = str_replace( [ TSK_PATH . 'src/', '.php', '/' ], [ '', '', '\\' ], $file_path );

		return 'Triskelion\\TriskelionToolkit\\' . $relative_path;
	}

	/**
	 * Devuelve los módulos que están corriendo actualmente.
	 * Útil para que la Capa de Visualización del Admin sepa qué pestañas pintar.
	 */
	public function get_active_modules(): array {
		return $this->loaded_modules;
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
			$mo_file = TSK_PATH . 'languages/' . $domain . '-es.mo';

			if ( file_exists( $mo_file ) ) {
				load_textdomain( $domain, $mo_file );

				return;
			}
		}

		// LÓGICA GENERAL: Para otros idiomas (inglés, francés, etc.)
		// Intentamos cargar el archivo específico del locale actual
		$specific_mo = TSK_PATH . "languages/{$domain}-{$locale}.mo";

		if ( file_exists( $specific_mo ) ) {
			load_textdomain( $domain, $specific_mo );
		} else {
			// FALLBACK: Si no hay traducción, cargamos el .pot (inglés) por defecto
			load_plugin_textdomain( $domain, false, dirname( plugin_basename( TSK_FILE ) ) . '/languages' );
		}
	}
}