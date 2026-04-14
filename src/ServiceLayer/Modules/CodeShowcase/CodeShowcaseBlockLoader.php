<?php
namespace Triskelion\Toolkit\Modules\CodeShowcase;

use Triskelion\Toolkit\Core\AbstractBlockLoader;
use Triskelion\Toolkit\Modules\VendorRegistry;

class CodeShowcaseBlockLoader extends AbstractBlockLoader {

    protected function get_block_name(): string {
        return 'code-showcase';
    }

    /**
     * Registro de dependencias externas
     */
    public function load(): void {
        VendorRegistry::use('prism'); // El Registry se encarga de todo el setup
        parent::load();
    }

    /**
     * Encolado solo cuando el bloque se usa
     */
    public function enqueue_block_assets(): void {
        if (is_admin()) return;
        wp_enqueue_style('tsk-prism-theme');
        wp_enqueue_script('tsk-prism-autoloader');
    }

    public function render_frontend($attributes, $content): string {
        $files = $attributes['files'] ?? [];
        if (empty($files)) return '';

        ob_start(); ?>
        <div class="tsk-code-showcase-container">
            <div class="tsk-code-header">
                <div class="tsk-window-buttons">
                    <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
                </div>
                <div class="tsk-tabs-wrapper">
                    <?php foreach ($files as $index => $file) : ?>
                        <button class="tsk-tab <?php echo $index === 0 ? 'active' : ''; ?>" data-tab="<?php echo $index; ?>">
                            <?php echo esc_html($file['fileName']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="tsk-mobile-selector">
                    <select class="tsk-file-select">
                        <?php foreach ($files as $index => $file) : ?>
                            <option value="<?php echo $index; ?>"><?php echo esc_html($file['fileName']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="tsk-select-icon">
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 1l4 4 4-4"/></svg>
                    </span>
                </div>
                <button class="tsk-copy-button" title="Copy Code">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                </button>
            </div>
            <div class="tsk-code-body">
                <?php foreach ($files as $index => $file) : ?>
                    <div class="tsk-code-pane <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="tsk-lang-badge"><?php echo esc_html($file['language']); ?></div>
                        <pre class="language-<?php echo esc_attr($file['language']); ?>"><code><?php echo esc_html($file['content']); ?></code></pre>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function render_module_fields(): void {
        echo "<h4>Code Showcase</h4><p>El bloque dinámico está activo y gestionando Prism de forma centralizada.</p>";
    }
}