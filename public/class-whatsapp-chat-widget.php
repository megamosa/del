<?php
/**
 * WordPress Widget
 * 
 * @package WhatsApp_Chat_Pro
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    wp_die(__('Direct access not allowed.', 'whatsapp-chat-pro'));
}

/**
 * WhatsApp Chat Widget
 */
class WhatsApp_Chat_Widget extends WP_Widget {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'whatsapp_chat_widget',
            __('WhatsApp Chat', 'whatsapp-chat-pro'),
            array(
                'description' => __('Display WhatsApp chat button in sidebar or widget areas.', 'whatsapp-chat-pro'),
                'classname' => 'whatsapp-chat-widget'
            )
        );
    }
    
    /**
     * Widget output
     * 
     * @param array $args Widget arguments
     * @param array $instance Widget instance
     */
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $phone = !empty($instance['phone']) ? $instance['phone'] : get_option('whatsapp_chat_phone_number', '+1234567890');
        $message = !empty($instance['message']) ? $instance['message'] : get_option('whatsapp_chat_default_message', 'Hello! I have a question.');
        $design = !empty($instance['design']) ? $instance['design'] : 'design-1';
        $show_icon = !empty($instance['show_icon']);
        $show_text = !empty($instance['show_text']);
        $button_text = !empty($instance['button_text']) ? $instance['button_text'] : $message;
        
        // Check if design is premium
        if (WhatsApp_Chat_License::is_premium_feature($design) && !WhatsApp_Chat_License::is_license_active()) {
            $design = 'design-1'; // Fallback to free design
        }
        
        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        // Build widget HTML
        $widget_html = '<div class="whatsapp-chat-widget-sidebar whatsapp-chat-' . esc_attr($design) . '">';
        $widget_html .= '<a href="https://wa.me/' . esc_attr($phone) . '?text=' . urlencode($message) . '" target="_blank" class="whatsapp-chat-link">';
        
        if ($show_icon) {
            $widget_html .= '<span class="whatsapp-chat-icon">';
            $widget_html .= '<svg viewBox="0 0 24 24" fill="currentColor">';
            $widget_html .= '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>';
            $widget_html .= '</svg>';
            $widget_html .= '</span>';
        }
        
        if ($show_text) {
            $widget_html .= '<span class="whatsapp-chat-text">' . esc_html($button_text) . '</span>';
        }
        
        $widget_html .= '</a>';
        $widget_html .= '</div>';
        
        echo $widget_html;
        
        echo $args['after_widget'];
        
        // Enqueue styles if not already enqueued
        if (!wp_style_is('whatsapp-chat-widget', 'enqueued')) {
            wp_enqueue_style('whatsapp-chat-widget', WHATSAPP_CHAT_PLUGIN_URL . 'public/css/widget.css', array(), WHATSAPP_CHAT_VERSION);
        }
    }
    
    /**
     * Widget form
     * 
     * @param array $instance Widget instance
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $phone = !empty($instance['phone']) ? $instance['phone'] : '';
        $message = !empty($instance['message']) ? $instance['message'] : '';
        $design = !empty($instance['design']) ? $instance['design'] : 'design-1';
        $show_icon = !empty($instance['show_icon']);
        $show_text = !empty($instance['show_text']);
        $button_text = !empty($instance['button_text']) ? $instance['button_text'] : '';
        
        // Get available designs
        $designs = WhatsApp_Chat_Database::get_designs();
        $license_active = WhatsApp_Chat_License::is_license_active();
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'whatsapp-chat-pro'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('phone'); ?>"><?php _e('Phone Number:', 'whatsapp-chat-pro'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('phone'); ?>" name="<?php echo $this->get_field_name('phone'); ?>" type="text" value="<?php echo esc_attr($phone); ?>" placeholder="<?php echo esc_attr(get_option('whatsapp_chat_phone_number', '+1234567890')); ?>">
            <small><?php _e('Leave empty to use default phone number', 'whatsapp-chat-pro'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('message'); ?>"><?php _e('Default Message:', 'whatsapp-chat-pro'); ?></label>
            <textarea class="widefat" id="<?php echo $this->get_field_id('message'); ?>" name="<?php echo $this->get_field_name('message'); ?>" rows="3"><?php echo esc_textarea($message); ?></textarea>
            <small><?php _e('Leave empty to use default message', 'whatsapp-chat-pro'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('button_text'); ?>"><?php _e('Button Text:', 'whatsapp-chat-pro'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('button_text'); ?>" name="<?php echo $this->get_field_name('button_text'); ?>" type="text" value="<?php echo esc_attr($button_text); ?>" placeholder="<?php echo esc_attr($message); ?>">
            <small><?php _e('Leave empty to use default message', 'whatsapp-chat-pro'); ?></small>
        </p>
        
        <p>
            <label><?php _e('Design:', 'whatsapp-chat-pro'); ?></label>
            <div class="whatsapp-widget-design-info">
                <span class="design-badge design-<?php echo str_replace('design-', '', $design); ?>">
                    <?php 
                    $design_names = array(
                        'design-1' => 'Modern Green',
                        'design-2' => 'Corporate Blue', 
                        'design-3' => 'Dark Elegant',
                        'design-4' => 'Gradient Modern',
                        'design-5' => 'Bright Red',
                        'design-6' => 'Professional Banner',
                        'design-7' => 'Custom Avatar'
                    );
                    echo $design_names[$design] ?? 'Unknown Design';
                    ?>
                </span>
                <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-designs'); ?>" class="button button-small" target="_blank">
                    <?php _e('Change Design', 'whatsapp-chat-pro'); ?>
                </a>
            </div>
            <small><?php _e('Go to Designs tab to change the widget design', 'whatsapp-chat-pro'); ?></small>
        </p>
        
        <p>
            <label>
                <input type="checkbox" id="<?php echo $this->get_field_id('show_icon'); ?>" name="<?php echo $this->get_field_name('show_icon'); ?>" <?php checked($show_icon); ?>>
                <?php _e('Show WhatsApp Icon', 'whatsapp-chat-pro'); ?>
            </label>
        </p>
        
        <p>
            <label>
                <input type="checkbox" id="<?php echo $this->get_field_id('show_text'); ?>" name="<?php echo $this->get_field_name('show_text'); ?>" <?php checked($show_text); ?>>
                <?php _e('Show Button Text', 'whatsapp-chat-pro'); ?>
            </label>
        </p>
        
        <?php if (!$license_active): ?>
        <p style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 4px;">
            <strong><?php _e('Premium Features Available', 'whatsapp-chat-pro'); ?></strong><br>
            <?php _e('Some designs are available in the premium version. Activate your license to unlock all features.', 'whatsapp-chat-pro'); ?>
            <a href="<?php echo admin_url('admin.php?page=whatsapp-chat-license'); ?>" class="button button-small"><?php _e('Activate License', 'whatsapp-chat-pro'); ?></a>
        </p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Update widget
     * 
     * @param array $new_instance New instance
     * @param array $old_instance Old instance
     * @return array
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['phone'] = (!empty($new_instance['phone'])) ? sanitize_text_field($new_instance['phone']) : '';
        $instance['message'] = (!empty($new_instance['message'])) ? sanitize_textarea_field($new_instance['message']) : '';
        $instance['design'] = (!empty($new_instance['design'])) ? sanitize_text_field($new_instance['design']) : 'design-1';
        $instance['show_icon'] = !empty($new_instance['show_icon']);
        $instance['show_text'] = !empty($new_instance['show_text']);
        $instance['button_text'] = (!empty($new_instance['button_text'])) ? sanitize_text_field($new_instance['button_text']) : '';
        
        return $instance;
    }
}
