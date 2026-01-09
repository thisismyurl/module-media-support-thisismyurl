/**
 * Media Library Enhancement Script
 * Adds social media image features to WordPress Media Library
 */
(function($) {
    'use strict';

    /**
     * Initialize the Media Library enhancements
     */
    var TimuMediaEnhancer = {
        
        init: function() {
            this.bindEvents();
            this.extendMediaModal();
        },

        /**
         * Bind event listeners
         */
        bindEvents: function() {
            $(document).on('click', '.timu-apply-filter', this.applyFilter.bind(this));
            $(document).on('click', '.timu-crop-preset', this.cropToPreset.bind(this));
            $(document).on('click', '.timu-generate-hashtags', this.generateHashtags.bind(this));
            $(document).on('click', '.timu-generate-preview', this.generatePreview.bind(this));
            $(document).on('click', '.timu-apply-template', this.applyTemplate.bind(this));
        },

        /**
         * Extend WordPress Media Modal
         */
        extendMediaModal: function() {
            if (typeof wp !== 'undefined' && wp.media) {
                // Add custom sidebar for image enhancements
                var originalAttachmentDisplay = wp.media.view.Attachment.Details.TwoColumn;
                
                wp.media.view.Attachment.Details.TwoColumn = originalAttachmentDisplay.extend({
                    template: function(view) {
                        var output = originalAttachmentDisplay.prototype.template.call(this, view);
                        
                        // Only add enhancements for images
                        if (view.model && view.model.get('type') === 'image') {
                            output += TimuMediaEnhancer.renderEnhancementsPanel(view.model);
                        }
                        
                        return output;
                    }
                });
            }
        },

        /**
         * Render enhancements panel HTML
         */
        renderEnhancementsPanel: function(model) {
            var attachmentId = model.get('id');
            
            return `
                <div class="timu-media-enhancements">
                    <h3>${timuMediaData.i18n.socialEnhancements}</h3>
                    
                    <div class="timu-section">
                        <h4>${timuMediaData.i18n.filters}</h4>
                        <div class="timu-filters-grid">
                            ${this.renderFilterButtons()}
                        </div>
                    </div>
                    
                    <div class="timu-section">
                        <h4>${timuMediaData.i18n.cropPresets}</h4>
                        <div class="timu-crop-presets">
                            ${this.renderCropPresets()}
                        </div>
                    </div>
                    
                    <div class="timu-section">
                        <h4>${timuMediaData.i18n.hashtags}</h4>
                        <button class="button timu-generate-hashtags" data-attachment-id="${attachmentId}">
                            ${timuMediaData.i18n.generateHashtags}
                        </button>
                        <div id="timu-hashtags-output-${attachmentId}" class="timu-output"></div>
                    </div>
                    
                    <div class="timu-section">
                        <h4>${timuMediaData.i18n.socialPreview}</h4>
                        <button class="button timu-generate-preview" data-attachment-id="${attachmentId}">
                            ${timuMediaData.i18n.generatePreview}
                        </button>
                        <div id="timu-preview-output-${attachmentId}" class="timu-output"></div>
                    </div>
                    
                    <div class="timu-section">
                        <h4>${timuMediaData.i18n.templates}</h4>
                        <select id="timu-template-select-${attachmentId}" class="timu-template-select">
                            <option value="">${timuMediaData.i18n.selectTemplate}</option>
                            ${this.renderTemplateOptions()}
                        </select>
                        <button class="button timu-apply-template" data-attachment-id="${attachmentId}">
                            ${timuMediaData.i18n.applyTemplate}
                        </button>
                    </div>
                </div>
            `;
        },

        /**
         * Render filter buttons
         */
        renderFilterButtons: function() {
            var filters = timuMediaData.filters || {};
            var html = '';
            
            for (var key in filters) {
                if (filters.hasOwnProperty(key)) {
                    html += `<button class="button timu-apply-filter" data-filter="${key}">${filters[key].name}</button>`;
                }
            }
            
            return html;
        },

        /**
         * Render crop preset buttons
         */
        renderCropPresets: function() {
            var presets = timuMediaData.cropPresets || {};
            var html = '';
            
            for (var key in presets) {
                if (presets.hasOwnProperty(key)) {
                    html += `<button class="button timu-crop-preset" data-preset="${key}">${presets[key].name}</button>`;
                }
            }
            
            return html;
        },

        /**
         * Render template options
         */
        renderTemplateOptions: function() {
            var templates = timuMediaData.templates || {};
            var html = '';
            
            for (var key in templates) {
                if (templates.hasOwnProperty(key)) {
                    html += `<option value="${key}">${templates[key].name}</option>`;
                }
            }
            
            return html;
        },

        /**
         * Apply filter to image
         */
        applyFilter: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var filter = $button.data('filter');
            var attachmentId = this.getCurrentAttachmentId();
            
            if (!attachmentId) {
                alert(timuMediaData.i18n.noImageSelected);
                return;
            }
            
            $button.prop('disabled', true).text(timuMediaData.i18n.processing);
            
            $.ajax({
                url: timuMediaData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'timu_apply_filter',
                    nonce: timuMediaData.nonce,
                    attachment_id: attachmentId,
                    filter: filter
                },
                success: function(response) {
                    if (response.success) {
                        alert(timuMediaData.i18n.filterApplied + ' ' + response.data.url);
                        location.reload();
                    } else {
                        alert(timuMediaData.i18n.error + ' ' + response.data.message);
                    }
                },
                error: function() {
                    alert(timuMediaData.i18n.error);
                },
                complete: function() {
                    $button.prop('disabled', false).text($button.data('original-text'));
                }
            });
        },

        /**
         * Crop image to preset
         */
        cropToPreset: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var preset = $button.data('preset');
            var attachmentId = this.getCurrentAttachmentId();
            
            if (!attachmentId) {
                alert(timuMediaData.i18n.noImageSelected);
                return;
            }
            
            $button.prop('disabled', true).text(timuMediaData.i18n.processing);
            
            $.ajax({
                url: timuMediaData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'timu_crop_image',
                    nonce: timuMediaData.nonce,
                    attachment_id: attachmentId,
                    preset: preset
                },
                success: function(response) {
                    if (response.success) {
                        alert(timuMediaData.i18n.cropApplied + ' ' + response.data.url);
                        location.reload();
                    } else {
                        alert(timuMediaData.i18n.error + ' ' + response.data.message);
                    }
                },
                error: function() {
                    alert(timuMediaData.i18n.error);
                },
                complete: function() {
                    $button.prop('disabled', false).text($button.data('original-text'));
                }
            });
        },

        /**
         * Generate hashtags
         */
        generateHashtags: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var attachmentId = $button.data('attachment-id');
            var $output = $('#timu-hashtags-output-' + attachmentId);
            
            $button.prop('disabled', true).text(timuMediaData.i18n.processing);
            
            $.ajax({
                url: timuMediaData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'timu_generate_hashtags',
                    nonce: timuMediaData.nonce,
                    attachment_id: attachmentId,
                    category: 'general',
                    count: 10
                },
                success: function(response) {
                    if (response.success) {
                        var hashtags = response.data.hashtags.join(' ');
                        $output.html('<textarea readonly style="width:100%;height:60px;">' + hashtags + '</textarea>');
                    } else {
                        $output.html('<p class="error">' + response.data.message + '</p>');
                    }
                },
                error: function() {
                    $output.html('<p class="error">' + timuMediaData.i18n.error + '</p>');
                },
                complete: function() {
                    $button.prop('disabled', false).text(timuMediaData.i18n.generateHashtags);
                }
            });
        },

        /**
         * Generate social preview
         */
        generatePreview: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var attachmentId = $button.data('attachment-id');
            var $output = $('#timu-preview-output-' + attachmentId);
            
            $button.prop('disabled', true).text(timuMediaData.i18n.processing);
            
            $.ajax({
                url: timuMediaData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'timu_generate_preview',
                    nonce: timuMediaData.nonce,
                    attachment_id: attachmentId
                },
                success: function(response) {
                    if (response.success) {
                        var html = '<div class="timu-preview-results">';
                        for (var platform in response.data) {
                            if (response.data.hasOwnProperty(platform)) {
                                var preview = response.data[platform];
                                html += '<div class="timu-preview-item">';
                                html += '<strong>' + preview.platform_name + '</strong>: ';
                                html += preview.target_width + 'x' + preview.target_height;
                                if (preview.crop_required) {
                                    html += ' <span class="dashicons dashicons-warning"></span> ' + timuMediaData.i18n.cropRequired;
                                } else {
                                    html += ' <span class="dashicons dashicons-yes"></span> ' + timuMediaData.i18n.perfectFit;
                                }
                                html += '</div>';
                            }
                        }
                        html += '</div>';
                        $output.html(html);
                    } else {
                        $output.html('<p class="error">' + response.data.message + '</p>');
                    }
                },
                error: function() {
                    $output.html('<p class="error">' + timuMediaData.i18n.error + '</p>');
                },
                complete: function() {
                    $button.prop('disabled', false).text(timuMediaData.i18n.generatePreview);
                }
            });
        },

        /**
         * Apply template
         */
        applyTemplate: function(e) {
            e.preventDefault();
            
            var $button = $(e.currentTarget);
            var attachmentId = $button.data('attachment-id');
            var templateId = $('#timu-template-select-' + attachmentId).val();
            
            if (!templateId) {
                alert(timuMediaData.i18n.selectTemplate);
                return;
            }
            
            $button.prop('disabled', true).text(timuMediaData.i18n.processing);
            
            $.ajax({
                url: timuMediaData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'timu_apply_template',
                    nonce: timuMediaData.nonce,
                    attachment_id: attachmentId,
                    template_id: templateId
                },
                success: function(response) {
                    if (response.success) {
                        alert(timuMediaData.i18n.templateApplied + ' ' + response.data.url);
                        location.reload();
                    } else {
                        alert(timuMediaData.i18n.error + ' ' + response.data.message);
                    }
                },
                error: function() {
                    alert(timuMediaData.i18n.error);
                },
                complete: function() {
                    $button.prop('disabled', false).text(timuMediaData.i18n.applyTemplate);
                }
            });
        },

        /**
         * Get current attachment ID from media modal
         */
        getCurrentAttachmentId: function() {
            if (wp.media && wp.media.frame && wp.media.frame.state()) {
                var selection = wp.media.frame.state().get('selection');
                if (selection) {
                    var selected = selection.first();
                    if (selected) {
                        return selected.get('id');
                    }
                }
            }
            return null;
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        TimuMediaEnhancer.init();
    });

})(jQuery);
