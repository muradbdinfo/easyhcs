<script setup>
import { ref, watch } from 'vue'
import { Search, RotateCcw, X } from 'lucide-vue-next'
import AppBadge from '@/Components/shared/AppBadge.vue'
import AppPagination from '@/Components/shared/AppPagination.vue'
import { useToast } from '@/composables/useToast'
import axios from 'axios'

const toast  = useToast()
const transactions = ref({ data: [], links: [], meta: {} })
const summary      = ref({ this_month: 0, total_completed: 0 })
const search = ref('')
const status = ref('')
const refundTarget = ref(null)
const refundForm   = ref({ amount: '', reason: '' })
const refunding    = ref(false)

const statusColor = (s) => ({ completed:'green', pending:'yellow', failed:'red', refunded:'gray' }[s] ?? 'gray')

async function fetch() {
  const { data } = await axios.get('/api/admin/transactions', { params: { search: search.value, status: status.value } })
  transactions.value = data.transactions ?? data
  summary.value = data.summary ?? summary.value
}
watch([search, status], fetch)
fetch()

function openRefund(tx) { refundTarget.value = tx; refundForm.value = { amount: tx.amount, reason: '' } }

async function submitRefund() {
  refunding.value = true
  try {
    await axios.post(`/api/admin/transactions/${refundTarget.value.id}/refund`, refundForm.value)
    toast.success('Refund processed.')
    refundTarget.value = null
    fetch()
  } catch { toast.error('Refund failed.') }
  finally { refunding.value = false }
}
</script>

<template>
  <div class="p-6">
    <div class="grid grid-cols-2 gap-4 mb-6">
      <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">This Month</p>
        <p class="text-2xl font-bold text-gray-800">${{ Number(summary.this_month).toLocaleString() }}</p>
      </div>
      <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">All Time</p>
        <p class="text-2xl font-bold text-gray-800">${{ Number(summary.total_completed).toLocaleString() }}</p>
      </div>
    </div>

    <div class="flex gap-3 mb-4">
      <div class="relative flex-1">
        <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input v-model="search" placeholder="Search ref or tenant…"
          class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
      </div>
      <select v-model="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
        <option value="">All</option>
        <option>completed</option><option>pending</option><option>failed</option><option>refunded</option>
      </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Ref</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Tenant</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Gateway</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Amount</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
            <th class="text-right px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="!transactions.data.length"><td colspan="7" class="px-5 py-10 text-center text-gray-400">No transactions yet.</td></tr>
          <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ tx.transaction_ref }}</td>
            <td class="px-5 py-3 font-medium text-gray-800">{{ tx.tenant?.name }}</td>
            <td class="px-5 py-3 capitalize text-gray-600">{{ tx.gateway }}</td>
            <td class="px-5 py-3 text-right font-semibold">${{ Number(tx.amount).toLocaleString() }}</td>
            <td class="px-5 py-3"><AppBadge :color="statusColor(tx.status)">{{ tx.status }}</AppBadge></td>
            <td class="px-5 py-3 text-gray-400 text-xs">{{ new Date(tx.created_at).toLocaleDateString() }}</td>
            <td class="px-5 py-3 text-right">
              <button v-if="tx.status === 'completed'" @click="openRefund(tx)" class="p-1.5 rounded hover:bg-orange-50 text-orange-500">
                <RotateCcw :size="15" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="px-5 py-3 border-t border-gray-100"><AppPagination :links="transactions.links" :meta="transactions.meta" /></div>
    </div>

    <!-- Refund Modal -->
    <div v-if="refundTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="bg-white rounded-2xl w-full max-w-md mx-4 shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-800">Process Refund</h3>
          <button @click="refundTarget = null" class="p-1.5 rounded hover:bg-gray-100 text-gray-400"><X :size="18" /></button>
        </div>
        <form @submit.prevent="submitRefund" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (max ${{ refundTarget.amount }})</label>
            <input v-model="refundForm.amount" type="number" step="0.01" :max="refundTarget.amount" min="0.01" required
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
            <textarea v-model="refundForm.reason" rows="3" required
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div class="flex gap-3">
            <button type="submit" :disabled="refunding"
              class="flex-1 bg-orange-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-orange-700 disabled:opacity-50 transition">
              {{ refunding ? 'Processing…' : 'Process Refund' }}
            </button>
            <button type="button" @click="refundTarget = null"
              class="flex-1 border border-gray-300 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>