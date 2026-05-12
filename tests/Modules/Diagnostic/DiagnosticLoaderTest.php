<?php

namespace Triskelion\TriskelionToolkit\Tests\Modules\Diagnostic;

use Triskelion\TriskelionToolkit\Modules\Diagnostic\DiagnosticLoader;
use Triskelion\TriskelionToolkit\Core\Enums\LogLevel;
use Triskelion\TriskelionToolkit\Tests\TestCase;

/**
 * Class DiagnosticLoaderTest
 *
 * Comprehensive test suite for the Diagnostic and System Logging module.
 * It validates strictly typed configuration handling, telemetry visualization,
 * and robust sanitization patterns using the WP_DISABLED bridge strategy.
 *
 * @package Triskelion\TriskelionToolkit\Tests\Modules\Diagnostic
 */
class DiagnosticLoaderTest extends TestCase {

	/**
	 * System Under Test (SUT).
	 *
	 * @var DiagnosticLoader
	 */
	private DiagnosticLoader $loader;

	/**
	 * Set up the test environment before each execution.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$this->loader = new DiagnosticLoader();
	}

	/**
	 * Test: Sanitization Logic with valid Enum values.
	 *
	 * Ensures that when a valid integer weight is provided (e.g., 500 for ERROR),
	 * the sanitizer correctly persists the raw value and handles boolean flags.
	 *
	 * @test
	 * @return void
	 */
	public function test_sanitize_module_settings_persists_valid_log_levels(): void {
		$input = array(
			'level'         => LogLevel::ERROR->value, // 500
		);

		$output = $this->loader->sanitize_module_settings( $input );

		$this->assertEquals( 500, $output['level'], 'Should persist the integer value of the ERROR level.' );
	}

	/**
	 * Test: Sanitization Hardening with invalid data.
	 *
	 * Logic: If a malicious or non-existent log level weight is provided,
	 * the system must fallback to INFO (300) to prevent logic corruption.
	 *
	 * @test
	 * @return void
	 */
	public function test_sanitize_module_settings_enforces_enum_fallbacks(): void {
		$garbage_input = array(
			'level'         => 9999, // Invalid level weight
		);

		$output = $this->loader->sanitize_module_settings( $garbage_input );

		$this->assertEquals( 300, $output['level'], 'Sanitizer must fallback to INFO for invalid level weights.' );
	}

	/**
	 * Test: Terminal Render Resilience.
	 *
	 * Validates that the render_outside_form method (The Terminal) handles
	 * empty log files gracefully without throwing PHP notices or breaking the DOM.
	 *
	 * @test
	 * @return void
	 */
	public function test_render_outside_form_handles_empty_telemetry(): void {


		$html = $this->loader->render_outside_form();


		$this->assertStringContainsString( 'triskelion-toolkit-terminal', $html );
		$this->assertStringContainsString( 'refresh-trigger', $html );

		// The textarea should be present even if empty
		$this->assertStringContainsString( '<textarea', $html );
	}

	/**
	 * Test: Form Integration for Log Levels.
	 *
	 * Verifies that the select dropdown iterates over the LogLevel Enum
	 * and produces the correct HTML structure for the Admin UI.
	 *
	 * @test
	 * @return void
	 */
	public function test_render_inside_form_renders_all_enum_cases(): void {
		$html = $this->loader->render_inside_form();

		// Check for specific Enum names in the select options
		foreach ( LogLevel::cases() as $case ) {
			$this->assertStringContainsString( $case->name, $html );
			$this->assertStringContainsString( 'value="' . $case->value . '"', $html );
		}

		$this->assertStringContainsString( 'triskelion_toolkit_diagnostic_settings[level]', $html );
	}
}