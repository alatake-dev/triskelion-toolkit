<?php

namespace Triskelion\TriskelionToolkit\Core\Interfaces;

use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;

interface RegistrableModuleInterface {
	public static function get_config(): ModuleConfig;

	/**
	 * Registro de strings para internacionalización (i18n).
	 *
	 * Este método tiene un triple propósito:
	 * 1. Actuar como "ancla" para que herramientas como wp-cli (make-pot) encuentren
	 *    los strings literales y los añadan al archivo .pot.
	 * 2. Proporcionar un lugar seguro para definir etiquetas que se mostrarán en la UI,
	 *    invocándolo solo DESPUÉS de que el hook 'init' o 'plugins_loaded' haya cargado
	 *    los archivos .mo correctamente.
	 * 3. Engañar a JIT (El causante de esta solución)
	 *
	 * @return array Mapa de strings traducibles asociados al módulo.
	 */
	public static function i18n_config(): array;

}