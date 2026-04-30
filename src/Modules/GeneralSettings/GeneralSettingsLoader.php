<?php
namespace Triskelion\TriskelionToolkit\Modules\GeneralSettings;

use Triskelion\TriskelionToolkit\Core\AbstractModule;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfigBuilder;
use Triskelion\TriskelionToolkit\Core\Interfaces\RegistrableModuleInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\SettingsInterface;
use Triskelion\TriskelionToolkit\Modules\GeneralSettings\ServiceLayer\SettingsService;
use Triskelion\TriskelionToolkit\Modules\GeneralSettings\ViewLayer\AdminInterface;

class GeneralSettingsLoader extends AbstractModule implements SettingsInterface, RegistrableModuleInterface {

    protected function register() {
        new SettingsService();
        if ( is_admin() ) {
            new AdminInterface();
        }
    }
    /*

    public function load(): void { }

    protected function render_header(): void {
        ?>
        <div class="tsk-tab-header">
            <h2><?php esc_html_e( 'Suite Management', 'triskelion-toolkit' ); ?></h2>
            <p class="description"><?php esc_html_e( 'Activate or deactivate modules.', 'triskelion-toolkit' ); ?></p>
        </div>
        <?php
    }

    protected function render_module_fields(): void {
        $modules = Toolkit::get_modules();

        foreach ( $modules as $id => $data ) {
            if ( ! empty( $data['is_core'] ) ) continue;
            $this->render_module_row( $id, $data );
        }
    }

    private function render_module_row( string $id, array $data ): void {
        $active_map = (array) get_option( TSK_ACTIVE_MODULES, [] );
        $is_active  = ! empty( $active_map[$id] );
        ?>
        <div class="tsk-module-card">
            <div class="tsk-module-toggle-area">
                <label class="tsk-switch">
                    <input type="checkbox"
                           name="tsk_active_modules[<?php echo esc_attr( $id ); ?>]"
                           value="1" <?php checked( $is_active ); ?>>
                    <span class="tsk-slider"></span>
                </label>
            </div>
            <div class="tsk-module-info-area">
                <span class="tsk-module-name"><?php echo esc_html( $data['name'] ); ?></span>
                <p class="tsk-module-description"><?php echo esc_html( $data['description'] ?? '' ); ?></p>
            </div>
        </div>
        <?php
    }

    protected function get_custom_css(): string {
        return "
        #wpbody-content .tsk-tab-content-wrapper .tsk-module-card {
            display: flex !important;
            flex-direction: row !important;
            align-items: flex-start !important;
            padding: 25px 0;
            border-bottom: 1px solid #f0f0f1;
            gap: 25px;
            margin: 0;
            background: transparent;
            border-left: none;
            border-right: none;
            border-top: none;
        }

        #wpbody-content .tsk-module-toggle-area {
            flex: 0 0 50px !important;
            display: flex !important;
            padding-top: 5px;
        }

        #wpbody-content .tsk-module-info-area {
            flex: 1 !important;
            min-width: 0;
        }

        .tsk-module-name {
            display: block;
            font-size: 1.1rem;
            font-weight: 600;
            color: #1d2327;
            margin-bottom: 4px;
        }

        .tsk-module-description {
            margin: 0;
            color: #646970;
            font-size: 13px;
            line-height: 1.5;
        }
    ";
    }

    public function register_module_settings(): void {
        register_setting(
                $this->get_settings_group(),
                TSK_ACTIVE_MODULES,
                [
                        'type'              => 'array',
                        'sanitize_callback' => [ $this, 'sanitize_module_settings' ],

                ]
        );
    }
    public function sanitize_module_settings( $input ): array {
        $ret_val = [];
        if ( is_array( $input ) ) {
            foreach ( $input as $module_id => $value ) {
                $ret_val[ sanitize_key( $module_id ) ] = true;
            }
        }
        return $ret_val;
    }

    */
// src/Modules/GeneralSettings/GeneralSettingsLoader.php

	public function render_settings(): string {
		$manifest = Kernel::get_manifest();
		$db_settings = get_option( 'triskelion_modules_settings', [] );

		ob_start();
		?>
		<div class="tsk-settings-header">
			<h1><?php echo esc_html__( 'System Modules', 'triskelion-toolkit' ); ?></h1>
			<p><?php echo esc_html__( 'Core modules are mandatory. Optional modules can be toggled.', 'triskelion-toolkit' ); ?></p>
		</div>

		<form method="post" action="options.php">
			<?php settings_fields( 'triskelion_settings_group' ); ?>

			<div class="tsk-modules-list">
				<?php foreach ( $manifest as $id => $config ) :
					$is_core = ! empty( $config['is_core'] ); //[cite: 2]
					$is_active = $is_core || ! empty( $db_settings[ $id ] );
					?>
					<div class="tsk-module-card <?php echo $is_core ? 'is-core-module' : ''; ?>">
						<div class="tsk-module-toggle-area">
							<?php if ( $is_core ) : ?>
								<!-- Badge visual para módulos que no se pueden apagar[cite: 2] -->
								<span class="tsk-badge tsk-badge-active"><?php echo esc_html__( 'Core', 'triskelion-toolkit' ); ?></span>
							<?php else : ?>
								<label class="tsk-switch">
									<input type="checkbox"
									       name="triskelion_modules_settings[<?php echo esc_attr( $id ); ?>]"
									       value="1"
										<?php checked( $is_active ); ?>>
									<span class="tsk-slider"></span>
								</label>
							<?php endif; ?>
						</div>

						<div class="tsk-module-info-area">
							<span class="tsk-module-name"><?php echo esc_html( $config['name'] ); ?></span>
							<p class="tsk-module-description"><?php echo esc_html( $config['description'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php submit_button( __( 'Save Module Configuration', 'triskelion-toolkit' ) ); ?>
		</form>
		<?php
		return ob_get_clean();
	}

    public static function get_config(): ModuleConfig {
        return (new ModuleConfigBuilder())
                ->set_id('general_settings')
                ->set_name(__( 'General Settings', 'triskelion-toolkit' ))
                ->set_description(__('General settings for the plugin.', 'triskelion-toolkit'))
                ->set_class(\Triskelion\TriskelionToolkit\Modules\GeneralSettings\GeneralSettingsLoader::class)
                ->set_is_core(true)
                ->set_priority(0)
                ->set_icon('dashicons-admin-generic')
                ->build();
    }

}