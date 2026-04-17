<?php
namespace Triskelion\Toolkit\Modules;

class VendorRegistry {
	private static array $registered = [];

	/**
	 * El punto de entrada para cualquier módulo que necesite una librería.
	 */
	public static function use( string $library ): void {
		if ( isset( self::$registered[ $library ] ) ) {
			return;
		}

		switch ( $library ) {
			case 'prism':
				self::register_prism();
				break;
			// Aquí irán futuras librerías como 'chartjs', 'lottie', etc.
		}

		self::$registered[ $library ] = true;
	}

	private static function register_prism(): void {
		$version  = '1.30.0';
		$base_url = trailingslashit( TSK_URL ) . 'assets/vendor/prism/';

		wp_register_script('tsk-prism-core', $base_url . 'prism.min.js', [], $version, true);

		wp_register_style('tsk-prism-theme', $base_url . 'themes/prism-okaidia.min.css', [], $version);

		wp_register_script(
			'tsk-prism-autoloader',
			$base_url . 'plugins/autoloader/prism-autoloader.min.js',
			['tsk-prism-core'],
			$version,
			true
		);

		$components_path = $base_url . 'components/';

		$inline_config = "
        window.Prism = window.Prism || {};
        Prism.plugins = Prism.plugins || {};
        Prism.plugins.autoloader = Prism.plugins.autoloader || {};
        Prism.plugins.autoloader.languages_path = '$components_path';
    ";

		wp_add_inline_script('tsk-prism-core', $inline_config, 'before');
	}}
