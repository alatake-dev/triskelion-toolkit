<?php
namespace Triskelion\Toolkit\Modules\CodeShowcase;

use Triskelion\Toolkit\Core\AbstractBlockLoader;
use Triskelion\Toolkit\Core\SettingsProviderInterface;
use Triskelion\Toolkit\Modules\VendorRegistry;

class CodeShowcaseBlockLoader extends AbstractBlockLoader implements SettingsProviderInterface{

    protected function get_block_name(): string {
        return 'code-showcase';
    }

    /**
     * Registro de dependencias externas
     */
    public function load(): void {
        VendorRegistry::use('prism');
        parent::load();
    }

    /**
     * Encolado solo cuando el bloque se usa
     */
    public function enqueue_block_assets(): void {
        // 1. Assets globales del bloque (CSS y Prism)
        wp_enqueue_style('tsk-prism-theme');
        wp_enqueue_script('tsk-prism-autoloader');
        wp_enqueue_script(TRISKELION_TOOLKIT_CORE);
        wp_script_add_data('triskelion-code-showcase-view-script', 'data', '/* i18n bridge */');
    }

    public function render_frontend($attributes, $content): string {
        $files = $attributes['files'] ?? [];
        if (empty($files)) return '';

        // Generamos un ID único para este bloque para evitar colisiones si hay varios en la misma página
        $block_id = 'tsk-code-' . wp_generate_password(6, false);

        ob_start(); ?>
        <div class="tsk-code-showcase-container" id="<?php echo esc_attr($block_id); ?>">
            <div class="tsk-code-header">
                <div class="tsk-window-buttons" aria-hidden="true">
                    <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
                </div>

                <div class="tsk-tabs-wrapper" role="tablist" aria-label="<?php esc_attr_e('Source files', TSK_DOMAIN); ?>">
                    <?php foreach ($files as $index => $file) :
                        $tab_id = "$block_id-tab-$index";
                        $panel_id = "$block_id-panel-$index";
                        $is_active = ($index === 0);
                        ?>
                        <button
                                class="tsk-tab <?php echo $is_active ? 'active' : ''; ?>"
                                data-tab="<?php echo $index; ?>"
                                role="tab"
                                id="<?php echo esc_attr($tab_id); ?>"
                                aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                                aria-controls="<?php echo esc_attr($panel_id); ?>"
                                tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
                        >
                            <?php echo esc_html($file['fileName']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="tsk-mobile-selector">
                    <select class="tsk-file-select" aria-label="<?php esc_attr_e('Select file to view', TSK_DOMAIN); ?>">
                        <?php foreach ($files as $index => $file) : ?>
                            <option value="<?php echo $index; ?>"><?php echo esc_html($file['fileName']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="tsk-select-icon" aria-hidden="true">
                    <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 1l4 4 4-4"/></svg>
                </span>
                </div>

                <button class="tsk-copy-button" title="<?php esc_attr_e('Copy Code', TSK_DOMAIN); ?>" aria-label="<?php esc_attr_e('Copy code to clipboard', TSK_DOMAIN); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                </button>
            </div>

            <div class="tsk-code-body">
                <?php foreach ($files as $index => $file) :
                    $tab_id = "$block_id-tab-$index";
                    $panel_id = "$block_id-panel-$index";
                    $is_active = ($index === 0);
                    ?>
                    <div
                            class="tsk-code-pane <?php echo $is_active ? 'active' : ''; ?>"
                            id="<?php echo esc_attr($panel_id); ?>"
                            role="tabpanel"
                            aria-labelledby="<?php echo esc_attr($tab_id); ?>"
                            <?php echo ! $is_active ? 'hidden' : ''; ?>>
                        <div class="tsk-lang-badge" aria-hidden="true"><?php echo esc_html($file['language']); ?></div>
                        <pre class="language-<?php echo esc_attr($file['language']); ?>"><code><?php echo esc_html($file['content']); ?></code></pre>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function render_module_fields(): void {
        echo "<h4>" . esc_html__('Code Showcase', TSK_DOMAIN) . "</h4>";
        echo "<p>" . esc_html__('Dynamic block is active and managing Prism highlighting.', TSK_DOMAIN) . "</p>";
    }
}