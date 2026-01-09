/**
 * Canva Integration JavaScript
 *
 * Handles Canva integration in WordPress Media Library.
 */

(function($) {
	'use strict';

	const CanvaIntegration = {
		/**
		 * Initialize
		 */
		init: function() {
			this.addMediaLibraryButton();
			this.setupEventHandlers();
			this.addTemplateModal();
		},

		/**
		 * Add "Create in Canva" button to Media Library
		 */
		addMediaLibraryButton: function() {
			if (typeof wp !== 'undefined' && wp.media) {
				// Add to media library toolbar
				const MediaLibraryView = wp.media.view.AttachmentsBrowser;
				wp.media.view.AttachmentsBrowser = MediaLibraryView.extend({
					createToolbar: function() {
						MediaLibraryView.prototype.createToolbar.apply(this, arguments);
						
						this.toolbar.set('canva-buttons', new wp.media.View({
							className: 'media-toolbar-canva',
							template: function() {
								return '<div class="media-toolbar-canva-buttons">' +
									'<button type="button" class="button button-primary button-large timu-canva-create">' +
									timuCanva.createLabel +
									'</button>' +
									'<button type="button" class="button button-secondary button-large timu-canva-templates">' +
									timuCanva.templatesLabel +
									'</button>' +
									'<button type="button" class="button button-secondary button-large timu-canva-sync">' +
									timuCanva.syncLabel +
									'</button>' +
									'</div>';
							}
						}));
					}
				});
			}

			// Also add to upload.php page
			if ($('.wrap h1').first().length && $('.page-title-action').length === 0) {
				$('.wrap h1').first().after(
					'<a href="#" class="page-title-action timu-canva-create-top">' + timuCanva.createLabel + '</a>' +
					'<a href="#" class="page-title-action timu-canva-templates-top">' + timuCanva.templatesLabel + '</a>'
				);
			}
		},

		/**
		 * Setup event handlers
		 */
		setupEventHandlers: function() {
			const self = this;

			// Create in Canva
			$(document).on('click', '.timu-canva-create, .timu-canva-create-top', function(e) {
				e.preventDefault();
				self.handleCreateDesign();
			});

			// Browse templates
			$(document).on('click', '.timu-canva-templates, .timu-canva-templates-top', function(e) {
				e.preventDefault();
				self.handleBrowseTemplates();
			});

			// Sync designs
			$(document).on('click', '.timu-canva-sync', function(e) {
				e.preventDefault();
				self.handleSyncDesigns();
			});

			// Edit in Canva
			$(document).on('click', '.timu-edit-design', function(e) {
				e.preventDefault();
				const attachmentId = $(this).data('attachment-id');
				self.handleEditDesign(attachmentId);
			});

			// Template selection
			$(document).on('click', '.timu-template-item', function(e) {
				e.preventDefault();
				const templateId = $(this).data('template-id');
				self.handleTemplateSelect(templateId);
			});

			// Category filter
			$(document).on('click', '.timu-template-category', function(e) {
				e.preventDefault();
				const category = $(this).data('category');
				self.filterTemplates(category);
				$('.timu-template-category').removeClass('active');
				$(this).addClass('active');
			});
		},

		/**
		 * Handle create design
		 */
		handleCreateDesign: function() {
			if (!timuCanva.isConnected) {
				this.showConnectPrompt();
				return;
			}

			const designType = 'Document'; // Default type

			$.ajax({
				url: timuCanva.ajaxUrl,
				type: 'POST',
				data: {
					action: 'timu_canva_create',
					nonce: timuCanva.nonce,
					design_type: designType
				},
				beforeSend: function() {
					$('.timu-canva-create, .timu-canva-create-top').prop('disabled', true).text('Creating...');
				},
				success: function(response) {
					if (response.success) {
						// Open Canva editor in new window
						const editUrl = 'https://www.canva.com/design/' + response.data.id + '/edit';
						window.open(editUrl, '_blank', 'width=1200,height=800');
						
						// Show import prompt after 5 seconds
						setTimeout(function() {
							if (confirm('Would you like to import your design to WordPress Media Library?')) {
								CanvaIntegration.importDesign(response.data.id);
							}
						}, 5000);
					} else {
						alert('Error: ' + response.data.message);
					}
				},
				error: function() {
					alert('Failed to create design. Please try again.');
				},
				complete: function() {
					$('.timu-canva-create, .timu-canva-create-top').prop('disabled', false).text(timuCanva.createLabel);
				}
			});
		},

		/**
		 * Handle browse templates
		 */
		handleBrowseTemplates: function() {
			if (!timuCanva.isConnected) {
				this.showConnectPrompt();
				return;
			}

			$('#timu-template-modal').show();
			this.loadTemplates();
		},

		/**
		 * Load templates
		 */
		loadTemplates: function(category) {
			const self = this;

			$.ajax({
				url: timuCanva.ajaxUrl,
				type: 'POST',
				data: {
					action: 'timu_canva_get_templates',
					nonce: timuCanva.nonce,
					category: category || ''
				},
				beforeSend: function() {
					$('#timu-template-list').html('<p>Loading templates...</p>');
				},
				success: function(response) {
					if (response.success) {
						self.renderTemplates(response.data);
					} else {
						$('#timu-template-list').html('<p>Error loading templates.</p>');
					}
				}
			});
		},

		/**
		 * Render templates
		 */
		renderTemplates: function(templates) {
			let html = '<div class="timu-template-grid">';
			
			// Handle nested category structure
			if (typeof templates === 'object' && !Array.isArray(templates)) {
				// Flatten all templates from all categories
				templates = Object.values(templates).flat();
			}

			if (templates.length === 0) {
				html = '<p>No templates available.</p>';
			} else {
				templates.forEach(function(template) {
					html += '<div class="timu-template-item" data-template-id="' + template.id + '">';
					html += '<img src="' + template.thumb + '" alt="' + template.name + '">';
					html += '<h4>' + template.name + '</h4>';
					html += '</div>';
				});
				html += '</div>';
			}

			$('#timu-template-list').html(html);
		},

		/**
		 * Filter templates by category
		 */
		filterTemplates: function(category) {
			this.loadTemplates(category);
		},

		/**
		 * Handle template selection
		 */
		handleTemplateSelect: function(templateId) {
			alert('Opening template in Canva: ' + templateId);
			// Open template in Canva
			window.open('https://www.canva.com/templates/' + templateId, '_blank');
		},

		/**
		 * Handle sync designs
		 */
		handleSyncDesigns: function() {
			if (!timuCanva.isConnected) {
				this.showConnectPrompt();
				return;
			}

			$.ajax({
				url: timuCanva.ajaxUrl,
				type: 'POST',
				data: {
					action: 'timu_canva_sync',
					nonce: timuCanva.nonce
				},
				beforeSend: function() {
					$('.timu-canva-sync').prop('disabled', true).text('Syncing...');
				},
				success: function(response) {
					if (response.success) {
						alert('Sync complete! ' + response.data.synced + ' designs synced.');
						// Reload media library
						if (typeof wp !== 'undefined' && wp.media && wp.media.frame) {
							wp.media.frame.content.get().collection.props.set({ignore: (+ new Date())});
						}
					} else {
						alert('Error: ' + response.data.message);
					}
				},
				error: function() {
					alert('Failed to sync designs. Please try again.');
				},
				complete: function() {
					$('.timu-canva-sync').prop('disabled', false).text(timuCanva.syncLabel);
				}
			});
		},

		/**
		 * Handle edit design
		 */
		handleEditDesign: function(attachmentId) {
			$.ajax({
				url: timuCanva.ajaxUrl,
				type: 'POST',
				data: {
					action: 'timu_canva_edit',
					nonce: timuCanva.nonce,
					attachment_id: attachmentId
				},
				success: function(response) {
					if (response.success && response.data.edit_url) {
						window.open(response.data.edit_url, '_blank', 'width=1200,height=800');
					} else {
						alert('Error: ' + (response.data.message || 'Failed to get edit URL'));
					}
				}
			});
		},

		/**
		 * Import design
		 */
		importDesign: function(designId) {
			$.ajax({
				url: timuCanva.ajaxUrl,
				type: 'POST',
				data: {
					action: 'timu_canva_import',
					nonce: timuCanva.nonce,
					design_id: designId
				},
				beforeSend: function() {
					// Show loading indicator
				},
				success: function(response) {
					if (response.success) {
						alert('Design imported successfully!');
						// Reload media library
						if (typeof wp !== 'undefined' && wp.media && wp.media.frame) {
							wp.media.frame.content.get().collection.props.set({ignore: (+ new Date())});
						}
					} else {
						alert('Error: ' + response.data.message);
					}
				}
			});
		},

		/**
		 * Show connect prompt
		 */
		showConnectPrompt: function() {
			if (confirm('You need to connect your Canva account first. Go to settings?')) {
				window.location.href = timuCanva.connectUrl;
			}
		},

		/**
		 * Add template modal
		 */
		addTemplateModal: function() {
			if ($('#timu-template-modal').length === 0) {
				const modal = '<div id="timu-template-modal" class="timu-modal" style="display:none;">' +
					'<div class="timu-modal-content">' +
					'<span class="timu-modal-close">&times;</span>' +
					'<h2>Browse Canva Templates</h2>' +
					'<div class="timu-template-categories">' +
					'<button class="button timu-template-category active" data-category="">All</button>' +
					'<button class="button timu-template-category" data-category="social_posts">Social Posts</button>' +
					'<button class="button timu-template-category" data-category="blog_graphics">Blog Graphics</button>' +
					'<button class="button timu-template-category" data-category="infographics">Infographics</button>' +
					'</div>' +
					'<div id="timu-template-list"></div>' +
					'</div>' +
					'</div>';
				
				$('body').append(modal);

				// Close modal
				$(document).on('click', '.timu-modal-close, #timu-template-modal', function(e) {
					if (e.target === this) {
						$('#timu-template-modal').hide();
					}
				});
			}
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		CanvaIntegration.init();
	});

})(jQuery);
