import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import './style.scss';
import metadata from './block.json';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( metadata.name, {
	attributes: {
		content: {
			type: 'string',
			source: 'html',
			selector: 'p',
		},
	},
	edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();

		return (
			<RichText
				{ ...blockProps }
				tagName="p"
				value={ attributes.content }
				allowedFormats={ [ 'core/bold', 'core/italic' ] }
				onChange={ ( content ) => setAttributes( { content } ) }
				placeholder={ __( 'Callout...' ) }
			/>
		);
	},

	save( { attributes } ) {
		const blockProps = useBlockProps.save();

		return <RichText.Content { ...blockProps } tagName="p" value={ attributes.content } />;
	}
} );
