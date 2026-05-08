<?php

namespace Triskelion\TriskelionToolkit\Modules\GeneralSettings;

use Triskelion\TriskelionToolkit\Core\AbstractModule;
use Triskelion\TriskelionToolkit\Core\Data\ModuleCollection;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfigBuilder;
use Triskelion\TriskelionToolkit\Core\Interfaces\HasSettingsInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\NeedsModuleCollectionInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\RegistrableModuleInterface;
use Triskelion\TriskelionToolkit\Core\Logger;

class GeneralSettingsLoader extends AbstractModule implements HasSettingsInterface, RegistrableModuleInterface, NeedsModuleCollectionInterface {

	private ModuleCollection $module_collection;

	public function __construct() {
		parent::__construct();
		add_action( 'admin_init', array( $this, 'register_module_settings' ) );
	}

	public function register_module_settings(): void {
		register_setting(
			'triskelion_toolkit_settings_group',
			'triskelion_toolkit_active_modules',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_module_settings' ),
				'default'           => array(),
			)
		);
	}

	public function render_settings(): string {
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
			add_settings_error( 'triskelion_toolkit_settings_group', 'settings_updated', __( 'Settings saved.', 'triskelion-toolkit' ), 'updated' );
		}
		$active_modules = get_option( 'triskelion_toolkit_active_modules', array() );

		ob_start(); ?>
		<div class="wrap triskelion-toolkit-settings-container">
			<?php settings_errors( 'triskelion_toolkit_settings_group' ); ?>
			<h1><?php esc_html_e( self::get_config()->name, 'triskelion-toolkit' ); ?></h1>
			<p class="description"><?php esc_html_e( self::get_config()->description, 'triskelion-toolkit' ); ?></p>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'triskelion_toolkit_settings_group' );
				echo $this->render_module_grid( $active_modules );
				submit_button( __( 'Save Changes', 'triskelion-toolkit' ) );
				?>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function get_config(): ModuleConfig {
		return ( new ModuleConfigBuilder() )
				->set_id( 'general_settings' )
				->set_name( 'General Settings' )
				->set_description( 'General settings for the plugin.' )
				->set_class( self::class )
				->set_is_core( true )
				->set_priority( 0 )
				->set_icon( 'dashicons-admin-generic' )
				->build();
	}

	/**
	 * Componente de UI: El Grid de Módulos.
	 * Aquí aplicamos el orden jerárquico y bloqueamos los módulos obligatorios.
	 */
	private function render_module_grid( $active_modules ): string {
		$sorted_modules = $this->module_collection->get_all_sorted();

		ob_start();
		?>

		<div class="triskelion-toolkit-modules-grid">
			<?php
			foreach ( $sorted_modules as $config ) :
				$is_core   = $config->is_core;
				$is_active = $is_core || in_array( $config->id, $active_modules, true );

				$card_classes = 'triskelion-toolkit-module-card' . ( $is_core ? ' triskelion-toolkit-module-card--core' : '' );
				?>
				<div class="<?php echo esc_attr( $card_classes ); ?>">
					<div class="triskelion-toolkit-module-toggle">
						<label class="triskelion-toolkit-switch">
							<input type="checkbox"
									<?php
									echo ! $is_core ? 'name="triskelion_toolkit_active_modules[]"' : '';
									?>
									value="<?php echo esc_attr( $config->id ); ?>"
									<?php checked( $is_active ); ?>
									<?php disabled( $is_core ); ?>>
							<span class="triskelion-toolkit-slider"></span>
						</label>
					</div>

					<div class="triskelion-toolkit-module-info">
					<span class="triskelion-toolkit-module-title">

						<?php echo esc_html( $this->resolve_i18n_field( 'name', $config ) ); ?>
						<?php if ( $is_core ) : ?>
							<span class="triskelion-toolkit-badge triskelion-toolkit-badge--mandatory">(<?php _e( 'Core Module', 'triskelion-toolkit' ); ?>)</span>
						<?php endif; ?>
					</span>
						<p class="triskelion-toolkit-module-desc">
							<?php echo esc_html( $this->resolve_i18n_field( 'description', $config ) ); ?>
						</p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Resuelve el nombre o descripción del módulo de forma segura.
	 * Prioriza i18n_config sobre el valor estático del objeto Config.
	 */
	private function resolve_i18n_field( string $field, ModuleConfig $config ): string {
		$class = $config->class;
		if ( class_exists( $class ) && method_exists( $class, 'i18n_config' ) ) {
			$i18n = $class::i18n_config();

			if ( ! empty( $i18n[ $field ] ) ) {
				Logger::debug( 'i18n field' . $field . ' value:' . $i18n[ $field ], 'GeneralSettings' );

				return $i18n[ $field ];
			}
		}

		return $config->$field ?? '';
	}

	public static function i18n_config(): array {
		return array(
			'name'        => __( 'General Settings', 'triskelion-toolkit' ),
			'description' => __( 'General settings for the plugin.', 'triskelion-toolkit' ),
		);
	}

	public function sanitize_module_settings( $input ): array {
		$submitted = is_array( $input ) ? $input : array();
		$manifest  = $this->module_collection;
		$valid_ids = array_keys( $manifest->get_all_sorted() );

		return array_values(
			array_filter(
				$submitted,
				function ( $id ) use ( $valid_ids, $manifest ) {
					return in_array( $id, $valid_ids, true ) && ! $manifest->get( $id )->is_core;
				}
			)
		);
	}

	public function register(): void {
		// Silencio absoluto. No ensuciamos el arranque de WP.
	}

	public function set_module_collection( ModuleCollection $collection ): void {
		$this->module_collection = $collection;
	}
}