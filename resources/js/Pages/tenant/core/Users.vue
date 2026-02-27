<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $route.name }}</h2>
    <p class="text-gray-500">Coming in P9.</p>
  </div>
</template>
<script setup>
import { useRoute } from 'vue-router'
const $route = useRoute()
</script>
