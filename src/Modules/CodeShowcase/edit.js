import {__} from '@wordpress/i18n';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {Button, PanelBody, SelectControl, TextareaControl, TextControl} from '@wordpress/components';

export default function Edit({attributes, setAttributes}) {
    const {files = [], activeTabIndex = 0} = attributes || {};
    const blockProps = useBlockProps();


    const languageOptions = (window.triskelionToolkitSettings?.activeLanguages || ['javascript', 'php']).map(lang => ({
        // Transformamos el slug (ej. 'javascript') en una etiqueta legible (ej. 'JAVASCRIPT')
        label: lang.toUpperCase(),
        value: lang
    }));

    // --- LÓGICA DE GESTIÓN DE ARCHIVOS (INTACTA) ---

    const updateFile = (index, key, value) => {
        const newFiles = [...files];
        newFiles[index] = {...newFiles[index], [key]: value};
        setAttributes({files: newFiles});
    };

    const addFile = () => {
        // Al añadir, tomamos por defecto el primer lenguaje del inventario configurado
        const defaultLang = languageOptions[0]?.value || 'javascript';

        const newFiles = [...files, {
            fileName: __('new-file.js', 'triskelion-toolkit'),
            language: defaultLang,
            content: ''
        }];
        setAttributes({files: newFiles});
        setAttributes({activeTabIndex: newFiles.length - 1});
    };

    const removeFile = (index) => {
        if (files.length <= 1) return;
        const newFiles = files.filter((_, i) => i !== index);
        setAttributes({files: newFiles, activeTabIndex: 0});
    };

    const moveFile = (currentIndex, direction) => {
        const newIndex = currentIndex + direction;
        if (newIndex < 0 || newIndex >= files.length) return;

        const newFiles = [...files];
        [newFiles[currentIndex], newFiles[newIndex]] = [newFiles[newIndex], newFiles[currentIndex]];

        setAttributes({
            files: newFiles,
            activeTabIndex: newIndex
        });
    };

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('Current File Settings', 'triskelion-toolkit')}>
                    <TextControl
                        label={__('File Name', 'triskelion-toolkit')}
                        value={files[activeTabIndex]?.fileName}
                        onChange={(val) => updateFile(activeTabIndex, 'fileName', val)}
                    />

                    {/* El SelectControl ahora es dinámico según tu Admin */}
                    <SelectControl
                        label={__('Language', 'triskelion-toolkit')}
                        value={files[activeTabIndex]?.language}
                        options={languageOptions}
                        onChange={(val) => updateFile(activeTabIndex, 'language', val)}
                    />

                    <div style={{marginTop: '15px', display: 'flex', justifyContent: 'space-between'}}>
                        <Button isDestructive variant="link" onClick={() => removeFile(activeTabIndex)}
                                disabled={files.length <= 1}>
                            {__('Delete File', 'triskelion-toolkit')}
                        </Button>

                        <div style={{display: 'flex', gap: '5px'}}>
                            <Button
                                variant="secondary"
                                isSmall
                                icon="arrow-left-alt"
                                onClick={() => moveFile(activeTabIndex, -1)}
                                disabled={activeTabIndex === 0}
                            />
                            <Button
                                variant="secondary"
                                isSmall
                                icon="arrow-right-alt"
                                onClick={() => moveFile(activeTabIndex, 1)}
                                disabled={activeTabIndex === files.length - 1}
                            />
                        </div>
                    </div>
                </PanelBody>
            </InspectorControls>

            {/* --- PREVIEW DEL EDITOR (UX MACOS) --- */}
            <div className="triskelion-toolkit-code-showcase-preview">
                <div className="triskelion-toolkit-window-header">
                    <div className="triskelion-toolkit-dots">
                        <span className="dot red"></span>
                        <span className="dot yellow"></span>
                        <span className="dot green"></span>
                    </div>
                    <div className="triskelion-toolkit-tabs">
                        {files.map((file, index) => (
                            <button
                                key={index}
                                className={`triskelion-toolkit-tab ${activeTabIndex === index ? 'is-active' : ''}`}
                                onClick={() => setAttributes({activeTabIndex: index})}
                            >
                                {file.fileName || __('unnamed', 'triskelion-toolkit')}
                            </button>
                        ))}
                        <button className="triskelion-toolkit-add-tab" onClick={addFile}>+</button>
                    </div>
                </div>
                <div className="triskelion-toolkit-window-content">
                    <TextareaControl
                        value={files[activeTabIndex]?.content}
                        onChange={(val) => updateFile(activeTabIndex, 'content', val)}
                        placeholder={__('Paste code here...', 'triskelion-toolkit')}
                        spellCheck={false}
                    />
                </div>
            </div>
        </div>
    );
}