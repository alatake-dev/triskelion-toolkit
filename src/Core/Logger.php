<?php

namespace Triskelion\TriskelionToolkit\Core;

class Logger {
	public const LEVEL_DEBUG             = 'debug';
	public const LEVEL_INFO              = 'info';
	public const LEVEL_WARN              = 'warn';
	public const LEVEL_ERROR             = 'error';
	public const LEVEL_OFF               = 'off';
	private static string $log_path      = '';
	private static int $max_size         = 2097152;
	private static bool $initialized     = false;
	private static array $test_constants = array();

	public static function set_test_constant( string $name, $value ): void {
		self::$test_constants[ $name ] = $value;
	}
	public static function get_levels(): array {
		return array_keys( self::get_severity_map() );
	} // 2MB

	public static function get_severity_map(): array {
		return array(
			self::LEVEL_DEBUG => 0,
			self::LEVEL_INFO  => 1,
			self::LEVEL_WARN  => 2,
			self::LEVEL_ERROR => 3,
			self::LEVEL_OFF   => 4,
		);
	}



	public static function info( string $message, string $module = 'CORE' ): void {
		self::write( $message, 'info', $module );
	}

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

	private static function secure_directory(): void {
		self::init();
		self::$wp_filesystem->put_contents( self::$log_path . '/.htaccess', 'Deny from all' );
		self::$wp_filesystem->put_contents( self::$log_path . '/index.php', '<?php // Silence' );
	}

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
	 * Wrapper para constantes que permite ser mockeado en tests.
	 *
	 * @param string $name
	 * @return mixed|null
	 */
	protected static function get_env_constant( string $name ): mixed {
		if ( isset( self::$test_constants[ $name ] ) ) {
			return self::$test_constants[ $name ];
		}
		return defined( $name ) ? constant( $name ) : null;
	}

	private static function cleanup_backups(): void {
		$files = glob( self::$log_path . '/*.bak' );
		if ( count( $files ) > 3 ) {
			array_multisort( array_map( 'filemtime', $files ), SORT_ASC, $files );
			wp_delete_file( $files[0] );
		}
	}

	public static function warn( string $message, string $module = 'CORE' ): void {
		self::write( $message, 'warn', $module );
	}

	public static function error( string $message, string $module = 'CORE', array $context = array() ): void {
		if ( ! empty( $context ) ) {
			$message .= ' | Context: ' . wp_json_encode( $context );
		}
		self::write( $message, 'error', $module );
	}

	public static function debug( string $message, string $module = 'CORE' ): void {
		self::write( $message, 'debug', $module );
	}

	public static function get_log_path(): string {
		if ( empty( self::$log_path ) ) {
			self::init();
		}

		return self::$log_path . '/triskelion.log';
	}

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
