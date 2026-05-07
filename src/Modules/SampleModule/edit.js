import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
    const { title, titleTag, accentColor } = attributes;

    /**
     * @core-concept: Hybrid Power
     * Este bloque demuestra cómo heredar configuraciones del Toolkit Admin
     * (accentColor/titleTag) mientras permitimos al usuario final modificar
     * la jerarquía (Hx) localmente.
     */
    const blockProps = useBlockProps({
        style: {
            borderLeft: `5px solid ${accentColor}`,
            paddingLeft: '20px'
        }
    });

    return (
        <div { ...blockProps }>
            {/* Barra de herramientas para cambiar el nivel del Hx */}
            <BlockControls>
                <ToolbarGroup>
                    {['h2', 'h3', 'h4', 'h5', 'h6'].map( ( tag ) => (
                        <ToolbarButton
                            key={ tag }
                            isPressed={ titleTag === tag }
                            onClick={ () => setAttributes( { titleTag: tag } ) }
                        >
                            { tag.toUpperCase() }
                        </ToolbarButton>
                    ) )}
                </ToolbarGroup>
            </BlockControls>

            <RichText
                tagName={ titleTag }
                value={ title }
                style={ { color: accentColor, margin: 0 } }
                onChange={ ( val ) => setAttributes( { title: val } ) }
                placeholder={ __( 'Insight title...', 'triskelion-toolkit' ) }
            />
        </div>
    );
}