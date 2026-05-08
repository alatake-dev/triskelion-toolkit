<?php

namespace Triskelion\TriskelionToolkit\Modules\CodeShowcase;

use Highlight\Highlighter;
use Triskelion\TriskelionToolkit\Core\AbstractModule;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfigBuilder;
use Triskelion\TriskelionToolkit\Core\Interfaces\HasSettingsInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\RegistrableModuleInterface;

class CodeShowcaseLoader extends AbstractModule implements RegistrableModuleInterface, HasSettingsInterface {

	public static function get_config(): ModuleConfig {
		return ( new ModuleConfigBuilder() )
				->set_id( 'code_showcase' )
				->set_name( 'Code Showcase' )
				->set_description( 'Display code snippets with a premium macOS terminal aesthetic.' )
				->set_class( self::class )
				->set_priority( 100 )
				->set_is_core( false )
				->set_icon( 'dashicons-rest-api' )
				->build();
	}

	public function enqueue_admin_assets( $hook ): void {
		if ( ! str_contains( $hook, 'triskelion-toolkit' ) ) {
			return;
		}

		$js_path = 'src/Modules/CodeShowcase/assets/admin-inventory.js';
		wp_enqueue_script( 'triskelion-toolkit-admin-inventory', TRISKELION_TOOLKIT_URL . $js_path, array(), filemtime( TRISKELION_TOOLKIT_PATH . $js_path ), true );
		wp_enqueue_style( 'triskelion-toolkit-showcase-admin-styles', TRISKELION_TOOLKIT_URL . 'src/Modules/CodeShowcase/assets/admin-module.css', array(), '1.0.0' );
		wp_enqueue_style( 'triskelion-toolkit-showcase-hljs-styles', TRISKELION_TOOLKIT_URL . 'src/Modules/CodeShowcase/assets/syntax-highlighting.css', array(), '1.0.0' );

		$json = TRISKELION_TOOLKIT_PATH . 'src/Modules/CodeShowcase/assets/languages.json';
		if ( file_exists( $json ) ) {
			wp_localize_script(
				'triskelion-toolkit-admin-inventory',
				'triskelionToolkitInventoryData',
				array(
					'allLanguages' => json_decode( file_get_contents( $json ), true ) ?: array( 'php', 'javascript' ),
				)
			);
		}
	}

	public function render_settings(): string {
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
			add_settings_error( 'triskelion_showcase_group', 'settings_updated', __( 'Settings saved.', 'triskelion-toolkit' ), 'updated' );
		}
		$settings    = $this->get_settings();
		$theme       = $settings['active_theme'];
		$themes_data = $this->get_themes_config();
		ob_start(); ?>
		<h1><?php echo self::i18n_config()['name']; ?></h1>
		<p class="description"><?php echo self::i18n_config()['description']; ?></p>

		<div class="wrap triskelion-toolkit-settings-container">
			<?php settings_errors( 'triskelion_showcase_group' ); ?>

