<?php
/**
 * Logger Class File.
 *
 * Handles the toolkit's logging infrastructure, including file rotation,
 * directory security, and severity-based message distribution.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core;

use stdClass;
use Triskelion\TriskelionToolkit\Core\Bridge\WpBridge;
use Triskelion\TriskelionToolkit\Core\Enums\LogLevel;
use Triskelion\TriskelionToolkit\Core\Exceptions\FileSystemException;
use WP_Filesystem_Base;

/**
 * Class Logger
 *
 * Provides a static interface for application-wide logging. Features include
 * automatic log rotation, directory hardening via .htaccess/index.php,
 * and integration with the WordPress Filesystem API.
 *
 * @package Triskelion\TriskelionToolkit\Core
 */
class Logger {
	/**
	 * Log levels.
	 */

	/**
	 * Path to the log directory.
	 *
	 * @var string
	 */
	private static string $log_path = '';
	/**
	 * Maximum log file size in bytes (2MB default).
	 *
	 * @var int
	 */
	private static int $max_size = 2097152;
	/**
	 * Internal flag to track if the logger has been bootstrapped.
	 *
	 * @var bool
	 */
	private static bool $initialized = false;

	/**
	 * Flag to determine if an administrative notice should be triggered on failure.
	 *
	 * @var bool
	 */
	private static bool $admin_notice = false;

	/**
	 * Bridge for decoupled WordPress core functionality.
	 *
	 * @var WpBridge
	 */
	private static WpBridge $wp;


	/**
	 * Core write method. Handles initialization, rotation and writing.
	 *
	 * @param string   $message The message to log.
	 * @param LogLevel $level Severity level.
	 * @param string   $module Originating module.
	 *
	 * @return void
	 * @throws FileSystemException If init fails.
	 */
	private static function write( string $message, LogLevel $level, string $module ): void {
		self::init();
		$default_level = array( 'level' => LogLevel::OFF->value );
		$saved_value   = (int) self::$wp->settings->get_option( 'triskelion_toolkit_diagnostic_settings', $default_level )['level'];
		$config_level  = LogLevel::tryFrom( $saved_value ) ?? LogLevel::OFF;

		if ( $level->value < $config_level->value ) {
			return;
		}

		$file          = self::$log_path . '/triskelion.log';
		$wp_filesystem = self::get_filesystem();

		if ( file_exists( $file ) && filesize( $file ) > self::$max_size ) {
			$wp_filesystem->move( $file, self::$log_path . '/triskelion-' . gmdate( 'Ymd-His' ) . '.bak' );
			self::cleanup_backups();
		}

		$entry = sprintf(
			"[%s] [%s] [%-12s] %s\n",
			gmdate( 'Y-m-d H:i:s' ),
			str_pad( $level->name, 5 ),
			strtoupper( substr( $module, 0, 12 ) ),
			$message
		);
		self::direct_write( $file, $entry );
	}

	/**
	 * Bootstraps the logging environment.
	 *
	 * Ensures the log directory exists and is secured against direct access.
	 *
	 * @throws FileSystemException If the directory cannot be created or secured.
	 * @return void
	 */
	public static function init(): void {
		if ( self::$initialized ) {
			return;
		}
		self::$wp          = new WpBridge();
		self::$initialized = true;
		try {
			$upload_dir     = self::$wp->settings->upload_dir();
			self::$log_path = self::$wp->settings->normalize_path( $upload_dir['basedir'] . '/triskelion-logs' );

			$fs = self::get_filesystem();

			if ( ! $fs->is_dir( self::$log_path ) ) {
				if ( ! $fs->mkdir( self::$log_path, '0755' ) ) {
					$msg = 'Could not create log directory: ' . self::$log_path;
					self::error_log( $msg );
					throw new FileSystemException( $msg );
				}
			}
			if ( file_exists( self::$log_path ) ) {
				self::secure_directory();
			}

			if ( ! $fs->is_writable( self::$log_path ) ) {
				$owner = 'unknown';
				if ( function_exists( 'posix_getpwuid' ) ) {
					$owner_info = posix_getpwuid( fileowner( self::$log_path ) );
					$owner      = $owner_info['name'] ?? 'unknown';
				}
				$msg = 'Log directory is not writable. Current owner ' . $owner;
				self::error_log( $msg );
				throw new FileSystemException( $msg );
			}
			self::info( 'Logger initialized successfully.' );
		} catch ( FileSystemException $e ) {
			self::error_log( 'admin_notice: ' . self::$admin_notice );
			self::register_admin_error_notice( $e->getMessage() );
		}
	}

	/**
	 * Hardens the log directory.
	 *
	 * Generates an .htaccess file to deny all web access and an empty index.php
	 * to prevent directory listing.
	 *
	 * @return void
	 */
	private static function secure_directory(): void {
		self::init();
		$wp_filesystem = self::get_filesystem();
		$wp_filesystem->put_contents( self::$log_path . '/.htaccess', 'Deny from all' );
		$wp_filesystem->put_contents( self::$log_path . '/index.php', '<?php // Silence' );
	}

