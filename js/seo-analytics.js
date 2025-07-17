class SEOAnalytics {
    constructor() {
        this.config = null;
        this.isEnabled = false;
    }

    async init() {
        try {
            const response = await fetch(`data/feature-flags.json?v=${window.CACHE_VERSION || Date.now()}`);
            const data = await response.json();
            this.config = data.features.seo_analytics;
            this.isEnabled = this.config && this.config.enabled;
            
            if (this.isEnabled) {
                this.initGoogleAnalytics();
                this.addStructuredData();
                this.updateSocialMediaTags();
                this.initPerformanceOptimizations();
                console.log('SEO Analytics: Enabled');
            }
        } catch (error) {
            console.error('Failed to load SEO analytics config:', error);
        }
    }

    initGoogleAnalytics() {
        if (!this.config.google_analytics.enabled) return;
        
        const trackingId = this.config.google_analytics.tracking_id;
        if (!trackingId || trackingId === 'G-XXXXXXXXXX') return;

        // Google Analytics 4
        const script1 = document.createElement('script');
        script1.async = true;
        script1.src = `https://www.googletagmanager.com/gtag/js?id=${trackingId}`;
        document.head.appendChild(script1);

        const script2 = document.createElement('script');
        script2.innerHTML = `
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '${trackingId}', {
                page_title: document.title,
                page_location: window.location.href
            });
        `;
        document.head.appendChild(script2);

        // Make gtag globally available
        window.gtag = window.gtag || function(){dataLayer.push(arguments);};
        
        // Track form submissions
        this.trackFormSubmissions();
        console.log('Google Analytics 4 initialized');
    }

    addStructuredData() {
        if (!this.config.structured_data.enabled) return;

        // Organization Schema
        const organizationSchema = {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": this.config.structured_data.organization_name,
            "url": "https://www.filo.cards",
            "logo": "https://www.filo.cards/images/rmclogo.png",
            "contactPoint": {
                "@type": "ContactPoint",
                "telephone": this.config.structured_data.contact_phone,
                "contactType": "customer service",
                "availableLanguage": ["German", "Turkish", "English"]
            },
            "areaServed": this.config.structured_data.service_area,
            "description": "Europäische Mobilitätslösungen - Tankkarten, Mautlösungen und Prepaid-Kreditkarten"
        };

        // Service Schema
        const serviceSchema = {
            "@context": "https://schema.org",
            "@type": "Service",
            "name": "Europäische Tankkarten und Mautlösungen",
            "provider": {
                "@type": "Organization",
                "name": this.config.structured_data.organization_name
            },
            "areaServed": this.config.structured_data.service_area,
            "serviceType": ["Fuel Cards", "Toll Solutions", "Prepaid Credit Cards"],
            "description": "Tankkarten für 19 Länder, Mautlösungen für 17 Länder, Prepaid-Kreditkarten ohne Bonitätsprüfung"
        };

        // FAQ Schema
        const faqSchema = {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [
                {
                    "@type": "Question",
                    "name": "Wo kann ich mit der Tankkarte tanken?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "An tausenden Tankstellen in ganz Europa. Unsere Tankkarte wird an allen großen Tankstellenketten sowie vielen freien Tankstellen akzeptiert."
                    }
                },
                {
                    "@type": "Question", 
                    "name": "In welchen Ländern funktioniert die Mautlösung?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Aktuell in 17 europäischen Ländern, von Österreich und Deutschland bis Schweden und Kroatien."
                    }
                }
            ]
        };

        // Add schemas to page
        [organizationSchema, serviceSchema, faqSchema].forEach(schema => {
            const script = document.createElement('script');
            script.type = 'application/ld+json';
            script.textContent = JSON.stringify(schema);
            document.head.appendChild(script);
        });

        console.log('Structured Data added');
    }

    updateSocialMediaTags() {
        if (!this.config.social_media.enabled) return;

        const currentLang = document.documentElement.lang || 'de';
        const title = document.title;
        const description = document.querySelector('meta[name="description"]')?.content || '';
        const ogImage = `https://www.filo.cards/${this.config.social_media.og_image}`;

        // Open Graph Tags
        this.addMetaTag('property', 'og:title', title);
        this.addMetaTag('property', 'og:description', description);
        this.addMetaTag('property', 'og:image', ogImage);
        this.addMetaTag('property', 'og:url', window.location.href);
        this.addMetaTag('property', 'og:type', 'website');
        this.addMetaTag('property', 'og:locale', currentLang === 'de' ? 'de_DE' : currentLang === 'tr' ? 'tr_TR' : 'en_US');
        this.addMetaTag('property', 'og:site_name', 'filo.cards');

        // Twitter Cards
        this.addMetaTag('name', 'twitter:card', 'summary_large_image');
        this.addMetaTag('name', 'twitter:title', title);
        this.addMetaTag('name', 'twitter:description', description);
        this.addMetaTag('name', 'twitter:image', ogImage);
        if (this.config.social_media.twitter_handle) {
            this.addMetaTag('name', 'twitter:site', this.config.social_media.twitter_handle);
        }

        console.log('Social Media tags updated');
    }

    addMetaTag(attribute, name, content) {
        const existing = document.querySelector(`meta[${attribute}="${name}"]`);
        if (existing) {
            existing.content = content;
        } else {
            const meta = document.createElement('meta');
            meta.setAttribute(attribute, name);
            meta.content = content;
            document.head.appendChild(meta);
        }
    }

    initPerformanceOptimizations() {
        if (this.config.performance.lazy_loading) {
            this.initLazyLoading();
        }
        
        if (this.config.performance.preload_fonts) {
            this.preloadFonts();
        }

        // Core Web Vitals tracking
        this.trackCoreWebVitals();
    }

    initLazyLoading() {
        // Lazy load images
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    preloadFonts() {
        const fontUrls = [
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-brands-400.woff2',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2'
        ];

        fontUrls.forEach(url => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = url;
            link.as = 'font';
            link.type = 'font/woff2';
            link.crossOrigin = 'anonymous';
            document.head.appendChild(link);
        });
    }

    trackCoreWebVitals() {
        // Track Largest Contentful Paint
        new PerformanceObserver((entryList) => {
            for (const entry of entryList.getEntries()) {
                if (window.gtag && entry.loadTime) {
                    gtag('event', 'LCP', {
                        event_category: 'Web Vitals',
                        value: Math.round(entry.loadTime),
                        non_interaction: true,
                    });
                }
            }
        }).observe({entryTypes: ['largest-contentful-paint']});

        // Track First Input Delay
        new PerformanceObserver((entryList) => {
            for (const entry of entryList.getEntries()) {
                if (window.gtag) {
                    gtag('event', 'FID', {
                        event_category: 'Web Vitals',
                        value: Math.round(entry.processingStart - entry.startTime),
                        non_interaction: true,
                    });
                }
            }
        }).observe({entryTypes: ['first-input']});
    }

    trackFormSubmissions() {
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'contactForm' && window.gtag) {
                gtag('event', 'form_submit', {
                    event_category: 'engagement',
                    event_label: 'contact_form'
                });
            }
        });
    }

    // CTA Button tracking
    trackCTAClicks() {
        document.querySelectorAll('.cta-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (window.gtag) {
                    const ctaType = btn.getAttribute('data-translate')?.split('.')[1] || 'unknown';
                    gtag('event', 'cta_click', {
                        event_category: 'engagement',
                        event_label: ctaType
                    });
                }
            });
        });
    }
}

window.seoAnalytics = new SEOAnalytics();
