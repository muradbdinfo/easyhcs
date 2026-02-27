import { useAuthStore }     from '@/stores/auth'
import { useSettingsStore } from '@/stores/settings'

/**
 * Attach all navigation guards to the router.
 * Must be called AFTER pinia.use() in app.js.
 *
 * Guard execution order:
 *   1. Boot (fetchUser once per app lifetime)
 *   2. requiresSuperAdmin
 *   3. requiresAuth
 *   4. Prevent super-admin from entering tenant panel
 *   5. meta.module — module enabled check
 *   6. meta.permission — spatie permission check
 *   7. Guest-only pages (redirect logged-in users away)
 */
export async function setupGuards(router) {

    router.beforeEach(async (to, from, next) => {
        const auth     = useAuthStore()
        const settings = useSettingsStore()

        // ────────────────────────────────────────────────────────────────────
        // STEP 1 — Boot guard
        // On every page load (including hard refresh), fetchUser() must resolve
        // before guards make any redirect decision.
        // _bootPromise prevents duplicate concurrent calls.
        // ────────────────────────────────────────────────────────────────────
        if (!auth.initialized) {
            await auth.fetchUser()
        }

        // ────────────────────────────────────────────────────────────────────
        // STEP 2 — Lazy load tenant settings
        // Only fetch once, only for tenant panel routes.
        // ────────────────────────────────────────────────────────────────────
        if (to.meta.requiresTenant && auth.isLoggedIn && !settings.loaded) {
            await settings.fetchSettings()
        }

        // ────────────────────────────────────────────────────────────────────
        // STEP 3 — Super-admin routes
        // Must be: authenticated AND is_super_admin === true
        // ────────────────────────────────────────────────────────────────────
        if (to.meta.requiresSuperAdmin) {
            if (!auth.isLoggedIn) {
                return next({
                    name  : 'login',
                    query : { redirect: to.fullPath },
                })
            }
            if (!auth.isSuperAdmin) {
                // Logged-in tenant user trying to reach /admin — go home
                return next({ name: 'dashboard' })
            }
            return next()
        }

        // ────────────────────────────────────────────────────────────────────
        // STEP 4 — Auth required (tenant + any authenticated route)
        // ────────────────────────────────────────────────────────────────────
        if (to.meta.requiresAuth) {
            if (!auth.isLoggedIn) {
                return next({
                    name  : 'login',
                    query : { redirect: to.fullPath },
                })
            }
        }

        // ────────────────────────────────────────────────────────────────────
        // STEP 5 — Prevent super-admin from accessing tenant panel
        // Super-admin has no tenant context — send to their own dashboard.
        // ────────────────────────────────────────────────────────────────────
        if (to.meta.requiresTenant && auth.isSuperAdmin) {
            return next({ name: 'admin.dashboard' })
        }

        // ────────────────────────────────────────────────────────────────────
        // STEP 6 — Module enabled check
        // 'core' and 'accounts' are always enabled (see settings getter).
        // ────────────────────────────────────────────────────────────────────
        if (to.meta.module) {
            const enabled = settings.isModuleEnabled(to.meta.module)
            if (!enabled) {
                return next({
                    name   : 'module.locked',
                    params : { module: to.meta.module },
                })
            }
        }

        // ────────────────────────────────────────────────────────────────────
        // STEP 7 — Permission check
        // Checks against the user's spatie permissions array.
        // Super-admin bypasses automatically (see hasPermission getter).
        // ────────────────────────────────────────────────────────────────────
        if (to.meta.permission) {
            if (!auth.hasPermission(to.meta.permission)) {
                return next({ name: 'unauthorized' })
            }
        }

        // ────────────────────────────────────────────────────────────────────
        // STEP 8 — Redirect already-authenticated users away from guest pages
        // Prevents visiting /login, /register when already logged in.
        // ────────────────────────────────────────────────────────────────────
        const guestOnlyRoutes = new Set([
            'login',
            'register',
            'forgot-password',
            'reset-password',
        ])

        if (guestOnlyRoutes.has(to.name) && auth.isLoggedIn) {
            return next(auth.isSuperAdmin
                ? { name: 'admin.dashboard' }
                : { name: 'dashboard' }
            )
        }

        // ────────────────────────────────────────────────────────────────────
        // STEP 9 — Root redirect
        // / → appropriate dashboard
        // ────────────────────────────────────────────────────────────────────
        if (to.path === '/' && auth.isLoggedIn) {
            return next(auth.isSuperAdmin
                ? { name: 'admin.dashboard' }
                : { name: 'dashboard' }
            )
        }

        if (to.path === '/' && !auth.isLoggedIn) {
            return next({ name: 'login' })
        }

        next()
    })

    // ──────────────────────────────────────────────────────────────────────────
    // After-each: optional scroll reset + page title
    // ──────────────────────────────────────────────────────────────────────────
    router.afterEach((to) => {
        const appName = import.meta.env.VITE_APP_NAME ?? 'EasyHCS'
        const title   = to.meta?.title

        if (title) {
            document.title = `${title} — ${appName}`
        } else {
            // Auto-generate title from route name
            const name = String(to.name ?? '')
                .replace(/^(admin\.|pharmacy\.|diagnostic\.|hospital\.|accounts\.)/, '')
                .replace(/[-_.]/g, ' ')
                .replace(/\b\w/g, c => c.toUpperCase())
                .trim()

            document.title = name ? `${name} — ${appName}` : appName
        }
    })
}