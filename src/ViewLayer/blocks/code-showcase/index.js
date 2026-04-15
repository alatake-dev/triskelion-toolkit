import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';

// Importamos la lógica que definimos paso a paso
import Edit from './edit'; // Mueve la lógica que escribimos antes a un archivo edit.js


import './style.scss';
import './editor.scss';


registerBlockType( metadata.name, {
    /**
     * @see ./edit.js
     */
    edit: Edit,

    save: ( { attributes } ) => {
        // Si planeas usar un render_callback en PHP (recomendado para SSR),
        // el save debe retornar null.
        return null;
    },
} );