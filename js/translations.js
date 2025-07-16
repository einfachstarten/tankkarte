// Übersetzungs-Loader
class TranslationLoader {
    constructor() {
        this.translations = null;
        this.currentLanguage = 'de';
        this.fallbackLanguage = 'de';
        this.isLoaded = false;
    }

    async loadTranslations() {
        try {
            console.log('Loading translations...');

            // Cache-Busting für JSON-Datei
            const cacheVersion = window.CACHE_VERSION || Date.now();
            const response = await fetch(`data/translations.json?v=${cacheVersion}`);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            this.translations = data.translations;
            this.isLoaded = true;

            console.log('Translations loaded successfully');
            return true;
        } catch (error) {
            console.error('Failed to load translations:', error);
            this.isLoaded = false;
            return false;
        }
    }

    getTranslation(key, language = this.currentLanguage) {
        if (!this.isLoaded || !this.translations) {
            console.warn('Translations not loaded yet');
            return key;
        }

        const keys = key.split('.');
        let translation = this.translations[language];

        // Navigate through nested object
        for (const k of keys) {
            if (translation && typeof translation === 'object' && k in translation) {
                translation = translation[k];
            } else {
                // Fallback to default language
                if (language !== this.fallbackLanguage) {
                    console.warn(`Translation not found for key: ${key} in language: ${language}, trying fallback`);
                    return this.getTranslation(key, this.fallbackLanguage);
                }
                console.warn(`Translation not found for key: ${key}`);
                return key; // Return key as fallback
            }
        }

        return translation;
    }

    getAllTranslations(language = this.currentLanguage) {
        if (!this.isLoaded || !this.translations) {
            return {};
        }
        return this.translations[language] || {};
    }

    setLanguage(language) {
        this.currentLanguage = language;
    }

    getAvailableLanguages() {
        if (!this.isLoaded || !this.translations) {
            return ['de'];
        }
        return Object.keys(this.translations);
    }
}

// Global instance
window.translationLoader = new TranslationLoader();
