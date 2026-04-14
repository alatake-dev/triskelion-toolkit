<?php
namespace Triskelion\Toolkit\Core;

abstract class AbstractModuleLoader {
	abstract public function load(): void;
	abstract protected function render_module_fields(): void;

	protected function render_header(): void {}

	final public function render_settings(): void {
		wp_enqueue_style('tsk-admin-styles');
		echo '<div class="tsk-tab-content-wrapper">';
		$this->render_header();
		$this->render_form_start();
		$this->render_module_fields();
		$this->render_form_end();
		echo '</div>';
	}

	private function render_form_start(): void {
		echo '<form method="post" action="options.php" class="tsk-modules-form">';
		settings_fields('tsk_settings');
		$current_tab = $_GET['tab'] ?? 'general_settings';
		$return_url = admin_url('tools.php?page=triskelion-toolkit&tab=' . $current_tab);
		echo '<input type="hidden" name="_wp_http_referer" value="' . esc_url($return_url) . '" />';
	}

	private function render_form_end(): void {
		submit_button();
		echo '</form>';
	}
}