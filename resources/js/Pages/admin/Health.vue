<script setup>
import { ref } from 'vue'
import { CheckCircle, AlertTriangle, XCircle, Trash2, RefreshCw } from 'lucide-vue-next'
import { useToast } from '@/composables/useToast'
import axios from 'axios'

const toast  = useToast()
const status = ref({})

async function fetch() {
  const { data } = await axios.get('/api/admin/health')
  status.value = data
}
fetch()

const statusIcon  = (s) => ({ ok: CheckCircle, warning: AlertTriangle, error: XCircle }[s] ?? AlertTriangle)
const statusClass = (s) => ({ ok:'text-green-500 bg-green-50', warning:'text-yellow-500 bg-yellow-50', error:'text-red-500 bg-red-50' }[s] ?? 'text-gray-400 bg-gray-50')

async function clearCache() {
  await axios.post('/api/admin/health/cache-clear')
  toast.success('Caches cleared.')
}
async function restartQueue() {
  await axios.post('/api/admin/health/queue-restart')
  toast.success('Queue restart signal sent.')
}

const sections = [
  { key:'database', label:'Database' },
  { key:'redis',    label:'Redis' },
  { key:'queue',    label:'Queue Workers' },
  { key:'storage',  label:'Storage' },
  { key:'scheduler',label:'Scheduler' },
]
</script>

<template>
  <div class="p-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
      <div v-for="s in sections" :key="s.key" class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center gap-3 mb-2">
          <span :class="['p-2 rounded-lg', statusClass(status[s.key]?.status)]">
            <component :is="statusIcon(status[s.key]?.status)" :size="18" />
          </span>
          <h3 class="font-medium text-gray-800">{{ s.label }}</h3>
        </div>
        <p class="text-sm text-gray-500">{{ status[s.key]?.message ?? 'Checking…' }}</p>
        <p v-if="status[s.key]?.latency_ms" class="text-xs text-gray-400 mt-1">Latency: {{ status[s.key].latency_ms }}ms</p>
        <template v-if="s.key === 'queue'">
          <p class="text-xs text-gray-400 mt-1">Pending: {{ status.queue?.pending ?? 0 }} | Failed: {{ status.queue?.failed ?? 0 }}</p>
        </template>
        <template v-if="s.key === 'storage'">
          <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-blue-500 rounded-full" :style="{ width: (status.storage?.used_pct ?? 0) + '%' }" />
          </div>
          <p class="text-xs text-gray-400 mt-1">{{ status.storage?.used_pct ?? 0 }}% used · {{ status.storage?.free_gb ?? 0 }}GB free</p>
        </template>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Server Info -->
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Server Info</h3>
        <dl class="space-y-2">
          <div v-for="(val, key) in (status.server ?? {})" :key="key" class="flex justify-between text-sm">
            <dt class="text-gray-500 capitalize">{{ String(key).replace('_', ' ') }}</dt>
            <dd class="font-medium text-gray-700">{{ val }}</dd>
          </div>
        </dl>
      </div>

      <!-- Actions -->
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Maintenance Actions</h3>
        <div class="space-y-3">
          <button @click="clearCache"
            class="w-full flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition text-sm text-left">
            <span class="p-1.5 bg-blue-50 rounded-lg"><Trash2 :size="15" class="text-blue-600" /></span>
            <div>
              <p class="font-medium text-gray-800">Clear All Caches</p>
              <p class="text-xs text-gray-400">config, view, route, application cache</p>
            </div>
          </button>
          <button @click="restartQueue"
            class="w-full flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition text-sm text-left">
            <span class="p-1.5 bg-orange-50 rounded-lg"><RefreshCw :size="15" class="text-orange-600" /></span>
            <div>
              <p class="font-medium text-gray-800">Restart Queue Workers</p>
              <p class="text-xs text-gray-400">Sends restart signal via queue:restart</p>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>