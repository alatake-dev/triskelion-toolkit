<?php
namespace Triskelion\TriskelionToolkit\Core;

use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Core\Interfaces\HasSettingsInterface;

class AdminManager {
    private ModuleCollection $modules;
    private array $active_loaders;

	public function __construct(ModuleCollection $modules, array $active_loaders) {
        $this->modules = $modules;
        $this->active_loaders = $active_loaders;
	}

    public function init(): void {
        add_action('admin_menu', [$this, 'add_toolkit_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        $basename = plugin_basename(TSK_FILE);
        add_filter("plugin_action_links_{$basename}", [$this, 'add_settings_link']);
        add_action( 'admin_init', [ $this, 'trigger_module_settings' ] );
    }

    public function trigger_module_settings(): void {
        foreach ( $this->active_loaders as $module ) {
            if ( $module instanceof HasSettingsInterface ) {
                $module->register_module_settings();
            }
        }
    }

    public function enqueue_admin_assets($hook): void {
        if ('tools_page_triskelion-toolkit' !== $hook) {
            return;
        }

        wp_enqueue_style(
                'tsk-admin-layout',
                plugin_dir_url(TSK_FILE) . 'build/admin-layout.css',
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
        add_submenu_page(
                'tools.php',
			esc_html__( 'Triskelion Suite', 'triskelion-toolkit' ),
			esc_html__( 'Triskelion Suite', 'triskelion-toolkit' ),
			'manage_options',
			'triskelion-toolkit',
			[$this, 'render_layout']
		);
	}

    public function render_layout(): void {
        $active_instances = $this->active_loaders;

        $sorted_configs = $this->modules->get_all_sorted();

        $final_menu = [];
        foreach ($sorted_configs as $id => $config) {
            if (isset($active_instances[$id])) {
                $final_menu[$id] = $active_instances[$id];
            }
        }

        $current_tab = $_GET['tab'] ?? 'general_settings';
        ?>
        <div class="wrap tsk-admin-page">
            <div class="tsk-admin-layout">
                <nav class="tsk-admin-nav">
                    <?php
                    foreach ( $final_menu as $id => $obj ) :
                        if ( ! ( $obj instanceof HasSettingsInterface ) ) continue;

                        $config = $obj::get_config();

                        $active_class = ( $current_tab === $id ) ? ' active' : '';
                        ?>
                        <a href="?page=triskelion-toolkit&tab=<?php echo esc_attr( $id ); ?>"
                           class="tsk-tab-link<?php echo $active_class; ?>">
                            <?php echo esc_html( $config->name ); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <main class="tsk-admin-main">
                    <?php
                    settings_errors( 'tsk_showcase_settings' );
                    $current_module = $final_menu[ $current_tab ] ?? null;
                    if ( $current_module instanceof HasSettingsInterface ) {
                        echo $current_module->render_settings();
                    } else {
                        echo '<div class="notice notice-error"><p>' . esc_html__( 'Module not available or without settings.', 'triskelion-toolkit' ) . '</p></div>';
                    }
                    ?>
                </main>
            </div>
        </div>
        <?php
    }
}