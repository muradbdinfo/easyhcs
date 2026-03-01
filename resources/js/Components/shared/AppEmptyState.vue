<script setup>
import { Inbox } from 'lucide-vue-next'
defineProps({
  message: { type: String, default: 'No data found.' }
})
</script>

<template>
  <div class="flex flex-col items-center justify-center py-12 text-gray-400">
    <Inbox :size="36" class="mb-3 opacity-50" />
    <p class="text-sm">{{ message }}</p>
    <slot />
  </div>
</template>