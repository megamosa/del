<?php
/**
 * AJAX handlers
 * 
 * @package WhatsApp_Chat_Pro
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    wp_die(__('Direct access not allowed.', 'whatsapp-chat-pro'));
}

/**
 * AJAX handlers class
 */
class WhatsApp_Chat_Ajax {
    
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
        // Public AJAX handlers
        add_action('wp_ajax_whatsapp_chat_get_contacts', array($this, 'get_contacts'));
        add_action('wp_ajax_nopriv_whatsapp_chat_get_contacts', array($this, 'get_contacts'));
        
        // Admin AJAX handlers
        add_action('wp_ajax_whatsapp_chat_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_whatsapp_chat_save_contact', array($this, 'save_contact'));
        add_action('wp_ajax_whatsapp_chat_get_contact', array($this, 'get_contact'));
        add_action('wp_ajax_whatsapp_chat_delete_contact', array($this, 'delete_contact'));
        add_action('wp_ajax_whatsapp_chat_validate_license', array($this, 'validate_license'));
        add_action('wp_ajax_whatsapp_chat_upload_file', array($this, 'upload_file'));
        add_action('wp_ajax_whatsapp_chat_get_stats', array($this, 'get_stats'));
    }
    
    /**
     * Get contacts
     */
    public function get_contacts() {
        check_ajax_referer('whatsapp_chat_widget_nonce', 'nonce');
        
        $contacts = WhatsApp_Chat_Database::get_contacts(array('status' => 1));
        
        if (empty($contacts)) {
            // Fallback to main contact
            $contacts = array((object) array(
                'id' => 1,
                'firstname' => get_option('whatsapp_chat_support_team_name', 'Support Team'),
                'lastname' => '',
                'phone' => get_option('whatsapp_chat_phone_number', '+1234567890'),
                'message' => get_option('whatsapp_chat_default_message', 'Hello! I have a question.'),
                'label' => get_option('whatsapp_chat_support_team_label', 'Customer Support'),
                'avatar' => '',
                'status' => 1,
                'sort_order' => 1
            ));
        }
        
        wp_send_json_success($contacts);
    }
    
    /**
     * Save settings
     */
    public function save_settings() {
        check_ajax_referer('whatsapp_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
        }
        
        $settings = array(
            'whatsapp_chat_enabled',
            'whatsapp_chat_phone_number',
            'whatsapp_chat_default_message',
            'whatsapp_chat_enable_agents',
            'whatsapp_chat_button_position',
            'whatsapp_chat_button_layout',
            'whatsapp_chat_show_on_mobile',
            'whatsapp_chat_show_on_desktop',
            'whatsapp_chat_design',
            'whatsapp_chat_header_text',
            'whatsapp_chat_footer_text',
            'whatsapp_chat_support_team_name',
            'whatsapp_chat_support_team_label',
            'whatsapp_chat_no_agents_message',
            'whatsapp_chat_offline_message',
            'whatsapp_chat_time_restrictions',
            'whatsapp_chat_start_time',
            'whatsapp_chat_end_time',
            'whatsapp_chat_enable_analytics',
            'whatsapp_chat_ga4_measurement_id',
            'whatsapp_chat_analytics_event_name'
        );
        
        $updated = 0;
        foreach ($settings as $setting) {
            if (isset($_POST[$setting])) {
                $value = sanitize_text_field($_POST[$setting]);
                if (update_option($setting, $value)) {
                    $updated++;
                }
            }
        }
        
        if ($updated > 0) {
            wp_send_json_success(__('Settings saved successfully!', 'whatsapp-chat-pro'));
        } else {
            wp_send_json_error(__('No settings were updated.', 'whatsapp-chat-pro'));
        }
    }
    
    /**
     * Save contact
     */
    public function save_contact() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'whatsapp_chat_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
        }
        
        // Debug status value
        error_log('WhatsApp Chat Debug - Status value: ' . (isset($_POST['status']) ? $_POST['status'] : 'not set'));
        error_log('WhatsApp Chat Debug - Status check: ' . (isset($_POST['status']) && $_POST['status'] == '1' ? '1' : '0'));
        
        $data = array(
            'firstname' => isset($_POST['firstname']) ? sanitize_text_field($_POST['firstname']) : '',
            'lastname' => isset($_POST['lastname']) ? sanitize_text_field($_POST['lastname']) : '',
            'phone' => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '',
            'message' => isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '',
            'label' => isset($_POST['label']) ? sanitize_text_field($_POST['label']) : '',
            'avatar' => isset($_POST['avatar']) ? esc_url_raw($_POST['avatar']) : '',
            'status' => isset($_POST['status']) && $_POST['status'] == '1' ? 1 : 0,
            'sort_order' => isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0
        );
        
        // Validate required fields
        if (empty($data['firstname']) || empty($data['phone'])) {
            wp_send_json_error(__('First name and phone are required.', 'whatsapp-chat-pro'));
        }
        
        $id = intval($_POST['id']);
        
        if ($id > 0) {
            // Update existing contact
            $result = WhatsApp_Chat_Database::update_contact($id, $data);
        } else {
            // Check license limits for new contacts
            $license_active = WhatsApp_Chat_License::is_license_active();
            $contacts = WhatsApp_Chat_Database::get_contacts();
            
            if (!$license_active && count($contacts) >= 1) {
                wp_send_json_error(__('Free version is limited to 1 contact. Upgrade to Pro for unlimited contacts.', 'whatsapp-chat-pro'));
            }
            
            // Add new contact
            $result = WhatsApp_Chat_Database::insert_contact($data);
        }
        
        // Process result
        
        if ($result) {
            wp_send_json_success(__('Contact saved successfully!', 'whatsapp-chat-pro'));
        } else {
            wp_send_json_error(__('Error saving contact!', 'whatsapp-chat-pro'));
        }
    }
    
    /**
     * Get contact
     */
    public function get_contact() {
        check_ajax_referer('whatsapp_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
        }
        
        $id = intval($_POST['id']);
        $contact = WhatsApp_Chat_Database::get_contact($id);
        
        if ($contact) {
            wp_send_json_success($contact);
        } else {
            wp_send_json_error(__('Contact not found!', 'whatsapp-chat-pro'));
        }
    }
    
    /**
     * Delete contact
     */
    public function delete_contact() {
        check_ajax_referer('whatsapp_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
        }
        
        $id = intval($_POST['id']);
        $result = WhatsApp_Chat_Database::delete_contact($id);
        
        if ($result) {
            wp_send_json_success(__('Contact deleted successfully!', 'whatsapp-chat-pro'));
        } else {
            wp_send_json_error(__('Error deleting contact!', 'whatsapp-chat-pro'));
        }
    }
    
    /**
     * Validate license
     */
    public function validate_license() {
        check_ajax_referer('whatsapp_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
        }
        
        $license_key = sanitize_text_field($_POST['license_key']);
        
        if (empty($license_key)) {
            wp_send_json_error(__('Please enter a license key.', 'whatsapp-chat-pro'));
        }
        
        // Simulate license validation (replace with real API call)
        $license_status = $this->validate_license_key($license_key);
        
        if ($license_status['valid']) {
            update_option('whatsapp_chat_license_key', $license_key);
            update_option('whatsapp_chat_license_status', 'active');
            update_option('whatsapp_chat_license_expires', $license_status['expires']);
            
            wp_send_json_success(array(
                'status' => 'active',
                'expires' => $license_status['expires'],
                'message' => __('License activated successfully!', 'whatsapp-chat-pro')
            ));
        } else {
            update_option('whatsapp_chat_license_status', 'inactive');
            wp_send_json_error($license_status['message']);
        }
    }
    
    /**
     * Upload file
     */
    public function upload_file() {
        check_ajax_referer('whatsapp_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
        }
        
        if (!isset($_FILES['file'])) {
            wp_send_json_error(__('No file uploaded.', 'whatsapp-chat-pro'));
        }
        
        $file = $_FILES['file'];
        
        // Check file type
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(__('Invalid file type. Only images are allowed.', 'whatsapp-chat-pro'));
        }
        
        // Check file size (2MB limit)
        if ($file['size'] > 2 * 1024 * 1024) {
            wp_send_json_error(__('File too large. Maximum size is 2MB.', 'whatsapp-chat-pro'));
        }
        
        // Upload file
        $upload_dir = wp_upload_dir();
        $whatsapp_dir = $upload_dir['basedir'] . '/whatsapp-chat/avatars';
        $whatsapp_url = $upload_dir['baseurl'] . '/whatsapp-chat/avatars';
        
        // Create directory if it doesn't exist
        if (!file_exists($whatsapp_dir)) {
            wp_mkdir_p($whatsapp_dir);
        }
        
        $filename = sanitize_file_name($file['name']);
        $filename = wp_unique_filename($whatsapp_dir, $filename);
        $filepath = $whatsapp_dir . '/' . $filename;
        $fileurl = $whatsapp_url . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            wp_send_json_success(array(
                'url' => $fileurl,
                'filename' => $filename
            ));
        } else {
            wp_send_json_error(__('Error uploading file.', 'whatsapp-chat-pro'));
        }
    }
    
    /**
     * Get stats
     */
    public function get_stats() {
        check_ajax_referer('whatsapp_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
        }
        
        $stats = array(
            'total_contacts' => count(WhatsApp_Chat_Database::get_contacts()),
            'active_contacts' => count(WhatsApp_Chat_Database::get_contacts(array('status' => 1))),
            'license_status' => get_option('whatsapp_chat_license_status', 'inactive'),
            'widget_enabled' => get_option('whatsapp_chat_enabled', '1'),
            'analytics_enabled' => get_option('whatsapp_chat_enable_analytics', '0')
        );
        
        wp_send_json_success($stats);
    }
    
    /**
     * Validate license key (simulated)
     */
    private function validate_license_key($license_key) {
        // This is a simulated validation
        // In a real implementation, you would make an API call to your license server
        
        // Simulate API response
        if (strlen($license_key) >= 10) {
            return array(
                'valid' => true,
                'expires' => date('Y-m-d', strtotime('+1 year')),
                'message' => 'License valid'
            );
        } else {
            return array(
                'valid' => false,
                'message' => 'Invalid license key'
            );
        }
    }
}
