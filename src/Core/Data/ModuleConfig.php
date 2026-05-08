<?php

namespace Triskelion\TriskelionToolkit\Core\Data;

class ModuleConfig {

	public function __construct(
		public readonly string $id,
		public readonly string $name,
		public readonly string $description,
		public readonly string $class,
		public readonly int $priority = 500,
		public readonly bool $is_core = false,
		public readonly string $icon = 'dashicons-admin-generic'
	) {
	}
}
