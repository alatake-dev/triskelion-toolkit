<?php
namespace Triskelion\Toolkit\Core;

abstract class AbstractModuleLoader {

	abstract public function load() :void;
    abstract protected function render_module_fields(): void;

    /**
     * Header opcional que los hijos pueden sobrescribir
     */
    protected function render_header(): void {
        // Por defecto vacío o un título genérico
    }


	/**
	 * Helper para que los hijos registren sus assets
	 * usando el nuevo sistema de filtros de Toolkit.
	 */
	protected function register_assets(string $id, array $scripts = [], array $styles = []): void {
		if (!empty($scripts)) {
			add_filter(Toolkit::HOOK_REGISTER_SCRIPTS, function($all_scripts) use ($id, $scripts) {
				return array_merge($all_scripts, $scripts);
			});
		}

		if (!empty($styles)) {
			add_filter(Toolkit::HOOK_REGISTER_STYLES, function($all_styles) use ($id, $styles) {
				return array_merge($all_styles, $styles);
			});
		}
	}


	protected function get_custom_css(): string {
		return '';
	}
    final public function render_settings(): void {
	    wp_enqueue_style( 'tsk-admin-styles' );
		$custom_css = $this->get_custom_css();
		if (!empty($custom_css)) {
			wp_add_inline_style( 'tsk-admin-styles', $custom_css );
		}
        echo '<div class="tsk-tab-content-wrapper">';
	    ?>
	    <div class="tsk-tab-content-wrapper">
		    <?php
		    $this->render_header();
		    $this->render_form_start();
		    $this->render_module_fields();
		    $this->render_form_end();
		    ?>
	    </div>
	    <?php
    }

	private function render_form_start(): void {
		echo '<form method="post" action="options.php" class="tsk-modules-form">';
		settings_fields( 'tsk_settings' );

		$current_tab = $_GET['tab'] ?? 'general_settings';
		$return_url  = admin_url( 'tools.php?page=triskelion-toolkit&tab=' . $current_tab );
		echo '<input type="hidden" name="_wp_http_referer" value="' . esc_url( $return_url ) . '" />';
	}

	private function render_form_end(): void {
		submit_button();
		echo '</form>';
	}

}