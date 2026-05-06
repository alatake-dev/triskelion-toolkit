<?php
namespace Triskelion\TriskelionToolkit\Core\Data;

class ModuleConfigBuilder {
	private array $data = [];

	public function set_id(string $id): self { $this->data['id'] = $id; return $this; }
	public function set_name(string $name): self { $this->data['name'] = $name; return $this; }
	public function set_description(string $desc): self { $this->data['description'] = $desc; return $this; }
	public function set_class(string $class): self { $this->data['class'] = $class; return $this; }
	public function set_priority(int $priority): self { $this->data['priority'] = $priority; return $this; }
	public function set_is_core(bool $is_core): self { $this->data['is_core'] = $is_core; return $this; }
	public function set_icon(string $icon): self { $this->data['icon'] = $icon; return $this; }

	public function build(): ModuleConfig {
		// Validación mínima: si no hay ID o Clase, esto no va a arrancar.
		if (empty($this->data['id']) || empty($this->data['class'])) {
			throw new \InvalidArgumentException("ModuleConfigBuilder: ID and Class are mandatory.");
		}

		return new ModuleConfig(
			id:          $this->data['id'],
			name:        $this->data['name'] ?? $this->data['id'],
			description: $this->data['description'] ?? '',
			class:       $this->data['class'],
			priority:    $this->data['priority'] ?? 500,
			is_core:     $this->data['is_core'] ?? false,
			icon:        $this->data['icon'] ?? 'dashicons-admin-generic'
		);
	}
}