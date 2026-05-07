import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save( { attributes } ) {
    const { title, titleTag, accentColor } = attributes;

    const blockProps = useBlockProps.save({
        style: { borderLeft: `5px solid ${accentColor}`, paddingLeft: '20px' }
    });

    return (
        <div { ...blockProps }>
            <RichText.Content
                tagName={ titleTag }
                value={ title }
                style={ { color: accentColor } }
                className="sample-card-title"
            />
        </div>
    );
}