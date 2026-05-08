<?php

namespace Triskelion\TriskelionToolkit\Core;

use Triskelion\TriskelionToolkit\Core\Bridge\WpBridge;
use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Core\Interfaces\HasSettingsInterface;

class AdminManager {
	private ModuleCollection $modules;
	private array $active_loaders;

	private WpBridge $wp;

	public function __construct( ModuleCollection $modules, array $active_loaders ) {
		$this->modules        = $modules;
		$this->active_loaders = $active_loaders;
		$this->wp             = new WpBridge();
	}

	public function init(): void {
		if ( ! $this->wp->security->current_user_can( 'manage_options' ) ) {
			return;
		}
		$this->wp->hooks->add_action( 'admin_menu', array( $this, 'add_toolkit_menu' ) );

		$this->wp->hooks->add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		$basename = plugin_basename( TRISKELION_TOOLKIT_FILE );
		$this->wp->hooks->add_filter( "plugin_action_links_$basename", array( $this, 'add_settings_link' ) );
		$this->wp->hooks->add_action( 'admin_init', array( $this, 'trigger_module_settings' ) );
		$this->wp->hooks->add_filter( 'block_categories_all', array( $this, 'add_block_categories' ) );
	}


	/**
	 * Adds the Triskelion category to the Gutenberg block editor.
	 *
	 * @param array<int, array<string, string>> $categories Existing block categories.
	 * @return array<int, array<string, string>> Filtered block categories.
	 */
	public function add_block_categories( array $categories ): array {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'triskelion',
					'title' => __( 'Triskelion', 'triskelion-toolkit' ),
					'icon'  => 'admin-generic',
				),
			)
		);
	}

	public function trigger_module_settings(): void {
		foreach ( $this->active_loaders as $module ) {
			if ( $module instanceof HasSettingsInterface ) {
				$module->register_module_settings();
			}
		}
	}

	public function enqueue_admin_assets( $hook ): void {
		if ( 'tools_page_triskelion-toolkit' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'triskelion-toolkit-admin-layout',
			plugin_dir_url( TRISKELION_TOOLKIT_FILE ) . 'build/admin-layout.css',
			array(),
			'1.0.0'
		);
	}

	public function add_settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=triskelion-toolkit">' . __( 'Settings', 'triskelion-toolkit' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	public function add_toolkit_menu(): void {
		$this->wp->menu->add_submenu_page(
			'tools.php',
			esc_html__( 'Triskelion Toolkit', 'triskelion-toolkit' ),
			esc_html__( 'Triskelion Toolkit', 'triskelion-toolkit' ),
			'manage_options',
			'triskelion-toolkit',
			array( $this, 'render_layout' )
		);
	}

	/**
	 * Sets the WordPress Bridge instance.
	 *
	 * This setter allows for injecting a mock or a specific instance of the WpBridge,
	 * which is essential for decoupling the manager from global WordPress functions
	 * during unit testing.
	 *
	 * @param WpBridge $wp The WordPress Bridge instance.
	 * @since 1.0.0
	 */
	public function set_wp( WpBridge $wp ): void {
		$this->wp = $wp;
	}

	public function render_layout(): void {
		$active_instances = $this->active_loaders;

		$sorted_configs = $this->modules->get_all_sorted();

		$final_menu = array();
		foreach ( $sorted_configs as $id => $config ) {
			if ( isset( $active_instances[ $id ] ) ) {
				$final_menu[ $id ] = $active_instances[ $id ];
			}
		}

		$current_tab = $_GET['tab'] ?? 'general_settings';
		?>
		<div class="wrap triskelion-toolkit-admin-page">
			<div class="triskelion-toolkit-admin-layout">
				<nav class="triskelion-toolkit-admin-nav">
					<?php
					foreach ( $final_menu as $id => $obj ) :
						if ( ! ( $obj instanceof HasSettingsInterface ) ) {
							continue;
						}

						$config = $obj::get_config();

						$active_class = ( $current_tab === $id ) ? ' active' : '';
						?>
						<a href="?page=triskelion-toolkit&tab=<?php echo esc_attr( $id ); ?>"
							class="triskelion-toolkit-tab-link<?php echo $active_class; ?>">
							<?php echo esc_html( __( $config->name, 'triskelion-toolkit' ) ); ?>
						</a>
					<?php endforeach; ?>
				</nav>

				<main class="triskelion-toolkit-admin-main">
					<?php
					settings_errors( 'triskelion_toolkit_showcase_settings' );
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