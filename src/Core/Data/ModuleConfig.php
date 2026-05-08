<?php
/**
 * Value Object for Module Configuration.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Data
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Data;

/**
 * Class ModuleConfig
 *
 * This Data Transfer Object (DTO) holds the immutable metadata for a toolkit module.
 * Using PHP 8.2 readonly properties to ensure data integrity after instantiation.
 *
 * @package Triskelion\TriskelionToolkit\Core\Data
 */
class ModuleConfig {

	/**
	 * ModuleConfig constructor.
	 *
	 * @param string $id          Unique identifier for the module (used in slugs and keys).
	 * @param string $name        Display name for the module in the UI.
	 * @param string $description Short explanation of the module's purpose.
	 * @param string $clazz       Fully Qualified Class Name (FQCN) of the loader.
	 * @param int    $priority    Execution priority (lower numbers run earlier). Default 500.
	 * @param bool   $is_core     Whether this is a vital system module that cannot be disabled.
	 * @param string $icon        Dashicon class or URL for the module's menu icon.
	 * @param string $settings_group        Module group name for settings.
	 */
	public function __construct(
		public readonly string $id,
		public readonly string $name,
		public readonly string $description,
		public readonly string $clazz,
		public readonly int $priority = 500,
		public readonly bool $is_core = false,
		public readonly string $icon = 'dashicons-admin-generic',
		public readonly string $settings_group
	) {
	}
}
