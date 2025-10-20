<?php
/**
 * License management class
 * 
 * @package WhatsApp_Chat_Pro
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    wp_die(__('Direct access not allowed.', 'whatsapp-chat-pro'));
}

/**
 * License management class
 */
class WhatsApp_Chat_License {
    
    /**
     * License API URL
     */
    const LICENSE_API_URL = 'https://api.magoarab.com/whatsapp-chat-pro/';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Handle license activation/deactivation
        add_action('admin_init', array($this, 'handle_license_actions'));
        
        // Schedule license check
        add_action('wp', array($this, 'schedule_license_check'));
        add_action('whatsapp_chat_license_check', array($this, 'check_license_status'));
        
        // Add license status to admin bar
        add_action('admin_bar_menu', array($this, 'add_license_status_to_admin_bar'), 100);
        
        // Multisite compatibility
        if (is_multisite()) {
            add_action('wpmu_new_blog', array($this, 'new_blog_created'));
            add_action('delete_blog', array($this, 'blog_deleted'));
        }
    }
    
    /**
     * Handle new blog creation in multisite
     */
    public function new_blog_created($blog_id) {
        switch_to_blog($blog_id);
        
        // Set default license options for new blog
        $this->set_default_license_options();
        
        restore_current_blog();
    }
    
    /**
     * Handle blog deletion in multisite
     */
    public function blog_deleted($blog_id) {
        switch_to_blog($blog_id);
        
        // Clean up license options for deleted blog
        $this->cleanup_license_options();
        
        restore_current_blog();
    }
    
    /**
     * Set default license options
     */
    private function set_default_license_options() {
        // Set default license status
        if (!get_option('whatsapp_chat_license_status')) {
            update_option('whatsapp_chat_license_status', 'inactive');
        }
        
        // Set default license key
        if (!get_option('whatsapp_chat_license_key')) {
            update_option('whatsapp_chat_license_key', '');
        }
        
        // Set default license expires
        if (!get_option('whatsapp_chat_license_expires')) {
            update_option('whatsapp_chat_license_expires', '');
        }
    }
    
    /**
     * Cleanup license options
     */
    private function cleanup_license_options() {
        // Remove license options
        delete_option('whatsapp_chat_license_status');
        delete_option('whatsapp_chat_license_key');
        delete_option('whatsapp_chat_license_expires');
        
        // Clear license transients
        delete_transient('whatsapp_chat_license_status');
        delete_transient('whatsapp_chat_license_data');
    }
    
    /**
     * Handle license actions
     */
    public function handle_license_actions() {
        if (!isset($_POST['whatsapp_chat_license_nonce']) || !wp_verify_nonce($_POST['whatsapp_chat_license_nonce'], 'whatsapp_chat_license')) {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $action = sanitize_text_field($_POST['action']);
        $license_key = sanitize_text_field($_POST['whatsapp_chat_license_key']);
        
        switch ($action) {
            case 'activate':
                $this->activate_license($license_key);
                break;
            case 'deactivate':
                $this->deactivate_license();
                break;
        }
    }
    
    /**
     * Activate license
     */
    public function activate_license($license_key) {
        $response = $this->make_api_request('activate', array(
            'license_key' => $license_key,
            'site_url' => home_url(),
            'plugin_version' => WHATSAPP_CHAT_VERSION
        ));
        
        if ($response && $response['success']) {
            update_option('whatsapp_chat_license_key', $license_key);
            update_option('whatsapp_chat_license_status', 'active');
            update_option('whatsapp_chat_license_expires', $response['data']['expires']);
            
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>' . __('License activated successfully!', 'whatsapp-chat-pro') . '</p>';
                echo '</div>';
            });
        } else {
            update_option('whatsapp_chat_license_status', 'inactive');
            
            add_action('admin_notices', function() use ($response) {
                $message = $response ? $response['message'] : __('Failed to activate license. Please check your license key.', 'whatsapp-chat-pro');
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>' . $message . '</p>';
                echo '</div>';
            });
        }
    }
    
    /**
     * Deactivate license
     */
    public function deactivate_license() {
        $license_key = get_option('whatsapp_chat_license_key', '');
        
        if ($license_key) {
            $response = $this->make_api_request('deactivate', array(
                'license_key' => $license_key,
                'site_url' => home_url()
            ));
        }
        
        update_option('whatsapp_chat_license_key', '');
        update_option('whatsapp_chat_license_status', 'inactive');
        update_option('whatsapp_chat_license_expires', '');
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . __('License deactivated successfully!', 'whatsapp-chat-pro') . '</p>';
            echo '</div>';
        });
    }
    
    /**
     * Check license status
     */
    public function check_license_status() {
        $license_key = get_option('whatsapp_chat_license_key', '');
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        
        if (!$license_key || $license_status !== 'active') {
            return;
        }
        
        $response = $this->make_api_request('check', array(
            'license_key' => $license_key,
            'site_url' => home_url(),
            'plugin_version' => WHATSAPP_CHAT_VERSION
        ));
        
        if ($response && $response['success']) {
            update_option('whatsapp_chat_license_status', 'active');
            update_option('whatsapp_chat_license_expires', $response['data']['expires']);
        } else {
            update_option('whatsapp_chat_license_status', 'inactive');
        }
    }
    
    /**
     * Schedule license check
     */
    public function schedule_license_check() {
        if (!wp_next_scheduled('whatsapp_chat_license_check')) {
            wp_schedule_event(time(), 'daily', 'whatsapp_chat_license_check');
        }
    }
    
    /**
     * Make API request
     */
    private function make_api_request($action, $data) {
        $url = self::LICENSE_API_URL . $action;
        
        $args = array(
            'method' => 'POST',
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($data)
        );
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
    
    /**
     * Check if license is active
     */
    public static function is_license_active() {
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        return $license_status === 'active';
    }
    
    /**
     * Check if feature is premium
     */
    public static function is_premium_feature($feature) {
        $premium_features = array(
            'design-4',
            'design-5', 
            'design-6',
            'design-7',
            'multi_agents',
            'unlimited_agents',
            'custom_colors',
            'advanced_analytics',
            'priority_support'
        );
        
        return in_array($feature, $premium_features);
    }
    
    /**
     * Check if user can access premium feature
     */
    public static function can_access_premium_feature($feature) {
        if (!self::is_premium_feature($feature)) {
            return true;
        }
        
        return self::is_license_active();
    }
    
    /**
     * Get license info
     */
    public static function get_license_info() {
        return array(
            'key' => get_option('whatsapp_chat_license_key', ''),
            'status' => get_option('whatsapp_chat_license_status', 'inactive'),
            'expires' => get_option('whatsapp_chat_license_expires', '')
        );
    }
    
    /**
     * Add license status to admin bar
     */
    public function add_license_status_to_admin_bar($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $license_info = self::get_license_info();
        $status = $license_info['status'];
        
        $wp_admin_bar->add_node(array(
            'id' => 'whatsapp-chat-license',
            'title' => sprintf(
                '<span class="ab-icon dashicons-whatsapp"></span> <span class="ab-label">WhatsApp Chat: %s</span>',
                ucfirst($status)
            ),
            'href' => admin_url('admin.php?page=whatsapp-chat-license'),
            'meta' => array(
                'class' => $status === 'active' ? 'license-active' : 'license-inactive'
            )
        ));
    }
    
    /**
     * Get premium features list
     */
    public static function get_premium_features() {
        return array(
            'design-4' => array(
                'name' => __('Design 4 - Gradient Modern', 'whatsapp-chat-pro'),
                'description' => __('Modern gradient design with smooth animations', 'whatsapp-chat-pro')
            ),
            'design-5' => array(
                'name' => __('Design 5 - Bright Vibrant (Red)', 'whatsapp-chat-pro'),
                'description' => __('Bright vibrant red design for high visibility', 'whatsapp-chat-pro')
            ),
            'design-6' => array(
                'name' => __('Design 6 - Professional Banner', 'whatsapp-chat-pro'),
                'description' => __('Professional banner design with avatar support', 'whatsapp-chat-pro')
            ),
            'design-7' => array(
                'name' => __('Design 7 - Custom Avatar Professional', 'whatsapp-chat-pro'),
                'description' => __('Custom avatar professional design with speech bubble', 'whatsapp-chat-pro')
            ),
            'custom_colors' => array(
                'name' => __('Custom Colors', 'whatsapp-chat-pro'),
                'description' => __('Unlimited custom color customization', 'whatsapp-chat-pro')
            ),
            'advanced_analytics' => array(
                'name' => __('Advanced Analytics', 'whatsapp-chat-pro'),
                'description' => __('Advanced Google Analytics 4 integration', 'whatsapp-chat-pro')
            ),
            'priority_support' => array(
                'name' => __('Priority Support', 'whatsapp-chat-pro'),
                'description' => __('Priority email support and updates', 'whatsapp-chat-pro')
            )
        );
    }
    
    /**
     * Show premium feature notice
     */
    public static function show_premium_notice($feature) {
        if (self::can_access_premium_feature($feature)) {
            return;
        }
        
        $premium_features = self::get_premium_features();
        $feature_info = isset($premium_features[$feature]) ? $premium_features[$feature] : array('name' => $feature, 'description' => '');
        
        echo '<div class="premium-feature-notice">';
        echo '<div class="premium-badge">Premium</div>';
        echo '<h4>' . $feature_info['name'] . '</h4>';
        echo '<p>' . $feature_info['description'] . '</p>';
        echo '<p><a href="' . admin_url('admin.php?page=whatsapp-chat-license') . '" class="button button-primary">' . __('Activate License', 'whatsapp-chat-pro') . '</a></p>';
        echo '</div>';
    }
}
