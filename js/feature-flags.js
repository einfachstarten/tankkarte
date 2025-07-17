class FeatureFlags {
    constructor() {
        this.flags = {};
        this.loaded = false;
    }

    async load() {
        try {
            const response = await fetch(`data/feature-flags.json?v=${window.CACHE_VERSION || Date.now()}`);
            const data = await response.json();
            this.flags = data.features;
            this.loaded = true;
            return true;
        } catch (error) {
            console.error('Failed to load feature flags:', error);
            this.loaded = false;
            return false;
        }
    }

    isEnabled(feature) {
        if (!this.loaded) return false;
        return this.flags[feature]?.enabled || false;
    }

    getConfig(feature) {
        if (!this.loaded) return null;
        return this.flags[feature] || null;
    }
}

window.featureFlags = new FeatureFlags();
