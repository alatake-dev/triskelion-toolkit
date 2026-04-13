<?php
namespace Triskelion\Toolkit\Core;

/**
 * Clase base para registrar bloques de Gutenberg
 */
abstract class AbstractBlockLoader extends AbstractModuleLoader {

	/**
	 * Implementamos el contrato de AbstractModuleLoader
	 */
	public function load(): void {
		$this->register_block_type();
	}

	/**
	 * Lógica de registro delegada a la función nativa de WP
	 */
	public function register_block_type(): void {
		$block_name = $this->get_block_name();
		$path = TSK_PATH . 'build/blocks/' . $block_name;

		$registry = register_block_type( $path );

		if ( $registry ) {
			error_log( "✅ PHP: Bloque '$block_name' registrado." );
			// Verifiquemos si WP encontró el script del editor
			if ( empty( $registry->editor_script_handles ) ) {
				error_log( "⚠️ ALERTA: WP registró el bloque pero NO generó handles para el JS. Revisa si el path es correcto: " . $path );
			} else {
				error_log( "📦 ASSETS: Handle generado: " . $registry->editor_script_handles[0] );
			}
		} else {
			error_log( "❌ PHP: Falló register_block_type para " . $block_name );
		}
	}
	/**
	 * Cada bloque debe decirnos su nombre de carpeta
	 */
	abstract protected function get_block_name(): string;
}