<?php
/**
 * Admin Manager Class File.
 *
 *  Orchestrates the WordPress administration interface, handling navigation,
 *  security headers, and module-specific settings rendering.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Core
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Core;

use Exception;
use Triskelion\TriskelionToolkit\Core\Bridge\WpBridge;
use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Core\Interfaces\HasSettingsInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\RegistrableModuleInterface;

/**
 * Class AdminManager
 *
 * Orchestrates the administration interface, including navigation tabs,
 * security verification, and module-specific settings rendering.
 *
 * @package Triskelion\TriskelionToolkit\Core
 */
class AdminManager {
	/** Registry of all available module configurations.
	 *
	 * @var ModuleCollection
	 */
	private ModuleCollection $modules;

	/** Instances of currently active module loaders.
	 *
	 * @var array<string, object>
	 */
	private array $active_loaders;

	/**Bridge for decoupled WordPress core functionality.
	 *
	 * @var WpBridge
	 */
	private WpBridge $wp;

	/**
	 * AdminManager constructor.
	 *
	 *  Initialized with the essential module registries and loaders. The WordPress
	 * bridge is instantiated internally to manage core decoupled functionality.
	 *
	 * @param ModuleCollection $modules        Registry of all available modules.
	 * @param array            $active_loaders Instances of currently active module loaders.
	 */
	public function __construct( ModuleCollection $modules, array $active_loaders ) {
		$this->modules        = $modules;
		$this->active_loaders = $active_loaders;
		$this->wp             = new WpBridge();
	}

	/**
	 * Renders the main administration page.
	 *
	 * Orchestrates the layout by checking capabilities, rendering the header,
	 * navigation tabs, and the content of the active module.
	 *
	 * @return void
	 */
	public function render_admin_page(): void {
		$final_menu  = $this->get_navigation_menu();
		$current_tab = $this->get_current_tab( $final_menu );
		$module      = $final_menu[ $current_tab ] ?? null;

		if ( ! ( $module instanceof HasSettingsInterface ) ) {
			return;
		}

		$config = $module::get_config();
		$i18n   = ( $module instanceof RegistrableModuleInterface ) ? $module::i18n_config() : (array) $config;
		$group  = $config->settings_group;
		$this->maybe_validate_nonce( $group );
		?>
		<div class="wrap triskelion-toolkit-admin-page">
			<div class="triskelion-toolkit-admin-layout">
				<?php $this->render_navigation( $final_menu, $current_tab ); ?>

				<main class="triskelion-toolkit-admin-main">
					<h1><?php echo esc_html( $i18n['name'] ); ?></h1>
					<p class="description"><?php echo esc_html( $i18n['description'] ); ?></p>

					<?php $this->render_messages( $group ); ?>

					<div id="triskelion-module-<?php echo esc_attr( $config->id ); ?>"
						class="triskelion-module-wrapper">
						<?php
						$this->render_form_section( $module, $group );
						// output is validated inside the method.
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $module->render_outside_form();
						?>
					</div>
				</main>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders the module form and security fields.
	 *
	 * @param HasSettingsInterface $module The active module.
	 * @param string               $group The settings group.
	 *
	 * @return void
	 */
	private function render_form_section( HasSettingsInterface $module, string $group ): void {
		$form_content = $module->render_inside_form();
		if ( empty( $form_content ) ) {
			return;
		}

		echo '<form method="post" action="options.php" class="triskelion-toolkit-form">';
		$this->wp->security->settings_fields( $group );
		$this->wp->security->nonce_field( $group, '_triskelion_nonce' );
		// output is validated inside the method.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $form_content;

		submit_button();
		echo '</form>';
	}

	/**
	 * Orchestrates stateful security verification for administrative form submissions.
	 *
	 * This method acts as a gatekeeper for POST requests. It performs a non-blocking
	 * check on the request method and triggers a formal nonce verification if a
	 * submission is detected. It leverages the WpBridge to ensure that security
	 * checks are decoupled and testable.
	 *
	 * @param string $group_action The unique action identifier for nonce verification.
	 *
	 * @return void
	 * @throws Exception In case of wp_die.
	 */
	private function maybe_validate_nonce( string $group_action ): void {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			return;
		}

		if ( ! $this->wp->security->check_admin_referer( $group_action, '_triskelion_nonce' ) ) {
			$this->wp->security->wp_die( esc_html__( 'Security check failed.', 'triskelion-toolkit' ) );
		}
	}

