<template>
  <div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
    <span class="text-7xl font-black text-gray-200 mb-4">404</span>
    <h2 class="text-xl font-bold text-gray-800 mb-2">Page Not Found</h2>
    <RouterLink :to="{ name: 'dashboard' }" class="text-indigo-600 hover:underline text-sm">
      Go to Dashboard
    </RouterLink>
  </div>
</template>
<script setup>
import { RouterLink } from 'vue-router'
</script>
