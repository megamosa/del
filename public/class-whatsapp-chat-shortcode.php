<?php
/**
 * Shortcode functionality
 * 
 * @package WhatsApp_Chat_Pro
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    wp_die(__('Direct access not allowed.', 'whatsapp-chat-pro'));
}

/**
 * Shortcode class
 */
class WhatsApp_Chat_Shortcode {
    
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
        // Register shortcodes
        add_shortcode('whatsapp_chat', array($this, 'whatsapp_chat_shortcode'));
        add_shortcode('whatsapp_button', array($this, 'whatsapp_button_shortcode'));
        add_shortcode('whatsapp_contact', array($this, 'whatsapp_contact_shortcode'));
    }
    
    /**
     * WhatsApp Chat shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function whatsapp_chat_shortcode($atts) {
        $atts = shortcode_atts(array(
            'phone' => '',
            'message' => '',
            'text' => '',
            'design' => '',
            'position' => '',
            'size' => 'normal',
            'color' => '',
            'show_icon' => 'true',
            'show_text' => 'true'
        ), $atts, 'whatsapp_chat');
        
        // Get default values from settings
        $default_phone = get_option('whatsapp_chat_phone_number', '+1234567890');
        $default_message = get_option('whatsapp_chat_default_message', 'Hello! I have a question about your products.');
        $default_design = get_option('whatsapp_chat_design', 'design-1');
        
        // Use shortcode attributes or defaults
        $phone = !empty($atts['phone']) ? $atts['phone'] : $default_phone;
        $message = !empty($atts['message']) ? $atts['message'] : $default_message;
        $text = !empty($atts['text']) ? $atts['text'] : $message;
        $design = !empty($atts['design']) ? $atts['design'] : $default_design;
        $position = !empty($atts['position']) ? $atts['position'] : 'inline';
        $size = $atts['size'];
        $color = $atts['color'];
        $show_icon = $atts['show_icon'] === 'true';
        $show_text = $atts['show_text'] === 'true';
        
        // Check if design is premium
        if (WhatsApp_Chat_License::is_premium_feature($design) && !WhatsApp_Chat_License::is_license_active()) {
            $design = 'design-1'; // Fallback to free design
        }
        
        // Generate unique ID
        $unique_id = 'whatsapp-chat-' . uniqid();
        
        // Build CSS classes
        $css_classes = array(
            'whatsapp-chat-shortcode',
            'whatsapp-chat-' . $design,
            'whatsapp-chat-' . $size
        );
        
        if ($position !== 'inline') {
            $css_classes[] = 'whatsapp-chat-' . $position;
        }
        
        // Build inline styles
        $inline_styles = array();
        if (!empty($color)) {
            $inline_styles[] = '--wa-color: ' . esc_attr($color) . ';';
        }
        
        $style_attr = !empty($inline_styles) ? ' style="' . implode(' ', $inline_styles) . '"' : '';
        
        // Build button HTML
        $button_html = '<div class="' . implode(' ', $css_classes) . '"' . $style_attr . '>';
        $button_html .= '<a href="https://wa.me/' . esc_attr($phone) . '?text=' . urlencode($message) . '" target="_blank" class="whatsapp-chat-link">';
        
        if ($show_icon) {
            $button_html .= '<span class="whatsapp-chat-icon">';
            $button_html .= '<svg viewBox="0 0 24 24" fill="currentColor">';
            $button_html .= '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>';
            $button_html .= '</svg>';
            $button_html .= '</span>';
        }
        
        if ($show_text) {
            $button_html .= '<span class="whatsapp-chat-text">' . esc_html($text) . '</span>';
        }
        
        $button_html .= '</a>';
        $button_html .= '</div>';
        
        // Enqueue styles if not already enqueued
        if (!wp_style_is('whatsapp-chat-widget', 'enqueued')) {
            wp_enqueue_style('whatsapp-chat-widget', WHATSAPP_CHAT_PLUGIN_URL . 'public/css/widget.css', array(), WHATSAPP_CHAT_VERSION);
        }
        
        return $button_html;
    }
    
    /**
     * WhatsApp Button shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function whatsapp_button_shortcode($atts) {
        $atts = shortcode_atts(array(
            'phone' => '',
            'message' => '',
            'text' => 'Chat with us',
            'design' => 'design-1',
            'size' => 'normal'
        ), $atts, 'whatsapp_button');
        
        return $this->whatsapp_chat_shortcode($atts);
    }
    
    /**
     * WhatsApp Contact shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function whatsapp_contact_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'name' => '',
            'phone' => '',
            'message' => '',
            'label' => '',
            'avatar' => '',
            'design' => 'design-1'
        ), $atts, 'whatsapp_contact');
        
        // If ID is provided, get contact from database
        if (!empty($atts['id'])) {
            $contact = WhatsApp_Chat_Database::get_contact(intval($atts['id']));
            if ($contact) {
                $atts['name'] = $contact->firstname . ' ' . $contact->lastname;
                $atts['phone'] = $contact->phone;
                $atts['message'] = $contact->message;
                $atts['label'] = $contact->label;
                $atts['avatar'] = $contact->avatar;
            }
        }
        
        // Build contact HTML
        $contact_html = '<div class="whatsapp-contact-shortcode whatsapp-chat-' . esc_attr($atts['design']) . '">';
        
        if (!empty($atts['avatar'])) {
            $contact_html .= '<div class="whatsapp-contact-avatar">';
            $contact_html .= '<img src="' . esc_url($atts['avatar']) . '" alt="' . esc_attr($atts['name']) . '">';
            $contact_html .= '</div>';
        }
        
        $contact_html .= '<div class="whatsapp-contact-info">';
        $contact_html .= '<div class="whatsapp-contact-name">' . esc_html($atts['name']) . '</div>';
        
        if (!empty($atts['label'])) {
            $contact_html .= '<div class="whatsapp-contact-label">' . esc_html($atts['label']) . '</div>';
        }
        
        $contact_html .= '</div>';
        
        $contact_html .= '<div class="whatsapp-contact-button">';
        $contact_html .= '<a href="https://wa.me/' . esc_attr($atts['phone']) . '?text=' . urlencode($atts['message']) . '" target="_blank" class="whatsapp-contact-link">';
        $contact_html .= '<svg viewBox="0 0 24 24" fill="currentColor">';
        $contact_html .= '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>';
        $contact_html .= '</svg>';
        $contact_html .= '</a>';
        $contact_html .= '</div>';
        
        $contact_html .= '</div>';
        
        // Enqueue styles if not already enqueued
        if (!wp_style_is('whatsapp-chat-widget', 'enqueued')) {
            wp_enqueue_style('whatsapp-chat-widget', WHATSAPP_CHAT_PLUGIN_URL . 'public/css/widget.css', array(), WHATSAPP_CHAT_VERSION);
        }
        
        return $contact_html;
    }
}
