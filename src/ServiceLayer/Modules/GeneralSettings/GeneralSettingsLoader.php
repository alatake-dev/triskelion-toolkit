<?php
namespace Triskelion\Toolkit\Modules\GeneralSettings;

use Triskelion\Toolkit\Core\AbstractModuleLoader;
use Triskelion\Toolkit\Core\Toolkit;

class GeneralSettingsLoader extends AbstractModuleLoader {

    public function load(): void { }

    protected function render_header(): void {
        ?>
        <div class="tsk-tab-header">
            <h2><?php esc_html_e( 'Suite Management', TSK_DOMAIN ); ?></h2>
            <p class="description"><?php esc_html_e( 'Activate or deactivate modules.', TSK_DOMAIN ); ?></p>
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
        $active_map = (array) get_option( Toolkit::TSK_ACTIVE_MODULES, [] );
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
}