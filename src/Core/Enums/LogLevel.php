<?php
/**
 * Log Level Enumeration File.
 *
 * Defines the standardized severity levels for the toolkit's logging system.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core\Enums
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core\Enums;

/**
 * Enum LogLevel
 *
 * Provides a type-safe way to handle logging priorities. This ensures that
 * administrative alerts and internal debug traces are handled with consistent
 * severity mapping across all module loaders.
 *
 * @package Triskelion\TriskelionToolkit\Core\Enums
 */
enum LogLevel: int {
	/**
	 * Fine-grained informational events.
	 *
	 * Typically used for tracing the execution flow of the application.
	 */
	case TRACE = 100;
	/**
	 * Diagnostic information.
	 *
	 * Useful for debugging during development or in staging environments.
	 */
	case DEBUG = 200;
	/**
	 * Routine information.
	 *
	 * Confirms that things are working as expected.
	 */
	case INFO = 300;
	/**
	 * Warning conditions.
	 *
	 * Something unexpected happened, but the application is still functioning.
	 */
	case WARN = 400;
	/**
	 * Error conditions.
	 *
	 * Runtime errors that do not require immediate action but should be logged.
	 */
	case ERROR = 500;
	/**
	 * Logging disabled.
	 *
	 * Maximum threshold to effectively suppress all log output.
	 */
	case OFF = 1000;
}
