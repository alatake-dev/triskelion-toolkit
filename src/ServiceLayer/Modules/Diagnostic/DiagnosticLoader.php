<?php
namespace Triskelion\Toolkit\Modules\Diagnostic;

use Triskelion\Toolkit\Core\AbstractModuleLoader;
use Triskelion\Toolkit\Core\SettingsProviderInterface;
use Triskelion\Toolkit\Modules\ModuleRegistry;
use Triskelion\Toolkit\Core\Logger;

class DiagnosticLoader extends AbstractModuleLoader implements SettingsProviderInterface {

    public function load(): void {
        // Registramos la configuración para TODO el entorno admin
        // Así options.php siempre sabrá cómo procesar el POST
        if ( is_admin() ) {
            add_action( 'admin_init', [ $this, 'register_diagnostic_settings' ] );
        }
    }

    /**
     * Registro oficial de la configuración
     */
    public function register_diagnostic_settings(): void {
        register_setting(
                TSK_SETTINGS_GROUP,
                'tsk_settings_diagnostic',
                [
                        'type'              => 'array',
                        'sanitize_callback' => [ $this, 'sanitize_module_settings' ],
                        'default'           => [
                                'debug_enabled' => false,
                                'level'         => 'error'
                        ]
                ]
        );
    }

    public function sanitize_module_settings( $input ): array {
        $ret_val = [
                'debug_enabled' => false,
                'level'         => 'error'
        ];

        if ( is_array( $input ) ) {
            $ret_val['debug_enabled'] = isset( $input['debug_enabled'] );

            $allowed = ['debug', 'info', 'warn', 'error', 'off'];
            if ( isset( $input['level'] ) && in_array( $input['level'], $allowed ) ) {
                $ret_val['level'] = $input['level'];
            }
        }

        return $ret_val;
    }

