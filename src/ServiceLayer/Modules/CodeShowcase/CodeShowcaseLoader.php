<?php

namespace Triskelion\Toolkit\Modules\CodeShowcase;

use Triskelion\Toolkit\Core\AbstractBlockLoader;

class CodeShowcaseLoader extends AbstractBlockLoader {

	protected function get_block_name(): string {
		return 'code-showcase';
	}

	public function load(): void {
		parent::load();
		add_action('admin_enqueue_scripts', [$this, 'extra_assets']);
	}

	public function extra_assets(): void {
		// Por ahora vacío para que no truene el Fatal Error
	}

}