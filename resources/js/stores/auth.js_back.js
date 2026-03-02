import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {

    // ─────────────────────────────────────────────────────────────────────────
    // STATE
    // ─────────────────────────────────────────────────────────────────────────
    state: () => ({
        user        : null,
        permissions : [],
        roles       : [],

        /**
         * Set to true once fetchUser() has resolved (success OR 401).
         * Guards must wait for this before making redirect decisions.
         * Without this, a page reload always redirects to /login briefly.
         */
        initialized : false,

        /**
         * Tracks in-flight boot fetch so multiple guard calls don't
         * race-condition to fetchUser() at the same time.
         */
        _bootPromise : null,
    }),

    // ─────────────────────────────────────────────────────────────────────────
    // GETTERS
    // ─────────────────────────────────────────────────────────────────────────
    getters: {
        /** True when a user object is present in state */
        isLoggedIn : (state) => !!state.user,

        /** True when user.is_super_admin === true (system-level only) */
        isSuperAdmin : (state) => !!state.user?.is_super_admin,

        /** True when the user holds the tenant-owner role */
        isTenantOwner : (state) => state.roles.includes('tenant-owner'),

        /** Check a single role */
        hasRole : (state) => (name) => state.roles.includes(name),

        /**
         * Check a single permission.
         * Super-admin bypasses all permission checks.
         * Tenant-owner is handled on the backend; frontend
         * mirrors it by checking the permissions array.
         */
        hasPermission : (state) => (permission) =>
            !!state.user?.is_super_admin ||
            state.permissions.includes(permission),

        /** Check if user holds ANY of the given permissions */
        hasAnyPermission : (state) => (permList = []) =>
            !!state.user?.is_super_admin ||
            permList.some(p => state.permissions.includes(p)),

        /** Check if user holds ALL of the given permissions */
        hasAllPermissions : (state) => (permList = []) =>
            !!state.user?.is_super_admin ||
            permList.every(p => state.permissions.includes(p)),

        // Purchase-request approval shortcuts
        canApproveStep1 : (state) =>
            state.permissions.includes('pr-approve-step1'),
        canApproveStep2 : (state) =>
            state.permissions.includes('pr-approve-step2'),

        // 2FA flag
        twoFactorEnabled : (state) => !!state.user?.two_factor_enabled,

        // Redirect target after login based on role
        homeRoute : (state) => {
            if (!state.user) return { name: 'login' }
            return state.user.is_super_admin
                ? { name: 'admin.dashboard' }
                : { name: 'dashboard' }
        },
    },

    // ─────────────────────────────────────────────────────────────────────────
    // ACTIONS
    // ─────────────────────────────────────────────────────────────────────────
    actions: {

        // ── LOGIN ─────────────────────────────────────────────────────────────
        /**
         * Perform Sanctum SPA login.
         * Always fetches CSRF cookie first, then posts credentials.
         *
         * Returns:
         *   { two_factor_required: true }            — caller redirects to /two-factor
         *   { user: {...}, redirect: '/dashboard' }  — login success
         *
         * Throws on validation error (422) — caller shows field errors.
         * Throws on credentials error (401/419) — caller shows message.
         */
  async login(credentials) {
    await axios.get('/sanctum/csrf-cookie')
    const { data } = await axios.post('/api/login', credentials)
    if (data.two_factor_required) return data
    this.user = data.user
    this.initialized = true          // ← add this
    await this.refreshPermissions()
    await axios.get('/sanctum/csrf-cookie')  // ← refresh again after login
    return data
},

        // ── TWO-FACTOR AUTHENTICATION ─────────────────────────────────────────
        /**
         * Verify a TOTP/recovery code after login with 2FA enabled.
         * Expects the server to return { user: {...} } on success.
         */
        async verifyTwoFactor(code, isRecovery = false) {
            const payload = isRecovery
                ? { recovery_code: code }
                : { code }

            const { data } = await axios.post('/two-factor-challenge', payload)
            this.user = data.user
            await this.refreshPermissions()
            return data
        },

        /**
         * Resend 2FA notification (SMS / email OTP flows).
         */
        async resendTwoFactor() {
            await axios.post('/two-factor-challenge/resend')
        },

        /**
         * Enable 2FA for the current user.
         * After this call, use getQrCode() and confirmTwoFactor().
         */
        async enableTwoFactor() {
            await axios.post('/user/two-factor-authentication')
            if (this.user) this.user.two_factor_enabled = true
        },

        /**
         * Retrieve the SVG QR code for authenticator app setup.
         * Returns the SVG string directly.
         */
        async getTwoFactorQrCode() {
            const { data } = await axios.get('/user/two-factor-qr-code')
            return data.svg
        },

        /**
         * Retrieve setup keys for manual entry.
         */
        async getTwoFactorSecretKey() {
            const { data } = await axios.get('/user/two-factor-secret-key')
            return data.secretKey
        },

        /**
         * Retrieve one-time recovery codes.
         */
        async getTwoFactorRecoveryCodes() {
            const { data } = await axios.get('/user/two-factor-recovery-codes')
            return data
        },

        /**
         * Confirm 2FA is set up correctly by verifying the first code.
         */
        async confirmTwoFactor(code) {
            await axios.post('/user/confirmed-two-factor-authentication', { code })
        },

        /**
         * Disable 2FA — requires current password confirmation.
         */
        async disableTwoFactor(password) {
            await axios.delete('/user/two-factor-authentication', {
                data: { password },
            })
            if (this.user) this.user.two_factor_enabled = false
        },

        // ── LOGOUT ────────────────────────────────────────────────────────────
        /**
         * Log out the current user.
         * Resets state regardless of whether the server call succeeds.
         */
        async logout() {
            try {
               await axios.post('/api/logout')
            } catch (_) {
                // Ignore errors — we reset regardless
            } finally {
                this.$reset()
                // Ensure initialized stays true after reset
                // so the router guard doesn't re-fetch on next navigation
                this.initialized = true
            }
        },

        // ── SESSION BOOT ──────────────────────────────────────────────────────
        /**
         * Called ONCE on app mount to restore an existing session.
         * Sets initialized = true whether the user is logged in or not.
         *
         * Uses _bootPromise to prevent parallel calls from multiple
         * guard invocations during the first navigation.
         */
        async fetchUser() {
            // Return existing promise if already in flight
            if (this._bootPromise) return this._bootPromise

            this._bootPromise = (async () => {
                try {
                    const { data } = await axios.get('/api/user')
                    this.user = data
                    await this.refreshPermissions()
                } catch (err) {
                    // 401 = no session, clear state quietly
                    // Any other error = treat as unauthenticated
                    this.user        = null
                    this.permissions = []
                    this.roles       = []
                } finally {
                    this.initialized  = true
                    this._bootPromise = null
                }
            })()

            return this._bootPromise
        },

        // ── PERMISSIONS ───────────────────────────────────────────────────────
        /**
         * Refresh roles and permissions from the server.
         * Called after login, 2FA verify, and role changes.
         */
        async refreshPermissions() {
            if (!this.user) return
            try {
                const { data } = await axios.get('/api/user/permissions')
                this.permissions = data.permissions ?? []
                this.roles       = data.roles       ?? []
            } catch (_) {
                this.permissions = []
                this.roles       = []
            }
        },

        // ── PROFILE ───────────────────────────────────────────────────────────
        /**
         * Update the user's profile (name, email).
         */
        async updateProfile(payload) {
            const { data } = await axios.patch('/api/user/profile', payload)
            this.user = { ...this.user, ...data.user }
            return data
        },

        /**
         * Update the user's password.
         */
        async updatePassword(payload) {
            await axios.put('/user/password', payload)
        },

        // ── HELPERS ───────────────────────────────────────────────────────────
        /**
         * Returns true if the user object has the given attribute.
         * Useful for feature flags stored on the user model.
         */
        userCan(ability) {
            return this.hasPermission(ability)
        },
    },
})