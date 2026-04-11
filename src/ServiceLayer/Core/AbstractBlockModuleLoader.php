<?php

namespace Triskelion\Toolkit\Core;

abstract class AbstractBlockModuleLoader extends AbstractModuleLoader {

	public function load() :void {
		$module_id = $this->get_block_id();
		$path = TSK_PATH . "build/modules/$module_id";

		if (file_exists("$path/block.json")) {
			register_block_type($path);
		}

		// Permitimos que el hijo haga cosas extra si quiere
		$this->after_block_load();
	}

	abstract protected function get_block_id() :string;

	protected function after_block_load() :void {
	}
}