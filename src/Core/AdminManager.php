<?php
namespace Triskelion\TriskelionToolkit\Core;

use Triskelion\TriskelionToolkit\Core\Interfaces\SettingsInterface;

class AdminManager {
	public function __construct() {
		add_action('admin_menu', [$this, 'add_toolkit_menu']);

        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        $basename = plugin_basename(TSK_PATH . 'triskelion-toolkit.php');
        add_filter("plugin_action_links_{$basename}", [$this, 'add_settings_link']);
	}
    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_triskelion-toolkit' !== $hook) {
            return;
        }

        // Usamos TSK_PATH para la ruta física y plugin_dir_url para la pública
        wp_enqueue_style(
                'tsk-admin-layout',
                plugin_dir_url(TSK_PATH . 'triskelion-toolkit.php') . 'assets/css/admin-layout.css',
                [],
                '1.0.0'
        );
    }

    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=triskelion-toolkit">' . __('Settings', 'triskelion-toolkit') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
	public function add_toolkit_menu(): void {
		add_menu_page(
			esc_html__( 'Triskelion Suite', 'triskelion-toolkit' ),
			esc_html__( 'Triskelion Suite', 'triskelion-toolkit' ),
			'manage_options',
			'triskelion-toolkit',
			[$this, 'render_layout'], // El "cascarón"
			'dashicons-admin-tools',
			60
		);
	}

    public function render_layout() {
        // 1. Instancias vivas (desordenadas por el sistema de archivos)
        $active_instances = Kernel::get_active_modules();

        // 2. Manifiesto ordenado (la "Verdad" según la Colección)
        $sorted_configs = Kernel::get_manifest()->get_all_sorted();

        // 3. Creamos el menú final cruzando el orden del manifiesto con las instancias vivas
        $final_menu = [];
        foreach ($sorted_configs as $id => $config) {
            if (isset($active_instances[$id])) {
                // Guardamos la instancia, pero en el orden que dictó $sorted_configs
                $final_menu[$id] = $active_instances[$id];
            }
        }

        $current_tab = $_GET['tab'] ?? 'general_settings';
        ?>
        <div class="wrap tsk-admin-page">
            <div class="tsk-admin-layout">
                <nav class="tsk-admin-nav">
                    <?php
                    /**
                     * ITERACIÓN MAESTRA:
                     * Iteramos sobre $final_menu porque ya está ORDENADO y FILTRADO.
                     */
                    foreach ( $final_menu as $id => $obj ) :
                        // 1. Verificamos contrato: si no tiene settings, no hay pestaña
                        if ( ! ( $obj instanceof SettingsInterface ) ) continue;

                        /**
                         * 2. RECUPERACIÓN DE DATOS:
                         * No usamos ->get(). Accedemos directamente al config del objeto
                         * usando el método estático que ya definimos en cada Loader.
                         */
                        $config = $obj::get_config();
                        $name   = $config->name;

                        $active_class = ( $current_tab === $id ) ? ' active' : '';
                        ?>
                        <a href="?page=triskelion-toolkit&tab=<?php echo esc_attr( $id ); ?>"
                           class="tsk-tab-link<?php echo $active_class; ?>">
                            <?php echo esc_html( $name ); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <main class="tsk-admin-main">
                    <?php
                    // Renderizamos el módulo actual si existe y cumple el contrato
                    $current_module = $final_menu[ $current_tab ] ?? null;
                    if ( $current_module instanceof SettingsInterface ) {
                        echo $current_module->render_settings();
                    } else {
                        echo '<p>Módulo no disponible o sin configuración.</p>';
                    }
                    ?>
                </main>
            </div>
        </div>
        <?php
    }
}