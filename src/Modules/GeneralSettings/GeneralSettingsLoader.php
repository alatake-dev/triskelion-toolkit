<?php

namespace Triskelion\TriskelionToolkit\Modules\GeneralSettings;

use Triskelion\TriskelionToolkit\Core\AbstractModule;
use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfigBuilder;
use Triskelion\TriskelionToolkit\Core\Interfaces\NeedsModuleCollectionInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\RegistrableModuleInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\HasSettingsInterface;
use Triskelion\TriskelionToolkit\Core\Kernel;

class GeneralSettingsLoader extends AbstractModule implements HasSettingsInterface, RegistrableModuleInterface, NeedsModuleCollectionInterface {

    private ModuleCollection $module_collection;

    public function __construct() {
        add_action( 'admin_init', [ $this, 'register_module_settings' ] );
    }

    public function register_module_settings(): void {
        register_setting( 'tsk_settings_group', 'tsk_active_modules', [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_module_settings' ],
                'default'           => [],
        ] );
    }

    public function render_settings(): string {
        $active_modules = get_option( 'tsk_active_modules', [] );

        ob_start(); ?>
        <div class="tsk-settings-container">
            <h1>System Modules</h1>
            <p class="description">Core modules are mandatory. Optional modules can be toggled.</p>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'tsk_settings_group' );
                echo $this->render_module_grid(  $active_modules );
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
    private function render_module_grid($active_modules): string {
        $sorted_modules = $this->module_collection->get_all_sorted();

        ob_start(); ?>

        <div class="tsk-modules-grid">
            <?php foreach ($sorted_modules as $config) :
                $is_core   = $config->is_core;
                $is_active = $is_core || in_array($config->id, $active_modules, true);

                $card_classes = 'tsk-module-card' . ($is_core ? ' tsk-module-card--core' : '');
                ?>
                <div class="<?php echo esc_attr($card_classes); ?>">
                    <div class="tsk-module-toggle">
                        <label class="tsk-switch">
                            <input type="checkbox"
                                    <?php
                                    echo ! $is_core ? 'name="tsk_active_modules[]"' : ''; ?>
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
    public function sanitize_module_settings( $input ): array {
        $submitted = is_array($input) ? $input : [];
        $manifest = $this->module_collection;
        $valid_ids = array_keys($manifest->get_all_sorted());

        return array_values(array_filter($submitted, function($id) use ($valid_ids, $manifest) {
            return in_array($id, $valid_ids, true) && ! $manifest->get($id)->is_core;
        }));
    }

    public static function get_config(): ModuleConfig {
        return ( new ModuleConfigBuilder() )
                ->set_id( 'general_settings' )
                ->set_name( __( 'General Settings', 'triskelion-toolkit' ) )
                ->set_description( __( 'General settings for the plugin.', 'triskelion-toolkit' ) )
                ->set_class( self::class )
                ->set_is_core( true )
                ->set_priority( 0 )
                ->set_icon( 'dashicons-admin-generic' )
                ->build();
    }

    public function register(): void {
        // Silencio absoluto. No ensuciamos el arranque de WP.
    }

    public function set_module_collection( ModuleCollection $collection ): void {
        $this->module_collection = $collection;
    }

}