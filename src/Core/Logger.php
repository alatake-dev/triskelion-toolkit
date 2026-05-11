<?php
/**
 * Logger Class File.
 *
 * @package Triskelion\TriskelionToolkit
 * @since 1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core;

use Triskelion\TriskelionToolkit\Core\Enums\LogLevel;
use Triskelion\TriskelionToolkit\Core\Exceptions\FileSystemException;
use WP_Filesystem_Base;

/**
 * Class Logger
 *
 * Handles application logging, including file rotation and directory security.
 *
 * @package Triskelion\TriskelionToolkit
 * @since 1.0.0
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
	 * Initialization flag.
	 *
	 * @var bool
	 */
	private static bool $initialized = false;
	/**
	 * Storage for mocked constants during testing.
	 *
	 * @var array
	 */
	private static array $test_constants = array();

	private static bool $admin_notice = false;

	/**
	 * Sets a value for a mocked constant during unit tests.
	 *
	 * @param string $name Constant name.
	 * @param mixed  $value Constant value.
	 *
	 * @return void
	 */
	public static function set_test_constant( string $name, $value ): void {
		self::$test_constants[ $name ] = $value;
	}


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
		$config = self::get_config();
		if ( ! $config['enabled'] ) {
			return;
		}

		$saved_value  = (int) get_option( 'triskelion_toolkit_diagnostic_settings' )['level'];
		$config_level = LogLevel::tryFrom( $saved_value ) ?? LogLevel::OFF;

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
	 * Initializes the logger and sets up the WP_Filesystem.
	 *
	 * @throws FileSystemException In any error.
	 */
	public static function init(): void {
		if ( self::$initialized ) {
			return;
		}
		self::$initialized = true;
		try {
			$upload_dir     = self::get_file_system_status();
			self::$log_path = wp_normalize_path( $upload_dir['basedir'] . '/triskelion-logs' );

			$fs = self::get_filesystem();

			if ( ! $fs->is_dir( self::$log_path ) ) {
				if ( ! $fs->mkdir( self::$log_path, '0755' ) ) {
					self::error_log( 'No se pudo crear la carpeta de logs.' );
					throw new FileSystemException( 'No se pudo crear la carpeta de logs.' );
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
				self::error_log( 'Carpeta de logs no escribible. Dueño actual: ' . $owner );
				throw new FileSystemException( 'Carpeta de logs no escribible. Dueño actual: ' . $owner );
			}
			self::info( 'Logger initialized successfully.' );
		} catch ( FileSystemException $e ) {
			self::$initialized = false;
			self::error_log( 'admin_notice: ' . self::$admin_notice );
			self::register_admin_error_notice( $e->getMessage() );
		}
	}

	/**
	 * Adds security files to the log directory.
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
	 * Retrieves logger configuration from constants or database.
	 *
	 * @return array Configuration data.
	 */
	private static function get_config(): array {
		$ret_val   = array();
		$env_debug = self::get_env_constant( 'TRISKELION_TOOLKIT_DEBUG' );
		if ( null !== $env_debug ) {
			$enabled = (bool) $env_debug;
		}

		$env_level = self::get_env_constant( 'TRISKELION_TOOLKIT_LOG_LEVEL' );
		if ( null !== $env_level ) {
			$level = (string) $env_level;
		}
		if ( ! isset( $enabled ) || ! isset( $level ) ) {
			$settings = $settings ?? get_option( 'triskelion_toolkit_diagnostic_settings', array() );
			if ( ! isset( $enabled ) ) {
				$enabled = $settings['debug_enabled'] ?? false;
			}
			if ( ! isset( $level ) ) {
				$level = $settings['level'] ?? 'error';
			}
		}
		$ret_val['enabled'] = $enabled;
		$ret_val['level']   = strtolower( $level );

		return $ret_val;
	}

	/**
	 * Environment-aware constant retriever. Supports testing mocks.
	 *
	 * @param string $name Constant name.
	 *
	 * @return mixed|null Value or null if not defined.
	 */
	protected static function get_env_constant( string $name ): mixed {
		if ( isset( self::$test_constants[ $name ] ) ) {
			return self::$test_constants[ $name ];
		}

		return defined( $name ) ? constant( $name ) : null;
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

	public static function get_file_system_status(): array {
		$uploads = wp_upload_dir();

		if ( ! empty( $uploads['error'] ) ) {
			self::error_log( 'WordPress Filesystem Error [get_file_system_status]: ' . $uploads['error'] );
			throw new FileSystemException( 'WordPress Filesystem Error: ' . $uploads['error'] );
		}
		return $uploads;
	}

	/**
	 * Ensures the WordPress Filesystem global is initialized.
	 *
	 * @return \WP_Filesystem_Base
	 * @throws FileSystemException If the filesystem cannot be initialized.
	 */
	private static function get_filesystem(): WP_Filesystem_Base {
		global $wp_filesystem;
		$uploads = self::get_file_system_status();

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';

			$method = get_filesystem_method();
			if ( 'direct' !== $method ) {
				self::error_log( 'WordPress Filesystem Error [get_filesystem_method]: NOT direct' );
				throw new FileSystemException( 'Filesystem method is not "direct". Check server configuration.' );
			}

			if ( ! WP_Filesystem() ) {
				self::error_log( 'WordPress Filesystem Error [get_filesystem_method]: initialize WordPress Filesystem' );
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
		error_log( $message );
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
	private static function register_admin_error_notice( $message ) {
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
	 * Log an trce message.
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
