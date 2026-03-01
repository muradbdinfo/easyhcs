<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Search, Plus, Eye, Ban, CheckCircle, Trash2 } from 'lucide-vue-next'
import AppBadge from '@/Components/shared/AppBadge.vue'
import AppPagination from '@/Components/shared/AppPagination.vue'
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue'
import { useToast } from '@/composables/useToast'
import axios from 'axios'

const router = useRouter()
const toast  = useToast()

const tenants       = ref({ data: [], links: [], meta: {} })
const search        = ref('')
const status        = ref('')
const loading       = ref(false)
const confirmDelete = ref(null)

const statusColor = (s) => ({ active:'green', suspended:'red', trialing:'yellow' }[s] ?? 'gray')

async function fetchTenants() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/tenants', {
      params: { search: search.value, status: status.value }
    })
    tenants.value = Array.isArray(data) ? { data, links: [], meta: {} } : data
  } catch { toast.error('Failed to load tenants.') }
  finally { loading.value = false }
}

watch([search, status], fetchTenants)
fetchTenants()

async function suspend(t) {
  await axios.post(`/api/admin/tenants/${t.id}/suspend`)
  toast.success(`${t.business_name} suspended.`)
  fetchTenants()
}
async function activate(t) {
  await axios.post(`/api/admin/tenants/${t.id}/activate`)
  toast.success(`${t.business_name} activated.`)
  fetchTenants()
}
async function deleteTenant(t) {
  await axios.delete(`/api/admin/tenants/${t.id}`)
  confirmDelete.value = null
  toast.success('Tenant deleted.')
  fetchTenants()
}
</script>

<template>
  <div class="p-6">
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
      <div class="relative flex-1">
        <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input v-model="search" placeholder="Search tenants…"
          class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
      </div>
      <select v-model="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
        <option value="">All Statuses</option>
        <option value="active">Active</option>
        <option value="trialing">Trial</option>
        <option value="suspended">Suspended</option>
      </select>
      <button @click="router.push('/admin/tenants/create')"
        class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
        <Plus :size="16" /> New Tenant
      </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Tenant</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Plan</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Created</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="loading"><td colspan="5" class="px-5 py-10 text-center text-gray-400">Loading…</td></tr>
          <tr v-else-if="!tenants.data.length"><td colspan="5" class="px-5 py-10 text-center text-gray-400">No tenants found.</td></tr>
          <tr v-for="t in tenants.data" :key="t.id" class="hover:bg-gray-50">
            <td class="px-5 py-3">
              <p class="font-medium text-gray-800">{{ t.business_name }}</p>
              <p class="text-xs text-gray-400">{{ t.contact_email }}</p>
            </td>
            <td class="px-5 py-3 text-gray-600">{{ t.subscription?.plan?.name ?? '—' }}</td>
            <td class="px-5 py-3">
              <AppBadge :color="statusColor(t.plan_status)">{{ t.plan_status }}</AppBadge>
            </td>
            <td class="px-5 py-3 text-gray-400 text-xs">{{ new Date(t.created_at).toLocaleDateString() }}</td>
            <td class="px-5 py-3">
              <div class="flex items-center justify-end gap-1">
                <button @click="router.push(`/admin/tenants/${t.id}`)" class="p-1.5 rounded hover:bg-blue-50 text-blue-600"><Eye :size="15" /></button>
                <button v-if="t.plan_status === 'active'" @click="suspend(t)" class="p-1.5 rounded hover:bg-orange-50 text-orange-500"><Ban :size="15" /></button>
                <button v-else @click="activate(t)" class="p-1.5 rounded hover:bg-green-50 text-green-600"><CheckCircle :size="15" /></button>
                <button @click="confirmDelete = t" class="p-1.5 rounded hover:bg-red-50 text-red-500"><Trash2 :size="15" /></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="px-5 py-3 border-t border-gray-100">
        <AppPagination :links="tenants.links" :meta="tenants.meta" />
      </div>
    </div>

    <ConfirmDialog v-if="confirmDelete"
      :title="`Delete ${confirmDelete.business_name}?`"
      message="This permanently deletes the tenant and all data."
      confirm-label="Delete"
      @confirm="deleteTenant(confirmDelete)"
      @cancel="confirmDelete = null" />
  </div>
</template>