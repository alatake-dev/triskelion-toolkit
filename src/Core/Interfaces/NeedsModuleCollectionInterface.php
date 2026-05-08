<?php
/**
 * Interface for components that require access to the ModuleCollection.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Interfaces
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Interfaces;

use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;

/**
 * Interface NeedsModuleCollectionInterface
 *
 * This interface should be implemented by any class (like Loaders)
 * that needs to interact with the global collection of registered modules.
 *
 * @package Triskelion\TriskelionToolkit\Core\Interfaces
 */
interface NeedsModuleCollectionInterface {

	/**
	 * Injects the ModuleCollection instance.
	 *
	 * @param ModuleCollection $collection The collection of all registered toolkit modules.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_module_collection( ModuleCollection $collection ): void;
}
