<?php
namespace Triskelion\Toolkit\Admin;

use Triskelion\Toolkit\Core\Toolkit;

class Admin {
    public static function init(): void {
        add_action('admin_menu', [self::class, 'add_menu_page']);
        add_action('admin_init', [self::class, 'register_settings']);

        $plugin_base = 'triskelion-toolkit/triskelion-toolkit.php';
        add_filter("plugin_action_links_$plugin_base", [self::class, 'add_settings_link']);
    }

    public static function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('tools.php?page=triskelion-toolkit') . '">GeneralSettings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public static function add_menu_page(): void {
        $hook = add_management_page(
                __( 'Triskelion Toolkit', 'triskelion-toolkit' ),
                __( 'Triskelion Toolkit', 'triskelion-toolkit' ),
                'manage_options',
                'triskelion-toolkit',
                [self::class, 'render_admin_page']
        );

        add_action("admin_print_styles-$hook", [self::class, 'enqueue_admin_assets']);
    }

    public static function enqueue_admin_assets(): void {
        wp_enqueue_style('tsk-admin-styles');
    }

    public static function register_settings(): void {
        // Solo registramos la opción. No necesitamos sections ni fields de WP
        // porque estamos haciendo nuestro propio render dinámico por UX.
        register_setting('tsk_settings', Toolkit::TSK_ACTIVE_MODULES);
    }

    /**
     * ORQUESTADOR PRINCIPAL
     */
    /**
     * ORQUESTADOR PRINCIPAL - Versión Limpia
     */
    public static function render_admin_page(): void {
        $current_tab = self::get_current_tab();
        $modules     = Toolkit::get_modules();
        $active_map  = (array) get_option( Toolkit::TSK_ACTIVE_MODULES, [] );

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

        if ( $instance && method_exists( $instance, 'render_settings' ) ) {
            $instance->render_settings();
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
            <h1><?php esc_html_e( 'Triskelion Toolkit', 'triskelion-toolkit' ); ?></h1>
        </header>
        <?php
    }

    private static function render_tab_menu( string $current_tab, array $modules, array $active_map ): void {
        ?>
        <aside class="tsk-admin-nav">
            <nav class="tsk-tab-menu">
                <?php foreach ( $modules as $id => $data ) :
                    $is_core      = ! empty( $data['is_core'] ); // Usamos la nueva bandera
                    $is_active    = ! empty( $active_map[$id] );
                    $has_settings = ! isset( $data['has_settings'] ) || $data['has_settings'] === true;

                    if ( ! $is_core && ( ! $is_active || ! $has_settings ) ) continue;

                    $active_class = ( $current_tab === $id ) ? 'active' : '';
                    $tab_url      = admin_url( 'tools.php?page=triskelion-toolkit&tab=' . $id );
                    ?>
                    <a href="<?php echo esc_url( $tab_url ); ?>" class="tsk-tab-link <?php echo esc_attr( $active_class ); ?>">
                        <span class="dashicons <?php echo esc_attr( $data['icon'] ?? 'dashicons-admin-generic' ); ?>"></span>
                        <?php echo esc_html( $data['name'] ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>
        <?php
    }


    private static function render_module_error_notice(): void {
        ?>
        <div class="notice notice-warning inline">
            <p><?php esc_html_e( 'This module is active but its settings are not available.', 'triskelion-toolkit' ); ?></p>
        </div>
        <?php
    }

    private static function get_current_tab(): string {
        return isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general_settings';
    }

    private static function can_access_tab( string $current_tab, array $modules, array $active_map ): bool {
        if ( ! array_key_exists( $current_tab, $modules ) ) return false;

        // Si es CORE (General, Diagnostic), siempre tiene acceso
        if ( ! empty( $modules[$current_tab]['is_core'] ) ) return true;

        $is_active    = ! empty( $active_map[$current_tab] );
        $has_settings = ! isset( $modules[$current_tab]['has_settings'] ) || $modules[$current_tab]['has_settings'] === true;

        return $is_active && $has_settings;
    }
    private static function render_access_denied(): void {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Access Denied', 'triskelion-toolkit' ); ?></h1>
            <p><?php esc_html_e( 'This section is not available or does not require configuration.', 'triskelion-toolkit' ); ?></p>
            <a href="<?php echo admin_url( 'tools.php?page=triskelion-toolkit' ); ?>" class="button button-primary">
                <?php esc_html_e( 'Back to General', 'triskelion-toolkit' ); ?>
            </a>
        </div>
        <?php
    }
}