<?php

namespace Triskelion\TriskelionToolkit\Tests;

use Mockery;
use Triskelion\TriskelionToolkit\Core\Logger;
use WP_Mock;

class TestCase extends \WP_Mock\Tools\TestCase {
	/**
	 * Se ejecuta antes de cada test.
	 */
	public function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();
		$this->set_env_debug( false );
		$this->mock_wp_filesystem();
		$this->mock_wp_creation();
	}

	public function mock_wp_creation(): void {
		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', __DIR__ . '/../' );
		}
		$this->set_env_debug( false );


		WP_Mock::userFunction( 'settings_errors', ['return' => null]);
		WP_Mock::userFunction( 'settings_fields', ['return' => null]);
		WP_Mock::userFunction( 'submit_button', [ 'return' => '<button>Save</button>' ] );
		WP_Mock::userFunction( 'do_settings_sections', [] );
		WP_Mock::userFunction( 'disabled', [
			'return' => ' disabled="disabled"',
			'print' => true
		]);


	}

	/**
	 * Se ejecuta después de cada test.
	 * Verifica que todas las expectativas de los mocks se hayan cumplido.
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
		Mockery::close();
		parent::tearDown();
	}

	protected function mock_wp_filesystem(): void {
		$filesystem = Mockery::mock( 'stdClass' );

		$filesystem->shouldReceive( 'put_contents' )->andReturn( true );
		$filesystem->shouldReceive( 'get_contents' )->andReturn( '' );
		$filesystem->shouldReceive( 'exists' )->andReturn( true );
		$filesystem->shouldReceive( 'move' )->andReturn( true );
		$filesystem->shouldReceive( 'abspath' )->andReturnArg( 0 );

		$GLOBALS['wp_filesystem'] = $filesystem;

		WP_Mock::userFunction( 'WP_Filesystem', [
			'return' => true,
		] );
	}

	protected function set_env_debug( bool $enabled, string $level = 'error' ): void {
		Logger::set_test_constant( 'TRISKELION_TOOLKIT_DEBUG', $enabled );
		Logger::set_test_constant( 'TRISKELION_TOOLKIT_LOG_LEVEL', $level );
	}
}