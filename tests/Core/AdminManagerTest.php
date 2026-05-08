<?php

namespace Triskelion\TriskelionToolkit\Tests\Core;

use Mockery;
use Triskelion\TriskelionToolkit\Core\AdminManager;
use Triskelion\TriskelionToolkit\Core\Bridge\WpBridge;
use Triskelion\TriskelionToolkit\Core\Bridge\WpMenu;
use Triskelion\TriskelionToolkit\Core\Bridge\WpSecurity;
use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Modules\CodeShowcase\CodeShowcaseLoader;
use Triskelion\TriskelionToolkit\Modules\Diagnostic\DiagnosticLoader;
use Triskelion\TriskelionToolkit\Tests\TestCase;
use WP_Mock;

/**
 * Class AdminManagerTest
 *
 * @package Triskelion\TriskelionToolkit\Tests\Core
 */
class AdminManagerTest extends TestCase {

	/**
	 * @var ModuleCollection
	 */
	private $module_collection;

	private WpBridge $wp_bridge;

	/**
	 * Set up test environment.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->module_collection = new ModuleCollection();
		$this->wp_bridge = new WpBridge();
		if ( ! defined( 'TRISKELION_TOOLKIT_FILE' ) ) {
			define( 'TRISKELION_TOOLKIT_FILE', 'triskelion-toolkit/triskelion-toolkit.php' );
		}
	}

	private function get_admin_manager(): AdminManager {
		$active_loaders = array(
			'diagnostic' => new DiagnosticLoader()
		);
		return new AdminManager( $this->module_collection, $active_loaders );
	}


	/**
	 * Test 2: Enlace de ajustes en la lista de plugins
	 */
	public function test_add_settings_link() {
		$links = array( '<a href="test.php">Other</a>' );
		$url   = 'http://example.org/wp-admin/admin.php?page=triskelion-toolkit';
		$manager = $this->get_admin_manager();
		$result = $manager->add_settings_link( $links );
		var_dump($result);
		$expected = '<a href="admin.php?page=triskelion-toolkit">';
		var_dump($expected);
		$this->assertStringContainsString( $expected, $result[0] );
	}

	public function test_add_toolkit_menu() {
		$wp        = new WpBridge();
		$menu_mock = $this->createMock( WpMenu::class );
		$menu_mock->expects( $this->once() )
		          ->method( 'add_submenu_page' )
		          ->with( 'tools.php',
			          $this->anything(),
			          $this->anything(),
			          $this->anything(),
			          $this->anything(),
			          $this->anything(),
			          $this->anything());
		$wp->set_menu( $menu_mock );
		$manager = $this->get_admin_manager();
		$manager->set_wp( $wp );
		$manager->add_toolkit_menu();
	}

	public function test_enqueue_admin_assets() {
		$this->expectNotToPerformAssertions();
		WP_Mock::userFunction( 'plugin_dir_url', [
			'return' => 'https://example.com',
			'times'  => 1
		] );
		WP_Mock::userFunction( 'wp_enqueue_style', [
			'times' => 1,
			'args'  => [
				'triskelion-toolkit-admin-layout',
				Mockery::any(),
				[],
				Mockery::any()
			],
		] );
		$active_loaders = array(
			'diagnostic' => new DiagnosticLoader()
		);

		$manager = new AdminManager(
			$this->module_collection,
			$active_loaders
		);
		$manager->enqueue_admin_assets( 'tools_page_triskelion-toolkit' );
	}

	public function test_trigger_module_settings() {
		$this->expectNotToPerformAssertions();
		$mock = $this->getMockBuilder( DiagnosticLoader::class )
		             ->disableOriginalConstructor()
		             ->getMock();

		$active_loaders = array(
			'diagnostic' => $mock
		);

		$manager = new AdminManager(
			$this->module_collection,
			$active_loaders
		);
		$manager->trigger_module_settings();
	}

	public function test_add_block_categories_merges_correctly() {
		$manager = $this->get_admin_manager();

		$initial_categories = array(
			array(
				'slug'  => 'text',
				'title' => 'Texto',
				'icon'  => null,
			),
		);

		$result = $manager->add_block_categories( $initial_categories );

		$this->assertCount( 2, $result, 'El array resultante debería tener 2 categorías.' );

		$last_category = end( $result );
		$this->assertEquals( 'triskelion', $last_category['slug'] );
		$this->assertEquals( 'Triskelion', $last_category['title'] );
		$this->assertEquals( 'admin-generic', $last_category['icon'] );
	}

	public function test_init() {
		$this->expectNotToPerformAssertions();
		$wp        = new WpBridge();
		$active_loaders = array(
			'diagnostic' => new DiagnosticLoader()
		);
		$manager = new AdminManager( $this->module_collection, $active_loaders );
		$manager->set_wp( $wp );
		$manager->add_toolkit_menu();

		// Usamos cargadores que no choquen con la inicialización interna
		WP_Mock::userFunction( 'plugin_basename', [
			'return' => 'mi-plugin/mi-plugin.php', // El valor que esperas recibir
			'times'  => 1, // Opcional: asegurar que se llame exactamente una vez
		] );
		$active_loaders = array(
			'diagnostic'    => new DiagnosticLoader(),
			'code_showcase' => new CodeShowcaseLoader(),
		);

		$manager = new AdminManager(
			$this->module_collection,
			$active_loaders
		);
		$manager->init();
	}

	/**
	 * Test 3: Renderizado del Layout
	 */
	public function test_render_toolkit_page_layout() {
		// Mocks de persistencia mínimos
		WP_Mock::userFunction( 'get_option' )->andReturn( array() );

		// Agregamos configuraciones reales (sin GeneralSettings para evitar el error de propiedad tipada)
		$this->module_collection->add( DiagnosticLoader::get_config() );
		$this->module_collection->add( CodeShowcaseLoader::get_config() );

		// Usamos cargadores que no choquen con la inicialización interna
		$active_loaders = array(
			'diagnostic'    => new DiagnosticLoader(),
			'code_showcase' => new CodeShowcaseLoader(),
		);

		$manager = new AdminManager(
			$this->module_collection,
			$active_loaders
		);

		// Mocks de UI de WordPress
		WP_Mock::userFunction( '__' )->andReturnUsing( function ( $text ) {
			return $text;
		} );
		WP_Mock::userFunction( 'esc_attr' )->andReturnUsing( function ( $text ) {
			return $text;
		} );
		WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function ( $text ) {
			return $text;
		} );
		WP_Mock::userFunction( 'settings_errors' );
		WP_Mock::userFunction( 'settings_fields' );
		WP_Mock::userFunction( 'do_settings_sections' );
		WP_Mock::userFunction( 'submit_button' );

		ob_start();
		$manager->render_layout();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'triskelion-toolkit-admin-layout', $output );
		$this->assertStringContainsString( 'tab=diagnostic', $output );
		$this->assertStringContainsString( 'tab=code_showcase', $output );
	}
}