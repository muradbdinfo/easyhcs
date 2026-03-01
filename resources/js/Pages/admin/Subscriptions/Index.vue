<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AppBadge from '@/Components/shared/AppBadge.vue'
import AppPagination from '@/Components/shared/AppPagination.vue'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Search, Eye } from 'lucide-vue-next'
import { useDebounceFn } from '@vueuse/core'

const props   = defineProps({ subscriptions: Object, filters: Object })
const search  = ref(props.filters?.search ?? '')
const status  = ref(props.filters?.status ?? '')

const apply = useDebounceFn(() => {
  router.get('/admin/subscriptions', { search: search.value, status: status.value }, { preserveState: true, replace: true })
}, 350)

watch([search, status], apply)

const statusColor = (s) => ({ active:'green', trial:'yellow', cancelled:'red', suspended:'orange', expired:'gray' }[s] ?? 'gray')
</script>

<template>
  <AdminLayout title="Subscriptions">
    <div class="flex gap-3 mb-6">
      <div class="relative flex-1">
        <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input v-model="search" placeholder="Search by tenant…"
          class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
      </div>
      <select v-model="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
        <option value="">All</option>
        <option>active</option><option>trial</option>
        <option>cancelled</option><option>expired</option>
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
          <tr v-for="sub in subscriptions.data" :key="sub.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-medium text-gray-800">{{ sub.tenant?.name }}</td>
            <td class="px-5 py-3 text-gray-600">{{ sub.plan?.name }}</td>
            <td class="px-5 py-3 text-gray-600 capitalize">{{ sub.billing_cycle }}</td>
            <td class="px-5 py-3"><AppBadge :color="statusColor(sub.status)">{{ sub.status }}</AppBadge></td>
            <td class="px-5 py-3 text-gray-400">
              {{ sub.ends_at ? new Date(sub.ends_at).toLocaleDateString() : '—' }}
            </td>
            <td class="px-5 py-3 text-right">
              <a :href="`/admin/subscriptions/${sub.id}`" class="p-1.5 rounded hover:bg-blue-50 text-blue-600 inline-flex">
                <Eye :size="15" />
              </a>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="px-5 py-3 border-t border-gray-100">
        <AppPagination :links="subscriptions.links" :meta="subscriptions.meta" />
      </div>
    </div>
  </AdminLayout>
</template>