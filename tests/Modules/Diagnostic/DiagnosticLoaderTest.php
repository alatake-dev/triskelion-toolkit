<?php

namespace Triskelion\TriskelionToolkit\Tests\Modules\Diagnostic;

use Triskelion\TriskelionToolkit\Modules\Diagnostic\DiagnosticLoader;
use Triskelion\TriskelionToolkit\Core\Logger;
use Triskelion\TriskelionToolkit\Tests\TestCase;
use WP_Mock;

class DiagnosticLoaderTest extends TestCase {

	public function test_render_settings_displays_diagnostic_info() {
		$this->set_env_debug( true, Logger::LEVEL_OFF );
		WP_Mock::userFunction( 'get_option', [
			'return' => function( $option, $default = [] ) {
				if ( 'triskelion_toolkit_diagnostic_settings' === $option ) {
					return [ 'debug_enabled' => true, 'level' => Logger::LEVEL_DEBUG ];
				}
				return $default;
			}
		] );

		// Mocks de UI de WordPress

		WP_Mock::userFunction( 'is_admin', ['return' => true] );

		WP_Mock::userFunction( 'checked', [ 'print' => true, 'return' => ' checked' ] );
		WP_Mock::userFunction( 'selected', [ 'print' => true, 'return' => ' selected' ] );


		$loader = new DiagnosticLoader();
		$html = $loader->render_settings();

		// Assertions del Happy Path
		$this->assertStringContainsString( 'triskelion_toolkit_diagnostic_settings[debug_enabled]', $html );
		$this->assertStringContainsString( 'triskelion_toolkit_diagnostic_settings[level]', $html );
	}

	public function test_sanitize_module_settings_validates_logger_levels() {
		$this->set_env_debug( true, Logger::LEVEL_OFF );
		$loader = new DiagnosticLoader();
		$input = [
			'debug_enabled' => '1',
			'level'         => Logger::LEVEL_ERROR
		];

		$output = $loader->sanitize_module_settings( $input );

		$this->assertTrue( $output['debug_enabled'] );
		$this->assertEquals( Logger::LEVEL_ERROR, $output['level'] );
	}
}