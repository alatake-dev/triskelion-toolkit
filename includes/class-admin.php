<?php
namespace Triskelion\Toolkit;

class Admin {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_menu_page']);
    }

    public static function add_menu_page() {
        add_menu_page(
            'Triskelion Toolkit',
            'Triskelion',
            'manage_options',
            'triskelion-toolkit',
            [__CLASS__, 'render_admin_page'],
            'dashicons-admin-generic', // Aquí irá el logo después
            30
        );
    }

    public static function render_admin_page() {
        ?>
        <div class="wrap triskelion-admin">
            <div class="tsk-logo-container" style="margin-bottom: 20px;">
                <div style="width: 200px; height: 80px; background: #eee; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center;">
                    <span style="color: #999; font-weight: bold;">LOGO TRISKELION</span>
                </div>
            </div>

            <h1>Configuración de Módulos</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('tsk_tk_settings');
                do_settings_sections('triskelion-toolkit');
                submit_button('Guardar Cambios');
                ?>
            </form>
        </div>
        <?php
    }
}