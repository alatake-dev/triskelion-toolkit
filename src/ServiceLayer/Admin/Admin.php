<?php
namespace Triskelion\Toolkit\Admin;

use Triskelion\Toolkit\Core\Toolkit;

class Admin {
    public static function init() {
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

    public static function add_menu_page() {
        // Retorna el 'hook_suffix', lo necesitamos para cargar CSS solo en nuestra página
        $hook = add_management_page(
                __( 'Triskelion Toolkit', 'triskelion-toolkit' ),
                __( 'Triskelion Toolkit', 'triskelion-toolkit' ),
                'manage_options',
                'triskelion-toolkit',
                [__CLASS__, 'render_admin_page']
        );

        // Solo cargamos estilos si estamos en nuestra página del Toolkit
        add_action("admin_print_styles-$hook", [self::class, 'admin_styles']);
    }
    public static function admin_styles() {
        ?>
        <style>
            /* Personalizamos el título H1 para que tenga el espíritu de Triskelion */
            .triskelion-admin h1::before {
                content: "\f147"; /* Dashicon de Red/API */
                font-family: dashicons;
                vertical-align: middle;
                margin-right: 10px;
                color: #2271b1; /* El azul estándar de WP o el de Triskelion */
            }
            .tsk-logo-placeholder {
                background: #f0f0f1;
                border: 2px dashed #c3c4c7;
                border-radius: 4px;
                padding: 20px;
                text-align: center;
                margin-bottom: 20px;
            }
        </style>
        <?php
    }
    public static function render_admin_page() {
        ?>
        <div class="wrap triskelion-admin">
            <div class="tsk-logo-container" style="margin-bottom: 20px;">
                <div style="width: 200px; height: 80px; background: #eee; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center;">
                    <span style="color: #999; font-weight: bold;">LOGO TRISKELION</span>
                </div>
            </div>

            <h1><?php esc_html_e("Module's configuration", 'triskelion-toolkit') ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('tsk_settings');
                do_settings_sections('triskelion-toolkit');
                submit_button(__('Save', 'triskelion-toolkit'));
                ?>
            </form>
        </div>
        <?php
    }

    public static function register_settings() {
        register_setting('tsk_settings', Toolkit::TSK_ACTIVE_MODULES);

        add_settings_section(
                'tsk_main_section',
                __( 'Suite Modules', 'triskelion-toolkit' ),
                null,
                'triskelion-toolkit'
        );

        $modules = Toolkit::get_modules();

        foreach ($modules as $id => $data) {
            add_settings_field(
                    "module_$id",
                    $data['name'],
                    [__CLASS__, 'render_module_checkbox'], // <--- Ahora apunta aquí
                    'triskelion-toolkit',
                    'tsk_main_section',
                    ['id' => $id]
            );
        }
    }
    public static function render_module_checkbox($args) {
        $id = $args['id'];
        $options = (array) get_option(Toolkit::TSK_ACTIVE_MODULES, []);

        // Si el ID existe en el array y es true, marcamos el check
        $checked = !empty($options[$id]) ? 'checked' : '';
        echo "<input type='checkbox' name='" . Toolkit::TSK_ACTIVE_MODULES . "[$id]' value='1' $checked />";
    }

}
// docker exec -it triskelion-wp cat /var/www/html/wp-content/plugins/triskelion-toolkit/includes/Admin.php | grep "__("