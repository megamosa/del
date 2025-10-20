/**
 * WhatsApp Chat Pro - Frontend Widget JavaScript (Clean Version)
 * Professional WhatsApp Chat Widget with Multiple Agents Support
 * 
 * @package WhatsApp_Chat_Pro
 * @since 1.0.0
 */

(function() {
    'use strict';

    /**
     * WhatsApp Chat Widget
     */
    class WhatsAppChatWidget {
        constructor(options) {
            this.options = Object.assign({
                enabled: true,
                phoneNumber: '+1234567890',
                defaultMessage: 'Hello! I have a question about your products.',
                position: 'bottom-right',
                layout: 'button',
                design: 'design-1',
                showOnMobile: true,
                showOnDesktop: true,
                enableAgents: false,
                headerText: 'Chat with us',
                footerText: "We're here to help!",
                supportTeamName: 'Support Team',
                supportTeamLabel: 'Customer Support',
                timeRestrictions: false,
                working_hours_enabled: false,
                is_within_working_hours: true,
                startTime: '09:00',
                endTime: '18:00',
                workingDays: [],
                enableAnalytics: false,
                ga4MeasurementId: '',
                analyticsEventName: 'whatsapp_click',
                agents: []
            }, options);
            
            this.isVisible = false;
            this.chatWindow = null;
            this.init();
        }

        /**
         * Initialize widget
         */
        init() {
            if (!this.options.enabled) {
                return;
            }

            if (this.options.timeRestrictions && !this.is_within_working_hours()) {
                return;
            }

            this.createWidget();
            this.bindEvents();
            this.applyAnimation();
        }

        /**
         * Check if within working hours
         */
        is_within_working_hours() {
            // If working hours are disabled, always show widget (24/7)
            if (!this.options.working_hours_enabled) {
                return true;
            }
            
            const now = new Date();
            const currentDay = now.getDay(); // 0 = Sunday, 1 = Monday, etc.
            const currentHour = now.getHours();
            const currentMinute = now.getMinutes();
            const currentTime = currentHour * 60 + currentMinute;

            // Check if today is a working day
            if (this.options.workingDays && this.options.workingDays.length > 0) {
                const workingDays = this.options.workingDays.map(day => parseInt(day));
                if (!workingDays.includes(currentDay)) {
                    return false;
                }
            }

            const [startHour, startMinute] = this.options.startTime.split(':').map(Number);
            const [endHour, endMinute] = this.options.endTime.split(':').map(Number);
            const startTime = startHour * 60 + startMinute;
            const endTime = endHour * 60 + endMinute;

            return currentTime >= startTime && currentTime <= endTime;
        }

    /**
         * Create widget HTML
     */
        createWidget() {
            const existingWidget = document.querySelector('.whatsapp-chat-widget');
            if (existingWidget) {
                this.chatWindow = existingWidget;
                return;
            }
            
            const widget = document.createElement('div');
            widget.className = `whatsapp-chat-widget ${this.options.design}`;
            widget.setAttribute('data-whatsapp-config', JSON.stringify(this.options));
            
            if (this.options.enableAgents && this.options.agents.length > 0) {
                const widgetContent = this.createMultiAgentWidget();
                widget.innerHTML = widgetContent;
            } else {
                const widgetContent = this.createSingleAgentWidget();
                widget.innerHTML = widgetContent;
            }
            
            document.body.appendChild(widget);
            this.chatWindow = widget;
        }

        /**
         * Create single agent widget
         */
        createSingleAgentWidget() {
            return `
                <div class="whatsapp-button whatsapp-button--${this.options.position} button" data-whatsapp-button>
                    <span class="whatsapp-button__icon">💬</span>
                </div>
                <div class="whatsapp-box whatsapp-box--${this.options.position}" data-whatsapp-box>
                    <div class="whatsapp-box__header">
                        <div class="whatsapp-box__title">${this.options.headerText}</div>
                        <button class="whatsapp-box__close" data-whatsapp-close>×</button>
                    </div>
                    <div class="whatsapp-box__content">
                        <div class="whatsapp-box__message">${this.options.footerText}</div>
                        <a href="https://wa.me/${this.options.phoneNumber}?text=${encodeURIComponent(this.options.defaultMessage)}" 
                           class="whatsapp-box__button" 
                   target="_blank" 
                           rel="noopener">
                            <span class="whatsapp-box__button-icon">💬</span>
                            <span class="whatsapp-box__button-text">Start Chat</span>
                        </a>
                    </div>
                </div>
            `;
        }

    /**
         * Create multi-agent widget
         */
        createMultiAgentWidget() {
            const agents = this.options.agents.filter(agent => agent.status === 1);
            
            if (agents.length === 0) {
                return this.createSingleAgentWidget();
            }

            let contactsHtml = '';
            agents.forEach(agent => {
                contactsHtml += `
                    <div class="whatsapp-contact" data-whatsapp-contact>
                        <div class="whatsapp-contact__avatar">
                            <img src="${agent.avatar}" alt="${agent.firstname}">
                        </div>
                        <div class="whatsapp-contact__info">
                            <div class="whatsapp-contact__name">${agent.firstname} ${agent.lastname}</div>
                            <div class="whatsapp-contact__label">${agent.label}</div>
                        </div>
                        <button class="whatsapp-contact__button" 
                                data-whatsapp-contact-button
                                data-whatsapp-phone="${agent.phone}"
                                data-whatsapp-message="${agent.message}">
                            💬
                        </button>
                    </div>
                `;
            });

            return `
                <div class="whatsapp-button whatsapp-button--${this.options.position} button" data-whatsapp-button>
                    <span class="whatsapp-button__icon">💬</span>
                </div>
                <div class="whatsapp-box whatsapp-box--${this.options.position}" data-whatsapp-box>
                    <div class="whatsapp-box__header">
                        <div class="whatsapp-box__title">${this.options.headerText}</div>
                        <button class="whatsapp-box__close" data-whatsapp-close>×</button>
                    </div>
                    <div class="whatsapp-box__content">
                        <div class="whatsapp-box__message">${this.options.footerText}</div>
                        <div class="whatsapp-contacts">
                            ${contactsHtml}
                        </div>
                    </div>
                </div>
            `;
        }

    /**
         * Bind events
     */
        bindEvents() {
            if (!this.chatWindow) return;

            const button = this.chatWindow.querySelector('[data-whatsapp-button]');
            const box = this.chatWindow.querySelector('[data-whatsapp-box]');
            const closeBtn = this.chatWindow.querySelector('[data-whatsapp-close]');
            const contacts = this.chatWindow.querySelectorAll('[data-whatsapp-contact]');
            
            if (button) {
                if (this.options.enableAgents && this.options.agents.length > 0) {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.trackClick();
                        this.toggleChatWindow();
                    });
                } else {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.trackClick();
                        
                        if (box) {
                            this.toggleChatWindow();
                        } else {
                            const phone = this.options.phoneNumber || this.options.phone_number;
                            const message = this.options.defaultMessage || this.options.default_message;
                            // WhatsApp Chat - Phone and message from config
                            const whatsappUrl = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
                            // WhatsApp Chat - Final WhatsApp URL generated
                            window.open(whatsappUrl, '_blank');
                        }
                    });
                }
            }
            
            if (closeBtn) {
                closeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.hideChatWindow();
                });
            }
            
            if (contacts.length > 0) {
                contacts.forEach((contact) => {
                    const contactBtn = contact.querySelector('[data-whatsapp-contact-button]');
                    
                    // Make entire contact area clickable for designs 6 and 7
                    if (this.options.design === 'design-6' || this.options.design === 'design-7') {
                        contact.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            const phone = contact.getAttribute('data-whatsapp-phone') || 
                                        contactBtn?.getAttribute('data-whatsapp-phone');
                            const message = contact.getAttribute('data-whatsapp-message') || 
                                           contactBtn?.getAttribute('data-whatsapp-message');
                            
                            this.trackClick();
                            this.openWhatsApp(phone, message);
                        });
                    } else if (contactBtn) {
                        // For other designs, keep button-only click
                        contactBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            const phone = contactBtn.getAttribute('data-whatsapp-phone');
                            const message = contactBtn.getAttribute('data-whatsapp-message');
                            
                            this.trackClick();
                            this.openWhatsApp(phone, message);
                        });
                    }
                });
            }
    }
    
    /**
     * Toggle chat window
     */
    toggleChatWindow() {
        if (this.isVisible) {
            this.hideChatWindow();
        } else {
            this.showChatWindow();
        }
    }

    /**
     * Show chat window
     */
    showChatWindow() {
            if (!this.chatWindow) return;

            const box = this.chatWindow.querySelector('[data-whatsapp-box]');
            if (!box) return;

            box.style.display = 'block';
        box.classList.add('active');
        this.isVisible = true;

            setTimeout(() => {
                box.style.opacity = '1';
                box.style.transform = 'scale(1)';
            }, 10);
    }

    /**
     * Hide chat window
     */
    hideChatWindow() {
            if (!this.chatWindow) return;

            const box = this.chatWindow.querySelector('[data-whatsapp-box]');
            if (!box) return;

            box.style.opacity = '0';
            box.style.transform = 'scale(0.9)';

            setTimeout(() => {
                box.style.display = 'none';
                box.classList.remove('active');
            this.isVisible = false;
            }, 200);
        }

        /**
         * Open WhatsApp
         */
        openWhatsApp(phone, message) {
            this.trackClick();
            
            if (!phone) {
                return;
            }

            const cleanPhone = phone.replace(/[^\d+]/g, '');
            const encodedMessage = encodeURIComponent(message || this.options.defaultMessage);
            const whatsappUrl = `https://wa.me/${cleanPhone}?text=${encodedMessage}`;
            
            window.open(whatsappUrl, '_blank');
        }

        /**
         * Track click
     */
        trackClick() {
            this.sendTrackingEvent();
            
            if (this.options.enableAnalytics && this.options.ga4MeasurementId) {
                this.sendAnalyticsEvent();
            }
        }

        /**
         * Send tracking event
         */
        sendTrackingEvent() {
            const ajaxUrl = window.ajaxurl || '/wp-admin/admin-ajax.php';
            
            const formData = new FormData();
            formData.append('action', 'whatsapp_chat_track_click');
            formData.append('nonce', this.options.nonce || '');
            formData.append('timestamp', Date.now());
            formData.append('user_agent', navigator.userAgent);
            formData.append('page_url', window.location.href);
            
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Silent success
            })
            .catch(error => {
                // Silent error
            });
        }

        /**
         * Send analytics event
         */
        sendAnalyticsEvent() {
            if (typeof gtag !== 'undefined') {
                gtag('event', this.options.analyticsEventName, {
                    event_category: 'WhatsApp Chat',
                    event_label: 'Widget Click'
                });
            }
        }

        /**
         * Apply animation to widget
         */
        applyAnimation() {
            if (!this.chatWindow) return;

            const animation = this.options.button_animation || this.options.animation;
            
            if (animation && animation !== 'none') {
                // Remove any existing animation classes
                this.chatWindow.classList.remove('animation-bounce', 'animation-pulse', 'animation-shake', 'animation-wiggle', 'animation-float');
                
                // Add the new animation class
                this.chatWindow.classList.add('animation-' + animation);
            }
        }
    }

    // Initialize widget
    function initWhatsAppWidget() {
        if (typeof whatsappChat !== 'undefined' && whatsappChat.config) {
            window.whatsappChat = new WhatsAppChatWidget(whatsappChat.config);
        }
    }

    // Performance optimized initialization
    function initWhatsAppWidgetOptimized() {
        if (typeof whatsappChat === 'undefined' || !whatsappChat.config) {
            return;
        }
        
        if (!whatsappChat.config.enabled) {
            return;
        }
        
        // Preload widget immediately for better performance
        requestAnimationFrame(() => {
            window.whatsappChat = new WhatsAppChatWidget(whatsappChat.config);
        });
    }

    // Initialize when DOM is ready with performance optimization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            // Use requestAnimationFrame for smoother initialization
            requestAnimationFrame(initWhatsAppWidgetOptimized);
        });
    } else {
        // DOM already loaded, initialize immediately
        requestAnimationFrame(initWhatsAppWidgetOptimized);
    }

    // Reduced fallback timeout for faster loading
    setTimeout(() => {
        if (!window.whatsappChat) {
            initWhatsAppWidget();
        }
    }, 500); // Reduced from 1000ms to 500ms

})();
