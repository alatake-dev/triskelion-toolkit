<?php
namespace src\Modules\CodeShowcase;

use src\Core\AbstractBlockLoader;
use src\Core\SettingsProviderInterface;
use src\Modules\VendorRegistry;

class CodeShowcaseBlockLoader extends AbstractBlockLoader implements SettingsProviderInterface {

    private const DEFAULT_LANGS = ['java', 'javascript', 'php', 'python'];

    protected function get_block_name(): string {
        return 'code-showcase';
    }

    public function load(): void {
        VendorRegistry::use('prism');
        parent::load();
    }

    public function register_module_settings(): void {
        register_setting(
                $this->get_settings_group(),
                'tsk_showcase_settings',
                [
                        'type'         => 'object',
                        'show_in_rest' => [
                                'schema' => [
                                        'type'       => 'object',
                                        'properties' => [
                                                'active_languages'    => [
                                                        'type'  => 'array',
                                                        'items' => [ 'type' => 'string' ],
                                                ],
                                                'line_numbers_global' => [
                                                        'type'    => 'boolean',
                                                ],
                                        ],
                                ],
                        ],
                        'default' => [
                                'active_languages'    => self::DEFAULT_LANGS,
                                'line_numbers_global' => true,
                        ],
                ]
        );
    }

    public function sanitize_module_settings( $input ) {
        $sanitized = [];

        $sanitized['line_numbers_global'] = isset($input['line_numbers_global']) ? true : false;

        if ( empty($input['active_languages']) ) {
            $sanitized['active_languages'] = self::DEFAULT_LANGS;
            add_settings_error(
                    $this->get_settings_group(), // Usar el ID dinámico del grupo
                    'empty_languages',
                    __( 'At least one language must be active. Defaults restored.', 'triskelion-toolkit' ),
                    'error'
            );
        } else {
            $sanitized['active_languages'] = array_map('sanitize_text_field', $input['active_languages']);
        }

        return $sanitized;
    }
    protected function render_module_fields(): void {
        $settings = get_option('tsk_showcase_settings', []);
        $active_langs = $settings['active_languages'] ?? self::DEFAULT_LANGS;
        $line_numbers = $settings['line_numbers_global'] ?? true;
        ?>

        <div class="tsk-settings-card">
            <div class="tsk-field-row" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h4 style="margin:0;"><?php esc_html_e( 'Line Numbers', 'triskelion-toolkit' ); ?></h4>
                    <p class="description"><?php esc_html_e( 'Enable line numbers column globally.', 'triskelion-toolkit' ); ?></p>
                </div>
                <label class="tsk-switch">
                    <input type="checkbox" name="tsk_showcase_settings[line_numbers_global]" <?php checked($line_numbers); ?>>
                    <span class="tsk-slider"></span>
                </label>
            </div>
        </div>

        <div class="tsk-settings-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h4 style="margin:0;"><?php esc_html_e( 'Language Inventory', 'triskelion-toolkit' ); ?></h4>
                <input type="text" id="tsk-lang-search"
                       placeholder="<?php esc_attr_e( 'Search languages...', 'triskelion-toolkit' ); ?>"
                       style="width:250px; border-radius:20px; padding:5px 15px;">
            </div>

            <div id="tsk-active-languages" class="tsk-lang-grid">
                <?php foreach ( $active_langs as $lang ) : ?>
                    <div class="tsk-lang-item active" data-lang="<?php echo esc_attr($lang); ?>">
                        <label class="tsk-switch-label">
                            <label class="tsk-switch">
                                <input type="checkbox" name="tsk_showcase_settings[active_languages][]"
                                       value="<?php echo esc_attr($lang); ?>" checked>
                                <span class="tsk-slider"></span>
                            </label>
                            <span class="tsk-lang-text"><?php echo esc_html( ucfirst($lang) ); ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr style="margin:20px 0; opacity:0.1;">

            <div id="tsk-available-languages" class="tsk-lang-grid" style="opacity:0.6;">
            </div>
        </div>
        <?php
    }


    public function enqueue_block_assets(): void {
        $settings = get_option('tsk_showcase_settings', []);
        $active_languages = $settings['active_languages'] ?? self::DEFAULT_LANGS;

        wp_enqueue_style('tsk-prism-theme');
        wp_enqueue_script('tsk-prism-autoloader');
        wp_enqueue_script('tsk-prism-line-numbers');
        wp_enqueue_script('tsk-prism-line-highlight');

        wp_localize_script(
                'triskelion-code-showcase-editor-script',
                'tskShowcaseConfig',
                [
                        'activeLanguages' => $active_languages,
                        'lineNumbersDefault' => $settings['line_numbers_global'] ?? true
                ]
        );
    }

