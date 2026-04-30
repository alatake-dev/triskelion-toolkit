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

	public static function boot() {
		add_action('init', [self::class, 'init_i18n']);

		add_action('plugins_loaded', function() {
			self::$module_configs = new ModuleCollection();
			
			$db_settings = get_option('triskelion_modules_settings', []);

			$loader_files = glob(TSK_PATH . 'src/Modules/*/*Loader.php');

			foreach ($loader_files as $file) {

				$class = self::resolve_namespace($file);

				if (!class_exists($class, true) || !is_subclass_of($class, \Triskelion\TriskelionToolkit\Core\Interfaces\RegistrableModuleInterface::class)) {
					continue;
				}
				$config = $class::get_config();
				self::$module_configs->add($config);

				$is_active = $config->is_core || !empty($db_settings[$config->id]);
				if ($is_active) {
					self::$loaded_modules[$config->id] = new $class();
				}

			}
		}, 1);
	}

	private static function resolve_namespace(string $file_path): string {
		// TSK_PATH suele ser /var/www/html/wp-content/plugins/triskelion-toolkit/
		// Queremos llegar a: Triskelion\TriskelionToolkit\Modules\Smtp\SmtpLoader

		$relative_path = str_replace(TSK_PATH . 'src/', '', $file_path); // Modules/Smtp/SmtpLoader.php
		$relative_path = str_replace('.php', '', $relative_path);       // Modules/Smtp/SmtpLoader
		$relative_path = str_replace('/', '\\', $relative_path);        // Modules\Smtp\SmtpLoader

		return 'Triskelion\\TriskelionToolkit\\' . $relative_path;
	}
	private static function get_class_name_from_path($file_path): string {
		// Esto es un ejemplo, depende de tu estructura de carpetas y namespace base
		$path = str_replace([TSK_PATH . 'src/', '.php', '/'], ['', '', '\\'], $file_path);
		return 'Triskelion\\TriskelionToolkit\\' . $path;
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