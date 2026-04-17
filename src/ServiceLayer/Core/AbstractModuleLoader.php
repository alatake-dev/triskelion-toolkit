<?php
namespace Triskelion\Toolkit\Core;

use ReflectionClass;

abstract class AbstractModuleLoader {

	protected string $module_id;
	protected string $module_path;
	protected string $module_url;

	public function __construct(string $module_id) {
		$this->module_id = $module_id;
		$reflection        = new ReflectionClass( $this );
		$this->module_path = dirname( $reflection->getFileName() );

		$relative_path     = str_replace( wp_normalize_path( TSK_PATH ), '', wp_normalize_path( $this->module_path ) );
		$this->module_url  = plugins_url( $relative_path, TSK_FILE );
	}

	public function enqueue_module_styles(): void {
		$css_file = $this->module_path . '/style.css';

		if ( file_exists( $css_file ) ) {
			wp_enqueue_style(
				"tsk-module-$this->module_id",
				$this->module_url . '/style.css',
				[],
				TSK_VERSION
			);
		}
	}
	abstract public function load(): void;
	abstract protected function render_module_fields(): void;

	protected function render_header(): void {}


	public function get_settings_group(): string {
		return "tsk_{$this->module_id}_group";
	}
	final public function render_module_settings(): void {
		wp_enqueue_style('tsk-admin-styles');
		echo '<div class="tsk-tab-content-wrapper">';
		$this->render_header();
		$this->render_form_start();
		$this->render_module_fields();
		$this->render_form_end();
		echo '</div>';
		Logger::info("render_module_settings, 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890 1234567890", "BOOT");

	}

	public function sanitize_module_settings( $input ) {
		return $input;
	}

	private function render_form_start(): void {
		echo '<form method="post" action="options.php" class="tsk-modules-form">';
		settings_fields( $this->get_settings_group() );
		do_settings_sections( $this->get_settings_group() );
		Logger::info("render_form_start", "BOOT");
	}

	private function render_form_end(): void {
		submit_button();
		echo '</form>';
		Logger::info("render_form_end", "BOOT");
	}
}