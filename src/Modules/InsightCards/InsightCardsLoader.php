<?php
namespace Triskelion\TriskelionToolkit\Modules\InsightCards;

use src\Core\AbstractBlockLoader;
use src\Core\SettingsProviderInterface;

class InsightCardsLoader extends AbstractBlockLoader implements SettingsProviderInterface{
	protected function get_block_name(): string {
		return 'insight-cards';
	}

	public function load(): void {
		// TODO: Implement load() method.
	}

	protected function render_module_fields(): void {
		echo "<p> los settings de InsightCards </p>";
	}
	protected function render_header(): void {
		?>
		<div class="tsk-tab-header">
			<h2><?php esc_html_e( 'InsightCards', 'triskelion-toolkit' ); ?></h2>
			<p class="description"><?php esc_html_e( 'InsightCards configuration', 'triskelion-toolkit' ); ?></p>
		</div>
		<?php
	}

    public function register_module_settings(): void {
        // silence is golden
    }
}