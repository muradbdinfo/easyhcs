<template>
  <div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
    <Lock :size="56" class="text-amber-400 mb-4" />
    <h2 class="text-2xl font-bold text-gray-800 mb-2 capitalize">{{ module }} Module Locked</h2>
    <p class="text-gray-500 mb-6">Upgrade your plan to unlock this feature.</p>
    <RouterLink :to="{ name: 'billing' }" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
      <Zap :size="16" /> Upgrade Plan
    </RouterLink>
  </div>
</template>
<script setup>
import { useRoute, RouterLink } from 'vue-router'
import { Lock, Zap } from 'lucide-vue-next'
const route  = useRoute()
const module = route.params.module ?? 'this'
</script>
