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
    const { files, activeTabIndex, showLineNumbers, terminalTheme } = attributes;
    const blockProps = useBlockProps();

    // --- MANEJADORES DE ESTADO ---
    const updateFile = ( index, key, value ) => {
        const newFiles = [ ...files ];
        newFiles[ index ] = { ...newFiles[ index ], [ key ]: value };
        setAttributes( { files: newFiles } );
    };

    const addFile = () => {
        const newFiles = [ ...files, { fileName: 'nuevo.js', language: 'javascript', content: '' } ];
        setAttributes( { files: newFiles } );
        setAttributes( { activeTabIndex: newFiles.length - 1 } );
    };

    const removeFile = ( index ) => {
        if ( files.length <= 1 ) return;
        const newFiles = files.filter( ( _, i ) => i !== index );
        setAttributes( { files: newFiles, activeTabIndex: 0 } );
    };
    const moveFile = ( currentIndex, direction ) => {
        const newIndex = currentIndex + direction;
        if ( newIndex < 0 || newIndex >= files.length ) return;

        const newFiles = [ ...files ];
        // Intercambio de posiciones (Swap)
        [ newFiles[ currentIndex ], newFiles[ newIndex ] ] = [ newFiles[ newIndex ], newFiles[ currentIndex ] ];

        setAttributes( {
            files: newFiles,
            activeTabIndex: newIndex // Seguimos a la pestaña mientras se mueve
        } );
    };
    return (
        <div { ...blockProps }>
            { /* 1. BARRA LATERAL (Configuración) */ }
            <InspectorControls>
                <PanelBody title={ __( 'Archivo Actual', 'triskelion-toolkit' ) }>
                    <TextControl
                        label={ __( 'Nombre del archivo', 'triskelion-toolkit' ) }
                        value={ files[ activeTabIndex ]?.fileName }
                        onChange={ ( val ) => updateFile( activeTabIndex, 'fileName', val ) }
                    />
                    <SelectControl
                        label={ __( 'Lenguaje', 'triskelion-toolkit' ) }
                        value={ files[ activeTabIndex ]?.language }
                        options={ [
                            { label: 'JavaScript', value: 'javascript' },
                            { label: 'PHP', value: 'php' },
                            { label: 'CSS', value: 'css' },
                            { label: 'HTML', value: 'html' },
                        ] }
                        onChange={ ( val ) => updateFile( activeTabIndex, 'language', val ) }
                    />

                    <Button isDestructive variant="link" onClick={ () => removeFile( activeTabIndex ) } disabled={ files.length <= 1 }>
                        { __( 'Eliminar Pestaña', 'triskelion-toolkit' ) }
                    </Button>
                    <div style={ { marginTop: '15px', display: 'flex', gap: '10px', alignItems: 'center' } }>
                        <span style={ { fontSize: '12px', fontWeight: 'bold' } }>{ __( 'Ordenar:', 'triskelion-toolkit' ) }</span>

                        <Button
                            variant="secondary"
                            isSmall
                            icon="arrow-left-alt"
                            onClick={ () => moveFile( activeTabIndex, -1 ) }
                            disabled={ activeTabIndex === 0 }
                            label={ __( 'Mover a la izquierda', 'triskelion-toolkit' ) }
                        />

                        <Button
                            variant="secondary"
                            isSmall
                            icon="arrow-right-alt"
                            onClick={ () => moveFile( activeTabIndex, 1 ) }
                            disabled={ activeTabIndex === files.length - 1 }
                            label={ __( 'Mover a la derecha', 'triskelion-toolkit' ) }
                        />
                    </div>

                    <hr style={ { margin: '15px 0' } } />

                    <Button isDestructive variant="link" onClick={ () => removeFile( activeTabIndex ) } disabled={ files.length <= 1 }>
                        { __( 'Eliminar Pestaña', 'triskelion-toolkit' ) }
                    </Button>
                </PanelBody>

                <PanelBody title={ __( 'Ajustes Visuales', 'triskelion-toolkit' ) } initialOpen={ false }>
                    <ToggleControl
                        label={ __( 'Números de línea', 'triskelion-toolkit' ) }
                        checked={ showLineNumbers }
                        onChange={ ( val ) => setAttributes( { showLineNumbers: val } ) }
                    />
                    <SelectControl
                        label={ __( 'Tema', 'triskelion-toolkit' ) }
                        value={ terminalTheme }
                        options={ [
                            { label: 'Dark (Monokai)', value: 'dark' },
                            { label: 'Light (Classic)', value: 'light' },
                        ] }
                        onChange={ ( val ) => setAttributes( { terminalTheme: val } ) }
                    />
                </PanelBody>
            </InspectorControls>

            { /* 2. CUERPO DEL BLOQUE (La Terminal) */ }
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
                                { file.fileName || 'unnamed' }
                            </button>
                        ) ) }
                        <Button onClick={ addFile } className="tsk-add-tab">+</Button>
                    </div>
                </div>
                <div className="tsk-code-body">
                    <TextareaControl
                        value={ files[ activeTabIndex ]?.content }
                        onChange={ ( val ) => updateFile( activeTabIndex, 'content', val ) }
                        spellCheck={ false }
                    />
                </div>
            </div>
        </div>
    );
}