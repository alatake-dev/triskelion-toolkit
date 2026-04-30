<?php

namespace src\Core;

interface ModuleInterface {
	public static function get_module_id(): string;
	public static function get_module_args(): array;
}