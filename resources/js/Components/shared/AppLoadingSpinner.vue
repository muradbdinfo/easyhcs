<script setup>
import { Loader2 } from 'lucide-vue-next'
defineProps({ size: { type: Number, default: 24 } })
</script>

<template>
  <div class="flex items-center justify-center py-8">
    <Loader2 :size="size" class="animate-spin text-blue-500" />
  </div>
</template>