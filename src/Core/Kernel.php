<?php
/**
 * Kernel Class File.
 *
 * This is the central nervous system of the Triskelion Toolkit. It handles
 * the initialization lifecycle, internationalization, and dynamic module discovery.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core;

use Triskelion\TriskelionToolkit\Core\Bridge\WpBridge;
use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Core\Interfaces\NeedsModuleCollectionInterface;

/**
 * Class Kernel
 *
 * The core engine of the plugin. It registers the internal infrastructure,
 * handles bootstrapping sequences, and manages the dependency injection
 * for active module loaders.
 *
 * @package Triskelion\TriskelionToolkit\Core
 */
class Kernel {
	/**
	 * Registry for all discovered module configurations.
	 *
	 * @var ModuleCollection
	 */
	private ModuleCollection $modules;
	/**
	 * Map of currently instantiated and active module loaders.
	 *
	 * @var array<string, object>
	 */
	private array $loaded_modules = array();
	/**
	 * Bridge for decoupled WordPress core functionality.
	 *
	 * @var WpBridge
	 */
	private WpBridge $wp;

	/**
	 * Kernel constructor.
	 *
	 * Initializes the internal state by instantiating the WordPress bridge
	 * and the module registry.
	 */
	public function __construct() {
		$this->wp      = new WpBridge();
		$this->modules = new ModuleCollection();
	}

	/**
	 * Orchestrates the primary boot sequence.
	 *
	 * Triggers internationalization loading and internal infrastructure setup.
	 *
	 * @return void
	 */
	public function boot(): void {
		$this->init_i18n();
		$this->setup();
	}

	/**
	 * Initializes the internationalization system.
	 *
	 * Detects the current locale and loads specific .mo files, with a
	 * fallback to the native WordPress plugin textdomain system.
	 *
	 * @return void
	 */
	public function init_i18n(): void {
		$domain = 'triskelion-toolkit';
		$locale = $this->wp->security->determine_locale();

		if ( str_starts_with( $locale, 'es' ) ) {
			$mo_file = TRISKELION_TOOLKIT_PATH . 'languages/' . $domain . '-es.mo';

			if ( file_exists( $mo_file ) ) {
				$this->wp->security->load_textdomain( $domain, $mo_file );

				return;
			}
		}

		$specific_mo = TRISKELION_TOOLKIT_PATH . "languages/$domain-$locale.mo";

		if ( file_exists( $specific_mo ) ) {
			$this->wp->security->load_textdomain( $domain, $specific_mo );
		} else {
			$this->wp->security->load_plugin_textdomain(
				$domain,
				false,
				dirname(
					$this->wp->settings->plugin_basename( TRISKELION_TOOLKIT_FILE )
				) . '/languages'
			);
		}

		$this->wp->events->add_filter(
			'load_script_translation_file',
			function ( $file, $handle, $current_domain ) use ( $domain ) {
				if ( $domain !== $current_domain ) {
					return $file;
				}

				$locale = $this->wp->security->determine_locale();
				if ( 'es' !== $locale && str_starts_with( $locale, 'es' ) ) {
					$fallback_file = str_replace( '-' . $locale . '-', '-es-', $file );
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

	/**
	 * Configures the administrative infrastructure.
	 *
	 * Loads active modules and, if in an administrative context, initializes
	 * the AdminManager to handle the user interface.
	 *
	 * @return void
	 */
	public function setup(): void {
		$this->load_active_modules();
		if ( $this->wp->security->is_admin() ) {
			$admin = new AdminManager( $this->modules, $this->loaded_modules );
			$admin->init();
		}
	}

	/**
	 * Scans and instantiates active module loaders.
	 *
	 * Discovers modules via file system globbing, registers their configs,
	 * and performs dependency injection for modules requiring the collection.
	 *
	 * @return void
	 */
	private function load_active_modules(): void {
		$db_settings  = $this->wp->settings->get_option( 'triskelion_toolkit_general_settings', array() );
		$loader_files = glob( TRISKELION_TOOLKIT_PATH . 'src/Modules/*/*Loader.php' );

		// Discovery.
		foreach ( $loader_files as $file ) {
			$clazz = $this->resolve_namespace( $file );
			if ( class_exists( $clazz ) ) {
				$this->modules->add( $clazz::get_config() );
				Logger::trace( "Triskelion Debug: Loading module $clazz", 'Kernel' );
			}
		}
		// Creation.
		foreach ( $this->modules->get_all() as $config ) {
			if ( $config->is_core || in_array( $config->id, $db_settings, true ) ) {

				$clazz    = $config->clazz;
				$instance = new $clazz();
				Logger::trace( "Triskelion Debug: Inyectando colección de módulos en $clazz", 'Kernel' );

				if ( $instance instanceof NeedsModuleCollectionInterface ) {
					$instance->set_module_collection( $this->modules );
				}

				$this->loaded_modules[ $config->id ] = $instance;
			}
		}
	}

	/**
	 * Resolves a physical file path to its fully qualified class name.
	 *
	 * @param string $file_path The absolute path to the PHP file.
	 *
	 * @return string The resolved PSR-4 compliant namespace.
	 */
	private function resolve_namespace( string $file_path ): string {

		$relative_path = str_replace(
			array( TRISKELION_TOOLKIT_PATH . 'src/', '.php', '/' ),
			array(
				'',
				'',
				'\\',
			),
			$file_path
		);

		return 'Triskelion\\TriskelionToolkit\\' . $relative_path;
	}
}
