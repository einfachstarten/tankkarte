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

    // Update station finder links
    updateStationFinderLinks();

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

function updateStationFinderLinks() {
    const infoLink = document.querySelector('.finder-btn-info');
    if (infoLink) {
        const baseUrl = 'https://www.rmc-service.com';
        let langPath = '/de/tankstellenfinder/tankstellennetz';

        switch(currentLanguage) {
            case 'en':
                langPath = '/en/station-finder/station-network';
                break;
            case 'tr':
                langPath = '/tr/istasyon-bulucu/istasyon-agi';
                break;
            default:
                langPath = '/de/tankstellenfinder/tankstellennetz';
        }

        infoLink.href = baseUrl + langPath;
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

// Mobile Menu Toggle
function toggleMobileMenu() {
    const navLinks = document.querySelector('.nav-links');
    navLinks.classList.toggle('mobile-active');
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const nav = document.querySelector('.nav');
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');

    if (!nav.contains(event.target) && navLinks.classList.contains('mobile-active')) {
        navLinks.classList.remove('mobile-active');
    }
});

// Close mobile menu when clicking on nav links
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', function() {
            const navLinks = document.querySelector('.nav-links');
            navLinks.classList.remove('mobile-active');
        });
    });
});

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
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Wird gesendet...';
            submitBtn.disabled = true;

            document.getElementById('formLanguage').value = currentLanguage;
            const formData = new FormData(contactForm);

            try {
                const response = await fetch('contact.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    const successMsg = window.translationLoader.getTranslation('contact.success') || result.message;
                    alert(successMsg);
                    contactForm.reset();
                } else {
                    const errorMsg = result.errors ? result.errors.join('\n') : result.message;
                    alert('Fehler: ' + errorMsg);
                }
            } catch (error) {
                console.error('Form submission error:', error);
                alert('Netzwerkfehler. Bitte versuchen Sie es später erneut.');
            } finally {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
    }
});


// CTA Button tracking and form pre-fill
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.cta-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Get CTA context from translation key
            const translationKey = this.getAttribute('data-translate');
            const ctaType = translationKey ? translationKey.split('.')[1] : 'consultation';

            // Pre-fill form message based on CTA using translation system
            setTimeout(() => {
                const messageField = document.getElementById('message');
                if (messageField && !messageField.value && window.translationLoader) {
                    const prefillKey = `cta.prefill.${ctaType}`;
                    const preMessage = window.translationLoader.getTranslation(prefillKey, currentLanguage);

                    // Only set if translation exists and is not the key itself
                    if (preMessage && preMessage !== prefillKey) {
                        messageField.value = preMessage;

                        // Add visual feedback
                        messageField.style.backgroundColor = '#f0f8ff';
                        setTimeout(() => {
                            messageField.style.backgroundColor = '';
                        }, 1000);
                    }
                }
            }, 500);
        });
    });
});

// Track Station Finder clicks
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.finder-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const btnType = this.classList.contains('finder-btn-web') ? 'web' :
                           this.classList.contains('finder-btn-android') ? 'android' :
                           this.classList.contains('finder-btn-ios') ? 'ios' : 'info';

            // Log for debugging
            console.log('Station Finder clicked:', btnType, this.href);

            // Add analytics tracking here if needed
            // gtag('event', 'station_finder_click', { button_type: btnType });
        });
    });
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
window.toggleMobileMenu = toggleMobileMenu;

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

