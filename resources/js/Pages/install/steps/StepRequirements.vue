<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { CheckCircle, XCircle, RefreshCw, AlertTriangle, ChevronRight } from 'lucide-vue-next'

const emit = defineEmits(['next'])

const loading  = ref(true)
const results  = ref(null)
const apiError = ref(null)

async function check() {
  loading.value  = true
  results.value  = null
  apiError.value = null
  try {
    const { data } = await axios.get('/install/requirements')
    results.value = data
  } catch (e) {
    apiError.value = e.response?.data?.message ?? e.message
    results.value  = { all_passed: false, php_version: null, extensions: {}, writable: {} }
  } finally {
    loading.value = false
  }
}

onMounted(check)
</script>

<template>
  <div>
    <h2 class="text-lg font-semibold text-gray-800 mb-4">System Requirements</h2>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center gap-3 text-gray-500 py-8 justify-center">
      <RefreshCw :size="18" class="animate-spin" />
      <span>Checking requirements…</span>
    </div>

    <!-- API Error -->
    <div
      v-else-if="apiError"
      class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-800 mb-4"
    >
      <p class="font-medium mb-1">API Error (you can still continue):</p>
      <code class="text-xs break-all">{{ apiError }}</code>
    </div>

    <!-- Results -->
    <div v-else-if="results" class="space-y-5">

      <!-- PHP Version -->
      <div v-if="results.php_version">
        <h3 class="text-sm font-medium text-gray-600 mb-2">PHP Version</h3>
        <div class="flex items-center gap-2 text-sm">
          <CheckCircle v-if="results.php_ok" :size="16" class="text-green-500 shrink-0" />
          <XCircle     v-else                :size="16" class="text-red-500 shrink-0" />
          <span :class="results.php_ok ? 'text-green-700' : 'text-red-700'">
            PHP {{ results.php_version }}
            {{ results.php_ok ? '✓ (8.2+ required)' : '✗ (8.2+ required)' }}
          </span>
        </div>
      </div>

      <!-- Extensions -->
      <div v-if="Object.keys(results.extensions ?? {}).length">
        <h3 class="text-sm font-medium text-gray-600 mb-2">PHP Extensions</h3>
        <div class="grid grid-cols-2 gap-1.5">
          <div
            v-for="(ok, name) in results.extensions"
            :key="name"
            class="flex items-center gap-2 text-sm"
          >
            <CheckCircle v-if="ok" :size="14" class="text-green-500 shrink-0" />
            <XCircle     v-else    :size="14" class="text-red-500 shrink-0" />
            <span :class="ok ? 'text-gray-700' : 'text-red-700 font-medium'">{{ name }}</span>
          </div>
        </div>
      </div>

      <!-- Writable Directories -->
      <div v-if="Object.keys(results.writable ?? {}).length">
        <h3 class="text-sm font-medium text-gray-600 mb-2">Writable Directories</h3>
        <div class="space-y-1">
          <div
            v-for="(ok, path) in results.writable"
            :key="path"
            class="flex items-center gap-2 text-sm"
          >
            <CheckCircle v-if="ok" :size="14" class="text-green-500 shrink-0" />
            <XCircle     v-else    :size="14" class="text-red-500 shrink-0" />
            <code
              class="font-mono text-xs px-1.5 py-0.5 rounded"
              :class="ok ? 'bg-gray-100 text-gray-700' : 'bg-red-50 text-red-700'"
            >{{ path }}</code>
          </div>
        </div>
      </div>

      <!-- Issues warning -->
      <div
        v-if="!results.all_passed && results.php_version"
        class="flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800"
      >
        <AlertTriangle :size="16" class="shrink-0 mt-0.5" />
        <span>Some checks failed. Fix them before going live, but you can continue for now.</span>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
      <button
        @click="check"
        :disabled="loading"
        class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-40"
      >
        <RefreshCw :size="14" :class="{ 'animate-spin': loading }" />
        Re-check
      </button>

      <!-- Continue is always enabled — PHP 8.2 confirmed via CLI -->
      <button
        @click="emit('next')"
        class="flex items-center gap-2 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
      >
        Continue <ChevronRight :size="16" />
      </button>
    </div>
  </div>
</template>