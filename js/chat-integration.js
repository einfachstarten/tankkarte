class ChatIntegration {
    constructor() {
        this.config = null;
        this.currentLanguage = 'de';
        this.isEnabled = false;
    }

    async init() {
        try {
            const response = await fetch(`data/feature-flags.json?v=${window.CACHE_VERSION || Date.now()}`);
            const data = await response.json();
            this.config = data.features.whatsapp_integration;
            this.isEnabled = this.config && this.config.enabled;
            
            if (this.isEnabled) {
                this.initWhatsAppButton();
                this.enhanceCTAButtons();
                console.log('WhatsApp Integration: Enabled');
            } else {
                console.log('WhatsApp Integration: Disabled');
            }
        } catch (error) {
            console.error('Failed to load feature flags:', error);
            this.isEnabled = false;
        }
    }

    generateWhatsAppLink(preMessage = '') {
        if (!this.isEnabled) return '#';
        const message = encodeURIComponent(preMessage);
        return `https://wa.me/${this.config.phone_number}?text=${message}`;
    }

    initWhatsAppButton() {
        if (!this.isEnabled) return;

        const button = document.createElement('div');
        button.className = 'whatsapp-float-btn';
        button.innerHTML = `
            <i class="fab fa-whatsapp"></i>
            <span class="whatsapp-text" data-translate="chat.whatsapp_text">${this.config.display_name}</span>
        `;
        button.onclick = () => this.openWhatsApp();
        button.setAttribute('data-position', this.config.position || 'bottom-right');
        
        if (!this.config.show_on_mobile) {
            button.setAttribute('data-mobile-hidden', 'true');
        }
        
        document.body.appendChild(button);
    }

    openWhatsApp(customMessage = '') {
        if (!this.isEnabled) return;
        
        const defaultMessage = window.translationLoader?.getTranslation('chat.whatsapp_default_message') || 'Hallo! Ich interessiere mich für die Mobilitätslösungen von filo.cards.';
        const message = customMessage || defaultMessage;
        
        if (window.gtag) {
            window.gtag('event', 'whatsapp_click', {
                event_category: 'engagement',
                event_label: customMessage ? 'cta_triggered' : 'float_button'
            });
        }
        
        window.open(this.generateWhatsAppLink(message), '_blank');
    }

    enhanceCTAButtons() {
        if (!this.isEnabled || !this.config.show_notifications) return;

        document.querySelectorAll('.cta-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const translationKey = btn.getAttribute('data-translate');
                const ctaType = translationKey ? translationKey.split('.')[1] : 'consultation';
                
                setTimeout(() => {
                    this.showWhatsAppNotification(ctaType);
                }, 3000);
            });
        });
    }

    showWhatsAppNotification(ctaType) {
        if (!this.isEnabled) return;
        
        const message = window.translationLoader?.getTranslation(`cta.prefill.${ctaType}`) || '';
        const notificationText = window.translationLoader?.getTranslation('chat.notification_text') || 'Oder direkt per WhatsApp?';
        const buttonText = window.translationLoader?.getTranslation('chat.notification_button') || 'Jetzt chatten';
        
        const notification = document.createElement('div');
        notification.className = 'whatsapp-notification';
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fab fa-whatsapp"></i>
                <span>${notificationText}</span>
                <button onclick="window.chatIntegration.openWhatsApp('${message}'); this.closest('.whatsapp-notification').remove();">
                    ${buttonText}
                </button>
                <button class="close-btn" onclick="this.closest('.whatsapp-notification').remove();">×</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 8000);
    }
}

window.chatIntegration = new ChatIntegration();
