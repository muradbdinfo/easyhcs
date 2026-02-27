<script setup>
import { ref, computed } from 'vue'
import { ChevronRight, ChevronLeft } from 'lucide-vue-next'

const props = defineProps({ modelValue: Object })
const emit  = defineEmits(['update:modelValue', 'next', 'back'])

const form      = ref({ ...props.modelValue })
const submitted = ref(false)

const timezones = [
  'Asia/Dhaka', 'Asia/Kolkata', 'Asia/Karachi', 'Asia/Kathmandu',
  'Asia/Colombo', 'Asia/Bangkok', 'Asia/Singapore', 'UTC',
  'Europe/London', 'America/New_York', 'America/Los_Angeles',
]

function update(field, value) {
  form.value[field] = value
  emit('update:modelValue', { ...form.value })
}

const errors = computed(() => {
  if (!submitted.value) return {}
  const e = {}
  if (!form.value.name) e.name = 'App name is required.'
  if (!form.value.url)  e.url  = 'App URL is required.'
  return e
})

function next() {
  submitted.value = true
  if (Object.keys(errors.value).length === 0) emit('next')
}
</script>

<template>
  <div>
    <h2 class="text-lg font-semibold text-gray-800 mb-1">Application Settings</h2>
    <p class="text-sm text-gray-500 mb-5">Configure basic app identity and locale settings.</p>

    <div class="space-y-4">
      <!-- App Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Application Name</label>
        <input
          type="text"
          :value="form.name"
          @input="update('name', $event.target.value)"
          class="w-full rounded-lg text-sm focus:ring-blue-500"
          :class="errors.name ? 'border-red-400' : 'border-gray-300'"
          placeholder="EasyHCS"
        />
        <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
      </div>

      <!-- App URL -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Application URL</label>
        <input
          type="url"
          :value="form.url"
          @input="update('url', $event.target.value)"
          class="w-full rounded-lg text-sm focus:ring-blue-500"
          :class="errors.url ? 'border-red-400' : 'border-gray-300'"
          placeholder="http://easyhcs.local"
        />
        <p v-if="errors.url" class="mt-1 text-xs text-red-600">{{ errors.url }}</p>
        <p class="mt-1 text-xs text-gray-400">Include http:// or https://. No trailing slash.</p>
      </div>

      <!-- Timezone -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
        <select
          :value="form.timezone"
          @change="update('timezone', $event.target.value)"
          class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
        </select>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
      <button @click="emit('back')" class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
        <ChevronLeft :size="16" /> Back
      </button>
      <button
        @click="next"
        class="flex items-center gap-2 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
      >
        Continue <ChevronRight :size="16" />
      </button>
    </div>
  </div>
</template>