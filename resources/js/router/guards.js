import { useAuthStore }     from '@/stores/auth'
import { useSettingsStore } from '@/stores/settings'

export async function setupGuards(router) {

    router.beforeEach(async (to, from) => {   // ← removed `next`
        const auth     = useAuthStore()
        const settings = useSettingsStore()

        if (!auth.initialized) {
            await auth.fetchUser()
        }

        if (to.meta.requiresTenant && auth.isLoggedIn && !settings.loaded) {
            await settings.fetchSettings()
        }

        if (to.meta.requiresSuperAdmin) {
            if (!auth.isLoggedIn)   return { name: 'login', query: { redirect: to.fullPath } }
            if (!auth.isSuperAdmin) return { name: 'dashboard' }
            return true
        }

        if (to.meta.requiresAuth && !auth.isLoggedIn) {
            return { name: 'login', query: { redirect: to.fullPath } }
        }

        if (to.meta.requiresTenant && auth.isSuperAdmin) {
            return { name: 'admin.dashboard' }
        }

        if (to.meta.module && !settings.isModuleEnabled(to.meta.module)) {
            return { name: 'module.locked', params: { module: to.meta.module } }
        }

        if (to.meta.permission && !auth.hasPermission(to.meta.permission)) {
            return { name: 'unauthorized' }
        }

        const guestOnly = new Set(['login','register','forgot-password','reset-password'])
        if (guestOnly.has(to.name) && auth.isLoggedIn) {
            return auth.isSuperAdmin ? { name: 'admin.dashboard' } : { name: 'dashboard' }
        }

        if (to.path === '/') {
            if (auth.isLoggedIn) return auth.isSuperAdmin ? { name: 'admin.dashboard' } : { name: 'dashboard' }
            return { name: 'login' }
        }

        return true   // ← replaces bare next()
    })

    // afterEach stays exactly the same — no changes needed
    router.afterEach((to) => {
        const appName = import.meta.env.VITE_APP_NAME ?? 'EasyHCS'
        const title   = to.meta?.title

        if (title) {
            document.title = `${title} — ${appName}`
        } else {
            const name = String(to.name ?? '')
                .replace(/^(admin\.|pharmacy\.|diagnostic\.|hospital\.|accounts\.)/, '')
                .replace(/[-_.]/g, ' ')
                .replace(/\b\w/g, c => c.toUpperCase())
                .trim()
            document.title = name ? `${name} — ${appName}` : appName
        }
    })
}