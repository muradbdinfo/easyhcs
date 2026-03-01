<script setup>
import { ref } from 'vue'
import AppBadge from '@/Components/shared/AppBadge.vue'
import AppPagination from '@/Components/shared/AppPagination.vue'
import axios from 'axios'

const refunds = ref({ data: [], links: [], meta: {} })

async function fetch() {
  const { data } = await axios.get('/api/admin/transactions', { params: { type: 'refund' } })
  refunds.value = data
}
fetch()
</script>

<template>
  <div class="p-6">
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Ref</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Tenant</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Amount</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Reason</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="!refunds.data.length"><td colspan="5" class="px-5 py-10 text-center text-gray-400">No refunds yet.</td></tr>
          <tr v-for="r in refunds.data" :key="r.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ r.transaction_ref }}</td>
            <td class="px-5 py-3 font-medium text-gray-800">{{ r.tenant?.name }}</td>
            <td class="px-5 py-3 text-right font-semibold text-red-600">-${{ Number(r.amount).toLocaleString() }}</td>
            <td class="px-5 py-3 text-gray-500 text-xs">{{ r.notes ?? '—' }}</td>
            <td class="px-5 py-3 text-gray-400 text-xs">{{ new Date(r.created_at).toLocaleDateString() }}</td>
          </tr>
        </tbody>
      </table>
      <div class="px-5 py-3 border-t border-gray-100"><AppPagination :links="refunds.links" :meta="refunds.meta" /></div>
    </div>
  </div>
</template>