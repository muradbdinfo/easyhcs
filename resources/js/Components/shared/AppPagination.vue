<script setup>
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

defineProps({
  links: Array,
  meta:  Object,
})
</script>

<template>
  <div v-if="links && links.length > 3" class="flex items-center justify-between text-sm">
    <p class="text-gray-500 text-xs">
      Showing {{ meta?.from ?? '?' }} – {{ meta?.to ?? '?' }} of {{ meta?.total ?? '?' }}
    </p>
    <div class="flex gap-1">
      <template v-for="link in links" :key="link.label">
        <template v-if="link.url">
          
            :href="link.url"
            class="px-3 py-1 rounded-lg border text-xs transition"
            :class="link.active
              ? 'bg-blue-600 text-white border-blue-600'
              : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
            v-html="link.label"
          />
        </template>
        <template v-else>
          <span
            class="px-3 py-1 rounded-lg border border-gray-200 text-xs text-gray-300"
            v-html="link.label"
          />
        </template>
      </template>
    </div>
  </div>
</template>