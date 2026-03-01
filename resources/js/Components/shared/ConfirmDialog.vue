<script setup>
import { AlertTriangle } from 'lucide-vue-next'

defineProps({
  title:        { type: String, default: 'Are you sure?' },
  message:      { type: String, default: 'This action cannot be undone.' },
  confirmLabel: { type: String, default: 'Confirm' },
  confirmClass: { type: String, default: 'bg-red-600 hover:bg-red-700' },
})

const emit = defineEmits(['confirm', 'cancel'])
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl w-full max-w-sm mx-4 shadow-2xl p-6">
      <div class="flex items-start gap-4">
        <span class="p-2 bg-red-50 rounded-lg shrink-0">
          <AlertTriangle :size="20" class="text-red-500" />
        </span>
        <div>
          <h3 class="font-semibold text-gray-800 mb-1">{{ title }}</h3>
          <p class="text-sm text-gray-500">{{ message }}</p>
        </div>
      </div>
      <div class="flex gap-3 mt-5">
        <button @click="emit('confirm')"
          :class="confirmClass"
          class="flex-1 text-white py-2 rounded-lg text-sm font-medium transition">
          {{ confirmLabel }}
        </button>
        <button @click="emit('cancel')"
          class="flex-1 border border-gray-300 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>