<?php
/**
 * Database operations class
 * 
 * @package WhatsApp_Chat_Pro
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    wp_die(__('Direct access not allowed.', 'whatsapp-chat-pro'));
}

/**
 * Database operations class
 */
class WhatsApp_Chat_Database {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize database operations
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Add custom database operations if needed
        
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
        
        // Create database tables for new blog
        self::create_tables();
        
        // Set default options for new blog
        self::set_default_options();
        
        // Initialize default designs for new blog
        self::init_default_designs();
        
        // Initialize default departments and working hours for new blog
        self::init_default_departments();
        self::init_default_working_hours();
        
        restore_current_blog();
    }
    
    /**
     * Handle blog deletion in multisite
     */
    public function blog_deleted($blog_id) {
        switch_to_blog($blog_id);
        
        // Clean up database tables for deleted blog
        self::drop_tables();
        
        restore_current_blog();
    }
    
    /**
     * Get contacts
     * 
     * @param array $args Query arguments
     * @return array
     */
    public static function get_contacts($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => null, // Get all contacts by default
            'orderby' => 'sort_order',
            'order' => 'ASC',
            'limit' => -1
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_contacts';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        
        $sql = "SELECT * FROM $table_name";
        $params = array();
        
        // Add status filter only if specified
        if ($args['status'] !== null) {
            $sql .= " WHERE status = %d";
            $params[] = $args['status'];
        }
        
        if ($args['orderby']) {
            $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
        }
        
        if ($args['limit'] > 0) {
            $sql .= " LIMIT %d";
            $params[] = $args['limit'];
        }
        
        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }
        
        $results = $wpdb->get_results($sql);
        
        return $results;
    }
    
    /**
     * Get active contacts only
     * 
     * @param array $args Query arguments
     * @return array
     */
    public static function get_active_contacts($args = array()) {
        $args['status'] = 1;
        return self::get_contacts($args);
    }
    
    /**
     * Get contact by ID
     * 
     * @param int $id Contact ID
     * @return object|null
     */
    public static function get_contact($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_contacts';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ));
    }
    
    /**
     * Insert contact
     * 
     * @param array $data Contact data
     * @return int|false
     */
    public static function insert_contact($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_contacts';
        
        $defaults = array(
            'firstname' => '',
            'lastname' => '',
            'phone' => '',
            'message' => '',
            'label' => '',
            'avatar' => '',
            'status' => 1,
            'sort_order' => 0
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert($table_name, $data);
        
        if ($result === false) {
            return false;
        }
        
        $insert_id = $wpdb->insert_id;
        
        return $insert_id;
    }
    
    /**
     * Update contact
     * 
     * @param int $id Contact ID
     * @param array $data Contact data
     * @return bool
     */
    public static function update_contact($id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_contacts';
        
        $result = $wpdb->update(
            $table_name,
            $data,
            array('id' => $id),
            null,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete contact
     * 
     * @param int $id Contact ID
     * @return bool
     */
    public static function delete_contact($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_contacts';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get designs
     * 
     * @param array $args Query arguments
     * @return array
     */
    public static function get_designs($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'is_premium' => null,
            'orderby' => 'id',
            'order' => 'ASC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_designs';
        
        $sql = "SELECT * FROM $table_name WHERE 1=1";
        $params = array();
        
        if ($args['is_premium'] !== null) {
            $sql .= " AND is_premium = %d";
            $params[] = $args['is_premium'];
        }
        
        if ($args['orderby']) {
            $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
        }
        
        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get design by slug
     * 
     * @param string $slug Design slug
     * @return object|null
     */
    public static function get_design($slug) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_designs';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE slug = %s",
            $slug
        ));
    }
    
    /**
     * Insert design
     * 
     * @param array $data Design data
     * @return int|false
     */
    public static function insert_design($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_designs';
        
        $defaults = array(
            'name' => '',
            'slug' => '',
            'is_premium' => 0,
            'css_class' => '',
            'description' => '',
            'preview_image' => ''
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert($table_name, $data);
        
        if ($result === false) {
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get setting
     * 
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed
     */
    public static function get_setting($key, $default = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_settings';
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $table_name WHERE setting_key = %s",
            $key
        ));
        
        if ($result === null) {
            return $default;
        }
        
        // Try to unserialize if it's serialized
        $unserialized = maybe_unserialize($result);
        if ($unserialized !== false) {
            return $unserialized;
        }
        
        return $result;
    }
    
    /**
     * Update setting
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param string $type Setting type
     * @return bool
     */
    public static function update_setting($key, $value, $type = 'string') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_settings';
        
        // Serialize if needed
        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
        }
        
        $result = $wpdb->replace(
            $table_name,
            array(
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_type' => $type
            ),
            array('%s', '%s', '%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete setting
     * 
     * @param string $key Setting key
     * @return bool
     */
    public static function delete_setting($key) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_settings';
        
        $result = $wpdb->delete(
            $table_name,
            array('setting_key' => $key),
            array('%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Check license status
     * 
     * @return string
     */
    public static function get_license_status() {
        return get_option('whatsapp_chat_license_status', 'inactive');
    }
    
    /**
     * Check if user can add more contacts
     * 
     * @return bool
     */
    public static function can_add_contact() {
        $license_status = self::get_license_status();
        
        if ($license_status === 'active') {
            return true; // Pro version - unlimited contacts
        }
        
        // Free version - check if user has less than 1 contact
        $contacts_count = self::get_contacts_count();
        return $contacts_count < 1;
    }
    
    /**
     * Get contacts count
     * 
     * @return int
     */
    public static function get_contacts_count() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_contacts';
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        return intval($count);
    }
    
    /**
     * Check if design is premium
     * 
     * @param string $design_slug
     * @return bool
     */
    public static function is_premium_design($design_slug) {
        $design = self::get_design($design_slug);
        
        if (!$design) {
            return false;
        }
        
        return (bool) $design->is_premium;
    }
    
    /**
     * Check if user can use premium design
     * 
     * @param string $design_slug
     * @return bool
     */
    public static function can_use_design($design_slug) {
        $license_status = self::get_license_status();
        
        if ($license_status === 'active') {
            return true; // Pro version - all designs available
        }
        
        // Free version - only non-premium designs
        return !self::is_premium_design($design_slug);
    }
    
    /**
     * Get available designs for current license
     * 
     * @return array
     */
    public static function get_available_designs() {
        $license_status = self::get_license_status();
        $all_designs = self::get_designs();
        $available_designs = array();
        
        foreach ($all_designs as $design) {
            if ($license_status === 'active' || !$design->is_premium) {
                $available_designs[] = $design;
            }
        }
        
        return $available_designs;
    }
    
    /**
     * Initialize default designs
     */
    public static function init_default_designs() {
        $designs = array(
            array(
                'name' => 'Classic Green',
                'slug' => 'design-1',
                'is_premium' => 0,
                'css_class' => 'design-1',
                'description' => 'Traditional WhatsApp green design with clean layout',
                'preview_image' => ''
            ),
            array(
                'name' => 'Modern Blue',
                'slug' => 'design-2',
                'is_premium' => 0,
                'css_class' => 'design-2',
                'description' => 'Contemporary blue theme with rounded corners',
                'preview_image' => ''
            ),
            array(
                'name' => 'Minimal White',
                'slug' => 'design-3',
                'is_premium' => 0,
                'css_class' => 'design-3',
                'description' => 'Clean white design with subtle shadows',
                'preview_image' => ''
            ),
            array(
                'name' => 'Dark Theme',
                'slug' => 'design-4',
                'is_premium' => 0,
                'css_class' => 'design-4',
                'description' => 'Dark mode design for modern websites',
                'preview_image' => ''
            ),
            array(
                'name' => 'Premium Gold',
                'slug' => 'design-5',
                'is_premium' => 1,
                'css_class' => 'design-5',
                'description' => 'Luxury gold design with premium feel',
                'preview_image' => ''
            ),
            array(
                'name' => 'Professional Gradient',
                'slug' => 'design-6',
                'is_premium' => 1,
                'css_class' => 'design-6',
                'description' => 'Modern gradient design with professional look',
                'preview_image' => ''
            ),
            array(
                'name' => 'Ultimate Premium',
                'slug' => 'design-7',
                'is_premium' => 1,
                'css_class' => 'design-7',
                'description' => 'Most advanced design with animations and effects',
                'preview_image' => ''
            )
        );
        
        foreach ($designs as $design) {
            $existing = self::get_design($design['slug']);
            if (!$existing) {
                self::insert_design($design);
            }
        }
    }
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Contacts table
        $contacts_table = $wpdb->prefix . 'whatsapp_chat_contacts';
        $contacts_sql = "CREATE TABLE $contacts_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            firstname varchar(100) NOT NULL,
            lastname varchar(100) NOT NULL,
            phone varchar(20) NOT NULL,
            message text,
            label varchar(100),
            avatar varchar(255),
            department_id int(11) DEFAULT NULL,
            role varchar(100) DEFAULT 'agent',
            welcome_message text,
            working_hours_enabled tinyint(1) DEFAULT 0,
            working_days varchar(20) DEFAULT '1,2,3,4,5',
            start_time time DEFAULT '09:00:00',
            end_time time DEFAULT '18:00:00',
            timezone varchar(50) DEFAULT 'UTC',
            status tinyint(1) DEFAULT 1,
            sort_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY department_id (department_id)
        ) $charset_collate;";
        
        // Designs table
        $designs_table = $wpdb->prefix . 'whatsapp_chat_designs';
        $designs_sql = "CREATE TABLE $designs_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(100) NOT NULL UNIQUE,
            is_premium tinyint(1) DEFAULT 0,
            css_class varchar(100),
            description text,
            preview_image varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Settings table
        $settings_table = $wpdb->prefix . 'whatsapp_chat_settings';
        $settings_sql = "CREATE TABLE $settings_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL UNIQUE,
            setting_value longtext,
            setting_type varchar(20) DEFAULT 'string',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Departments table
        $departments_table = $wpdb->prefix . 'whatsapp_chat_departments';
        $departments_sql = "CREATE TABLE $departments_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            color varchar(7) DEFAULT '#25D366',
            status tinyint(1) DEFAULT 1,
            sort_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Working hours table
        $working_hours_table = $wpdb->prefix . 'whatsapp_chat_working_hours';
        $working_hours_sql = "CREATE TABLE $working_hours_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            day_of_week tinyint(1) NOT NULL,
            start_time time,
            end_time time,
            is_active tinyint(1) DEFAULT 1,
            timezone varchar(50) DEFAULT 'UTC',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Holidays table
        $holidays_table = $wpdb->prefix . 'whatsapp_chat_holidays';
        $holidays_sql = "CREATE TABLE $holidays_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            start_date date NOT NULL,
            end_date date NOT NULL,
            is_recurring tinyint(1) DEFAULT 0,
            status tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Analytics table
        $analytics_table = $wpdb->prefix . 'whatsapp_chat_analytics';
        $analytics_sql = "CREATE TABLE $analytics_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            agent_id int(11),
            page_url varchar(500),
            user_agent text,
            ip_address varchar(45),
            session_id varchar(100),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY agent_id (agent_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Page display rules table
        $display_rules_table = $wpdb->prefix . 'whatsapp_chat_display_rules';
        $display_rules_sql = "CREATE TABLE $display_rules_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            rule_type varchar(20) NOT NULL,
            rule_value varchar(500) NOT NULL,
            action varchar(10) NOT NULL,
            status tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Debug: Log table creation
        error_log('WhatsApp Chat: Creating database tables...');
        
        $contacts_result = dbDelta($contacts_sql);
        error_log('WhatsApp Chat: Contacts table creation result: ' . print_r($contacts_result, true));
        
        dbDelta($designs_sql);
        dbDelta($settings_sql);
        dbDelta($departments_sql);
        dbDelta($working_hours_sql);
        dbDelta($holidays_sql);
        dbDelta($analytics_sql);
        dbDelta($display_rules_sql);
        
        error_log('WhatsApp Chat: Database tables creation completed');
        
        // Initialize default designs
        self::init_default_designs();
        
        // Initialize default departments
        self::init_default_departments();
        
        // Initialize default working hours
        self::init_default_working_hours();
    }
    
    /**
     * Drop database tables
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'whatsapp_chat_contacts',
            $wpdb->prefix . 'whatsapp_chat_designs',
            $wpdb->prefix . 'whatsapp_chat_settings',
            $wpdb->prefix . 'whatsapp_chat_departments',
            $wpdb->prefix . 'whatsapp_chat_working_hours',
            $wpdb->prefix . 'whatsapp_chat_holidays',
            $wpdb->prefix . 'whatsapp_chat_analytics',
            $wpdb->prefix . 'whatsapp_chat_display_rules'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
    
    /**
     * Initialize default departments
     */
    public static function init_default_departments() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_departments';
        
        $departments = array(
            array(
                'name' => 'Customer Support',
                'description' => 'General customer support and inquiries',
                'color' => '#25D366',
                'sort_order' => 1
            ),
            array(
                'name' => 'Sales Team',
                'description' => 'Sales inquiries and product information',
                'color' => '#007BFF',
                'sort_order' => 2
            ),
            array(
                'name' => 'Technical Support',
                'description' => 'Technical assistance and troubleshooting',
                'color' => '#FF6B35',
                'sort_order' => 3
            )
        );
        
        foreach ($departments as $department) {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $table_name WHERE name = %s",
                $department['name']
            ));
            
            if (!$existing) {
                $wpdb->insert($table_name, $department);
            }
        }
    }
    
    /**
     * Initialize default working hours
     */
    public static function init_default_working_hours() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_working_hours';
        
        $working_hours = array(
            array('day_of_week' => 1, 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_active' => 1), // Monday
            array('day_of_week' => 2, 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_active' => 1), // Tuesday
            array('day_of_week' => 3, 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_active' => 1), // Wednesday
            array('day_of_week' => 4, 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_active' => 1), // Thursday
            array('day_of_week' => 5, 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_active' => 1), // Friday
            array('day_of_week' => 6, 'start_time' => '10:00:00', 'end_time' => '16:00:00', 'is_active' => 0), // Saturday
            array('day_of_week' => 0, 'start_time' => '10:00:00', 'end_time' => '16:00:00', 'is_active' => 0), // Sunday
        );
        
        foreach ($working_hours as $hours) {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $table_name WHERE day_of_week = %d",
                $hours['day_of_week']
            ));
            
            if (!$existing) {
                $wpdb->insert($table_name, $hours);
            }
        }
    }
    
    /**
     * Get departments
     */
    public static function get_departments($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => 1,
            'orderby' => 'sort_order',
            'order' => 'ASC',
            'limit' => -1
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_departments';
        
        $sql = "SELECT * FROM $table_name WHERE status = %d";
        $params = array($args['status']);
        
        if ($args['orderby']) {
            $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
        }
        
        if ($args['limit'] > 0) {
            $sql .= " LIMIT %d";
            $params[] = $args['limit'];
        }
        
        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get working hours
     */
    public static function get_working_hours() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_working_hours';
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY day_of_week");
    }
    
    /**
     * Check if current time is within working hours
     */
    public static function is_within_working_hours() {
        $working_hours = self::get_working_hours();
        $current_day = (int) date('w'); // 0 = Sunday, 1 = Monday, etc.
        $current_time = date('H:i:s');
        
        foreach ($working_hours as $hours) {
            if ($hours->day_of_week == $current_day && $hours->is_active) {
                return $current_time >= $hours->start_time && $current_time <= $hours->end_time;
            }
        }
        
        return false;
    }
    
    /**
     * Get holidays
     */
    public static function get_holidays($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => 1,
            'date' => null
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_holidays';
        
        $sql = "SELECT * FROM $table_name WHERE status = %d";
        $params = array($args['status']);
        
        if ($args['date']) {
            $sql .= " AND start_date <= %s AND end_date >= %s";
            $params[] = $args['date'];
            $params[] = $args['date'];
        }
        
        $sql = $wpdb->prepare($sql, $params);
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Check if current date is a holiday
     */
    public static function is_holiday() {
        $today = date('Y-m-d');
        $holidays = self::get_holidays(array('date' => $today));
        
        return !empty($holidays);
    }
    
    /**
     * Track analytics event
     */
    public static function track_event($event_type, $agent_id = null, $page_url = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_analytics';
        
        $data = array(
            'event_type' => $event_type,
            'agent_id' => $agent_id,
            'page_url' => $page_url ?: (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'ip_address' => self::get_client_ip(),
            'session_id' => session_id() ?: uniqid()
        );
        
        return $wpdb->insert($table_name, $data);
    }
    
    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
    
    /**
     * Get analytics data
     */
    public static function get_analytics($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'event_type' => null,
            'agent_id' => null,
            'date_from' => null,
            'date_to' => null,
            'limit' => 100
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_analytics';
        
        $sql = "SELECT * FROM $table_name WHERE 1=1";
        $params = array();
        
        if ($args['event_type']) {
            $sql .= " AND event_type = %s";
            $params[] = $args['event_type'];
        }
        
        if ($args['agent_id']) {
            $sql .= " AND agent_id = %d";
            $params[] = $args['agent_id'];
        }
        
        if ($args['date_from']) {
            $sql .= " AND created_at >= %s";
            $params[] = $args['date_from'];
        }
        
        if ($args['date_to']) {
            $sql .= " AND created_at <= %s";
            $params[] = $args['date_to'];
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($args['limit'] > 0) {
            $sql .= " LIMIT %d";
            $params[] = $args['limit'];
        }
        
        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Update working hours for a specific day
     * 
     * @param int $day_of_week Day of week (0=Sunday, 1=Monday, etc.)
     * @param int $is_active Whether the day is active
     * @param string $start_time Start time (HH:MM format)
     * @param string $end_time End time (HH:MM format)
     * @return bool
     */
    public static function update_working_hours($day_of_week, $is_active, $start_time, $end_time) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'whatsapp_chat_working_hours';
        
        // Check if record exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE day_of_week = %d",
            $day_of_week
        ));
        
        if ($existing) {
            // Update existing record
            return $wpdb->update(
                $table_name,
                array(
                    'is_active' => $is_active,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'updated_at' => current_time('mysql')
                ),
                array('day_of_week' => $day_of_week),
                array('%d', '%s', '%s', '%s'),
                array('%d')
            ) !== false;
        } else {
            // Insert new record
            return $wpdb->insert(
                $table_name,
                array(
                    'day_of_week' => $day_of_week,
                    'is_active' => $is_active,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s')
            ) !== false;
        }
    }
}
