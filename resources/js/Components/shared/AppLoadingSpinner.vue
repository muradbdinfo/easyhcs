<template>
    <div :class="['flex items-center justify-center', fullPage ? 'min-h-screen' : 'py-12']">
        <svg
            :class="['animate-spin text-indigo-600', sizeClass]"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
        </svg>
    </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({
    size     : { type: String, default: 'md' },
    fullPage : { type: Boolean, default: false },
})
const sizeClass = computed(() => ({ sm: 'w-4 h-4', md: 'w-8 h-8', lg: 'w-12 h-12' }[props.size]))
</script>