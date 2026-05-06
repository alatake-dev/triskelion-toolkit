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
        wp_enqueue_script( 'tsk-admin-inventory', TSK_URL . $js_path, [], filemtime( TSK_PATH . $js_path ), true );
        wp_enqueue_style( 'tsk-showcase-admin-styles', TSK_URL . 'src/Modules/CodeShowcase/assets/admin-module.css', [], '1.0.0' );
        wp_enqueue_style( 'tsk-showcase-hljs-styles', TSK_URL . 'src/Modules/CodeShowcase/assets/syntax-highlighting.css', [], '1.0.0' );

        $json = TSK_PATH . 'src/Modules/CodeShowcase/assets/languages.json';
        if ( file_exists( $json ) ) {
            wp_localize_script( 'tsk-admin-inventory', 'tskInventoryData', [
                    'allLanguages' => json_decode( file_get_contents( $json ), true ) ?: [ 'php', 'javascript' ]
            ] );
        }
    }

    public function render_settings(): string {
        $settings    = $this->get_settings();
        $theme       = $settings['active_theme'];
        $themes_data = $this->get_themes_config();
        ob_start(); ?>
        <h1><?php echo self::i18n_config()['name']; ?></h1>
        <p class="description"><?php echo self::i18n_config()['description'] ?></p>
        <div class="tsk-settings-container">
            <?php echo $this->get_theme_inline_css( $theme, true ); ?>
            <h2><?php esc_html_e( 'Code Showcase Configuration', 'triskelion-toolkit' ); ?></h2>
            <form action="options.php" method="post">
                <?php settings_fields( 'triskelion_showcase_group' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Theme', 'triskelion-toolkit' ); ?> </th>
                        <td>
                            <select name="tsk_showcase_settings[active_theme]">
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
                            <div class="tsk-code-showcase-preview is-theme-<?php echo esc_attr( $theme ); ?>">
                                <div class="tsk-window-header">
                                    <div class="tsk-dots"><span class="dot red"></span><span
                                                class="dot yellow"></span><span
                                                class="dot green"></span></div>
                                    <div class="tsk-tabs">
                                        <div class="tsk-tab is-active">preview.php</div>
                                    </div>
                                </div>
                                <div class="tsk-window-content">
                                <pre><code class="hljs php"><?php
                                        $hl = new Highlighter();
                                        echo $hl->highlight( 'php', "function hello() {\n    echo 'Triskelion Power';\n}" )->value;
                                        ?></code></pre>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Languages inventory', 'triskelion-toolkit' ); ?></th>
                        <td>
                            <div class="tsk-search-group">
                                <input type="text" id="tsk-lang-finder"
                                       placeholder="<?php echo esc_attr__( 'Add language...', 'triskelion-toolkit' ); ?>"
                                       class="regular-text">
                                <ul id="tsk-search-results" class="tsk-results-list" hidden></ul>

                            </div>
                            <div class="tsk-pills" id="tsk-active-langs">
                                <?php foreach ( $settings['active_languages'] as $lang ) : ?>
                                    <div class="tsk-pill">
                                        <span><?php echo strtoupper( $lang ); ?></span>
                                        <input type="hidden" name="tsk_showcase_settings[active_languages][]"
                                               value="<?php echo $lang; ?>">
                                        <button type="button" class="tsk-pill__remove">&times;</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php return ob_get_clean();
    }

    private function get_settings(): array {
        $defaults = [
                'active_theme'     => 'triskelion-dark',
                'active_languages' => [ 'php', 'javascript', 'css', 'html', 'json', 'sql' ]
        ];

        return wp_parse_args( get_option( 'tsk_showcase_settings', [] ), $defaults );
    }

    private function get_themes_config(): array {
        return [
                'triskelion-dark' => [
                        '--tsk-syntax-keyword'  => '#ff79c6',
                        '--tsk-syntax-string'   => '#f1fa8c',
                        '--tsk-syntax-comment'  => '#6272a4',
                        '--tsk-syntax-number'   => '#bd93f9',
                        '--tsk-syntax-function' => '#50fa7b',
                        '--tsk-bg'              => '#1e1e1e',
                        '--tsk-header'          => '#323232',
                        '--tsk-text'            => '#d4d4d4',
                        'label'                 => __( 'Triskelion Dark', 'triskelion-toolkit' )
                ],
                'monokai'         => [
                        '--tsk-syntax-keyword'  => '#f92672',
                        '--tsk-syntax-string'   => '#e6db74',
                        '--tsk-syntax-comment'  => '#75715e',
                        '--tsk-syntax-number'   => '#ae81ff',
                        '--tsk-syntax-function' => '#a6e22e',
                        '--tsk-bg'              => '#272822',
                        '--tsk-header'          => '#1e1f1c',
                        '--tsk-text'            => '#f8f8f2',
                        'label'                 => __( 'Monokai Original', 'triskelion-toolkit' )
                ],
                'cyber'           => [
                        '--tsk-syntax-keyword'  => '#00ffff',
                        '--tsk-syntax-string'   => '#ff00ff',
                        '--tsk-syntax-comment'  => '#ffff00',
                        '--tsk-syntax-number'   => '#00ff00',
                        '--tsk-syntax-function' => '#ffffff',
                        '--tsk-bg'              => '#000000',
                        '--tsk-header'          => '#00008b',
                        '--tsk-text'            => '#ffffff',
                        'label'                 => __( 'Cyber (High Contrast)', 'triskelion-toolkit' )
                ]
        ];
    }

    public static function i18n_config(): array {
        return [
                'name'        => __( 'Code Showcase', 'triskelion-toolkit' ),
                'description' => __( 'Display code snippets with a premium macOS terminal aesthetic.', 'triskelion-toolkit' )
        ];
    }

    private function get_theme_inline_css( $active_theme, $is_admin = false ): string {
        $themes_config = $this->get_themes_config();
        $css           = "";
        $targets       = $is_admin ? array_keys( $themes_config ) : [ $active_theme ];

        foreach ( $targets as $id ) {
            $vars = $themes_config[ $id ];
            $css  .= ".is-theme-$id { ";
            foreach ( $vars as $var => $val ) {
                if ( $var === 'label' ) {
                    continue;
                }
                $css .= "$var: $val; ";
            }
            $css .= "}\n";

            // Cadenas y documentación especial
            $css .= ".is-theme-<$id> .hljs-string, .is-theme-$id .hljs-doctag, .is-theme-$id .hljs-regexp { color: var(--tsk-syntax-string) !important; }\n";

            // Palabras reservadas y tipos de sistema
            $css .= ".is-theme-$id .hljs-keyword, .is-theme-$id .hljs-selector-tag, .is-theme-$id .hljs-built_in, .is-theme-$id .hljs-type { color: var(--tsk-syntax-keyword) !important; font-weight: bold !important; }\n";

            // Comentarios y citas
            $css .= ".is-theme-$id .hljs-comment, .is-theme-$id .hljs-quote { color: var(--tsk-syntax-comment) !important; font-style: italic !important; }\n";

            // Números y constantes literales
            $css .= ".is-theme-$id .hljs-number, .is-theme-$id .hljs-literal { color: var(--tsk-syntax-number) !important; }\n";

            // Funciones, clases y títulos de sección
            $css .= ".is-theme-$id .hljs-function, .is-theme-$id .hljs-title, .is-theme-$id .hljs-title.function_, .is-theme-$id .hljs-title.class_, .is-theme-$id .hljs-section { color: var(--tsk-syntax-function) !important; }\n";

            // Atributos y variables (si quieres diferenciarlos, si no, usa el color de texto base)
            $css .= ".is-theme-$id .hljs-attr, .is-theme-$id .hljs-variable, .is-theme-$id .hljs-template-variable { color: var(--tsk-syntax-number); }\n";
        }

        return "<style>$css</style>";
    }

    public function render_frontend( $attributes ): string {
        $settings = $this->get_settings();
        $theme    = $settings['active_theme'];
        $files    = $attributes['files'] ?? [];
        if ( empty( $files ) ) {
            return '';
        }

        $active_tab = (int) ( $attributes['activeTabIndex'] ?? 0 );
        $hl         = new Highlighter();
        ob_start(); ?>
        <?php echo $this->get_theme_inline_css( $theme ); ?>
        <div class="tsk-code-showcase is-theme-<?php echo esc_attr( $theme ); ?>">
            <div class="tsk-code-showcase__header">
                <div class="tsk-code-showcase__window-buttons">
                    <span class="tsk-code-showcase__dot tsk-code-showcase__dot--red"></span>
                    <span class="tsk-code-showcase__dot tsk-code-showcase__dot--yellow"></span>
                    <span class="tsk-code-showcase__dot tsk-code-showcase__dot--green"></span>
                </div>
                <div class="tsk-code-showcase__tabs" role="tablist">
                    <?php foreach ( $files as $index => $file ) : ?>
                        <button class="tsk-code-showcase__tab <?php echo $index === $active_tab ? 'is-active' : ''; ?>"
                                data-index="<?php echo $index; ?>" role="tab">
                            <?php echo esc_html( $file['fileName'] ); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <!-- Botón Copy Exclusivo del Front -->
                <button class="tsk-code-showcase__copy"
                        aria-label="<?php esc_attr_e( 'Copy code', 'triskelion-toolkit' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                </button>
            </div>
            <div class="tsk-code-showcase__body">
                <?php foreach ( $files as $index => $file ) :
                    $code = $hl->highlight( $file['language'] ?? 'php', html_entity_decode( $file['content'] ?? '' ) )->value;
                    ?>
                    <div class="tsk-code-showcase__pane <?php echo $index === $active_tab ? 'is-active' : ''; ?>"
                         id="pane-<?php echo $index; ?>"
                         role="tabpanel" <?php echo $index !== $active_tab ? 'hidden' : ''; ?>>
                        <pre><code class="hljs <?php echo esc_attr( $file['language'] ?? 'php' ); ?>"><?php echo $code; ?></code></pre>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    public function register_showcase_block(): void {
        $path = TSK_PATH . 'build/Modules/CodeShowcase';
        if ( file_exists( $path . '/block.json' ) ) {
            error_log( 'Registering CodeShowcase block' );
            register_block_type( $path, [ 'render_callback' => [ $this, 'render_frontend' ] ] );

            wp_set_script_translations(
                    'triskelion-code-showcase-editor-script',
                    'triskelion-toolkit',
                    TSK_PATH . 'languages'
            );
        } else {
            error_log( 'CodeShowcase block not found' );
        }
    }

    public function enqueue_block_assets(): void {
        $s = $this->get_settings();
        wp_localize_script( 'triskelion-code-showcase-editor-script', 'tskSettings', [
                'activeLanguages' => $s['active_languages'],
                'theme'           => $s['active_theme']
        ] );
    }

    public function register_module_settings(): void {
        register_setting( 'triskelion_showcase_group', "tsk_showcase_settings", [
                'type'              => 'object',
                'sanitize_callback' => [
                        $this,
                        'sanitize_module_settings'
                ],
                'show_in_rest'      => true
        ] );
    }

    public function sanitize_module_settings( $input ): array {
        return [
                'active_theme'     => sanitize_key( $input['active_theme'] ?? 'triskelion-dark' ),
                'active_languages' => is_array( $input['active_languages'] ) ? array_map( 'sanitize_key', $input['active_languages'] ) : []
        ];
    }

    protected function register(): void {
        add_action( 'init', [ $this, 'register_showcase_block' ] );
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }
}