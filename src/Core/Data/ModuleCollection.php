<?php
namespace Triskelion\TriskelionToolkit\Core\Data;

class ModuleCollection {
	/** @var ModuleConfig[] */
	private array $items = [];

	public function add(ModuleConfig $config): void {
		$this->items[$config->id] = $config;
		$this->apply_order();
	}

	private function apply_order(): void {
		uasort($this->items, function(ModuleConfig $a, ModuleConfig $b) {
			return ($a->priority <=> $b->priority) ?: ($a->name <=> $b->name);
		});
	}

	public function get_all(): array {
		return $this->items;
	}

	public function get(string $id): ?ModuleConfig {
		return $this->items[$id] ?? null;
	}
}