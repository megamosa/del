<?php
/**
 * Plugin deactivation class
 * 
 * @package WhatsApp_Chat_Pro
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    wp_die(__('Direct access not allowed.', 'whatsapp-chat-pro'));
}

/**
 * Plugin deactivator class
 */
class WhatsApp_Chat_Deactivator {
    
    /**
     * Deactivate plugin
     */
    public static function deactivate() {
        // Check if this is a multisite network
        if (is_multisite()) {
            // Network deactivation
            self::deactivate_network();
        } else {
            // Single site deactivation
            self::deactivate_single_site();
        }
    }
    
    /**
     * Deactivate plugin for single site
     */
    private static function deactivate_single_site() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Clear scheduled events
        wp_clear_scheduled_hook('whatsapp_chat_license_check');
        
        // Clear transients
        delete_transient('whatsapp_chat_license_status');
        delete_transient('whatsapp_chat_license_data');
        
        // Note: We don't drop tables on deactivation to preserve user data
        // Tables will only be dropped on uninstall
    }
    
    /**
     * Deactivate plugin for multisite network
     */
    private static function deactivate_network() {
        // Get all sites in the network
        $sites = get_sites();
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            // Clear scheduled events for this site
            wp_clear_scheduled_hook('whatsapp_chat_license_check');
            
            // Clear transients for this site
            delete_transient('whatsapp_chat_license_status');
            delete_transient('whatsapp_chat_license_data');
            
            restore_current_blog();
        }
        
        // Flush rewrite rules for the network
        flush_rewrite_rules();
    }
}
