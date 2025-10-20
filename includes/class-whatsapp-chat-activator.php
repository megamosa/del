<?php
/**
 * Plugin activation class
 * 
 * @package WhatsApp_Chat_Pro
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    wp_die(__('Direct access not allowed.', 'whatsapp-chat-pro'));
}

/**
 * Plugin activator class
 */
class WhatsApp_Chat_Activator {
    
    /**
     * Activate plugin
     */
    public static function activate() {
        // Check if this is a multisite network
        if (is_multisite()) {
            // Network activation
            self::activate_network();
        } else {
            // Single site activation
            self::activate_single_site();
        }
    }
    
    /**
     * Activate plugin for single site
     */
    private static function activate_single_site() {
        // Create database tables
        self::create_tables();
        
        // Set default options
        self::set_default_options();
        
        // Create upload directories
        self::create_upload_directories();
        
        // Initialize default designs
        WhatsApp_Chat_Database::init_default_designs();
        
        // Initialize default departments and working hours
        WhatsApp_Chat_Database::init_default_departments();
        WhatsApp_Chat_Database::init_default_working_hours();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Activate plugin for multisite network
     */
    private static function activate_network() {
        // Get all sites in the network
        $sites = get_sites();
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            // Create database tables for this site
            self::create_tables();
            
            // Set default options for this site
            self::set_default_options();
            
            // Create upload directories for this site
            self::create_upload_directories();
            
            // Initialize default designs for this site
            WhatsApp_Chat_Database::init_default_designs();
            
            // Initialize default departments and working hours for this site
            WhatsApp_Chat_Database::init_default_departments();
            WhatsApp_Chat_Database::init_default_working_hours();
            
            restore_current_blog();
        }
        
        // Flush rewrite rules for the network
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables
     */
    private static function create_tables() {
        // Use the database class method
        WhatsApp_Chat_Database::create_tables();
    }
    
    /**
     * Set default options
     */
    private static function set_default_options() {
        // General settings
        add_option('whatsapp_chat_enabled', '1');
        add_option('whatsapp_chat_phone_number', '+1234567890');
        add_option('whatsapp_chat_default_message', 'Hello! I have a question about your products.');
        
        // Display settings
        add_option('whatsapp_chat_button_position', 'bottom-right');
        add_option('whatsapp_chat_button_layout', 'button');
        add_option('whatsapp_chat_show_on_mobile', '1');
        add_option('whatsapp_chat_show_on_desktop', '1');
        add_option('whatsapp_chat_design', 'design-1');
        add_option('whatsapp_chat_enable_agents', '0');
        
        // Widget texts
        add_option('whatsapp_chat_header_text', 'Chat with us');
        add_option('whatsapp_chat_footer_text', "We're here to help!");
        add_option('whatsapp_chat_support_team_name', 'Support Team');
        add_option('whatsapp_chat_support_team_label', 'Customer Support');
        add_option('whatsapp_chat_no_agents_message', 'No agents are currently available. Please try again later or contact us directly.');
        add_option('whatsapp_chat_offline_message', 'Our chat is currently offline. Please contact us during business hours.');
        
        // Advanced settings - Working hours DISABLED by default for better UX
        add_option('whatsapp_chat_time_restrictions', '0');
        add_option('whatsapp_chat_working_hours_enabled', '0'); // DISABLED by default
        add_option('whatsapp_chat_start_time', '09:00');
        add_option('whatsapp_chat_end_time', '18:00');
        add_option('whatsapp_chat_enable_analytics', '0');
        add_option('whatsapp_chat_ga4_measurement_id', '');
        add_option('whatsapp_chat_analytics_event_name', 'whatsapp_click');
        add_option('whatsapp_chat_button_animation', 'none');
        add_option('whatsapp_chat_button_x', '20');
        add_option('whatsapp_chat_button_y', '20');
        
        // License settings
        add_option('whatsapp_chat_license_key', '');
        add_option('whatsapp_chat_license_status', 'inactive');
        add_option('whatsapp_chat_license_expires', '');
    }
    
    /**
     * Create upload directories
     */
    private static function create_upload_directories() {
        $upload_dir = wp_upload_dir();
        $whatsapp_dir = $upload_dir['basedir'] . '/whatsapp-chat';
        $avatars_dir = $whatsapp_dir . '/avatars';
        
        // Create directories if they don't exist
        if (!file_exists($whatsapp_dir)) {
            wp_mkdir_p($whatsapp_dir);
        }
        
        if (!file_exists($avatars_dir)) {
            wp_mkdir_p($avatars_dir);
        }
        
        // Create .htaccess for security
        $htaccess_content = "Options -Indexes\n";
        $htaccess_content .= "deny from all\n";
        $htaccess_content .= "<Files ~ \"\\.(jpg|jpeg|png|gif|webp)$\">\n";
        $htaccess_content .= "    allow from all\n";
        $htaccess_content .= "</Files>\n";
        
        file_put_contents($avatars_dir . '/.htaccess', $htaccess_content);
    }
}
