import { defineStore } from 'pinia'
import axios from 'axios'

export const useSettingsStore = defineStore('settings', {

    // ─────────────────────────────────────────────────────────────────────────
    // STATE
    // ─────────────────────────────────────────────────────────────────────────
    state: () => ({
        /**
         * Modules currently enabled for this tenant.
         * 'core' and 'accounts' are always present.
         */
        modulesEnabled  : ['core', 'accounts'],

        /**
         * Context-B patient payment methods allowed in this tenant.
         * e.g. ['cash', 'bkash', 'nagad', 'card', 'insurance', 'credit']
         */
        paymentMethods  : ['cash'],

        /** Flat key-value config bag from tenant settings table */
        tenantConfig    : {},

        /** True once fetchSettings() has resolved */
        loaded          : false,
    }),

    // ─────────────────────────────────────────────────────────────────────────
    // GETTERS
    // ─────────────────────────────────────────────────────────────────────────
    getters: {
        /**
         * Check if a given module is active for this tenant.
         * 'core' and 'accounts' always return true.
         */
        isModuleEnabled : (state) => (module) => {
            if (module === 'core' || module === 'accounts') return true
            return state.modulesEnabled.includes(module)
        },

        /** Display name of the tenant */
        tenantName      : (state) => state.tenantConfig.name      ?? 'EasyHCS',

        /** ISO 4217 currency code — default BDT for Bangladesh */
        tenantCurrency  : (state) => state.tenantConfig.currency  ?? 'BDT',

        /** IANA timezone — default Dhaka */
        tenantTimezone  : (state) => state.tenantConfig.timezone  ?? 'Asia/Dhaka',

        /** Tenant logo URL (null = show text initials) */
        tenantLogo      : (state) => state.tenantConfig.logo      ?? null,

        /** Plan name e.g. "Pharmacy", "Diagnostic", "Hospital", "Full" */
        currentPlan     : (state) => state.tenantConfig.plan      ?? null,

        /** Plan expiry date string */
        planExpiresAt   : (state) => state.tenantConfig.plan_expires_at ?? null,

        /** 'active' | 'trialing' | 'suspended' | 'expired' | null */
        planStatus      : (state) => state.tenantConfig.plan_status ?? null,

        /** True when plan_status is active or trialing */
        isPlanActive : (state) =>
            ['active', 'trialing'].includes(state.tenantConfig.plan_status),

        /** Trial end date string */
        trialEndsAt : (state) => state.tenantConfig.trial_ends_at ?? null,

        /** Tenant address for PDF headers */
        tenantAddress : (state) => state.tenantConfig.address ?? '',

        /** Tenant phone for PDF headers */
        tenantPhone : (state) => state.tenantConfig.phone ?? '',

        /** Decimal separator preference */
        numberFormat : (state) => state.tenantConfig.number_format ?? 'en-BD',

        /**
         * Format a monetary amount using the tenant's currency and locale.
         */
        formatCurrency : (state) => (amount, currency = null) => {
            const curr   = currency ?? (state.tenantConfig.currency ?? 'BDT')
            const locale = state.tenantConfig.number_format ?? 'en-BD'
            return new Intl.NumberFormat(locale, {
                style                : 'currency',
                currency             : curr,
                minimumFractionDigits: 2,
            }).format(amount ?? 0)
        },
    },

    // ─────────────────────────────────────────────────────────────────────────
    // ACTIONS
    // ─────────────────────────────────────────────────────────────────────────
    actions: {
        /**
         * Load all tenant settings from the API.
         * Called once by the router guard when entering the tenant panel.
         */
        async fetchSettings() {
            try {
                const { data } = await axios.get('/api/settings')
                this.modulesEnabled = data.modules_enabled ?? ['core', 'accounts']
                this.paymentMethods = data.payment_methods ?? ['cash']
                this.tenantConfig   = data.config          ?? {}
                this.loaded = true
            } catch (_) {
                // Keep defaults — do not crash app if settings fail
                this.loaded = true
            }
        },

        /**
         * Update a single setting key on the server and in local state.
         * Used by the Settings page.
         */
        async updateSetting(key, value) {
            await axios.patch('/api/settings', { key, value })
            this.tenantConfig = { ...this.tenantConfig, [key]: value }
        },

        /**
         * Bulk-update settings object.
         * Used when saving the entire settings form in one request.
         */
        async updateSettings(payload = {}) {
            const { data } = await axios.put('/api/settings', payload)
            this.tenantConfig = { ...this.tenantConfig, ...data.config ?? {} }
            if (data.modules_enabled) this.modulesEnabled = data.modules_enabled
            if (data.payment_methods) this.paymentMethods = data.payment_methods
        },

        /**
         * Force reload settings (e.g. after plan upgrade).
         */
        async reloadSettings() {
            this.loaded = false
            await this.fetchSettings()
        },
    },
})