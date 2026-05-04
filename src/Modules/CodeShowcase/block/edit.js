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

    const updateFile = ( index, key, value ) => {
        const newFiles = [ ...files ];
        newFiles[ index ] = { ...newFiles[ index ], [ key ]: value };
        setAttributes( { files: newFiles } );
    };

    const addFile = () => {
        const newFiles = [ ...files, {
            fileName: __( 'new-file.js', 'triskelion-toolkit' ),
            language: 'javascript',
            content: ''
        } ];
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
        [ newFiles[ currentIndex ], newFiles[ newIndex ] ] = [ newFiles[ newIndex ], newFiles[ currentIndex ] ];

        setAttributes( {
            files: newFiles,
            activeTabIndex: newIndex
        } );
    };

    return (
        <div { ...blockProps }>
            <InspectorControls>
                <PanelBody title={ __( 'Current File', 'triskelion-toolkit') }>
                    <TextControl
                        label={ __( 'File Name', 'triskelion-toolkit' ) }
                        value={ files[ activeTabIndex ]?.fileName }
                        onChange={ ( val ) => updateFile( activeTabIndex, 'fileName', val ) }
                    />
                    <SelectControl
                        label={ __( 'Language', 'triskelion-toolkit' ) }
                        value={ files[ activeTabIndex ]?.language }
                        options={ [
                            { label: 'JavaScript / React (JSX)', value: 'javascript' }, // Highlight.php maneja JSX dentro de JS
                            { label: 'PHP', value: 'php' },
                            { label: 'Python', value: 'python' },
                            { label: 'Java', value: 'java' },
                            { label: 'HTML / XML', value: 'xml' },
                            { label: 'CSS', value: 'css' },
                            { label: 'SCSS', value: 'scss' },
                            { label: 'SQL', value: 'sql' },
                            { label: 'JSON', value: 'json' },
                            { label: 'Bash/Shell', value: 'bash' },
                            { label: 'YAML', value: 'yaml' },
                        ] }
                        onChange={ ( val ) => updateFile( activeTabIndex, 'language', val ) }
                    />

                    <Button isDestructive variant="link" onClick={ () => removeFile( activeTabIndex ) } disabled={ files.length <= 1 }>
                        { __( 'Remove Tab', 'triskelion-toolkit' ) }
                    </Button>

                    <div style={ { marginTop: '15px', display: 'flex', gap: '10px', alignItems: 'center' } }>
                        <span style={ { fontSize: '12px', fontWeight: 'bold' } }>
                            { __( 'Ordering:', 'triskelion-toolkit' ) }
                        </span>
                        <Button
                            variant="secondary"
                            isSmall
                            icon="arrow-left-alt"
                            onClick={ () => moveFile( activeTabIndex, -1 ) }
                            disabled={ activeTabIndex === 0 }
                            label={ __( 'Move left', 'triskelion-toolkit' ) }
                        />
                        <Button
                            variant="secondary"
                            isSmall
                            icon="arrow-right-alt"
                            onClick={ () => moveFile( activeTabIndex, 1 ) }
                            disabled={ activeTabIndex === files.length - 1 }
                            label={ __( 'Move right', 'triskelion-toolkit' ) }
                        />
                    </div>
                </PanelBody>

                <PanelBody title={ __( 'Visual Settings', 'triskelion-toolkit' ) } initialOpen={ false }>
                    <ToggleControl
                        label={ __( 'Line numbers', 'triskelion-toolkit' ) }
                        checked={ showLineNumbers }
                        onChange={ ( val ) => setAttributes( { showLineNumbers: val } ) }
                    />
                    <SelectControl
                        label={ __( 'Theme', 'triskelion-toolkit' ) }
                        value={ terminalTheme }
                        options={ [
                            { label: 'Dark (Monokai)', value: 'dark' },
                            { label: 'Light (Classic)', value: 'light' },
                        ] }
                        onChange={ ( val ) => setAttributes( { terminalTheme: val } ) }
                    />
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
                    <Button
                        onClick={ addFile }
                        className="tsk-add-tab"
                        label={ __( 'Add Tab', 'triskelion-toolkit' ) }
                    >+</Button>
                </div>
                <div className="tsk-code-body">
                    <TextareaControl
                        value={ files[ activeTabIndex ]?.content }
                        onChange={ ( val ) => updateFile( activeTabIndex, 'content', val ) }
                        placeholder={ __( 'Paste your code here...', 'triskelion-toolkit' ) }
                        spellCheck={ false }
                    />
                </div>
            </div>
        </div>
    );
}