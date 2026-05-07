<?php

namespace Triskelion\TriskelionToolkit\Modules\SampleModule;

use Triskelion\TriskelionToolkit\Core\AbstractModule;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfig;
use Triskelion\TriskelionToolkit\Core\Data\ModuleConfigBuilder;
use Triskelion\TriskelionToolkit\Core\Interfaces\HasSettingsInterface;
use Triskelion\TriskelionToolkit\Core\Interfaces\RegistrableModuleInterface;

class SampleModuleLoader extends AbstractModule implements RegistrableModuleInterface, HasSettingsInterface {

    protected function register(): void {
        add_action( 'init', [ $this, 'register_sample_block' ] );
    }

    public function register_sample_block(): void {
        $path = TSK_PATH . 'build/Modules/SampleModule';

        if ( file_exists( $path . '/block.json' ) ) {
            $options = get_option( 'tsk_sample_module_settings', [
                    'default_tag'  => 'h4',
                    'accent_color' => 'var(--primary)'
            ] );

            register_block_type( $path, [
                    'attributes' => [
                        /* * NOTA PARA DEVS: Inyectar defaults desde PHP sincroniza el bloque con el Admin,
                        * pero cambiar estos valores globalmente causará un "Block Validation Error"
                        * en bloques ya existentes. Esto es un COMPORTAMIENTO ESPERADO en bloques estáticos.
                        */
                            'titleTag'    => [
                                    'type'    => 'string',
                                    'default' => $options['default_tag'], // Hx dinámico
                            ],
                            'accentColor' => [
                                    'type'    => 'string',
                                    'default' => $options['accent_color'], // Color dinámico
                            ],
                    ],
            ] );

            // Vinculamos traducciones usando el handle por defecto de WP para el plugin
            wp_set_script_translations(
                    'triskelion-sample-module-editor-script',
                    'triskelion-toolkit',
                    TSK_PATH . 'languages'
            );
        }
    }

    public static function get_config(): ModuleConfig {
        return ( new ModuleConfigBuilder() )
                ->set_id( 'sample_module' )
                ->set_name( 'Sample Module' )
                ->set_description( 'A blueprint module for developers to create new blocks.' )
                ->set_class( self::class )
                ->set_priority( 999 )
                ->set_is_core( false )
                ->set_icon( 'dashicons-layout' )
                ->build();
    }

    public static function i18n_config(): array {
        return [
                'name'        => __( 'Sample Module', 'triskelion-toolkit' ),
                'description' => __( 'A blueprint module for developers to create new blocks.', 'triskelion-toolkit' )
        ];
    }

    public function register_module_settings(): void {
        register_setting(
                'triskelion_sample_module_group', // Grupo global para el módulo
                'tsk_sample_module_settings',   // Key en la tabla options
                [
                        'type'              => 'object',
                        'sanitize_callback' => [ $this, 'sanitize_module_settings' ],
                        'default'           => [
                                'default_tag'  => 'h4',
                                'accent_color' => 'var(--primary)',
                        ],
                        'show_in_rest'      => true, // Obligatorio para que Gutenberg lo vea
                ]
        );
    }

    public function sanitize_module_settings( $input ): array {
        $sanitized = [];

        // Validamos que el tag sea solo uno de los permitidos
        if ( isset( $input['default_tag'] ) && in_array( $input['default_tag'], [ 'h2', 'h3', 'h4', 'h5', 'h6' ] ) ) {
            $sanitized['default_tag'] = $input['default_tag'];
        } else {
            $sanitized['default_tag'] = 'h4';
        }

        // El color puede ser Hex o una variable CSS
        if ( isset( $input['accent_color'] ) ) {
            $sanitized['accent_color'] = sanitize_text_field( $input['accent_color'] );
        }

        return $sanitized;
    }


    private function render_sample_heading( $options ): string {
        $return = '<' . esc_attr( $options['default_tag'] ) . ' style="margin-top:0;">';
        $return .= __( 'Sample Insight Card', 'triskelion-toolkit' );
        $return .= '</' . esc_attr( $options['default_tag'] ) . '>';

        return $return;
    }

    public function render_settings(): string {
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
            add_settings_error( 'triskelion_sample_module_group', 'settings_updated', __( 'Settings saved.', 'triskelion-toolkit' ), 'updated' );
        }
        $options = get_option( 'tsk_sample_module_settings', [
                'default_tag'  => 'h4',
                'accent_color' => 'var(--primary)'
        ] );

        ob_start(); ?>
        <div class="wrap tsk-module-settings-container">
            <?php settings_errors( 'triskelion_sample_module_group' ); ?>

            <div class="tsk-module-header">
                <h1><?php _e( 'Sample Module Settings', 'triskelion-toolkit' ); ?></h1>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields( 'triskelion_sample_module_group' ); ?>

                <div class="tsk-settings-layout" style="display: flex; gap: 20px; align-items: flex-start;">

                    <div class="tsk-settings-preview"
                         style="flex: 1; background: #fff; border: 1px solid #ccd0d4; padding: 20px;">
                        <h3 style="margin-top:0;"><?php _e( 'Live Preview', 'triskelion-toolkit' ); ?></h3>
                        <hr>
                        <div class="sample-card-preview"
                             style="border-left: 5px solid <?php echo esc_attr( $options['accent_color'] ); ?>; padding: 15px; background: #f9f9f9;">
                            <?php echo $this->render_sample_heading( $options ); ?>
                            <p style="margin-bottom:0;"><?php _e( 'Typography and color sync test.', 'triskelion-toolkit' ); ?></p>
                        </div>
                    </div>

                    <div class="form-side" style="flex: 1;">
                        <table class="form-table" role="presentation">
                            <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="default_tag"><?php _e( 'Default Heading Level', 'triskelion-toolkit' ); ?></label>
                                </th>
                                <td>
                                    <select name="tsk_sample_module_settings[default_tag]" id="default_tag"
                                            class="postform">
                                        <?php
                                        $tags = [
                                                'h2' => 'Heading 2',
                                                'h3' => 'Heading 3',
                                                'h4' => 'Heading 4',
                                                'h5' => 'Heading 5',
                                                'h6' => 'Heading 6'
                                        ];
                                        foreach ( $tags as $value => $label ) : ?>
                                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $options['default_tag'], $value ); ?>>
                                                <?php echo esc_html( $label ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="accent_color"><?php _e( 'Accent Color', 'triskelion-toolkit' ); ?></label>
                                </th>
                                <td>
                                    <input name="tsk_sample_module_settings[accent_color]" type="text" id="accent_color"
                                           value="<?php echo esc_attr( $options['accent_color'] ); ?>"
                                           class="regular-text code">
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <div style="margin-top: 20px;">
                            <?php submit_button( __( 'Save Changes', 'triskelion-toolkit' ) ); ?>
                        </div>
                    </div>
                </div>
            </form>
        </div> <?php
        return ob_get_clean();
    }
}