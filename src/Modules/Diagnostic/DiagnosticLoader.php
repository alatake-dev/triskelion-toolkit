<?php
/**
 * Diagnostic and System Logging Module Loader.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Modules\Diagnostic
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Modules\Diagnostic;

use Triskelion\TriskelionToolkit\Core\AbstractModule;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfigBuilder;
use Triskelion\TriskelionToolkit\Core\Enums\LogLevel;
use Triskelion\TriskelionToolkit\Core\Interfaces\HasSettingsInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\RegistrableModuleInterface;
use Triskelion\TriskelionToolkit\Core\Logger;

/**
 * Class DiagnosticLoader
 *
 * Orchestrates system diagnostics by providing an interface for log level
 * configuration and a specialized terminal-like viewer for telemetry inspection.
 *
 * @package Triskelion\TriskelionToolkit\Modules\Diagnostic
 */
class DiagnosticLoader extends AbstractModule implements RegistrableModuleInterface, HasSettingsInterface {

	/**
	 * Returns the internationalization configuration for the module.
	 *
	 * @return array{name: string, description: string} Localized strings.
	 */
	public static function i18n_config(): array {
		return array(
			'name'        => __( 'Logs & Diagnostic', 'triskelion-toolkit' ),
			'description' => __( 'Monitor system health and view activity logs.', 'triskelion-toolkit' ),
		);
	}

	/**
	 * Registers the settings group and fields in the WordPress Settings API.
	 *
	 * @return void
	 */
	public function register_module_settings(): void {
		$this->wp->settings->register_setting(
			'triskelion_toolkit_diagnostic_group',
			'triskelion_toolkit_diagnostic_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_module_settings' ),
				'default'           => array(
					'level' => LogLevel::INFO->value,
				),
			)
		);
	}

	/**
	 * Sanitizes the diagnostic settings array.
	 *
	 * @param mixed $input Raw input data from the form.
	 * @return array Validated settings.
	 */
	public function sanitize_module_settings( $input ): array {
		$level_value = isset( $input['level'] ) ? (int) $input['level'] : LogLevel::INFO->value;

		$level = LogLevel::tryFrom( $level_value )->value
				?? LogLevel::INFO->value;
		return array(
			'level' => $level,
		);
	}

	/**
	 * Renders configuration controls (Switches and Selects) inside the main form.
	 *
	 * Migrated from legacy render_settings. It focuses on the internal field
	 * elements, delegating the form container to the AdminManager.
	 *
	 * @return string The internal form HTML.
	 */
	public function render_inside_form(): string {
		$options         = $this->wp->settings->get_option(
			'triskelion_toolkit_diagnostic_settings',
			array(
				'level' => LogLevel::INFO->value,
			)
		);
		$is_level_forced = defined( 'TRISKELION_TOOLKIT_LOG_LEVEL' );

		$val_level = $is_level_forced ? constant( 'TRISKELION_TOOLKIT_LOG_LEVEL' ) : $options['level'];
		ob_start();
		?>
		<header class="triskelion-toolkit-section-header">
			<h2><?php esc_html_e( 'Logs & Diagnostic', 'triskelion-toolkit' ); ?></h2>
			<?php if ( $is_level_forced ) : ?>
				<p class="triskelion-toolkit-notice triskelion-toolkit-notice--info">
					<span class="dashicons dashicons-lock"></span>
					<?php esc_html_e( 'Configuration managed via code (wp-config.php).', 'triskelion-toolkit' ); ?>
				</p>
			<?php endif; ?>
		</header>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Log Level', 'triskelion-toolkit' ); ?></th>
				<td>
					<select name="triskelion_toolkit_diagnostic_settings[level]" <?php disabled( $is_level_forced ); ?>>
						<?php foreach ( LogLevel::cases() as $level_case ) : ?>
							<option value="<?php echo esc_attr( $level_case->value ); ?>" <?php selected( $level_case->value, (int) $val_level ); ?>>
								<?php echo esc_html( $level_case->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>
			<?php
			return ob_get_clean();
	}

	/**
	 * Defines the module configuration using the standardized builder.
	 *
	 * @return ModuleConfig The configuration object containing module metadata.
	 */
	public static function get_config(): ModuleConfig {
		return ( new ModuleConfigBuilder() )
				->set_id( 'diagnostic' )
				->set_name( 'Logs & Diagnostic' )
				->set_description( 'Monitor system health and view activity logs.' )
				->set_clazz( self::class )
				->set_priority( 1000 )
				->set_is_core( true )
				->set_icon( 'dashicons-rest-api' )
				->build();
	}

	/**
	 * Renders the terminal-style log viewer outside the main form.
	 *
	 * Migrated from render_log_viewer. Implements optimized file reading by
	 * capturing only the last 100 entries to ensure interface performance.
	 *
	 * @return string The terminal HTML and initialization scripts.
	 */
	public function render_outside_form(): string {
		$log_file = Logger::get_log_path();
		// Lógica limpia: si no hay archivo, mostramos un placeholder técnico.
		Logger::trace( 'TRACE Log Sample' );
		Logger::debug( 'DEBUG Log Sample' );
		Logger::info( 'INFO Log Sample' );
		Logger::warn( 'WARN Log Sample' );
		Logger::error( 'ERROR Log Sample' );
		$content = file_exists( $log_file )
				? implode( '', array_slice( file( $log_file ), - 100 ) )
				: '--- SYSTEM READY: NO LOG ENTRIES FOUND ---';
		ob_start();
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
		return ob_get_clean();
	}

	/**
	 * Entry point for WordPress hook registration.
	 *
	 * @return void
	 */
	protected function register(): void {
		if ( $this->wp->security->is_admin() ) {
			$this->wp->events->add_action( 'admin_init', array( $this, 'register_module_settings' ) );
		}
	}
}