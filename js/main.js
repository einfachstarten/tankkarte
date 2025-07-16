// Translation System Main
let currentLanguage = 'de';
let isTranslationsLoaded = false;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', async function() {
    console.log('DOM loaded, initializing translations...');

    // Load translations
    const loaded = await window.translationLoader.loadTranslations();

    if (loaded) {
        isTranslationsLoaded = true;

        // Set default language
        await switchLanguage('de');

        // Hide loading spinner and show content
        document.getElementById('loading-spinner').style.display = 'none';
        document.getElementById('main-content').style.opacity = '1';

        console.log('Website fully loaded and translated');
    } else {
        console.error('Failed to load translations');
        // Show error message or fallback content
        document.getElementById('loading-spinner').innerHTML = '<p style="color: red;">Error loading translations</p>';
    }
});

// Switch Language Function
async function switchLanguage(lang) {
    if (!isTranslationsLoaded) {
        console.warn('Translations not loaded yet');
        return;
    }

    console.log(`Switching to language: ${lang}`);

    currentLanguage = lang;
    window.translationLoader.setLanguage(lang);

    // Update HTML lang attribute
    document.documentElement.lang = lang;

    // Update page meta data
    updatePageMeta(lang);

    // Translate all elements
    translateAllElements();

    // Update language buttons
    updateLanguageButtons(lang);

    // Update fuel card text
    updateFuelCardText(lang);

    // Handle special cases
    handleSpecialTranslations(lang);

    console.log(`Language switched to: ${lang}`);
}

function updatePageMeta(lang) {
    const title = window.translationLoader.getTranslation('page.title', lang);
    const description = window.translationLoader.getTranslation('page.description', lang);

    document.title = title;

    // Update HTML lang attribute manually
    document.documentElement.lang = lang;

    // Update meta description
    const metaDesc = document.querySelector('meta[name="description"]');
    if (metaDesc) {
        metaDesc.setAttribute('content', description);
    }
}

function translateAllElements() {
    // Standard translations
    document.querySelectorAll('[data-translate]').forEach(element => {
        const key = element.getAttribute('data-translate');
        const translation = window.translationLoader.getTranslation(key);

        if (translation && translation !== key) {
            if (element.tagName === 'IMG') {
                element.setAttribute('alt', translation);
            } else {
                element.textContent = translation;
                if (key.includes('subtitle') && translation.includes('<br>')) {
                    element.innerHTML = translation;
                }
            }
        }
    });

    // List translations (for countries, features, etc.)
    document.querySelectorAll('[data-translate-list]').forEach(element => {
        const key = element.getAttribute('data-translate-list');
        const listItems = window.translationLoader.getTranslation(key);

        if (Array.isArray(listItems)) {
            if (element.classList.contains('countries-list')) {
                // Dezente Country-Liste
                element.innerHTML = listItems.map(country =>
                    `<div class="country-item">${country}</div>`
                ).join('');
            } else if (element.classList.contains('target-features')) {
                // Feature lists
                element.innerHTML = listItems.map(feature => 
                    `<li>${feature}</li>`
                ).join('');
            }
        }
    });

    // Paragraph translations (for descriptions with multiple paragraphs)
    document.querySelectorAll('[data-translate-paragraphs]').forEach(element => {
        const key = element.getAttribute('data-translate-paragraphs');
        const paragraphs = window.translationLoader.getTranslation(key);

        if (Array.isArray(paragraphs)) {
            element.innerHTML = paragraphs.map(p => `<p>${p}</p>`).join('');
        }
    });
}

function updateLanguageButtons(activeLang) {
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.classList.remove('active');

        const btnLang = btn.getAttribute('onclick').match(/'(\w+)'/)[1];
        if (btnLang === activeLang) {
            btn.classList.add('active');
        }
    });
}

function updateFuelCardText(lang) {
    const cardText = window.translationLoader.getTranslation('hero.cardText', lang);
    const cardElement = document.querySelector('.fuel-card');

    if (cardElement && cardText) {
        cardElement.style.setProperty('--card-text', `"${cardText}"`);
    }
}

