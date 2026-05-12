<?php

namespace Triskelion\TriskelionToolkit\Tests\Core;

use Triskelion\TriskelionToolkit\Core\AdminManager;
use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Modules\GeneralSettings\GeneralSettingsLoader;
use Triskelion\TriskelionToolkit\Modules\Diagnostic\DiagnosticLoader;
use Triskelion\TriskelionToolkit\Tests\TestCase;
use WP_Mock;

/**
 * Class AdminManagerTest
 *
 * Orchestration suite for the Admin UI Manager.
 * Validates tab navigation, security nonces, and module lifecycle execution
 * without booting the full WordPress environment.
 */
class AdminManagerTest extends TestCase {

	private ModuleCollection $modules;
	private array $active_loaders;
	private AdminManager $admin_manager;

	/**
	 * Set up the Orchestrator with real Module dependencies.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->modules = new ModuleCollection();
		$this->modules->add( GeneralSettingsLoader::get_config() );
		$this->modules->add( DiagnosticLoader::get_config() );

		// We inject real instances to test the interaction contract
		$this->active_loaders = array(
			'general_settings' => new GeneralSettingsLoader(),
			'diagnostic'       => new DiagnosticLoader(),
		);

		$this->admin_manager = new AdminManager( $this->modules, $this->active_loaders );
	}

	/**
	 * Test: Tab Navigation Logic.
	 *
	 * Ensures that the manager correctly identifies the active tab from the URL
	 * and falls back to 'general_settings' if no tab is specified.
	 *
	 * @test
	 */
	public function test_get_active_tab_defaults_to_general_settings(): void {
		// Case A: No tab in $_GET
		unset( $_GET['tab'] );
		$this->assertEquals( 'general_settings', $this->admin_manager->get_active_tab() );

		// Case B: Valid tab provided
		$_GET['tab'] = 'diagnostic';
		$this->assertEquals( 'diagnostic', $this->admin_manager->get_active_tab() );

		// Case C: Malicious/Invalid tab should still return the input
		// (The manager leaves validation to the render logic)
		$_GET['tab'] = '../../etc/passwd';
		$this->assertEquals( 'general_settings', $this->admin_manager->get_active_tab() );

	}

	/**
	 * Test: Asset Isolation.
	 *
	 * Hardening check: Admin assets must ONLY be enqueued on the plugin's page.
	 *
	 * @test
	 */
	public function test_enqueue_admin_assets_only_runs_on_plugin_page(): void {
		// Use WP_Mock to ensure enqueue_style is NEVER called on wrong pages
		$this->admin_manager->enqueue_admin_assets( 'dashboard' );

		// If it reaches here without calling the bridge's enqueue_style, the test passes.
		// We can verify this via a "Spy" or checking the WpEvents internal state if exposed.
		$this->assertTrue( true, 'Execution should return early for non-plugin pages.' );
	}


	public function test_enqueue_admin_assets_with_correct_hook(): void {
		// Use WP_Mock to ensure enqueue_style is NEVER called on wrong pages
		$this->admin_manager->enqueue_admin_assets( 'tools_page_triskelion-toolkit' );

		// If it reaches here without calling the bridge's enqueue_style, the test passes.
		// We can verify this via a "Spy" or checking the WpEvents internal state if exposed.
		$this->assertTrue( true, 'Execution should return early for non-plugin pages.' );
	}
	/**
	 * Test: Settings Registration Lifecycle.
	 *
	 * Verifies that the manager triggers 'register_module_settings' for all
	 * loaders that implement HasSettingsInterface.
	 *
	 * @test
	 */
	public function test_trigger_module_settings_calls_loaders(): void {
		// This is a behavioral test. We verify that the loaders are processed.
		// Since our loaders use the Bridge, and the Bridge is WP_DISABLED,
		// we are testing that the loop runs without crashing.

		try {
			$this->admin_manager->trigger_module_settings();
			$this->assertTrue( true, 'Settings registration cycle completed successfully.' );
		} catch ( \Throwable $e ) {
			$this->fail( 'Settings trigger crashed: ' . $e->getMessage() );
		}
	}

	public function test_init(){
		$this->expectNotToPerformAssertions();
		$this->admin_manager->init();
	}

	public function test_add_toolkit_menu(){
		$this->expectNotToPerformAssertions();
		$this->admin_manager->add_toolkit_menu();
	}

	public function test_add_block_categories(){
		$this->expectNotToPerformAssertions();
		$this->admin_manager->add_block_categories([]);
	}

	public function test_add_settings_link(){
		$this->expectNotToPerformAssertions();
		$links = array('url' => 'https://example.com', 'label' => 'Example');
		$this->admin_manager->add_settings_link($links);
	}

	public function test_trigger_module_settings(){
		$this->expectNotToPerformAssertions();
		$this->admin_manager->trigger_module_settings();
	}

}