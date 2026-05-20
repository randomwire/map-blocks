( function ( blocks, element, blockEditor, i18n ) {
	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'map-blocks/cat-map', {
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
				el( 'strong', null, __( 'Category Map', 'map-blocks' ) ),
				el( 'div', null, __( 'Pins for posts in the current category will render on the frontend.', 'map-blocks' ) )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.i18n );
