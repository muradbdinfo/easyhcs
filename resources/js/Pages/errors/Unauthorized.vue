<template>
  <div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
    <ShieldOff :size="56" class="text-red-400 mb-4" />
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Access Denied</h2>
    <p class="text-gray-500 mb-6">You don't have permission to view this page.</p>
    <RouterLink :to="{ name: 'dashboard' }" class="text-indigo-600 hover:underline text-sm">
      Go to Dashboard
    </RouterLink>
  </div>
</template>
<script setup>
import { ShieldOff } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
</script>
