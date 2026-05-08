<?php

namespace Triskelion\TriskelionToolkit\Core\Interfaces;

use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;

interface NeedsModuleCollectionInterface {
	public function set_module_collection( ModuleCollection $collection ): void;
}
