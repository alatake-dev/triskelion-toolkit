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
	 * Returns the HTML content to be placed inside the main module form.
	 *
	 * The AdminManager will automatically wrap this content in a <form> tag,
	 * injecting the necessary security nonces, settings fields, and the submit button.
	 * If this returns an empty string, the form container will not be rendered.
	 *
	 * @return string The HTML internal form fields.
	 */
	public function render_inside_form(): string;

	/**
	 * Returns the HTML content to be rendered outside of the main form.
	 *
	 * This is ideal for status displays, logs, previews, or any information
	 * that should not be sent as part of the settings POST request.
	 *
	 * @return string The HTML content for the external display area.
	 */
	public function render_outside_form(): string;
}
