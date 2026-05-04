<?php
namespace Triskelion\TriskelionToolkit\Core;

use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;

/**
 * Kernel: El cerebro del plugin.
 * Registra el Autoloader y levanta los módulos activos.
 */
class Kernel {
	private static ModuleCollection $module_configs;
	private static array $loaded_modules = [];

	public static function get_manifest(): ModuleCollection {
		if ( ! isset( self::$module_configs ) ) {
			self::$module_configs = new ModuleCollection();
		}
		return self::$module_configs;
	}

	public static function boot() {

		add_action('plugins_loaded', [self::class, 'setup'], 1);
		add_action('init', [self::class, 'init_i18n']);

	}
	public static function setup() {
		self::$module_configs = new ModuleCollection();

		// Ahora sí, ya cargado WP, buscamos los módulos
		$db_settings  = get_option( 'tsk_active_modules', [] );
		$loader_files = glob( TSK_PATH . 'src/Modules/*/*Loader.php' );

		foreach ( $loader_files as $file ) {
			$class = self::resolve_namespace( $file );

			if ( ! class_exists( $class, true ) ) {
				continue;
			}

			$config = $class::get_config(); // Aquí ya no habrá notice de traducción
			self::$module_configs->add( $config );

			// La lógica de in_array que ya corregimos
			if ( $config->is_core || in_array( $config->id, $db_settings, true ) ) {
				self::$loaded_modules[ $config->id ] = new $class();
			}
		}
	}

	private static function resolve_namespace(string $file_path): string {
		// TSK_PATH suele ser /var/www/html/wp-content/plugins/triskelion-toolkit/
		// Queremos llegar a: Triskelion\TriskelionToolkit\Modules\Smtp\SmtpLoader

		$relative_path = str_replace(TSK_PATH . 'src/', '', $file_path); // Modules/Smtp/SmtpLoader.php
		$relative_path = str_replace('.php', '', $relative_path);       // Modules/Smtp/SmtpLoader
		$relative_path = str_replace('/', '\\', $relative_path);        // Modules\Smtp\SmtpLoader

		return 'Triskelion\\TriskelionToolkit\\' . $relative_path;
	}

	/**
	 * Devuelve los módulos que están corriendo actualmente.
	 * Útil para que la ViewLayer del Admin sepa qué pestañas pintar.
	 */
	public static function get_active_modules() {
		return self::$loaded_modules;
	}

	/**
	 * Carga el dominio de traducción principal.
	 */
	public static function init_i18n() {
		load_plugin_textdomain(
			'triskelion-toolkit',
			false,
			dirname(plugin_basename(TSK_FILE)) . '/languages'
		);
	}
}