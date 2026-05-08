<?php

namespace Triskelion\TriskelionToolkit\Modules\Diagnostic;

use Triskelion\TriskelionToolkit\Core\AbstractModule;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfigBuilder;
use Triskelion\TriskelionToolkit\Core\Interfaces\HasSettingsInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\RegistrableModuleInterface;
use Triskelion\TriskelionToolkit\Core\Logger;

class DiagnosticLoader extends AbstractModule implements RegistrableModuleInterface, HasSettingsInterface {

	public static function i18n_config(): array {
		return array(
			'name'        => __( 'Logs & Diagnostic', 'triskelion-toolkit' ),
			'description' => __( 'Monitor system health and view activity logs.', 'triskelion-toolkit' ),
		);
	}

	public function register_module_settings(): void {
		register_setting(
			'triskelion_toolkit_diagnostic_group',
			'triskelion_toolkit_diagnostic_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_module_settings' ),
				'default'           => array(
					'debug_enabled' => false,
					'level'         => Logger::LEVEL_ERROR,
				),
			)
		);
	}

	public function sanitize_module_settings( $input ): array {
		$severity_map = Logger::get_severity_map();

		$level = ( isset( $input['level'] ) && isset( $severity_map[ $input['level'] ] ) )
				? $input['level']
				: Logger::LEVEL_ERROR;

		return array(
			'debug_enabled' => isset( $input['debug_enabled'] ),
			'level'         => $level,
		);
	}

	public function render_settings(): string {
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
			add_settings_error( 'triskelion_toolkit_diagnostic_group', 'settings_updated', __( 'Settings saved.', 'triskelion-toolkit' ), 'updated' );
		}
		$options = get_option(
			'triskelion_toolkit_diagnostic_settings',
			array(
				'debug_enabled' => false,
				'level'         => Logger::LEVEL_ERROR,
			)
		);

		$is_debug_forced = defined( 'TRISKELION_TOOLKIT_DEBUG' );
		$is_level_forced = defined( 'TRISKELION_TOOLKIT_LOG_LEVEL' );

		$val_enabled = $is_debug_forced ? (bool) constant( 'TRISKELION_TOOLKIT_DEBUG' ) : $options['debug_enabled'];
		$val_level   = $is_level_forced ? constant( 'TRISKELION_TOOLKIT_LOG_LEVEL' ) : $options['level'];

		ob_start(); ?>
		<h1><?php esc_html_e( self::get_config()->name, 'triskelion-toolkit' ); ?></h1>
		<p class="description"><?php esc_html_e( self::get_config()->description, 'triskelion-toolkit' ); ?></p>
		<div class="triskelion-toolkit-diagnostic-view">
			<?php settings_errors( 'triskelion_toolkit_diagnostic_group' ); ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'triskelion_toolkit_diagnostic_group' );
				?>

				<header class="triskelion-toolkit-section-header">
					<h2><?php _e( 'Logs & Diagnostic', 'triskelion-toolkit' ); ?></h2>
					<?php if ( $is_debug_forced || $is_level_forced ) : ?>
						<p class="triskelion-toolkit-notice triskelion-toolkit-notice--info">
							<span class="dashicons dashicons-lock"></span>
							<?php _e( 'Configuration managed via code (wp-config.php).', 'triskelion-toolkit' ); ?>
						</p>
					<?php endif; ?>
				</header>

				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Enable Logging', 'triskelion-toolkit' ); ?></th>
						<td>
							<label class="triskelion-toolkit-switch <?php echo $is_debug_forced ? 'triskelion-toolkit-disabled' : ''; ?>">
								<input type="checkbox"
										name="triskelion_toolkit_diagnostic_settings[debug_enabled]"
										value="1"
										<?php checked( true, (bool) $val_enabled ); ?>
										<?php disabled( $is_debug_forced ); ?>>
								<span class="triskelion-toolkit-slider"></span>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Log Level', 'triskelion-toolkit' ); ?></th>
						<td>
							<select name="triskelion_toolkit_diagnostic_settings[level]" <?php disabled( $is_level_forced ); ?>>
								<?php foreach ( Logger::get_levels() as $lvl ) : ?>
									<option value="<?php echo $lvl; ?>" <?php selected( $lvl, $val_level ); ?>>
										<?php echo strtoupper( $lvl ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</table>

				<?php
				submit_button( __( 'Save Changes', 'triskelion-toolkit' ) );
				?>
			</form>
			<?php $this->render_log_viewer(); ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function get_config(): ModuleConfig {
		return ( new ModuleConfigBuilder() )
				->set_id( 'diagnostic' )
				->set_name( 'Logs & Diagnostic' )
				->set_description( 'Monitor system health and view activity logs.' )
				->set_class( self::class )
				->set_priority( 1000 )
				->set_is_core( true )
				->set_icon( 'dashicons-rest-api' )
				->build();
	}

	private function render_log_viewer(): void {
		$log_file = Logger::get_log_path();
		// Lógica limpia: si no hay archivo, mostramos un placeholder técnico
		$content = file_exists( $log_file )
				? implode( '', array_slice( file( $log_file ), - 100 ) )
				: '--- SYSTEM READY: NO LOG ENTRIES FOUND ---';
		?>
		<section class="triskelion-toolkit-terminal">
			<header class="triskelion-toolkit-terminal__header">
				<div class="triskelion-toolkit-terminal__info">
					<span class="dashicons dashicons-terminal"></span>
					<span class="triskelion-toolkit-terminal__title"><?php esc_attr_e( 'Triskelion Logs', 'triskelion-toolkit' ); ?></span>
				</div>
				<div class="triskelion-toolkit-terminal__actions">
					<button type="button" class="triskelion-toolkit-terminal__btn triskelion-toolkit-copy-trigger"
							title="<?php esc_attr_e( 'Copy to Clipboard', 'triskelion-toolkit' ); ?>">
						<span class="dashicons dashicons-admin-page"></span>
					</button>
					<button type="button" class="triskelion-toolkit-terminal__btn triskelion-toolkit-refresh-trigger"
							title="<?php esc_attr_e( 'Refresh Log', 'triskelion-toolkit' ); ?>">
						<span class="dashicons dashicons-update"></span>
					</button>
				</div>
			</header>
			<textarea
					readonly
					class="triskelion-toolkit-terminal__body"
					spellcheck="false"
			><?php echo esc_textarea( trim( $content ) ); ?></textarea>
		</section>

		<script>
			(function () {
				const terminal = document.querySelector('.triskelion-toolkit-terminal');
				const output = terminal?.querySelector('.triskelion-toolkit-terminal__body');

				terminal?.addEventListener('click', (e) => {
					const btn = e.target.closest('.triskelion-toolkit-terminal__btn');
					if (!btn) return;

					if (btn.classList.contains('triskelion-toolkit-copy-trigger')) {
						navigator.clipboard.writeText(output.value);
						const icon = btn.querySelector('.dashicons');
						icon.classList.replace('dashicons-admin-page', 'dashicons-yes');
						setTimeout(() => icon.classList.replace('dashicons-yes', 'dashicons-admin-page'), 1500);
					}

					if (btn.classList.contains('triskelion-toolkit-refresh-trigger')) {
						window.location.reload();
					}
				});
			})();
		</script>
		<?php
	}

	protected function register(): void {
		if ( $this->wp->security->is_admin() ) {
			$this->wp->hooks->add_action( 'admin_init', array( $this, 'register_module_settings' ) );
		}
	}
}