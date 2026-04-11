<?php
namespace Triskelion\Toolkit\Core;

abstract class AbstractModuleLoader {

	// El único contrato obligatorio
	abstract public function load() :void;

	/**
	 * Renderiza la interfaz de configuración específica del módulo.
	 * Por defecto no hace nada. Los hijos pueden sobrescribirlo.
	 */
	public function render_settings(): void {
		// "Default" vacío.
	}

	/**
	 * Helper para que los hijos registren sus assets
	 * usando el nuevo sistema de filtros de Toolkit.
	 */
	protected function register_assets(string $id, array $scripts = [], array $styles = []) {
		if (!empty($scripts)) {
			add_filter(Toolkit::HOOK_REGISTER_SCRIPTS, function($all_scripts) use ($id, $scripts) {
				return array_merge($all_scripts, $scripts);
			});
		}

		if (!empty($styles)) {
			add_filter(Toolkit::HOOK_REGISTER_STYLES, function($all_styles) use ($id, $styles) {
				return array_merge($all_styles, $styles);
			});
		}
	}
}