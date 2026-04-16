<?php
namespace Triskelion\Toolkit\Modules\InsightCards;

use Triskelion\Toolkit\Core\AbstractBlockLoader;
use Triskelion\Toolkit\Core\SettingsProviderInterface;

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
			<h2><?php esc_html_e( 'InsightCards', TSK_DOMAIN ); ?></h2>
			<p class="description"><?php esc_html_e( 'InsightCards configuration', TSK_DOMAIN ); ?></p>
		</div>
		<?php
	}
}