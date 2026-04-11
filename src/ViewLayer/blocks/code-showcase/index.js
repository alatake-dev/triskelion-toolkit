import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

// Importamos los CSS para que el compilador los detecte
import './index.css';
import './style-index.css';

registerBlockType( metadata.name, {
    edit: () => {
        return (
            <div className="tsk-editor-wrapper">
                <h3>Code Showcase (Editor)</h3>
                <p>Configurando el entorno de Triskelion...</p>
            </div>
        );
    },
    save: () => {
        return (
            <div className="tsk-frontend-wrapper">
                <p>Aquí irá el código resaltado.</p>
            </div>
        );
    },
} );