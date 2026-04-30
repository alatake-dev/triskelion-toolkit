<?php
namespace Triskelion\TriskelionToolkit\Core;

use Triskelion\TriskelionToolkit\Core\Interfaces\SettingsInterface;

class AdminManager {
	public function __construct() {
		add_action('admin_menu', [$this, 'add_toolkit_menu']);
	}

	public function add_toolkit_menu() {
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
        $active_instances = Kernel::get_active_modules(); // Ya filtrados por el Kernel[cite: 1]
        $manifest = Kernel::get_manifest();
        $current_tab = $_GET['tab'] ?? 'general_settings';

        ?>
        <div class="wrap tsk-admin-page">
            <div class="tsk-admin-layout">
                <nav class="tsk-admin-nav">
                    <?php foreach ( $active_instances as $id => $obj ) :
                        // Si no tiene interfaz, no hay pestaña. Punto.
                        if ( ! ( $obj instanceof SettingsInterface ) ) continue;

                        $name = $manifest[ $id ]['name'] ?? $id;
                        $active = ( $current_tab === $id ) ? ' active' : '';
                        ?>
                        <a href="?page=triskelion-toolkit&tab=<?php echo esc_attr( $id ); ?>"
                           class="tsk-tab-link<?php echo $active; ?>">
                            <?php echo esc_html( $name ); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <main class="tsk-admin-main">
                    <?php
                    $current_module = $active_instances[ $current_tab ] ?? null;
                    if ( $current_module instanceof SettingsInterface ) {
                        echo $current_module->render_settings();
                    }
                    ?>
                </main>
            </div>
        </div>
        <?php
    }
}