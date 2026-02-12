import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Add ACF background attributes to core/cover block.
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
				acfMobileFieldKey: {
					type: 'string',
					default: '',
				},
			},
		};
	}
);

/**
 * Add ACF Background Images panel to core/cover InspectorControls.
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
							'ACF Background Images',
							'carmo-cover-block'
						) }
						initialOpen={ false }
					>
						<TextControl
							label={ __(
								'Desktop ACF Field Key',
								'carmo-cover-block'
							) }
							value={ attributes.acfDesktopFieldKey }
							onChange={ ( value ) =>
								setAttributes( {
									acfDesktopFieldKey: value,
								} )
							}
							help={ __(
								'Enter the ACF field key for desktop background (e.g., field_63a1b2c3d4e5f).',
								'carmo-cover-block'
							) }
						/>
						<TextControl
							label={ __(
								'Mobile ACF Field Key',
								'carmo-cover-block'
							) }
							value={ attributes.acfMobileFieldKey }
							onChange={ ( value ) =>
								setAttributes( {
									acfMobileFieldKey: value,
								} )
							}
							help={ __(
								'Optional. ACF field key for mobile background. If empty, desktop image is used on all devices.',
								'carmo-cover-block'
							) }
						/>
						{ desktopImageUrl && (
							<p style={ { fontStyle: 'italic' } }>
								{ __(
									'ACF desktop image detected.',
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
