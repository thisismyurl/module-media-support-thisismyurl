/**
 * Design Hub JavaScript
 *
 * Handles Design Hub functionality for multiple design app integrations.
 */

(function($) {
	'use strict';

	const DesignHub = {
		/**
		 * Initialize
		 */
		init: function() {
			this.setupEventHandlers();
		},

		/**
		 * Setup event handlers
		 */
		setupEventHandlers: function() {
			const self = this;

			// Launch connector
			$(document).on('click', '.timu-connector-launch', function(e) {
				e.preventDefault();
				const connector = $(this).data('connector');
				self.launchConnector(connector);
			});

			// Edit design
			$(document).on('click', '.timu-edit-design', function(e) {
				e.preventDefault();
				const attachmentId = $(this).data('attachment-id');
				self.editDesign(attachmentId);
			});
		},

		/**
		 * Launch connector
		 */
		launchConnector: function(connector) {
			switch(connector) {
				case 'canva':
					this.launchCanva();
					break;
				case 'crello':
					this.launchCrello();
					break;
				case 'adobe_express':
					this.launchAdobeExpress();
					break;
				case 'figma':
					this.launchFigma();
					break;
				default:
					alert('Connector not implemented: ' + connector);
			}
		},

		/**
		 * Launch Canva
		 */
		launchCanva: function() {
			// Check if connected
			if (typeof timuCanva !== 'undefined' && !timuCanva.isConnected) {
				if (confirm('You need to connect your Canva account first. Go to settings?')) {
					window.location.href = timuCanva.connectUrl;
				}
				return;
			}

			// Create new design
			$.ajax({
				url: timuDesignHub.ajaxUrl,
				type: 'POST',
				data: {
					action: 'timu_canva_create',
					nonce: timuDesignHub.nonce
				},
				success: function(response) {
					if (response.success) {
						const editUrl = 'https://www.canva.com/design/' + response.data.id + '/edit';
						window.open(editUrl, '_blank', 'width=1200,height=800');
					} else {
						alert('Error: ' + response.data.message);
					}
				},
				error: function() {
					alert('Failed to create design. Please try again.');
				}
			});
		},

		/**
		 * Launch Crello
		 */
		launchCrello: function() {
			// Open Crello in new window
			window.open('https://create.vista.com/', '_blank', 'width=1200,height=800');
		},

		/**
		 * Launch Adobe Express
		 */
		launchAdobeExpress: function() {
			// Open Adobe Express in new window
			window.open('https://www.adobe.com/express/', '_blank', 'width=1200,height=800');
		},

		/**
		 * Launch Figma
		 */
		launchFigma: function() {
			// Open Figma in new window
			window.open('https://www.figma.com/', '_blank', 'width=1200,height=800');
		},

		/**
		 * Edit design
		 */
		editDesign: function(attachmentId) {
			// Try to edit in Canva first
			if (typeof timuCanva !== 'undefined') {
				$.ajax({
					url: timuDesignHub.ajaxUrl,
					type: 'POST',
					data: {
						action: 'timu_canva_edit',
						nonce: timuDesignHub.nonce,
						attachment_id: attachmentId
					},
					success: function(response) {
						if (response.success && response.data.edit_url) {
							window.open(response.data.edit_url, '_blank', 'width=1200,height=800');
						} else {
							alert('This design cannot be edited in the design hub.');
						}
					}
				});
			}
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		DesignHub.init();
	});

})(jQuery);
