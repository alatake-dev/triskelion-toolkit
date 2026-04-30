<?php

namespace Triskelion\TriskelionToolkit\Core\Interfaces;

use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;

interface RegistrableModuleInterface {
	public static function get_config(): ModuleConfig;

}