	/**
	 * Resolves the navigation menu based on active and sorted modules.
	 *
	 * @return array<string, object>
	 */
	private function get_navigation_menu(): array {
		$sorted_configs = $this->modules->get_all_sorted();
		$final_menu     = array();

		foreach ( $sorted_configs as $id => $config ) {
			if ( isset( $this->active_loaders[ $id ] ) ) {
				$final_menu[ $id ] = $this->active_loaders[ $id ];
			}
		}

		return $final_menu;
	}

	/**
	 * Gets the current sanitized tab or default.
	 *
	 * @param array $menu Valid menu items.
	 *
	 * @return string
	 */
	private function get_current_tab( array $menu ): string {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general_settings';

		return isset( $menu[ $tab ] ) ? $tab : (string) key( $menu );
	}

	/**
	 * Renders the primary administrative navigation interface.
	 *
	 * This method iterates through the provided menu collection to generate a
	 * tabbed navigation bar. It dynamically resolves module naming by checking
	 * for i18n_config support or falling back to standard configuration arrays.
	 *
	 * @param array<string, object> $menu        Map of module IDs to their respective loader instances.
	 * @param string                $current_tab The slug of the currently active navigation tab.
	 *
	 * @return void
	 */
	private function render_navigation( array $menu, string $current_tab ): void {
		?>
		<nav class="triskelion-toolkit-admin-nav">
			<?php
			foreach ( $menu as $id => $obj ) :
				$config = $obj::get_config();
				$i18n   = ( $obj instanceof RegistrableModuleInterface ) ? $obj::i18n_config() : (array) $config;
				$active = ( $current_tab === $id ) ? ' active' : '';
				?>
				<a href="?page=triskelion-toolkit&tab=<?php echo esc_attr( $id ); ?>"
					class="triskelion-toolkit-tab-link<?php echo esc_attr( $active ); ?>">
					<?php echo esc_html( $i18n['name'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<?php
	}

	/**
	 * Injects a "Settings" action link into the WordPress Plugins inventory.
	 *
	 * This method enhances the plugin's administrative discoverability by
	 * prepending a direct contextual link to the Toolkit's main dashboard
	 * within the 'Plugins' table row.
	 * * It operates by intercepting the 'plugin_action_links' filter array,
	 * ensuring that the navigation remains consistent with the current
	 * administrative URL structure and security capabilities.
	 *
	 * @param string[] $links An associative array of action links for the plugin.
	 *
	 * @return string[] The modified array containing the prepended "Settings" link.
	 */
	public function add_settings_link( array $links ): array {
		$url   = $this->wp->settings->admin_url( 'admin.php?page=triskelion-toolkit' );
		$label = __( 'Settings', 'triskelion-toolkit' );

		array_unshift( $links, "<a href=\"{$url}\">{$label}</a>" );

		return $links;
	}

	/**
	 * Orchestrates the display of administrative feedback messages.
	 *
	 * This method intercepts the 'settings-updated' state from the environment
	 * to register a success notification and subsequently triggers the
	 * WordPress settings API error display logic. It ensures that users receive
	 * consistent confirmation after data persistence operations.
	 *
	 * @param string $group The settings group identifier for which to display messages.
	 *
	 * @return void
	 */
	private function render_messages( string $group ): void {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
			\add_settings_error( $group, 'settings_updated', __( 'Settings saved.', 'triskelion-toolkit' ), 'updated' );
		}
		\settings_errors( $group );
	}

	/**
	 * Main Orchestrator of Administrative Infrastructure.
	 * * This method serves as the single entry point for all administrative event
	 * subscriptions. It leverages the "Event Bus" pattern to decouple WordPress
	 * hooks from the core logic, ensuring that each concern (UI, Assets, Persistence)
	 * is registered in its appropriate execution context.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->wp->events->add_action( 'admin_menu', array( $this, 'add_toolkit_menu' ) );
		$this->wp->events->add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		$basename = $this->wp->settings->plugin_basename( TRISKELION_TOOLKIT_FILE );
		$this->wp->events->add_filter( "plugin_action_links_$basename", array( $this, 'add_settings_link' ) );
		$this->wp->events->add_action( 'admin_init', array( $this, 'trigger_module_settings' ) );
		$this->wp->events->add_filter( 'block_categories_all', array( $this, 'add_block_categories' ) );
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

	/**
	 * Registers the primary administrative menu and its associated submenus.
	 *
	 * Acts as the structural entry point for the Toolkit's dashboard within the
	 * WordPress administrative sidebar. This method orchestrates the creation
	 * of the top-level menu and ensures that the 'AdminManager' is set as the
	 * authoritative controller for the rendering lifecycle.
	 * * It uses the 'add_submenu_page' event to hook into the WordPress
	 * menu registration process, enforcing a consistent brand presence
	 * via the 'Triskelion' top-level slug.
	 *
	 * @return void
	 */
	public function add_toolkit_menu(): void {
		$this->wp->menu->add_submenu_page(
			'tools.php',
			esc_html__( 'Triskelion Toolkit', 'triskelion-toolkit' ),
			esc_html__( 'Triskelion Toolkit', 'triskelion-toolkit' ),
			'manage_options',
			'triskelion-toolkit',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Enqueues global administrative assets for the Toolkit dashboard.
	 * * Responsible for injecting the primary CSS design tokens and JavaScript
	 * behaviors required for the AdminManager's UI. It implements a strict
	 * hook-based filter to prevent asset pollution in unrelated WordPress
	 * administrative pages, ensuring optimal performance and style isolation.
	 * * It leverages the Event Bridge (WpSecurity) to handle the enqueuing
	 * process, maintaining compatibility with the decoupled testing architecture.
	 *
	 * @param string $hook The current administrative page handle.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets( $hook ): void {
		if ( 'tools_page_triskelion-toolkit' !== $hook ) {
			return;
		}
		$this->wp->events->enqueue_style(
			'triskelion-toolkit-admin-layout',
			$this->wp->settings->plugin_dir_url( TRISKELION_TOOLKIT_FILE ) . 'build/admin-layout.css',
			array(),
			'1.0.0'
		);
	}

	/**
	 * Orchestrates the global registration of module-specific settings.
	 *
	 * Acts as the centralized execution bridge for data persistence. This method
	 * iterates through the registered module collection and triggers the
	 * 'register_module_settings' lifecycle hook for each enabled module.
	 * * By centralizing this process within the AdminManager, the toolkit
	 * ensures that all settings groups and option keys are formally declared
	 * to the WordPress Settings API in a predictable sequence, preventing
	 * collisions and ensuring metadata integrity during REST API or Admin UI updates.
	 *
	 * @return void
	 */
	public function trigger_module_settings(): void {
		foreach ( $this->active_loaders as $module ) {
			if ( $module instanceof HasSettingsInterface ) {
				$module->register_module_settings();
			}
		}
	}

	/**
	 * Resolves the currently active navigation tab.
	 * * Hardening: It defaults to 'general_settings' if the parameter is missing
	 * or if the requested tab doesn't exist in the collection.
	 *
	 * @return string The validated active tab slug.
	 */
	public function get_active_tab(): string {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = $_GET['tab'] ?? 'general_settings';

		if ( ! $this->modules->has( $tab ) ) {
			return 'general_settings';
		}

		return $tab;
	}
}