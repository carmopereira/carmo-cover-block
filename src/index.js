import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Add ACF background attribute to core/cover block.
 */
addFilter(
	'blocks.registerBlockType',
	'carmo-cover-block/acf-attributes',
	( settings, name ) => {
		if ( name !== 'core/cover' ) {
			return settings;
		}

		return {
			...settings,
			attributes: {
				...settings.attributes,
				acfDesktopFieldKey: {
					type: 'string',
					default: '',
				},
			},
		};
	}
);

/**
 * Add ACF Background Image panel to core/cover InspectorControls.
 */
const withACFControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( props.name !== 'core/cover' ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes } = props;

		const desktopImageUrl = useSelect(
			( select ) => {
				if ( ! attributes.acfDesktopFieldKey ) {
					return '';
				}

				const { getCurrentPostId, getCurrentPostType } =
					select( 'core/editor' );
				const postId = getCurrentPostId();
				const postType = getCurrentPostType();

				if ( ! postId || ! postType ) {
					return '';
				}

				const post = select( 'core' ).getEntityRecord(
					'postType',
					postType,
					postId
				);

				if ( ! post?.acf?.[ attributes.acfDesktopFieldKey ] ) {
					return '';
				}

				const acfValue =
					post.acf[ attributes.acfDesktopFieldKey ];

				if ( typeof acfValue === 'object' && acfValue?.url ) {
					return acfValue.url;
				}
				if ( typeof acfValue === 'string' ) {
					return acfValue;
				}

				return '';
			},
			[ attributes.acfDesktopFieldKey ]
		);

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __(
							'ACF Background Image',
							'carmo-cover-block'
						) }
						initialOpen={ false }
					>
						<TextControl
							label={ __(
								'ACF Field Key',
								'carmo-cover-block'
							) }
							value={ attributes.acfDesktopFieldKey }
							onChange={ ( value ) =>
								setAttributes( {
									acfDesktopFieldKey: value,
								} )
							}
							help={ __(
								'Enter the ACF field key for background image (e.g., field_63a1b2c3d4e5f).',
								'carmo-cover-block'
							) }
						/>
						{ desktopImageUrl && (
							<p style={ { fontStyle: 'italic' } }>
								{ __(
									'ACF image detected.',
									'carmo-cover-block'
								) }
							</p>
						) }
					</PanelBody>
				</InspectorControls>
			</>
		);
	};
}, 'withACFControls' );

addFilter(
	'editor.BlockEdit',
	'carmo-cover-block/acf-controls',
	withACFControls
);
