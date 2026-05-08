<?php
/**
 * Module Loader for General Settings.
 *
 * @package    Triskelion\TriskelionToolkit
 * @subpackage Modules\GeneralSettings
 * @since      1.0.0
 */

namespace Triskelion\TriskelionToolkit\Modules\GeneralSettings;

use Triskelion\TriskelionToolkit\Core\AbstractModule;
use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfigBuilder;
use Triskelion\TriskelionToolkit\Core\Interfaces\HasSettingsInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\NeedsModuleCollectionInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\RegistrableModuleInterface;

/**
 * Class GeneralSettingsLoader
 *
 * Orchestrates the central configuration of the toolkit, specifically managing
 * the activation and deactivation states of optional modules within the system.
 *
 * @package Triskelion\TriskelionToolkit\Modules\GeneralSettings
 */
class GeneralSettingsLoader extends AbstractModule implements HasSettingsInterface, RegistrableModuleInterface, NeedsModuleCollectionInterface {

	/**
	 * Shared instance of the system's module collection.
	 *
	 * @var ModuleCollection
	 */
	private ModuleCollection $module_collection;

	/**
	 * Defines the module configuration using the standardized builder.
	 *
	 * @return ModuleConfig The configuration object containing module metadata.
	 */
	public static function get_config(): ModuleConfig {
		return ( new ModuleConfigBuilder() )
				->set_id( 'general_settings' )
				->set_name( 'General Settings' )
				->set_description( 'Configure which toolkit features are active.' )
				->set_clazz( self::class )
				->set_priority( 0 )
				->set_is_core( true )
				->set_icon( 'dashicons-admin-generic' )
				->build();
	}

	/**
	 * Returns the internationalization configuration for the module.
	 *
	 * @return array{name: string, description: string} Localized strings for UI display.
	 */
	public static function i18n_config(): array {
		return array(
			'name'        => __( 'General Settings', 'triskelion-toolkit' ),
			'description' => __( 'General settings for the plugin.', 'triskelion-toolkit' ),
		);
	}

	/**
	 * Entry point for WordPress hook registration.
	 *
	 * Attaches module-specific administrative functionality to the WordPress lifecycle.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_module_settings' ) );
	}

	/**
	 * Registers the module settings within the WordPress Settings API.
	 *
	 * Defines the settings group and the main option for active module management.
	 *
	 * @return void
	 */
	public function register_module_settings(): void {
		register_setting(
			'triskelion_toolkit_general_settings_group',
			'triskelion_toolkit_active_modules',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_module_settings' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Injects the complete module collection into the loader instance.
	 *
	 * Implementation of NeedsModuleCollectionInterface. This allows the loader
	 * to access all available modules to manage their activation status.
	 *
	 * @param ModuleCollection $collection Shared module collection instance.
	 * @return void
	 */
	public function set_module_collection( ModuleCollection $collection ): void {
		$this->module_collection = $collection;
	}

	/**
	 * Renders the internal HTML content for the module activation form.
	 *
	 * Captures the checkbox grid layout using output buffering for the AdminManager.
	 *
	 * @return string The generated internal form HTML.
	 */
	public function render_inside_form(): string {
		$active_modules = get_option( 'triskelion_toolkit_active_modules', array() );
		$all_modules    = $this->module_collection->get_all_sorted();

		ob_start();
		?>
		<div class="triskelion-toolkit-settings-grid">
			<?php foreach ( $all_modules as $id => $config ) : ?>
				<?php
				if ( $config->is_core ) {
					continue;
				}
				?>

				<label for="module_<?php echo esc_attr( $id ); ?>" class="triskelion-module-card-label">
					<input name="triskelion_toolkit_active_modules[]"
							type="checkbox"
							id="module_<?php echo esc_attr( $id ); ?>"
							value="<?php echo esc_attr( $id ); ?>"
							<?php checked( in_array( $id, $active_modules, true ) ); ?>>

					<div class="triskelion-module-info">
						<strong><?php echo esc_html( $this->resolve_i18n_field( 'name', $config ) ); ?></strong>
						<p class="description">
							<?php echo esc_html( $this->resolve_i18n_field( 'description', $config ) ); ?>
						</p>
					</div>
				</label>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Renders additional UI elements outside of the main settings form.
	 *
	 * @return string Empty string as no external content is required for this module.
	 */
	public function render_outside_form(): string {
		return '';
	}

	/**
	 * Sanitizes the submitted module activation data from the form.
	 *
	 * @param mixed $input Raw input data from the settings POST request.
	 * @return array Validated list of registered module IDs.
	 */
	public function sanitize_module_settings( $input ): array {
		$submitted = is_array( $input ) ? $input : array();
		$valid_ids = array_keys( $this->module_collection->get_all_sorted() );

		return array_values(
			array_filter(
				$submitted,
				fn( $id ) => in_array( $id, $valid_ids, true )
			)
		);
	}

	/**
	 * Resolves i18n fields prioritizing dynamic configuration over static properties.
	 *
	 * @param string       $field  Field to resolve (name|description).
	 * @param ModuleConfig $config The module configuration metadata object.
	 * @return string The resolved, localized, and sanitized text.
	 */
	private function resolve_i18n_field( string $field, ModuleConfig $config ): string {
		$clazz = $config->clazz;

		if ( class_exists( $clazz ) && method_exists( $clazz, 'i18n_config' ) ) {
			$i18n = $clazz::i18n_config();
			if ( ! empty( $i18n[ $field ] ) ) {
				return $i18n[ $field ];
			}
		}

		return $config->$field ?? '';
	}
}