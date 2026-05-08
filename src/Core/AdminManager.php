<?php

namespace Triskelion\TriskelionToolkit\Core;

use Triskelion\TriskelionToolkit\Core\Bridge\WpBridge;
use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Core\Interfaces\HasSettingsInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\RegistrableModuleInterface;

/**
 * Class AdminManager
 *
 * Orchestrates the administration interface, including navigation tabs,
 * security verification, and module-specific settings rendering.
 *
 * @package Triskelion\TriskelionToolkit\Core
 */
class AdminManager {
    /** @var ModuleCollection Registry of all available module configurations. */
    private ModuleCollection $modules;

    /** @var array<string, object> Instances of currently active module loaders. */
    private array $active_loaders;

    /** @var WpBridge Bridge for decoupled WordPress core functionality. */
    private WpBridge $wp;

    /**
     * AdminManager constructor.
     *
     * @param ModuleCollection $modules
     * @param array            $active_loaders
     */
    public function __construct( ModuleCollection $modules, array $active_loaders ) {
        $this->modules        = $modules;
        $this->active_loaders = $active_loaders;
        $this->wp             = new WpBridge();
    }

    /**
     * Renders the main administration layout.
     *
     * @return void
     */
    public function render_layout(): void {
        $final_menu  = $this->get_navigation_menu();
        $current_tab = $this->get_current_tab( $final_menu );
        $module      = $final_menu[ $current_tab ] ?? null;

        if ( ! ( $module instanceof HasSettingsInterface ) ) {
            return;
        }

        $config = $module::get_config();
        $i18n   = ( $module instanceof RegistrableModuleInterface ) ? $module::i18n_config() : (array) $config;
        $group  = "triskelion_toolkit_{$config->id}_group";

        $this->maybe_validate_nonce( $group );
        ?>
        <div class="wrap triskelion-toolkit-admin-page">
            <div class="triskelion-toolkit-admin-layout">
                <?php $this->render_navigation( $final_menu, $current_tab ); ?>

                <main class="triskelion-toolkit-admin-main">
                    <h1><?php echo esc_html( $i18n['name'] ); ?></h1>
                    <p class="description"><?php echo esc_html( $i18n['description'] ); ?></p>

                    <?php $this->render_messages( $group ); ?>

                    <div id="triskelion-module-<?php echo esc_attr( $config->id ); ?>" class="triskelion-module-wrapper">
                        <?php
                        $this->render_form_section( $module, $group );
                        echo $module->render_outside_form();
                        ?>
                    </div>
                </main>
            </div>
        </div>
        <?php
    }

    /**
     * Renders the module form and security fields.
     *
     * @param HasSettingsInterface $module The active module.
     * @param string               $group  The settings group.
     * @return void
     */
    private function render_form_section( HasSettingsInterface $module, string $group ): void {
        $form_content = $module->render_inside_form();
        if ( empty( $form_content ) ) {
            return;
        }

        echo '<form method="post" action="options.php" class="triskelion-toolkit-form">';
        $this->wp->security->settings_fields( $group );
        $this->wp->security->nonce_field( $group, '_triskelion_nonce' );

        echo $form_content;

        submit_button();
        echo '</form>';
    }

    /**
     * Validates the security nonce for POST requests.
     *
     * @param string $group_action
     * @return void
     */
    private function maybe_validate_nonce( string $group_action ): void {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
            return;
        }

        if ( ! $this->wp->security->check_admin_referer( $group_action, '_triskelion_nonce' ) ) {
            $this->wp->security->wp_die( esc_html__( 'Security check failed.', 'triskelion-toolkit' ) );
        }
    }

    /**
     * Resolves the navigation menu based on active and sorted modules.
     *
     * @return array<string, object>
     */
    private function get_navigation_menu(): array {
        $sorted_configs = $this->modules->get_all_sorted();
        $final_menu     = array();

        foreach ( $sorted_configs as $id => $config ) {
            if ( isset( $this->active_loaders[ $id ] ) ) {
                $final_menu[ $id ] = $this->active_loaders[ $id ];
            }
        }

        return $final_menu;
    }

    /**
     * Gets the current sanitized tab or default.
     *
     * @param array $menu Valid menu items.
     * @return string
     */
    private function get_current_tab( array $menu ): string {
        $tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general_settings';
        return isset( $menu[ $tab ] ) ? $tab : (string) key( $menu );
    }

    /**
     * Renders the sidebar navigation.
     *
     * @param array  $menu
     * @param string $current_tab
     * @return void
     */
    private function render_navigation( array $menu, string $current_tab ): void {
        ?>
        <nav class="triskelion-toolkit-admin-nav">
            <?php
            foreach ( $menu as $id => $obj ) :
                $config = $obj::get_config();
                $i18n   = ( $obj instanceof RegistrableModuleInterface ) ? $obj::i18n_config() : (array) $config;
                $active = ( $current_tab === $id ) ? ' active' : '';
                ?>
                <a href="?page=triskelion-toolkit&tab=<?php echo esc_attr( $id ); ?>"
                   class="triskelion-toolkit-tab-link<?php echo esc_attr( $active ); ?>">
                    <?php echo esc_html( $i18n['name'] ); ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php
    }

    /**
     * Triggers the display of settings errors.
     *
     * @param string $group
     * @return void
     */
    private function render_messages( string $group ): void {
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
            add_settings_error( $group, 'settings_updated', __( 'Settings saved.', 'triskelion-toolkit' ), 'updated' );
        }
        settings_errors( $group );
    }
}