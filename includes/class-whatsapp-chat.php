<?php
/**
 * Main plugin class
 * 
 * @package WhatsApp_Chat_Pro
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    wp_die(__('Direct access not allowed.', 'whatsapp-chat-pro'));
}

/**
 * Main WhatsApp Chat class
 */
class WhatsApp_Chat {
    
    /**
     * Plugin version
     */
    const VERSION = '1.0.0';
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Load required files
        $this->load_dependencies();
        
        // Initialize hooks
        $this->init_hooks();
    }
    
    /**
     * Load required files
     */
    private function load_dependencies() {
        // Core classes
        require_once WHATSAPP_CHAT_PLUGIN_DIR . 'includes/class-whatsapp-chat-activator.php';
        require_once WHATSAPP_CHAT_PLUGIN_DIR . 'includes/class-whatsapp-chat-deactivator.php';
        require_once WHATSAPP_CHAT_PLUGIN_DIR . 'includes/class-whatsapp-chat-database.php';
        require_once WHATSAPP_CHAT_PLUGIN_DIR . 'includes/class-whatsapp-chat-license.php';
        require_once WHATSAPP_CHAT_PLUGIN_DIR . 'includes/class-whatsapp-chat-ajax.php';
        
        // Admin classes
        if (is_admin()) {
            require_once WHATSAPP_CHAT_PLUGIN_DIR . 'admin/class-whatsapp-chat-admin.php';
            require_once WHATSAPP_CHAT_PLUGIN_DIR . 'admin/class-whatsapp-chat-settings.php';
            require_once WHATSAPP_CHAT_PLUGIN_DIR . 'admin/class-whatsapp-chat-contacts.php';
        }
        
        // Public classes
        require_once WHATSAPP_CHAT_PLUGIN_DIR . 'public/class-whatsapp-chat-public.php';
        require_once WHATSAPP_CHAT_PLUGIN_DIR . 'public/class-whatsapp-chat-widget.php';
        require_once WHATSAPP_CHAT_PLUGIN_DIR . 'public/class-whatsapp-chat-shortcode.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Plugin activation/deactivation
        register_activation_hook(WHATSAPP_CHAT_PLUGIN_FILE, array('WhatsApp_Chat_Activator', 'activate'));
        register_deactivation_hook(WHATSAPP_CHAT_PLUGIN_FILE, array('WhatsApp_Chat_Deactivator', 'deactivate'));
        
        // Initialize components
        add_action('init', array($this, 'init_components'));
        
        // Load text domain
        add_action('init', array($this, 'load_textdomain'));
    }
    
    /**
     * Initialize components
     */
    public function init_components() {
        // Initialize database
        new WhatsApp_Chat_Database();
        
        // Initialize license system
        new WhatsApp_Chat_License();
        
        // Initialize AJAX handlers
        new WhatsApp_Chat_Ajax();
        
        // Initialize admin
        if (is_admin()) {
            new WhatsApp_Chat_Admin();
        }
        
        // Initialize public
        new WhatsApp_Chat_Public();
        
        // Register widget
        add_action('widgets_init', array($this, 'register_widget'));
        
        // Register shortcode
        add_action('init', array($this, 'register_shortcode'));
    }
    
    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('whatsapp-chat-pro', false, dirname(plugin_basename(WHATSAPP_CHAT_PLUGIN_FILE)) . '/languages');
        
        // WPML compatibility
        if (function_exists('wpml_register_single_string')) {
            $this->register_wpml_strings();
        }
    }
    
    /**
     * Register strings with WPML
     */
    private function register_wpml_strings() {
        // Register all translatable strings with WPML
        $strings = array(
            'WhatsApp Chat Pro',
            'Professional WhatsApp chat widget Multi Agent Support for your website',
            'Dashboard',
            'Settings',
            'Designs',
            'Agents',
            'License',
            'Upgrade to Pro',
            'Upgrade Now - $24 Only',
            'Unlock premium features with WhatsApp Chat Pro:',
            'Unlimited Agents',
            'Premium Designs',
            'Advanced Analytics',
            'Priority Support',
            'WhatsApp Chat Settings',
            'Configure your WhatsApp chat widget',
            'General',
            'Display',
            'Advanced',
            'WhatsApp Chat Contacts',
            'Contacts',
            'WhatsApp Chat Designs',
            'Choose from 7 beautiful designs for your WhatsApp chat widget',
            'WhatsApp Chat Pro License',
            'Activate your license to unlock premium features',
            'Welcome!',
            'Need Help?',
            'Need Help? Chat via',
            'WhatsApp',
            'New message notification',
            'Saving...',
            'Settings saved!',
            'Error saving settings!',
            'Are you sure you want to delete this contact?',
            'You do not have sufficient permissions to access this page.',
            'Security check failed',
            'Insufficient permissions'
        );
        
        foreach ($strings as $string) {
            wpml_register_single_string('whatsapp-chat-pro', $string, $string);
        }
    }
    
    /**
     * Register widget
     */
    public function register_widget() {
        register_widget('WhatsApp_Chat_Widget');
    }
    
    /**
     * Register shortcode
     */
    public function register_shortcode() {
        new WhatsApp_Chat_Shortcode();
    }
}
