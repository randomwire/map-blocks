( function ( blocks, element, blockEditor, i18n ) {
	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'map-blocks/archive-map', {
		edit: function () {
			var blockProps = blockEditor.useBlockProps( {
				className: 'map-blocks-placeholder',
				style: {
					padding: '1em',
					border: '1px dashed #ccc',
					textAlign: 'center',
					background: '#f6f7f7',
				},
			} );
			return el(
				'div',
				blockProps,
				el( 'strong', null, __( 'Archive Map', 'map-blocks' ) ),
				el( 'div', null, __( 'A clustered map of all posts with location data will render on the frontend.', 'map-blocks' ) )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.i18n );
