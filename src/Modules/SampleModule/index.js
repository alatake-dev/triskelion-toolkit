import { registerBlockType } from '@wordpress/blocks';
import './editor.scss'; // Estilos para el editor
import './style.scss';  // Estilos generales (editor y front)
import Edit from './edit';
import save from './save';
import metadata from './block.json';
/**
 * TRISKELION SAMPLE MODULE
 * * Este bloque es un "Blueprint" educativo.
 * * AVISO DE VALIDACIÓN:
 * Al usar inyección de atributos desde PHP (Server-side defaults), si cambias el
 * "Default Tag" en el Admin del Toolkit, los bloques ya insertados mostrarán
 * un error de validación. Esto sucede porque el HTML guardado en el post
 * no coincidirá con el nuevo default del servidor.
 * * SOLUCIÓN PRO: Para evitar esto en producción, usa bloques dinámicos (render_callback).
 */
registerBlockType( metadata.name, {
    /**
     * @see ./edit.js
     */
    edit: Edit,
    /**
     * @see ./save.js
     */
    save,
} );