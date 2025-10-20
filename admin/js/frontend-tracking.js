/**
 * WhatsApp Chat Pro - Frontend Tracking
 * Lightweight click tracking for WhatsApp buttons
 * 
 * @package WhatsApp_Chat_Pro
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * WhatsApp Frontend Tracking
     */
    const WhatsAppTracking = {
        isTracking: false,
        ajaxUrl: '',
        nonce: '',

        /**
         * Initialize tracking
         */
        init: function() {
            // Get AJAX URL and nonce from localized script
            if (typeof whatsappChatAdmin !== 'undefined') {
                this.ajaxUrl = whatsappChatAdmin.ajaxUrl;
                this.nonce = whatsappChatAdmin.nonce;
            } else {
                // Fallback for frontend
                this.ajaxUrl = ajaxurl || '/wp-admin/admin-ajax.php';
                this.nonce = '';
            }

            this.bindEvents();
            // Frontend tracking initialized
        },

        /**
         * Bind click events
         */
        bindEvents: function() {
            // Track clicks on WhatsApp button (actual button class)
            $(document).on('click', '.whatsapp-button', function(e) {
                // Button clicked, tracking
                WhatsAppTracking.trackClick();
            });

            // Track clicks on WhatsApp chat widget
            $(document).on('click', '.whatsapp-chat-widget', function(e) {
                // Widget clicked, tracking
                WhatsAppTracking.trackClick();
            });

            // Track clicks on WhatsApp contact buttons
            $(document).on('click', '.whatsapp-contact', function(e) {
                // Contact clicked, tracking
                WhatsAppTracking.trackClick();
            });

            // Track clicks on WhatsApp links
            $(document).on('click', 'a[href*="wa.me"], a[href*="whatsapp.com"], a[href*="api.whatsapp.com"]', function(e) {
                // Check if it's our WhatsApp button or widget
                if ($(this).hasClass('whatsapp-button') || 
                    $(this).closest('.whatsapp-chat-widget').length > 0 ||
                    $(this).closest('.whatsapp-contact').length > 0) {
                    // WhatsApp link clicked, tracking
                    WhatsAppTracking.trackClick();
                }
            });

            // Track clicks on any element with data-whatsapp attributes
            $(document).on('click', '[data-whatsapp]', function(e) {
                // WhatsApp element clicked, tracking
                WhatsAppTracking.trackClick();
            });
        },

        /**
         * Track click
         */
        trackClick: function() {
            // Prevent multiple tracking
            if (this.isTracking) {
                // Already tracking, skipping
                return;
            }

            this.isTracking = true;
            // Starting click tracking

            // Send AJAX request
            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'whatsapp_chat_track_click',
                    nonce: this.nonce,
                    timestamp: Date.now(),
                    user_agent: navigator.userAgent,
                    page_url: window.location.href
                },
                success: function(response) {
                    if (response.success) {
                        // Click tracked successfully
                    } else {
                        // Tracking failed
                    }
                },
                error: function(xhr, status, error) {
                    // Error tracking click
                },
                complete: function() {
                    // Reset tracking flag
                    setTimeout(function() {
                        WhatsAppTracking.isTracking = false;
                        // Tracking flag reset
                    }, 1000);
                }
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        WhatsAppTracking.init();
    });

})(jQuery);
