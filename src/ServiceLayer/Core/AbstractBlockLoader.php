<?php
namespace Triskelion\Toolkit\Core;

abstract class AbstractBlockLoader extends AbstractModuleLoader {

	public function load(): void {
		$this->register_block();
	}

	public function register_block(): void {
		$block_name = $this->get_block_name();
		$path = trailingslashit(TSK_PATH) . 'build/blocks/' . $block_name;

		if (!file_exists($path . '/block.json')) return;

		$args = [];
		if (method_exists($this, 'render_frontend')) {
			$args['render_callback'] = [$this, 'render_frontend'];
		}

		register_block_type($path, $args);

		// Si el hijo necesita encolar assets específicos en el front
		if (method_exists($this, 'enqueue_block_assets')) {
			add_action('wp_enqueue_scripts', [$this, 'enqueue_block_assets']);
		}
	}

	abstract protected function get_block_name(): string;
}