<?php
namespace Triskelion\Toolkit\Modules\CodeShowcase;

use Triskelion\Toolkit\Core\AbstractBlockLoader;

class CodeShowcaseBlockLoader extends AbstractBlockLoader {

	/**
	 * Definimos el nombre de la carpeta en ViewLayer/blocks/
	 */
	protected function get_block_name(): string {
		return 'code-showcase';
	}

	/**
	 * Esta función es la que WordPress llamará automáticamente
	 * gracias a la lógica de 'method_exists' que pusimos en la abstracta.
	 */
    public function render_frontend( $attributes, $content ): string {
        // DEBUG: Esto nos dirá si WordPress realmente está llamando a esta función
        // y qué datos le está pasando.
        error_log( "🚀 TSK RENDER: Ejecutando render para " . $this->get_block_name() );
        error_log( "DATA: " . json_encode( $attributes ) );

        $files = $attributes['files'] ?? [];

        // Si no hay archivos, el bloque se "esconde"
        if ( empty( $files ) ) {
            return '';
        }

        $active_index = $attributes['activeTabIndex'] ?? 0;

        ob_start();
        ?>
        <div class="tsk-code-showcase-container" data-theme="<?php echo esc_attr( $attributes['terminalTheme'] ?? 'dark' ); ?>">
            <div class="tsk-code-header">
                <div class="tsk-tabs-wrapper">
                    <?php foreach ( $files as $index => $file ) : ?>
                        <div class="tsk-tab <?php echo $index === $active_index ? 'active' : ''; ?>">
                            <?php echo esc_html( $file['fileName'] ?? 'unnamed.js' ); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="tsk-code-body">
                <pre><code><?php echo esc_html( $files[$active_index]['content'] ?? '' ); ?></code></pre>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
	/**
	 * Si necesitas settings globales en el panel de Triskelion
	 */
	protected function render_module_fields(): void {
		echo "<h4>Configuración del Módulo</h4>";
		echo "<p>El bloque está registrado y listo para usarse en el editor.</p>";
	}

}