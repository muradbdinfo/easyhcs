<script setup>
import { Building2, CheckCircle, TrendingUp, AlertCircle } from 'lucide-vue-next'
import AppBadge from '@/Components/shared/AppBadge.vue'

const props = defineProps({
  metrics:            Object,
  revenueChart:       Array,
  recentTenants:      Array,
  recentTransactions: Array,
})

const statusColor = (s) => ({ active:'green', suspended:'red', trial:'yellow' }[s] ?? 'gray')
</script>

<template>
  <div class="p-6 space-y-6">

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm text-gray-500">Total Tenants</p>
          <span class="bg-blue-50 rounded-lg p-2"><Building2 :size="18" class="text-blue-600" /></span>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ metrics?.tenants?.total ?? 0 }}</p>
        <p class="text-xs text-gray-400 mt-1">+{{ metrics?.tenants?.new_this_month ?? 0 }} this month</p>
      </div>

      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm text-gray-500">Active Tenants</p>
          <span class="bg-green-50 rounded-lg p-2"><CheckCircle :size="18" class="text-green-600" /></span>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ metrics?.tenants?.active ?? 0 }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ metrics?.tenants?.suspended ?? 0 }} suspended</p>
      </div>

      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm text-gray-500">Revenue (Month)</p>
          <span class="bg-violet-50 rounded-lg p-2"><TrendingUp :size="18" class="text-violet-600" /></span>
        </div>
        <p class="text-3xl font-bold text-gray-900">${{ Number(metrics?.revenue?.this_month ?? 0).toLocaleString() }}</p>
        <p class="text-xs text-gray-400 mt-1">${{ Number(metrics?.revenue?.total ?? 0).toLocaleString() }} all-time</p>
      </div>

      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm text-gray-500">Expiring in 7 Days</p>
          <span class="bg-orange-50 rounded-lg p-2"><AlertCircle :size="18" class="text-orange-600" /></span>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ metrics?.subscriptions?.expiring ?? 0 }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ metrics?.subscriptions?.trial ?? 0 }} on trial</p>
      </div>
    </div>

    <!-- Recent Tables -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <!-- Recent Tenants -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
          <h3 class="text-sm font-semibold text-gray-700">Recent Tenants</h3>
          <a href="/admin/tenants" class="text-xs text-blue-600 hover:underline">View all</a>
        </div>
        <table class="w-full text-sm">
          <tbody>
            <tr v-if="!recentTenants?.length">
              <td class="px-5 py-8 text-center text-gray-400 text-sm">No tenants yet.</td>
            </tr>
            <tr v-for="t in recentTenants" :key="t.id"
              class="border-b border-gray-50 last:border-0 hover:bg-gray-50">
              <td class="px-5 py-3">
                <p class="font-medium text-gray-800">{{ t.name }}</p>
                <p class="text-xs text-gray-400">{{ t.email }}</p>
              </td>
              <td class="px-5 py-3">
                <AppBadge :color="statusColor(t.status)">{{ t.status }}</AppBadge>
              </td>
              <td class="px-5 py-3 text-xs text-gray-400 text-right">
                {{ new Date(t.created_at).toLocaleDateString() }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Recent Transactions -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
          <h3 class="text-sm font-semibold text-gray-700">Recent Transactions</h3>
          <a href="/admin/transactions" class="text-xs text-blue-600 hover:underline">View all</a>
        </div>
        <table class="w-full text-sm">
          <tbody>
            <tr v-if="!recentTransactions?.length">
              <td class="px-5 py-8 text-center text-gray-400 text-sm">No transactions yet.</td>
            </tr>
            <tr v-for="tx in recentTransactions" :key="tx.id"
              class="border-b border-gray-50 last:border-0 hover:bg-gray-50">
              <td class="px-5 py-3">
                <p class="font-medium text-gray-800">{{ tx.tenant?.name ?? '—' }}</p>
                <p class="text-xs text-gray-400">{{ tx.transaction_ref }}</p>
              </td>
              <td class="px-5 py-3 font-semibold">${{ Number(tx.amount).toLocaleString() }}</td>
              <td class="px-5 py-3">
                <AppBadge :color="tx.status === 'completed' ? 'green' : 'red'">{{ tx.status }}</AppBadge>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>