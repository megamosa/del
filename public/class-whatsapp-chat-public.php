<?php
/**
 * Public functionality
 *
 * @package WhatsApp_Chat_Pro
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	wp_die(__('Direct access not allowed.', 'whatsapp-chat-pro'));
}

/**
 * Public class
 */
class WhatsApp_Chat_Public {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        
        // WPML compatibility
        if (function_exists('wpml_register_single_string')) {
            add_action('init', array($this, 'register_wpml_strings'));
        }
    }

	/**
	 * Register WPML strings
	 */
	public function register_wpml_strings() {
		// Register all public strings with WPML
		$public_strings = array(
			'Welcome!',
			'Need Help?',
			'Need Help? Chat via',
			'WhatsApp',
			'New message notification'
		);
		
		foreach ($public_strings as $string) {
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
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Enqueue scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Add widget to footer
        add_action( 'wp_footer', array( $this, 'render_widget' ) );
        
        // WPML compatibility hooks
        if (function_exists('wpml_translate_single_string')) {
            add_filter('whatsapp_chat_translate_string', array($this, 'translate_wpml_string'), 10, 2);
        }

		// Handle AJAX requests
		add_action( 'wp_ajax_whatsapp_chat_get_contacts', array( $this, 'ajax_get_contacts' ) );
		add_action( 'wp_ajax_nopriv_whatsapp_chat_get_contacts', array( $this, 'ajax_get_contacts' ) );
	}
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Check if widget should be displayed
		if ( ! $this->should_display_widget() ) {
            return;
        }
        
        // Get cache busting version
        $cache_version = get_option('whatsapp_chat_cache_version', WHATSAPP_CHAT_VERSION);
        
        // Enqueue CSS
        wp_enqueue_style(
            'whatsapp-chat-widget',
            WHATSAPP_CHAT_PLUGIN_URL . 'public/css/widget.css',
            array(),
            $cache_version
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'whatsapp-chat-widget',
            WHATSAPP_CHAT_PLUGIN_URL . 'public/js/widget.js',
            array('jquery'),
            $cache_version,
            true
        );
        
        // Get widget config
        $widget_config = $this->get_widget_config();
        
        // Get widget config
        
        // Localize script
        wp_localize_script('whatsapp-chat-widget', 'whatsappChat', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('whatsapp_chat_widget_nonce'),
            'config' => $widget_config
        ));
        
        // Add ajaxurl to window for widget access
        wp_add_inline_script('whatsapp-chat-widget', 'window.ajaxurl = "' . admin_url('admin-ajax.php') . '";', 'before');
    }
    
    /**
     * Check if widget should be displayed - FIXED: Always show widget
     */
    private function should_display_widget() {
        // Check if enabled
        $enabled = get_option('whatsapp_chat_enabled', '1');
		if ( ! $enabled ) {
            error_log('WhatsApp Chat - Plugin disabled');
            return false;
        }
        
        // Check device restrictions
        $show_on_mobile = get_option('whatsapp_chat_show_on_mobile', '1');
        $show_on_desktop = get_option('whatsapp_chat_show_on_desktop', '1');
        $is_mobile = wp_is_mobile();
        
        if ($is_mobile && !$show_on_mobile) {
            error_log('WhatsApp Chat - Hidden on mobile');
            return false;
        }
        
		if ( ! $is_mobile && ! $show_on_desktop ) {
            error_log('WhatsApp Chat - Hidden on desktop');
            return false;
        }
        
        // Check display rules - FIXED: Added page restrictions
        if (!$this->check_display_rules()) {
            error_log('WhatsApp Chat - Widget hidden due to display rules');
            return false;
        }
        
        // Check working hours restrictions
        $working_hours_enabled = get_option('whatsapp_chat_working_hours_enabled', '0');
        error_log('WhatsApp Chat - Working hours enabled: ' . $working_hours_enabled);
        
        if ($working_hours_enabled) {
            $is_restricted = $this->is_time_restricted();
            error_log('WhatsApp Chat - Time restricted: ' . ($is_restricted ? 'YES' : 'NO'));
            
            if ($is_restricted) {
                error_log('WhatsApp Chat - Widget hidden due to working hours');
                return false;
            }
        }
        
        error_log('WhatsApp Chat - Widget should be displayed: YES');
        return true;
    }
    
    /**
     * Check display rules - FIXED: Added page restrictions
     */
    private function check_display_rules() {
        // Get current page info - FIXED: Handle null values
        global $wp;
        $current_url = home_url($wp->request);
        $current_path = parse_url($current_url, PHP_URL_PATH);
        $current_page_id = get_the_ID();
        
        // Ensure we have valid values
        $current_url = $current_url ?: '';
        $current_path = $current_path ?: '';
        $current_page_id = $current_page_id ?: 0;
        
        error_log('WhatsApp Chat - Current URL: ' . $current_url);
        error_log('WhatsApp Chat - Current Path: ' . $current_path);
        error_log('WhatsApp Chat - Current Page ID: ' . $current_page_id);
        
        // Check exclude pages - FIXED: Handle empty values
        $exclude_pages = get_option('whatsapp_chat_exclude_pages', '');
        if (!empty($exclude_pages)) {
            $exclude_list = array_map('trim', explode("\n", $exclude_pages));
            $exclude_list = array_filter($exclude_list, function($rule) {
                return !empty($rule);
            });
            
            if (!empty($exclude_list)) {
                error_log('WhatsApp Chat - Exclude pages: ' . implode(', ', $exclude_list));
                
                foreach ($exclude_list as $exclude_rule) {
                    // Check if current page matches exclude rule
                    if ($this->matches_display_rule($current_url, $current_path, $current_page_id, $exclude_rule)) {
                        error_log('WhatsApp Chat - Page excluded by rule: ' . $exclude_rule);
                        return false;
                    }
                }
            }
        }
        
        // Check include pages - FIXED: Handle empty values
        $include_pages = get_option('whatsapp_chat_include_pages', '');
        if (!empty($include_pages)) {
            $include_list = array_map('trim', explode("\n", $include_pages));
            $include_list = array_filter($include_list, function($rule) {
                return !empty($rule);
            });
            
            if (!empty($include_list)) {
                error_log('WhatsApp Chat - Include pages: ' . implode(', ', $include_list));
                
                $is_included = false;
                foreach ($include_list as $include_rule) {
                    // Check if current page matches include rule
                    if ($this->matches_display_rule($current_url, $current_path, $current_page_id, $include_rule)) {
                        error_log('WhatsApp Chat - Page included by rule: ' . $include_rule);
                        $is_included = true;
                        break;
                    }
                }
                
                if (!$is_included) {
                    error_log('WhatsApp Chat - Page not in include list, hiding widget');
                    return false;
                }
            }
        }
        
        // Check category rules - FIXED: Handle empty values
        $category_rules = get_option('whatsapp_chat_category_rules', '');
        if (!empty($category_rules)) {
            $category_list = array_map('trim', explode("\n", $category_rules));
            $category_list = array_filter($category_list, function($rule) {
                return !empty($rule);
            });
            
            if (!empty($category_list)) {
                error_log('WhatsApp Chat - Category rules: ' . implode(', ', $category_list));
                
                if (is_product() || is_product_category() || is_product_tag()) {
                    $product_categories = wp_get_post_terms(get_the_ID(), 'product_cat', array('fields' => 'slugs'));
                    $product_categories = is_array($product_categories) ? $product_categories : array();
                    
                    $matches_category = false;
                    foreach ($category_list as $category_slug) {
                        if (in_array($category_slug, $product_categories)) {
                            error_log('WhatsApp Chat - Product matches category: ' . $category_slug);
                            $matches_category = true;
                            break;
                        }
                    }
                    
                    if (!$matches_category) {
                        error_log('WhatsApp Chat - Product not in allowed categories, hiding widget');
                        return false;
                    }
                }
            }
        }
        
        // Check SKU rules - FIXED: Handle empty values
        $sku_rules = get_option('whatsapp_chat_sku_rules', '');
        if (!empty($sku_rules)) {
            $sku_list = array_map('trim', explode("\n", $sku_rules));
            $sku_list = array_filter($sku_list, function($rule) {
                return !empty($rule);
            });
            
            if (!empty($sku_list)) {
                error_log('WhatsApp Chat - SKU rules: ' . implode(', ', $sku_list));
                
                if (is_product()) {
                    $product_sku = get_post_meta(get_the_ID(), '_sku', true);
                    error_log('WhatsApp Chat - Product SKU: ' . $product_sku);
                    
                    if (!in_array($product_sku, $sku_list)) {
                        error_log('WhatsApp Chat - Product SKU not in allowed list, hiding widget');
                        return false;
                    }
                }
            }
        }
        
        error_log('WhatsApp Chat - Display rules passed');
        return true;
    }
    
    /**
     * Check if current page matches display rule - FIXED: Handle null values
     */
    private function matches_display_rule($current_url, $current_path, $current_page_id, $rule) {
        // Validate inputs
        if (empty($rule)) {
            return false;
        }
        
        // Check by page ID
        if (is_numeric($rule) && $current_page_id && $current_page_id == intval($rule)) {
            return true;
        }
        
        // Check by URL path (handle null values)
        if ($current_path && strpos($current_path, $rule) !== false) {
            return true;
        }
        
        // Check by full URL (handle null values)
        if ($current_url && strpos($current_url, $rule) !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if time is restricted - FIXED
     */
    private function is_time_restricted() {
        // Check if working hours are enabled
        $working_hours_enabled = get_option('whatsapp_chat_working_hours_enabled', '0');
        
        if (!$working_hours_enabled) {
            error_log('WhatsApp Chat - Working Hours Disabled: Always show widget');
            return false;
        }
        
        error_log('WhatsApp Chat - Working Hours Enabled: Checking schedule');
        
        // Get current day and time using WordPress timezone
        $current_day = (int) current_time('w'); // 0=Sunday, 1=Monday, etc.
        $current_time = current_time('H:i');
        
        error_log('WhatsApp Chat - Current Day: ' . $current_day);
        error_log('WhatsApp Chat - Current Time: ' . $current_time);
        
        // Get working hours from database
        $working_hours = WhatsApp_Chat_Database::get_working_hours();
        
        error_log('WhatsApp Chat - Retrieved working hours count: ' . count($working_hours));
        
        foreach ($working_hours as $hours) {
            error_log('WhatsApp Chat - Checking day ' . $hours->day_of_week . ' (active: ' . $hours->is_active . ')');
            
            if ($hours->day_of_week == $current_day && $hours->is_active) {
                $start_time = substr($hours->start_time, 0, 5); // Remove seconds
                $end_time = substr($hours->end_time, 0, 5); // Remove seconds
                
                error_log('WhatsApp Chat - Day ' . $current_day . ' is active: ' . $start_time . ' - ' . $end_time);
                
                $is_within_hours = ($current_time >= $start_time && $current_time <= $end_time);
                error_log('WhatsApp Chat - Within hours: ' . ($is_within_hours ? 'YES' : 'NO'));
                
                return !$is_within_hours; // Return true if outside hours
            }
        }
        
        error_log('WhatsApp Chat - No active schedule for day ' . $current_day . ', hiding widget');
        return true; // Hide widget if no schedule for current day
    }
    
    /**
     * Get widget configuration
     */
    private function get_widget_config() {
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        $design = get_option('whatsapp_chat_design', 'design-1');
        
        // Check if design is premium and license is not active
        if ($this->is_premium_design($design) && $license_status !== 'active') {
            $design = 'design-1'; // Fallback to free design
        }
        
        // Check if currently within working hours
        $is_within_working_hours = !$this->is_time_restricted();
        error_log('WhatsApp Chat - is_within_working_hours: ' . ($is_within_working_hours ? 'YES' : 'NO'));
        
        // Get phone number and validate
        $phone_number = get_option('whatsapp_chat_phone_number', '');
        error_log('WhatsApp Chat - Raw phone number from DB: ' . $phone_number);
        
        // Only use fallback if phone is truly empty
        if (empty($phone_number) || $phone_number === '+1234567890') {
            $phone_number = ''; // Keep empty if no real phone number
        }
        
        error_log('WhatsApp Chat - Final phone number: ' . $phone_number);
        
        $config = array(
            'enabled' => get_option('whatsapp_chat_enabled', '1'),
            'phone_number' => $phone_number,
            'phoneNumber' => $phone_number, // Also add phoneNumber for JavaScript compatibility
            'default_message' => get_option('whatsapp_chat_default_message', 'Hello! I have a question about your products.'),
            'defaultMessage' => get_option('whatsapp_chat_default_message', 'Hello! I have a question about your products.'), // Also add defaultMessage for JavaScript compatibility
            'button_position' => get_option('whatsapp_chat_button_position', 'bottom-right'),
            'button_layout' => get_option('whatsapp_chat_button_layout', 'button'),
            'design' => $design,
            'enable_agents' => get_option('whatsapp_chat_enable_agents', '0'),
            'header_text' => get_option('whatsapp_chat_header_text', 'Chat with us'),
            'footer_text' => get_option('whatsapp_chat_footer_text', "We're here to help!"),
            'support_team_name' => get_option('whatsapp_chat_support_team_name', 'Support Team'),
            'support_team_label' => get_option('whatsapp_chat_support_team_label', 'Customer Support'),
            'no_agents_message' => get_option('whatsapp_chat_no_agents_message', 'No agents are currently available. Please try again later or contact us directly.'),
            'offline_message' => get_option('whatsapp_chat_offline_message', 'Our chat is currently offline. Please contact us during business hours.'),
            'time_restrictions' => get_option('whatsapp_chat_time_restrictions', '0'),
            'working_hours_enabled' => get_option('whatsapp_chat_working_hours_enabled', '0'),
            'is_within_working_hours' => $is_within_working_hours,
            'start_time' => get_option('whatsapp_chat_start_time', '09:00'),
            'end_time' => get_option('whatsapp_chat_end_time', '18:00'),
            'analytics_enabled' => get_option('whatsapp_chat_enable_analytics', '0'),
            'ga4_measurement_id' => get_option('whatsapp_chat_ga4_measurement_id', ''),
            'analytics_event_name' => get_option('whatsapp_chat_analytics_event_name', 'whatsapp_click'),
            'button_animation' => get_option('whatsapp_chat_button_animation', 'none'),
            'button_x' => get_option('whatsapp_chat_button_x', '20'),
            'button_y' => get_option('whatsapp_chat_button_y', '20'),
            'license_active' => $license_status === 'active',
            'nonce' => wp_create_nonce('whatsapp_chat_admin_nonce'),
            'agents' => $this->get_available_agents()
        );
        
        
        return $config;
    }
    
    /**
     * Check if design is premium
     */
    private function is_premium_design($design) {
        $premium_designs = array('design-6', 'design-7');
        return in_array($design, $premium_designs);
    }
    
    /**
     * Get available agents
     */
    private function get_available_agents() {
        $license_status = get_option('whatsapp_chat_license_status', 'inactive');
        $enable_agents = get_option('whatsapp_chat_enable_agents', '0');
        
        // If agents are disabled or license is not active, return single agent
        if (!$enable_agents || $license_status !== 'active') {
            return array(array(
                'id' => 1,
                'firstname' => get_option('whatsapp_chat_support_team_name', 'Support Team'),
                'lastname' => '',
                'phone' => get_option('whatsapp_chat_phone_number', '+1234567890'),
                'message' => get_option('whatsapp_chat_default_message', 'Hello! I have a question.'),
                'label' => get_option('whatsapp_chat_support_team_label', 'Customer Support'),
                'avatar' => get_avatar_url(1, array('size' => 60, 'default' => 'identicon')),
                'status' => 1,
                'sort_order' => 1
            ));
        }
        
        // Get agents from database
        $contacts = $this->get_contacts();
        
        // Filter out inactive agents (status != 1)
        $active_contacts = array();
        foreach ($contacts as $contact) {
            if (isset($contact['status']) && $contact['status'] == 1) {
                $active_contacts[] = $contact;
            }
        }
        
        if (empty($active_contacts)) {
            // Fallback to main contact
            return array(array(
                'id' => 1,
                'firstname' => get_option('whatsapp_chat_support_team_name', 'Support Team'),
                'lastname' => '',
                'phone' => get_option('whatsapp_chat_phone_number', '+1234567890'),
                'message' => get_option('whatsapp_chat_default_message', 'Hello! I have a question.'),
                'label' => get_option('whatsapp_chat_support_team_label', 'Customer Support'),
                'avatar' => get_avatar_url(1, array('size' => 60, 'default' => 'identicon')),
                'status' => 1,
                'sort_order' => 1
            ));
        }
        
        return $active_contacts;
    }
    
    /**
     * Render widget
     */
    public function render_widget() {
		if ( ! $this->should_display_widget() ) {
            return;
        }
        
        $config = $this->get_widget_config();
        $design = $config['design'];
        
        
        ?>
        <div class="whatsapp-chat-widget <?php echo esc_attr($design); ?>" 
             data-whatsapp-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>"
             style="display: block !important; position: fixed; z-index: 9999; --wa-button-x: <?php echo esc_attr($config['button_x']); ?>px; --wa-button-y: <?php echo esc_attr($config['button_y']); ?>px;">
            
            <?php $this->render_button($config, $design); ?>
            <?php $this->render_box($config); ?>
            
            <?php if (!$config['license_active']): ?>
            <?php $this->render_upgrade_prompt(); ?>
            <?php endif; ?>
            
        </div>
        <?php
    }
    
    /**
     * Render upgrade prompt
     */
    private function render_upgrade_prompt() {
        ?>
        <div class="whatsapp-upgrade-prompt" style="display: none;">
            <div class="whatsapp-upgrade-content">
                <h3>🚀 Upgrade to Pro</h3>
                <p>Unlock unlimited agents, premium designs, and advanced features for just $24!</p>
                <a href="https://getjustplug.com/whatsapp-chat-pro" target="_blank" class="whatsapp-upgrade-btn">
                    Upgrade Now - $24 Only
                </a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render button
     */
    private function render_button($config, $design) {
        $position = $config['button_position'];
        $layout = $config['button_layout'];
        $enable_agents = $config['enable_agents'];
        
        ?>
        <div class="whatsapp-button whatsapp-button--<?php echo esc_attr($position); ?> <?php echo esc_attr($layout); ?>" 
             data-whatsapp-button
             <?php if (!$enable_agents): ?>
             data-whatsapp-phone="<?php echo esc_attr($config['phone_number']); ?>"
             data-whatsapp-message="<?php echo esc_attr($config['default_message']); ?>"
             <?php endif; ?>>
            
            <?php if ($design === 'design-6'): ?>
                <?php $this->render_design_6_button($config); ?>
            <?php elseif ($design === 'design-7'): ?>
                <?php $this->render_design_7_button($config); ?>
            <?php else: ?>
                <?php $this->render_design_button($config, $design); ?>
            <?php endif; ?>
            
        </div>
        <?php
    }
    
    /**
     * Render design button (1-5)
     */
    private function render_design_button($config, $design) {
        // Get floating message text for designs 1-5
        $floating_message = get_option('whatsapp_chat_floating_message', __('Need Help?', 'whatsapp-chat-pro'));
        
        // WPML compatibility - apply string translation
        if (function_exists('wpml_translate_single_string')) {
            $floating_message = wpml_translate_single_string($floating_message, 'whatsapp-chat-pro', 'Need Help?');
        }
        
        // Apply WPML translation to floating message
        $floating_message = apply_filters('whatsapp_chat_translate_string', $floating_message, 'floating_message');
        
        if ($design === 'design-5') {
            // Design 5: Welcome button with notification bubble
            ?>
            <div class="whatsapp-floating-message">
                <span class="whatsapp-floating-text"><?php echo esc_html($floating_message); ?></span>
            </div>
            <div class="whatsapp-button__icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                </svg>
            </div>
            <span class="whatsapp-button__text"><?php 
                $welcome_text = _e( 'Welcome!', 'whatsapp-chat-pro' );
                if (function_exists('wpml_translate_single_string')) {
                    $welcome_text = wpml_translate_single_string($welcome_text, 'whatsapp-chat-pro', 'Welcome!');
                }
                echo $welcome_text;
            ?></span>
            <span class="whatsapp-button__notification-bubble" aria-label="<?php 
                $notification_text = _e( 'New message notification', 'whatsapp-chat-pro' );
                if (function_exists('wpml_translate_single_string')) {
                    $notification_text = wpml_translate_single_string($notification_text, 'whatsapp-chat-pro', 'New message notification');
                }
                echo $notification_text;
            ?>">1</span>
            <?php
        } else {
            // Other designs (1-4) with floating message
            ?>
            <div class="whatsapp-floating-message">
                <span class="whatsapp-floating-text"><?php echo esc_html($floating_message); ?></span>
            </div>
            <div class="whatsapp-button__icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                </svg>
            </div>
            <?php
        }
    }
    
    /**
     * Render design 6 button (Professional Banner)
     */
    private function render_design_6_button($config) {
        // Get contacts for multi-agent support
        $contacts = $this->get_contacts();
        $enable_agents = $config['enable_agents'];
        
        if ($enable_agents && count($contacts) > 0) {
            // Multi-agent mode - show first contact as main button
            $main_contact = $contacts[0];
            $agent_name = trim($main_contact['firstname'] . ' ' . $main_contact['lastname']);
            $agent_avatar = $main_contact['avatar'];
            $status_text = 'Online';
            $message_text = $main_contact['message'];
        } else {
            // Single agent mode - use design settings
            $agent_name = get_option('whatsapp_chat_design_agent_name', 'Support Team');
            $agent_avatar = get_option('whatsapp_chat_design_avatar', '');
            $status_text = get_option('whatsapp_chat_design_status', 'Online');
            $message_text = get_option('whatsapp_chat_design_message', 'Hello! I have a question');
        }
        
        // Get customizable message text from admin settings
        $custom_message_text = get_option('whatsapp_chat_message_text', '');
        if (!empty($custom_message_text)) {
            $message_text = $custom_message_text;
        }
        
        $bg_color = get_option('whatsapp_chat_design_bg_color', '#25D364');
        $text_color = get_option('whatsapp_chat_design_text_color', '#ffffff');
        $name_color = get_option('whatsapp_chat_design_name_color', '#ffffff');
        $status_color = get_option('whatsapp_chat_design_status_color', '#000');
        
        // Get first letter for avatar if no image
        $agent_initial = substr($agent_name, 0, 1);
        ?>
        <div class="whatsapp-design6-container">
            <div class="whatsapp-design6-avatar-container">
                <div class="whatsapp-design6-avatar">
                    <?php if ($agent_avatar): ?>
                        <img src="<?php echo esc_url($agent_avatar); ?>" alt="<?php echo esc_attr($agent_name); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <div class="whatsapp-design6-avatar-default">
                            <?php echo esc_html($agent_initial); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="whatsapp-design6-whatsapp-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                    </svg>
                </div>
            </div>
            <div class="whatsapp-design6-banner">
                <div class="whatsapp-design6-banner-content">
                    <div class="whatsapp-design6-name-row">
                        <span class="whatsapp-design6-agent-name"><?php echo esc_html($agent_name); ?></span>
                        <span class="whatsapp-design6-online-badge"><?php echo esc_html($status_text); ?></span>
                    </div>
                    <div class="whatsapp-design6-message-row">
                        <span class="whatsapp-design6-message-text"><?php echo esc_html($message_text); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render design 7 button (Custom Avatar Professional)
     */
    private function render_design_7_button($config) {
        // Get contacts for multi-agent support
        $contacts = $this->get_contacts();
        $enable_agents = $config['enable_agents'];
        
        if ($enable_agents && count($contacts) > 0) {
            // Multi-agent mode - show first contact as main button
            $main_contact = $contacts[0];
            $agent_name = trim($main_contact['firstname'] . ' ' . $main_contact['lastname']);
            $agent_avatar = $main_contact['avatar'];
            $status_text = 'Online';
            $message_text = $main_contact['message'];
        } else {
            // Single agent mode - use design settings
            $agent_name = get_option('whatsapp_chat_design_agent_name', 'Support Team');
            $agent_avatar = get_option('whatsapp_chat_design_avatar', '');
            $status_text = get_option('whatsapp_chat_design_status', 'Online');
            $message_text = get_option('whatsapp_chat_design_message', 'Hello! I have a question');
        }
        
        $bg_color = get_option('whatsapp_chat_design_bg_color', '#25D364');
        $text_color = get_option('whatsapp_chat_design_text_color', '#ffffff');
        $name_color = get_option('whatsapp_chat_design_name_color', '#ffffff');
        $status_color = get_option('whatsapp_chat_design_status_color', '#4CAF50');
        
        // Get first letter for avatar if no image
        $agent_initial = substr($agent_name, 0, 1);
        ?>
        <div class="whatsapp-button__avatar">
            <?php if ($agent_avatar): ?>
                <img src="<?php echo esc_url($agent_avatar); ?>" alt="<?php echo esc_attr($agent_name); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php else: ?>
                <div class="whatsapp-button__avatar-default">
                    <?php echo esc_html($agent_initial); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="whatsapp-button__text" style="background: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>;">
            <div style="font-weight: 600; margin-bottom: 2px;">
                <span style="color: <?php echo esc_attr($name_color); ?>;"><?php echo esc_html($agent_name); ?></span>
                <span style="color: <?php echo esc_attr($status_color); ?>; font-size: 12px; margin-left: 5px;"><?php echo esc_html($status_text); ?></span>
            </div>
            <div style="font-size: 13px; color: <?php echo esc_attr($text_color); ?>;">
                <?php echo esc_html($message_text); ?>
            </div>
            <div class="whatsapp-button__icon">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                </svg>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render box
     */
    private function render_box($config) {
        error_log('WhatsApp Chat: render_box called');
        
        $position = $config['button_position'];
        $header_text = $config['header_text'];
        $footer_text = $config['footer_text'];
        $enable_agents = $config['enable_agents'];
        $design = $config['design'];
        
        // Only show box for designs 6-7 (professional designs)
        if (!in_array($design, ['design-6', 'design-7'])) {
            return; // Don't show box for designs 1-5
        }
        
        // Always get contacts for designs 6-7
        $contacts = $this->get_contacts();
        
        if (empty($contacts)) {
            return; // Don't show box if no contacts
        }
        
        ?>
        <div class="whatsapp-box whatsapp-box--<?php echo esc_attr($position); ?>" data-whatsapp-box>
            <div class="whatsapp-box__header">
                <div class="whatsapp-box__title"><?php echo esc_html($header_text); ?></div>
                <button class="whatsapp-box__close" data-whatsapp-close>&times;</button>
            </div>
            <div class="whatsapp-box__content" data-whatsapp-content>
                <?php 
                // Always show agents list for designs 6-7
                $this->render_agents_list();
                ?>
            </div>
            <div class="whatsapp-box__footer">
                <?php echo $footer_text; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get contacts/agents
     */
    private function get_contacts() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_contacts';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            // Return default contact if table doesn't exist
            return array(array(
                'id' => 1,
                'firstname' => get_option('whatsapp_chat_support_team_name', 'Support Team'),
                'lastname' => '',
                'phone' => get_option('whatsapp_chat_phone_number', '+1234567890'),
                'message' => get_option('whatsapp_chat_default_message', 'Hello! I have a question.'),
                'label' => get_option('whatsapp_chat_support_team_label', 'Customer Support'),
                'avatar' => get_avatar_url(1, array('size' => 60, 'default' => 'identicon')),
                'status' => 1,
                'sort_order' => 1
            ));
        }
        
        $contacts = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE status = 1 ORDER BY sort_order ASC",
            ARRAY_A
        );
        
        // Check license status for multi-agent support
        $license_active = WhatsApp_Chat_License::is_license_active();
        
        if (!$license_active && count($contacts) > 1) {
            // Free version: limit to 1 agent (the first one)
            $contacts = array_slice($contacts, 0, 1);
        }
        
        if (empty($contacts)) {
            // Return default contact if no contacts found
            return array(array(
                'id' => 1,
                'firstname' => get_option('whatsapp_chat_support_team_name', 'Support Team'),
                'lastname' => '',
                'phone' => get_option('whatsapp_chat_phone_number', '+1234567890'),
                'message' => get_option('whatsapp_chat_default_message', 'Hello! I have a question.'),
                'label' => get_option('whatsapp_chat_support_team_label', 'Customer Support'),
                'avatar' => get_avatar_url(1, array('size' => 60, 'default' => 'identicon')),
                'status' => 1,
                'sort_order' => 1
            ));
        }
        
        return $contacts;
    }
    
    /**
     * Render single agent box
     */
    private function render_single_agent_box($config) {
        
        $position = $config['button_position'];
        $header_text = $config['header_text'];
        $footer_text = $config['footer_text'];
        
        ?>
        <div class="whatsapp-box whatsapp-box--<?php echo esc_attr($position); ?>" data-whatsapp-box>
            <div class="whatsapp-box__header">
                <div class="whatsapp-box__title"><?php echo esc_html($header_text); ?></div>
                <button class="whatsapp-box__close" data-whatsapp-close>&times;</button>
            </div>
            <div class="whatsapp-box__content" data-whatsapp-content>
            <div class="whatsapp-contact" 
                 data-whatsapp-contact
                 data-whatsapp-phone="<?php echo esc_attr($config['phone_number']); ?>"
                 data-whatsapp-message="<?php echo esc_attr($config['default_message']); ?>">
                <div class="whatsapp-contact__avatar">
                    <div class="whatsapp-contact__avatar-placeholder">
                        <?php echo esc_html(strtoupper(substr($config['support_team_name'], 0, 2))); ?>
                    </div>
                </div>
                <div class="whatsapp-contact__info">
                    <div class="whatsapp-contact__name"><?php echo esc_html($config['support_team_name']); ?></div>
                    <div class="whatsapp-contact__label"><?php echo esc_html($config['support_team_label']); ?></div>
                </div>
                <div class="whatsapp-contact__action">
                    <button class="whatsapp-contact__button" 
                            data-whatsapp-contact-button
                            data-whatsapp-phone="<?php echo esc_attr($config['phone_number']); ?>"
                            data-whatsapp-message="<?php echo esc_attr($config['default_message']); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="whatsapp-box__footer">
                <?php echo $footer_text; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render agents list
     */
    private function render_agents_list() {
        $contacts = $this->get_contacts();
        
        // Debug: Log contacts for troubleshooting
        error_log('WhatsApp Chat - Rendering agents list. Contacts count: ' . count($contacts));
        
        if (empty($contacts)) {
            ?>
            <div class="whatsapp-no-agents">
                <div class="whatsapp-no-agents__icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="#ccc">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                    </svg>
                </div>
                <div class="whatsapp-no-agents__message">
                    No agents available
                </div>
            </div>
            <?php
            return;
        }
        
        foreach ($contacts as $contact) {
            // Skip inactive agents
            if (isset($contact['status']) && $contact['status'] != 1) {
                continue;
            }
            ?>
            <div class="whatsapp-contact" 
                 data-whatsapp-contact
                 data-whatsapp-phone="<?php echo esc_attr($contact['phone']); ?>"
                 data-whatsapp-message="<?php echo esc_attr($contact['message']); ?>">
                <div class="whatsapp-contact__avatar">
                    <?php if (isset($contact['avatar']) && !empty($contact['avatar'])): ?>
                        <img src="<?php echo esc_url($contact['avatar']); ?>" alt="<?php echo esc_attr($contact['firstname']); ?>" class="whatsapp-contact__avatar-img">
                    <?php else: ?>
                        <div class="whatsapp-contact__avatar-placeholder">
                            <?php echo esc_html(strtoupper(substr($contact['firstname'], 0, 1) . substr($contact['lastname'], 0, 1))); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="whatsapp-contact__info">
                    <div class="whatsapp-contact__name">
                        <?php echo esc_html($contact['firstname'] . ' ' . $contact['lastname']); ?>
                    </div>
                    <?php if (!empty($contact['label'])): ?>
                        <div class="whatsapp-contact__label"><?php echo esc_html($contact['label']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="whatsapp-contact__action">
                    <button class="whatsapp-contact__button" 
                            data-whatsapp-contact-button
                            data-whatsapp-phone="<?php echo esc_attr($contact['phone']); ?>"
                            data-whatsapp-message="<?php echo esc_attr($contact['message']); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                        </svg>
                    </button>
                </div>
            </div>
            <?php
        }
    }
    
    /**
     * AJAX get contacts
     */
    public function ajax_get_contacts() {
        check_ajax_referer('whatsapp_chat_widget_nonce', 'nonce');
        
        $contacts = WhatsApp_Chat_Database::get_contacts(array('status' => 1));
        
        // Check license status for multi-agent support
        $license_active = WhatsApp_Chat_License::is_license_active();
        
        if (!$license_active && count($contacts) > 1) {
            // Free version: limit to 1 agent (the first one)
            $contacts = array_slice($contacts, 0, 1);
        }
        
        // Add default avatar for contacts without avatar
        foreach ($contacts as $contact) {
            if (empty($contact->avatar)) {
                $contact->avatar = get_avatar_url($contact->id, array('size' => 60, 'default' => 'identicon'));
            }
        }
        
        if (empty($contacts)) {
            // Fallback to main contact
            $contacts = array((object) array(
                'id' => 1,
                'firstname' => get_option('whatsapp_chat_support_team_name', 'Support Team'),
                'lastname' => '',
                'phone' => get_option('whatsapp_chat_phone_number', '+1234567890'),
                'message' => get_option('whatsapp_chat_default_message', 'Hello! I have a question.'),
                'label' => get_option('whatsapp_chat_support_team_label', 'Customer Support'),
                'avatar' => get_avatar_url(1, array('size' => 60, 'default' => 'identicon')),
                'status' => 1,
                'sort_order' => 1
            ));
        }
        
        wp_send_json_success($contacts);
    }
    
    /**
     * Optimize performance
     */
    public function optimize_performance() {
        // Lazy load widget
        add_action('wp_footer', array($this, 'lazy_load_widget'), 99);
        
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
     * Lazy load widget
     */
    public function lazy_load_widget() {
		if ( ! $this->should_display_widget() ) {
            return;
        }
        
        // Only load widget after page load
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load widget after 1 second to improve page load speed
            setTimeout(function() {
                if (typeof WhatsAppChatWidget !== 'undefined') {
                    WhatsAppChatWidget.init();
                }
            }, 1000);
        });
        </script>
        <?php
    }
}
