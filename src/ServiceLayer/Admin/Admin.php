<?php
namespace Triskelion\Toolkit\Admin;

use Triskelion\Toolkit\Core\Toolkit;
use Triskelion\Toolkit\Modules\ModuleRegistry;
use Triskelion\Toolkit\Core\SettingsProviderInterface;

class Admin {
    public static function init(): void {
        add_action( 'admin_menu', [ self::class, 'add_menu_page' ] );
        add_action( 'admin_init', [ self::class, 'register_settings' ] );

        $plugin_base = 'triskelion-toolkit/triskelion-toolkit.php';
        add_filter( "plugin_action_links_$plugin_base", [ self::class, 'add_settings_link' ] );

    }

    /**
     * Agrega el enlace de "Settings" en la lista de plugins.
     */
    public static function add_settings_link( $links ) {
        $settings_link = sprintf(
                '<a href="%s">%s</a>',
                admin_url( 'tools.php?page=triskelion-toolkit' ),
                __( 'Settings', TSK_DOMAIN )
        );
        array_unshift( $links, $settings_link );
        return $links;
    }

    public static function add_menu_page(): void {
        $hook = add_management_page(
                __( 'Triskelion Toolkit', TSK_DOMAIN ),
                __( 'Triskelion Toolkit', TSK_DOMAIN ),
                'manage_options',
                TSK_DOMAIN,
                [ self::class, 'render_admin_page' ]
        );

        add_action( "admin_print_styles-$hook", [ self::class, 'enqueue_admin_assets' ] );
    }

    public static function enqueue_admin_assets(): void {
        wp_enqueue_style( 'tsk-admin-styles' );
    }

// En src/ServiceLayer/Admin/Admin.php

    public static function register_settings(): void {
        register_setting( 'tsk_general_group', TSK_ACTIVE_MODULES );

        // Lo que es de los Módulos
        $modules = Toolkit::get_modules();

        foreach ( $modules as $id => $data ) {
            if ( empty( $data['class'] ) ) continue;

            if ( is_subclass_of( $data['class'], SettingsProviderInterface::class ) ) {
                $loader = new $data['class']( $id );
                $loader->register_module_settings();
            }
        }
    }
    /**
     * Renderizado principal de la página de administración.
     */
    public static function render_admin_page(): void {
        $current_tab = self::get_current_tab();
        $modules     = Toolkit::get_modules();

        $active_map  = (array) get_option( TSK_ACTIVE_MODULES, [] );
        if ( ! self::can_access_tab( $current_tab, $modules, $active_map ) ) {
            self::render_access_denied();
            return;
        }

        echo '<div class="wrap tsk-admin-page">';
        self::render_header();

        echo '<div class="tsk-admin-layout">';
        self::render_tab_menu( $current_tab, $modules, $active_map );

        echo '<main class="tsk-admin-main">';

        $instance = Toolkit::get_module_instance( $current_tab );

        if ( $instance instanceof SettingsProviderInterface ) {
            if ( method_exists( $instance, 'enqueue_module_styles' ) ) {
                $instance->enqueue_module_styles();
            }
            $instance->render_module_settings();
        } else {
            self::render_module_error_notice();
        }

        echo '</main>';
        echo '</div>';
        echo '</div>';
    }

    private static function render_header(): void {
        ?>
        <header class="tsk-header">
            <div class="tsk-logo-placeholder">
                <span>LOGO TRISKELION</span>
            </div>
            <h1><?php esc_html_e( 'Triskelion Toolkit', TSK_DOMAIN ); ?></h1>
        </header>
        <?php
    }

    private static function render_tab_menu( string $current_tab, array $modules, array $active_map ): void {
        echo '<nav class="tsk-admin-sidebar">';

        foreach ( $modules as $id => $data ) {
            $is_core = isset( $data['is_core'] ) && (bool) $data['is_core'] === true;

            $is_active = ! empty( $active_map[ $id ] );

            $has_settings = ModuleRegistry::has_settings( $data['class'] );

            if ( $is_core || ( $is_active && $has_settings )) {
                $active_class = ( $current_tab === $id ) ? 'is-active' : '';
                $url = admin_url( 'tools.php?page=triskelion-toolkit&tab=' . $id );

                printf(
                        '<a href="%s" class="tsk-tab-link %s">%s</a>',
                        esc_url( $url ),
                        esc_attr( $active_class ),
                        esc_html( $data['name'] )
                );
            }
        }

        echo '</nav>';
    }

    private static function render_module_error_notice(): void {
        ?>
        <div class="notice notice-warning inline">
            <p><?php esc_html_e( 'This module is active but its settings are not available.', TSK_DOMAIN ); ?></p>
        </div>
        <?php
    }

    private static function get_current_tab(): string {
        return isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general_settings';
    }

    private static function can_access_tab( string $current_tab, array $modules, array $active_map ): bool {
        if ( ! array_key_exists( $current_tab, $modules ) ) {
            return false;
        }

        $data = $modules[ $current_tab ];
        if ( empty( $data['class'] ) ) {
            return false;
        }
        if( isset( $data['is_core'] ) && (bool) $data['is_core'] === true){
            return true;
        }
        $is_active = ! empty( $active_map[ $current_tab ] );

        return $is_active && \Triskelion\Toolkit\Modules\ModuleRegistry::has_settings( $data['class'] );
    }

    private static function render_access_denied(): void {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Access Denied', TSK_DOMAIN ); ?></h1>
            <p><?php esc_html_e( 'This section is not available or does not require configuration.', TSK_DOMAIN ); ?></p>
            <a href="<?php echo admin_url( 'tools.php?page=triskelion-toolkit' ); ?>" class="button">
                <?php esc_html_e( 'Back to Settings', TSK_DOMAIN ); ?>
            </a>
        </div>
        <?php
    }
}