			<?php echo $this->get_theme_inline_css( $theme, true ); ?>
			<h2><?php esc_html_e( 'Code Showcase Configuration', 'triskelion-toolkit' ); ?></h2>
			<form action="options.php" method="post">
				<?php settings_fields( 'triskelion_showcase_group' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Theme', 'triskelion-toolkit' ); ?> </th>
						<td>
							<select name="triskelion_toolkit_showcase_settings[active_theme]">
								<?php foreach ( $themes_data as $id => $data ) : ?>
									<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $theme, $id ); ?>>
										<?php echo esc_html( $data['label'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Preview View', 'triskelion-toolkit' ); ?></th>
						<td>
							<div class="triskelion-toolkit-code-showcase-preview is-theme-<?php echo esc_attr( $theme ); ?>">
								<div class="triskelion-toolkit-window-header">
									<div class="triskelion-toolkit-dots"><span class="dot red"></span><span
												class="dot yellow"></span><span
												class="dot green"></span></div>
									<div class="triskelion-toolkit-tabs">
										<div class="triskelion-toolkit-tab is-active">preview.php</div>
									</div>
								</div>
								<div class="triskelion-toolkit-window-content">
								<pre><code class="hljs php">
								<?php
										$hl = new Highlighter();
										echo $hl->highlight( 'php', "function hello() {\n    echo 'Triskelion Power';\n}" )->value;
								?>
										</code></pre>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Languages inventory', 'triskelion-toolkit' ); ?></th>
						<td>
							<div class="triskelion-toolkit-search-group">
								<input type="text" id="triskelion-toolkit-lang-finder"
										placeholder="<?php echo esc_attr__( 'Add language...', 'triskelion-toolkit' ); ?>"
										class="regular-text">
								<ul id="triskelion-toolkit-search-results" class="triskelion-toolkit-results-list" hidden></ul>

							</div>
							<div class="triskelion-toolkit-pills" id="triskelion-toolkit-active-langs">
								<?php foreach ( $settings['active_languages'] as $lang ) : ?>
									<div class="triskelion-toolkit-pill">
										<span><?php echo strtoupper( $lang ); ?></span>
										<input type="hidden" name="triskelion_toolkit_showcase_settings[active_languages][]"
												value="<?php echo $lang; ?>">
										<button type="button" class="triskelion-toolkit-pill__remove">&times;</button>
									</div>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	private function get_settings(): array {
		$defaults = array(
			'active_theme'     => 'triskelion-dark',
			'active_languages' => array( 'php', 'javascript', 'css', 'html', 'json', 'sql' ),
		);

		return wp_parse_args( get_option( 'triskelion_toolkit_showcase_settings', array() ), $defaults );
	}

	private function get_themes_config(): array {
		return array(
			'triskelion-dark' => array(
				'--triskelion-toolkit-syntax-keyword'  => '#ff79c6',
				'--triskelion-toolkit-syntax-string'   => '#f1fa8c',
				'--triskelion-toolkit-syntax-comment'  => '#6272a4',
				'--triskelion-toolkit-syntax-number'   => '#bd93f9',
				'--triskelion-toolkit-syntax-function' => '#50fa7b',
				'--triskelion-toolkit-bg'              => '#1e1e1e',
				'--triskelion-toolkit-header'          => '#323232',
				'--triskelion-toolkit-text'            => '#d4d4d4',
				'label'                                => __( 'Triskelion Dark', 'triskelion-toolkit' ),
			),
			'monokai'         => array(
				'--triskelion-toolkit-syntax-keyword'  => '#f92672',
				'--triskelion-toolkit-syntax-string'   => '#e6db74',
				'--triskelion-toolkit-syntax-comment'  => '#75715e',
				'--triskelion-toolkit-syntax-number'   => '#ae81ff',
				'--triskelion-toolkit-syntax-function' => '#a6e22e',
				'--triskelion-toolkit-bg'              => '#272822',
				'--triskelion-toolkit-header'          => '#1e1f1c',
				'--triskelion-toolkit-text'            => '#f8f8f2',
				'label'                                => __( 'Monokai Original', 'triskelion-toolkit' ),
			),
			'cyber'           => array(
				'--triskelion-toolkit-syntax-keyword'  => '#00ffff',
				'--triskelion-toolkit-syntax-string'   => '#ff00ff',
				'--triskelion-toolkit-syntax-comment'  => '#ffff00',
				'--triskelion-toolkit-syntax-number'   => '#00ff00',
				'--triskelion-toolkit-syntax-function' => '#ffffff',
				'--triskelion-toolkit-bg'              => '#000000',
				'--triskelion-toolkit-header'          => '#00008b',
				'--triskelion-toolkit-text'            => '#ffffff',
				'label'                                => __( 'Cyber (High Contrast)', 'triskelion-toolkit' ),
			),
		);
	}

	public static function i18n_config(): array {
		return array(
			'name'        => __( 'Code Showcase', 'triskelion-toolkit' ),
			'description' => __( 'Display code snippets with a premium macOS terminal aesthetic.', 'triskelion-toolkit' ),
		);
	}

	private function get_theme_inline_css( $active_theme, $is_admin = false ): string {
		$themes_config = $this->get_themes_config();
		$css           = '';
		$targets       = $is_admin ? array_keys( $themes_config ) : array( $active_theme );

		foreach ( $targets as $id ) {
			$vars = $themes_config[ $id ];
			$css .= ".is-theme-$id { ";
			foreach ( $vars as $var => $val ) {
				if ( $var === 'label' ) {
					continue;
				}
				$css .= "$var: $val; ";
			}
			$css .= "}\n";

			// Cadenas y documentación especial
			$css .= ".is-theme-<$id> .hljs-string, .is-theme-$id .hljs-doctag, .is-theme-$id .hljs-regexp { color: var(--triskelion-toolkit-syntax-string) !important; }\n";

			// Palabras reservadas y tipos de sistema
			$css .= ".is-theme-$id .hljs-keyword, .is-theme-$id .hljs-selector-tag, .is-theme-$id .hljs-built_in, .is-theme-$id .hljs-type { color: var(--triskelion-toolkit-syntax-keyword) !important; font-weight: bold !important; }\n";

			// Comentarios y citas
			$css .= ".is-theme-$id .hljs-comment, .is-theme-$id .hljs-quote { color: var(--triskelion-toolkit-syntax-comment) !important; font-style: italic !important; }\n";

			// Números y constantes literales
			$css .= ".is-theme-$id .hljs-number, .is-theme-$id .hljs-literal { color: var(--triskelion-toolkit-syntax-number) !important; }\n";

			// Funciones, clases y títulos de sección
			$css .= ".is-theme-$id .hljs-function, .is-theme-$id .hljs-title, .is-theme-$id .hljs-title.function_, .is-theme-$id .hljs-title.class_, .is-theme-$id .hljs-section { color: var(--triskelion-toolkit-syntax-function) !important; }\n";

			// Atributos y variables (si quieres diferenciarlos, si no, usa el color de texto base)
			$css .= ".is-theme-$id .hljs-attr, .is-theme-$id .hljs-variable, .is-theme-$id .hljs-template-variable { color: var(--triskelion-toolkit-syntax-number); }\n";
		}

		return "<style>$css</style>";
	}

	public function render_frontend( $attributes ): string {
		$settings = $this->get_settings();
		$theme    = $settings['active_theme'];
		$files    = $attributes['files'] ?? array();
		if ( empty( $files ) ) {
			return '';
		}

		$active_tab = (int) ( $attributes['activeTabIndex'] ?? 0 );
		$hl         = new Highlighter();
		ob_start();
		?>
		<?php echo $this->get_theme_inline_css( $theme ); ?>
		<div class="triskelion-toolkit-code-showcase is-theme-<?php echo esc_attr( $theme ); ?>">
			<div class="triskelion-toolkit-code-showcase__header">
				<div class="triskelion-toolkit-code-showcase__window-buttons">
					<span class="triskelion-toolkit-code-showcase__dot triskelion-toolkit-code-showcase__dot--red"></span>
					<span class="triskelion-toolkit-code-showcase__dot triskelion-toolkit-code-showcase__dot--yellow"></span>
					<span class="triskelion-toolkit-code-showcase__dot triskelion-toolkit-code-showcase__dot--green"></span>
				</div>
				<div class="triskelion-toolkit-code-showcase__tabs" role="tablist">
					<?php foreach ( $files as $index => $file ) : ?>
						<button class="triskelion-toolkit-code-showcase__tab <?php echo $index === $active_tab ? 'is-active' : ''; ?>"
								data-index="<?php echo $index; ?>" role="tab">
							<?php echo esc_html( $file['fileName'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>
				<!-- Botón Copy Exclusivo del Front -->
				<button class="triskelion-toolkit-code-showcase__copy"
						aria-label="<?php esc_attr_e( 'Copy code', 'triskelion-toolkit' ); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
						<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
					</svg>
				</button>
			</div>
			<div class="triskelion-toolkit-code-showcase__body">
				<?php
				foreach ( $files as $index => $file ) :
					$code = $hl->highlight( $file['language'] ?? 'php', html_entity_decode( $file['content'] ?? '' ) )->value;
					?>
					<div class="triskelion-toolkit-code-showcase__pane <?php echo $index === $active_tab ? 'is-active' : ''; ?>"
						id="pane-<?php echo $index; ?>"
						role="tabpanel" <?php echo $index !== $active_tab ? 'hidden' : ''; ?>>
						<pre><code class="hljs <?php echo esc_attr( $file['language'] ?? 'php' ); ?>"><?php echo $code; ?></code></pre>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function register_showcase_block(): void {
		$path = TRISKELION_TOOLKIT_PATH . 'build/Modules/CodeShowcase';
		if ( file_exists( $path . '/block.json' ) ) {
			register_block_type( $path, array( 'render_callback' => array( $this, 'render_frontend' ) ) );

			wp_set_script_translations(
				'triskelion-code-showcase-editor-script',
				'triskelion-toolkit',
				TRISKELION_TOOLKIT_PATH . 'languages'
			);
		}
	}

	public function enqueue_block_assets(): void {
		$s = $this->get_settings();
		wp_localize_script(
			'triskelion-code-showcase-editor-script',
			'triskelionToolkitSettings',
			array(
				'activeLanguages' => $s['active_languages'],
				'theme'           => $s['active_theme'],
			)
		);
	}

	public function register_module_settings(): void {
		register_setting(
			'triskelion_showcase_group',
			'triskelion_toolkit_showcase_settings',
			array(
				'type'              => 'object',
				'sanitize_callback' => array(
					$this,
					'sanitize_module_settings',
				),
				'show_in_rest'      => true,
			)
		);
	}

	public function sanitize_module_settings( $input ): array {
		return array(
			'active_theme'     => sanitize_key( $input['active_theme'] ?? 'triskelion-dark' ),
			'active_languages' => is_array( $input['active_languages'] ) ? array_map( 'sanitize_key', $input['active_languages'] ) : array(),
		);
	}

	protected function register(): void {
		add_action( 'init', array( $this, 'register_showcase_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}
}