    /**
     * Master Map of supported languages and their extensions.
     * Generated via Python Parser.
     */
    private function get_all_prism_languages(): array {
        return [
                'abap' => [
                        'label' => 'Abap',
                        'ext'   => ['abap']
                ],
                'abnf' => [
                        'label' => 'Abnf',
                        'ext'   => ['abnf']
                ],
                'actionscript' => [
                        'label' => 'Actionscript',
                        'ext'   => ['as']
                ],
                'ada' => [
                        'label' => 'Ada',
                        'ext'   => ['ada']
                ],
                'agda' => [
                        'label' => 'Agda',
                        'ext'   => ['agda']
                ],
                'al' => [
                        'label' => 'Al',
                        'ext'   => ['al']
                ],
                'antlr4' => [
                        'label' => 'Antlr4',
                        'ext'   => ['antlr4']
                ],
                'apacheconf' => [
                        'label' => 'Apacheconf',
                        'ext'   => ['conf']
                ],
                'apex' => [
                        'label' => 'Apex',
                        'ext'   => ['apex']
                ],
                'apl' => [
                        'label' => 'Apl',
                        'ext'   => ['apl']
                ],
                'applescript' => [
                        'label' => 'Applescript',
                        'ext'   => ['applescript']
                ],
                'aql' => [
                        'label' => 'Aql',
                        'ext'   => ['aql']
                ],
                'arduino' => [
                        'label' => 'Arduino',
                        'ext'   => ['arduino']
                ],
                'arff' => [
                        'label' => 'Arff',
                        'ext'   => ['arff']
                ],
                'armasm' => [
                        'label' => 'Armasm',
                        'ext'   => ['armasm']
                ],
                'arturo' => [
                        'label' => 'Arturo',
                        'ext'   => ['arturo']
                ],
                'asciidoc' => [
                        'label' => 'Asciidoc',
                        'ext'   => ['asciidoc']
                ],
                'asm6502' => [
                        'label' => 'Asm6502',
                        'ext'   => ['asm6502']
                ],
                'asmatmel' => [
                        'label' => 'Asmatmel',
                        'ext'   => ['asmatmel']
                ],
                'aspnet' => [
                        'label' => 'ASP.NET',
                        'ext'   => ['aspnet']
                ],
                'autohotkey' => [
                        'label' => 'Autohotkey',
                        'ext'   => ['autohotkey']
                ],
                'autoit' => [
                        'label' => 'Autoit',
                        'ext'   => ['autoit']
                ],
                'avisynth' => [
                        'label' => 'Avisynth',
                        'ext'   => ['avisynth']
                ],
                'avro-idl' => [
                        'label' => 'Avro Idl',
                        'ext'   => ['avro-idl']
                ],
                'awk' => [
                        'label' => 'Awk',
                        'ext'   => ['awk']
                ],
                'bash' => [
                        'label' => 'Bash',
                        'ext'   => ['sh', 'bash', 'zsh']
                ],
                'basic' => [
                        'label' => 'Basic',
                        'ext'   => ['basic']
                ],
                'batch' => [
                        'label' => 'Batch',
                        'ext'   => ['batch']
                ],
                'bbcode' => [
                        'label' => 'Bbcode',
                        'ext'   => ['bbcode']
                ],
                'bbj' => [
                        'label' => 'Bbj',
                        'ext'   => ['bbj']
                ],
                'bicep' => [
                        'label' => 'Bicep',
                        'ext'   => ['bicep']
                ],
                'birb' => [
                        'label' => 'Birb',
                        'ext'   => ['birb']
                ],
                'bison' => [
                        'label' => 'Bison',
                        'ext'   => ['bison']
                ],
                'bnf' => [
                        'label' => 'Bnf',
                        'ext'   => ['bnf']
                ],
                'bqn' => [
                        'label' => 'Bqn',
                        'ext'   => ['bqn']
                ],
                'brainfuck' => [
                        'label' => 'Brainfuck',
                        'ext'   => ['brainfuck']
                ],
                'brightscript' => [
                        'label' => 'Brightscript',
                        'ext'   => ['brightscript']
                ],
                'bro' => [
                        'label' => 'Bro',
                        'ext'   => ['bro']
                ],
                'bsl' => [
                        'label' => 'Bsl',
                        'ext'   => ['bsl']
                ],
                'c' => [
                        'label' => 'C',
                        'ext'   => ['c']
                ],
                'cfscript' => [
                        'label' => 'Cfscript',
                        'ext'   => ['cfscript']
                ],
                'chaiscript' => [
                        'label' => 'Chaiscript',
                        'ext'   => ['chaiscript']
                ],
                'cil' => [
                        'label' => 'Cil',
                        'ext'   => ['cil']
                ],
                'cilkc' => [
                        'label' => 'Cilkc',
                        'ext'   => ['cilkc']
                ],
                'cilkcpp' => [
                        'label' => 'Cilkcpp',
                        'ext'   => ['cilkcpp']
                ],
                'clike' => [
                        'label' => 'Clike',
                        'ext'   => ['clike']
                ],
                'clojure' => [
                        'label' => 'Clojure',
                        'ext'   => ['clojure']
                ],
                'cmake' => [
                        'label' => 'Cmake',
                        'ext'   => ['cmake']
                ],
                'cobol' => [
                        'label' => 'Cobol',
                        'ext'   => ['cobol']
                ],
                'coffeescript' => [
                        'label' => 'Coffeescript',
                        'ext'   => ['coffeescript']
                ],
                'concurnas' => [
                        'label' => 'Concurnas',
                        'ext'   => ['concurnas']
                ],
                'cooklang' => [
                        'label' => 'Cooklang',
                        'ext'   => ['cooklang']
                ],
                'coq' => [
                        'label' => 'Coq',
                        'ext'   => ['coq']
                ],
                'cpp' => [
                        'label' => 'C++',
                        'ext'   => ['cpp']
                ],
                'crystal' => [
                        'label' => 'Crystal',
                        'ext'   => ['crystal']
                ],
                'csharp' => [
                        'label' => 'C#',
                        'ext'   => ['cs']
                ],
                'cshtml' => [
                        'label' => 'Cshtml',
                        'ext'   => ['cshtml']
                ],
                'csp' => [
                        'label' => 'Csp',
                        'ext'   => ['csp']
                ],
                'css-extras' => [
                        'label' => 'Css Extras',
                        'ext'   => ['css-extras']
                ],
                'css' => [
                        'label' => 'CSS',
                        'ext'   => ['css']
                ],
                'csv' => [
                        'label' => 'Csv',
                        'ext'   => ['csv']
                ],
                'cue' => [
                        'label' => 'Cue',
                        'ext'   => ['cue']
                ],
                'cypher' => [
                        'label' => 'Cypher',
                        'ext'   => ['cypher']
                ],
                'd' => [
                        'label' => 'D',
                        'ext'   => ['d']
                ],
                'dart' => [
                        'label' => 'Dart',
                        'ext'   => ['dart']
                ],
                'dataweave' => [
                        'label' => 'Dataweave',
                        'ext'   => ['dataweave']
                ],
                'dax' => [
                        'label' => 'Dax',
                        'ext'   => ['dax']
                ],
                'dhall' => [
                        'label' => 'Dhall',
                        'ext'   => ['dhall']
                ],
                'diff' => [
                        'label' => 'Diff',
                        'ext'   => ['diff']
                ],
                'django' => [
                        'label' => 'Django',
                        'ext'   => ['django']
                ],
                'dns-zone-file' => [
                        'label' => 'Dns Zone File',
                        'ext'   => ['dns-zone-file']
                ],
                'docker' => [
                        'label' => 'Docker',
                        'ext'   => ['docker']
                ],
                'dot' => [
                        'label' => 'Dot',
                        'ext'   => ['dot']
                ],
                'ebnf' => [
                        'label' => 'Ebnf',
                        'ext'   => ['ebnf']
                ],
                'editorconfig' => [
                        'label' => 'Editorconfig',
                        'ext'   => ['editorconfig']
                ],
                'eiffel' => [
                        'label' => 'Eiffel',
                        'ext'   => ['eiffel']
                ],
                'ejs' => [
                        'label' => 'Ejs',
                        'ext'   => ['ejs']
                ],
                'elixir' => [
                        'label' => 'Elixir',
                        'ext'   => ['elixir']
                ],
                'elm' => [
                        'label' => 'Elm',
                        'ext'   => ['elm']
                ],
                'erb' => [
                        'label' => 'Erb',
                        'ext'   => ['erb']
                ],
                'erlang' => [
                        'label' => 'Erlang',
                        'ext'   => ['erlang']
                ],
                'etlua' => [
                        'label' => 'Etlua',
                        'ext'   => ['etlua']
                ],
                'excel-formula' => [
                        'label' => 'Excel Formula',
                        'ext'   => ['excel-formula']
                ],
                'factor' => [
                        'label' => 'Factor',
                        'ext'   => ['factor']
                ],
                'false' => [
                        'label' => 'False',
                        'ext'   => ['false']
                ],
                'firestore-security-rules' => [
                        'label' => 'Firestore Security Rules',
                        'ext'   => ['firestore-security-rules']
                ],
                'flow' => [
                        'label' => 'Flow',
                        'ext'   => ['flow']
                ],
                'fortran' => [
                        'label' => 'Fortran',
                        'ext'   => ['fortran']
                ],
                'fsharp' => [
                        'label' => 'Fsharp',
                        'ext'   => ['fsharp']
                ],
                'ftl' => [
                        'label' => 'Ftl',
                        'ext'   => ['ftl']
                ],
                'gap' => [
                        'label' => 'Gap',
                        'ext'   => ['gap']
                ],
                'gcode' => [
                        'label' => 'Gcode',
                        'ext'   => ['gcode']
                ],
                'gdscript' => [
                        'label' => 'Gdscript',
                        'ext'   => ['gdscript']
                ],
                'gedcom' => [
                        'label' => 'Gedcom',
                        'ext'   => ['gedcom']
                ],
                'gettext' => [
                        'label' => 'Gettext',
                        'ext'   => ['gettext']
                ],
                'gherkin' => [
                        'label' => 'Gherkin',
                        'ext'   => ['gherkin']
                ],
                'git' => [
                        'label' => 'Git',
                        'ext'   => ['git']
                ],
                'glsl' => [
                        'label' => 'Glsl',
                        'ext'   => ['glsl']
                ],
                'gml' => [
                        'label' => 'Gml',
                        'ext'   => ['gml']
                ],
                'gn' => [
                        'label' => 'Gn',
                        'ext'   => ['gn']
                ],
                'go-module' => [
                        'label' => 'Go Module',
                        'ext'   => ['go-module']
                ],
                'go' => [
                        'label' => 'Go',
                        'ext'   => ['go']
                ],
                'gradle' => [
                        'label' => 'Gradle',
                        'ext'   => ['gradle']
                ],
                'graphql' => [
                        'label' => 'Graphql',
                        'ext'   => ['graphql']
                ],
                'groovy' => [
                        'label' => 'Groovy',
                        'ext'   => ['groovy']
                ],
                'haml' => [
                        'label' => 'Haml',
                        'ext'   => ['haml']
                ],
                'handlebars' => [
                        'label' => 'Handlebars',
                        'ext'   => ['handlebars']
                ],
                'haskell' => [
                        'label' => 'Haskell',
                        'ext'   => ['haskell']
                ],
                'haxe' => [
                        'label' => 'Haxe',
                        'ext'   => ['haxe']
                ],
                'hcl' => [
                        'label' => 'Hcl',
                        'ext'   => ['hcl']
                ],
                'hlsl' => [
                        'label' => 'Hlsl',
                        'ext'   => ['hlsl']
                ],
                'hoon' => [
                        'label' => 'Hoon',
                        'ext'   => ['hoon']
                ],
                'hpkp' => [
                        'label' => 'Hpkp',
                        'ext'   => ['hpkp']
                ],
                'hsts' => [
                        'label' => 'Hsts',
                        'ext'   => ['hsts']
                ],
                'http' => [
                        'label' => 'Http',
                        'ext'   => ['http']
                ],
                'ichigojam' => [
                        'label' => 'Ichigojam',
                        'ext'   => ['ichigojam']
                ],
                'icon' => [
                        'label' => 'Icon',
                        'ext'   => ['icon']
                ],
                'icu-message-format' => [
                        'label' => 'Icu Message Format',
                        'ext'   => ['icu-message-format']
                ],
                'idris' => [
                        'label' => 'Idris',
                        'ext'   => ['idris']
                ],
                'iecst' => [
                        'label' => 'Iecst',
                        'ext'   => ['iecst']
                ],
                'ignore' => [
                        'label' => 'Ignore',
                        'ext'   => ['ignore']
                ],
                'inform7' => [
                        'label' => 'Inform7',
                        'ext'   => ['inform7']
                ],
                'ini' => [
                        'label' => 'Ini',
                        'ext'   => ['ini']
                ],
                'io' => [
                        'label' => 'Io',
                        'ext'   => ['io']
                ],
                'j' => [
                        'label' => 'J',
                        'ext'   => ['j']
                ],
                'java' => [
                        'label' => 'Java',
                        'ext'   => ['java']
                ],
                'javadoc' => [
                        'label' => 'Javadoc',
                        'ext'   => ['javadoc']
                ],
                'javadoclike' => [
                        'label' => 'Javadoclike',
                        'ext'   => ['javadoclike']
                ],
                'javascript' => [
                        'label' => 'JavaScript',
                        'ext'   => ['js', 'jsx', 'mjs']
                ],
                'javastacktrace' => [
                        'label' => 'Javastacktrace',
                        'ext'   => ['javastacktrace']
                ],
                'jexl' => [
                        'label' => 'Jexl',
                        'ext'   => ['jexl']
                ],
                'jolie' => [
                        'label' => 'Jolie',
                        'ext'   => ['jolie']
                ],
                'jq' => [
                        'label' => 'Jq',
                        'ext'   => ['jq']
                ],
                'js-extras' => [
                        'label' => 'Js Extras',
                        'ext'   => ['js-extras']
                ],
                'js-templates' => [
                        'label' => 'Js Templates',
                        'ext'   => ['js-templates']
                ],
                'jsdoc' => [
                        'label' => 'Jsdoc',
                        'ext'   => ['jsdoc']
                ],
                'json' => [
                        'label' => 'JSON',
                        'ext'   => ['json']
                ],
                'json5' => [
                        'label' => 'Json5',
                        'ext'   => ['json5']
                ],
                'jsonp' => [
                        'label' => 'Jsonp',
                        'ext'   => ['jsonp']
                ],
                'jsstacktrace' => [
                        'label' => 'Jsstacktrace',
                        'ext'   => ['jsstacktrace']
                ],
                'jsx' => [
                        'label' => 'Jsx',
                        'ext'   => ['jsx']
                ],
                'julia' => [
                        'label' => 'Julia',
                        'ext'   => ['julia']
                ],
                'keepalived' => [
                        'label' => 'Keepalived',
                        'ext'   => ['keepalived']
                ],
                'keyman' => [
                        'label' => 'Keyman',
                        'ext'   => ['keyman']
                ],
                'kotlin' => [
                        'label' => 'Kotlin',
                        'ext'   => ['kotlin']
                ],
                'kumir' => [
                        'label' => 'Kumir',
                        'ext'   => ['kumir']
                ],
                'kusto' => [
                        'label' => 'Kusto',
                        'ext'   => ['kusto']
                ],
                'latex' => [
                        'label' => 'Latex',
                        'ext'   => ['latex']
                ],
                'latte' => [
                        'label' => 'Latte',
                        'ext'   => ['latte']
                ],
                'less' => [
                        'label' => 'Less',
                        'ext'   => ['less']
                ],
                'lilypond' => [
                        'label' => 'Lilypond',
                        'ext'   => ['lilypond']
                ],
                'linker-script' => [
                        'label' => 'Linker Script',
                        'ext'   => ['linker-script']
                ],
                'liquid' => [
                        'label' => 'Liquid',
                        'ext'   => ['liquid']
                ],
                'lisp' => [
                        'label' => 'Lisp',
                        'ext'   => ['lisp']
                ],
                'livescript' => [
                        'label' => 'Livescript',
                        'ext'   => ['livescript']
                ],
                'llvm' => [
                        'label' => 'Llvm',
                        'ext'   => ['llvm']
                ],
                'log' => [
                        'label' => 'Log',
                        'ext'   => ['log']
                ],
                'lolcode' => [
                        'label' => 'Lolcode',
                        'ext'   => ['lolcode']
                ],
                'lua' => [
                        'label' => 'Lua',
                        'ext'   => ['lua']
                ],
                'magma' => [
                        'label' => 'Magma',
                        'ext'   => ['magma']
                ],
                'makefile' => [
                        'label' => 'Makefile',
                        'ext'   => ['makefile']
                ],
                'markdown' => [
                        'label' => 'Markdown',
                        'ext'   => ['markdown']
                ],
                'markup-templating' => [
                        'label' => 'Markup Templating',
                        'ext'   => ['markup-templating']
                ],
                'markup' => [
                        'label' => 'Markup',
                        'ext'   => ['html', 'xml', 'svg']
                ],
                'mata' => [
                        'label' => 'Mata',
                        'ext'   => ['mata']
                ],
                'matlab' => [
                        'label' => 'Matlab',
                        'ext'   => ['matlab']
                ],
                'maxscript' => [
                        'label' => 'Maxscript',
                        'ext'   => ['maxscript']
                ],
                'mel' => [
                        'label' => 'Mel',
                        'ext'   => ['mel']
                ],
                'mermaid' => [
                        'label' => 'Mermaid',
                        'ext'   => ['mermaid']
                ],
                'metafont' => [
                        'label' => 'Metafont',
                        'ext'   => ['metafont']
                ],
                'mizar' => [
                        'label' => 'Mizar',
                        'ext'   => ['mizar']
                ],
                'mongodb' => [
                        'label' => 'MongoDB',
                        'ext'   => ['mongodb']
                ],
                'monkey' => [
                        'label' => 'Monkey',
                        'ext'   => ['monkey']
                ],
                'moonscript' => [
                        'label' => 'Moonscript',
                        'ext'   => ['moonscript']
                ],
                'n1ql' => [
                        'label' => 'N1Ql',
                        'ext'   => ['n1ql']
                ],
                'n4js' => [
                        'label' => 'N4Js',
                        'ext'   => ['n4js']
                ],
                'nand2tetris-hdl' => [
                        'label' => 'Nand2Tetris Hdl',
                        'ext'   => ['nand2tetris-hdl']
                ],
                'naniscript' => [
                        'label' => 'Naniscript',
                        'ext'   => ['naniscript']
                ],
                'nasm' => [
                        'label' => 'Nasm',
                        'ext'   => ['nasm']
                ],
                'neon' => [
                        'label' => 'Neon',
                        'ext'   => ['neon']
                ],
                'nevod' => [
                        'label' => 'Nevod',
                        'ext'   => ['nevod']
                ],
                'nginx' => [
                        'label' => 'Nginx',
                        'ext'   => ['nginx']
                ],
                'nim' => [
                        'label' => 'Nim',
                        'ext'   => ['nim']
                ],
                'nix' => [
                        'label' => 'Nix',
                        'ext'   => ['nix']
                ],
                'nsis' => [
                        'label' => 'Nsis',
                        'ext'   => ['nsis']
                ],
                'objectivec' => [
                        'label' => 'Objective-C',
                        'ext'   => ['objectivec']
                ],
                'ocaml' => [
                        'label' => 'Ocaml',
                        'ext'   => ['ocaml']
                ],
                'odin' => [
                        'label' => 'Odin',
                        'ext'   => ['odin']
                ],
                'opencl' => [
                        'label' => 'Opencl',
                        'ext'   => ['opencl']
                ],
                'openqasm' => [
                        'label' => 'Openqasm',
                        'ext'   => ['openqasm']
                ],
                'oz' => [
                        'label' => 'Oz',
                        'ext'   => ['oz']
                ],
                'parigp' => [
                        'label' => 'Parigp',
                        'ext'   => ['parigp']
                ],
                'parser' => [
                        'label' => 'Parser',
                        'ext'   => ['parser']
                ],
                'pascal' => [
                        'label' => 'Pascal',
                        'ext'   => ['pascal']
                ],
                'pascaligo' => [
                        'label' => 'Pascaligo',
                        'ext'   => ['pascaligo']
                ],
                'pcaxis' => [
                        'label' => 'Pcaxis',
                        'ext'   => ['pcaxis']
                ],
                'peoplecode' => [
                        'label' => 'Peoplecode',
                        'ext'   => ['peoplecode']
                ],
                'perl' => [
                        'label' => 'Perl',
                        'ext'   => ['perl']
                ],
                'php-extras' => [
                        'label' => 'Php Extras',
                        'ext'   => ['php-extras']
                ],
                'php' => [
                        'label' => 'PHP',
                        'ext'   => ['php']
                ],
                'phpdoc' => [
                        'label' => 'Phpdoc',
                        'ext'   => ['phpdoc']
                ],
                'plant-uml' => [
                        'label' => 'Plant Uml',
                        'ext'   => ['plant-uml']
                ],
                'plsql' => [
                        'label' => 'Plsql',
                        'ext'   => ['plsql']
                ],
                'powerquery' => [
                        'label' => 'Powerquery',
                        'ext'   => ['powerquery']
                ],
                'powershell' => [
                        'label' => 'Powershell',
                        'ext'   => ['ps1', 'psm1']
                ],
                'processing' => [
                        'label' => 'Processing',
                        'ext'   => ['processing']
                ],
                'prolog' => [
                        'label' => 'Prolog',
                        'ext'   => ['prolog']
                ],
                'promql' => [
                        'label' => 'Promql',
                        'ext'   => ['promql']
                ],
                'properties' => [
                        'label' => 'Properties',
                        'ext'   => ['properties']
                ],
                'protobuf' => [
                        'label' => 'Protobuf',
                        'ext'   => ['protobuf']
                ],
                'psl' => [
                        'label' => 'Psl',
                        'ext'   => ['psl']
                ],
                'pug' => [
                        'label' => 'Pug',
                        'ext'   => ['pug']
                ],
                'puppet' => [
                        'label' => 'Puppet',
                        'ext'   => ['puppet']
                ],
                'pure' => [
                        'label' => 'Pure',
                        'ext'   => ['pure']
                ],
                'purebasic' => [
                        'label' => 'Purebasic',
                        'ext'   => ['purebasic']
                ],
                'purescript' => [
                        'label' => 'Purescript',
                        'ext'   => ['purescript']
                ],
                'python' => [
                        'label' => 'Python',
                        'ext'   => ['py', 'pyw']
                ],
                'q' => [
                        'label' => 'Q',
                        'ext'   => ['q']
                ],
                'qml' => [
                        'label' => 'Qml',
                        'ext'   => ['qml']
                ],
                'qore' => [
                        'label' => 'Qore',
                        'ext'   => ['qore']
                ],
                'qsharp' => [
                        'label' => 'Qsharp',
                        'ext'   => ['qsharp']
                ],
                'r' => [
                        'label' => 'R',
                        'ext'   => ['r']
                ],
                'racket' => [
                        'label' => 'Racket',
                        'ext'   => ['racket']
                ],
                'reason' => [
                        'label' => 'Reason',
                        'ext'   => ['reason']
                ],
                'regex' => [
                        'label' => 'Regex',
                        'ext'   => ['regex']
                ],
                'rego' => [
                        'label' => 'Rego',
                        'ext'   => ['rego']
                ],
                'renpy' => [
                        'label' => 'Renpy',
                        'ext'   => ['renpy']
                ],
                'rescript' => [
                        'label' => 'Rescript',
                        'ext'   => ['rescript']
                ],
                'rest' => [
                        'label' => 'Rest',
                        'ext'   => ['rest']
                ],
                'rip' => [
                        'label' => 'Rip',
                        'ext'   => ['rip']
                ],
                'roboconf' => [
                        'label' => 'Roboconf',
                        'ext'   => ['roboconf']
                ],
                'robotframework' => [
                        'label' => 'Robotframework',
                        'ext'   => ['robotframework']
                ],
                'ruby' => [
                        'label' => 'Ruby',
                        'ext'   => ['rb']
                ],
                'rust' => [
                        'label' => 'Rust',
                        'ext'   => ['rs']
                ],
                'sas' => [
                        'label' => 'Sas',
                        'ext'   => ['sas']
                ],
                'sass' => [
                        'label' => 'Sass',
                        'ext'   => ['sass']
                ],
                'scala' => [
                        'label' => 'Scala',
                        'ext'   => ['scala']
                ],
                'scheme' => [
                        'label' => 'Scheme',
                        'ext'   => ['scheme']
                ],
                'scss' => [
                        'label' => 'Scss',
                        'ext'   => ['scss']
                ],
                'shell-session' => [
                        'label' => 'Shell Session',
                        'ext'   => ['shell-session']
                ],
                'smali' => [
                        'label' => 'Smali',
                        'ext'   => ['smali']
                ],
                'smalltalk' => [
                        'label' => 'Smalltalk',
                        'ext'   => ['smalltalk']
                ],
                'smarty' => [
                        'label' => 'Smarty',
                        'ext'   => ['smarty']
                ],
                'sml' => [
                        'label' => 'Sml',
                        'ext'   => ['sml']
                ],
                'solidity' => [
                        'label' => 'Solidity',
                        'ext'   => ['solidity']
                ],
                'solution-file' => [
                        'label' => 'Solution File',
                        'ext'   => ['solution-file']
                ],
                'soy' => [
                        'label' => 'Soy',
                        'ext'   => ['soy']
                ],
                'sparql' => [
                        'label' => 'Sparql',
                        'ext'   => ['sparql']
                ],
                'splunk-spl' => [
                        'label' => 'Splunk Spl',
                        'ext'   => ['splunk-spl']
                ],
                'sqf' => [
                        'label' => 'Sqf',
                        'ext'   => ['sqf']
                ],
                'sql' => [
                        'label' => 'SQL',
                        'ext'   => ['sql']
                ],
                'squirrel' => [
                        'label' => 'Squirrel',
                        'ext'   => ['squirrel']
                ],
                'stan' => [
                        'label' => 'Stan',
                        'ext'   => ['stan']
                ],
                'stata' => [
                        'label' => 'Stata',
                        'ext'   => ['stata']
                ],
                'stylus' => [
                        'label' => 'Stylus',
                        'ext'   => ['stylus']
                ],
                'supercollider' => [
                        'label' => 'Supercollider',
                        'ext'   => ['supercollider']
                ],
                'swift' => [
                        'label' => 'Swift',
                        'ext'   => ['swift']
                ],
                'systemd' => [
                        'label' => 'Systemd',
                        'ext'   => ['systemd']
                ],
                't4-cs' => [
                        'label' => 'T4 Cs',
                        'ext'   => ['t4-cs']
                ],
                't4-templating' => [
                        'label' => 'T4 Templating',
                        'ext'   => ['t4-templating']
                ],
                't4-vb' => [
                        'label' => 'T4 Vb',
                        'ext'   => ['t4-vb']
                ],
                'tap' => [
                        'label' => 'Tap',
                        'ext'   => ['tap']
                ],
                'tcl' => [
                        'label' => 'Tcl',
                        'ext'   => ['tcl']
                ],
                'textile' => [
                        'label' => 'Textile',
                        'ext'   => ['textile']
                ],
                'toml' => [
                        'label' => 'Toml',
                        'ext'   => ['toml']
                ],
                'tremor' => [
                        'label' => 'Tremor',
                        'ext'   => ['tremor']
                ],
                'tsx' => [
                        'label' => 'Tsx',
                        'ext'   => ['tsx']
                ],
                'tt2' => [
                        'label' => 'Tt2',
                        'ext'   => ['tt2']
                ],
                'turtle' => [
                        'label' => 'Turtle',
                        'ext'   => ['turtle']
                ],
                'twig' => [
                        'label' => 'Twig',
                        'ext'   => ['twig']
                ],
                'typescript' => [
                        'label' => 'TypeScript',
                        'ext'   => ['ts', 'tsx']
                ],
                'typoscript' => [
                        'label' => 'Typoscript',
                        'ext'   => ['typoscript']
                ],
                'unrealscript' => [
                        'label' => 'Unrealscript',
                        'ext'   => ['unrealscript']
                ],
                'uorazor' => [
                        'label' => 'Uorazor',
                        'ext'   => ['uorazor']
                ],
                'uri' => [
                        'label' => 'Uri',
                        'ext'   => ['uri']
                ],
                'v' => [
                        'label' => 'V',
                        'ext'   => ['v']
                ],
                'vala' => [
                        'label' => 'Vala',
                        'ext'   => ['vala']
                ],
                'vbnet' => [
                        'label' => 'Vbnet',
                        'ext'   => ['vbnet']
                ],
                'velocity' => [
                        'label' => 'Velocity',
                        'ext'   => ['velocity']
                ],
                'verilog' => [
                        'label' => 'Verilog',
                        'ext'   => ['verilog']
                ],
                'vhdl' => [
                        'label' => 'Vhdl',
                        'ext'   => ['vhdl']
                ],
                'vim' => [
                        'label' => 'Vim',
                        'ext'   => ['vim']
                ],
                'visual-basic' => [
                        'label' => 'Visual Basic',
                        'ext'   => ['vb', 'vba']
                ],
                'warpscript' => [
                        'label' => 'Warpscript',
                        'ext'   => ['warpscript']
                ],
                'wasm' => [
                        'label' => 'Wasm',
                        'ext'   => ['wasm']
                ],
                'web-idl' => [
                        'label' => 'Web Idl',
                        'ext'   => ['web-idl']
                ],
                'wgsl' => [
                        'label' => 'Wgsl',
                        'ext'   => ['wgsl']
                ],
                'wiki' => [
                        'label' => 'Wiki',
                        'ext'   => ['wiki']
                ],
                'wolfram' => [
                        'label' => 'Wolfram',
                        'ext'   => ['wolfram']
                ],
                'wren' => [
                        'label' => 'Wren',
                        'ext'   => ['wren']
                ],
                'xeora' => [
                        'label' => 'Xeora',
                        'ext'   => ['xeora']
                ],
                'xml-doc' => [
                        'label' => 'Xml Doc',
                        'ext'   => ['xml-doc']
                ],
                'xojo' => [
                        'label' => 'Xojo',
                        'ext'   => ['xojo']
                ],
                'xquery' => [
                        'label' => 'Xquery',
                        'ext'   => ['xquery']
                ],
                'yaml' => [
                        'label' => 'YAML',
                        'ext'   => ['yml', 'yaml']
                ],
                'yang' => [
                        'label' => 'Yang',
                        'ext'   => ['yang']
                ],
                'zig' => [
                        'label' => 'Zig',
                        'ext'   => ['zig']
                ],
        ];
    }
}