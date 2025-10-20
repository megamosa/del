<?php
/**
 * Plugin Name: WhatsApp Chat Pro
 * Plugin URI: https://getjustplug.com/whatsapp-chat-pro
 * Description: Professional WhatsApp chat widget with multiple designs, agent management, and premium features. Free version includes 1 agent and 4 designs. Pro version unlocks unlimited agents and premium designs.
 * Version: 1.0.0
 * Author: GetJustPlug
 * Author URI: https://getjustplug.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: whatsapp-chat-pro
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define('WHATSAPP_CHAT_VERSION', '1.0.0');
define('WHATSAPP_CHAT_PLUGIN_FILE', __FILE__);
define('WHATSAPP_CHAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WHATSAPP_CHAT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WHATSAPP_CHAT_PLUGIN_BASENAME', plugin_basename(__FILE__));


// Include required files
$main_file = WHATSAPP_CHAT_PLUGIN_DIR . 'includes/class-whatsapp-chat.php';
if (file_exists($main_file)) {
    require_once $main_file;
} else {
    $main_file_alt = dirname(__FILE__) . '/includes/class-whatsapp-chat.php';
    if (file_exists($main_file_alt)) {
        require_once $main_file_alt;
    } else {
        wp_die(__('WhatsApp Chat Main file not found. Please check plugin installation.', 'whatsapp-chat-pro'));
    }
}

$activator_file = WHATSAPP_CHAT_PLUGIN_DIR . 'includes/class-whatsapp-chat-activator.php';
if (file_exists($activator_file)) {
    require_once $activator_file;
} else {
    $activator_file_alt = dirname(__FILE__) . '/includes/class-whatsapp-chat-activator.php';
    if (file_exists($activator_file_alt)) {
        require_once $activator_file_alt;
    } else {
        wp_die(__('WhatsApp Chat Activator file not found. Please check plugin installation.', 'whatsapp-chat-pro'));
    }
}

$deactivator_file = WHATSAPP_CHAT_PLUGIN_DIR . 'includes/class-whatsapp-chat-deactivator.php';
if (file_exists($deactivator_file)) {
    require_once $deactivator_file;
} else {
    $deactivator_file_alt = dirname(__FILE__) . '/includes/class-whatsapp-chat-deactivator.php';
    if (file_exists($deactivator_file_alt)) {
        require_once $deactivator_file_alt;
    } else {
        wp_die(__('WhatsApp Chat Deactivator file not found. Please check plugin installation.', 'whatsapp-chat-pro'));
    }
}

// Check if admin file exists before including
$admin_file = WHATSAPP_CHAT_PLUGIN_DIR . 'admin/class-whatsapp-chat-admin.php';
if (file_exists($admin_file)) {
    require_once $admin_file;
} else {
    // Fallback: try to find the file
    $admin_file_alt = dirname(__FILE__) . '/admin/class-whatsapp-chat-admin.php';
    if (file_exists($admin_file_alt)) {
        require_once $admin_file_alt;
    } else {
        // Debug information
        $debug_info = 'Plugin Directory: ' . WHATSAPP_CHAT_PLUGIN_DIR . '<br>';
        $debug_info .= 'Admin File Path: ' . $admin_file . '<br>';
        $debug_info .= 'Alternative Path: ' . $admin_file_alt . '<br>';
        $debug_info .= 'File Exists (main): ' . (file_exists($admin_file) ? 'Yes' : 'No') . '<br>';
        $debug_info .= 'File Exists (alt): ' . (file_exists($admin_file_alt) ? 'Yes' : 'No') . '<br>';
        wp_die(__('WhatsApp Chat Admin file not found. Debug info:', 'whatsapp-chat-pro') . '<br>' . $debug_info);
    }
}

// Check if public files exist before including
$public_file = WHATSAPP_CHAT_PLUGIN_DIR . 'public/class-whatsapp-chat-public.php';
if (file_exists($public_file)) {
    require_once $public_file;
} else {
    $public_file_alt = dirname(__FILE__) . '/public/class-whatsapp-chat-public.php';
    if (file_exists($public_file_alt)) {
        require_once $public_file_alt;
    } else {
        wp_die(__('WhatsApp Chat Public file not found. Please check plugin installation.', 'whatsapp-chat-pro'));
    }
}

$widget_file = WHATSAPP_CHAT_PLUGIN_DIR . 'public/class-whatsapp-chat-widget.php';
if (file_exists($widget_file)) {
    require_once $widget_file;
} else {
    $widget_file_alt = dirname(__FILE__) . '/public/class-whatsapp-chat-widget.php';
    if (file_exists($widget_file_alt)) {
        require_once $widget_file_alt;
    } else {
        wp_die(__('WhatsApp Chat Widget file not found. Please check plugin installation.', 'whatsapp-chat-pro'));
    }
}

$license_file = WHATSAPP_CHAT_PLUGIN_DIR . 'includes/class-whatsapp-chat-license.php';
if (file_exists($license_file)) {
    require_once $license_file;
} else {
    $license_file_alt = dirname(__FILE__) . '/includes/class-whatsapp-chat-license.php';
    if (file_exists($license_file_alt)) {
        require_once $license_file_alt;
    } else {
        wp_die(__('WhatsApp Chat License file not found. Please check plugin installation.', 'whatsapp-chat-pro'));
    }
}

$database_file = WHATSAPP_CHAT_PLUGIN_DIR . 'includes/class-whatsapp-chat-database.php';
if (file_exists($database_file)) {
    require_once $database_file;
} else {
    $database_file_alt = dirname(__FILE__) . '/includes/class-whatsapp-chat-database.php';
    if (file_exists($database_file_alt)) {
        require_once $database_file_alt;
    } else {
        wp_die(__('WhatsApp Chat Database file not found. Please check plugin installation.', 'whatsapp-chat-pro'));
    }
}

$ajax_file = WHATSAPP_CHAT_PLUGIN_DIR . 'includes/class-whatsapp-chat-ajax.php';
if (file_exists($ajax_file)) {
    require_once $ajax_file;
} else {
    $ajax_file_alt = dirname(__FILE__) . '/includes/class-whatsapp-chat-ajax.php';
    if (file_exists($ajax_file_alt)) {
        require_once $ajax_file_alt;
    } else {
        wp_die(__('WhatsApp Chat AJAX file not found. Please check plugin installation.', 'whatsapp-chat-pro'));
    }
}

/**
 * Main plugin class
 */
class WhatsApp_Chat_Pro {
    
    /**
     * Single instance of the plugin
     */
    private static $instance = null;
    
    /**
     * Get single instance
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
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array('WhatsApp_Chat_Activator', 'activate'));
        register_deactivation_hook(__FILE__, array('WhatsApp_Chat_Deactivator', 'deactivate'));
        
        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Load text domain
        add_action('init', array($this, 'load_textdomain'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize admin
        if (is_admin()) {
            new WhatsApp_Chat_Admin();
        }
        
        // Initialize public
        new WhatsApp_Chat_Public();
        
        // Initialize widget
        add_action('widgets_init', array($this, 'register_widget'));
        
        // Initialize database
        new WhatsApp_Chat_Database();
        
        // Initialize AJAX
        new WhatsApp_Chat_Ajax();
    }
    
    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('whatsapp-chat-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Register widget
     */
    public function register_widget() {
        register_widget('WhatsApp_Chat_Widget');
    }
}

// Initialize plugin
WhatsApp_Chat_Pro::get_instance();
