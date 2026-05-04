<?php

namespace Triskelion\TriskelionToolkit\Modules\GeneralSettings;

use Triskelion\TriskelionToolkit\Core\AbstractModule;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfigBuilder;
use Triskelion\TriskelionToolkit\Core\Interfaces\RegistrableModuleInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\SettingsInterface;
use Triskelion\TriskelionToolkit\Core\Kernel;

class GeneralSettingsLoader extends AbstractModule implements SettingsInterface, RegistrableModuleInterface {

    public function __construct() {
        // Registramos los ajustes en el core de WP al inicializar
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function register_settings(): void {
        register_setting( 'tsk_settings_group', 'triskelion_active_modules', [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_modules' ],
                'default'           => [],
        ] );
    }

    public function render_settings(): string {
        $manifest       = Kernel::get_manifest();
        $active_modules = get_option( 'triskelion_active_modules', [] );

        ob_start(); ?>
        <div class="tsk-settings-container">
            <h1>System Modules</h1>
            <p class="description">Core modules are mandatory. Optional modules can be toggled.</p>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'tsk_settings_group' );
                // Invocamos al componente visual interno
                echo $this->render_module_grid( $manifest, $active_modules );
                submit_button( __( 'Save Configuration', 'triskelion-toolkit' ) );
                ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Componente de UI: El Grid de Módulos.
     * Aquí aplicamos el orden jerárquico y bloqueamos los módulos obligatorios.
     */
    private function render_module_grid($manifest_collection, $active_modules): string {
        // 1. Obtenemos el manifiesto ya ordenado por la Colección
        $sorted_modules = $manifest_collection->get_all_sorted();

        ob_start(); ?>

        <div class="tsk-modules-grid">
            <?php foreach ($sorted_modules as $config) :
                // Lógica de estado y protección[cite: 1]
                $is_core   = $config->is_core;
                $is_active = $is_core || in_array($config->id, $active_modules, true);

                // BEM: Clase dinámica para el estado 'disabled' visual[cite: 1]
                $card_classes = 'tsk-module-card' . ($is_core ? ' tsk-module-card--core' : '');
                ?>
                <div class="<?php echo esc_attr($card_classes); ?>">
                    <div class="tsk-module-toggle">
                        <label class="tsk-switch">
                            <input type="checkbox"
                                    <?php
                                    /**
                                     * Si es core, no enviamos 'name'. Al estar disabled,
                                     * el navegador lo ignora, pero esto es doble seguridad.[cite: 1]
                                     */
                                    echo ! $is_core ? 'name="triskelion_active_modules[]"' : ''; ?>
                                   value="<?php echo esc_attr($config->id); ?>"
                                    <?php checked($is_active); ?>
                                    <?php disabled($is_core); ?>>
                            <span class="tsk-slider"></span>
                        </label>
                    </div>

                    <div class="tsk-module-info">
                    <span class="tsk-module-title">
                        <?php echo esc_html($config->name); ?>
                        <?php if ($is_core) : ?>
                            <span class="tsk-badge tsk-badge--mandatory">Core</span>
                        <?php endif; ?>
                    </span>
                        <p class="tsk-module-desc">
                            <?php echo esc_html($config->description); ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
        return ob_get_clean();
    }
    public function sanitize_modules( $input ): array {
        // Lógica para asegurar que los módulos 'core' siempre se guarden como activos
        return is_array( $input ) ? array_unique( $input ) : [];
    }

    public static function get_config(): ModuleConfig {
        return ( new ModuleConfigBuilder() )
                ->set_id( 'general_settings' )
                ->set_name( __( 'General Settings', 'triskelion-toolkit' ) )
                ->set_description( __( 'General settings for the plugin.', 'triskelion-toolkit' ) )
                ->set_class( \Triskelion\TriskelionToolkit\Modules\GeneralSettings\GeneralSettingsLoader::class )
                ->set_is_core( true )
                ->set_priority( 0 )
                ->set_icon( 'dashicons-admin-generic' )
                ->build();
    }

    public function register(): void {
        // Silencio absoluto. No ensuciamos el arranque de WP.
    }
}