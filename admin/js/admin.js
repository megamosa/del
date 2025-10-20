/**
 * WhatsApp Chat Pro - Admin JavaScript
 * Professional WordPress Admin Interface
 * 
 * @package WhatsApp_Chat_Pro
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * WhatsApp Chat Admin
     */
    const WhatsAppChatAdmin = {
        isTracking: false,
        
    /**
     * Initialize
     */
        init: function() {
            this.bindEvents();
            this.initTabs();
            this.initModals();
            this.initDesignSelector();
            this.initLicenseValidation();
            this.initUpgradePrompts();
            this.initNotifications();
            this.initFormValidation();
            this.initWorkingHoursToggle();
            this.initDesignSelection();
            this.initAvatarUpload();
        },

        /**
         * Bind Events
         */
        bindEvents: function() {
            // Form submissions
            $(document).on('submit', '#whatsapp-settings-form', this.handleSettingsSubmit);
            $(document).on('submit', '#whatsapp-quick-setup-form', this.handleSettingsSubmit);
            $(document).on('submit', '#contact-form', this.handleContactSubmit);
            
            // Contact management
            $(document).on('click', '#add-contact-btn', this.showContactModal);
            $(document).on('click', '#whatsapp-fab', this.showContactModal);
            $(document).on('click', '.edit-contact', this.editContact);
            $(document).on('click', '.delete-contact', this.deleteContact);
            
            // Design selection
            $(document).on('click', '.select-design', this.selectDesign);
            
        // Contact form submission - Use click instead of submit
        $(document).on('click', '#add-contact-form .whatsapp-btn-primary', this.handleAddContact);
        
        // Debug form fields
        $(document).on('focus', '#add-contact-form input, #add-contact-form textarea', function() {
            // Field focused - tracking for analytics if needed
        });
        
        // Toggle switch click events
        $(document).on('click', '.whatsapp-toggle-switch', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const checkbox = $(this).find('input[type="checkbox"]');
            checkbox.prop('checked', !checkbox.prop('checked'));
            checkbox.trigger('change');
            // Toggle switch clicked
        });
        
        // Also handle direct checkbox changes
        $(document).on('change', '.whatsapp-toggle-switch input[type="checkbox"]', function() {
            // Checkbox changed
        });
            
            // Modal controls
            $(document).on('click', '.whatsapp-modal-close', this.closeModal);
            $(document).on('click', '.whatsapp-modal', this.closeModalOnBackdrop);
            
            // License actions
            $(document).on('click', '.license-activate', this.activateLicense);
            $(document).on('click', '.license-deactivate', this.deactivateLicense);
            
        // Upgrade prompts
        $(document).on('click', '.upgrade-btn', this.handleUpgrade);
        
        // Design selection
        $(document).on('click', '.select-design', this.handleDesignSelection);
        
        // Auto-save functionality
        $(document).on('change', '.auto-save', this.autoSave);
        
        // Avatar upload functionality
        $(document).on('click', '#upload-avatar-btn', this.handleAvatarUpload);
        $(document).on('change', '#avatar', this.handleAvatarFileSelect);
        $(document).on('click', '#remove-avatar', this.removeAvatar);
        
        // Modal avatar upload functionality
        $(document).on('click', '#modal-upload-avatar-btn', this.handleModalAvatarUpload);
        $(document).on('change', '#modal-avatar', this.handleModalAvatarFileSelect);
        $(document).on('click', '#modal-remove-avatar', this.removeModalAvatar);
        
        // Design 7 avatar upload functionality
        $(document).on('change', 'input[name="design7_avatar_file"]', this.handleDesign7AvatarUpload);
        },

        /**
         * Initialize Tabs
         */
        initTabs: function() {
            // Remove tab functionality since we're using separate pages
            $('.whatsapp-nav-tab').on('click', function(e) {
                // Let the link work normally - no preventDefault
                // Just update active state
                $('.whatsapp-nav-tab').removeClass('active');
                $(this).addClass('active');
            });
        },

        /**
         * Initialize Modals
         */
        initModals: function() {
            // Create modal container if it doesn't exist
            if (!$('#whatsapp-modal-container').length) {
                $('body').append('<div id="whatsapp-modal-container"></div>');
            }
        },

        /**
         * Initialize Design Selector
         */
        initDesignSelector: function() {
            $('.design-option').on('click', function() {
                const $option = $(this);
                const isPremium = $option.hasClass('premium');
                const licenseStatus = $('input[name="license_status"]').val() || 'inactive';
                
                if (isPremium && licenseStatus !== 'active') {
                    WhatsAppChatAdmin.showUpgradePrompt('design');
                    return false;
                }
                
                $('.design-option').removeClass('selected');
                $option.addClass('selected');
                $option.find('input[type="radio"]').prop('checked', true);
            });
        },

        /**
         * Initialize License Validation
         */
        initLicenseValidation: function() {
            $('.license-key-input').on('input', function() {
                const key = $(this).val();
                if (key.length >= 20) {
                    WhatsAppChatAdmin.validateLicenseKey(key);
                }
            });
        },

        /**
         * Initialize Upgrade Prompts
         */
        initUpgradePrompts: function() {
            // Check for premium features being used
            this.checkPremiumUsage();
        },

        /**
         * Initialize Notifications
         */
        initNotifications: function() {
            // Auto-hide notifications after 5 seconds
            setTimeout(function() {
                $('.whatsapp-notification').fadeOut();
            }, 5000);
        },

        /**
         * Initialize Real-time Form Validation
         */
        initFormValidation: function() {
            // Real-time validation for form fields
            $(document).on('blur', 'input[required], textarea[required], select[required]', function() {
                const $field = $(this);
                const $group = $field.closest('.whatsapp-form-group');
                const value = $field.val().trim();
                
                if (!value) {
                    $group.removeClass('success').addClass('error');
                } else {
                    $group.removeClass('error').addClass('success');
                }
            });
            
            // Clear validation states on focus
            $(document).on('focus', 'input[required], textarea[required], select[required]', function() {
                const $group = $(this).closest('.whatsapp-form-group');
                $group.removeClass('error success');
            });
        },

        /**
         * Initialize Working Hours Toggle
         */
        initWorkingHoursToggle: function() {
            // Initializing working hours toggle
            
            // Handle working hours control toggle - FIXED
            $(document).on('change', 'input[name="working_hours_enabled"]', function() {
                const isEnabled = $(this).is(':checked');
                // Working hours control toggled
                
                // Find all working hours related elements
                const $workingHoursGrid = $('.whatsapp-working-hours-grid');
                const $daySchedules = $('.whatsapp-day-schedule');
                const $dayToggles = $('input[name*="day_"][name*="_active"]');
                const $timeInputs = $('input[type="time"]');
                
                // Found working hours elements
                // Found time inputs
                
                if (isEnabled) {
                    $workingHoursGrid.removeClass('disabled-schedule');
                    $daySchedules.removeClass('disabled-schedule');
                    $dayToggles.prop('disabled', false);
                    $timeInputs.prop('disabled', false);
                    // Working hours options enabled
                } else {
                    $workingHoursGrid.addClass('disabled-schedule');
                    $daySchedules.addClass('disabled-schedule');
                    $dayToggles.prop('disabled', true);
                    $timeInputs.prop('disabled', true);
                    // console.log('Working hours options disabled');
                }
            });
            
            // Initialize on page load - FIXED
            setTimeout(function() {
                const $workingHoursEnabled = $('input[name="working_hours_enabled"]');
                // console.log('Found working hours enabled input:', $workingHoursEnabled.length);
                
                if ($workingHoursEnabled.length) {
                    const isEnabled = $workingHoursEnabled.is(':checked');
                    // console.log('Initial state - working hours enabled:', isEnabled);
                    
                    const $workingHoursGrid = $('.whatsapp-working-hours-grid');
                    const $daySchedules = $('.whatsapp-day-schedule');
                    const $dayToggles = $('input[name*="day_"][name*="_active"]');
                    const $timeInputs = $('input[type="time"]');
                    
                    if (isEnabled) {
                        $workingHoursGrid.removeClass('disabled-schedule');
                        $daySchedules.removeClass('disabled-schedule');
                        $dayToggles.prop('disabled', false);
                        $timeInputs.prop('disabled', false);
                        // console.log('Working hours options shown and enabled');
                    } else {
                        $workingHoursGrid.addClass('disabled-schedule');
                        $daySchedules.addClass('disabled-schedule');
                        $dayToggles.prop('disabled', true);
                        $timeInputs.prop('disabled', true);
                        // console.log('Working hours options hidden and disabled');
                    }
                }
            }, 500);
        },

        /**
         * Handle Settings Submit - Enhanced with validation and better UX
         */
        handleSettingsSubmit: function(e) {
            e.preventDefault();
            // console.log('WhatsApp Chat: Settings form submitted');
            
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.html();
            // console.log('WhatsApp Chat: Submit button found:', $submitBtn.length);
            
            // Validate required fields
            let isValid = true;
            $form.find('input[required], textarea[required], select[required]').each(function() {
                const $field = $(this);
                const value = $field.val().trim();
                
                if (!value) {
                    $field.closest('.whatsapp-form-group').addClass('error');
                    isValid = false;
                } else {
                    $field.closest('.whatsapp-form-group').removeClass('error').addClass('success');
                }
            });
            
            if (!isValid) {
                WhatsAppChatAdmin.showNotification('error', 'Please fill in all required fields');
                return;
            }
            
            // Show loading state
            $submitBtn.html('<span class="dashicons dashicons-update"></span> Saving...');
            $submitBtn.prop('disabled', true);
            $form.addClass('whatsapp-form-loading');
            
            const formData = $form.serialize();
            // console.log('Form data:', formData);
            
            $.ajax({
                url: whatsappChatAdmin.ajaxUrl,
                type: 'POST',
                data: formData + '&action=whatsapp_chat_save_settings&nonce=' + whatsappChatAdmin.nonce,
                success: function(response) {
                    // console.log('Settings save response:', response);
                    if (response.success) {
                        WhatsAppChatAdmin.showNotification('success', 'Settings saved successfully!');
                        // Remove success states after a delay
                        setTimeout(function() {
                            $form.find('.whatsapp-form-group').removeClass('success');
                        }, 2000);
                    } else {
                        WhatsAppChatAdmin.showNotification('error', response.data || 'Error saving settings');
                    }
                },
                error: function(xhr, status, error) {
                    // console.log('Settings save error:', error);
                    WhatsAppChatAdmin.showNotification('error', 'Network error. Please check your connection and try again.');
                },
                complete: function() {
                    $submitBtn.html(originalText);
                    $submitBtn.prop('disabled', false);
                    $form.removeClass('whatsapp-form-loading');
                }
            });
        },

        /**
         * Handle Contact Submit
         */
        handleContactSubmit: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const formData = $form.serialize();
            
            $.ajax({
                url: whatsappChatAdmin.ajaxUrl,
                type: 'POST',
                data: formData + '&action=whatsapp_chat_save_contact&nonce=' + whatsappChatAdmin.nonce,
                success: function(response) {
                    if (response.success) {
                        WhatsAppChatAdmin.showNotification('success', 'Contact saved successfully!');
                        WhatsAppChatAdmin.closeModal();
                        location.reload(); // Refresh to show updated data
                    } else {
                        WhatsAppChatAdmin.showNotification('error', response.data || 'Error saving contact!');
                    }
                },
                error: function() {
                    WhatsAppChatAdmin.showNotification('error', 'Error saving contact!');
                }
            });
        },

        /**
         * Show Contact Modal
         */
        showContactModal: function() {
            const modalHtml = `
                <div class="whatsapp-modal" id="contact-modal">
                    <div class="whatsapp-modal-content">
                        <div class="whatsapp-modal-header">
                            <h3 class="whatsapp-modal-title">
                                <span class="dashicons dashicons-plus-alt2"></span>
                                Add New Contact
                            </h3>
                            <button class="whatsapp-modal-close">
                                <span class="dashicons dashicons-no-alt"></span>
                            </button>
                        </div>
                        <div class="whatsapp-modal-body">
                            <form id="contact-form">
                                <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                        <label class="whatsapp-form-label">First Name *</label>
                                        <input type="text" name="firstname" class="whatsapp-form-control" required placeholder="Enter first name">
                                </div>
                                <div class="whatsapp-form-group">
                                        <label class="whatsapp-form-label">Last Name *</label>
                                        <input type="text" name="lastname" class="whatsapp-form-control" required placeholder="Enter last name">
                                </div>
                                </div>
                                
                                <div class="whatsapp-form-group">
                                    <label class="whatsapp-form-label">Phone Number *</label>
                                    <input type="text" name="phone" class="whatsapp-form-control" required placeholder="+1234567890">
                                    <div class="whatsapp-form-help">Include country code (e.g., +1234567890)</div>
                                </div>
                                
                                <div class="whatsapp-form-group">
                                    <label class="whatsapp-form-label">Default Message</label>
                                    <textarea name="message" class="whatsapp-form-control" rows="3" placeholder="Enter default message for WhatsApp chat"></textarea>
                                </div>
                                
                                <div class="whatsapp-form-group">
                                    <label class="whatsapp-form-label">Label</label>
                                    <input type="text" name="label" class="whatsapp-form-control" placeholder="e.g., Sales, Support, Customer Service">
                                </div>
                                
                                <div class="whatsapp-form-group">
                                    <label class="whatsapp-form-label">Agent Avatar</label>
                                    <div class="whatsapp-avatar-upload-container">
                                        <div class="whatsapp-avatar-preview" id="modal-avatar-preview" style="display: none;">
                                            <img id="modal-avatar-preview-img" src="" alt="Avatar Preview">
                                        </div>
                                        <div id="modal-avatar-placeholder" class="whatsapp-avatar-placeholder">
                                            <span class="dashicons dashicons-camera"></span>
                                            <p>Upload Avatar</p>
                                        </div>
                                        <input type="file" id="modal-avatar" name="avatar_file" class="whatsapp-form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" style="display: none;">
                                        <button type="button" id="modal-upload-avatar-btn" class="whatsapp-btn whatsapp-btn-secondary">
                                            <span class="dashicons dashicons-upload"></span>
                                            Upload Avatar
                                        </button>
                                        <button type="button" id="modal-media-library-btn" class="whatsapp-btn whatsapp-btn-secondary">
                                            <span class="dashicons dashicons-admin-media"></span>
                                            Choose from Media Library
                                        </button>
                                        <input type="hidden" id="modal-avatar-url" name="avatar" value="">
                                        <div class="whatsapp-upload-progress" id="modal-upload-progress" style="display: none;">
                                            <div class="progress-bar">
                                                <div class="progress-fill"></div>
                                            </div>
                                            <span class="progress-text">Uploading...</span>
                                        </div>
                                    </div>
                                    <div class="whatsapp-form-note">
                                        <small>Upload an image for the agent's avatar (JPG, PNG, GIF, WebP - Max 2MB) or choose from your media library.</small>
                                    </div>
                                </div>
                                
                                <div class="whatsapp-form-group">
                                    <label class="whatsapp-switch-large">
                                        <input type="checkbox" name="status" value="1" checked>
                                        <span class="slider-large"></span>
                                        <span class="switch-label">Active Contact</span>
                                    </label>
                                </div>
                            </form>
                        </div>
                        <div class="whatsapp-modal-footer">
                            <button type="button" class="whatsapp-btn whatsapp-btn-secondary whatsapp-modal-close">
                                <span class="dashicons dashicons-no-alt"></span>
                                Cancel
                            </button>
                            <button type="submit" form="contact-form" class="whatsapp-btn whatsapp-btn-primary">
                                <span class="dashicons dashicons-saved"></span>
                                Save Contact
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $('#whatsapp-modal-container').html(modalHtml);
            $('#contact-modal').show();
            
            // Add floating action button if not exists
            if (!$('#whatsapp-fab').length) {
                $('body').append(`
                    <button id="whatsapp-fab" class="whatsapp-fab" title="Add New Contact">
                        <span class="dashicons dashicons-plus-alt2"></span>
                    </button>
                `);
            }
        },

        /**
         * Edit Contact
         */
        editContact: function() {
            const contactId = $(this).data('id');
            const firstname = $(this).data('firstname');
            const lastname = $(this).data('lastname');
            const phone = $(this).data('phone');
            const label = $(this).data('label');
            const message = $(this).data('message');
            const avatar = $(this).data('avatar');
            const status = $(this).data('status');
            
            const modalHtml = `
                <div class="whatsapp-modal" id="edit-contact-modal">
                    <div class="whatsapp-modal-content">
                        <div class="whatsapp-modal-header">
                            <h3 class="whatsapp-modal-title">
                                <span class="dashicons dashicons-edit"></span>
                                Edit Contact
                            </h3>
                            <button class="whatsapp-modal-close">
                                <span class="dashicons dashicons-no-alt"></span>
                            </button>
                        </div>
                        <div class="whatsapp-modal-body">
                            <form id="edit-contact-form">
                                <input type="hidden" id="edit_contact_id" name="contact_id" value="${contactId}">
                                
                                <div class="whatsapp-form-row">
                                    <div class="whatsapp-form-group">
                                        <label class="whatsapp-form-label">First Name *</label>
                                        <input type="text" id="edit_firstname" name="firstname" class="whatsapp-form-control" required value="${firstname}">
                                    </div>
                                    <div class="whatsapp-form-group">
                                        <label class="whatsapp-form-label">Last Name *</label>
                                        <input type="text" id="edit_lastname" name="lastname" class="whatsapp-form-control" required value="${lastname}">
                                    </div>
                                </div>
                                
                                <div class="whatsapp-form-group">
                                    <label class="whatsapp-form-label">Phone Number *</label>
                                    <input type="text" id="edit_phone" name="phone" class="whatsapp-form-control" required value="${phone}">
                                    <div class="whatsapp-form-help">Include country code (e.g., +1234567890)</div>
                                </div>
                                
                                <div class="whatsapp-form-group">
                                    <label class="whatsapp-form-label">Default Message</label>
                                    <textarea id="edit_message" name="message" class="whatsapp-form-control" rows="3">${message}</textarea>
                                </div>
                                
                                <div class="whatsapp-form-group">
                                    <label class="whatsapp-form-label">Label</label>
                                    <input type="text" id="edit_label" name="label" class="whatsapp-form-control" value="${label}">
                                </div>
                                
                                <div class="whatsapp-form-group">
                                    <label class="whatsapp-form-label">Agent Avatar</label>
                                    <div class="whatsapp-avatar-upload-container">
                                        <div class="whatsapp-avatar-preview" id="edit-avatar-preview" style="${avatar ? 'display: flex;' : 'display: none;'}">
                                            <img id="edit-avatar-preview-img" src="${avatar || ''}" alt="Avatar Preview">
                                            <button type="button" id="edit-remove-avatar" class="whatsapp-btn whatsapp-btn-sm whatsapp-btn-danger">
                                                <span class="dashicons dashicons-trash"></span>
                                                Remove
                                            </button>
                                        </div>
                                        <div id="edit-avatar-placeholder" class="whatsapp-avatar-placeholder" style="${avatar ? 'display: none;' : 'display: flex;'}">
                                            <span class="dashicons dashicons-camera"></span>
                                            <p>Upload Avatar</p>
                                        </div>
                                        <input type="file" id="edit-avatar-file" name="avatar_file" class="whatsapp-form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" style="display: none;">
                                        <button type="button" id="edit-upload-avatar-btn" class="whatsapp-btn whatsapp-btn-secondary">
                                            <span class="dashicons dashicons-upload"></span>
                                            Upload Avatar
                                        </button>
                                        <button type="button" id="edit-media-library-btn" class="whatsapp-btn whatsapp-btn-secondary">
                                            <span class="dashicons dashicons-admin-media"></span>
                                            Choose from Media Library
                                        </button>
                                        <input type="hidden" id="edit-avatar-url" name="avatar" value="${avatar || ''}">
                                        <div class="whatsapp-upload-progress" id="edit-upload-progress" style="display: none;">
                                            <div class="progress-bar">
                                                <div class="progress-fill"></div>
                                            </div>
                                            <span class="progress-text">Uploading...</span>
                                        </div>
                                    </div>
                                    <div class="whatsapp-form-note">
                                        <small>Upload an image for the agent's avatar (JPG, PNG, GIF, WebP - Max 2MB) or choose from your media library.</small>
                                    </div>
                                </div>
                                
                                <div class="whatsapp-form-group">
                                    <label class="whatsapp-switch-large">
                                        <input type="checkbox" id="edit_status" name="status" value="1" ${status === '1' ? 'checked' : ''}>
                                        <span class="slider-large"></span>
                                        <span class="switch-label">Active Contact</span>
                                    </label>
                                </div>
                            </form>
                        </div>
                        <div class="whatsapp-modal-footer">
                            <button type="button" class="whatsapp-btn whatsapp-btn-secondary whatsapp-modal-close">
                                <span class="dashicons dashicons-no-alt"></span>
                                Cancel
                            </button>
                            <button type="submit" form="edit-contact-form" class="whatsapp-btn whatsapp-btn-primary">
                                <span class="dashicons dashicons-saved"></span>
                                Update Contact
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $('#whatsapp-modal-container').html(modalHtml);
            $('#edit-contact-modal').show();
        },

        /**
         * Delete Contact
         */
        deleteContact: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const contactId = $(this).data('id');
            const nonce = $(this).data('nonce');
            const contactName = $(this).closest('.whatsapp-contact-item').find('.contact-details h5').text();
            
            // Single confirmation dialog
            if (!confirm(`Are you sure you want to delete "${contactName}"?`)) {
                return;
            }
            
            // Show loading state
            const deleteBtn = $(this);
            const originalText = deleteBtn.html();
            deleteBtn.html('<span class="dashicons dashicons-update"></span> Deleting...');
            deleteBtn.prop('disabled', true);
            
            // console.log('Deleting contact:', contactId);
            // console.log('Nonce:', nonce);
            
            $.ajax({
                url: whatsappChatAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'whatsapp_chat_delete_contact',
                    contact_id: contactId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        WhatsAppChatAdmin.showNotification('success', 'Contact deleted successfully!');
                        // Remove the contact item from DOM
                        deleteBtn.closest('.whatsapp-contact-item').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        WhatsAppChatAdmin.showNotification('error', response.data || 'Error deleting contact!');
                        deleteBtn.html(originalText);
                        deleteBtn.prop('disabled', false);
                    }
                },
                error: function() {
                    WhatsAppChatAdmin.showNotification('error', 'Network error. Please try again.');
                    deleteBtn.html(originalText);
                    deleteBtn.prop('disabled', false);
                }
            });
        },

        /**
         * Close Modal
         */
        closeModal: function() {
            $('.whatsapp-modal').hide();
            $('#whatsapp-modal-container').empty();
        },

        /**
         * Close Modal on Backdrop Click
         */
        closeModalOnBackdrop: function(e) {
            if (e.target === this) {
                WhatsAppChatAdmin.closeModal();
            }
        },

        /**
         * Activate License
         */
        activateLicense: function() {
            const licenseKey = $('.license-key-input').val();
            
            if (!licenseKey) {
                WhatsAppChatAdmin.showNotification('error', 'Please enter a license key');
                return;
            }
            
            $.ajax({
                url: whatsappChatAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'whatsapp_chat_validate_license',
                    license_key: licenseKey,
                    nonce: whatsappChatAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WhatsAppChatAdmin.showNotification('success', 'License activated successfully!');
                        location.reload();
                    } else {
                        WhatsAppChatAdmin.showNotification('error', response.data || 'Invalid license key');
                    }
                },
                error: function() {
                    WhatsAppChatAdmin.showNotification('error', 'Error validating license');
                }
            });
        },

        /**
         * Deactivate License
         */
        deactivateLicense: function() {
            // Implementation for deactivating license
        },

        /**
         * Validate License Key
         */
        validateLicenseKey: function(key) {
            // Basic validation - in real implementation, this would call your license server
            const isValid = key.length >= 20 && /^[A-Z0-9-]+$/.test(key);
            
            if (isValid) {
                $('.license-key-input').addClass('valid');
            } else {
                $('.license-key-input').removeClass('valid');
            }
        },

        /**
         * Show Upgrade Prompt
         */
        showUpgradePrompt: function(feature) {
            const messages = {
                design: 'This premium design is available in the Pro version. Upgrade to unlock all premium designs!',
                agents: 'Add unlimited agents with the Pro version. Free version is limited to 1 agent.',
                analytics: 'Advanced analytics are available in the Pro version.'
            };
            
            const message = messages[feature] || 'This feature is available in the Pro version.';
            
            const promptHtml = `
                <div class="upgrade-prompt">
                    <div class="upgrade-prompt-content">
                        <h3>🚀 Upgrade to Pro</h3>
                        <p>${message}</p>
                        <button class="whatsapp-btn whatsapp-btn-primary upgrade-btn">
                            Upgrade Now - $24 Only
                        </button>
                    </div>
                </div>
            `;
            
            // Show as notification
            WhatsAppChatAdmin.showNotification('info', message, true);
        },

        /**
         * Handle Upgrade
         */
        handleUpgrade: function() {
            // Redirect to purchase page
            window.open('https://getjustplug.com/whatsapp-chat-pro', '_blank');
        },

        /**
         * Check Premium Usage
         */
        checkPremiumUsage: function() {
            const licenseStatus = $('input[name="license_status"]').val() || 'inactive';
            
            if (licenseStatus === 'inactive') {
                // Show upgrade prompts for premium features
                $('.premium-feature').each(function() {
                    $(this).append('<div class="upgrade-overlay">Pro Feature</div>');
                });
            }
        },

        /**
         * Auto Save
         */
        autoSave: function() {
            const $field = $(this);
            const fieldName = $field.attr('name');
            const fieldValue = $field.val();
            
            // Debounce auto-save
            clearTimeout($field.data('autoSaveTimeout'));
            $field.data('autoSaveTimeout', setTimeout(function() {
                WhatsAppChatAdmin.saveField(fieldName, fieldValue);
            }, 1000));
        },

        /**
         * Save Field
         */
        saveField: function(name, value) {
            $.ajax({
                url: whatsappChatAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'whatsapp_chat_save_field',
                    field_name: name,
                    field_value: value,
                    nonce: whatsappChatAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WhatsAppChatAdmin.showNotification('success', 'Settings saved automatically');
                    }
                }
            });
        },

        /**
         * Initialize Design Selection
         */
        initDesignSelection: function() {
            // Handle design selection
            $('.select-design').on('click', function() {
                const designId = $(this).data('design');
                WhatsAppChatAdmin.selectDesign(designId);
            });
            
            // Handle design item clicks
            $('.whatsapp-design-item').on('click', function() {
                const designId = $(this).data('design');
                WhatsAppChatAdmin.toggleDesignOptions(designId);
            });
            
            // Initialize design options on page load
            const currentDesign = $('.whatsapp-design-item.selected').data('design');
            if (currentDesign) {
                WhatsAppChatAdmin.toggleDesignOptions(currentDesign);
            } else {
                // Show basic options by default and hide professional options
                $('#design-1-5-options').show();
                $('.whatsapp-card:has(#design-6-7-options)').removeClass('show-professional').hide();
            }
            
            // Initialize WordPress Media Uploader
            WhatsAppChatAdmin.initMediaUploader();
            
            // Initialize design form submission
            WhatsAppChatAdmin.initDesignFormSubmission();
            WhatsAppChatAdmin.initBasicDesignFormSubmission();
        },
        
        /**
         * Handle Design Selection
         */
        handleDesignSelection: function(e) {
            e.preventDefault();
            const designId = $(this).data('design');
            WhatsAppChatAdmin.selectDesign(designId);
        },
        
        /**
         * Select Design
         */
        selectDesign: function(designId) {
            $.ajax({
                url: whatsappChatAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'whatsapp_chat_select_design',
                    design_id: designId,
                    nonce: whatsappChatAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update UI
                        $('.design-preview-card').removeClass('selected');
                        $(`.design-preview-card[data-design="${designId}"]`).addClass('selected');
                        
                        // Update buttons
                        $('.design-actions .whatsapp-btn').text('Select Design');
                        $(`.design-preview-card[data-design="${designId}"] .design-actions`).html('<span class="selected-badge">✅ Selected</span>');
                        
                        // Toggle design options
                        WhatsAppChatAdmin.toggleDesignOptions(designId);
                        
                        // Show notification
                        WhatsAppChatAdmin.showNotification('success', response.data);
                    } else {
                        WhatsAppChatAdmin.showNotification('error', response.data);
                    }
                },
                error: function() {
                    WhatsAppChatAdmin.showNotification('error', 'Error selecting design!');
                }
            });
        },
        
        /**
         * Toggle Design Options
         */
        toggleDesignOptions: function(designId) {
            // Hide all sections first
            $('#design-1-5-options, #design-6-7-options').hide();
            $('.whatsapp-card:has(#design-6-7-options)').removeClass('show-professional').hide();
            $('.whatsapp-card:has(#design-1-5-options)').hide();
            
            // Show appropriate section based on design
            if (['design-1', 'design-2', 'design-3', 'design-4', 'design-5'].includes(designId)) {
                $('#design-1-5-options').show();
                $('.whatsapp-card:has(#design-1-5-options)').show();
                // Hide the professional design card for designs 1-5
                $('.whatsapp-card:has(#design-6-7-options)').hide();
            } else if (['design-6', 'design-7'].includes(designId)) {
                $('#design-6-7-options').show();
                // Show the professional design card for designs 6-7
                $('.whatsapp-card:has(#design-6-7-options)').addClass('show-professional').show();
                // Hide the basic design card for designs 6-7
                $('.whatsapp-card:has(#design-1-5-options)').hide();
            }
        },
        
        /**
         * Initialize WordPress Media Uploader
         */
        initMediaUploader: function() {
            // Unified Avatar Upload for Design 6 & 7
            $('#upload-design-avatar').on('click', function(e) {
                e.preventDefault();
                
                const mediaUploader = wp.media({
                    title: 'Choose Avatar Image',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                mediaUploader.on('select', function() {
                    const attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#design_avatar_url').val(attachment.url);
                    $('.whatsapp-avatar-preview').html('<img src="' + attachment.url + '" alt="Avatar Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">');
                    $('#remove-design-avatar').show();
                });
                
                mediaUploader.open();
            });
            
            // Remove Avatar
            $('#remove-design-avatar').on('click', function(e) {
                e.preventDefault();
                $('#design_avatar_url').val('');
                $('.whatsapp-avatar-preview').html('<div>No avatar selected</div>');
                $(this).hide();
            });
        },
        
        /**
         * Initialize Design Form Submission
         */
        initDesignFormSubmission: function() {
            $('#design-special-form').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'whatsapp_chat_save_design_options');
                formData.append('nonce', whatsappChatAdmin.nonce);
                
                $.ajax({
                    url: whatsappChatAdmin.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            WhatsAppChatAdmin.showNotification('success', 'Design options saved successfully!');
                        } else {
                            WhatsAppChatAdmin.showNotification('error', 'Error saving design options: ' + response.data);
                        }
                    },
                    error: function() {
                        WhatsAppChatAdmin.showNotification('error', 'Error saving design options');
                    }
                });
            });
            
            // Reset colors button functionality
            $('#reset-design-colors').on('click', function(e) {
                e.preventDefault();
                
                // Reset to default colors
                $('input[name="design_bg_color"]').val('#25D364');
                $('input[name="design_text_color"]').val('#ffffff');
                $('input[name="design_name_color"]').val('#ffffff');
                $('input[name="design_status_color"]').val('#000');
                
                WhatsAppChatAdmin.showNotification('success', 'Colors reset to default values!');
            });
            
            // General reset colors button functionality
            $('#reset-colors-btn').on('click', function(e) {
                e.preventDefault();
                
                if (!confirm('Are you sure you want to reset all colors to default? This cannot be undone.')) {
                    return;
                }
                
                const $btn = $(this);
                $btn.prop('disabled', true).text('Resetting...');
                
                $.ajax({
                    url: whatsappChatAdmin.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'whatsapp_chat_reset_colors',
                        nonce: whatsappChatAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update color pickers with default values
                            $.each(response.data.colors, function(key, value) {
                                const $input = $('input[name="' + key + '"]');
                                if ($input.length) {
                                    $input.val(value).trigger('change');
                                    // Update color picker if it exists
                                    if ($input.hasClass('wp-color-picker')) {
                                        $input.wpColorPicker('color', value);
                                    }
                                }
                            });
                            
                            WhatsAppChatAdmin.showNotification('Colors reset to default successfully!', 'success');
                        } else {
                            WhatsAppChatAdmin.showNotification('Error resetting colors. Please try again.', 'error');
                        }
                    },
                    error: function() {
                        WhatsAppChatAdmin.showNotification('Error resetting colors. Please try again.', 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Reset Colors');
                    }
                });
            });
        },
        
        /**
         * Initialize Basic Design Form Submission
         */
        initBasicDesignFormSubmission: function() {
            $('#basic-design-form').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'whatsapp_chat_save_basic_design_options');
                formData.append('nonce', whatsappChatAdmin.nonce);
                
                $.ajax({
                    url: whatsappChatAdmin.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            WhatsAppChatAdmin.showNotification('success', 'Basic design options saved successfully!');
                        } else {
                            WhatsAppChatAdmin.showNotification('error', 'Error saving basic design options: ' + response.data);
                        }
                    },
                    error: function() {
                        WhatsAppChatAdmin.showNotification('error', 'Error saving basic design options');
                    }
                });
            });
        },
        
        /**
         * Show Notification - Enhanced with better animations
         */
        showNotification: function(type, message, persistent = false) {
            // Remove existing notifications
            $('.whatsapp-notification').remove();
            
            const notificationHtml = `
                <div class="whatsapp-notification ${type}">
                    <div class="notification-content">
                        <strong>${type === 'success' ? '✓' : type === 'error' ? '✗' : 'ℹ'}</strong>
                        ${message}
                    </div>
                </div>
            `;
            
            $('body').append(notificationHtml);
            
            // Trigger animation
            setTimeout(function() {
                $('.whatsapp-notification').addClass('show');
            }, 100);
            
            if (!persistent) {
                setTimeout(function() {
                    $('.whatsapp-notification').removeClass('show');
                    setTimeout(function() {
                        $('.whatsapp-notification').remove();
                    }, 300);
                }, 5000);
            }
        },

        /**
         * Show success notification and refresh page
         */
        showSuccessAndRefresh: function(message) {
            this.showNotification('success', message);
            setTimeout(function() {
                location.reload();
            }, 1500);
        },

        /**
         * Handle avatar upload button click
         */
        handleAvatarUpload: function() {
            $('#avatar').click();
        },

        /**
         * Handle avatar file selection
         */
        handleAvatarFileSelect: function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                WhatsAppChatAdmin.showNotification('error', 'Please select a valid image file (JPEG, PNG, GIF, WebP)');
                return;
            }

            // Validate file size (2MB limit)
            if (file.size > 2 * 1024 * 1024) {
                WhatsAppChatAdmin.showNotification('error', 'File size must be less than 2MB');
                return;
            }

            // Show preview immediately
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#avatar-preview-img').attr('src', e.target.result);
                $('#avatar-preview').show();
                $('#upload-avatar-btn').hide();
            };
            reader.readAsDataURL(file);

            // Show upload progress
            $('#upload-progress').show();
            $('#upload-avatar-btn').prop('disabled', true);

            // Upload file
            WhatsAppChatAdmin.uploadAvatarFile(file);
        },

        /**
         * Upload avatar file
         */
        uploadAvatarFile: function(file) {
            const formData = new FormData();
            formData.append('action', 'whatsapp_chat_upload_file');
            formData.append('nonce', whatsappChatAdmin.nonce);
            formData.append('file', file);

            $.ajax({
                url: whatsappChatAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = evt.loaded / evt.total * 100;
                            $('.progress-fill').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $('#upload-progress').hide();
                    $('#upload-avatar-btn').prop('disabled', false);
                    
                    if (response.success) {
                        $('#avatar-url').val(response.data.url);
                        WhatsAppChatAdmin.showNotification('success', 'Avatar uploaded successfully!');
                    } else {
                        WhatsAppChatAdmin.showNotification('error', response.data);
                        $('#avatar-preview').hide();
                        $('#upload-avatar-btn').show();
                    }
                },
                error: function() {
                    $('#upload-progress').hide();
                    $('#upload-avatar-btn').prop('disabled', false);
                    WhatsAppChatAdmin.showNotification('error', 'Error uploading avatar!');
                    $('#avatar-preview').hide();
                    $('#upload-avatar-btn').show();
                }
            });
        },

        /**
         * Remove avatar
         */
        removeAvatar: function() {
            $('#avatar-preview').hide();
            $('#upload-avatar-btn').show();
            $('#avatar').val('');
            $('#avatar-url').val('');
        },
        
        /**
         * Handle modal avatar upload
         */
        handleModalAvatarUpload: function() {
            $('#modal-avatar').click();
        },
        
        /**
         * Handle modal avatar file select
         */
        handleModalAvatarFileSelect: function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                WhatsAppChatAdmin.showNotification('error', 'Please select a valid image file (JPG, PNG, GIF, WebP)');
                return;
            }
            
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                WhatsAppChatAdmin.showNotification('error', 'File size too large. Maximum size is 2MB.');
                return;
            }
            
            // Show preview immediately
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#modal-avatar-preview-img').attr('src', e.target.result);
                $('#modal-avatar-preview').show();
                $('#modal-upload-avatar-btn').hide();
            };
            reader.readAsDataURL(file);

            // Show upload progress
            $('#modal-upload-progress').show();
            $('#modal-upload-avatar-btn').prop('disabled', true);

            // Upload file
            WhatsAppChatAdmin.uploadModalAvatarFile(file);
        },
        
        /**
         * Upload modal avatar file
         */
        uploadModalAvatarFile: function(file) {
            const formData = new FormData();
            formData.append('action', 'whatsapp_chat_upload_file');
            formData.append('nonce', whatsappChatAdmin.nonce);
            formData.append('file', file);

            $.ajax({
                url: whatsappChatAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = evt.loaded / evt.total * 100;
                            $('#modal-upload-progress .progress-fill').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $('#modal-upload-progress').hide();
                    $('#modal-upload-avatar-btn').prop('disabled', false);
                    
                    if (response.success) {
                        $('#modal-avatar-url').val(response.data.url);
                        WhatsAppChatAdmin.showNotification('success', 'Avatar uploaded successfully!');
                    } else {
                        WhatsAppChatAdmin.showNotification('error', response.data);
                        $('#modal-avatar-preview').hide();
                        $('#modal-upload-avatar-btn').show();
                    }
                },
                error: function() {
                    $('#modal-upload-progress').hide();
                    $('#modal-upload-avatar-btn').prop('disabled', false);
                    WhatsAppChatAdmin.showNotification('error', 'Error uploading avatar!');
                    $('#modal-avatar-preview').hide();
                    $('#modal-upload-avatar-btn').show();
                }
            });
        },
        
        /**
         * Remove modal avatar
         */
        removeModalAvatar: function() {
            $('#modal-avatar-preview').hide();
            $('#modal-upload-avatar-btn').show();
            $('#modal-avatar').val('');
            $('#modal-avatar-url').val('');
        },
        
        /**
         * Handle Design 7 avatar upload
         */
        handleDesign7AvatarUpload: function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                WhatsAppChatAdmin.showNotification('Please select an image file', 'error');
                return;
            }
            
            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                WhatsAppChatAdmin.showNotification('File size too large. Maximum size is 2MB', 'error');
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = $('.whatsapp-avatar-preview');
                preview.html('<img src="' + e.target.result + '" alt="Avatar Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">');
            };
            reader.readAsDataURL(file);
            
            // Upload file
            const formData = new FormData();
            formData.append('action', 'whatsapp_chat_upload_avatar');
            formData.append('nonce', whatsappChatAdmin.nonce);
            formData.append('avatar_file', file);
            
            $.ajax({
                url: whatsappChatAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('input[name="design7_avatar"]').val(response.data.url);
                        WhatsAppChatAdmin.showNotification('Avatar uploaded successfully!', 'success');
                    } else {
                        WhatsAppChatAdmin.showNotification('Error uploading avatar: ' + response.data, 'error');
                    }
                },
                error: function() {
                    WhatsAppChatAdmin.showNotification('Error uploading avatar', 'error');
                }
            });
        },
        
        /**
         * Select design
         */
        selectDesign: function(e) {
            e.preventDefault();
            
            const designId = $(this).data('design');
            const nonce = $(this).data('nonce');
            const button = $(this);
            
            // Check if this is a PRO design and user doesn't have license
            const isProDesign = designId >= 6;
            const hasLicense = whatsappChatAdmin.hasLicense || false;
            
            if (isProDesign && !hasLicense) {
                WhatsAppChatAdmin.showUpgradeModal();
                return;
            }
            
            // Show loading state
            button.html('<span class="dashicons dashicons-update"></span> Selecting...');
            button.prop('disabled', true);
            
            $.ajax({
                url: whatsappChatAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'whatsapp_chat_select_design',
                    design_id: designId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Remove all previous selections first
                        $('.select-design').removeClass('selected').html('Select').prop('disabled', false);
                        $('.whatsapp-design-item').removeClass('selected');
                        
                        // Add selection to current button and item
                        button.addClass('selected').html('✓ Selected');
                        button.closest('.whatsapp-design-item').addClass('selected');
                        
                        WhatsAppChatAdmin.showNotification('Design selected successfully!', 'success');
                    } else {
                        WhatsAppChatAdmin.showNotification(response.data || 'Error selecting design', 'error');
                        button.html('Select');
                        button.prop('disabled', false);
                    }
                },
                error: function() {
                    WhatsAppChatAdmin.showNotification('Error selecting design. Please try again.', 'error');
                    button.html('Select');
                    button.prop('disabled', false);
                }
            });
        },
        
        /**
         * Handle add contact form
         */
        handleAddContact: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const form = $('#add-contact-form');
            const submitBtn = $(this);
            
            // Show loading state
            submitBtn.html('<span class="dashicons dashicons-update"></span> Adding...');
            submitBtn.prop('disabled', true);
            
            const formData = {
                action: 'whatsapp_chat_save_contact',
                id: 0,
                nonce: $('input[name="nonce"]').val(),
                firstname: $('#firstname').val(),
                lastname: $('#lastname').val(),
                phone: $('#phone').val(),
                label: $('#label').val(),
                message: $('#message').val(),
                avatar: $('#avatar-url').val(),
                status: $('input[name="status"]').is(':checked') ? '1' : '0'
            };
            
            // Validate required fields
            if (!formData.firstname || !formData.phone) {
                WhatsAppChatAdmin.showNotification('error', 'Please fill in all required fields');
                submitBtn.html('<span class="dashicons dashicons-plus"></span> Add Contact');
                submitBtn.prop('disabled', false);
                return;
            }
            
            $.ajax({
                url: whatsappChatAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        WhatsAppChatAdmin.showNotification('success', 'Contact added successfully!');
                        form[0].reset();
                        location.reload();
                    } else {
                        WhatsAppChatAdmin.showNotification('error', response.data);
                    }
                },
                error: function() {
                    WhatsAppChatAdmin.showNotification('error', 'Error adding contact');
                },
                complete: function() {
                    submitBtn.html('<span class="dashicons dashicons-plus"></span> Add Contact');
                    submitBtn.prop('disabled', false);
                }
            });
        },

        /**
         * Initialize Avatar Upload
         */
        initAvatarUpload: function() {
            // console.log('WhatsApp Chat: Initializing avatar upload');
            
            // Main form avatar upload
            this.bindAvatarUploadEvents('#upload-avatar-btn', '#avatar-file', '#avatar-preview', '#avatar-preview-img', '#avatar-url', '#remove-avatar', '#upload-progress');
            
            // Edit form avatar upload
            this.bindAvatarUploadEvents('#edit-upload-avatar-btn', '#edit-avatar-file', '#edit-avatar-preview', '#edit-avatar-preview-img', '#edit-avatar-url', '#edit-remove-avatar', '#edit-upload-progress');
            
            // Media library buttons
            this.bindMediaLibraryEvents('#media-library-btn', '#avatar-preview', '#avatar-preview-img', '#avatar-url', '#remove-avatar');
            this.bindMediaLibraryEvents('#edit-media-library-btn', '#edit-avatar-preview', '#edit-avatar-preview-img', '#edit-avatar-url', '#edit-remove-avatar');
        },

        /**
         * Bind Avatar Upload Events
         */
        bindAvatarUploadEvents: function(uploadBtnId, fileInputId, previewId, previewImgId, hiddenInputId, removeBtnId, progressId) {
            const self = this;
            
            // Upload button click
            $(document).on('click', uploadBtnId, function() {
                $(fileInputId).click();
            });
            
            // File input change
            $(document).on('change', fileInputId, function() {
                const file = this.files[0];
                if (file) {
                    self.uploadAvatarFile(file, previewId, previewImgId, hiddenInputId, progressId);
                }
            });
            
            // Remove avatar
            $(document).on('click', removeBtnId, function() {
                $(previewId).hide();
                $(hiddenInputId).val('');
                $(fileInputId).val('');
            });
        },

        /**
         * Bind Media Library Events
         */
        bindMediaLibraryEvents: function(btnId, previewId, previewImgId, hiddenInputId, removeBtnId) {
            const self = this;
            
            $(document).on('click', btnId, function() {
                if (typeof wp !== 'undefined' && wp.media) {
                    const mediaUploader = wp.media({
                        title: 'Choose Avatar Image',
                        button: {
                            text: 'Use this image'
                        },
                        multiple: false,
                        library: {
                            type: 'image'
                        }
                    });
                    
                    mediaUploader.on('select', function() {
                        const attachment = mediaUploader.state().get('selection').first().toJSON();
                        self.setAvatarPreview(attachment.url, previewId, previewImgId, hiddenInputId);
                    });
                    
                    mediaUploader.open();
                } else {
                    console.error('WordPress media library not available');
                }
            });
        },

        /**
         * Upload Avatar File
         */
        uploadAvatarFile: function(file, previewId, previewImgId, hiddenInputId, progressId) {
            const self = this;
            const formData = new FormData();
            formData.append('action', 'whatsapp_chat_upload_file');
            formData.append('file', file);
            formData.append('nonce', whatsappChatAdmin.nonce);
            
            // Show progress
            $(progressId).show();
            
            $.ajax({
                url: whatsappChatAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        self.setAvatarPreview(response.data.url, previewId, previewImgId, hiddenInputId);
                    } else {
                        WhatsAppChatAdmin.showNotification('error', response.data || 'Upload failed');
                    }
                },
                error: function() {
                    WhatsAppChatAdmin.showNotification('error', 'Upload failed');
                },
                complete: function() {
                    $(progressId).hide();
                }
            });
        },

        /**
         * Set Avatar Preview
         */
        setAvatarPreview: function(url, previewId, previewImgId, hiddenInputId) {
            $(previewImgId).attr('src', url);
            $(previewId).show();
            $(hiddenInputId).val(url);
        },
        
        /**
         * Initialize Professional Settings Form
         */
        initProfessionalSettings: function() {
            // console.log('WhatsApp Chat: Initializing professional settings form');
            
            // Handle professional toggle switches
            $('.whatsapp-settings-toggle').on('click', function(e) {
                e.preventDefault();
                const checkbox = $(this).find('input[type="checkbox"]');
                checkbox.prop('checked', !checkbox.prop('checked'));
                checkbox.trigger('change');
                
                // Add visual feedback
                $(this).addClass('toggle-active');
                setTimeout(() => {
                    $(this).removeClass('toggle-active');
                }, 200);
            });
            
            // Handle form input focus effects
            $('.whatsapp-settings-input').on('focus', function() {
                $(this).closest('.whatsapp-settings-group').addClass('focused');
            }).on('blur', function() {
                $(this).closest('.whatsapp-settings-group').removeClass('focused');
            });
            
            // Handle section hover effects
            $('.whatsapp-settings-section').on('mouseenter', function() {
                $(this).addClass('section-hover');
            }).on('mouseleave', function() {
                $(this).removeClass('section-hover');
            });
            
            // Add loading state to form submission
            $('#whatsapp-settings-form').on('submit', function() {
                const submitBtn = $(this).find('.whatsapp-settings-btn-primary');
                const originalText = submitBtn.html();
                
                submitBtn.html('<span class="dashicons dashicons-update"></span> Saving...');
                submitBtn.prop('disabled', true);
                
                // Re-enable after 3 seconds (in case of error)
                setTimeout(function() {
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                }, 3000);
            });
            
            // console.log('WhatsApp Chat: Professional settings form initialized');
        },
        
        /**
         * Initialize Analytics Tracking
         */
        initAnalyticsTracking: function() {
            // console.log('WhatsApp Chat: Initializing analytics tracking');
            
            // Track clicks on WhatsApp chat button specifically
            $(document).on('click', '.whatsapp-chat-button', function(e) {
                // console.log('WhatsApp Chat: Button clicked, tracking...');
                WhatsAppChatAdmin.trackClick();
            });
            
            // Track clicks on WhatsApp chat widget
            $(document).on('click', '.whatsapp-chat-widget', function(e) {
                // console.log('WhatsApp Chat: Widget clicked, tracking...');
                WhatsAppChatAdmin.trackClick();
            });
            
            // Track clicks on any WhatsApp link that contains our plugin classes
            $(document).on('click', 'a[href*="wa.me"], a[href*="whatsapp.com"], a[href*="api.whatsapp.com"]', function(e) {
                // Check if it's our WhatsApp button or widget
                if ($(this).hasClass('whatsapp-chat-button') || 
                    $(this).closest('.whatsapp-chat-widget').length > 0 ||
                    $(this).closest('.whatsapp-chat').length > 0) {
                    // console.log('WhatsApp Chat: WhatsApp link clicked, tracking...');
                    WhatsAppChatAdmin.trackClick();
                }
            });
            
            // console.log('WhatsApp Chat: Analytics tracking initialized');
        },
        
        /**
         * Track WhatsApp button click
         */
        trackClick: function() {
            // Prevent multiple tracking of the same click
            if (WhatsAppChatAdmin.isTracking) {
                return;
            }
            
            WhatsAppChatAdmin.isTracking = true;
            
            // Send AJAX request to track click
            $.ajax({
                url: whatsappChatAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'whatsapp_chat_track_click',
                    nonce: whatsappChatAdmin.nonce,
                    timestamp: Date.now(),
                    user_agent: navigator.userAgent,
                    page_url: window.location.href
                },
                success: function(response) {
                    if (response.success) {
                        // console.log('WhatsApp Chat: Click tracked successfully', response.data);
                        // Update dashboard counters if on admin page
                        WhatsAppChatAdmin.updateDashboardCounters(response.data);
                        
                        // Show visual feedback
                        WhatsAppChatAdmin.showClickFeedback();
                    } else {
                        // console.log('WhatsApp Chat: Tracking failed:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    // console.log('WhatsApp Chat: Error tracking click:', error);
                },
                complete: function() {
                    // Reset tracking flag after 1 second
                    setTimeout(function() {
                        WhatsAppChatAdmin.isTracking = false;
                    }, 1000);
                }
            });
        },
        
        /**
         * Show visual feedback for click tracking
         */
        showClickFeedback: function() {
            // Create a small notification
            const feedback = $('<div class="whatsapp-click-feedback">✓ Click tracked</div>');
            $('body').append(feedback);
            
            // Animate and remove
            setTimeout(() => {
                feedback.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 1500);
        },
        
        /**
         * Update dashboard counters
         */
        updateDashboardCounters: function(data) {
            // Update total clicks
            $('.analytics-card h3').first().text(data.total_clicks.toLocaleString());
            
            // Update today's clicks
            $('.analytics-trend').first().text('+' + data.today_clicks + ' today');
            
            // Update metrics
            $('.metric-value').first().text(data.today_clicks.toLocaleString());
        },
        
        /**
         * Fix WhatsApp widget positioning
         */
        fixWidgetPositioning: function() {
            // Get custom position values
            const xPosition = $('input[name="whatsapp_chat_button_x"]').val() || 20;
            const yPosition = $('input[name="whatsapp_chat_button_y"]').val() || 70;
            const currentDesign = $('.whatsapp-design-item.selected').data('design');
            
            // Apply custom positioning using CSS custom properties
            $('.whatsapp-chat-widget').css({
                'position': 'fixed',
                'bottom': yPosition + 'px',
                'right': xPosition + 'px',
                'z-index': '999999',
                '--wa-button-x': xPosition + 'px',
                '--wa-button-y': yPosition + 'px'
            });
            
            // Special handling for Design 6 & 7 to prevent sticking
            if (currentDesign === 'design-6' || currentDesign === 'design-7') {
                $('.whatsapp-chat-box, .whatsapp-box').css({
                    'position': 'absolute',
                    'bottom': '100%',
                    'right': '0',
                    'margin-bottom': '20px',
                    'margin-left': '0px',
                    'z-index': '999999'
                });
                
                $('.whatsapp-design6-wrapper').css({
                    'margin-bottom': '20px'
                });
            } else {
                // Standard positioning for other designs
                $('.whatsapp-chat-box').css({
                    'position': 'absolute',
                    'bottom': '100%',
                    'right': '0',
                    'margin-bottom': '10px',
                    'z-index': '999999'
                });
            }
        },
        
        /**
         * Initialize position controls
         */
        initPositionControls: function() {
            // Handle position input changes
            $('input[name="whatsapp_chat_button_x"], input[name="whatsapp_chat_button_y"]').on('input', function() {
                WhatsAppChatAdmin.fixWidgetPositioning();
            });
            
            // Handle position select changes
            $('select[name="whatsapp_chat_button_position"]').on('change', function() {
                const position = $(this).val();
                
                // Reset custom positioning
                $('.whatsapp-chat-widget').removeClass('bottom-right bottom-left top-right top-left');
                $('.whatsapp-chat-widget').addClass(position);
                
                // Apply position-specific styles
                WhatsAppChatAdmin.applyPositionStyles(position);
            });
        },
        
        /**
         * Apply position-specific styles
         */
        applyPositionStyles: function(position) {
            const widget = $('.whatsapp-chat-widget');
            const chatBox = $('.whatsapp-chat-box');
            
            switch(position) {
                case 'bottom-right':
                    widget.css({
                        'bottom': '20px',
                        'right': '20px',
                        'top': 'auto',
                        'left': 'auto'
                    });
                    chatBox.css({
                        'bottom': '100%',
                        'right': '0',
                        'top': 'auto',
                        'left': 'auto'
                    });
                    break;
                case 'bottom-left':
                    widget.css({
                        'bottom': '20px',
                        'left': '20px',
                        'top': 'auto',
                        'right': 'auto'
                    });
                    chatBox.css({
                        'bottom': '100%',
                        'left': '0',
                        'top': 'auto',
                        'right': 'auto'
                    });
                    break;
                case 'top-right':
                    widget.css({
                        'top': '20px',
                        'right': '20px',
                        'bottom': 'auto',
                        'left': 'auto'
                    });
                    chatBox.css({
                        'top': '100%',
                        'right': '0',
                        'bottom': 'auto',
                        'left': 'auto'
                    });
                    break;
                case 'top-left':
                    widget.css({
                        'top': '20px',
                        'left': '20px',
                        'bottom': 'auto',
                        'right': 'auto'
                    });
                    chatBox.css({
                        'top': '100%',
                        'left': '0',
                        'bottom': 'auto',
                        'right': 'auto'
                    });
                    break;
            }
        }
    };

        /**
         * Initialize when document is ready
         */
        $(document).ready(function() {
            // console.log('WhatsApp Chat Admin: Document ready');
            // console.log('Settings form exists:', $('#whatsapp-settings-form').length);
            // console.log('Quick setup form exists:', $('#whatsapp-quick-setup-form').length);
            // console.log('Working hours form exists:', $('#working-hours-form').length);
            // console.log('Analytics form exists:', $('#analytics-settings-form').length);
            // console.log('Display rules form exists:', $('#display-rules-form').length);
            
            // Debug button existence
            // console.log('Working hours button exists:', $('#working-hours-form button[type="submit"]').length);
            // console.log('Analytics button exists:', $('#analytics-settings-form button[type="submit"]').length);
            // console.log('Display rules button exists:', $('#display-rules-form button[type="submit"]').length);
            
            // Debug all forms on page
            // console.log('All forms on page:', $('form').length);
            $('form').each(function(index) {
                // console.log('Form ' + index + ':', $(this).attr('id'), $(this).attr('class'));
            });
            
            // Initialize professional settings form
            WhatsAppChatAdmin.initProfessionalSettings();
            
            // Initialize analytics tracking
            WhatsAppChatAdmin.initAnalyticsTracking();
            
            // Initialize position controls
            WhatsAppChatAdmin.initPositionControls();
            
            WhatsAppChatAdmin.init();
        });

    /**
     * Handle tab switching via URL parameters
     */
    $(window).on('load', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        
        if (tab) {
            $('.whatsapp-nav-tab').removeClass('active');
            $(`.whatsapp-nav-tab[data-tab="${tab}"]`).addClass('active');
            
            $('.tab-content').hide();
            $(`.tab-content[data-tab="${tab}"]`).show();
        }
    });

    /**
     * Handle browser back/forward buttons
     */
    $(window).on('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'general';
        
        $('.whatsapp-nav-tab').removeClass('active');
        $(`.whatsapp-nav-tab[data-tab="${tab}"]`).addClass('active');
        
        $('.tab-content').hide();
        $(`.tab-content[data-tab="${tab}"]`).show();
    });

    /**
     * Contact Management
     */
    // Add contact button
    $(document).on('click', '#add-contact-btn', function() {
        $('#add-contact-form').show();
        $(this).hide();
    });
    
    // Cancel add contact
    $(document).on('click', '#cancel-add-contact', function() {
        $('#add-contact-form').hide();
        $('#add-contact-btn').show();
    });
    
    // Contact form submission
    $(document).on('submit', '#contact-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        
        // Show loading state
        $submitBtn.html('<span class="dashicons dashicons-update"></span> Adding...');
        $submitBtn.prop('disabled', true);
        
        // Send AJAX request
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    showNotice('Contact added successfully!', 'success');
                    $form[0].reset();
                    $('#add-contact-form').hide();
                    $('#add-contact-btn').show();
                    // Reload page to show new contact
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotice(response.data || 'Error adding contact', 'error');
                }
            },
            error: function() {
                showNotice('Network error. Please try again.', 'error');
            },
            complete: function() {
                $submitBtn.html(originalText);
                $submitBtn.prop('disabled', false);
            }
        });
    });
    
    // Edit contact buttons
    $(document).on('click', '.edit-contact', function(e) {
        e.preventDefault();
        const contactId = $(this).data('id');
        showNotice('Edit functionality coming soon!', 'info');
    });
    
    // Delete contact buttons - Remove duplicate handler
    // The deleteContact function in WhatsAppChatAdmin object already handles this
    
    /**
     * Show notice
     */
    function showNotice(message, type) {
        const noticeClass = type === 'success' ? 'notice-success' : 
                           type === 'error' ? 'notice-error' : 
                           type === 'info' ? 'notice-info' : 'notice-warning';
        
        const notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap').prepend(notice);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
    }
    
    /**
     * Show notification (alias for showNotice)
     */
    function showNotification(message, type) {
        showNotice(message, type);
    }
    
    // Handle working hours form submission
    $(document).on('submit', '#working-hours-form', function(e) {
        e.preventDefault();
        // console.log('=== WORKING HOURS FORM SUBMISSION ===');
        // console.log('Working hours form submitted');
        // console.log('Form element:', this);
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        
        // Show loading state
        $submitBtn.html('<span class="dashicons dashicons-update"></span> Saving...');
        $submitBtn.prop('disabled', true);
        $form.addClass('whatsapp-form-loading');
        
        const formData = $form.serialize();
        
        // console.log('AJAX URL:', whatsappChatAdmin.ajaxUrl);
        // console.log('Nonce:', whatsappChatAdmin.nonce);
        // console.log('Form data:', formData);
        
        $.ajax({
            url: whatsappChatAdmin.ajaxUrl,
            type: 'POST',
            data: formData + '&action=whatsapp_chat_save_working_hours&nonce=' + whatsappChatAdmin.nonce,
            beforeSend: function() {
                // console.log('Sending working hours AJAX request...');
                // console.log('AJAX URL:', whatsappChatAdmin.ajaxUrl);
                // console.log('Form data:', formData);
            },
            success: function(response) {
                // console.log('=== WORKING HOURS AJAX SUCCESS ===');
                // console.log('Working hours AJAX response:', response);
                if (response.success) {
                    WhatsAppChatAdmin.showSuccessAndRefresh('Working hours saved successfully!');
                } else {
                    WhatsAppChatAdmin.showNotification('error', 'Error saving working hours: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                // console.log('Working hours AJAX error:', error);
                WhatsAppChatAdmin.showNotification('error', 'Network error. Please check your connection and try again.');
            },
            complete: function() {
                $submitBtn.html(originalText);
                $submitBtn.prop('disabled', false);
                $form.removeClass('whatsapp-form-loading');
            }
        });
    });
    
    // Handle analytics settings form submission
    $(document).on('submit', '#analytics-settings-form', function(e) {
        e.preventDefault();
        // console.log('=== ANALYTICS FORM SUBMISSION ===');
        // console.log('Analytics settings form submitted');
        // console.log('Form element:', this);
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        
        // Show loading state
        $submitBtn.html('<span class="dashicons dashicons-update"></span> Saving...');
        $submitBtn.prop('disabled', true);
        $form.addClass('whatsapp-form-loading');
        
        const formData = $form.serialize();
        
        // console.log('AJAX URL:', whatsappChatAdmin.ajaxUrl);
        // console.log('Nonce:', whatsappChatAdmin.nonce);
        // console.log('Form data:', formData);
        
        $.ajax({
            url: whatsappChatAdmin.ajaxUrl,
            type: 'POST',
            data: formData + '&action=whatsapp_chat_save_analytics_settings&nonce=' + whatsappChatAdmin.nonce,
            beforeSend: function() {
                // console.log('Sending analytics AJAX request...');
                // console.log('AJAX URL:', whatsappChatAdmin.ajaxUrl);
                // console.log('Form data:', formData);
            },
            success: function(response) {
                // console.log('=== ANALYTICS AJAX SUCCESS ===');
                // console.log('Analytics AJAX response:', response);
                if (response.success) {
                    WhatsAppChatAdmin.showSuccessAndRefresh('Analytics settings saved successfully!');
                } else {
                    WhatsAppChatAdmin.showNotification('error', 'Error saving analytics settings: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                // console.log('Analytics AJAX error:', error);
                WhatsAppChatAdmin.showNotification('error', 'Network error. Please check your connection and try again.');
            },
            complete: function() {
                $submitBtn.html(originalText);
                $submitBtn.prop('disabled', false);
                $form.removeClass('whatsapp-form-loading');
            }
        });
    });
    
    // Handle display rules form submission
    $(document).on('submit', '#display-rules-form', function(e) {
        e.preventDefault();
        // console.log('=== DISPLAY RULES FORM SUBMISSION ===');
        // console.log('Display rules form submitted');
        // console.log('Form element:', this);
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        
        // Show loading state
        $submitBtn.html('<span class="dashicons dashicons-update"></span> Saving...');
        $submitBtn.prop('disabled', true);
        $form.addClass('whatsapp-form-loading');
        
        const formData = $form.serialize();
        
        // console.log('AJAX URL:', whatsappChatAdmin.ajaxUrl);
        // console.log('Nonce:', whatsappChatAdmin.nonce);
        // console.log('Form data:', formData);
        
        $.ajax({
            url: whatsappChatAdmin.ajaxUrl,
            type: 'POST',
            data: formData + '&action=whatsapp_chat_save_display_rules&nonce=' + whatsappChatAdmin.nonce,
            beforeSend: function() {
                // console.log('Sending display rules AJAX request...');
                // console.log('AJAX URL:', whatsappChatAdmin.ajaxUrl);
                // console.log('Form data:', formData);
            },
            success: function(response) {
                // console.log('=== DISPLAY RULES AJAX SUCCESS ===');
                // console.log('Display rules AJAX response:', response);
                if (response.success) {
                    WhatsAppChatAdmin.showSuccessAndRefresh('Display rules saved successfully!');
                } else {
                    WhatsAppChatAdmin.showNotification('error', 'Error saving display rules: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                // console.log('Display rules AJAX error:', error);
                WhatsAppChatAdmin.showNotification('error', 'Network error. Please check your connection and try again.');
            },
            complete: function() {
                $submitBtn.html(originalText);
                $submitBtn.prop('disabled', false);
                $form.removeClass('whatsapp-form-loading');
            }
        });
    });
    
    // Settings form submission is handled by WhatsAppChatAdmin.handleSettingsSubmit
    
    // Direct form submission handlers
    $(document).on('click', '#working-hours-form button[type="submit"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // console.log('Working hours button clicked - submitting form directly');
        
        // Submit form directly instead of triggering
        const $form = $('#working-hours-form');
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        
        // Show loading state
        $submitBtn.html('<span class="dashicons dashicons-update"></span> Saving...');
        $submitBtn.prop('disabled', true);
        $form.addClass('whatsapp-form-loading');
        
        const formData = $form.serialize();
        // console.log('Working hours form data:', formData);
        // console.log('Working hours form fields:');
        $form.find('input, select, textarea').each(function() {
            // console.log('Field:', $(this).attr('name'), 'Value:', $(this).val());
        });
        
        $.ajax({
            url: whatsappChatAdmin.ajaxUrl,
            type: 'POST',
            data: formData + '&action=whatsapp_chat_save_working_hours&nonce=' + whatsappChatAdmin.nonce,
            beforeSend: function() {
                // console.log('Sending working hours AJAX request...');
            },
            success: function(response) {
                // console.log('=== WORKING HOURS AJAX SUCCESS ===');
                // console.log('Working hours AJAX response:', response);
                if (response.success) {
                    WhatsAppChatAdmin.showSuccessAndRefresh('Working hours saved successfully!');
                } else {
                    WhatsAppChatAdmin.showNotification('error', 'Error saving working hours: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                // console.log('Working hours AJAX error:', error);
                WhatsAppChatAdmin.showNotification('error', 'Network error. Please check your connection and try again.');
            },
            complete: function() {
                $submitBtn.html(originalText);
                $submitBtn.prop('disabled', false);
                $form.removeClass('whatsapp-form-loading');
            }
        });
    });
    
    $(document).on('click', '#analytics-settings-form button[type="submit"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // console.log('Analytics button clicked - submitting form directly');
        
        // Submit form directly instead of triggering
        const $form = $('#analytics-settings-form');
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        
        // Show loading state
        $submitBtn.html('<span class="dashicons dashicons-update"></span> Saving...');
        $submitBtn.prop('disabled', true);
        $form.addClass('whatsapp-form-loading');
        
        const formData = $form.serialize();
        // console.log('Analytics form data:', formData);
        // console.log('Analytics form fields:');
        $form.find('input, select, textarea').each(function() {
            // console.log('Field:', $(this).attr('name'), 'Value:', $(this).val());
        });
        
        $.ajax({
            url: whatsappChatAdmin.ajaxUrl,
            type: 'POST',
            data: formData + '&action=whatsapp_chat_save_analytics_settings&nonce=' + whatsappChatAdmin.nonce,
            beforeSend: function() {
                // console.log('Sending analytics AJAX request...');
            },
            success: function(response) {
                // console.log('=== ANALYTICS AJAX SUCCESS ===');
                // console.log('Analytics AJAX response:', response);
                if (response.success) {
                    WhatsAppChatAdmin.showSuccessAndRefresh('Analytics settings saved successfully!');
                } else {
                    WhatsAppChatAdmin.showNotification('error', 'Error saving analytics settings: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                // console.log('Analytics AJAX error:', error);
                WhatsAppChatAdmin.showNotification('error', 'Network error. Please check your connection and try again.');
            },
            complete: function() {
                $submitBtn.html(originalText);
                $submitBtn.prop('disabled', false);
                $form.removeClass('whatsapp-form-loading');
            }
        });
    });
    
    $(document).on('click', '#display-rules-form button[type="submit"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // console.log('Display rules button clicked - submitting form directly');
        
        // Submit form directly instead of triggering
        const $form = $('#display-rules-form');
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        
        // Show loading state
        $submitBtn.html('<span class="dashicons dashicons-update"></span> Saving...');
        $submitBtn.prop('disabled', true);
        $form.addClass('whatsapp-form-loading');
        
        const formData = $form.serialize();
        // console.log('Display rules form data:', formData);
        // console.log('Display rules form fields:');
        $form.find('input, select, textarea').each(function() {
            // console.log('Field:', $(this).attr('name'), 'Value:', $(this).val());
        });
        
        // Debug: Check specific textarea values
        // console.log('Exclude pages value:', $form.find('textarea[name="exclude_pages"]').val());
        // console.log('Include pages value:', $form.find('textarea[name="include_pages"]').val());
        // console.log('Category rules value:', $form.find('textarea[name="category_rules"]').val());
        // console.log('SKU rules value:', $form.find('textarea[name="sku_rules"]').val());
        
        $.ajax({
            url: whatsappChatAdmin.ajaxUrl,
            type: 'POST',
            data: formData + '&action=whatsapp_chat_save_display_rules&nonce=' + whatsappChatAdmin.nonce,
            beforeSend: function() {
                // console.log('Sending display rules AJAX request...');
                // console.log('Display rules form data being sent:', formData);
            },
            success: function(response) {
                // console.log('=== DISPLAY RULES AJAX SUCCESS ===');
                // console.log('Display rules AJAX response:', response);
                if (response.success) {
                    WhatsAppChatAdmin.showSuccessAndRefresh('Display rules saved successfully!');
                } else {
                    WhatsAppChatAdmin.showNotification('error', 'Error saving display rules: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                // console.log('Display rules AJAX error:', error);
                WhatsAppChatAdmin.showNotification('error', 'Network error. Please check your connection and try again.');
            },
            complete: function() {
                $submitBtn.html(originalText);
                $submitBtn.prop('disabled', false);
                $form.removeClass('whatsapp-form-loading');
            }
        });
    });
    
    // Handle design special form submission
    $('#design-special-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: whatsappChatAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'whatsapp_chat_save_design_special',
                nonce: whatsappChatAdmin.nonce,
                ...formData
            },
            success: function(response) {
                if (response.success) {
                    showNotice('Design options saved successfully!', 'success');
                } else {
                    showNotice('Error saving design options: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('Error saving design options', 'error');
            }
        });
    });
    
    // Handle avatar file upload
    $('input[type="file"][name="avatar_file"]').on('change', function() {
        var file = this.files[0];
        var preview = $(this).siblings('.whatsapp-avatar-preview');
        var hiddenInput = $(this).siblings('input[type="hidden"][name="avatar"]');
        
        if (file) {
            // Validate file type
            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showNotice('Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.', 'error');
                return;
            }
            
            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                showNotice('File size too large. Maximum size is 2MB.', 'error');
                return;
            }
            
            // Show preview
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.html('<img src="' + e.target.result + '" alt="Avatar Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">');
            };
            reader.readAsDataURL(file);
            
            // Upload file
            var formData = new FormData();
            formData.append('action', 'whatsapp_chat_upload_avatar');
            formData.append('nonce', whatsappChatAdmin.nonce);
            formData.append('avatar_file', file);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        hiddenInput.val(response.data.url);
                        showNotice('Avatar uploaded successfully!', 'success');
                    } else {
                        showNotice('Error uploading avatar: ' + response.data, 'error');
                    }
                },
                error: function() {
                    showNotice('Error uploading avatar', 'error');
                }
            });
        }
    });

    // Two-Column Layout Interactions
    $(document).ready(function() {
        // Refresh agents list
        $('#refresh-contacts').on('click', function() {
            location.reload();
        });
        
        // Two-column layout interactions
        $('.whatsapp-contacts-left').on('scroll', function() {
            // Add smooth scrolling behavior
            $(this).css('scroll-behavior', 'smooth');
        });
        
        // Form validation for two-column layout
        $('#add-contact-form').on('submit', function(e) {
            e.preventDefault();
            
            // Validate required fields
            var isValid = true;
            $(this).find('input[required]').each(function() {
                if ($(this).val().trim() === '') {
                    $(this).addClass('error');
                    isValid = false;
                } else {
                    $(this).removeClass('error');
                }
            });
            
            if (isValid) {
                // Submit form via AJAX
                submitContactForm();
            } else {
                WhatsAppChatAdmin.showNotification('error', 'Please fill in all required fields');
            }
        });
        
        // Allow specific form submissions for WhatsApp Chat forms
        $('form').on('submit', function(e) {
            // Only prevent default for non-WhatsApp forms
            if (!$(this).hasClass('whatsapp-form') && !$(this).attr('id').includes('whatsapp')) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
        
        // Real-time form validation
        $('#add-contact-form input[required]').on('blur', function() {
            if ($(this).val().trim() === '') {
                $(this).addClass('error');
            } else {
                $(this).removeClass('error');
            }
        });
        
        // Enhanced contact item interactions
        $('.whatsapp-contact-item').on('mouseenter', function() {
            $(this).addClass('hover');
        }).on('mouseleave', function() {
            $(this).removeClass('hover');
        });
        
        // Edit Contact Button
        $(document).on('click', '.edit-contact', function(e) {
            e.preventDefault();
            var contactId = $(this).data('id');
            var firstname = $(this).data('firstname');
            var lastname = $(this).data('lastname');
            var phone = $(this).data('phone');
            var label = $(this).data('label');
            var message = $(this).data('message');
            var avatar = $(this).data('avatar');
            var status = $(this).data('status');
            var nonce = $(this).data('nonce');
            
            // Show edit modal
            showEditContactModal(contactId, firstname, lastname, phone, label, message, avatar, status, nonce);
        });
        
        // Delete Contact Button
        $(document).on('click', '.delete-contact', function(e) {
            e.preventDefault();
            var contactId = $(this).data('id');
            var nonce = $(this).data('nonce');
            
            if (confirm('Are you sure you want to delete this contact?')) {
                deleteContact(contactId, nonce);
            }
        });
    });

    // Show Edit Contact Modal
    function showEditContactModal(contactId, firstname, lastname, phone, label, message, avatar, status, nonce) {
        // Create modal HTML
        var modalHtml = `
            <div id="edit-contact-modal" class="whatsapp-modal" style="display: none;">
                <div class="whatsapp-modal-content">
                    <div class="whatsapp-modal-header">
                        <h3 class="whatsapp-modal-title">
                            <span class="dashicons dashicons-edit"></span>
                            Edit Contact
                        </h3>
                        <button type="button" class="whatsapp-modal-close">
                            <span class="dashicons dashicons-no"></span>
                        </button>
                    </div>
                    <div class="whatsapp-modal-body">
                        <form id="edit-contact-form">
                            <input type="hidden" name="contact_id" value="${contactId}">
                            <input type="hidden" name="nonce" value="${nonce}">
                            
                            <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                    <label for="edit_firstname">First Name *</label>
                                    <input type="text" id="edit_firstname" name="firstname" class="whatsapp-form-control" value="${firstname}" required>
                                </div>
                                <div class="whatsapp-form-group">
                                    <label for="edit_lastname">Last Name *</label>
                                    <input type="text" id="edit_lastname" name="lastname" class="whatsapp-form-control" value="${lastname}" required>
                                </div>
                            </div>
                            
                            <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                    <label for="edit_phone">Phone Number *</label>
                                    <input type="text" id="edit_phone" name="phone" class="whatsapp-form-control" value="${phone}" required>
                                </div>
                                <div class="whatsapp-form-group">
                                    <label for="edit_label">Label</label>
                                    <input type="text" id="edit_label" name="label" class="whatsapp-form-control" value="${label}">
                                </div>
                            </div>
                            
                            <div class="whatsapp-form-group">
                                <label for="edit_message">Message</label>
                                <textarea id="edit_message" name="message" class="whatsapp-form-control" rows="3">${message}</textarea>
                            </div>
                            
                            <div class="whatsapp-form-group">
                                <label class="whatsapp-toggle-switch">
                                    <input type="checkbox" id="edit_status" name="status" value="1" ${status == '1' ? 'checked' : ''}>
                                    <span class="toggle-label">Active Contact</span>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </form>
                    </div>
                    <div class="whatsapp-modal-footer">
                        <button type="button" class="whatsapp-btn whatsapp-btn-secondary" id="cancel-edit">Cancel</button>
                        <button type="button" class="whatsapp-btn whatsapp-btn-primary" id="save-edit">Save Changes</button>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#edit-contact-modal').remove();
        
        // Add modal to body
        $('body').append(modalHtml);
        
        // Show modal with animation
        setTimeout(function() {
            $('#edit-contact-modal').addClass('show').fadeIn(300).css({
                'display': 'flex',
                'align-items': 'center',
                'justify-content': 'center'
            });
            
            // Set the status checkbox correctly
            if (status == '1') {
                $('#edit_status').prop('checked', true);
                // console.log('Setting status to checked');
            } else {
                $('#edit_status').prop('checked', false);
                // console.log('Setting status to unchecked');
            }
            
            // Log the current status value
            // console.log('Current status value from data:', status);
            // console.log('Checkbox checked after setting:', $('#edit_status').is(':checked'));
            
            // Force update the toggle switch visual state
            setTimeout(function() {
                var isChecked = $('#edit_status').is(':checked');
                // console.log('Status checkbox is checked:', isChecked);
                
                // Trigger change event to update visual state
                $('#edit_status').trigger('change');
                
                // Force visual update by toggling classes
                var toggleSwitch = $('#edit_status').closest('.whatsapp-toggle-switch');
                if (isChecked) {
                    toggleSwitch.addClass('active');
                } else {
                    toggleSwitch.removeClass('active');
                }
            }, 100);
        }, 10);
        
        // Bind toggle switch click event
        $('#edit-contact-modal .whatsapp-toggle-switch').on('click', function(e) {
            e.preventDefault();
            const checkbox = $(this).find('input[type="checkbox"]');
            checkbox.prop('checked', !checkbox.prop('checked'));
            checkbox.trigger('change');
            // Toggle switch clicked
        });
        
        // Bind events
        $('#edit-contact-modal .whatsapp-modal-close, #cancel-edit').on('click', function() {
            $('#edit-contact-modal').fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        // Bind Update Contact button
        $('#edit-contact-modal .whatsapp-btn-primary').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            saveEditContact(contactId);
        });
        
        // Also bind to form submit
        $('#edit-contact-form').on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            saveEditContact(contactId);
        });
        
        // Initialize avatar upload for edit modal
        WhatsAppChatAdmin.initAvatarUpload();
        
        
        // Bind avatar upload button
        $(document).on('click', '#edit-upload-avatar-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            // console.log('Upload avatar button clicked');
            // console.log('File input element:', $('#edit-avatar-file'));
            $('#edit-avatar-file').click();
        });
        
        $(document).on('change', '#edit-avatar-file', function() {
            // console.log('Avatar file changed');
            var file = this.files[0];
            if (file) {
                // console.log('File selected:', file.name);
                var reader = new FileReader();
                reader.onload = function(e) {
                    // console.log('File loaded, setting preview');
                    $('#edit-avatar-preview-img').attr('src', e.target.result);
                    $('#edit-avatar-preview').show();
                    $('#edit-avatar-url').val(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });
        
        $(document).on('click', '#edit-remove-avatar', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#edit-avatar-preview').hide();
            $('#edit-avatar-url').val('');
        });
        
        $(document).on('click', '#edit-media-library-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            // console.log('Media library button clicked');
            
            if (typeof wp !== 'undefined' && wp.media) {
                // console.log('WordPress media library available');
                var frame = wp.media({
                    title: 'Select Avatar',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                frame.on('select', function() {
                    // console.log('Media selection made');
                    var attachment = frame.state().get('selection').first().toJSON();
                    // console.log('Media selected:', attachment.url);
                    $('#edit-avatar-preview-img').attr('src', attachment.url);
                    $('#edit-avatar-preview').show();
                    $('#edit-avatar-url').val(attachment.url);
                    frame.close();
                });
                
                frame.on('close', function() {
                    // console.log('Media frame closed');
                });
                
                frame.open();
            } else {
                // console.log('WordPress media library not available');
            }
        });
    }
    
    // Save Edit Contact
    function saveEditContact(contactId) {
        var form = $('#edit-contact-form');
        var submitBtn = $('#edit-contact-modal .whatsapp-btn-primary');
        
        submitBtn.html('<span class="dashicons dashicons-update"></span> Saving...');
        submitBtn.prop('disabled', true);
        
        // Add nonce for security
        var nonce = $('input[name="nonce"]').val();
        
        
        // Debug the status value
        var statusValue = $('#edit_status').is(':checked') ? 1 : 0;
        // console.log('Saving contact with status:', statusValue);
        // console.log('Checkbox element:', $('#edit_status'));
        // console.log('Checkbox checked:', $('#edit_status').is(':checked'));
        // console.log('Status value being sent:', statusValue);
        
        $.ajax({
            url: whatsappChatAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'whatsapp_chat_save_contact',
                id: contactId,
                nonce: nonce,
                firstname: $('#edit_firstname').val(),
                lastname: $('#edit_lastname').val(),
                phone: $('#edit_phone').val(),
                label: $('#edit_label').val(),
                message: $('#edit_message').val(),
                avatar: $('#edit-avatar-url').val(),
                status: statusValue
            },
            success: function(response) {
                if (response.success) {
                    WhatsAppChatAdmin.showNotification('success', 'Contact updated successfully!');
                    $('#edit-contact-modal').fadeOut(300, function() {
                        $(this).remove();
                        location.reload();
                    });
                } else {
                    WhatsAppChatAdmin.showNotification('error', response.data);
                }
            },
            error: function() {
                WhatsAppChatAdmin.showNotification('error', 'Error updating contact');
            },
            complete: function() {
                submitBtn.html('<span class="dashicons dashicons-saved"></span> Update Contact');
                submitBtn.prop('disabled', false);
            }
        });
    }

    /**
     * Hide WordPress notices
     */
    function hideWordPressNotices() {
        // Hide all WordPress notices
        const notices = document.querySelectorAll('.notice, .notice-success, .notice-info, .notice-warning, .notice-error, .settings-error, .e-notice, .cs-notice, #setting-error-tgmpa');
        notices.forEach(notice => {
            notice.style.display = 'none';
        });
        
        // Also hide notices that might appear later
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        if (node.classList && (node.classList.contains('notice') || 
                            node.classList.contains('notice-success') || 
                            node.classList.contains('notice-info') || 
                            node.classList.contains('notice-warning') || 
                            node.classList.contains('notice-error') || 
                            node.classList.contains('settings-error') || 
                            node.classList.contains('e-notice') || 
                            node.classList.contains('cs-notice') || 
                            node.id === 'setting-error-tgmpa')) {
                            node.style.display = 'none';
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Call hideWordPressNotices when DOM is ready
    $(document).ready(function() {
        hideWordPressNotices();
        initFloatingMessageSettings();
    });
    
    /**
     * Initialize Floating Message Settings
     */
    function initFloatingMessageSettings() {
        const floatingMessageInput = document.getElementById('floating-message-input');
        const charCount = document.getElementById('char-count');
        
        if (floatingMessageInput && charCount) {
            // Update character count on input
            floatingMessageInput.addEventListener('input', function() {
                const currentLength = this.value.length;
                charCount.textContent = currentLength;
                
                // Change color based on character count
                if (currentLength > 25) {
                    charCount.style.color = '#ff4444';
                } else if (currentLength > 20) {
                    charCount.style.color = '#ff8800';
                } else {
                    charCount.style.color = '#25D366';
                }
            });
            
            // Initialize character count
            charCount.textContent = floatingMessageInput.value.length;
        }
    }
    
    // Delete Contact function removed - handled by WhatsAppChatAdmin.deleteContact

})(jQuery);