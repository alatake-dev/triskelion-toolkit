<?php
/**
 * Logger Class File.
 *
 * @package Triskelion\TriskelionToolkit
 * @since 1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core;

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
	 * Log levels constants.
	 */
	public const LEVEL_DEBUG = 'debug';
	public const LEVEL_INFO  = 'info';
	public const LEVEL_WARN  = 'warn';
	public const LEVEL_ERROR = 'error';
	public const LEVEL_OFF   = 'off';
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

	/**
	 * Sets a value for a mocked constant during unit tests.
	 *
	 * @param string $name  Constant name.
	 * @param mixed  $value Constant value.
	 * @return void
	 */
	public static function set_test_constant( string $name, $value ): void {
		self::$test_constants[ $name ] = $value;
	}
	/**
	 * Returns available log levels.
	 *
	 * @return array List of severity levels.
	 */
	public static function get_levels(): array {
		return array_keys( self::get_severity_map() );
	}
	/**
	 * Returns severity weights for filtering.
	 *
	 * @return array Map of level => weight.
	 */
	public static function get_severity_map(): array {
		return array(
			self::LEVEL_DEBUG => 0,
			self::LEVEL_INFO  => 1,
			self::LEVEL_WARN  => 2,
			self::LEVEL_ERROR => 3,
			self::LEVEL_OFF   => 4,
		);
	}

	/**
	 * Log an info message.
	 *
	 * @param string $message The message to log.
	 * @param string $module  Originating module.
	 * @return void
	 */
	public static function info( string $message, string $module = 'CORE' ): void {
		self::write( $message, 'info', $module );
	}
	/**
	 * Core write method. Handles initialization, rotation and writing.
	 *
	 * @param string $message The message to log.
	 * @param string $level   Severity level.
	 * @param string $module  Originating module.
	 * @return void
	 */
	private static function write( string $message, string $level, string $module ): void {
		self::init();

		$config = self::get_config();
		if ( ! $config['enabled'] ) {
			return;
		}

		$severity      = self::get_severity_map();
		$msg_weight    = $severity[ $level ] ?? 3;
		$thresh_weight = $severity[ $config['level'] ] ?? 3;

		if ( $msg_weight < $thresh_weight ) {
			return;
		}

		$file = self::$log_path . '/triskelion.log';

		if ( file_exists( $file ) && filesize( $file ) > self::$max_size ) {
			self::$wp_filesystem->move( $file, self::$log_path . '/triskelion-' . gmdate( 'Ymd-His' ) . '.bak' );
			self::cleanup_backups();
		}

		$entry = sprintf(
			"[%s] [%s] [%-12s] %s\n",
			gmdate( 'Y-m-d H:i:s' ),
			str_pad( strtoupper( $level ), 5 ),
			strtoupper( substr( $module, 0, 12 ) ),
			$message
		);
		self::direct_write( $file, $entry );
	}
	/**
	 * Initializes the logger and sets up the WP_Filesystem.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( self::$initialized ) {
			return;
		}
		self::$initialized = true;
		if ( empty( $GLOBALS['wp_filesystem'] ) ) {
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			WP_Filesystem();
		}
		if ( ! empty( self::$log_path ) || ! function_exists( 'wp_upload_dir' ) ) {
			return;
		}

		$upload_dir = wp_upload_dir();

		if ( ! empty( $upload_dir['error'] ) ) {
			return;
		}

		self::$log_path = wp_normalize_path( $upload_dir['basedir'] . '/triskelion-logs' );

		if ( ! file_exists( self::$log_path ) ) {
			wp_mkdir_p( self::$log_path );
		}

		if ( file_exists( self::$log_path ) ) {
			self::secure_directory();
		}
	}
	/**
	 * Adds security files to the log directory.
	 *
	 * @return void
	 */
	private static function secure_directory(): void {
		self::init();
		self::$wp_filesystem->put_contents( self::$log_path . '/.htaccess', 'Deny from all' );
		self::$wp_filesystem->put_contents( self::$log_path . '/index.php', '<?php // Silence' );
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
	 * Log a warning message.
	 *
	 * @param string $message The message to log.
	 * @param string $module  Originating module.
	 * @return void
	 */
	public static function warn( string $message, string $module = 'CORE' ): void {
		self::write( $message, 'warn', $module );
	}
	/**
	 * Log an error message with optional context.
	 *
	 * @param string $message The message to log.
	 * @param string $module  Originating module.
	 * @param array  $context Additional data to log.
	 * @return void
	 */
	public static function error( string $message, string $module = 'CORE', array $context = array() ): void {
		if ( ! empty( $context ) ) {
			$message .= ' | Context: ' . wp_json_encode( $context );
		}
		self::write( $message, 'error', $module );
	}
	/**
	 * Log a debug message.
	 *
	 * @param string $message The message to log.
	 * @param string $module  Originating module.
	 * @return void
	 */
	public static function debug( string $message, string $module = 'CORE' ): void {
		self::write( $message, 'debug', $module );
	}
	/**
	 * Returns the full path to the log file.
	 *
	 * @return string Full file path.
	 */
	public static function get_log_path(): string {
		if ( empty( self::$log_path ) ) {
			self::init();
		}

		return self::$log_path . '/triskelion.log';
	}
	/**
	 * Performs direct file write using PHP filesystem functions for performance.
	 *
	 * @param string $file  File path.
	 * @param string $entry Log entry.
	 * @return mixed
	 */
	protected static function direct_write( string $file, string $entry ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$handle = fopen( $file, 'a' );
		if ( $handle ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
			fwrite( $handle, $entry );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			fclose( $handle );
		}
	}
}
