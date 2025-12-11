/**
 * Add-to-Some Share Buttons Block
 *
 * @package AddToSome
 * @since 1.1.0
 */

(function(blocks, element, blockEditor, components) {
	var el = element.createElement;
	var Fragment = element.Fragment;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var ExternalLink = components.ExternalLink;

	/**
	 * Register the Share Buttons block
	 */
	blocks.registerBlockType('add-to-some/share-buttons', {
		title: 'Add to Some - Share Buttons',
		description: 'Display social sharing buttons for this content.',
		icon: 'share',
		category: 'widgets',
		keywords: ['share', 'social', 'buttons'],
		supports: {
			multiple: true,
			html: false,
		},
		attributes: {
			isPreview: {
				type: 'boolean',
				default: false,
			},
		},
		example: {
			attributes: {
				isPreview: true,
			},
		},


		/**
		 * Edit function - renders the block in the editor
		 */
		edit: function(props) {
			// If in preview mode (inserter), show the static image
			if (props.attributes.isPreview) {
				return el('img', {
					src: addToSomeBlock.previewImageUrl,
					alt: 'Add to Some Share Buttons Preview',
					style: { width: '100%', height: 'auto' }
				});
			}

			return el(
				Fragment,
				{},
				// Sidebar controls
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{
							title: 'Settings',
							initialOpen: true,
						},
						el('p', {}, 'Configure which share buttons appear and their appearance in the plugin settings.'),
						el(
							ExternalLink,
							{
								href: addToSomeBlock.settingsUrl,
								className: 'add-to-some-settings-link',
							},
							'Open Share Button Settings'
						)
					)
				),
				// Block preview in editor
				el('div', {
					className: 'add-to-some-block-preview',
					dangerouslySetInnerHTML: { __html: addToSomeBlock.previewHtml }
				})
			);
		},

		/**
		 * Save function - returns null for server-side rendering
		 */
		save: function() {
			return null;
		}
	});

})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
