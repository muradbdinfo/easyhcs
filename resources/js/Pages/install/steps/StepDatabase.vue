<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { CheckCircle, XCircle, ChevronRight, ChevronLeft, RefreshCw } from 'lucide-vue-next'

const props  = defineProps({ modelValue: Object })
const emit   = defineEmits(['update:modelValue', 'next', 'back'])

const form    = ref({ ...props.modelValue })
const testing = ref(false)
const result  = ref(null)

function update(field, value) {
  form.value[field] = value
  emit('update:modelValue', { ...form.value })
  result.value = null   // reset test result on change
}

async function testConnection() {
  testing.value = true
  result.value  = null
  try {
    const { data } = await axios.post('/install/test-database', form.value)
    result.value = data
  } catch (e) {
    result.value = { success: false, message: e.response?.data?.message ?? e.message }
  } finally {
    testing.value = false
  }
}

function next() {
  if (result.value?.success) emit('next')
}
</script>

<template>
  <div>
    <h2 class="text-lg font-semibold text-gray-800 mb-1">Database Configuration</h2>
    <p class="text-sm text-gray-500 mb-5">
      This is your <strong>system database</strong> (easyhcs_system). Make sure the user has
      <code class="bg-gray-100 px-1 rounded text-xs">CREATE</code> privilege for tenant DB auto-creation.
    </p>

    <div class="grid grid-cols-2 gap-4">
      <!-- Host -->
      <div class="col-span-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">DB Host</label>
        <input
          type="text"
          :value="form.host"
          @input="update('host', $event.target.value)"
          class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
          placeholder="127.0.0.1"
        />
      </div>

      <!-- Port -->
      <div class="col-span-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
        <input
          type="number"
          :value="form.port"
          @input="update('port', parseInt($event.target.value))"
          class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
          placeholder="3306"
        />
      </div>

      <!-- Database name -->
      <div class="col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
        <input
          type="text"
          :value="form.database"
          @input="update('database', $event.target.value)"
          class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
          placeholder="easyhcs_system"
        />
      </div>

      <!-- Username -->
      <div class="col-span-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input
          type="text"
          :value="form.username"
          @input="update('username', $event.target.value)"
          class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
          placeholder="root"
        />
      </div>

      <!-- Password -->
      <div class="col-span-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input
          type="password"
          :value="form.password"
          @input="update('password', $event.target.value)"
          class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
          placeholder="Leave blank for no password"
        />
      </div>
    </div>

    <!-- Test result -->
    <div
      v-if="result"
      class="mt-4 flex items-center gap-2 text-sm px-4 py-3 rounded-lg"
      :class="result.success
        ? 'bg-green-50 text-green-800 border border-green-200'
        : 'bg-red-50 text-red-800 border border-red-200'"
    >
      <CheckCircle v-if="result.success" :size="16" class="shrink-0" />
      <XCircle     v-else                :size="16" class="shrink-0" />
      <span>{{ result.message }}</span>
      <span v-if="result.success && !result.has_create" class="ml-2 text-amber-700">
        — ⚠ No CREATE privilege (tenant DBs may not auto-create)
      </span>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
      <button @click="emit('back')" class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
        <ChevronLeft :size="16" /> Back
      </button>

      <div class="flex gap-3">
        <button
          @click="testConnection"
          :disabled="testing"
          class="flex items-center gap-2 px-4 py-2 border border-blue-600 text-blue-600 text-sm font-medium rounded-lg hover:bg-blue-50 disabled:opacity-40 transition-colors"
        >
          <RefreshCw :size="14" :class="{ 'animate-spin': testing }" />
          {{ testing ? 'Testing…' : 'Test Connection' }}
        </button>

        <button
          @click="next"
          :disabled="!result?.success"
          class="flex items-center gap-2 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
        >
          Continue <ChevronRight :size="16" />
        </button>
      </div>
    </div>
  </div>
</template>