import './bootstrap'          // Breeze's bootstrap.js (axios, Lodash setup)
import '../css/app.css'

import { createApp }   from 'vue'
import { createPinia } from 'pinia'
import Toast, { POSITION } from 'vue-toastification'
import 'vue-toastification/dist/index.css'
import axios from 'axios'

import App     from './App.vue'
import router  from './router/index.js'
import { setupGuards } from './router/guards.js'

// ─── Axios — Sanctum SPA (cookie-based) ────────────────────────────────────
// withCredentials = true ensures session cookies are sent cross-origin.
// X-Requested-With tells Laravel this is an AJAX request (not a browser form).
// Sanctum handles CSRF via the session cookie; /sanctum/csrf-cookie seeds it.
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['Accept']            = 'application/json'

// CSRF meta tag (present in app.blade.php via csrf_token())
const csrfMeta = document.head.querySelector('meta[name="csrf-token"]')
if (csrfMeta) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfMeta.content
}

// Global 401 interceptor — if session expires mid-session, send to login
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response?.status

        // 401 = session expired / unauthenticated
        if (status === 401) {
            // Reset auth state without calling the server again
            import('@/stores/auth').then(({ useAuthStore }) => {
                const auth = useAuthStore()
                auth.$reset()
                auth.initialized = true
            })
            // Only redirect if we're not already on an auth page
            const path = window.location.pathname
            if (!path.startsWith('/login') && !path.startsWith('/install')) {
                window.location.href = `/login?redirect=${encodeURIComponent(path)}`
            }
        }

        // 419 = CSRF token mismatch — refresh the cookie and retry once
        if (status === 419) {
            return axios.get('/sanctum/csrf-cookie').then(() => {
                return axios.request(error.config)
            })
        }

        return Promise.reject(error)
    }
)

// ─── Pinia ──────────────────────────────────────────────────────────────────
const pinia = createPinia()

// ─── App ────────────────────────────────────────────────────────────────────
const app = createApp(App)

app.use(pinia)
app.use(router)
app.use(Toast, {
    position         : POSITION.TOP_RIGHT,
    timeout          : 4000,
    closeOnClick     : true,
    pauseOnFocusLoss : true,
    pauseOnHover     : true,
    draggable        : true,
    draggablePercent : 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar  : false,
    icon             : true,
    maxToasts        : 5,
    newestOnTop      : true,

    // De-duplicate toasts with identical messages
    filterBeforeCreate: (toast, toasts) => {
        const isDuplicate = toasts.some(t =>
            t.content === toast.content && t.type === toast.type
        )
        return isDuplicate ? false : toast
    },
})

// ─── Guards (must be after pinia.use so stores are available) ───────────────
setupGuards(router)

// ─── Boot: restore session BEFORE first navigation ──────────────────────────
//
// We call fetchUser() here, before app.mount(), so that:
//   1. The session is checked against the server once on page load.
//   2. auth.initialized = true before any guard runs.
//   3. If the user IS logged in, their permissions/roles are ready.
//   4. If NOT logged in, guards can safely redirect to /login.
//
// router.isReady() ensures the initial navigation (which triggers guards)
// happens after fetchUser() has resolved.
//
;(async () => {
    const { useAuthStore } = await import('@/stores/auth')
    const auth = useAuthStore()

    // fetchUser resolves even on 401 — it never throws
    await auth.fetchUser()

    // Wait for router to be ready (processes the initial URL)
    await router.isReady()

    app.mount('#app')
})()