// Debug function to test pre-fill messages
window.testPrefill = function() {
    console.log('Testing CTA prefill messages:');
    const types = ['fuel_card', 'credit_card', 'toll_solution', 'consultation'];

    types.forEach(type => {
        const key = `cta.prefill.${type}`;
        const message = window.translationLoader.getTranslation(key, currentLanguage);
        console.log(`${type}:`, message);
    });
};
// Generate worldmap cells with random animation delays
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.worldmap-container');
    if (container) {
        const northAmerica = [19,20,21,73,74,75,76,77,123,124,127,128,129,130,131,132,177,178,179,181,182,183,184,185,186,187,188,228,229,230,231,232,233,234,237,238,239,240,241,242,243,283,284,285,286,287,288,289,293,294,295,296,297,332,333,334,337,339,340,341,342,343,348,349,350,351,352,387,388,389,390,391,392,393,394,395,396,397,398,399,404,405,406,407,441,442,443,444,445,446,447,448,449,450,451,452,453,454,455,459,460,497,498,499,500,501,502,503,504,505,506,507,509,510,511,514,552,553,554,555,556,557,558,559,560,561,562,564,565,566,607,610,611,612,613,614,615,616,619,620,621,622,666,667,668,669,670,671,672,674,675,676,722,723,724,725,726,727,728,729,730,731,778,779,780,781,782,783,784,785,786,834,835,836,837,838,839,889,890,891,892,893,894,944,945,946,947,948,949,1000,1001,1002,1005,1056,1057,1112,1113,1114,1169];
        const southAmerica = [1225,1226,1227,1228,1229,1281,1282,1283,1284,1285,1335,1336,1337,1338,1339,1340,1341,1342,1390,1391,1392,1393,1394,1395,1396,1397,1446,1447,1448,1449,1450,1451,1502,1503,1504,1505,1506,1557,1558,1559,1560,1612,1613,1614,1666,1667,1668,1721,1722,1776,1777,1831,1832,1887];
        const europe = [361,362,415,416,417,469,470,471,472,523,524,525,527,582,580,579,578,632,634,636,631,311,312,251,519,520,686,687,689,688,690,691,692,637,638,583,528,473,418,474,475,365,421,476,477,531,530,529,584,585,586,639,640,641,693,694,695,696,795,796,850,851,797,742,743,744,745,746,747,748,749,750,751,805,804,803,802,801,799,798,800,854,856,857,911];
        const africa = [961,962,963,965,1020,1021,1019,1018,1017,1016,1015,1069,1070,1071,1072,1073,1074,1075,1076,1077,1124,1125,1126,1127,1128,1129,1130,1131,1132,1133,1179,1180,1181,1182,1183,1184,1185,1186,1187,1188,1189,1235,1236,1237,1238,1239,1240,1241,1242,1243,1244,1293,1294,1295,1296,1297,1298,1348,1349,1350,1351,1352,1404,1405,1406,1407,1459,1460,1461,1462,1514,1515,1516,1518,1573,1569,1570,1571,1624,1625];
        const asia = [154,208,209,210,262,263,264,265,314,315,316,317,318,319,320,321,322,323,325,326,369,370,371,372,373,374,375,376,377,378,379,380,381,382,423,424,425,426,427,428,429,430,431,432,433,434,435,436,437,438,439,440,478,479,480,481,482,483,484,485,486,487,488,489,490,491,492,493,494,495,532,533,534,535,536,537,538,539,540,541,542,543,544,545,546,547,548,549,550,587,588,589,590,591,592,593,594,595,596,597,598,599,600,601,603,642,643,644,645,646,647,648,649,650,651,652,653,654,657,658,697,698,699,700,701,702,703,704,705,706,707,708,709,712,752,753,754,755,756,757,758,759,760,761,762,763,764,807,808,809,810,811,812,813,814,815,816,817,818,819,821,859,860,861,862,863,864,865,866,867,868,869,870,871,872,873,876,913,914,916,917,918,919,920,921,922,923,924,925,926,928,930,967,968,969,970,972,973,974,975,976,977,978,979,980,981,982,1022,1023,1024,1025,1026,1027,1029,1030,1031,1034,1035,1036,1037,1079,1080,1081,1085,1089,1090,1135,1144,1145,1200,1202,1203,1255,1256,1257,1311];
        const australia = [1206,1262,1263,1317,1369,1370,1372,1422,1423,1424,1425,1426,1427,1428,1476,1477,1478,1479,1480,1481,1482,1483,1531,1532,1533,1534,1535,1536,1537,1538,1586,1587,1590,1591,1592,1645,1646,1650,1705];

        // Alle Zellen generieren
        for (let i = 0; i < 35 * 55; i++) {
            const cell = document.createElement('div');
            cell.className = 'worldmap-cell';
            container.appendChild(cell);
        }

        // Kontinente mit Farben und Animationen versehen
        const allCells = container.querySelectorAll('.worldmap-cell');

        // Nordamerika - Primary Yellow
        northAmerica.forEach(index => {
            const cell = allCells[index - 1];
            if (cell) {
                cell.classList.add('north-america');
                cell.style.animationDelay = `-${Math.random() * 4000}ms`;
            }
        });

        // S\xC3\xBCdamerika - Dark Green
        southAmerica.forEach(index => {
            const cell = allCells[index - 1];
            if (cell) {
                cell.classList.add('south-america');
                cell.style.animationDelay = `-${Math.random() * 3000}ms`;
            }
        });

        // Europa - Primary Green
        europe.forEach(index => {
            const cell = allCells[index - 1];
            if (cell) {
                cell.classList.add('europe');
                cell.style.animationDelay = `-${Math.random() * 3000}ms`;
            }
        });

        // Afrika - Medium Gray
        africa.forEach(index => {
            const cell = allCells[index - 1];
            if (cell) {
                cell.classList.add('africa');
                cell.style.animationDelay = `-${Math.random() * 4000}ms`;
            }
        });

        // Asien - Light Green
        asia.forEach(index => {
            const cell = allCells[index - 1];
            if (cell) {
                cell.classList.add('asia');
                cell.style.animationDelay = `-${Math.random() * 5000}ms`;
            }
        });

        // Australien - Dark Gray
        australia.forEach(index => {
            const cell = allCells[index - 1];
            if (cell) {
                cell.classList.add('australia');
                cell.style.animationDelay = `-${Math.random() * 2000}ms`;
            }
        });
    }
});
