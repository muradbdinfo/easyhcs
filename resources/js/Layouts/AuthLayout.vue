<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-blue-50 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 shadow-lg mb-4">
                    <span class="text-white font-bold text-2xl">H</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">EasyHCS</h1>
                <p class="text-sm text-gray-500 mt-1">Easy Healthcare Solutions</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
                <RouterView />
            </div>

            <!-- Footer -->
            <p class="text-center text-xs text-gray-400 mt-6">
                © {{ new Date().getFullYear() }} EasyHCS. All rights reserved.
            </p>
        </div>
    </div>
</template>

<script setup>
import { RouterView } from 'vue-router'
</script>