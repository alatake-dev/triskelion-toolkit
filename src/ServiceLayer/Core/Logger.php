<?php
namespace Triskelion\Toolkit\Core;

class Logger {
	private static string $log_path = '';
	private static int $max_size = 2097152; // 2MB

	public static function init(): void {
		// Si ya corrió o no estamos en WP, abortamos
		if ( ! empty( self::$log_path ) || ! function_exists( 'wp_upload_dir' ) ) {
			return;
		}

		$upload_dir = wp_upload_dir();

		// Si WP reporta error en uploads (común en Docker/Permisos)
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

	private static function get_config(): array {
		$ret_val = [];
		if ( defined( 'TSK_DEBUG' ) ) {
			$enabled = (bool) constant( 'TSK_DEBUG' );
		}
		if ( defined( 'TSK_LOG_LEVEL' ) ) {
			$level = (string) constant( 'TSK_LOG_LEVEL' );
		}

		if (! isset($enabled) || ! isset($level)) {
			$settings = $settings ?? get_option( 'tsk_settings_diagnostic', [] );
			if (! isset($enabled)) {
				$enabled    = $settings['debug_enabled'] ?? false;
			}
			if ( ! isset($level)){
				$level    = $settings['level'] ?? 'error';
			}
		}
		$ret_val['enabled'] = $enabled;
		$ret_val['level']   = strtolower( $level );


		return $ret_val;
	}

	public static function info( string $message, string $module = 'CORE' ): void {
		self::write( $message, 'info', $module );
	}

	public static function warn( string $message, string $module = 'CORE' ): void {
		self::write( $message, 'warn', $module );
	}

	public static function error( string $message, string $module = 'CORE', array $context = [] ): void {
		if ( ! empty( $context ) ) {
			$message .= ' | Context: ' . json_encode( $context );
		}
		self::write( $message, 'error', $module );
	}

	public static function debug( string $message, string $module = 'CORE' ): void {
		self::write( $message, 'debug', $module );
	}



	private static function write( string $message, string $level, string $module ): void {
		$config = self::get_config();

		// Cortocircuito: Si está apagado, fuera.
		if ( ! $config['enabled'] ) {
			return;
		}

		// Validar Nivel
		$levels = [ 'debug' => 0, 'info' => 1, 'warn' => 2, 'error' => 3, 'off' => 4 ];
		$msg_level   = $levels[ $level ] ?? 3;
		$thresh_level = $levels[ $config['level'] ] ?? 3;

		if ( $msg_level < $thresh_level ) {
			return;
		}

		$file = self::$log_path . '/triskelion.log';

		if ( file_exists( $file ) && filesize( $file ) > self::$max_size ) {
			rename( $file, self::$log_path . '/triskelion-' . date( 'Ymd-His' ) . '.bak' );
			self::cleanup_backups();
		}

		$entry = sprintf(
			"[%s] [%s] [%-12s] %s\n",
			date( 'Y-m-d H:i:s' ),
			str_pad( strtoupper( $level ), 5 ),
			strtoupper( substr( $module, 0, 12 ) ),
			$message
		);

		file_put_contents( $file, $entry, FILE_APPEND );
	}

	private static function secure_directory(): void {
		file_put_contents( self::$log_path . '/.htaccess', "Deny from all" );
		file_put_contents( self::$log_path . '/index.php', "<?php // Silence" );
	}

	private static function cleanup_backups(): void {
		$files = glob( self::$log_path . '/*.bak' );
		if ( count( $files ) > 3 ) {
			array_multisort( array_map( 'filemtime', $files ), SORT_ASC, $files );
			unlink( $files[0] );
		}
	}

	public static function get_log_path(): string {
		if ( empty( self::$log_path ) ) {
			self::init();
		}
		return self::$log_path . '/triskelion.log';
	}
}