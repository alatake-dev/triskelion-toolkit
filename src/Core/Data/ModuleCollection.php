<?php
/**
 * Collection object for Toolkit Modules.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Data
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Data;

/**
 * Class ModuleCollection
 *
 * Maintains a registry of modules and their configurations.
 *
 * @package Triskelion\TriskelionToolkit\Core\Data
 */
class ModuleCollection {

	/**
	 * Internal storage for modules.
	 *
	 * @var array<string, object>
	 */
	private array $items = array();

	/**
	 * Adds a module configuration to the collection.
	 *
	 * @param ModuleConfig $config The module configuration object (usually from get_config()).
	 * @return void
	 * @since 1.0.0
	 */
	public function add( ModuleConfig $config ): void {
		$this->items[ $config->id ] = $config;
	}

	/**
	 * Retrieves all modules sorted by priority and then by name.
	 *
	 * This method uses a stable sorting algorithm (uasort) to preserve keys.
	 * It first compares the 'priority' property, and if equal, falls back
	 * to alphabetical sorting by the 'name' property.
	 *
	 * @see https://www.php.net/manual/en/function.uasort.php
	 *
	 * @return array<string, ModuleConfig> Sorted list of module configurations.
	 * @since 1.0.0
	 */
	public function get_all_sorted(): array {
		$items = $this->items;
		uasort(
			$items,
			function ( ModuleConfig $a, ModuleConfig $b ) {
				$comparison = $a->priority <=> $b->priority;
				return ( 0 !== $comparison ) ? $comparison : ( $a->name <=> $b->name );
			}
		);

		return $items;
	}
	/**
	 * Retrieves all registered modules.
	 *
	 * @return array<string, object> List of module configurations.
	 * @since 1.0.0
	 */
	public function get_all(): array {
		return $this->items;
	}
	/**
	 * Gets a specific module by its ID.
	 *
	 * @param string $id The unique module identifier.
	 * @return object|null The module configuration or null if not found.
	 * @since 1.0.0
	 */
	public function get( string $id ): ?ModuleConfig {
		return $this->items[ $id ] ?? null;
	}

	/**
	 * Checks if a specific module configuration exists in the collection.
	 *
	 * @param string $module_id The module identifier to verify.
	 *
	 * @return bool True if the module exists, false otherwise.
	 */
	public function has( string $module_id ): bool {
		return isset( $this->items[ $module_id ] );
	}
}
