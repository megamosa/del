<?php
/**
 * Admin functionality - Simple and User-Friendly
 *
 * @package WhatsApp_Chat_Pro
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	wp_die(__('Direct access not allowed.', 'whatsapp-chat-pro'));
}

/**
 * Admin class - Super Simple Interface
 */
class WhatsApp_Chat_Admin {

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
		// WPML compatibility
		if (function_exists('wpml_register_single_string')) {
			add_action('init', array($this, 'register_wpml_strings'));
		}
		
		// Add admin menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

        // Add admin styles and scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        
        // WPML compatibility hooks
        if (function_exists('wpml_translate_single_string')) {
            add_filter('whatsapp_chat_translate_string', array($this, 'translate_wpml_string'), 10, 2);
        }

		// Add frontend tracking script
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );

		// Add plugin action links
		add_filter( 'plugin_action_links_' . WHATSAPP_CHAT_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );

		// Remove WordPress notices on our plugin pages
		add_action( 'admin_init', array( $this, 'remove_wp_notices' ) );

		// Handle AJAX requests
		add_action( 'wp_ajax_whatsapp_chat_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_whatsapp_chat_save_contact', array( $this, 'ajax_save_contact' ) );
		add_action( 'wp_ajax_whatsapp_chat_delete_contact', array( $this, 'ajax_delete_contact' ) );
		add_action( 'wp_ajax_whatsapp_chat_validate_license', array( $this, 'ajax_validate_license' ) );
		add_action( 'wp_ajax_whatsapp_chat_select_design', array( $this, 'ajax_select_design' ) );
		add_action( 'wp_ajax_whatsapp_chat_upload_file', array( $this, 'ajax_upload_file' ) );
		add_action( 'wp_ajax_add_contact', array( $this, 'ajax_add_contact' ) );
		add_action( 'wp_ajax_whatsapp_chat_track_click', array( $this, 'track_whatsapp_click' ) );
		add_action( 'wp_ajax_nopriv_whatsapp_chat_track_click', array( $this, 'track_whatsapp_click' ) );
		add_action( 'wp_ajax_whatsapp_chat_save_design_options', array( $this, 'save_design_options' ) );
		add_action( 'wp_ajax_whatsapp_chat_save_working_hours', array( $this, 'ajax_save_working_hours' ) );
		add_action( 'wp_ajax_whatsapp_chat_upload_avatar', array( $this, 'ajax_upload_avatar' ) );
		add_action( 'wp_ajax_whatsapp_chat_save_settings_ajax', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_whatsapp_chat_save_design_special', array( $this, 'ajax_save_design_special' ) );
		add_action( 'wp_ajax_whatsapp_chat_save_analytics_settings', array( $this, 'ajax_save_analytics_settings' ) );
		add_action( 'wp_ajax_whatsapp_chat_save_display_rules', array( $this, 'ajax_save_display_rules' ) );
		add_action( 'wp_ajax_whatsapp_chat_save_basic_design_options', array( $this, 'ajax_save_basic_design_options' ) );
		add_action( 'wp_ajax_whatsapp_chat_reset_colors', array( $this, 'ajax_reset_colors' ) );

		// Handle form submissions
		add_action( 'admin_post_whatsapp_chat_save_settings', array( $this, 'handle_settings_save' ) );
		add_action( 'admin_post_whatsapp_chat_license_action', array( $this, 'handle_license_action' ) );
	}

	/**
	 * Remove WordPress notices on plugin pages
	 */
	public function remove_wp_notices() {
		$screen = get_current_screen();
		if ( $screen && strpos( $screen->id, 'whatsapp-chat' ) !== false ) {
			// Remove all WordPress notices
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'network_admin_notices' );
			remove_all_actions( 'user_admin_notices' );
			remove_all_actions( 'all_admin_notices' );

			// Remove update notices
			remove_action( 'admin_notices', 'update_nag', 3 );
			remove_action( 'admin_notices', 'maintenance_nag', 10 );

			// Remove plugin update notices
			remove_action( 'admin_notices', 'wp_plugin_update_row' );

			// Remove WordPress core update notices
			remove_action( 'admin_notices', '_admin_notice_php_version' );
			remove_action( 'admin_notices', '_admin_notice_https_required' );

			// Remove theme and plugin notices
			remove_action( 'admin_notices', 'wp_plugin_update_row' );
			remove_action( 'admin_notices', 'wp_theme_update_row' );

			// Add custom CSS to hide any remaining notices
			add_action( 'admin_head', array( $this, 'hide_admin_notices_css' ) );
		}
	}

	/**
	 * Add CSS to hide admin notices
	 */
	public function hide_admin_notices_css() {
        ?>
        <style>
        .wrap .notice,
        .wrap .updated,
        .wrap .error,
        .wrap .update-nag,
        .wrap .notice-info,
        .wrap .notice-warning,
        .wrap .notice-success,
        .wrap .notice-error {
            display: none !important;
        }
        .whatsapp-chat-admin .notice {
            display: none !important;
        }
        </style>
        <?php
    }
    
	/**
	 * Register WPML strings
	 */
	public function register_wpml_strings() {
		// Register all admin strings with WPML
		$admin_strings = array(
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
			'Saving...',
			'Settings saved!',
			'Error saving settings!',
			'Are you sure you want to delete this contact?',
			'You do not have sufficient permissions to access this page.',
			'Security check failed',
			'Insufficient permissions'
		);
		
		foreach ($admin_strings as $string) {
			wpml_register_single_string('whatsapp-chat-pro', $string, $string);
		}
	}

	/**
	 * Translate WPML string
	 */
	public function translate_wpml_string($string, $context = '') {
		if (function_exists('wpml_translate_single_string')) {
			return wpml_translate_single_string($string, 'whatsapp-chat-pro', $string);
		}
		return $string;
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
        // Main menu page with tabs
        add_menu_page(
            __('WhatsApp Chat Pro', 'whatsapp-chat-pro'),
            __('WhatsApp Chat', 'whatsapp-chat-pro'),
            'manage_options',
            'whatsapp-chat-pro',
            array($this, 'admin_page'),
            'dashicons-whatsapp',
            30
        );
        
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'whatsapp-chat') === false) {
            return;
        }
        
        // Get cache busting version
        $cache_version = $this->get_cache_version();
        
        wp_enqueue_style(
            'whatsapp-chat-admin',
            WHATSAPP_CHAT_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            $cache_version
        );
        
        wp_enqueue_script(
            'whatsapp-chat-admin',
            WHATSAPP_CHAT_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery', 'media-upload', 'thickbox'),
            $cache_version,
            true
        );
        
        // Enqueue frontend tracking script
        wp_enqueue_script(
            'whatsapp-chat-frontend-tracking',
            WHATSAPP_CHAT_PLUGIN_URL . 'admin/js/frontend-tracking.js',
            array('jquery'),
            $cache_version,
            true
        );
        
        // Enqueue WordPress media library
        wp_enqueue_media();
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        
        $localized_data = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('whatsapp_chat_admin_nonce'),
            'licenseStatus' => get_option('whatsapp_chat_license_status', 'inactive'),
            'strings' => array(
                'saving' => __('Saving...', 'whatsapp-chat-pro'),
                'saved' => __('Settings saved!', 'whatsapp-chat-pro'),
                'error' => __('Error saving settings!', 'whatsapp-chat-pro'),
                'confirmDelete' => __('Are you sure you want to delete this contact?', 'whatsapp-chat-pro')
            )
        );
        
        wp_localize_script('whatsapp-chat-admin', 'whatsappChatAdmin', $localized_data);
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        // Only enqueue if WhatsApp chat is enabled
        $enabled = get_option('whatsapp_chat_enabled', '1');
        if ($enabled !== '1') {
            return;
        }
        
        // Get cache busting version
        $cache_version = $this->get_cache_version();
        
        // Enqueue frontend tracking script
        wp_enqueue_script(
            'whatsapp-chat-frontend-tracking',
            WHATSAPP_CHAT_PLUGIN_URL . 'admin/js/frontend-tracking.js',
            array('jquery'),
            $cache_version,
            true
        );
        
        // Localize script data
        wp_localize_script('whatsapp-chat-frontend-tracking', 'whatsappChatAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('whatsapp_chat_admin_nonce')
        ));
    }
    
    /**
     * Save Design Options
     */
    public function save_design_options() {
        // Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'whatsapp_chat_admin_nonce' ) ) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_die('Insufficient permissions');
        }
        
        // Save unified design options for both Design 6 and 7
        if (isset($_POST['design_avatar'])) {
            update_option('whatsapp_chat_design_avatar', esc_url_raw($_POST['design_avatar']));
        }
        if (isset($_POST['design_agent_name'])) {
            update_option('whatsapp_chat_design_agent_name', sanitize_text_field($_POST['design_agent_name']));
        }
        if (isset($_POST['design_status'])) {
            update_option('whatsapp_chat_design_status', sanitize_text_field($_POST['design_status']));
        }
        if (isset($_POST['design_message'])) {
            update_option('whatsapp_chat_design_message', sanitize_text_field($_POST['design_message']));
        }
        if (isset($_POST['design_bg_color'])) {
            update_option('whatsapp_chat_design_bg_color', sanitize_hex_color($_POST['design_bg_color']));
        }
        if (isset($_POST['design_text_color'])) {
            update_option('whatsapp_chat_design_text_color', sanitize_hex_color($_POST['design_text_color']));
        }
        if (isset($_POST['design_name_color'])) {
            update_option('whatsapp_chat_design_name_color', sanitize_hex_color($_POST['design_name_color']));
        }
        if (isset($_POST['design_status_color'])) {
            update_option('whatsapp_chat_design_status_color', sanitize_hex_color($_POST['design_status_color']));
        }
        
        // Save floating message for designs 1-5
        if (isset($_POST['whatsapp_chat_floating_message'])) {
            $floating_message = sanitize_text_field($_POST['whatsapp_chat_floating_message']);
            // Limit to 30 characters
            if (strlen($floating_message) > 30) {
                $floating_message = substr($floating_message, 0, 30);
            }
            update_option('whatsapp_chat_floating_message', $floating_message);
        }
        
        wp_send_json_success('Design options saved successfully');
    }
    
    /**
     * AJAX save basic design options (1-5)
     */
    public function ajax_save_basic_design_options() {
        check_ajax_referer('whatsapp_chat_admin_nonce', 'nonce');
        
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
        }
        
        // Save floating message for designs 1-5
        if (isset($_POST['whatsapp_chat_floating_message'])) {
            $floating_message = sanitize_text_field($_POST['whatsapp_chat_floating_message']);
            // Limit to 30 characters
            if (strlen($floating_message) > 30) {
                $floating_message = substr($floating_message, 0, 30);
            }
            update_option('whatsapp_chat_floating_message', $floating_message);
        }
        
        wp_send_json_success(array(
            'message' => 'Basic design options saved successfully!',
            'floating_message' => $floating_message
        ));
    }
    
    /**
     * Track WhatsApp button clicks (lightweight analytics)
     */
    public function track_whatsapp_click() {
        // Verify nonce for security
		if ( ! wp_verify_nonce( $_POST['nonce'], 'whatsapp_chat_admin_nonce' ) ) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        // Get user IP
        $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $current_time = current_time('timestamp');
        
        // Check for duplicate clicks from same IP within 5 minutes
        $recent_clicks = get_option('whatsapp_chat_recent_clicks', array());
        $five_minutes_ago = $current_time - 300; // 5 minutes
        
        // Filter recent clicks from same IP within 5 minutes
        $recent_clicks = array_filter($recent_clicks, function($click) use ($user_ip, $five_minutes_ago) {
            return !($click['ip'] === $user_ip && $click['timestamp'] > $five_minutes_ago);
        });
        
        // If there's a recent click from same IP, ignore this one
        $has_recent_click = false;
        foreach ($recent_clicks as $click) {
            if ($click['ip'] === $user_ip && $click['timestamp'] > $five_minutes_ago) {
                $has_recent_click = true;
                break;
            }
        }
        
        if ($has_recent_click) {
            wp_send_json_success(array(
                'message' => 'Click ignored - duplicate from same IP',
                'total_clicks' => get_option('whatsapp_chat_total_clicks', 0),
                'today_clicks' => get_option('whatsapp_chat_today_clicks', 0)
            ));
            return;
        }
        
        // Get current analytics data
        $total_clicks = (int) get_option('whatsapp_chat_total_clicks', 0);
        $today_clicks = (int) get_option('whatsapp_chat_today_clicks', 0);
        $week_clicks = (int) get_option('whatsapp_chat_week_clicks', 0);
        $month_clicks = (int) get_option('whatsapp_chat_month_clicks', 0);
        
        // Get current date info
        $current_date = date('Y-m-d');
        $current_week = date('W');
        $current_month = date('Y-m');
        
        // Check if we need to reset daily counter
        $last_click_date = get_option('whatsapp_chat_last_click_date', '');
        if ($last_click_date !== $current_date) {
            // Reset daily counter
            $today_clicks = 1;
            update_option('whatsapp_chat_today_clicks', $today_clicks);
            update_option('whatsapp_chat_last_click_date', $current_date);
        } else {
            // Increment daily counter
            $today_clicks++;
            update_option('whatsapp_chat_today_clicks', $today_clicks);
        }
        
        // Check if we need to reset weekly counter
        $last_click_week = get_option('whatsapp_chat_last_click_week', '');
        if ($last_click_week !== $current_week) {
            $week_clicks = 1;
            update_option('whatsapp_chat_week_clicks', $week_clicks);
            update_option('whatsapp_chat_last_click_week', $current_week);
        } else {
            $week_clicks++;
            update_option('whatsapp_chat_week_clicks', $week_clicks);
        }
        
        // Check if we need to reset monthly counter
        $last_click_month = get_option('whatsapp_chat_last_click_month', '');
        if ($last_click_month !== $current_month) {
            $month_clicks = 1;
            update_option('whatsapp_chat_month_clicks', $month_clicks);
            update_option('whatsapp_chat_last_click_month', $current_month);
        } else {
            $month_clicks++;
            update_option('whatsapp_chat_month_clicks', $month_clicks);
        }
        
        // Update total clicks
        $total_clicks++;
        update_option('whatsapp_chat_total_clicks', $total_clicks);
        
        // Store click timestamp for recent activity
        $recent_clicks[] = array(
            'timestamp' => $current_time,
            'ip' => $user_ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'page_url' => $_POST['page_url'] ?? 'unknown'
        );
        
        // Keep only last 100 clicks to prevent database bloat
        if (count($recent_clicks) > 100) {
            $recent_clicks = array_slice($recent_clicks, -100);
        }
        
        update_option('whatsapp_chat_recent_clicks', $recent_clicks);
        
        // Track unique visitors
        $unique_visitors = get_option('whatsapp_chat_unique_visitors', array());
        if (!in_array($user_ip, $unique_visitors)) {
            $unique_visitors[] = $user_ip;
            update_option('whatsapp_chat_unique_visitors', $unique_visitors);
        }
        
        // Track conversion rate (clicks vs opens)
        $conversion_data = get_option('whatsapp_chat_conversion_data', array(
            'total_clicks' => 0,
            'whatsapp_opens' => 0,
            'conversion_rate' => 0
        ));
        
        $conversion_data['total_clicks'] = $total_clicks;
        $conversion_data['whatsapp_opens'] = $total_clicks; // Assuming all clicks lead to WhatsApp
        $conversion_data['conversion_rate'] = $total_clicks > 0 ? round(($total_clicks / $total_clicks) * 100, 2) : 0;
        
        update_option('whatsapp_chat_conversion_data', $conversion_data);
        
        // Return success response
        wp_send_json_success(array(
            'message' => 'Click tracked successfully',
            'total_clicks' => $total_clicks,
            'today_clicks' => $today_clicks,
            'week_clicks' => $week_clicks,
            'month_clicks' => $month_clicks,
            'unique_visitors' => count($unique_visitors),
            'conversion_rate' => $conversion_data['conversion_rate']
        ));
    }
    
    /**
     * Add action links
     */
    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=whatsapp-chat-pro') . '">' . __('Settings', 'whatsapp-chat-pro') . '</a>';
        $upgrade_link = '<a href="https://getjustplug.com/whatsapp-chat-pro" target="_blank" style="color: #25D366; font-weight: bold;">' . __('Upgrade to Pro', 'whatsapp-chat-pro') . '</a>';
        array_unshift($links, $settings_link, $upgrade_link);
        return $links;
    }
    
    /**
     * Admin page (Dashboard) - Super Simple
     */
    public function admin_page() {
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        $enabled = get_option('whatsapp_chat_enabled', '1');
        $phone_number = get_option('whatsapp_chat_phone_number', '');
        $design = get_option('whatsapp_chat_design', 'design-1');
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
        
        ?>
        <div class="wrap whatsapp-chat-admin">
            <!-- Custom Header -->
            <div class="whatsapp-chat-header">
                <h1>
                    <span class="dashicons dashicons-whatsapp"></span>
                    <?php _e('WhatsApp Chat Pro', 'whatsapp-chat-pro'); ?>
                </h1>
                <p class="whatsapp-chat-subtitle">
                    <?php _e('Professional WhatsApp chat widget Multi Agent Support for your website', 'whatsapp-chat-pro'); ?>
                </p>
                <?php if (get_option('whatsapp_chat_license_status', 'inactive') !== 'active'): ?>
                <a href="https://getjustplug.com/whatsapp-chat-pro" target="_blank" class="upgrade-button">
                    <span class="dashicons dashicons-star-filled"></span>
                    <?php _e( 'Upgrade to Pro', 'whatsapp-chat-pro' ); ?>
                </a>
                <?php endif; ?>
            </div>
            
            <!-- Professional Tab Navigation -->
            <div class="whatsapp-tab-navigation">
                <nav class="whatsapp-tabs">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=whatsapp-chat-pro&tab=dashboard' ) ); ?>" 
                       class="whatsapp-tab <?php echo esc_attr( $current_tab === 'dashboard' ? 'active' : '' ); ?>">
                        <span class="dashicons dashicons-dashboard"></span>
                        <span class="tab-text"><?php _e( 'Dashboard', 'whatsapp-chat-pro' ); ?></span>
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=whatsapp-chat-pro&tab=settings' ) ); ?>" 
                       class="whatsapp-tab <?php echo esc_attr( $current_tab === 'settings' ? 'active' : '' ); ?>">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <span class="tab-text"><?php _e( 'Settings', 'whatsapp-chat-pro' ); ?></span>
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=whatsapp-chat-pro&tab=designs' ) ); ?>" 
                       class="whatsapp-tab <?php echo esc_attr( $current_tab === 'designs' ? 'active' : '' ); ?>">
                        <span class="dashicons dashicons-admin-appearance"></span>
                        <span class="tab-text"><?php _e( 'Designs', 'whatsapp-chat-pro' ); ?></span>
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=whatsapp-chat-pro&tab=contacts' ) ); ?>" 
                       class="whatsapp-tab <?php echo esc_attr( $current_tab === 'contacts' ? 'active' : '' ); ?>">
                        <span class="dashicons dashicons-groups"></span>
                        <span class="tab-text"><?php _e( 'Agents', 'whatsapp-chat-pro' ); ?></span>
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=whatsapp-chat-pro&tab=license' ) ); ?>" 
                       class="whatsapp-tab <?php echo esc_attr( $current_tab === 'license' ? 'active' : '' ); ?>">
                        <span class="dashicons dashicons-admin-network"></span>
                        <span class="tab-text"><?php _e( 'License', 'whatsapp-chat-pro' ); ?></span>
                    </a>
                </nav>
            </div>
            
            <!-- Tab Content -->
            <div class="whatsapp-tab-content">
                <?php
                switch ($current_tab) {
                    case 'dashboard':
                        $this->render_dashboard_tab();
                        break;
                    case 'settings':
                        $this->render_settings_tab();
                        break;
                    case 'designs':
                        $this->render_designs_tab();
                        break;
                    case 'contacts':
                        $this->render_contacts_tab();
                        break;
                    case 'license':
                        $this->render_license_tab();
                        break;
                    default:
                        $this->render_dashboard_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Professional Dashboard Tab
     */
    private function render_dashboard_tab() {
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        $enabled = get_option('whatsapp_chat_enabled', '1');
        $phone_number = get_option('whatsapp_chat_phone_number', '');
        $design = get_option('whatsapp_chat_design', 'design-1');
        
        // Get analytics data
        $total_clicks = get_option('whatsapp_chat_total_clicks', 0);
        $today_clicks = get_option('whatsapp_chat_today_clicks', 0);
        $this_week_clicks = get_option('whatsapp_chat_week_clicks', 0);
        $this_month_clicks = get_option('whatsapp_chat_month_clicks', 0);
        
        // Get agents count
        $agents_count = $this->get_agents_count();
        
        // Get design info
        $design_info = $this->get_design_info($design);
        
        // Get recent activity
        $recent_activity = $this->get_recent_activity();
        ?>
        <div class="whatsapp-professional-dashboard">
            <!-- Dashboard Header -->
            <div class="whatsapp-dashboard-header">
                <div class="dashboard-welcome">
                    <h2>Welcome to WhatsApp Chat Pro</h2>
                    <p>Monitor your customer interactions and optimize your chat performance</p>
                </div>
                <div class="dashboard-actions">
                    <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-pro&tab=settings'); ?>" class="whatsapp-dashboard-btn">
                        <span class="dashicons dashicons-admin-settings"></span>
                        Settings
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-pro&tab=designs'); ?>" class="whatsapp-dashboard-btn">
                        <span class="dashicons dashicons-admin-appearance"></span>
                        Designs
                    </a>
                </div>
            </div>
            
            <!-- Analytics Overview -->
            <div class="whatsapp-analytics-overview">
                <div class="analytics-card">
                    <div class="analytics-icon">
                        <span class="dashicons dashicons-chart-line"></span>
                    </div>
                    <div class="analytics-content">
                        <h3><?php echo number_format($total_clicks); ?></h3>
                        <p>Total Clicks</p>
                        <span class="analytics-trend">+<?php echo $today_clicks; ?> today</span>
                    </div>
                </div>
                
                <div class="analytics-card">
                    <div class="analytics-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <div class="analytics-content">
                        <h3><?php echo number_format($this_week_clicks); ?></h3>
                        <p>This Week</p>
                        <span class="analytics-trend">+<?php echo $this_week_clicks - $this_month_clicks; ?> vs last week</span>
                    </div>
                </div>
                
                <div class="analytics-card">
                    <div class="analytics-icon">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <div class="analytics-content">
                        <h3><?php echo $agents_count; ?></h3>
                        <p>Active Agents</p>
                        <span class="analytics-trend"><?php echo $license_status === 'active' ? 'Pro Unlimited' : 'Free: 1/1'; ?></span>
                    </div>
                </div>
                
                <div class="analytics-card">
                    <div class="analytics-icon">
                        <span class="dashicons dashicons-admin-appearance"></span>
                    </div>
                    <div class="analytics-content">
                        <h3><?php echo $design_info['name']; ?></h3>
                        <p>Current Design</p>
                        <span class="analytics-trend"><?php echo $design_info['type']; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Main Dashboard Grid -->
            <div class="whatsapp-dashboard-grid">
                <!-- Quick Actions -->
                <div class="whatsapp-dashboard-card">
                    <div class="card-header">
                        <h3><span class="dashicons dashicons-lightning"></span> Quick Actions</h3>
                    </div>
                    <div class="card-content">
                        <div class="quick-actions">
                            <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-pro&tab=settings'); ?>" class="quick-action">
                                <span class="dashicons dashicons-admin-settings"></span>
                                <span>Configure Settings</span>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-pro&tab=designs'); ?>" class="quick-action">
                                <span class="dashicons dashicons-admin-appearance"></span>
                                <span>Change Design</span>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-pro&tab=contacts'); ?>" class="quick-action">
                                <span class="dashicons dashicons-groups"></span>
                                <span>Manage Agents</span>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-pro&tab=license'); ?>" class="quick-action">
                                <span class="dashicons dashicons-admin-network"></span>
                                <span>License Info</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Current Status -->
                <div class="whatsapp-dashboard-card">
                    <div class="card-header">
                        <h3><span class="dashicons dashicons-info"></span> Current Status</h3>
                    </div>
                    <div class="card-content">
                        <div class="status-overview">
                            <div class="status-item">
                                <span class="status-label">Plugin Status:</span>
                                <span class="status-value <?php echo $enabled ? 'active' : 'inactive'; ?>">
                                    <?php echo $enabled ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Phone Number:</span>
                                <span class="status-value"><?php echo esc_html($phone_number); ?></span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">License:</span>
                                <span class="status-value <?php echo $license_status === 'active' ? 'pro' : 'free'; ?>">
                                    <?php echo $license_status === 'active' ? 'Pro Version' : 'Free Version'; ?>
                                </span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Design:</span>
                                <span class="status-value"><?php echo $design_info['name']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Performance Metrics -->
                <div class="whatsapp-dashboard-card">
                    <div class="card-header">
                        <h3><span class="dashicons dashicons-chart-bar"></span> Performance Metrics</h3>
                    </div>
                    <div class="card-content">
                        <div class="metrics-grid">
                            <div class="metric-item">
                                <div class="metric-value"><?php echo number_format($today_clicks); ?></div>
                                <div class="metric-label">Today's Clicks</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-value"><?php echo number_format($this_week_clicks); ?></div>
                                <div class="metric-label">This Week</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-value"><?php echo number_format($this_month_clicks); ?></div>
                                <div class="metric-label">This Month</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-value"><?php echo number_format($total_clicks); ?></div>
                                <div class="metric-label">All Time</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="whatsapp-dashboard-card">
                    <div class="card-header">
                        <h3><span class="dashicons dashicons-clock"></span> Recent Activity</h3>
                    </div>
                    <div class="card-content">
                        <div class="activity-list">
                            <?php if (!empty($recent_activity)): ?>
                                <?php foreach ($recent_activity as $activity): ?>
                                    <div class="activity-item">
                                        <span class="activity-icon"><?php echo $activity['icon']; ?></span>
                                        <div class="activity-content">
                                            <div class="activity-text"><?php echo $activity['text']; ?></div>
                                            <div class="activity-time"><?php echo $activity['time']; ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-activity">
                                    <span class="dashicons dashicons-info"></span>
                                    <p>No recent activity</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Professional Upgrade Banner for Free Users -->
            <?php if ($license_status === 'inactive'): ?>
            <?php 
            $main_features = array(
                'Advanced analytics & detailed reports',
                'Unlimited agents & contact management',
                'Premium design templates & customization',
                'Priority support & lifetime updates'
            );
            echo $this->generate_professional_upgrade_banner(
                'Unlock Advanced Analytics & Pro Features',
                'Get detailed insights into your customer interactions and unlock unlimited agents with professional features.',
                $main_features,
                'dashicons-chart-line'
            );
            ?>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render Settings Tab
     */
    private function render_settings_tab() {
        // Ensure database tables exist
        $this->ensure_database_tables();
        
        $enabled = get_option('whatsapp_chat_enabled', '1');
        $phone_number = get_option('whatsapp_chat_phone_number', '');
        $default_message = get_option('whatsapp_chat_default_message', 'Hello! I have a question about your products.');
        $position = get_option('whatsapp_chat_button_position', 'bottom-right');
        $layout = get_option('whatsapp_chat_button_layout', 'button');
        $show_mobile = get_option('whatsapp_chat_show_on_mobile', '1');
        $show_desktop = get_option('whatsapp_chat_show_on_desktop', '1');
        $enable_agents = get_option('whatsapp_chat_enable_agents', '0');
        $header_text = get_option('whatsapp_chat_header_text', 'Chat with us');
        $footer_text = get_option('whatsapp_chat_footer_text', "We're here to help!");
        $support_team_name = get_option('whatsapp_chat_support_team_name', 'Support Team');
        $support_team_label = get_option('whatsapp_chat_support_team_label', 'Customer Support');
        $license_status = get_option('whatsapp_chat_license_status', 'active'); // Temporary for testing
        ?>
        <div class="whatsapp-settings">
            <div class="whatsapp-settings-card">
                <div class="whatsapp-settings-header">
                    <h3 class="whatsapp-settings-title">
                        <span class="dashicons dashicons-admin-generic"></span>
                        General Settings
                    </h3>
                    <p class="whatsapp-settings-subtitle">Configure your WhatsApp chat widget with professional settings</p>
                </div>
                <div class="whatsapp-settings-content">
                    <form id="whatsapp-settings-form" method="post" class="whatsapp-settings-form">
                        <?php wp_nonce_field('whatsapp_chat_admin_nonce', 'nonce'); ?>
                        
                        <!-- Basic Configuration Section -->
                        <div class="whatsapp-settings-section">
                            <h4 class="whatsapp-section-title">
                                <span class="dashicons dashicons-admin-settings"></span>
                                Basic Configuration
                            </h4>
                            <div class="whatsapp-settings-grid">
                                <div class="whatsapp-settings-group">
                                    <div class="whatsapp-settings-toggle">
                                        <input type="checkbox" name="whatsapp_chat_enabled" value="1" <?php checked($enabled, '1'); ?>>
                                        <span class="whatsapp-toggle-slider"></span>
                                        <span class="whatsapp-toggle-label">Enable WhatsApp Chat</span>
                                    </div>
                                    <div class="whatsapp-settings-help">Turn on/off the WhatsApp chat widget</div>
                                </div>
                                
                                <div class="whatsapp-settings-group">
                                    <label class="whatsapp-settings-label">
                                        <span class="dashicons dashicons-phone"></span>
                                        WhatsApp Phone Number
                                    </label>
                                    <input type="text" name="whatsapp_chat_phone_number" value="<?php echo esc_attr($phone_number); ?>" 
                                           placeholder="+1234567890" class="whatsapp-settings-input" required>
                                    <div class="whatsapp-settings-help">Enter your WhatsApp number with country code</div>
                                    <?php if (empty($phone_number)): ?>
                                    <div class="whatsapp-settings-warning">
                                        <span class="dashicons dashicons-warning"></span>
                                        Please enter your WhatsApp phone number to enable the chat widget
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="whatsapp-settings-group">
                                    <label class="whatsapp-settings-label">
                                        <span class="dashicons dashicons-format-chat"></span>
                                        Default Message
                                    </label>
                                    <textarea name="whatsapp_chat_default_message" class="whatsapp-settings-input whatsapp-settings-textarea" rows="3" 
                                              placeholder="Hello! I have a question about your products."><?php echo esc_textarea($default_message); ?></textarea>
                                    <div class="whatsapp-settings-help">Message that appears when customers click the button</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Display Settings Section -->
                        <div class="whatsapp-settings-section">
                            <h4 class="whatsapp-section-title">
                                <span class="dashicons dashicons-visibility"></span>
                                Display Settings
                            </h4>
                            <div class="whatsapp-settings-grid">
                                <div class="whatsapp-settings-group">
                                    <label class="whatsapp-settings-label">
                                        <span class="dashicons dashicons-location"></span>
                                        Button Position
                                    </label>
                                    <select name="whatsapp_chat_button_position" class="whatsapp-settings-input whatsapp-settings-select">
                                        <option value="bottom-right" <?php selected($position, 'bottom-right'); ?>>Bottom Right</option>
                                        <option value="bottom-left" <?php selected($position, 'bottom-left'); ?>>Bottom Left</option>
                                        <option value="top-right" <?php selected($position, 'top-right'); ?>>Top Right</option>
                                        <option value="top-left" <?php selected($position, 'top-left'); ?>>Top Left</option>
                                    </select>
                                </div>
                                
                                <div class="whatsapp-settings-group">
                                    <label class="whatsapp-settings-label">
                                        <span class="dashicons dashicons-move"></span>
                                        Custom Position (Pixels)
                                    </label>
                                    <div class="whatsapp-position-controls">
                                        <div class="whatsapp-position-input">
                                            <label>X Position:</label>
                                            <input type="number" name="whatsapp_chat_button_x" value="<?php echo esc_attr(get_option('whatsapp_chat_button_x', '20')); ?>" min="0" max="2000">
                                            <span>px</span>
                                        </div>
                                        <div class="whatsapp-position-input">
                                            <label>Y Position:</label>
                                            <input type="number" name="whatsapp_chat_button_y" value="<?php echo esc_attr(get_option('whatsapp_chat_button_y', '20')); ?>" min="0" max="2000">
                                            <span>px</span>
                                        </div>
                                    </div>
                                    <div class="whatsapp-settings-help">Custom positioning from edges (0 = edge, 20 = 20px from edge)</div>
                                </div>
                                
                                <div class="whatsapp-settings-group">
                                    <label class="whatsapp-settings-label">
                                        <span class="dashicons dashicons-controls-play"></span>
                                        Button Animation
                                    </label>
                                    <select name="whatsapp_chat_button_animation" class="whatsapp-settings-input whatsapp-settings-select">
                                        <option value="none" <?php selected(get_option('whatsapp_chat_button_animation', 'none'), 'none'); ?>>No Animation</option>
                                        <option value="bounce" <?php selected(get_option('whatsapp_chat_button_animation', 'none'), 'bounce'); ?>>Bounce</option>
                                        <option value="pulse" <?php selected(get_option('whatsapp_chat_button_animation', 'none'), 'pulse'); ?>>Pulse</option>
                                        <option value="shake" <?php selected(get_option('whatsapp_chat_button_animation', 'none'), 'shake'); ?>>Shake</option>
                                        <option value="wiggle" <?php selected(get_option('whatsapp_chat_button_animation', 'none'), 'wiggle'); ?>>Wiggle</option>
                                        <option value="float" <?php selected(get_option('whatsapp_chat_button_animation', 'none'), 'float'); ?>>Float</option>
                                    </select>
                                    <div class="whatsapp-settings-help">Choose animation effect for the WhatsApp button</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Device Settings Section -->
                        <div class="whatsapp-settings-section">
                            <h4 class="whatsapp-section-title">
                                <span class="dashicons dashicons-smartphone"></span>
                                Device Settings
                            </h4>
                            <div class="whatsapp-settings-grid">
                                <div class="whatsapp-settings-group">
                                    <div class="whatsapp-settings-toggle">
                                        <input type="checkbox" name="whatsapp_chat_show_on_mobile" value="1" <?php checked($show_mobile, '1'); ?>>
                                        <span class="whatsapp-toggle-slider"></span>
                                        <span class="whatsapp-toggle-label">Show on Mobile</span>
                                    </div>
                                    <div class="whatsapp-settings-help">Display widget on mobile devices</div>
                                </div>
                                
                                <div class="whatsapp-settings-group">
                                    <div class="whatsapp-settings-toggle">
                                        <input type="checkbox" name="whatsapp_chat_show_on_desktop" value="1" <?php checked($show_desktop, '1'); ?>>
                                        <span class="whatsapp-toggle-slider"></span>
                                        <span class="whatsapp-toggle-label">Show on Desktop</span>
                                    </div>
                                    <div class="whatsapp-settings-help">Display widget on desktop devices</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="whatsapp-settings-actions">
                            <button type="submit" class="whatsapp-settings-btn whatsapp-settings-btn-primary">
                                <span class="dashicons dashicons-saved"></span>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- Working Hours -->
            <div class="whatsapp-card">
                <div class="whatsapp-card-header">
                    <h3>⏰ Working Hours</h3>
                    <p>Set working hours for your WhatsApp widget</p>
                    <?php if ($license_status !== 'active'): ?>
                        <div class="whatsapp-upgrade-banner">
                            <span class="dashicons dashicons-star-filled"></span>
                            <span>Pro Feature</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="whatsapp-card-content">
                    <?php if ($license_status !== 'active'): ?>
                        <?php 
                        $working_hours_features = array(
                            'Custom working hours per day',
                            'Timezone support & holiday management',
                            'Offline message handling',
                            'Smart scheduling automation'
                        );
                        echo $this->generate_professional_upgrade_banner(
                            'Smart Time Management',
                            'Set working hours and control when your WhatsApp widget appears. Perfect for businesses with specific operating hours.',
                            $working_hours_features,
                            'dashicons-clock'
                        );
                        ?>
                    <?php else: ?>
                        <div class="whatsapp-working-hours-section">
                            <form id="working-hours-form" method="post" onsubmit="return false;">
                                <?php wp_nonce_field('whatsapp_chat_admin_nonce', 'nonce'); ?>
                                
                                <!-- Enable/Disable Working Hours -->
                                <div class="whatsapp-form-row">
                                    <div class="whatsapp-form-group">
                                        <label class="whatsapp-switch-large">
                                            <input type="checkbox" name="working_hours_enabled" 
                                                   <?php checked(get_option('whatsapp_chat_working_hours_enabled', '0'), '1'); ?>>
                                            <span class="slider-large"></span>
                                            <span class="switch-label">Enable Working Hours Control</span>
                                        </label>
                                        <small>When enabled, the widget will only show during specified working hours</small>
                                    </div>
                                </div>
                                
                                <div class="whatsapp-working-hours-grid">
                                    <?php
                                    $working_hours = WhatsApp_Chat_Database::get_working_hours();
                                    $days = array(
                                        1 => 'Monday',
                                        2 => 'Tuesday',
                                        3 => 'Wednesday',
                                        4 => 'Thursday',
                                        5 => 'Friday',
                                        6 => 'Saturday',
                                        0 => 'Sunday'
                                    );
                                    foreach ($days as $day_num => $day_name):
                                        $day_hours = null;
                                        foreach ($working_hours as $hours) {
                                            if ($hours->day_of_week == $day_num) {
                                                $day_hours = $hours;
                                                break;
                                            }
                                        }
                                    ?>
                                        <div class="whatsapp-day-schedule">
                                            <div class="day-header">
                                                <h4><?php echo $day_name; ?></h4>
                                                <label class="whatsapp-switch">
                                                    <input type="checkbox" name="day_<?php echo $day_num; ?>_active" 
                                                           <?php echo ($day_hours && $day_hours->is_active) ? 'checked' : ''; ?>>
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                            <div class="day-times">
                                                <div class="time-group">
                                                    <label>Start Time</label>
                                                    <input type="time" name="day_<?php echo $day_num; ?>_start" 
                                                           value="<?php echo $day_hours ? $day_hours->start_time : '09:00'; ?>">
                                                </div>
                                                <div class="time-group">
                                                    <label>End Time</label>
                                                    <input type="time" name="day_<?php echo $day_num; ?>_end" 
                                                           value="<?php echo $day_hours ? $day_hours->end_time : '18:00'; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="whatsapp-form-actions">
                                    <button type="submit" class="whatsapp-btn whatsapp-btn-primary">
                                        <span class="dashicons dashicons-saved"></span>
                                        Save Working Hours
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Analytics & Tracking -->
            <div class="whatsapp-card">
                <div class="whatsapp-card-header">
                    <h3>📊 Analytics & Tracking</h3>
                    <p>Track your WhatsApp interactions</p>
                    <?php if ($license_status !== 'active'): ?>
                        <div class="whatsapp-upgrade-banner">
                            <span class="dashicons dashicons-star-filled"></span>
                            <span>Pro Feature</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="whatsapp-card-content">
                    <?php if ($license_status !== 'active'): ?>
                        <?php 
                        $analytics_features = array(
                            'Google Analytics 4 integration',
                            'Advanced click & conversion tracking',
                            'Page URL & agent selection analytics',
                            'Real-time performance insights'
                        );
                        echo $this->generate_professional_upgrade_banner(
                            'Advanced Analytics & Tracking',
                            'Track every interaction with detailed analytics including clicks, agent selection, and conversion data.',
                            $analytics_features,
                            'dashicons-chart-line'
                        );
                        ?>
                    <?php else: ?>
                        <div class="whatsapp-analytics-section">
                            <form id="analytics-settings-form" method="post" onsubmit="return false;">
                                <?php wp_nonce_field('whatsapp_chat_admin_nonce', 'nonce'); ?>
                                <div class="whatsapp-form-group">
                                    <label>GA4 Measurement ID</label>
                                    <input type="text" name="ga4_measurement_id" 
                                           value="<?php echo esc_attr(get_option('whatsapp_chat_ga4_measurement_id', '')); ?>"
                                           placeholder="G-XXXXXXXXXX">
                                    <small>Enter your Google Analytics 4 Measurement ID to track WhatsApp interactions.</small>
                                </div>
                                <div class="whatsapp-form-group">
                                    <label>Event Name</label>
                                    <input type="text" name="analytics_event_name" 
                                           value="<?php echo esc_attr(get_option('whatsapp_chat_analytics_event_name', 'whatsapp_click')); ?>">
                                </div>
                                <button type="submit" class="whatsapp-btn whatsapp-btn-primary">
                                    <span class="dashicons dashicons-saved"></span>
                                    Save Analytics Settings
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Display Rules -->
            <div class="whatsapp-card">
                <div class="whatsapp-card-header">
                    <h3>🎯 Display Rules</h3>
                    <p>Control where your widget appears</p>
                    <?php if ($license_status !== 'active'): ?>
                        <div class="whatsapp-upgrade-banner">
                            <span class="dashicons dashicons-star-filled"></span>
                            <span>Pro Feature</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="whatsapp-card-content">
                    <?php if ($license_status !== 'active'): ?>
                        <?php 
                        $display_rules_features = array(
                            'Exclude/include specific pages',
                            'Category & product control',
                            'Advanced SKU targeting',
                            'Smart display automation'
                        );
                        echo $this->generate_professional_upgrade_banner(
                            'Smart Page Display Control',
                            'Control where your WhatsApp chat widget appears on your website with advanced display rules.',
                            $display_rules_features,
                            'dashicons-visibility'
                        );
                        ?>
                    <?php else: ?>
                        <div class="whatsapp-display-rules-section">
                            <form id="display-rules-form" method="post" onsubmit="return false;">
                                <?php wp_nonce_field('whatsapp_chat_admin_nonce', 'nonce'); ?>
                                
                                <div class="whatsapp-form-group">
                                    <h4>Exclude Pages</h4>
                                    <p>Hide the widget on specific pages like checkout, cart, or customer account.</p>
                                    <textarea name="exclude_pages" rows="5" placeholder="Enter page URLs or page IDs, one per line&#10;Example:&#10;/checkout/&#10;/cart/&#10;/my-account/&#10;123"><?php echo esc_textarea(get_option('whatsapp_chat_exclude_pages', '')); ?></textarea>
                                </div>
                                
                                <div class="whatsapp-form-group">
                                    <h4>Include Pages</h4>
                                    <p>Show the widget only on specific pages. Leave empty to show on all pages.</p>
                                    <textarea name="include_pages" rows="5" placeholder="Enter page URLs or page IDs, one per line&#10;Example:&#10;/products/&#10;/services/&#10;456"><?php echo esc_textarea(get_option('whatsapp_chat_include_pages', '')); ?></textarea>
                                </div>
                                
                                <div class="whatsapp-form-group">
                                    <h4>Category Control</h4>
                                    <p>Control widget display on specific product categories.</p>
                                    <textarea name="category_rules" rows="5" placeholder="Enter category slugs, one per line&#10;Example:&#10;electronics&#10;clothing&#10;books"><?php echo esc_textarea(get_option('whatsapp_chat_category_rules', '')); ?></textarea>
                                </div>
                                
                                <div class="whatsapp-form-group">
                                    <h4>Product SKU Control</h4>
                                    <p>Control widget display on specific products by SKU.</p>
                                    <textarea name="sku_rules" rows="5" placeholder="Enter product SKUs, one per line&#10;Example:&#10;PROD-001&#10;PROD-002&#10;PROD-003"><?php echo esc_textarea(get_option('whatsapp_chat_sku_rules', '')); ?></textarea>
                                </div>
                                
                                <button type="submit" class="whatsapp-btn whatsapp-btn-primary">
                                    <span class="dashicons dashicons-saved"></span>
                                    Save Display Rules
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Designs Tab
     */
    private function render_designs_tab() {
        // Ensure database tables exist
        $this->ensure_database_tables();
        
        $current_design = get_option('whatsapp_chat_design', 'design-1');
        $license_status = get_option('whatsapp_chat_license_status', 'active'); // Temporary for testing
        ?>
        <div class="whatsapp-designs">
            <div class="whatsapp-card">
                <div class="whatsapp-card-header">
                    <h3>🎨 Choose Design</h3>
                    <p>Select a design for your WhatsApp chat widget</p>
                </div>
                <div class="whatsapp-card-content">
                    <div class="whatsapp-designs-grid">
                        <?php
                        $designs = array(
                            array('id' => 'design-1', 'name' => 'Modern Green', 'premium' => false, 'icon' => '🟢'),
                            array('id' => 'design-2', 'name' => 'Corporate Blue', 'premium' => false, 'icon' => '🔵'),
                            array('id' => 'design-3', 'name' => 'Dark Elegant', 'premium' => false, 'icon' => '⚫'),
                            array('id' => 'design-4', 'name' => 'Gradient Modern', 'premium' => false, 'icon' => '🌈'),
                            array('id' => 'design-5', 'name' => 'Bright Red', 'premium' => false, 'icon' => '🔴'),
                            array('id' => 'design-6', 'name' => 'Professional Banner', 'premium' => true, 'icon' => '💼'),
                            array('id' => 'design-7', 'name' => 'Custom Avatar', 'premium' => true, 'icon' => '👤')
                        );
                        
                        foreach ($designs as $design):
                            $is_premium = $design['premium'] && $license_status !== 'active';
                            $is_selected = $current_design === $design['id'];
                        ?>
                        <div class="whatsapp-design-item <?php echo $is_selected ? 'selected' : ''; ?> <?php echo $is_premium ? 'premium' : ''; ?>" data-design="<?php echo $design['id']; ?>">
                            <div class="design-preview">
                                <div class="design-widget-preview design-<?php echo str_replace('design-', '', $design['id']); ?>">
                                    <?php if ($design['id'] === 'design-6'): ?>
                                        <!-- Design 6 - Professional Banner -->
                                        <div class="preview-widget-container">
                                            <img src="<?php echo WHATSAPP_CHAT_PLUGIN_URL; ?>admin/images/6.png" alt="Professional Banner" style="width: 100%; height: auto; border-radius: 8px;">
                                        </div>
                                    <?php elseif ($design['id'] === 'design-7'): ?>
                                        <!-- Design 7 - Multi Agents -->
                                        <div class="preview-widget-container">
                                            <img src="<?php echo WHATSAPP_CHAT_PLUGIN_URL; ?>admin/images/mutli-agents.png" alt="Multi Agents" style="width: 100%; height: auto; border-radius: 8px;">
                                        </div>
                                    <?php else: ?>
                                        <!-- Designs 1-5 - Circular Button -->
                                        <div class="preview-circular-button">
                                            <div class="preview-whatsapp-icon-large">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                                </svg>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="design-name"><?php echo $design['name']; ?></div>
                                <?php if ($is_premium): ?>
                                <div class="premium-badge">PRO</div>
                                <?php endif; ?>
                            </div>
                            <?php if (!$is_premium): ?>
                            <button class="whatsapp-btn whatsapp-btn-sm select-design <?php echo $is_selected ? 'selected' : ''; ?>" 
                                    data-design="<?php echo $design['id']; ?>"
                                    data-nonce="<?php echo wp_create_nonce('whatsapp_chat_admin_nonce'); ?>">
                                <?php echo $is_selected ? 'Selected' : 'Select'; ?>
                            </button>
                            <?php else: ?>
                            <button class="whatsapp-btn whatsapp-btn-sm whatsapp-btn-secondary" disabled>
                                <span class="dashicons dashicons-lock"></span>
                                Upgrade Required
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Design Options for 1-5 -->
        <div class="whatsapp-card">
            <div class="whatsapp-card-header">
                <h3>💬 Basic Design Options (Designs 1-5)</h3>
                <p>Customize floating message for basic designs</p>
            </div>
            <div class="whatsapp-card-content">
                <form id="basic-design-form" method="post">
                    <?php wp_nonce_field('whatsapp_chat_admin_nonce', 'nonce'); ?>
                    
                    <!-- Basic Design Options for 1-5 -->
                    <div class="whatsapp-form-section" id="design-1-5-options">
                        <h4>💬 Floating Message Settings</h4>
                        
                        <!-- Floating Message Settings -->
                        <div class="whatsapp-form-row">
                            <div class="whatsapp-form-group">
                                <label>💬 Floating Message Text (Designs 1-5)</label>
                                <input type="text" name="whatsapp_chat_floating_message" 
                                       value="<?php echo esc_attr(get_option('whatsapp_chat_floating_message', 'Need Help?')); ?>" 
                                       class="whatsapp-form-control" 
                                       placeholder="Need Help?"
                                       maxlength="30"
                                       id="floating-message-input">
                                <small>Message text that appears next to the WhatsApp icon in designs 1-5 (max 30 characters)</small>
                                <div class="character-count">
                                    <span id="char-count">0</span>/30 characters
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="whatsapp-form-actions">
                        <button type="submit" class="whatsapp-btn whatsapp-btn-primary" id="save-basic-design-options">
                            <span class="dashicons dashicons-saved"></span>
                            Save Basic Design Options
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Design 6 & 7 Special Options -->
        <div class="whatsapp-card">
            <div class="whatsapp-card-header">
                <h3>🎨 Professional Design Options (Designs 6-7)</h3>
                <p>Customize professional banner and avatar designs for Designs 6 & 7</p>
            </div>
            <div class="whatsapp-card-content">
                <form id="design-special-form" method="post">
                    <?php wp_nonce_field('whatsapp_chat_admin_nonce', 'nonce'); ?>
                    
                    <!-- Professional Options for 6-7 -->
                    <div class="whatsapp-form-section" id="design-6-7-options">
                        <h4>🎨 Professional Design Customization</h4>
                        
                        <!-- Avatar Settings -->
                        <div class="whatsapp-form-row">
                            <div class="whatsapp-form-group">
                                <label>Default Avatar Image</label>
                                <div class="whatsapp-avatar-upload">
                                    <button type="button" class="whatsapp-btn whatsapp-btn-secondary" id="upload-design-avatar">
                                        <span class="dashicons dashicons-upload"></span>
                                        Choose Avatar
                                    </button>
                                    <button type="button" class="whatsapp-btn whatsapp-btn-danger" id="remove-design-avatar" style="display: none;">
                                        <span class="dashicons dashicons-trash"></span>
                                        Remove
                                    </button>
                                    <div class="whatsapp-avatar-preview">
                                        <?php 
                                        $design_avatar = get_option('whatsapp_chat_design_avatar', '');
                                        if ($design_avatar): ?>
                                            <img src="<?php echo esc_url($design_avatar); ?>" alt="Avatar Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                        <?php else: ?>
                                            <div>No avatar selected</div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" name="design_avatar" id="design_avatar_url" value="<?php echo esc_attr($design_avatar); ?>">
                                </div>
                                <small>Avatar image for both Design 6 and Design 7</small>
                            </div>
                        </div>

                        <!-- Agent Name -->
                        <div class="whatsapp-form-row">
                            <div class="whatsapp-form-group">
                                <label>Agent Name</label>
                                <input type="text" name="design_agent_name" value="<?php echo esc_attr(get_option('whatsapp_chat_design_agent_name', 'Support Team')); ?>" class="whatsapp-form-control">
                                <small>Name to display in the design</small>
                            </div>
                        </div>

                        <!-- Status Text -->
                        <div class="whatsapp-form-row">
                            <div class="whatsapp-form-group">
                                <label>Status Text</label>
                                <input type="text" name="design_status" value="<?php echo esc_attr(get_option('whatsapp_chat_design_status', 'Online')); ?>" class="whatsapp-form-control">
                                <small>Status text (e.g., "Online", "Available")</small>
                            </div>
                        </div>

                        <!-- Message Text -->
                        <div class="whatsapp-form-row">
                            <div class="whatsapp-form-group">
                                <label>Message Text</label>
                                <input type="text" name="design_message" value="<?php echo esc_attr(get_option('whatsapp_chat_design_message', 'Hello! I have a question')); ?>" class="whatsapp-form-control">
                                <small>Message text to display</small>
                            </div>
                        </div>

                        <!-- Background Color -->
                        <div class="whatsapp-form-row">
                            <div class="whatsapp-form-group">
                                <label>Background Color</label>
                                <input type="color" name="design_bg_color" value="<?php echo esc_attr(get_option('whatsapp_chat_design_bg_color', '#25D364')); ?>" class="whatsapp-form-control">
                                <small>Background color for the design</small>
                            </div>
                        </div>

                        <!-- Text Color -->
                        <div class="whatsapp-form-row">
                            <div class="whatsapp-form-group">
                                <label>Text Color</label>
                                <input type="color" name="design_text_color" value="<?php echo esc_attr(get_option('whatsapp_chat_design_text_color', '#ffffff')); ?>" class="whatsapp-form-control">
                                <small>Text color for the design</small>
                            </div>
                        </div>

                        <!-- Agent Name Color -->
                        <div class="whatsapp-form-row">
                            <div class="whatsapp-form-group">
                                <label>Agent Name Color</label>
                                <input type="color" name="design_name_color" value="<?php echo esc_attr(get_option('whatsapp_chat_design_name_color', '#ffffff')); ?>" class="whatsapp-form-control">
                                <small>Color for the agent name</small>
                            </div>
                        </div>

                        <!-- Status Badge Color -->
                        <div class="whatsapp-form-row">
                            <div class="whatsapp-form-group">
                                <label>Status Badge Color</label>
                                <input type="color" name="design_status_color" value="<?php echo esc_attr(get_option('whatsapp_chat_design_status_color', '#000')); ?>" class="whatsapp-form-control">
                                <small>Color for the status badge</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="whatsapp-form-actions">
                        <button type="button" class="whatsapp-btn whatsapp-btn-secondary" id="reset-design-colors">
                            <span class="dashicons dashicons-image-rotate"></span>
                            Reset Colors
                        </button>
                        <button type="submit" class="whatsapp-btn whatsapp-btn-primary" id="save-design-options">
                            <span class="dashicons dashicons-saved"></span>
                            Save Design Options
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php
    }
    
    /**
     * Render Contacts Tab - Professional Two-Column Layout
     */
    private function render_contacts_tab() {
        // Ensure database tables exist
        $this->ensure_database_tables();
        
        $contacts = $this->get_contacts();
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        $contacts_count = count($contacts);
        
        // If license is not active, show upgrade page
        if ($license_status !== 'active') {
            $this->render_agents_upgrade_page();
            return;
        }
        ?>
        <div class="whatsapp-contacts-container">
            <!-- Header Section -->
            <div class="whatsapp-contacts-header">
                <div class="whatsapp-contacts-title">
                    <h2>👥 Manage Agents</h2>
                    <p>Add and manage your WhatsApp agents</p>
                </div>
                <div class="whatsapp-contacts-stats">
                    <span class="contacts-count"><?php echo $contacts_count; ?> Agents</span>
                    <span class="license-status <?php echo $license_status; ?>">
                        <?php echo $license_status === 'active' ? 'Pro License' : 'Free License'; ?>
                    </span>
                </div>
            </div>

            <!-- Two-Column Layout -->
            <div class="whatsapp-contacts-content">
                <!-- Left Column: Contact List -->
                <div class="whatsapp-contacts-left">
                    <div class="whatsapp-contacts-list">
                        <div class="contacts-list-header">
                            <h3>📋 Current Contacts</h3>
                            <div class="contacts-list-actions">
                                <button type="button" class="whatsapp-btn whatsapp-btn-secondary" id="refresh-contacts">
                                    <span class="dashicons dashicons-update"></span>
                                    Refresh
                                </button>
                            </div>
                        </div>
                        
                        <div class="contacts-list-content">
                            <?php if (empty($contacts)): ?>
                            <div class="whatsapp-empty-state">
                                <div class="empty-state-icon">
                                    <span class="dashicons dashicons-groups"></span>
                                </div>
                                <h4>No agents yet</h4>
                                <p>Add your first WhatsApp agent to get started</p>
                            </div>
                            <?php else: ?>
                            <div class="whatsapp-contacts-grid">
                                <?php foreach ($contacts as $contact): ?>
                                    <div class="whatsapp-contact-item <?php echo $contact->status == 0 ? 'inactive' : ''; ?>" data-contact-id="<?php echo $contact->id; ?>">
                                        <div class="contact-info">
                                            <div class="contact-avatar">
                                                <?php if (!empty($contact->avatar)): ?>
                                                    <img src="<?php echo esc_url($contact->avatar); ?>" alt="<?php echo esc_attr($contact->firstname . ' ' . $contact->lastname); ?>" width="50" height="50">
                                                <?php else: ?>
                                                    <div class="avatar-placeholder">
                                                        <?php echo strtoupper(substr($contact->firstname, 0, 1) . substr($contact->lastname, 0, 1)); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="contact-details">
                                                <h5><?php echo esc_html($contact->firstname . ' ' . $contact->lastname); ?></h5>
                                                <p class="contact-phone"><?php echo esc_html($contact->phone); ?></p>
                                                <?php if ($contact->label): ?>
                                                <span class="contact-label"><?php echo esc_html($contact->label); ?></span>
                                                <?php endif; ?>
                                                <?php if ($contact->status == 0): ?>
                                                <span class="contact-status inactive">Inactive</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <div class="contact-actions">
                                        <button type="button" class="whatsapp-btn-contact edit edit-contact" 
                                                data-id="<?php echo $contact->id; ?>"
                                                data-firstname="<?php echo esc_attr($contact->firstname); ?>"
                                                data-lastname="<?php echo esc_attr($contact->lastname); ?>"
                                                data-phone="<?php echo esc_attr($contact->phone); ?>"
                                                data-label="<?php echo esc_attr($contact->label); ?>"
                                                data-message="<?php echo esc_attr($contact->message); ?>"
                                                data-avatar="<?php echo esc_attr($contact->avatar); ?>"
                                                data-status="<?php echo esc_attr($contact->status); ?>"
                                                data-nonce="<?php echo wp_create_nonce('whatsapp_chat_contact_action'); ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                            Edit
                                        </button>
                                        <button type="button" class="whatsapp-btn-contact delete delete-contact" 
                                                data-id="<?php echo $contact->id; ?>"
                                                data-nonce="<?php echo wp_create_nonce('whatsapp_chat_contact_action'); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Add New Contact -->
                <div class="whatsapp-contacts-right">
                    <div class="whatsapp-add-contact">
                        <div class="add-contact-header">
                            <h3>➕ Add New Contact</h3>
                            <p>Create a new WhatsApp agent</p>
                        </div>
                        
                        <form id="add-contact-form" class="whatsapp-contact-form" onsubmit="return false;">
                            <?php wp_nonce_field('whatsapp_chat_admin_nonce', 'nonce'); ?>
                            <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                    <label for="firstname">First Name *</label>
                                    <input type="text" id="firstname" name="firstname" class="whatsapp-form-control" required>
                                </div>
                                <div class="whatsapp-form-group">
                                    <label for="lastname">Last Name</label>
                                    <input type="text" id="lastname" name="lastname" class="whatsapp-form-control">
                                </div>
                            </div>
                            <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                    <label>Phone Number *</label>
                                    <input type="text" id="phone" name="phone" class="whatsapp-form-control" placeholder="+1234567890" required>
                                </div>
                                <div class="whatsapp-form-group">
                                    <label>Label</label>
                                    <input type="text" id="label" name="label" class="whatsapp-form-control" placeholder="Customer Support">
                                </div>
                            </div>
                            <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                    <label>Default Message</label>
                                    <textarea id="message" name="message" class="whatsapp-form-control" rows="2" placeholder="Hello! How can I help you?"></textarea>
                                </div>
                            </div>
                            
                            <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                    <label>Agent Avatar</label>
                                    <div class="whatsapp-avatar-upload-container">
                                        <div class="whatsapp-avatar-preview" id="avatar-preview" style="display: none;">
                                            <img id="avatar-preview-img" src="" alt="Avatar Preview" width="80" height="80" style="border-radius: 50%; object-fit: cover; border: 2px solid #25D366;">
                                            <button type="button" id="remove-avatar" class="whatsapp-btn whatsapp-btn-sm whatsapp-btn-danger" style="margin-top: 5px;">Remove</button>
                                        </div>
                                        <input type="file" id="avatar-file" name="avatar_file" class="whatsapp-form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" style="display: none;">
                                        <button type="button" id="upload-avatar-btn" class="whatsapp-btn whatsapp-btn-secondary">
                                            <span class="dashicons dashicons-upload"></span>
                                            Upload Avatar
                                        </button>
                                        <button type="button" id="media-library-btn" class="whatsapp-btn whatsapp-btn-secondary">
                                            <span class="dashicons dashicons-admin-media"></span>
                                            Choose from Media Library
                                        </button>
                                        <input type="hidden" id="avatar-url" name="avatar" value="">
                                        <div class="whatsapp-upload-progress" id="upload-progress" style="display: none;">
                                            <div class="progress-bar">
                                                <div class="progress-fill"></div>
                                            </div>
                                            <span class="progress-text">Uploading...</span>
                                        </div>
                                    </div>
                                    <div class="whatsapp-form-note">
                                        <small>Upload an image for the agent's avatar (JPG, PNG, GIF, WebP - Max 2MB) or choose from your media library</small>
                                    </div>
                                </div>
                            </div>
                            <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                    <label class="whatsapp-toggle-switch">
                                        <input type="checkbox" name="status" value="1" checked>
                                        <span class="toggle-label">Active Contact</span>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <div class="whatsapp-form-note">
                                        <small>Toggle to enable/disable this contact</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="whatsapp-form-actions">
                                <button type="submit" class="whatsapp-btn whatsapp-btn-primary">
                                    <span class="dashicons dashicons-plus"></span>
                                    Add Contact
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Edit Contact Modal -->
            <div id="edit-contact-modal" class="whatsapp-modal" style="display: none;">
                <div class="whatsapp-modal-content">
                    <div class="whatsapp-modal-header">
                        <h3>✏️ Edit Contact</h3>
                        <button class="whatsapp-modal-close">&times;</button>
                    </div>
                    <div class="whatsapp-modal-body">
                        <form id="edit-contact-form" method="post">
                            <?php wp_nonce_field('whatsapp_chat_admin_nonce', 'nonce'); ?>
                            <input type="hidden" id="edit_contact_id" name="contact_id">
                            
                            <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                    <label for="edit_firstname">First Name</label>
                                    <input type="text" id="edit_firstname" name="firstname" required>
                                </div>
                                <div class="whatsapp-form-group">
                                    <label for="edit_lastname">Last Name</label>
                                    <input type="text" id="edit_lastname" name="lastname" required>
                                </div>
                            </div>
                            
                            <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                    <label for="edit_phone">Phone Number</label>
                                    <input type="text" id="edit_phone" name="phone" required>
                                </div>
                                <div class="whatsapp-form-group">
                                    <label for="edit_label">Label</label>
                                    <input type="text" id="edit_label" name="label">
                                </div>
                            </div>
                            
                            <div class="whatsapp-form-group">
                                <label for="edit_message">Default Message</label>
                                <textarea id="edit_message" name="message" rows="3"></textarea>
                            </div>
                            
                            <div class="whatsapp-form-group">
                                <label for="edit_avatar">Avatar Image</label>
                                <div class="whatsapp-avatar-upload-container">
                                    <div class="whatsapp-avatar-preview" id="edit-avatar-preview" style="display: none;">
                                        <img id="edit-avatar-preview-img" src="" alt="Avatar Preview" width="80" height="80" style="border-radius: 50%; object-fit: cover; border: 2px solid #25D366;">
                                        <button type="button" id="edit-remove-avatar" class="whatsapp-btn whatsapp-btn-sm whatsapp-btn-danger" style="margin-top: 5px;">Remove</button>
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
                                    <input type="hidden" id="edit-avatar-url" name="avatar" value="">
                                    <div class="whatsapp-upload-progress" id="edit-upload-progress" style="display: none;">
                                        <div class="progress-bar">
                                            <div class="progress-fill"></div>
                                        </div>
                                        <span class="progress-text">Uploading...</span>
                                    </div>
                                </div>
                                <div class="whatsapp-form-note">
                                    <small>Upload an image for the agent's avatar (JPG, PNG, GIF, WebP - Max 2MB) or choose from your media library</small>
                                </div>
                            </div>
                            
                            <div class="whatsapp-form-actions">
                                <button type="button" class="whatsapp-btn whatsapp-btn-secondary whatsapp-modal-close">Cancel</button>
                                <button type="submit" class="whatsapp-btn whatsapp-btn-primary">
                                    <span class="dashicons dashicons-saved"></span>
                                    Update Contact
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Agents Upgrade Page - Professional Design
     */
    private function render_agents_upgrade_page() {
        ?>
        <div class="whatsapp-agents-upgrade">
            <!-- Hero Section -->
            <div class="whatsapp-upgrade-hero">
                <div class="upgrade-hero-content">
                    <div class="upgrade-hero-icon">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <h1>🚀 Multi-Agent WhatsApp System</h1>
                    <p class="upgrade-hero-subtitle">Unlock unlimited agents and advanced features with WhatsApp Chat Pro</p>
                    <div class="upgrade-hero-badge">
                        <span class="dashicons dashicons-star-filled"></span>
                        <span>Pro Feature</span>
                    </div>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="whatsapp-upgrade-features">
                <div class="upgrade-features-grid">
                    <!-- Multi-Agent Management -->
                    <div class="upgrade-feature-card">
                        <div class="feature-icon">
                            <span class="dashicons dashicons-groups"></span>
                        </div>
                        <h3>Unlimited Agents</h3>
                        <p>Add unlimited WhatsApp agents with individual phone numbers, names, and custom messages</p>
                        <ul class="feature-list">
                            <li>✅ Unlimited agent creation</li>
                            <li>✅ Individual phone numbers</li>
                            <li>✅ Custom agent names & labels</li>
                            <li>✅ Personalized messages</li>
                        </ul>
                    </div>

                    <!-- Advanced Analytics -->
                    <div class="upgrade-feature-card">
                        <div class="feature-icon">
                            <span class="dashicons dashicons-chart-line"></span>
                        </div>
                        <h3>Advanced Analytics</h3>
                        <p>Track performance with detailed analytics and reports for each agent</p>
                        <ul class="feature-list">
                            <li>✅ Click tracking per agent</li>
                            <li>✅ Google Analytics 4 integration</li>
                            <li>✅ Detailed performance reports</li>
                            <li>✅ Export data capabilities</li>
                        </ul>
                    </div>

                    <!-- Professional Designs -->
                    <div class="upgrade-feature-card">
                        <div class="feature-icon">
                            <span class="dashicons dashicons-admin-appearance"></span>
                        </div>
                        <h3>Premium Designs</h3>
                        <p>Access professional design templates including Design 6 & 7</p>
                        <ul class="feature-list">
                            <li>✅ Design 6 - Professional Banner</li>
                            <li>✅ Design 7 - Custom Avatar</li>
                            <li>✅ Custom color schemes</li>
                            <li>✅ Advanced animations</li>
                        </ul>
                    </div>

                    <!-- Working Hours -->
                    <div class="upgrade-feature-card">
                        <div class="feature-icon">
                            <span class="dashicons dashicons-clock"></span>
                        </div>
                        <h3>Working Hours</h3>
                        <p>Set custom working hours and offline messages for each agent</p>
                        <ul class="feature-list">
                            <li>✅ Per-agent working hours</li>
                            <li>✅ Custom offline messages</li>
                            <li>✅ Time zone support</li>
                            <li>✅ Automatic status updates</li>
                        </ul>
                    </div>

                    <!-- Display Rules -->
                    <div class="upgrade-feature-card">
                        <div class="feature-icon">
                            <span class="dashicons dashicons-visibility"></span>
                        </div>
                        <h3>Smart Display Rules</h3>
                        <p>Control where and when your widget appears with advanced rules</p>
                        <ul class="feature-list">
                            <li>✅ Page-specific display</li>
                            <li>✅ Category-based rules</li>
                            <li>✅ SKU-based targeting</li>
                            <li>✅ Device-specific display</li>
                        </ul>
                    </div>

                    <!-- Priority Support -->
                    <div class="upgrade-feature-card">
                        <div class="feature-icon">
                            <span class="dashicons dashicons-sos"></span>
                        </div>
                        <h3>Priority Support</h3>
                        <p>Get dedicated support and regular updates with Pro license</p>
                        <ul class="feature-list">
                            <li>✅ Priority email support</li>
                            <li>✅ Regular updates</li>
                            <li>✅ Feature requests</li>
                            <li>✅ Bug fixes priority</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="whatsapp-comparison-table">
                <h2>📊 Free vs Pro Comparison</h2>
                <div class="comparison-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Feature</th>
                                <th class="free-column">Free Version</th>
                                <th class="pro-column">Pro Version</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Number of Agents</td>
                                <td class="free-column">1 Agent</td>
                                <td class="pro-column">✅ Unlimited</td>
                            </tr>
                            <tr>
                                <td>Design Templates</td>
                                <td class="free-column">5 Basic Designs</td>
                                <td class="pro-column">✅ 7 Professional Designs</td>
                            </tr>
                            <tr>
                                <td>Analytics & Tracking</td>
                                <td class="free-column">❌ Not Available</td>
                                <td class="pro-column">✅ Full Analytics</td>
                            </tr>
                            <tr>
                                <td>Working Hours</td>
                                <td class="free-column">❌ Not Available</td>
                                <td class="pro-column">✅ Advanced Scheduling</td>
                            </tr>
                            <tr>
                                <td>Display Rules</td>
                                <td class="free-column">❌ Not Available</td>
                                <td class="pro-column">✅ Smart Rules</td>
                            </tr>
                            <tr>
                                <td>Support</td>
                                <td class="free-column">Community Support</td>
                                <td class="pro-column">✅ Priority Support</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="whatsapp-upgrade-cta-agents">
                <div class="upgrade-cta-content-agents">
                    <h2>🎯 Ready to Upgrade?</h2>
                    <p class="upgrade-cta-subtitle">Join thousands of satisfied customers using WhatsApp Chat Pro</p>
                    
                    <div class="upgrade-pricing">
                        <div class="pricing-card">
                            <div class="pricing-header">
                                <h3>WhatsApp Chat Pro</h3>
                                <div class="pricing-price">
                                    <span class="currency">$</span>
                                    <span class="amount">24</span>
                                    <span class="period">one-time</span>
                                </div>
                            </div>
                            <div class="pricing-features">
                                <ul>
                                    <li>✅ Unlimited agents</li>
                                    <li>✅ Premium designs</li>
                                    <li>✅ Advanced analytics</li>
                                    <li>✅ Working hours</li>
                                    <li>✅ Display rules</li>
                                    <li>✅ Priority support</li>
                                    <li>✅ Lifetime updates</li>
                                </ul>
                            </div>
                            <div class="pricing-cta">
                                <a href="https://getjustplug.com/whatsapp-chat-pro" target="_blank" class="upgrade-button-large">
                                    <span class="dashicons dashicons-cart"></span>
                                    <?php _e( 'Upgrade Now - $24 Only', 'whatsapp-chat-pro' ); ?>
                                </a>
                                <p class="upgrade-guarantee">💯 30-day money-back guarantee</p>
                            </div>
                        </div>
                    </div>

                    <div class="upgrade-testimonials">
                        <h3>🌟 What Our Customers Say</h3>
                        <div class="testimonials-grid">
                            <div class="testimonial-card">
                                <div class="testimonial-content">
                                    <p>"Amazing plugin! The multi-agent feature helped us organize our customer support perfectly."</p>
                                </div>
                                <div class="testimonial-author">
                                    <strong>Sarah M.</strong>
                                    <span>E-commerce Store Owner</span>
                                </div>
                            </div>
                            <div class="testimonial-card">
                                <div class="testimonial-content">
                                    <p>"The analytics feature is incredible. We can track which agents perform best."</p>
                                </div>
                                <div class="testimonial-author">
                                    <strong>Mike R.</strong>
                                    <span>Digital Agency</span>
                                </div>
                            </div>
                            <div class="testimonial-card">
                                <div class="testimonial-content">
                                    <p>"Professional designs and excellent support. Worth every penny!"</p>
                                </div>
                                <div class="testimonial-author">
                                    <strong>Lisa K.</strong>
                                    <span>Consultant</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render License Tab
     */
    private function render_license_tab() {
        $license_key = get_option('whatsapp_chat_license_key', '');
        $license_status = get_option('whatsapp_chat_license_status', 'active'); // Temporary for testing
        $license_expires = get_option('whatsapp_chat_license_expires', '');
        ?>
        <div class="whatsapp-license">
            <div class="whatsapp-card">
                <div class="whatsapp-card-header">
                    <h3>🔑 License Management</h3>
                    <p>Manage your WhatsApp Chat Pro license</p>
                </div>
                <div class="whatsapp-card-content">
                    <div class="license-status">
                        <div class="status-indicator <?php echo $license_status === 'active' ? 'active' : 'inactive'; ?>">
                            <?php echo $license_status === 'active' ? '✅ Active' : '❌ Inactive'; ?>
                        </div>
                        <p>Current Status: <strong><?php echo ucfirst($license_status); ?></strong></p>
                        <?php if ($license_expires): ?>
                        <p>Expires: <strong><?php echo date('F j, Y', strtotime($license_expires)); ?></strong></p>
                        <?php endif; ?>
                    </div>
                    
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <input type="hidden" name="action" value="whatsapp_chat_license_action">
                        <?php wp_nonce_field('whatsapp_chat_license', 'whatsapp_chat_license_nonce'); ?>
                        
                        <div class="whatsapp-form-group">
                            <label>License Key</label>
                            <input type="text" name="whatsapp_chat_license_key" value="<?php echo esc_attr($license_key); ?>" 
                                   class="whatsapp-form-control" placeholder="Enter your license key">
                        </div>
                        
                        <div class="whatsapp-form-actions">
                            <button type="submit" name="license_action" value="activate" class="whatsapp-btn whatsapp-btn-primary">
                                <span class="dashicons dashicons-yes"></span>
                                Activate License
                            </button>
                            <button type="submit" name="license_action" value="deactivate" class="whatsapp-btn whatsapp-btn-secondary">
                                <span class="dashicons dashicons-no"></span>
                                Deactivate License
                            </button>
                        </div>
                    </form>
                    
                    <?php if ($license_status === 'inactive'): ?>
                    <div class="license-upgrade">
                        <h4>🚀 <?php _e( 'Upgrade to Pro', 'whatsapp-chat-pro' ); ?></h4>
                        <p><?php _e( 'Unlock premium features with WhatsApp Chat Pro:', 'whatsapp-chat-pro' ); ?></p>
                        <ul>
                            <li>✅ <?php _e( 'Unlimited Agents', 'whatsapp-chat-pro' ); ?></li>
                            <li>✅ <?php _e( 'Premium Designs', 'whatsapp-chat-pro' ); ?></li>
                            <li>✅ <?php _e( 'Advanced Analytics', 'whatsapp-chat-pro' ); ?></li>
                            <li>✅ <?php _e( 'Priority Support', 'whatsapp-chat-pro' ); ?></li>
                        </ul>
                        <a href="https://getjustplug.com/whatsapp-chat-pro" target="_blank" class="whatsapp-btn whatsapp-btn-primary">
                            <?php _e( 'Upgrade Now - $24 Only', 'whatsapp-chat-pro' ); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Settings page - Simple and Clean
     */
    public function settings_page() {
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        ?>
        <div class="wrap whatsapp-chat-admin">
            <div class="whatsapp-chat-header">
                <h1>
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('WhatsApp Chat Settings', 'whatsapp-chat-pro'); ?>
                </h1>
                <p class="whatsapp-chat-subtitle">
                    <?php _e('Configure your WhatsApp chat widget', 'whatsapp-chat-pro'); ?>
                </p>
            </div>
            
            <div class="whatsapp-nav-tabs">
                <a href="?page=whatsapp-chat-settings&tab=general" class="whatsapp-nav-tab <?php echo $tab === 'general' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php _e('General', 'whatsapp-chat-pro'); ?>
                </a>
                <a href="?page=whatsapp-chat-settings&tab=display" class="whatsapp-nav-tab <?php echo $tab === 'display' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-admin-appearance"></span>
                    <?php _e('Display', 'whatsapp-chat-pro'); ?>
                </a>
                <a href="?page=whatsapp-chat-settings&tab=advanced" class="whatsapp-nav-tab <?php echo $tab === 'advanced' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php _e('Advanced', 'whatsapp-chat-pro'); ?>
                </a>
            </div>
            
            <form id="whatsapp-settings-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="whatsapp_chat_save_settings">
                <?php wp_nonce_field('whatsapp_chat_settings', 'whatsapp_chat_nonce'); ?>
                
                <div class="whatsapp-main-content">
                    <?php
                    switch ($tab) {
                        case 'general':
                            $this->render_general_tab();
                            break;
                        case 'display':
                            $this->render_display_tab();
                            break;
                        case 'advanced':
                            $this->render_advanced_tab();
                            break;
                    }
                    ?>
                </div>
                
                <div class="whatsapp-form-actions">
                    <button type="submit" class="whatsapp-btn whatsapp-btn-primary">
                        <span class="dashicons dashicons-saved"></span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render general tab
     */
    private function render_general_tab() {
        $enabled = get_option('whatsapp_chat_enabled', '1');
        $phone_number = get_option('whatsapp_chat_phone_number', '');
        $default_message = get_option('whatsapp_chat_default_message', 'Hello! I have a question about your products.');
        $enable_agents = get_option('whatsapp_chat_enable_agents', '0');
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        
        ?>
        <div class="whatsapp-card">
            <div class="whatsapp-card-header">
                <h3>⚙️ General Settings</h3>
            </div>
            <div class="whatsapp-card-content">
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                    <label>
                        <input type="checkbox" name="whatsapp_chat_enabled" value="1" <?php checked($enabled, '1'); ?>>
                            ✅ Enable WhatsApp Chat Widget
                    </label>
                        <small>Show the WhatsApp chat button on your website</small>
                    </div>
                </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>📱 WhatsApp Phone Number</label>
                        <input type="text" name="whatsapp_chat_phone_number" value="<?php echo esc_attr($phone_number); ?>" 
                               class="whatsapp-form-control" required>
                        <small>Enter your WhatsApp number with country code (e.g., +1234567890)</small>
                        <?php if (empty($phone_number)): ?>
                        <div class="whatsapp-settings-warning">
                            <span class="dashicons dashicons-warning"></span>
                            Please enter your WhatsApp phone number to enable the chat widget
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>💬 Default Message</label>
                        <textarea name="whatsapp_chat_default_message" class="whatsapp-form-control" rows="3"><?php echo esc_textarea($default_message); ?></textarea>
                        <small>Message that will be pre-filled when customers click the button</small>
                    </div>
                </div>
                
                        <div class="whatsapp-form-row">
                            <div class="whatsapp-form-group">
                                <label>💬 Floating Message (Designs 1-5)</label>
                                <input type="text" name="whatsapp_chat_floating_message" value="<?php echo esc_attr(get_option('whatsapp_chat_floating_message', 'Need Help?')); ?>" class="whatsapp-form-control" placeholder="Need Help?">
                                <small>Floating message text that appears next to the WhatsApp icon in designs 1-5</small>
                            </div>
                        </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                    <label>
                        <input type="checkbox" name="whatsapp_chat_enable_agents" value="1" <?php checked($enable_agents, '1'); ?>>
                            👥 Enable Multiple Agents
                            <?php if ($license_status === 'inactive'): ?>
                            <span class="premium-badge">Pro Feature</span>
                            <?php endif; ?>
                    </label>
                        <small>
                            Allow customers to choose from multiple agents
                            <?php if ($license_status === 'inactive'): ?>
                            <br><strong>Free version limited to 1 agent. Upgrade to Pro for unlimited agents.</strong>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render display tab
     */
    private function render_display_tab() {
        $button_position = get_option('whatsapp_chat_button_position', 'bottom-right');
        $button_layout = get_option('whatsapp_chat_button_layout', 'button');
        $show_on_mobile = get_option('whatsapp_chat_show_on_mobile', '1');
        $show_on_desktop = get_option('whatsapp_chat_show_on_desktop', '1');
        $design = get_option('whatsapp_chat_design', 'design-1');
        $header_text = get_option('whatsapp_chat_header_text', 'Chat with us');
        $footer_text = get_option('whatsapp_chat_footer_text', "We're here to help!");
        $support_team_name = get_option('whatsapp_chat_support_team_name', 'Support Team');
        $support_team_label = get_option('whatsapp_chat_support_team_label', 'Customer Support');
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        
        ?>
        <div class="whatsapp-card">
            <div class="whatsapp-card-header">
                <h3>🎨 Display Settings</h3>
            </div>
            <div class="whatsapp-card-content">
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>📍 Button Position</label>
                        <select name="whatsapp_chat_button_position" class="whatsapp-form-control">
                            <option value="bottom-right" <?php selected($button_position, 'bottom-right'); ?>>Bottom Right</option>
                            <option value="bottom-left" <?php selected($button_position, 'bottom-left'); ?>>Bottom Left</option>
                            <option value="top-right" <?php selected($button_position, 'top-right'); ?>>Top Right</option>
                            <option value="top-left" <?php selected($button_position, 'top-left'); ?>>Top Left</option>
                    </select>
                    </div>
                </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>📱 Show on Mobile</label>
                    <label>
                        <input type="checkbox" name="whatsapp_chat_show_on_mobile" value="1" <?php checked($show_on_mobile, '1'); ?>>
                            Show widget on mobile devices
                    </label>
                    </div>
                </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>💻 Show on Desktop</label>
                    <label>
                        <input type="checkbox" name="whatsapp_chat_show_on_desktop" value="1" <?php checked($show_on_desktop, '1'); ?>>
                            Show widget on desktop devices
                    </label>
                    </div>
                </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>🎨 Current Design</label>
                        <div class="whatsapp-current-design">
                            <span class="design-badge design-<?php echo str_replace('design-', '', $design); ?>">
                                <?php 
                                $design_names = array(
                                    'design-1' => 'Classic Green',
                                    'design-2' => 'Modern Blue', 
                                    'design-3' => 'Minimal White',
                                    'design-4' => 'Dark Theme',
                                    'design-5' => 'Premium Gold',
                                    'design-6' => 'Professional Gradient',
                                    'design-7' => 'Ultimate Premium'
                                );
                                echo $design_names[$design] ?? 'Unknown Design';
                                ?>
                            </span>
                            <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-designs'); ?>" class="whatsapp-btn whatsapp-btn-sm whatsapp-btn-secondary">
                                <span class="dashicons dashicons-admin-appearance"></span>
                                Change Design
                            </a>
                        </div>
                        <small>Go to Designs tab to change the widget design</small>
                    </div>
                </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>💬 Chat Header Text</label>
                        <input type="text" name="whatsapp_chat_header_text" value="<?php echo esc_attr($header_text); ?>" 
                               class="whatsapp-form-control">
                    </div>
                </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>📝 Chat Footer Text</label>
                        <input type="text" name="whatsapp_chat_footer_text" value="<?php echo esc_attr($footer_text); ?>" 
                               class="whatsapp-form-control">
                    </div>
                </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>👤 Support Team Name</label>
                        <input type="text" name="whatsapp_chat_support_team_name" value="<?php echo esc_attr($support_team_name); ?>" 
                               class="whatsapp-form-control">
                    </div>
                </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>🏷️ Support Team Label</label>
                        <input type="text" name="whatsapp_chat_support_team_label" value="<?php echo esc_attr($support_team_label); ?>" 
                               class="whatsapp-form-control">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render advanced tab
     */
    private function render_advanced_tab() {
        $time_restrictions = get_option('whatsapp_chat_time_restrictions', '0');
        $start_time = get_option('whatsapp_chat_start_time', '09:00');
        $end_time = get_option('whatsapp_chat_end_time', '18:00');
        $enable_analytics = get_option('whatsapp_chat_enable_analytics', '0');
        $ga4_measurement_id = get_option('whatsapp_chat_ga4_measurement_id', '');
        $analytics_event_name = get_option('whatsapp_chat_analytics_event_name', 'whatsapp_click');
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        
        ?>
        <div class="whatsapp-card">
            <div class="whatsapp-card-header">
                <h3>🔧 Advanced Settings</h3>
            </div>
            <div class="whatsapp-card-content">
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                    <label>
                        <input type="checkbox" name="whatsapp_chat_time_restrictions" value="1" <?php checked($time_restrictions, '1'); ?>>
                            ⏰ Enable Time Restrictions
                    </label>
                        <small>Show the widget only during specific hours</small>
                    </div>
                </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>🕘 Start Time</label>
                        <input type="time" name="whatsapp_chat_start_time" value="<?php echo esc_attr($start_time); ?>" 
                               class="whatsapp-form-control">
                    </div>
                </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>🕕 End Time</label>
                        <input type="time" name="whatsapp_chat_end_time" value="<?php echo esc_attr($end_time); ?>" 
                               class="whatsapp-form-control">
                    </div>
                </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                    <label>
                        <input type="checkbox" name="whatsapp_chat_enable_analytics" value="1" <?php checked($enable_analytics, '1'); ?>>
                            📊 Enable Google Analytics Tracking
                            <?php if ($license_status === 'inactive'): ?>
                            <span class="premium-badge">Pro Feature</span>
                            <?php endif; ?>
                    </label>
                        <small>
                            Track WhatsApp button clicks with Google Analytics
                            <?php if ($license_status === 'inactive'): ?>
                            <br><strong>Analytics tracking is available in the Pro version.</strong>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
                
                <?php if ($license_status === 'active'): ?>
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>📊 GA4 Measurement ID</label>
                        <input type="text" name="whatsapp_chat_ga4_measurement_id" value="<?php echo esc_attr($ga4_measurement_id); ?>" 
                               class="whatsapp-form-control" placeholder="G-XXXXXXXXXX">
                        <small>Enter your Google Analytics 4 Measurement ID</small>
                    </div>
                </div>
                
                <div class="whatsapp-form-row">
                    <div class="whatsapp-form-group">
                        <label>📈 Analytics Event Name</label>
                        <input type="text" name="whatsapp_chat_analytics_event_name" value="<?php echo esc_attr($analytics_event_name); ?>" 
                               class="whatsapp-form-control">
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Contacts page - Simple Management
     */
    public function contacts_page() {
        $contacts = $this->get_contacts();
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        $contacts_count = count($contacts);
        $max_contacts = ($license_status === 'active') ? 'unlimited' : '1';
        
        ?>
        <div class="wrap whatsapp-chat-admin">
            <div class="whatsapp-chat-header">
                <h1>
                    <span class="dashicons dashicons-groups"></span>
                    <?php _e('WhatsApp Chat Contacts', 'whatsapp-chat-pro'); ?>
                </h1>
                <p class="whatsapp-chat-subtitle">
                    <?php printf(__('Manage your WhatsApp agents (%s/%s)', 'whatsapp-chat-pro'), $contacts_count, $max_contacts); ?>
                </p>
            </div>
            
            <div class="whatsapp-nav-tabs">
                <a href="?page=whatsapp-chat-pro" class="whatsapp-nav-tab">
                    <span class="dashicons dashicons-dashboard"></span>
                    <?php _e('Dashboard', 'whatsapp-chat-pro'); ?>
                </a>
                <a href="?page=whatsapp-chat-settings" class="whatsapp-nav-tab">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('Settings', 'whatsapp-chat-pro'); ?>
                </a>
                <a href="?page=whatsapp-chat-contacts" class="whatsapp-nav-tab active">
                    <span class="dashicons dashicons-groups"></span>
                    <?php _e('Contacts', 'whatsapp-chat-pro'); ?>
                </a>
                <a href="?page=whatsapp-chat-license" class="whatsapp-nav-tab">
                    <span class="dashicons dashicons-admin-network"></span>
                    <?php _e('License', 'whatsapp-chat-pro'); ?>
                </a>
                </div>
            
            <?php if ($license_status === 'inactive' && $contacts_count >= 1): ?>
            <div class="whatsapp-upgrade-prompt">
                <div class="upgrade-content">
                    <h3>🚀 Upgrade to Pro for Unlimited Agents</h3>
                    <p>Free version is limited to 1 agent. Upgrade to Pro for unlimited agents and premium features!</p>
                    <a href="https://getjustplug.com/whatsapp-chat-pro" target="_blank" class="whatsapp-btn whatsapp-btn-primary">
                        Upgrade Now - $24 Only
                    </a>
            </div>
            </div>
            <?php endif; ?>
            
            <div class="whatsapp-main-content">
                <!-- Add Contact Form -->
                <div class="whatsapp-card" id="add-contact-form" style="display: none;">
                    <div class="whatsapp-card-header">
                        <h3>➕ Add New Contact</h3>
                        <button type="button" class="whatsapp-btn whatsapp-btn-secondary" id="cancel-add-contact">
                            <span class="dashicons dashicons-no-alt"></span>
                            Cancel
                        </button>
                    </div>
                    <div class="whatsapp-card-content">
                        <form id="contact-form" method="post" action="" onsubmit="return false;">
                            <?php wp_nonce_field('whatsapp_chat_contact_action', 'whatsapp_chat_contact_nonce'); ?>
                            <input type="hidden" name="action" value="add_contact">
                            
                            <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                    <label for="firstname">First Name *</label>
                                    <input type="text" id="firstname" name="firstname" class="whatsapp-form-control" required>
                                </div>
                                <div class="whatsapp-form-group">
                                    <label for="lastname">Last Name</label>
                                    <input type="text" id="lastname" name="lastname" class="whatsapp-form-control">
                                </div>
                            </div>
                            
                            <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                    <label for="phone">Phone Number *</label>
                                    <input type="tel" id="phone" name="phone" class="whatsapp-form-control" placeholder="+1234567890" required>
                                </div>
                                <div class="whatsapp-form-group">
                                    <label for="label">Label/Title</label>
                                    <input type="text" id="label" name="label" class="whatsapp-form-control" placeholder="Customer Support">
                                </div>
                            </div>
                            
                            <div class="whatsapp-form-group">
                                <label for="message">Default Message</label>
                                <textarea id="message" name="message" class="whatsapp-form-control" rows="3" placeholder="Hello! I have a question about your products."></textarea>
                            </div>
                            
                            <div class="whatsapp-form-group">
                                <label for="avatar">Agent Avatar</label>
                                <div class="whatsapp-avatar-upload-container">
                                    <div class="whatsapp-avatar-preview" id="avatar-preview" style="display: none;">
                                        <img id="avatar-preview-img" src="" alt="Avatar Preview" width="60" height="60" style="border-radius: 50%; object-fit: cover; border: 2px solid #25D366;">
                                        <button type="button" id="remove-avatar" class="whatsapp-btn whatsapp-btn-sm whatsapp-btn-danger" style="margin-top: 5px;">Remove</button>
                                    </div>
                                    <input type="file" id="avatar" name="avatar" class="whatsapp-form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" style="display: none;">
                                    <button type="button" id="upload-avatar-btn" class="whatsapp-btn whatsapp-btn-secondary">
                                        <span class="dashicons dashicons-upload"></span>
                                        Upload Avatar
                                    </button>
                                    <input type="hidden" id="avatar-url" name="avatar" value="">
                                    <div class="whatsapp-upload-progress" id="upload-progress" style="display: none;">
                                        <div class="progress-bar">
                                            <div class="progress-fill"></div>
                                        </div>
                                        <span class="progress-text">Uploading...</span>
                                    </div>
                                </div>
                                <div class="whatsapp-form-note">
                                    <small>Recommended size: 100x100px. Supported formats: JPG, PNG, GIF, WebP (Max 2MB)</small>
                                </div>
                            </div>
                            
                            <div class="whatsapp-form-actions">
                                <button type="submit" class="whatsapp-btn whatsapp-btn-primary">
                                    <span class="dashicons dashicons-saved"></span>
                                    Add Contact
                                </button>
                                <button type="button" class="whatsapp-btn whatsapp-btn-secondary" id="cancel-add-contact">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="whatsapp-card">
                    <div class="whatsapp-card-header">
                        <h3>👥 Contact Management</h3>
                        <?php if ($license_status === 'active' || $contacts_count < 1): ?>
                        <button type="button" class="whatsapp-btn whatsapp-btn-primary" id="add-contact-btn">
                            <span class="dashicons dashicons-plus-alt"></span>
                            Add New Contact
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="whatsapp-card-content">
                        <?php if (empty($contacts)): ?>
                        <div class="whatsapp-empty-state">
                            <div class="empty-icon">👥</div>
                            <h3>No agents configured</h3>
                            <p>Add your first WhatsApp agent to get started</p>
                            <?php if ($license_status === 'active' || $contacts_count < 1): ?>
                            <button type="button" class="whatsapp-btn whatsapp-btn-primary" id="add-contact-btn">
                                <span class="dashicons dashicons-plus-alt"></span>
                                Add First Agent
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="whatsapp-contacts-list">
                    <?php foreach ($contacts as $contact): ?>
                            <div class="whatsapp-contact-item">
                                <div class="contact-avatar">
                                    <?php if (!empty($contact['avatar'])): ?>
                                    <img src="<?php echo esc_url($contact['avatar']); ?>" alt="<?php echo esc_attr($contact['firstname']); ?>">
                                    <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?php echo strtoupper(substr($contact['firstname'], 0, 1)); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="contact-info">
                                    <h4><?php echo esc_html($contact['firstname'] . ' ' . $contact['lastname']); ?></h4>
                                    <p><?php echo esc_html($contact['phone']); ?></p>
                                    <span class="contact-label"><?php echo esc_html($contact['label']); ?></span>
                                </div>
                                <div class="contact-status">
                                    <span class="status-badge <?php echo $contact['status'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $contact['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                                <div class="contact-actions">
                                    <button type="button" class="whatsapp-btn whatsapp-btn-sm whatsapp-btn-secondary edit-contact" data-id="<?php echo $contact['id']; ?>">
                                        Edit
                            </button>
                                    <button type="button" class="whatsapp-btn whatsapp-btn-sm whatsapp-btn-danger delete-contact" data-id="<?php echo $contact['id']; ?>">
                                        Delete
                            </button>
                                </div>
                            </div>
                    <?php endforeach; ?>
        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Designs page - Show all 7 designs
     */
    public function designs_page() {
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        $current_design = get_option('whatsapp_chat_design', 'design-1');
        
        ?>
        <div class="wrap whatsapp-chat-admin">
            <div class="whatsapp-chat-header">
                <h1>
                    <span class="dashicons dashicons-admin-appearance"></span>
                    <?php _e('WhatsApp Chat Designs', 'whatsapp-chat-pro'); ?>
                </h1>
                <p class="whatsapp-chat-subtitle">
                    <?php _e('Choose from 7 beautiful designs for your WhatsApp chat widget', 'whatsapp-chat-pro'); ?>
                </p>
            </div>
            
            <div class="whatsapp-nav-tabs">
                <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-pro'); ?>" class="whatsapp-nav-tab">
                    <span class="dashicons dashicons-dashboard"></span>
                    <?php _e('Dashboard', 'whatsapp-chat-pro'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-settings'); ?>" class="whatsapp-nav-tab">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('Settings', 'whatsapp-chat-pro'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-designs'); ?>" class="whatsapp-nav-tab active">
                    <span class="dashicons dashicons-admin-appearance"></span>
                    <?php _e('Designs', 'whatsapp-chat-pro'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-contacts'); ?>" class="whatsapp-nav-tab">
                    <span class="dashicons dashicons-groups"></span>
                    <?php _e('Contacts', 'whatsapp-chat-pro'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-license'); ?>" class="whatsapp-nav-tab">
                    <span class="dashicons dashicons-admin-network"></span>
                    <?php _e('License', 'whatsapp-chat-pro'); ?>
                </a>
            </div>
            
            <div class="whatsapp-main-content">
                <!-- Free Designs -->
                <div class="whatsapp-card">
                    <div class="whatsapp-card-header">
                        <h3>🆓 Free Designs</h3>
                        <p>5 beautiful designs included with the free version</p>
                    </div>
                    <div class="whatsapp-card-content">
                        <div class="designs-grid">
                            <?php $this->render_design_preview('design-1', 'Design 1 - Modern Minimalist (Green)', 'Clean green design with modern minimalist approach', false, $current_design, $license_status); ?>
                            <?php $this->render_design_preview('design-2', 'Design 2 - Corporate Blue', 'Professional blue theme for corporate websites', false, $current_design, $license_status); ?>
                            <?php $this->render_design_preview('design-3', 'Design 3 - Dark Elegant', 'Elegant dark theme for sophisticated websites', false, $current_design, $license_status); ?>
                            <?php $this->render_design_preview('design-4', 'Design 4 - Gradient Modern', 'Modern gradient design with smooth color transitions', false, $current_design, $license_status); ?>
                            <?php $this->render_design_preview('design-5', 'Design 5 - Bright Vibrant (Red)', 'Bright red design for attention-grabbing websites', false, $current_design, $license_status); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Pro Designs -->
                <div class="whatsapp-card">
                    <div class="whatsapp-card-header">
                        <h3>🚀 Pro Designs</h3>
                        <p>2 premium designs available with Pro version ($24)</p>
                    </div>
                    <div class="whatsapp-card-content">
                        <div class="designs-grid">
                            <?php $this->render_design_preview('design-6', 'Design 6 - Professional Banner (Green)', 'Professional banner design with agent avatar and status', true, $current_design, $license_status); ?>
                            <?php $this->render_design_preview('design-7', 'Design 7 - Custom Avatar Professional', 'Professional design with custom avatar and chat bubble', true, $current_design, $license_status); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Simple Upgrade Prompt for Free Users -->
                <?php if ($license_status === 'inactive'): ?>
                <div class="whatsapp-simple-upgrade">
                    <div class="upgrade-content">
                        <h3>🚀 Want More Professional Designs?</h3>
                        <p>Get 2 additional premium designs with agent avatars and professional layouts</p>
                        <a href="https://getjustplug.com/whatsapp-chat-pro" target="_blank" class="whatsapp-btn whatsapp-btn-primary">
                            <?php _e( 'Upgrade to Pro', 'whatsapp-chat-pro' ); ?> - $24
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render design preview
     */
    private function render_design_preview($design_id, $name, $description, $is_premium, $current_design, $license_status) {
        $is_locked = $is_premium && $license_status !== 'active';
        $is_selected = $current_design === $design_id;
        
        ?>
        <div class="design-preview-card <?php echo $is_locked ? 'locked' : ''; ?> <?php echo $is_selected ? 'selected' : ''; ?>" 
             data-design="<?php echo $design_id; ?>">
            <div class="design-preview-header">
                <h4><?php echo $name; ?></h4>
                <?php if ($is_premium): ?>
                <span class="premium-badge">Pro</span>
                <?php endif; ?>
            </div>
            
            <div class="design-preview-content">
        <div class="design-mockup design-<?php echo str_replace('design-', '', $design_id); ?>">
            <?php if ($design_id === 'design-6'): ?>
                <div class="mockup-button">
                    <div class="mockup-avatar">T</div>
                    <div class="mockup-banner">
                        <div style="font-weight: bold; font-size: 11px; margin-bottom: 2px;">Tommy Wilton</div>
                        <div style="font-size: 9px; opacity: 0.9; background: rgba(255,255,255,0.2); padding: 1px 4px; border-radius: 6px; display: inline-block;">Online</div>
                        <div style="font-size: 9px; opacity: 0.95; margin-top: 2px;">Need Help? Chat with us</div>
                    </div>
                </div>
            <?php elseif ($design_id === 'design-7'): ?>
                <div class="mockup-button">
                    <div class="mockup-avatar">T</div>
                    <div class="mockup-bubble">
                        <div style="font-weight: bold; font-size: 11px; margin-bottom: 2px;">Tommy Wilton</div>
                        <div style="font-size: 9px; opacity: 0.9; background: rgba(255,255,255,0.2); padding: 1px 4px; border-radius: 6px; display: inline-block;">Online</div>
                        <div style="font-size: 9px; opacity: 0.95; margin-top: 2px;">Need Help? Chat via WhatsApp</div>
                        <div class="mockup-whatsapp-icon">💬</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="mockup-button">
                    <span class="mockup-icon">💬</span>
                </div>
            <?php endif; ?>
        </div>
                
                <p class="design-description"><?php echo $description; ?></p>
                
                <?php if ($is_locked): ?>
                <div class="design-locked">
                    <span class="lock-icon">🔒</span>
                    <p>Pro Feature</p>
                    <a href="https://getjustplug.com/whatsapp-chat-pro" target="_blank" class="whatsapp-btn whatsapp-btn-sm whatsapp-btn-primary">
                        Upgrade to Unlock
                    </a>
                </div>
                <?php else: ?>
                <div class="design-actions">
                    <?php if ($is_selected): ?>
                    <span class="selected-badge">✅ Selected</span>
                    <?php else: ?>
                    <button class="whatsapp-btn whatsapp-btn-sm whatsapp-btn-primary select-design" data-design="<?php echo $design_id; ?>">
                        Select Design
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * License page - Simple Activation
     */
    public function license_page() {
        $license_key = get_option('whatsapp_chat_license_key', '');
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        $license_expires = get_option('whatsapp_chat_license_expires', '');
        
        ?>
        <div class="wrap whatsapp-chat-admin">
            <div class="whatsapp-chat-header">
                <h1>
                    <span class="dashicons dashicons-admin-network"></span>
                    <?php _e('WhatsApp Chat Pro License', 'whatsapp-chat-pro'); ?>
                </h1>
                <p class="whatsapp-chat-subtitle">
                    <?php _e('Activate your license to unlock premium features', 'whatsapp-chat-pro'); ?>
                </p>
            </div>
            
            <div class="whatsapp-nav-tabs">
                <a href="?page=whatsapp-chat-pro" class="whatsapp-nav-tab">
                    <span class="dashicons dashicons-dashboard"></span>
                    <?php _e('Dashboard', 'whatsapp-chat-pro'); ?>
                </a>
                <a href="?page=whatsapp-chat-settings" class="whatsapp-nav-tab">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('Settings', 'whatsapp-chat-pro'); ?>
                </a>
                <a href="?page=whatsapp-chat-contacts" class="whatsapp-nav-tab">
                    <span class="dashicons dashicons-groups"></span>
                    <?php _e('Contacts', 'whatsapp-chat-pro'); ?>
                </a>
                <a href="?page=whatsapp-chat-license" class="whatsapp-nav-tab active">
                    <span class="dashicons dashicons-admin-network"></span>
                    <?php _e('License', 'whatsapp-chat-pro'); ?>
                </a>
            </div>
            
            <div class="whatsapp-main-content">
                <div class="whatsapp-card">
                    <div class="whatsapp-card-header">
                        <h3>🔑 License Information</h3>
                    </div>
                    <div class="whatsapp-card-content">
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                            <input type="hidden" name="action" value="whatsapp_chat_license_action">
                <?php wp_nonce_field('whatsapp_chat_license', 'whatsapp_chat_license_nonce'); ?>
                
                            <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                    <label>🔑 License Key</label>
                                    <input type="text" name="whatsapp_chat_license_key" value="<?php echo esc_attr($license_key); ?>" 
                                           class="whatsapp-form-control" placeholder="Enter your license key">
                                    <small>Enter your license key to activate premium features</small>
                                </div>
                            </div>
                            
                            <div class="whatsapp-form-row">
                                <div class="whatsapp-form-group">
                                    <label>📊 License Status</label>
                                    <div class="license-status-display">
                            <span class="license-status <?php echo $license_status; ?>">
                                <?php echo ucfirst($license_status); ?>
                            </span>
                            <?php if ($license_expires): ?>
                                            <small>Expires: <?php echo $license_expires; ?></small>
                            <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="whatsapp-form-actions">
                                <button type="submit" name="license_action" value="activate" class="whatsapp-btn whatsapp-btn-primary">
                                    <span class="dashicons dashicons-admin-network"></span>
                                    Activate License
                    </button>
                                <?php if ($license_status === 'active'): ?>
                                <button type="submit" name="license_action" value="deactivate" class="whatsapp-btn whatsapp-btn-secondary">
                                    Deactivate License
                    </button>
                                <?php endif; ?>
                            </div>
            </form>
                    </div>
                </div>
                
                <!-- Upgrade Information -->
                <div class="whatsapp-upgrade-prompt">
                    <div class="upgrade-content">
                        <h3>🚀 Upgrade to Pro Version</h3>
                        <p>Get unlimited agents, premium designs, advanced analytics, and priority support for just $24!</p>
                        <div class="upgrade-comparison">
                            <div class="comparison-item">
                                <h4>✅ Free Version</h4>
                                <ul>
                                    <li>1 Agent</li>
                                    <li>4 Basic Designs</li>
                                    <li>Basic Features</li>
                                </ul>
                            </div>
                            <div class="comparison-item">
                                <h4>🚀 Pro Version - $24</h4>
                                <ul>
                                    <li>Unlimited Agents</li>
                                    <li>7 Premium Designs</li>
                                    <li>Advanced Analytics</li>
                                    <li>Priority Support</li>
                                </ul>
                            </div>
                        </div>
                        <a href="https://getjustplug.com/whatsapp-chat-pro" target="_blank" class="whatsapp-btn whatsapp-btn-primary">
                            <?php _e( 'Upgrade Now - $24 Only', 'whatsapp-chat-pro' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get contacts
     */
    private function get_contacts() {
        // Ensure database tables exist
        $this->ensure_database_tables();
        
        // Get contacts from database
        $contacts = WhatsApp_Chat_Database::get_contacts();
        
        // Return contacts without logging
        
        return $contacts;
    }
    
    /**
     * Ensure database tables exist
     */
    private function ensure_database_tables() {
        global $wpdb;
        
        // Check if new tables exist
        $tables_to_check = array(
            'whatsapp_chat_contacts',
            'whatsapp_chat_departments',
            'whatsapp_chat_working_hours',
            'whatsapp_chat_holidays',
            'whatsapp_chat_analytics',
            'whatsapp_chat_display_rules'
        );
        
        $missing_tables = array();
        
        foreach ($tables_to_check as $table) {
            $table_name = $wpdb->prefix . $table;
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
            
            if (!$table_exists) {
                $missing_tables[] = $table;
            }
        }
        
        // If any tables are missing, create them
        if (!empty($missing_tables)) {
            WhatsApp_Chat_Database::create_tables();
        }
    }
    
    /**
     * Handle settings save
     */
    public function handle_settings_save() {
        if (!wp_verify_nonce($_POST['whatsapp_chat_nonce'], 'whatsapp_chat_settings')) {
            wp_die(__('Security check failed', 'whatsapp-chat-pro'));
        }
        
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_die(__('You do not have sufficient permissions', 'whatsapp-chat-pro'));
        }
        
        $settings = array(
            'whatsapp_chat_enabled',
            'whatsapp_chat_phone_number',
            'whatsapp_chat_default_message',
            'whatsapp_chat_floating_message',
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
            'whatsapp_chat_time_restrictions',
            'whatsapp_chat_start_time',
            'whatsapp_chat_end_time',
            'whatsapp_chat_enable_analytics',
            'whatsapp_chat_ga4_measurement_id',
            'whatsapp_chat_analytics_event_name',
            'whatsapp_chat_button_animation',
            'whatsapp_chat_button_x',
            'whatsapp_chat_button_y'
        );
        
        foreach ($settings as $setting) {
            if (isset($_POST[$setting])) {
                update_option($setting, sanitize_text_field($_POST[$setting]));
            }
        }
        
        wp_redirect(admin_url('admin.php?page=whatsapp-chat-pro&updated=1'));
        exit;
    }
    
    /**
     * Handle license action
     */
    public function handle_license_action() {
        if (!wp_verify_nonce($_POST['whatsapp_chat_license_nonce'], 'whatsapp_chat_license')) {
            wp_die(__('Security check failed', 'whatsapp-chat-pro'));
        }
        
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_die(__('You do not have sufficient permissions', 'whatsapp-chat-pro'));
        }
        
        $action = isset($_POST['license_action']) ? sanitize_text_field($_POST['license_action']) : '';
        $license_key = isset($_POST['whatsapp_chat_license_key']) ? sanitize_text_field($_POST['whatsapp_chat_license_key']) : '';
        
        if ($action === 'activate') {
            // Simulate license activation
            update_option('whatsapp_chat_license_key', $license_key);
            update_option('whatsapp_chat_license_status', 'active');
            update_option('whatsapp_chat_license_expires', date('Y-m-d', strtotime('+1 year')));
            
            wp_redirect(admin_url('admin.php?page=whatsapp-chat-license&activated=1'));
        } elseif ($action === 'deactivate') {
            update_option('whatsapp_chat_license_status', 'inactive');
            wp_redirect(admin_url('admin.php?page=whatsapp-chat-license&deactivated=1'));
        }
        
        exit;
    }
    
    /**
     * AJAX save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('whatsapp_chat_admin_nonce', 'nonce');
        
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
        }
        
        $settings = array(
            'whatsapp_chat_enabled',
            'whatsapp_chat_phone_number',
            'whatsapp_chat_default_message',
            'whatsapp_chat_floating_message',
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
            'whatsapp_chat_time_restrictions',
            'whatsapp_chat_start_time',
            'whatsapp_chat_end_time',
            'whatsapp_chat_enable_analytics',
            'whatsapp_chat_ga4_measurement_id',
            'whatsapp_chat_analytics_event_name',
            'whatsapp_chat_button_animation',
            'whatsapp_chat_button_x',
            'whatsapp_chat_button_y'
        );
        
        foreach ($settings as $setting) {
            if (isset($_POST[$setting])) {
                update_option($setting, sanitize_text_field($_POST[$setting]));
            }
        }
        
        wp_send_json_success(__('Settings saved successfully!', 'whatsapp-chat-pro'));
    }
    
    /**
     * AJAX save analytics settings
     */
    public function ajax_save_analytics_settings() {
        // Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'whatsapp_chat_admin_nonce' ) ) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error('Insufficient permissions');
        }
        
        // Save analytics settings
        if (isset($_POST['ga4_measurement_id'])) {
            update_option('whatsapp_chat_ga4_measurement_id', sanitize_text_field($_POST['ga4_measurement_id']));
        }
        
        if (isset($_POST['analytics_event_name'])) {
            update_option('whatsapp_chat_analytics_event_name', sanitize_text_field($_POST['analytics_event_name']));
        }
        
        wp_send_json_success('Analytics settings saved successfully');
    }
    
    /**
     * AJAX save display rules
     */
    public function ajax_save_display_rules() {
        // Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'whatsapp_chat_admin_nonce' ) ) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error('Insufficient permissions');
        }
        
        // Save display rules settings - FIXED: Map form field names to option names
        $display_rules_mapping = array(
            'exclude_pages' => 'whatsapp_chat_exclude_pages',
            'include_pages' => 'whatsapp_chat_include_pages', 
            'category_rules' => 'whatsapp_chat_category_rules',
            'sku_rules' => 'whatsapp_chat_sku_rules'
        );
        
        foreach ($display_rules_mapping as $form_field => $option_name) {
            if (isset($_POST[$form_field])) {
                $value = sanitize_textarea_field($_POST[$form_field]);
                update_option($option_name, $value);
                error_log('WhatsApp Chat - Saved display rule: ' . $form_field . ' -> ' . $option_name . ' = ' . $value);
            } else {
                error_log('WhatsApp Chat - Display rule not found in POST: ' . $form_field);
            }
        }
        
        // Log all POST data for debugging
        error_log('WhatsApp Chat - All POST data: ' . print_r($_POST, true));
        
        // Debug: Check specific fields
        error_log('WhatsApp Chat - exclude_pages in POST: ' . (isset($_POST['exclude_pages']) ? 'YES' : 'NO'));
        error_log('WhatsApp Chat - include_pages in POST: ' . (isset($_POST['include_pages']) ? 'YES' : 'NO'));
        error_log('WhatsApp Chat - category_rules in POST: ' . (isset($_POST['category_rules']) ? 'YES' : 'NO'));
        error_log('WhatsApp Chat - sku_rules in POST: ' . (isset($_POST['sku_rules']) ? 'YES' : 'NO'));
        
        wp_send_json_success('Display rules saved successfully');
    }
    
    /**
     * AJAX save contact
     */
    public function ajax_save_contact() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'whatsapp_chat_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
        }
        
        
        $data = array(
            'firstname' => isset($_POST['firstname']) ? sanitize_text_field($_POST['firstname']) : '',
            'lastname' => isset($_POST['lastname']) ? sanitize_text_field($_POST['lastname']) : '',
            'phone' => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '',
            'message' => isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '',
            'label' => isset($_POST['label']) ? sanitize_text_field($_POST['label']) : '',
            'avatar' => isset($_POST['avatar']) ? esc_url_raw($_POST['avatar']) : '',
            'status' => (isset($_POST['status']) && $_POST['status'] == '1') ? 1 : 0,
            'sort_order' => isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0
        );
        
        
        // Validate required fields
        if (empty($data['firstname']) || empty($data['phone'])) {
            wp_send_json_error(__('First name and phone are required.', 'whatsapp-chat-pro'));
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id > 0) {
            // Update existing contact
            $result = WhatsApp_Chat_Database::update_contact($id, $data);
        } else {
            // Check license limits for new contacts
            $license_status = get_option('whatsapp_chat_license_status', 'inactive');
            $contacts = $this->get_contacts();
            
            if ($license_status !== 'active' && count($contacts) >= 1) {
                wp_send_json_error(__('Free version is limited to 1 agent. Upgrade to Pro for unlimited agents.', 'whatsapp-chat-pro'));
            }
            
            // Add new contact
            $result = WhatsApp_Chat_Database::insert_contact($data);
        }
        
        if ($result) {
            wp_send_json_success(__('Contact saved successfully!', 'whatsapp-chat-pro'));
        } else {
            wp_send_json_error(__('Error saving contact!', 'whatsapp-chat-pro'));
        }
    }
    
    
    /**
     * AJAX validate license
     */
    public function ajax_validate_license() {
        check_ajax_referer('whatsapp_chat_admin_nonce', 'nonce');
        
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
        }
        
        $license_key = isset($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '';
        
        // Simulate license validation
        $license_status = 'active';
        $license_expires = date('Y-m-d', strtotime('+1 year'));
        
        update_option('whatsapp_chat_license_key', $license_key);
        update_option('whatsapp_chat_license_status', $license_status);
        update_option('whatsapp_chat_license_expires', $license_expires);
        
        wp_send_json_success(array(
            'status' => $license_status,
            'expires' => $license_expires
        ));
    }
    
    /**
     * AJAX select design
     */
    public function ajax_select_design() {
        check_ajax_referer('whatsapp_chat_admin_nonce', 'nonce');
        
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
        }
        
        $design_id = isset($_POST['design_id']) ? sanitize_text_field($_POST['design_id']) : '';
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        
        // Check if design is premium and license is not active
        $premium_designs = array('design-6', 'design-7');
        if (in_array($design_id, $premium_designs) && $license_status !== 'active') {
            wp_send_json_error(__('This design requires Pro version. Please upgrade to unlock premium designs.', 'whatsapp-chat-pro'));
        }
        
        // Update design
        update_option('whatsapp_chat_design', $design_id);
        
        // Clear cache to ensure changes take effect immediately
        $this->clear_cache();
        
        wp_send_json_success(__('Design selected successfully!', 'whatsapp-chat-pro'));
    }
    
    /**
     * AJAX reset colors
     */
    public function ajax_reset_colors() {
        check_ajax_referer('whatsapp_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
        }
        
        // Define default colors
        $default_colors = array(
            'whatsapp_chat_design_bg_color' => '#25D366',
            'whatsapp_chat_design_text_color' => '#ffffff',
            'whatsapp_chat_design_name_color' => '#ffffff',
            'whatsapp_chat_design_status_color' => '#4CAF50',
            'whatsapp_chat_primary_color' => '#25D366',
            'whatsapp_chat_hover_color' => '#128C7E',
            'whatsapp_chat_text_color' => '#ffffff',
            'whatsapp_chat_background_color' => '#ffffff',
            'whatsapp_chat_border_color' => '#e0e0e0',
            'whatsapp_chat_shadow_color' => 'rgba(0,0,0,0.1)'
        );
        
        // Reset each color to default
        foreach ($default_colors as $option_name => $default_value) {
            update_option($option_name, $default_value);
        }
        
        // Clear cache
        $this->clear_cache();
        
        wp_send_json_success(array(
            'message' => __('Colors reset to default successfully!', 'whatsapp-chat-pro'),
            'colors' => $default_colors
        ));
    }
    
    /**
     * Get cache busting version
     */
    private function get_cache_version() {
        $cache_version = get_option('whatsapp_chat_cache_version', WHATSAPP_CHAT_VERSION);
        return $cache_version;
    }
    
    /**
     * Clear cache when design changes
     */
    public function clear_cache() {
        // Update cache version
        $new_version = time();
        update_option('whatsapp_chat_cache_version', $new_version);
        
        // Clear WordPress cache if available
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // Clear object cache
        if (function_exists('wp_cache_delete')) {
            wp_cache_delete('whatsapp_chat_settings', 'options');
        }
        
        // Clear any CDN cache if available
        do_action('whatsapp_chat_clear_cache');
    }
    
    /**
     * Optimize plugin performance
     */
    public function optimize_performance() {
        // Only load scripts on our pages
        if (!is_admin() || strpos(get_current_screen()->id, 'whatsapp-chat') === false) {
            return;
        }
        
        // Minimize database queries
        add_filter('pre_option_whatsapp_chat_enabled', array($this, 'cache_option'));
        add_filter('pre_option_whatsapp_chat_design', array($this, 'cache_option'));
        add_filter('pre_option_whatsapp_chat_phone_number', array($this, 'cache_option'));
    }
    
    /**
     * Cache option values
     */
    public function cache_option($value) {
        static $cache = array();
        
        $option_name = current_filter();
        $option_name = str_replace('pre_option_', '', $option_name);
        
        if (!isset($cache[$option_name])) {
            $cache[$option_name] = get_option($option_name);
        }
        
        return $cache[$option_name];
    }
    
    /**
     * AJAX: Save working hours
     */
    public function ajax_save_working_hours() {
        // Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'whatsapp_chat_admin_nonce' ) ) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            // Save working hours enabled setting
            $working_hours_enabled = isset($_POST['working_hours_enabled']) ? '1' : '0';
            update_option('whatsapp_chat_working_hours_enabled', $working_hours_enabled);
            
            // Save individual day settings
            $days = array(0, 1, 2, 3, 4, 5, 6); // Sunday to Saturday
            
            foreach ($days as $day_num) {
                $is_active = isset($_POST['day_' . $day_num . '_active']) ? 1 : 0;
                $start_time = isset($_POST['day_' . $day_num . '_start']) ? sanitize_text_field($_POST['day_' . $day_num . '_start']) : '09:00';
                $end_time = isset($_POST['day_' . $day_num . '_end']) ? sanitize_text_field($_POST['day_' . $day_num . '_end']) : '18:00';
                
                // Update or insert working hours
                WhatsApp_Chat_Database::update_working_hours($day_num, $is_active, $start_time, $end_time);
            }
            
            wp_send_json_success('Working hours saved successfully');
            
        } catch (Exception $e) {
            wp_send_json_error('Error saving working hours: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Upload avatar
     */
    public function ajax_upload_avatar() {
        // Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'whatsapp_chat_admin_nonce' ) ) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error('Insufficient permissions');
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['avatar_file']) || $_FILES['avatar_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('No file uploaded or upload error');
        }
        
        $file = $_FILES['avatar_file'];
        
        // Validate file type
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        $file_type = wp_check_filetype($file['name']);
        
        if (!in_array($file['type'], $allowed_types) || !in_array($file_type['type'], $allowed_types)) {
            wp_send_json_error('Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.');
        }
        
        // Validate file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            wp_send_json_error('File size too large. Maximum size is 2MB.');
        }
        
        // Use WordPress media upload system
        $upload_overrides = array(
            'test_form' => false,
            'action' => 'whatsapp_chat_upload_avatar'
        );
        
        $uploaded_file = wp_handle_upload($file, $upload_overrides);
        
        if (isset($uploaded_file['error'])) {
            wp_send_json_error($uploaded_file['error']);
        }
        
        // Create attachment
        $attachment = array(
            'post_mime_type' => $uploaded_file['type'],
            'post_title' => sanitize_file_name(pathinfo($file['name'], PATHINFO_FILENAME)),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment($attachment, $uploaded_file['file']);
        
        if (!is_wp_error($attachment_id)) {
            // Generate attachment metadata
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $uploaded_file['file']);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            
            $file_url = $uploaded_file['url'];
            
            wp_send_json_success(array(
                'url' => $file_url,
                'filename' => basename($uploaded_file['file']),
                'attachment_id' => $attachment_id
            ));
        } else {
            wp_send_json_error('Failed to upload file');
        }
    }
    
    /**
     * AJAX: Save design special options
     */
    public function ajax_save_design_special() {
        // Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'whatsapp_chat_admin_nonce' ) ) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error('Insufficient permissions');
        }
        
        try {
            // Save Design 6 options
            if (isset($_POST['design6_title'])) {
                update_option('whatsapp_chat_design6_title', sanitize_text_field($_POST['design6_title']));
            }
            if (isset($_POST['design6_subtitle'])) {
                update_option('whatsapp_chat_design6_subtitle', sanitize_text_field($_POST['design6_subtitle']));
            }
            if (isset($_POST['design6_bg_color'])) {
                update_option('whatsapp_chat_design6_bg_color', sanitize_hex_color($_POST['design6_bg_color']));
            }
            
            // Save Design 7 options
            if (isset($_POST['design7_avatar'])) {
                update_option('whatsapp_chat_design7_avatar', esc_url_raw($_POST['design7_avatar']));
            }
            if (isset($_POST['design7_name'])) {
                update_option('whatsapp_chat_design7_name', sanitize_text_field($_POST['design7_name']));
            }
            if (isset($_POST['design7_status'])) {
                update_option('whatsapp_chat_design7_status', sanitize_text_field($_POST['design7_status']));
            }
            
            wp_send_json_success('Design options saved successfully');
            
        } catch (Exception $e) {
            wp_send_json_error('Error saving design options: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Add contact
     */
    public function ajax_add_contact() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['whatsapp_chat_contact_nonce'], 'whatsapp_chat_contact_action')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error('Insufficient permissions');
        }
        
        // Get form data
        $firstname = isset($_POST['firstname']) ? sanitize_text_field($_POST['firstname']) : '';
        $lastname = isset($_POST['lastname']) ? sanitize_text_field($_POST['lastname']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $label = isset($_POST['label']) ? sanitize_text_field($_POST['label']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
        $avatar = isset($_POST['avatar']) ? esc_url_raw($_POST['avatar']) : '';
        
        // Validate required fields
        if (empty($firstname) || empty($phone)) {
            wp_send_json_error('First name and phone are required');
        }
        
        // Check license limits
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        $contacts = $this->get_contacts();
        
        if ($license_status !== 'active' && count($contacts) >= 1) {
            wp_send_json_error('Free version is limited to 1 agent. Upgrade to Pro for unlimited agents.');
        }
        
        // Insert contact using database class
        $contact_data = array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'phone' => $phone,
            'label' => $label,
            'message' => $message,
            'avatar' => $avatar,
            'status' => 1,
            'sort_order' => count($contacts) + 1
        );
        
        $result = WhatsApp_Chat_Database::insert_contact($contact_data);
        
        if ($result === false) {
            wp_send_json_error('Failed to add contact');
        }
        
        wp_send_json_success('Contact added successfully');
    }
    
    /**
     * AJAX: Delete contact
     */
    public function ajax_delete_contact() {
        // Debug logging
        error_log('WhatsApp Chat Debug - Delete contact request received');
        error_log('WhatsApp Chat Debug - POST data: ' . print_r($_POST, true));
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'whatsapp_chat_contact_action')) {
            error_log('WhatsApp Chat Debug - Nonce verification failed');
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
            error_log('WhatsApp Chat Debug - Insufficient permissions');
            wp_send_json_error('Insufficient permissions');
        }
        
        $contact_id = isset($_POST['contact_id']) ? intval($_POST['contact_id']) : 0;
        error_log('WhatsApp Chat Debug - Contact ID: ' . $contact_id);
        
        if (!$contact_id) {
            error_log('WhatsApp Chat Debug - Invalid contact ID');
            wp_send_json_error('Invalid contact ID');
        }
        
        // Delete contact using database class
        $result = WhatsApp_Chat_Database::delete_contact($contact_id);
        error_log('WhatsApp Chat Debug - Delete result: ' . ($result ? 'success' : 'failed'));
        
        if ($result === false) {
            wp_send_json_error('Failed to delete contact');
        }
        
        wp_send_json_success('Contact deleted successfully');
    }
    
    /**
     * AJAX upload file
     */
    public function ajax_upload_file() {
        check_ajax_referer('whatsapp_chat_admin_nonce', 'nonce');
        
		if ( ! current_user_can( 'manage_options' ) ) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'whatsapp-chat-pro'));
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
     * Generate Professional Upgrade Banner HTML
     * 
     * @param string $feature_name The name of the premium feature
     * @param string $description Brief description of the feature
     * @param array $features Array of feature benefits
     * @param string $icon Dashicon class for the feature icon
     * @param bool $compact Whether to show compact version
     * @return string HTML for the upgrade banner
     */
    public function generate_professional_upgrade_banner($feature_name, $description, $features = array(), $icon = 'dashicons-star-filled', $compact = false) {
        $banner_class = $compact ? 'whatsapp-upgrade-compact' : 'whatsapp-upgrade-banner';
        
        // Default features if none provided
        if (empty($features)) {
            $features = array(
                'Unlimited agents',
                'Premium design templates',
                'Advanced analytics & reports',
                'Priority support & updates'
            );
        }
        
        $features_html = '';
        foreach ($features as $feature) {
            $features_html .= sprintf(
                '<div class="whatsapp-feature-badge">
                    <div class="whatsapp-feature-check">
                        <span class="dashicons dashicons-yes"></span>
                    </div>
                    <div class="whatsapp-feature-text">%s</div>
                </div>',
                esc_html($feature)
            );
        }
        
        return sprintf(
            '<div class="%s">
                <div class="whatsapp-pro-badge">PRO FEATURE</div>
                <div class="whatsapp-upgrade-header">
                    <div class="whatsapp-upgrade-icon">
                        <span class="dashicons %s"></span>
                    </div>
                    <div>
                        <h3 class="whatsapp-upgrade-title">%s</h3>
                        <p class="whatsapp-upgrade-subtitle">%s</p>
                    </div>
                </div>
                <div class="whatsapp-upgrade-features">
                    %s
                </div>
                <div class="whatsapp-upgrade-cta">
                    <a href="https://getjustplug.com/whatsapp-chat-pro" target="_blank" class="whatsapp-btn-upgrade">
                        <span class="dashicons dashicons-external"></span>
                        UPGRADE TO PRO
                    </a>
                </div>
            </div>',
            esc_attr($banner_class),
            esc_attr($icon),
            esc_html($feature_name),
            esc_html($description),
            $features_html
        );
    }
    
    /**
     * Generate Professional Upgrade Banner for Settings
     * 
     * @param string $feature_name The name of the premium feature
     * @param string $description Brief description of the feature
     * @param array $features Array of feature benefits
     * @param string $icon Dashicon class for the feature icon
     * @return string HTML for the upgrade banner
     */
    public function generate_settings_upgrade_banner($feature_name, $description, $features = array(), $icon = 'dashicons-star-filled') {
        return $this->generate_professional_upgrade_banner($feature_name, $description, $features, $icon, true);
    }
    
    /**
     * Get agents count for dashboard
     * 
     * @return int Number of active agents
     */
    private function get_agents_count() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'whatsapp_chat_contacts';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return 0;
        }
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE status = %s",
            '1'
        ));
        
        return intval($count);
    }
    
    /**
     * Get design information
     * 
     * @param string $design Design ID
     * @return array Design information
     */
    private function get_design_info($design) {
        $designs = array(
            'design-1' => array('name' => 'Modern Green', 'type' => 'Free'),
            'design-2' => array('name' => 'Corporate Blue', 'type' => 'Free'),
            'design-3' => array('name' => 'Dark Elegant', 'type' => 'Free'),
            'design-4' => array('name' => 'Gradient Modern', 'type' => 'Free'),
            'design-5' => array('name' => 'Bright Red', 'type' => 'Free'),
            'design-6' => array('name' => 'Professional Banner', 'type' => 'Pro'),
            'design-7' => array('name' => 'Custom Avatar', 'type' => 'Pro')
        );
        
        return $designs[$design] ?? array('name' => 'Unknown Design', 'type' => 'Unknown');
    }
    
    /**
     * Get recent activity for dashboard
     * 
     * @return array Recent activity items
     */
    private function get_recent_activity() {
        $activities = array();
        
        // Check if plugin was recently enabled
        $last_enabled = get_option('whatsapp_chat_last_enabled', '');
        if ($last_enabled && strtotime($last_enabled) > strtotime('-24 hours')) {
            $activities[] = array(
                'icon' => '✅',
                'text' => 'WhatsApp Chat was enabled',
                'time' => human_time_diff(strtotime($last_enabled)) . ' ago'
            );
        }
        
        // Check recent clicks
        $today_clicks = get_option('whatsapp_chat_today_clicks', 0);
        if ($today_clicks > 0) {
            $activities[] = array(
                'icon' => '📱',
                'text' => $today_clicks . ' clicks today',
                'time' => 'Today'
            );
        }
        
        // Check design changes
        $last_design_change = get_option('whatsapp_chat_last_design_change', '');
        if ($last_design_change && strtotime($last_design_change) > strtotime('-7 days')) {
            $activities[] = array(
                'icon' => '🎨',
                'text' => 'Design was changed',
                'time' => human_time_diff(strtotime($last_design_change)) . ' ago'
            );
        }
        
        // Check settings changes
        $last_settings_change = get_option('whatsapp_chat_last_settings_change', '');
        if ($last_settings_change && strtotime($last_settings_change) > strtotime('-7 days')) {
            $activities[] = array(
                'icon' => '⚙️',
                'text' => 'Settings were updated',
                'time' => human_time_diff(strtotime($last_settings_change)) . ' ago'
            );
        }
        
        // Limit to 5 most recent activities
        return array_slice($activities, 0, 5);
    }
    
    
}