function handleSpecialTranslations(lang) {
    // Add language-specific FAQs
    const languageSpecificFaqs = document.getElementById('language-specific-faqs');
    if (!languageSpecificFaqs) {
        console.warn('language-specific-faqs element not found');
        return;
    }
    languageSpecificFaqs.innerHTML = '';

    if (lang === 'en') {
        // Add Brexit FAQ for English
        const brexitFaq = document.createElement('div');
        brexitFaq.className = 'faq-item';
        brexitFaq.innerHTML = `
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>${window.translationLoader.getTranslation('faq.q8.question', lang)}</span>
                <span class="faq-icon">+</span>
            </button>
            <div class="faq-answer">${window.translationLoader.getTranslation('faq.q8.answer', lang)}</div>
        `;
        languageSpecificFaqs.appendChild(brexitFaq);

        // Add Telematics FAQ
        const telematicsFaq = document.createElement('div');
        telematicsFaq.className = 'faq-item';
        telematicsFaq.innerHTML = `
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>${window.translationLoader.getTranslation('faq.q9.question', lang)}</span>
                <span class="faq-icon">+</span>
            </button>
            <div class="faq-answer">${window.translationLoader.getTranslation('faq.q9.answer', lang)}</div>
        `;
        languageSpecificFaqs.appendChild(telematicsFaq);
    }

    if (lang === 'tr') {
        // Add Turkey-specific FAQs
        const turkeyFaq = document.createElement('div');
        turkeyFaq.className = 'faq-item';
        turkeyFaq.innerHTML = `
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>${window.translationLoader.getTranslation('faq.q8.question', lang)}</span>
                <span class="faq-icon">+</span>
            </button>
            <div class="faq-answer">${window.translationLoader.getTranslation('faq.q8.answer', lang)}</div>
        `;
        languageSpecificFaqs.appendChild(turkeyFaq);

        // Add TIR FAQ
        const tirFaq = document.createElement('div');
        tirFaq.className = 'faq-item';
        tirFaq.innerHTML = `
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>${window.translationLoader.getTranslation('faq.q9.question', lang)}</span>
                <span class="faq-icon">+</span>
            </button>
            <div class="faq-answer">${window.translationLoader.getTranslation('faq.q9.answer', lang)}</div>
        `;
        languageSpecificFaqs.appendChild(tirFaq);
    }
}

// FAQ Toggle Function
function toggleFaq(button) {
    const answer = button.nextElementSibling;
    const icon = button.querySelector('.faq-icon');
    const isOpen = answer.classList.contains('open');

    if (isOpen) {
        answer.style.maxHeight = null;
        answer.classList.remove('open');
        icon.textContent = '+';
    } else {
        answer.classList.add('open');
        answer.style.maxHeight = answer.scrollHeight + 'px';
        icon.textContent = '−';
    }
}

// Contact Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const form = e.target;
            const data = new FormData(form);
            const subject = 'Kontaktformular';
            const bodyLines = [
                `Name: ${data.get('name')}`,
                `Firma: ${data.get('firma')}`,
                `E-Mail: ${data.get('email')}`,
                `Telefon: ${data.get('telefon')}`,
                `Nachricht: ${data.get('nachricht') || ''}`
            ];
            const mailtoLink = `mailto:o.gokceviran@rmc-service.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(bodyLines.join('\n'))}`;
            window.location.href = mailtoLink;

            const alertMessage = window.translationLoader.getTranslation('contact.alert');
            alert(alertMessage);
            form.reset();
        });
    }
});

// Scroll Effects
window.addEventListener('scroll', function() {
    const nav = document.querySelector('.nav');
    const scrolled = window.scrollY;

    if (scrolled > 100) {
        nav.style.background = 'rgba(255, 255, 255, 0.98)';
        nav.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
    } else {
        nav.style.background = 'rgba(255, 255, 255, 0.95)';
        nav.style.boxShadow = 'none';
    }

    const reveals = document.querySelectorAll('.scroll-reveal');
    reveals.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const elementVisible = 150;

        if (elementTop < window.innerHeight - elementVisible) {
            element.classList.add('revealed');
        }
    });
});

// Smooth scroll for navigation links
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Make functions globally available
window.switchLanguage = switchLanguage;
window.toggleFaq = toggleFaq;

// Debug function
window.debugTranslations = function() {
    console.log('Current language:', currentLanguage);
    console.log('Available languages:', window.translationLoader.getAvailableLanguages());
    console.log('Translations loaded:', isTranslationsLoaded);

    document.querySelectorAll('[data-translate]').forEach(el => {
        const key = el.getAttribute('data-translate');
        console.log(`${key}: "${el.textContent}"`);
    });
};

// Generate worldmap cells after DOM load
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.worldmap-container');
    if (container) {
        for (let i = 0; i < 35 * 55; i++) {
            const cell = document.createElement('div');
            cell.className = 'worldmap-cell';
            container.appendChild(cell);
        }
    }
});
