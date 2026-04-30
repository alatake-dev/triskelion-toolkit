import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
    PanelBody,
    TextControl,
    SelectControl,
    ToggleControl,
    TextareaControl,
    Button
} from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
    const { files, activeTabIndex, showLineNumbers } = attributes;
    const blockProps = useBlockProps();


    // 1. Obtener lenguajes desde el Bridge de PHP (con fallback de seguridad)
    const availableLangs = window.tskShowcaseConfig?.activeLanguages || ['javascript', 'php', 'java', 'python'];

    const languageOptions = availableLangs.map( lang => ({
        label: lang.charAt(0).toUpperCase() + lang.slice(1),
        value: lang
    }));

    // 2. Smart-Mapping: Detectar lenguaje por extensión
    const getLanguageFromExtension = ( fileName ) => {
        if ( ! fileName.includes( '.' ) ) return null;
        const ext = fileName.split( '.' ).pop().toLowerCase();

        // Agrupamos extensiones comunes por el ID de Prism
        const extensionMap = {
            // Web
            'js': 'javascript', 'jsx': 'javascript', 'ts': 'typescript', 'tsx': 'typescript',
            'html': 'html', 'xhtml': 'html', 'css': 'css', 'scss': 'sass', 'less': 'less',
            // Backend & Systems
            'php': 'php', 'phtml': 'php', 'java': 'java', 'class': 'java', 'py': 'python',
            'rb': 'ruby', 'go': 'go', 'rs': 'rust', 'cs': 'csharp', 'cpp': 'cpp', 'c': 'c',
            // Data & Config
            'json': 'json', 'xml': 'xml', 'yml': 'yaml', 'yaml': 'yaml', 'sql': 'sql',
            'md': 'markdown', 'csv': 'csv',
            // Shell & Scripts
            'sh': 'bash', 'zsh': 'bash', 'bash': 'bash', 'bat': 'batch', 'ps1': 'powershell',
            // Mobile
            'swift': 'swift', 'kt': 'kotlin', 'dart': 'dart'
        };

        return extensionMap[ ext ] || null;
    };

    const updateFile = ( index, key, value ) => {
        const newFiles = [ ...files ];

        // Creamos el objeto actualizado
        let updatedFile = { ...newFiles[ index ], [ key ]: value };

        // LÓGICA DE DETECCIÓN: Solo si estamos editando el nombre y contiene un punto
        if ( key === 'fileName' && value.includes('.') ) {
            const ext = value.split('.').pop().toLowerCase();

            // Obtenemos el mapa que enviamos desde PHP (Bridge)
            const extensionMap = window.tskShowcaseConfig?.extensionMap || {};

            // Si la extensión existe en nuestro mapa, actualizamos el lenguaje
            if ( extensionMap[ext] ) {
                updatedFile.language = extensionMap[ext];
            }
        }

        newFiles[ index ] = updatedFile;
        setAttributes( { files: newFiles } );
    };
    const addFile = () => {
        const newFiles = [ ...files, {
            fileName: 'new-file.js',
            language: 'javascript',
            content: ''
        } ];
        setAttributes( { files: newFiles, activeTabIndex: newFiles.length - 1 } );
    };

    const removeFile = ( index ) => {
        if ( files.length <= 1 ) return;
        const newFiles = files.filter( ( _, i ) => i !== index );
        setAttributes( { files: newFiles, activeTabIndex: 0 } );
    };

    return (
        <div { ...blockProps }>
            <InspectorControls>
                <PanelBody title={ __( 'Configuración del Bloque', 'triskelion-toolkit' ) }>
                    <ToggleControl
                        label={ __( 'Mostrar números de línea', 'triskelion-toolkit' ) }
                        checked={ showLineNumbers }
                        onChange={ ( val ) => setAttributes( { showLineNumbers: val } ) }
                    />
                </PanelBody>

                <PanelBody title={ __( 'Archivo Actual', 'triskelion-toolkit' ) } initialOpen={ true }>
                    <TextControl
                        label={ __( 'Nombre del archivo', 'triskelion-toolkit' ) }
                        value={ files[ activeTabIndex ]?.fileName }
                        onChange={ ( val ) => updateFile( activeTabIndex, 'fileName', val ) }
                    />
                    <SelectControl
                        label={ __( 'Lenguaje', 'triskelion-toolkit' ) }
                        value={ files[ activeTabIndex ]?.language }
                        options={ languageOptions }
                        onChange={ ( val ) => updateFile( activeTabIndex, 'language', val ) }
                    />
                    { files.length > 1 && (
                        <Button isDestructive onClick={ () => removeFile( activeTabIndex ) }>
                            { __( 'Eliminar este archivo', 'triskelion-toolkit' ) }
                        </Button>
                    ) }
                </PanelBody>
            </InspectorControls>

            <div className="tsk-code-showcase-container">
                <div className="tsk-code-header">
                    <div className="tsk-window-buttons">
                        <span className="dot red"></span>
                        <span className="dot yellow"></span>
                        <span className="dot green"></span>
                    </div>
                    <div className="tsk-tabs-wrapper">
                        { files.map( ( file, index ) => (
                            <button
                                key={ index }
                                className={ `tsk-tab ${ activeTabIndex === index ? 'active' : '' }` }
                                onClick={ () => setAttributes( { activeTabIndex: index } ) }
                            >
                                { file.fileName || __( 'unnamed', 'triskelion-toolkit' ) }
                            </button>
                        ) ) }
                    </div>
                    <Button onClick={ addFile } className="tsk-add-tab">+</Button>
                </div>
                <div className="tsk-code-body">
                    <TextareaControl
                        value={ files[ activeTabIndex ]?.content }
                        onChange={ ( val ) => updateFile( activeTabIndex, 'content', val ) }
                        placeholder={ __( 'Pega tu código aquí...', 'triskelion-toolkit' ) }
                        spellCheck={ false }
                    />
                </div>
            </div>
        </div>
    );
}