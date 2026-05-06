<?php

namespace Triskelion\TriskelionToolkit\Core\Data;

class ModuleCollection {
	/** @var ModuleConfig[] */
	private array $items = [];

	public function add( ModuleConfig $config ): void {
		$this->items[ $config->id ] = $config;
	}

	public function get_all_sorted(): array {
		$items = $this->items;
		uasort( $items, function ( ModuleConfig $a, ModuleConfig $b ) {
			return ( $a->priority <=> $b->priority ) ?: ( $a->name <=> $b->name );
		} );

		return $items;
	}

	public function get_all(): array {
		return $this->items;
	}

	public function get( string $id ): ?ModuleConfig {
		return $this->items[ $id ] ?? null;
	}
}