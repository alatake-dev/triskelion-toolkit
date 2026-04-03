<?php
namespace Triskelion\Toolkit;

class Toolkit {
    public static function init() {
        // Registrar ajustes en la BD (wp_options)
        add_action('admin_init', [__CLASS__, 'register_settings']);

        // Cargar módulos activos
        self::load_modules();
    }

    public static function register_settings() {
        register_setting('tsk_tk_settings', 'tsk_tk_active_modules');

        add_settings_section(
            'tsk_tk_main_section',
            'Módulos Disponibles',
            null,
            'triskelion-toolkit'
        );

        // Aquí es donde Memo agregaría un nuevo 'add_settings_field' por cada bloque
        add_settings_field(
            'module_code_console',
            'Consola de Código',
            [__CLASS__, 'render_module_checkbox'],
            'triskelion-toolkit',
            'tsk_tk_main_section',
            ['id' => 'code-console']
        );
    }

    public static function render_module_checkbox($args) {
        $options = get_option('tsk_tk_active_modules', []);
        $id = $args['id'];
        $checked = isset($options[$id]) ? checked($options[$id], 1, false) : '';
        echo "<input type='checkbox' name='tsk_tk_active_modules[$id]' value='1' $checked />";
    }

    private static function load_modules() {
        $active = get_option('tsk_tk_active_modules', []);

        // Si el módulo está activo, despertamos a su clase
        if (isset($active['code-console']) && $active['code-console']) {
            \Triskelion\Toolkit\Modules\CodeConsole\Loader::init();
        }
    }
}