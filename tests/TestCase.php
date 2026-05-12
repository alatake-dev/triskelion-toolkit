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
		$this->mock_constants();
		$this->mock_wp_filesystem();
		$this->mock_wp_creation();
	}

	public function mock_constants(): void {
		$project_root = dirname( __DIR__ ) . '/';
		if ( ! defined( 'TRISKELION_TOOLKIT_VERSION' ) ) {
			define( 'TRISKELION_TOOLKIT_VERSION', '1.0.0' );
		}
		if ( ! defined( 'TRISKELION_TOOLKIT_WP_DISABLED' ) ) {
			define( 'TRISKELION_TOOLKIT_WP_DISABLED', 'Tests running' );
		}
		if ( ! defined( 'TRISKELION_TOOLKIT_FILE' ) ) {
			define( 'TRISKELION_TOOLKIT_FILE', $project_root . 'triskelion-toolkit.php' );
		}
		if ( ! defined( 'TRISKELION_TOOLKIT_PATH' ) ) {
			define( 'TRISKELION_TOOLKIT_PATH',  $project_root  );
		}
	}

	public function mock_wp_creation(): void {
		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', __DIR__ . '/../' );
		}


		WP_Mock::userFunction( 'settings_errors', ['return' => null]);
		WP_Mock::userFunction( 'settings_fields', ['return' => null]);
		WP_Mock::userFunction( 'submit_button', [ 'return' => '<button>Save</button>' ] );
		WP_Mock::userFunction( 'do_settings_sections', [] );
		WP_Mock::userFunction( 'disabled', [
			'return' => ' disabled="disabled"',
			'print' => true
		]);
		WP_Mock::userFunction( 'selected', [
			'return' => function( $selected, $current, $echo = true ) {
				$result = ( (string) $selected === (string) $current ) ? ' selected="selected"' : '';
				if ( $echo ) {
					echo $result;
				}
				return $result;
			},
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
		$filesystem->shouldReceive( 'is_dir' )->andReturn( true );
		$filesystem->shouldReceive( 'is_writable' )->andReturn( true );

		$GLOBALS['wp_filesystem'] = $filesystem;

		WP_Mock::userFunction( 'WP_Filesystem', [
			'return' => true,
		] );
	}

}