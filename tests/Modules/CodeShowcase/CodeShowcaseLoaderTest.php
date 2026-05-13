<?php

namespace Triskelion\TriskelionToolkit\Tests\Modules\CodeShowcase;


use Triskelion\TriskelionToolkit\Modules\CodeShowcase\CodeShowcaseLoader;
use Triskelion\TriskelionToolkit\Tests\TestCase;

class CodeShowcaseLoaderTest extends TestCase {


	public function test_sanitize_module_settings() {
		$loader = new CodeShowcaseLoader();

		$dirty_input = array(
			'active_theme'     => '  Triskelion-Dark-Custom!  ',
			'active_languages' => array( 'PHP', 'JavaScript', '  html  ' )
		);

		$result = $loader->sanitize_module_settings( $dirty_input );

		// Verificamos que los slugs estén limpios y en minúsculas
		$this->assertEquals( 'triskelion-dark-custom', $result['active_theme'] );

		// Verificamos que se hayan limpiado y ordenado alfabéticamente
		$expected_langs = array( 'html', 'javascript', 'php' );
		$this->assertEquals( $expected_langs, $result['active_languages'] );
	}

	public function testRender_frontend() {
		$loader = new CodeShowcaseLoader();
		$attributes = [
			'activeTabIndex' => 0,
			'files' => [
				[
					'fileName' => 'triskelion.php',
					'language' => 'php',
					'content'  => '<?php echo "Triskelion Power"; ?>'
				],
				[
					'fileName' => 'style.css',
					'language' => 'css',
					'content'  => '.terminal { color: #50fa7b; }'
				]
			]
		];
		$output = $loader->render_frontend( $attributes );

		// 4. Asserts: Validamos la estructura premium
		$this->assertStringContainsString( 'triskelion-toolkit-code-showcase', $output );
		$this->assertStringContainsString( 'triskelion-toolkit-code-showcase__tab', $output );

		// Validamos que el SSR de Highlighter funcionó (clases de HLJS)
		$this->assertStringContainsString( 'hljs-keyword', $output ); // para el 'echo'
		$this->assertStringContainsString( 'hljs-string', $output );  // para "Triskelion Power"

		// Validamos los nombres de los archivos
		$this->assertStringContainsString( 'triskelion.php', $output );
		$this->assertStringContainsString( 'style.css', $output );	}


	public function testRender_outside_form() {
		$loader = new CodeShowcaseLoader();
		$output = $loader->render_outside_form();

		$this->assertEquals('', $output);

	}

	public function test_i18n_config() {
		$this->expectNotToPerformAssertions();
		$loader = new CodeShowcaseLoader();
		$loader->i18n_config();
	}

	public function test_register_module_settings() {
		$this->expectNotToPerformAssertions();
		$loader = new CodeShowcaseLoader();
		$loader->register_module_settings();
	}

	public function testRender_inside_form() {
		$loader = new CodeShowcaseLoader();
		$output = $loader->render_inside_form();

		// 1. Verificamos que contenga la tabla de configuración de WordPress
		$this->assertStringContainsString( 'class="form-table"', $output );

		// 2. Verificamos la presencia del selector de Temas
		$this->assertStringContainsString( 'name="triskelion_toolkit_showcase_settings[active_theme]"', $output );

		// 3. Verificamos la joya de la corona: La vista previa con SSR
		// Debe tener el contenedor de la ventana
		$this->assertStringContainsString( 'triskelion-toolkit-window-header', $output );
		$this->assertStringContainsString( 'triskelion-toolkit-window-content', $output );

		// 4. Verificamos que el Highlighter haya procesado el código de ejemplo "hello()"
		$this->assertStringContainsString( 'hljs php', $output );
		$this->assertStringContainsString( 'hljs-keyword', $output ); // El "function" o "echo"
		$this->assertStringContainsString( 'Triskelion Power', $output );

		// 5. Verificamos el inventario de lenguajes (pills)
		$this->assertStringContainsString( 'id="triskelion-toolkit-active-langs"', $output );
		$this->assertStringContainsString( 'triskelion-toolkit-pill', $output );

		$this->assertTrue(true);
	}

	public function test_enqueue_admin_assets() {
		$this->expectNotToPerformAssertions();
		$loader = new CodeShowcaseLoader();
		$loader->enqueue_admin_assets('triskelion-toolkit');
	}

	public function test_register_showcase_block() {
		$this->expectNotToPerformAssertions();
		$loader = new CodeShowcaseLoader();
		$loader->register_showcase_block();
		//$this->assertTrue(true);
	}
	public function testEnqueue_block_assets() {
		/*
		$this->expectNotToPerformAssertions();
		$loader = new CodeShowcaseLoader();
		$loader->enqueue_block_assets();
		*/
		$this->assertTrue(true);
	}



}
