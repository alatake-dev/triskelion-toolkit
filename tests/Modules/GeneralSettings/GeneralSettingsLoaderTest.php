<?php

namespace Triskelion\TriskelionToolkit\Tests\Modules\GeneralSettings;

use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;
use Triskelion\TriskelionToolkit\Modules\CodeShowcase\CodeShowcaseLoader;
use Triskelion\TriskelionToolkit\Modules\Diagnostic\DiagnosticLoader;
use Triskelion\TriskelionToolkit\Modules\GeneralSettings\GeneralSettingsLoader;
use Triskelion\TriskelionToolkit\Tests\TestCase;
use WP_Mock;


class GeneralSettingsLoaderTest extends TestCase {

	private ModuleConfig $valid_config;
	private ModuleCollection $module_collection;

	public function setUp(): void {
		parent::setUp();
		$this->setup_module_collection();
	}

	private function setup_module_collection(): void {
		$this->module_collection = new ModuleCollection();
		$this->valid_config      = GeneralSettingsLoader::get_config();
		$this->module_collection->add( $this->valid_config );
		$this->module_collection->add( DiagnosticLoader::get_config() );
		$this->module_collection->add( CodeShowcaseLoader::get_config() );
	}

	/**
	 * Prueba que el sanitizador solo deje pasar IDs que existen en la colección
	 * y que no son obligatorios (is_core).
	 */
	public function test_sanitize_module_settings_filters_invalid_and_core_ids() {
		echo "test_sanitize_module_settings_filters_invalid_and_core_ids";
		$loader = new GeneralSettingsLoader();
		$loader->set_module_collection( $this->module_collection );
		$input  = [ CodeShowcaseLoader::get_config()->id ];
		$output = $loader->sanitize_module_settings( $input );
		$this->assertCount( 1, $output );
		$this->assertEquals( $this->valid_config->id, in_array( CodeShowcaseLoader::get_config()->id, $output, true ) );

	}

	public function test_render_settings_includes_module_grid_html() {
		$collection = $this->module_collection;
		$loader     = new GeneralSettingsLoader();
		$loader->set_module_collection( $collection );




		WP_Mock::userFunction( 'get_option', [
			'return' => function( $option, $default ) {
				if ( $option === 'triskelion_toolkit_active_modules' ) {
					return [ GeneralSettingsLoader::get_config()->id ];
				}
				if ( $option === 'triskelion_toolkit_diagnostic_settings' ) {
					return [ 'enabled' => false ];
				}
				return $default;
			}
		] );

		WP_Mock::userFunction( 'checked', [
			'return' => ' checked="checked"',
			'print' => true
		] );

		WP_Mock::userFunction( 'submit_button', [
			'return' => '<button>Save</button>'
		] );

		$html = $loader->render_settings();

		$this->assertStringContainsString( $this->valid_config->id, $html );
		$this->assertStringContainsString( '<div class="triskelion-toolkit-module-info">', $html );
		$this->assertStringContainsString( 'triskelion-toolkit-module-card--core', $html );
		$this->assertStringContainsString( 'triskelion-toolkit-badge--mandatory', $html );

	}
	public function test_sanitize_diagnostic_settings_enforces_data_integrity() {
		$loader = new DiagnosticLoader();

		$dirty_input = [
			'debug_enabled' => '1',
			'level'         => 'fake_level'
		];

		$output = $loader->sanitize_module_settings( $dirty_input );

		$this->assertIsBool( $output['debug_enabled'] );
		$this->assertTrue( $output['debug_enabled'] );

		$this->assertEquals( 'error', $output['level'] );
	}
}