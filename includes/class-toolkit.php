<?php
namespace Triskelion\Toolkit;

class Toolkit {

	public static function get_modules() {
		// cualquier nuevo módulo debe agregar una entrada aquí
		return [
			'code-console'   => [
				'name'  => 'Consola de Código',
				'class' => 'Triskelion\\Toolkit\\Modules\\CodeConsole\\Loader'
			],
			'custom-quotes'  => [
				'name'  => 'Bloque de Citas',
				'class' => 'Triskelion\\Toolkit\\Modules\\CustomQuotes\\Loader'
			],
		];
	}

    public static function init() {
	    error_log("Toolkit activado");
    }

	public static function register_vendor_assets() {
		// Registramos Prism UNA SOLA VEZ bajo el handle 'tsk-prism'
		wp_register_script(
			'tsk-prism',
			TSK_URL . 'assets/vendor/prism/prism.js',
			[],
			'1.30.0',
			true // En el footer, por favor
		);

		wp_register_style(
			'tsk-prism-theme',
			TSK_URL . 'assets/vendor/prism/prism.css',
			[],
			'1.30.0'
		);
	}


}