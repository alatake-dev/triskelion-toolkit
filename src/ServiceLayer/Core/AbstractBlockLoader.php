<?php
namespace Triskelion\Toolkit\Core;

abstract class AbstractBlockLoader extends AbstractModuleLoader {

	public function load(): void {
		// Ejecución inmediata. Si el Toolkit nos llama, registramos el bloque.
		$this->register_block();
	}

	public function register_block(): void {
		$block_name = $this->get_block_name();

		// Ruta estandarizada según tu package.json
		$path = trailingslashit( TSK_PATH ) . 'build/blocks/' . $block_name;

		// Solo validamos que el archivo exista para no intentar registrar basura
		if ( ! file_exists( $path . '/block.json' ) ) {
			return;
		}

		$args = [];

		// Si el hijo (CodeShowcase) tiene render_frontend, activamos SSR
		if ( method_exists( $this, 'render_frontend' ) ) {
			$args['render_callback'] = [ $this, 'render_frontend' ];
		}

		// Registramos el bloque. WP se encarga del resto.
		$registry = register_block_type( $path, $args );

		// Opcional: Solo loguear si hay un error real de WordPress
		if ( is_wp_error( $registry ) ) {
			/** @var \WP_Error $registry */
			error_log( "❌ TSK Error [" . $block_name . "]: " . $registry->get_error_message() );
		}
	}


	abstract protected function get_block_name(): string;
}