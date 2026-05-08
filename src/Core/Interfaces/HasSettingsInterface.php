<?php
/**
 * Interface for modules that include administrative settings.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Interfaces
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Interfaces;

/**
 * Interface HasSettingsInterface
 *
 * Any loader or module that requires a settings section in the admin panel
 * must implement this interface to ensure compatibility with AdminManager.
 *
 * @package Triskelion\TriskelionToolkit\Core\Interfaces
 */
interface HasSettingsInterface {
	/**
	 * Defines module fields and default values.
	 *
	 * Should be used to call register_setting, add_settings_section, and add_settings_field.
	 *
	 * @see https://developer.wordpress.org/plugins/settings/settings-api/
	 * @return void
	 * @since 1.0.0
	 */
	public function register_module_settings(): void;

	/**
	 * Sanitizes data before saving it to the database.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_setting/
	 *
	 * @param mixed $input Raw data submitted via POST.
	 * @return array Sanitized data ready for persistence.
	 * @since 1.0.0
	 */
	public function sanitize_module_settings( $input ): array;
	/**
	 * Renders the settings HTML for the module's tab.
	 *
	 * @return string HTML content to be displayed.
	 * @since 1.0.0
	 */
	public function render_settings(): string;
}
