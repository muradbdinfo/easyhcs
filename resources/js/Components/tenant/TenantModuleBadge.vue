<template>
    <div class="space-y-1">
        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Active Modules</p>
        <div class="flex flex-wrap gap-1">
            <span
                v-for="mod in enabledModules"
                :key="mod"
                class="text-xs bg-indigo-900 text-indigo-300 px-1.5 py-0.5 rounded capitalize"
            >
                {{ mod }}
            </span>
            <span
                v-if="enabledModules.length === 0"
                class="text-xs text-gray-600"
            >
                Core only
            </span>
        </div>
    </div>
</template>

<script setup>
import { computed }          from 'vue'
import { useSettingsStore }  from '@/stores/settings'

const settings      = useSettingsStore()
const enabledModules = computed(() => settings.modulesEnabled ?? [])
</script>