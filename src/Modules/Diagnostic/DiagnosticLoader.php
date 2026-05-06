<?php

namespace Triskelion\TriskelionToolkit\Modules\Diagnostic;

use Triskelion\TriskelionToolkit\Core\AbstractModule;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfigBuilder;
use Triskelion\TriskelionToolkit\Core\Interfaces\RegistrableModuleInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\HasSettingsInterface;
use Triskelion\TriskelionToolkit\Core\Logger;

class DiagnosticLoader extends AbstractModule implements RegistrableModuleInterface, HasSettingsInterface {

    protected function register(): void {
        if ( is_admin() ) {
            add_action( 'admin_init', [ $this, 'register_module_settings' ] );
        }
    }

    public function register_module_settings(): void {
        register_setting(
                'tsk_diagnostic_group',
                'tsk_diagnostic_settings',
                [
                        'type'              => 'array',
                        'sanitize_callback' => [ $this, 'sanitize_module_settings' ],
                        'default'           => [ 'debug_enabled' => false, 'level' => Logger::LEVEL_ERROR ]
                ]
        );
    }

    public function sanitize_module_settings( $input ): array {
        $severity_map = Logger::get_severity_map();

        $level = ( isset($input['level']) && isset($severity_map[$input['level']]) )
                ? $input['level']
                : Logger::LEVEL_ERROR;

        return [
                'debug_enabled' => isset($input['debug_enabled']),
                'level'         => $level
        ];
    }

    public function render_settings(): string {
        $options = get_option( 'tsk_diagnostic_settings', [
                'debug_enabled' => false,
                'level'         => Logger::LEVEL_ERROR
        ]);

        $is_debug_forced = defined('TSK_DEBUG');
        $is_level_forced = defined('TSK_LOG_LEVEL');

        $val_enabled = $is_debug_forced ? (bool)constant('TSK_DEBUG') : $options['debug_enabled'];
        $val_level   = $is_level_forced ? constant('TSK_LOG_LEVEL')   : $options['level'];

        ob_start(); ?>
        <div class="tsk-diagnostic-view">
            <form method="post" action="options.php">
                <?php
                settings_fields( 'tsk_diagnostic_group' );
                ?>

                <header class="tsk-section-header">
                    <h2><?php _e( 'Logs & Diagnostics', 'triskelion-toolkit' ); ?></h2>
                    <?php if ( $is_debug_forced || $is_level_forced ) : ?>
                        <p class="tsk-notice tsk-notice--info">
                            <span class="dashicons dashicons-lock"></span>
                            <?php _e( 'Configuration managed via code (wp-config.php).', 'triskelion-toolkit' ); ?>
                        </p>
                    <?php endif; ?>
                </header>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e( 'Enable Logging', 'triskelion-toolkit' ); ?></th>
                        <td>
                            <label class="tsk-switch <?php echo $is_debug_forced ? 'tsk-disabled' : ''; ?>">
                                <input type="checkbox"
                                       name="tsk_diagnostic_settings[debug_enabled]"
                                       value="1"
                                        <?php checked( true, (bool)$val_enabled ); ?>
                                        <?php disabled( $is_debug_forced ); ?>>
                                <span class="tsk-slider"></span>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Log Level', 'triskelion-toolkit' ); ?></th>
                        <td>
                            <select name="tsk_diagnostic_settings[level]" <?php disabled( $is_level_forced ); ?>>
                                <?php foreach ( Logger::get_levels() as $lvl ) : ?>
                                    <option value="<?php echo $lvl; ?>" <?php selected( $lvl, $val_level ); ?>>
                                        <?php echo strtoupper($lvl); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <?php
                submit_button( __( 'Save Diagnostic Settings', 'triskelion-toolkit' ) );
                ?>
            </form>
            <?php $this->render_log_viewer(); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_log_viewer(): void {
        $log_file = Logger::get_log_path();
        // Lógica limpia: si no hay archivo, mostramos un placeholder técnico
        $content  = file_exists($log_file)
                ? implode("", array_slice(file($log_file), -100))
                : "--- SYSTEM READY: NO LOG ENTRIES FOUND ---";

        ?>
        <section class="tsk-terminal">
            <header class="tsk-terminal__header">
                <div class="tsk-terminal__info">
                    <span class="dashicons dashicons-terminal"></span>
                    <span class="tsk-terminal__title">System Telemetry</span>
                </div>
                <div class="tsk-terminal__actions">
                    <button type="button" class="tsk-terminal__btn tsk-copy-trigger" title="<?php esc_attr_e( 'Copy to Clipboard', 'triskelion-toolkit' ); ?>">
                        <span class="dashicons dashicons-admin-page"></span>
                    </button>
                    <button type="button" class="tsk-terminal__btn tsk-refresh-trigger" title="<?php esc_attr_e( 'Refresh Log', 'triskelion-toolkit' ); ?>">
                        <span class="dashicons dashicons-update"></span>
                    </button>
                </div>
            </header>
            <textarea
                    readonly
                    class="tsk-terminal__body"
                    spellcheck="false"
            ><?php echo esc_textarea(trim($content)); ?></textarea>
        </section>

        <script>
            (function() {
                const terminal = document.querySelector('.tsk-terminal');
                const output = terminal?.querySelector('.tsk-terminal__body');

                terminal?.addEventListener('click', (e) => {
                    const btn = e.target.closest('.tsk-terminal__btn');
                    if (!btn) return;

                    if (btn.classList.contains('tsk-copy-trigger')) {
                        navigator.clipboard.writeText(output.value);
                        const icon = btn.querySelector('.dashicons');
                        icon.classList.replace('dashicons-admin-page', 'dashicons-yes');
                        setTimeout(() => icon.classList.replace('dashicons-yes', 'dashicons-admin-page'), 1500);
                    }

                    if (btn.classList.contains('tsk-refresh-trigger')) {
                        window.location.reload(); // Sincrónico por ahora
                    }
                });
            })();
        </script>
        <?php
    }
    public static function get_config(): ModuleConfig {
        return ( new ModuleConfigBuilder() )
                ->set_id( 'diagnostic' )
                ->set_name( __( 'Logs & Diagnostic', 'triskelion-toolkit' ) )
                ->set_description( __( 'Monitor system health and view activity logs.', 'triskelion-toolkit' ) )
                ->set_class( self::class )
                ->set_priority( 1000 )
                ->set_is_core( true )
                ->set_icon( 'dashicons-rest-api' )
                ->build();
    }



}