    protected function render_module_fields(): void {
        echo '<div class="tsk-diagnostic-settings-container">';
        $this->render_diagnostic_controls();
        echo '</div>';

        // Bloque 2: Telemetría (El Visor de Log)
        $this->render_log_viewer();
    }
    private function render_diagnostic_controls(): void {
        // 1. Valores de DB (Fallback)
        $options = get_option( 'tsk_settings_diagnostic', [
                'debug_enabled' => false,
                'level'         => 'error'
        ]);

        // 2. Verificación quirúrgica de constantes
        $forced_enabled = defined('TSK_LOG_ENABLED');
        $forced_level   = defined('TSK_LOG_LEVEL');

        // Determinamos el valor real que está operando el sistema
        $val_enabled = $forced_enabled ? constant('TSK_LOG_ENABLED') : $options['debug_enabled'];
        $val_level   = $forced_level   ? constant('TSK_LOG_LEVEL')   : $options['level'];

        // 3. Aviso si hay algo bloqueado
        if ( $forced_enabled || $forced_level ) {
            echo '<div class="notice notice-warning inline" style="margin-bottom: 20px; border-left-width: 4px;">';
            echo '<p><span class="dashicons dashicons-lock" style="font-size:16px; vertical-align:middle;"></span> ';
            _e( 'Algunos valores están bloqueados por el sistema (wp-config.php).', TSK_DOMAIN );
            echo '</p></div>';
        }
        ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php _e( 'Enable Debug Logging', TSK_DOMAIN ); ?></th>
                <td>
                    <label class="tsk-switch <?php echo $forced_enabled ? 'tsk-disabled' : ''; ?>">
                        <input type="checkbox"
                               name="tsk_settings_diagnostic[debug_enabled]"
                               value="1"
                                <?php checked( true, (bool)$val_enabled ); ?>
                                <?php disabled( $forced_enabled ); ?>>
                        <span class="tsk-slider"></span>
                    </label>
                    <?php if ( $forced_enabled ) : ?>
                        <span class="description" style="margin-left:12px; color: #646970; font-style: italic;">
                        <?php _e( '(Definido en código)', TSK_DOMAIN ); ?>
                    </span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e( 'Log Level', TSK_DOMAIN ); ?></th>
                <td>
                    <select name="tsk_settings_diagnostic[level]" <?php disabled( $forced_level ); ?>>
                        <?php
                        $levels = ['debug', 'info', 'warn', 'error', 'off'];
                        foreach ( $levels as $lvl ): ?>
                            <option value="<?php echo $lvl; ?>" <?php selected( $lvl, $val_level ); ?>>
                                <?php echo strtoupper( $lvl ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ( $forced_level ) : ?>
                        <span class="description" style="margin-left:12px; color: #646970; font-style: italic;">
                        <?php printf( __( '(Forzado a %s)', TSK_DOMAIN ), strtoupper($val_level) ); ?>
                    </span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
    }
    private function render_settings_section(): void {
        $settings = get_option( 'tsk_settings_diagnostic', [] );
        $current_level = $settings['level'] ?? 'error';
        $is_enabled = !empty($settings['debug_enabled']);

        $has_debug_override = defined('TSK_DEBUG');
        $has_level_override = defined('TSK_LOG_LEVEL');

        ?>
        <div class="tsk-diagnostic-section">
            <h3><?php _e( 'Configuration & Overrides', TSK_DOMAIN ); ?></h3>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'Enable Logging', TSK_DOMAIN ); ?></th>
                    <td>
                        <label class="tsk-switch">
                            <input type="checkbox"
                                   name="tsk_settings_diagnostic[debug_enabled]"
                                   value="1"
                                    <?php checked( $is_enabled ); ?>
                                    <?php disabled( $has_debug_override ); ?>>
                            <span class="tsk-slider round"></span>
                        </label>
                        <?php if ( $has_debug_override ) : ?>
                            <p class="description">
                                <span class="dashicons dashicons-lock"></span>
                                <?php _e( 'Forced via TSK_DEBUG in wp-config.php', TSK_DOMAIN ); ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Log Level', TSK_DOMAIN ); ?></th>
                    <td>
                        <select name="tsk_settings_diagnostic[level]" class="tsk-select" <?php disabled( $has_level_override ); ?>>
                            <?php foreach ( ['debug', 'info', 'warn', 'error', 'off'] as $lvl ) : ?>
                                <option value="<?php echo $lvl; ?>" <?php selected( $current_level, $lvl ); ?>>
                                    <?php echo strtoupper($lvl); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( $has_level_override ) : ?>
                            <p class="description">
                                <span class="dashicons dashicons-lock"></span>
                                <?php _e( 'Forced via TSK_LOG_LEVEL in wp-config.php', TSK_DOMAIN ); ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    private function render_system_info(): void {
        $modules = ModuleRegistry::get_all();
        $active  = (array) get_option( TSK_ACTIVE_MODULES, [] );
        ?>
        <div class="tsk-diagnostic-section" style="margin-top: 40px;">
            <h3><?php esc_html_e( 'Active Components', TSK_DOMAIN ); ?></h3>
            <table class="widefat striped tsk-info-table">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Module', TSK_DOMAIN ); ?></th>
                    <th><?php esc_html_e( 'Status', TSK_DOMAIN ); ?></th>
                    <th><?php esc_html_e( 'Type', TSK_DOMAIN ); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ( $modules as $id => $data ) :
                    $is_core = ! empty( $data['is_core'] );
                    $is_active = $is_core || isset( $active[$id] );
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html( $data['name'] ); ?></strong></td>
                        <td>
                            <span class="tsk-badge <?php echo $is_active ? 'tsk-badge-active' : 'tsk-badge-inactive'; ?>">
                                <?php echo $is_active ? '✅ Active' : '⚪ Disabled'; ?>
                            </span>
                        </td>
                        <td><?php echo $is_core ? 'Core Service' : 'Optional Module'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function render_log_viewer(): void {
        $log_file = Logger::get_log_path();
        $log_content = "--- TRISKELION TELEMETRY ONLINE ---\n";

        if ( file_exists( $log_file ) ) {
            $file_lines = file( $log_file );
            $lines = array_slice( $file_lines, -100 );
            $log_content = implode( "", $lines );
        }
        ?>
        <div class="tsk-terminal-wrapper">
            <div class="tsk-terminal-header">
                <h3><span class="dashicons dashicons-terminal"></span> <?php _e( 'System Telemetry', TSK_DOMAIN ); ?></h3>
                <div class="tsk-terminal-actions">
                    <button type="button" class="button button-secondary" onclick="window.location.reload();">
                        <span class="dashicons dashicons-update"></span> <?php _e( 'Refresh', TSK_DOMAIN ); ?>
                    </button>
                    <button type="button" class="button button-secondary tsk-copy-btn" onclick="tskCopyToClipboard()">
                        <span class="dashicons dashicons-clipboard"></span> <?php _e( 'Copy Logs', TSK_DOMAIN ); ?>
                    </button>
                </div>
            </div>
            <textarea readonly id="tsk-terminal" class="tsk-log-container" spellcheck="false"><?php echo esc_textarea( trim($log_content) ); ?></textarea>
        </div>
        <p class="description"><?php _e( 'Viewing last 100 entries from triskelion.log', TSK_DOMAIN ); ?></p>

        <script>
            function tskCopyToClipboard() {
                const textarea = document.getElementById('tsk-terminal');
                const btn = document.querySelector('.tsk-copy-btn');
                navigator.clipboard.writeText(textarea.value).then(() => {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<span class="dashicons dashicons-yes"></span> <?php _e( "Copied!", TSK_DOMAIN ); ?>';
                    btn.classList.add('tsk-btn-success');
                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('tsk-btn-success');
                    }, 2000);
                });
            }
            window.addEventListener('load', function() {
                const terminal = document.getElementById('tsk-terminal');
                if (terminal) setTimeout(() => { terminal.scrollTop = terminal.scrollHeight; }, 100);
            });
        </script>
        <?php
    }
    protected function render_header(): void {
        ?>
        <div class="tsk-tab-header">
            <h2><?php esc_html_e( 'Logs & Diagnostic', TSK_DOMAIN ); ?></h2>
            <p class="description"><?php esc_html_e( 'Real-time monitoring and internal toolkit configuration.', TSK_DOMAIN ); ?></p>
        </div>
        <?php
    }


    public function register_module_settings(): void {
        register_setting(
                $this->get_settings_group(),
                'tsk_settings_diagnostic',
                [
                        'type'              => 'array',
                        'sanitize_callback' => [ $this, 'sanitize_module_settings' ],
                        'default'           => [ 'debug_enabled' => false, 'level' => 'error' ]
                ]
        );
    }
}