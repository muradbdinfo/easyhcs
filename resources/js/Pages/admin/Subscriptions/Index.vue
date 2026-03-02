<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Search, Eye } from 'lucide-vue-next'
import AppBadge from '@/Components/shared/AppBadge.vue'
import AppPagination from '@/Components/shared/AppPagination.vue'
import { useToast } from '@/composables/useToast'
import axios from 'axios'

const router = useRouter()
const toast  = useToast()

const subscriptions = ref({ data: [], links: [], meta: {} })
const search = ref('')
const status = ref('')
const loading = ref(false)

const statusColor = (s) => ({ active:'green', trialing:'yellow', cancelled:'red', suspended:'orange', expired:'gray' }[s] ?? 'gray')

async function fetchSubscriptions() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/subscriptions', {
      params: { search: search.value, status: status.value }
    })
    subscriptions.value = Array.isArray(data) ? { data, links: [], meta: {} } : data
  } catch { toast.error('Failed to load subscriptions.') }
  finally { loading.value = false }
}

watch([search, status], fetchSubscriptions)
fetchSubscriptions()
</script>

<template>
  <div class="p-6">
    <div class="flex gap-3 mb-6">
      <div class="relative flex-1">
        <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input v-model="search" placeholder="Search by tenant…"
          class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
      </div>
      <select v-model="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
        <option value="">All</option>
        <option value="active">Active</option>
        <option value="trialing">Trial</option>
        <option value="cancelled">Cancelled</option>
        <option value="expired">Expired</option>
        <option value="suspended">Suspended</option>
      </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Tenant</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Plan</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Billing</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Ends At</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="loading"><td colspan="6" class="px-5 py-10 text-center text-gray-400">Loading…</td></tr>
          <tr v-else-if="!subscriptions.data.length"><td colspan="6" class="px-5 py-10 text-center text-gray-400">No subscriptions found.</td></tr>
          <tr v-for="sub in subscriptions.data" :key="sub.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-medium text-gray-800">{{ sub.tenant?.business_name ?? sub.tenant_id }}</td>
            <td class="px-5 py-3 text-gray-600">{{ sub.plan?.name }}</td>
            <td class="px-5 py-3 text-gray-600 capitalize">{{ sub.billing_cycle }}</td>
            <td class="px-5 py-3"><AppBadge :color="statusColor(sub.status)">{{ sub.status }}</AppBadge></td>
            <td class="px-5 py-3 text-gray-400">{{ sub.ends_at ? new Date(sub.ends_at).toLocaleDateString() : '—' }}</td>
            <td class="px-5 py-3 text-right">
              <button @click="router.push(`/admin/subscriptions/${sub.id}`)" class="p-1.5 rounded hover:bg-blue-50 text-blue-600 inline-flex">
                <Eye :size="15" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="px-5 py-3 border-t border-gray-100">
        <AppPagination :links="subscriptions.links" :meta="subscriptions.meta" />
      </div>
    </div>
  </div>
</template>