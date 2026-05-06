<?php

namespace Triskelion\TriskelionToolkit\Core\Interfaces;

interface HasSettingsInterface {
	/**
	 * Define los campos y valores por defecto del módulo.
	 */
	public function register_module_settings(): void;

	/**
	 * Limpia los datos antes de guardarlos.
	 */
	public function sanitize_module_settings( $input ): array;

	public function render_settings(): string;
}