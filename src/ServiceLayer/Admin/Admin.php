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
        $settings_link = '<a href="' . admin_url('tools.php?page=triskelion-toolkit') . '">Settings</a>';
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

        // Este hook busca el método 'enqueue_admin_assets'
        add_action("admin_print_styles-$hook", [self::class, 'enqueue_admin_assets']);
    }

    public static function enqueue_admin_assets(): void {
        wp_enqueue_style('tsk-admin-styles');
    }

    public static function render_admin_page(): void {
        $current_tab = self::get_current_tab();
        $modules     = Toolkit::get_modules();
        $active_map  = (array) get_option( Toolkit::TSK_ACTIVE_MODULES, [] );
        // No entras si:
        // - El tab no existe.
        // - No es 'general' y el módulo está apagado.
        // - No es 'general' y el módulo explícitamente no tiene settings.
        $exists       = array_key_exists( $current_tab, $modules );
        $is_general   = ( $current_tab === 'general' );
        $is_disabled  = ! $is_general && empty( $active_map[$current_tab] );
        $no_settings  = ! $is_general && isset( $modules[$current_tab]['has_settings'] ) && $modules[$current_tab]['has_settings'] === false;

        // 1. Validación de seguridad
        if ( ! $exists || $is_disabled || $no_settings ) {
            ?>
            <div class="wrap">
                <h1><?php esc_html_e( 'Access Denied', 'triskelion-toolkit' ); ?></h1>
                <p><?php esc_html_e( 'This section is not available or does not require configuration.', 'triskelion-toolkit' ); ?></p>
                <a href="<?php echo admin_url( 'tools.php?page=triskelion-toolkit' ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Back to General', 'triskelion-toolkit' ); ?>
                </a>
            </div>
            <?php
            return;
        }

        $active_map = (array) get_option( Toolkit::TSK_ACTIVE_MODULES, [] );
        ?>

        <div class="wrap triskelion-admin">
            <div class="tsk-logo-container" style="margin-bottom: 20px;">
                <div class="tsk-logo-placeholder">
                    <span>LOGO TRISKELION</span>
                </div>
            </div>

            <div class="tsk-admin-layout">
                <aside class="tsk-admin-sidebar">
                    <nav class="tsk-tab-menu">
                        <?php foreach ( $modules as $id => $data ) :
                            $is_active    = ! empty( $active_map[$id] );
                            $has_settings = ! isset( $data['has_settings'] ) || $data['has_settings'] === true;
                            if ( $id !== 'general' && ( ! $is_active || ! $has_settings ) ) {
                                continue;
                            }
                            $active_class = ( $current_tab === $id ) ? 'active' : '';
                            $tab_url      = admin_url( 'tools.php?page=triskelion-toolkit&tab=' . $id );
                            $icon         = $data['icon'] ?? 'dashicons-admin-generic';
                            ?>
                            <a href="<?php echo esc_url( $tab_url ); ?>" class="tsk-tab-link <?php echo esc_attr( $active_class ); ?>">
                                <span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
                                <?php echo esc_html( $data['name'] ); ?>
                            </a>

                        <?php endforeach; ?>
                    </nav>
                </aside>

                <main class="tsk-admin-main-content">
                    <div class="tsk-tab-content">

                        <?php if ( $current_tab === 'general' ) : ?>
                            <h1><?php esc_html_e( 'Suite Management', 'triskelion-toolkit' ); ?></h1>
                            <p class="description">
                                <?php esc_html_e( 'Activate or deactivate the modules you need for your ecosystem.', 'triskelion-toolkit' ); ?>
                            </p>

                            <form method="post" action="options.php">
                                <?php
                                settings_fields( 'tsk_settings' );
                                do_settings_sections( 'triskelion-toolkit' );
                                submit_button();
                                ?>
                            </form>

                        <?php else : ?>
                            <?php $instance = Toolkit::get_module_instance( $current_tab ); ?>

                            <h1><?php echo esc_html( $modules[$current_tab]['name'] ); ?></h1>

                            <?php if ( $instance ) : ?>
                                <div class="tsk-module-settings-container">
                                    <?php $instance->render_settings(); ?>
                                </div>
                            <?php else : ?>
                                <div class="notice notice-warning inline">
                                    <p>
                                        <?php esc_html_e( 'This module is active but its loader is not available or it has no settings.', 'triskelion-toolkit' ); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                        <?php endif; ?>

                    </div>
                </main>
            </div>
        </div>
        <?php
    }

    private static function get_current_tab(): string {
        return isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
    }

    public static function register_settings(): void {
        register_setting('tsk_settings', Toolkit::TSK_ACTIVE_MODULES);

        add_settings_section(
                'tsk_main_section',
                __( 'Available Modules', 'triskelion-toolkit' ),
                '__return_empty_string',
                'triskelion-toolkit'
        );

        foreach (Toolkit::get_modules() as $id => $data) {
            if ( $id === 'general' ) continue;

            add_settings_field(
                    "module_$id",
                    $data['name'],
                    [self::class, 'render_module_checkbox'],
                    'triskelion-toolkit',
                    'tsk_main_section',
                    ['id' => $id]
            );
        }
    }

    public static function render_module_checkbox($args): void {
        $id      = $args['id'];
        $options = (array) get_option(Toolkit::TSK_ACTIVE_MODULES, []);
        $checked = !empty($options[$id]) ? 'checked' : '';

        ?>
        <label class="tsk-switch" title="<?php esc_attr_e('Toggle module status', 'triskelion-toolkit'); ?>">
            <input type="checkbox"
                   name="<?php echo Toolkit::TSK_ACTIVE_MODULES; ?>[<?php echo esc_attr($id); ?>]"
                   value="1"
                    <?php echo $checked; ?>>
            <span class="tsk-slider"></span>
        </label>
        <?php
    }
}