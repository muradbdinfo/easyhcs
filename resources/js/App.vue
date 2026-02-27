<template>
    <!--
        RouterView renders the matched layout (AuthLayout / AdminLayout / TenantLayout).
        Each layout contains its own inner RouterView for the page component.
    -->
    <RouterView />

    <!--
        Global loading overlay shown while auth is not yet initialized.
        Prevents a flash of the login page on hard refresh when user IS logged in.
    -->
    <Transition name="fade">
        <div
            v-if="!auth.initialized"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-white"
        >
            <div class="flex flex-col items-center gap-3">
                <!-- Spinner -->
                <svg
                    class="w-10 h-10 text-indigo-600 animate-spin"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12" cy="12" r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    />
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8v8H4z"
                    />
                </svg>
                <span class="text-sm text-gray-400 font-medium">Loading EasyHCS…</span>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>

<style>
/* Global transition for the loading overlay */
.fade-enter-active,
.fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from,
.fade-leave-to     { opacity: 0; }
</style>