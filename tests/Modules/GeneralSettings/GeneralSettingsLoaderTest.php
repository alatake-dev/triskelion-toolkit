<?php

namespace Triskelion\TriskelionToolkit\Tests\Modules\GeneralSettings;

use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Modules\CodeShowcase\CodeShowcaseLoader;
use Triskelion\TriskelionToolkit\Modules\Diagnostic\DiagnosticLoader;
use Triskelion\TriskelionToolkit\Modules\GeneralSettings\GeneralSettingsLoader;
use Triskelion\TriskelionToolkit\Tests\TestCase;

/**
 * Class GeneralSettingsLoaderTest
 *
 * Test suite for GeneralSettingsLoader using the WP_DISABLED bridge strategy.
 * This approach tests real object interactions without mocking WordPress globals.
 */
class GeneralSettingsLoaderTest extends TestCase {

	private ModuleCollection $module_collection;
	private GeneralSettingsLoader $loader;

	public function setUp(): void {
		parent::setUp();

		$this->module_collection = new ModuleCollection();
		$this->module_collection->add( GeneralSettingsLoader::get_config() );
		$this->module_collection->add( DiagnosticLoader::get_config() );

		$this->loader = new GeneralSettingsLoader();
		$this->loader->set_module_collection( $this->module_collection );
	}

	public function test_sanitize_module_settings_filters_invalid_ids() {
		// 'diagnostic' y 'code_showcase' son válidos. 'hacker_mod' no existe.
		$input = array( 'diagnostic', 'hacker_mod' );

		$output = $this->loader->sanitize_module_settings( $input );

		$this->assertCount( 1, $output );
		$this->assertContains( 'diagnostic', $output );
		$this->assertNotContains( 'hacker_mod', $output );
	}

	public function test_sanitize_module_settings_handles_garbage_input() {
		$this->assertSame( array(), $this->loader->sanitize_module_settings( null ) );
		$this->assertSame( array(), $this->loader->sanitize_module_settings( 'invalid_string' ) );
		$this->assertSame( array(), $this->loader->sanitize_module_settings( 12345 ) );
	}

	/**
	 * @test
	 * Verifica que el renderizado genere el HTML esperado para los módulos.
	 * Al estar WP_DISABLED, las funciones como checked() o esc_html()
	 * deben estar cubiertas por el TestCase o el Bridge.
	 */
	public function test_render_inside_form_resilience_with_empty_db() {
		// Ejecutamos el render (usa ob_start internamente)
		$html = $this->loader->render_inside_form();
		var_dump( $html );

		// Verificamos elementos clave del DOM definido en el Loader
		$this->assertStringContainsString( 'triskelion_toolkit_general_settings[]', $html );
		$this->assertStringNotContainsString( 'Code Showcase', $html );

		// Verificamos que General Settings (is_core) tenga su badge mandatory
		$this->assertStringContainsString( 'triskelion-toolkit-badge--mandatory', $html );
	}

}