	/**
	 * Cleans up old backup files, keeping only a limited number.
	 *
	 * @return void
	 */
	private static function cleanup_backups(): void {
		$files = glob( self::$log_path . '/*.bak' );
		if ( count( $files ) > 3 ) {
			array_multisort( array_map( 'filemtime', $files ), SORT_ASC, $files );
			wp_delete_file( $files[0] );
		}
	}

	/**
	 * Returns the full path to the log file.
	 *
	 * @return string Full file path.
	 */
	public static function get_log_path(): string {
		self::init();

		return self::$log_path . '/triskelion.log';
	}

	/**
	 * Performs direct file write using PHP filesystem functions for performance.
	 *
	 * @param string $file File path.
	 * @param string $entry Log entry.
	 *
	 * @return void
	 */
	protected static function direct_write( string $file, string $entry ): void {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$handle = fopen( $file, 'a' );
		if ( $handle ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
			fwrite( $handle, $entry );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			fclose( $handle );
		}
	}

	/**
	 * Retrieves and validates the current WordPress filesystem status.
	 *
	 * Interrogates the environment via the WpBridge to determine the
	 * availability and writability of the uploads directory. This serves
	 * as a pre-flight check for all disk-heavy operations.
	 *
	 * @throws FileSystemException If the WordPress filesystem returns an error state.
	 * @return array<string, mixed> The filesystem path and URL data from wp_upload_dir().
	 */
	public static function get_file_system_status(): array {
		$uploads = self::$wp->settings->upload_dir();

		if ( ! empty( $uploads['error'] ) ) {
			$msg = 'WordPress Filesystem Error: ' . $uploads['error'];
			self::error_log( 'WordPress Filesystem Error [get_file_system_status]: ' . $uploads['error'] );
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new FileSystemException( $msg );
		}
		return $uploads;
	}

	/**
	 * Ensures the WordPress Filesystem global is initialized.
	 *
	 * @return WP_Filesystem_Base|stdClass stdClass if the bridge is in no WP mode.
	 * @throws FileSystemException If the filesystem cannot be initialized.
	 */
	private static function get_filesystem(): WP_Filesystem_Base|stdClass {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			if ( ! function_exists( 'WP_Filesystem' ) || ! WP_Filesystem() ) {
				self::error_log( 'Critical: Failed to initialize WordPress Filesystem.' );
				throw new FileSystemException( 'Failed to initialize WordPress Filesystem.' );
			}
		}
		return $wp_filesystem;
	}

	/**
	 * Proxy method for PHP's native error_log.
	 *
	 * This abstraction allows us to bypass strict linting rules (like WordPress.PHP.DevelopmentFunctions)
	 * in a single, controlled location. It serves as the "emergency exit" for logging when the
	 * custom filesystem-based logger fails or is not yet initialized.
	 *
	 * @param string $message The error message to be sent to the server's error log.
	 * @return void
	 */
	public static function error_log( string $message ): void {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( "[Triskelion Emergency] $message" );
	}

	/**
	 * Registers a one-time administrative notice for critical filesystem errors.
	 *
	 * This method uses a static guard to ensure that even if the logger is called multiple
	 * times during a single request (e.g., in a loop), the admin notice is only hooked once.
	 * It prevents the "cascading notices" UI bug in the WordPress Dashboard.
	 *
	 * @hook admin_notices
	 *
	 * @param string $message The validation or filesystem error message to display.
	 * @return void
	 */
	private static function register_admin_error_notice( $message ): void {
		if ( self::$admin_notice ) {
			return;
		}
		self::$admin_notice = true;
		add_action(
			'admin_notices',
			printf(
				'<div class="notice notice-error"><p><strong>Triskelion Toolkit:</strong>%s</p></div>',
				esc_html( $message )
			)
		);
	}


	/**
	 * Log an info message.
	 *
	 * @param string $message The message to log.
	 * @param string $module Originating module.
	 *
	 * @return void
	 */
	public static function info( string $message, string $module = 'CORE' ): void {
		self::write( $message, LogLevel::INFO, $module );
	}

	/**
	 * Log an trace message.
	 *
	 * @param string $message The message to log.
	 * @param string $module Originating module.
	 *
	 * @return void
	 */
	public static function trace( string $message, string $module = 'CORE' ): void {
		self::write( $message, LogLevel::TRACE, $module );
	}

	/**
	 * Log a warning message.
	 *
	 * @param string $message The message to log.
	 * @param string $module Originating module.
	 *
	 * @return void
	 */
	public static function warn( string $message, string $module = 'CORE' ): void {
		self::write( $message, LogLevel::WARN, $module );
	}

	/**
	 * Log an error message with optional context.
	 *
	 * @param string $message The message to log.
	 * @param string $module Originating module.
	 * @param array  $context Additional data to log.
	 *
	 * @return void
	 */
	public static function error( string $message, string $module = 'CORE', array $context = array() ): void {
		if ( ! empty( $context ) ) {
			$message .= ' | Context: ' . wp_json_encode( $context );
		}
		self::write( $message, LogLevel::ERROR, $module );
	}

	/**
	 * Log a debug message.
	 *
	 * @param string $message The message to log.
	 * @param string $module Originating module.
	 *
	 * @return void
	 */
	public static function debug( string $message, string $module = 'CORE' ): void {
		self::write( $message, LogLevel::DEBUG, $module );
	}
}
