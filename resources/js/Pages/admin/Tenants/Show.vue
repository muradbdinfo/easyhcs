<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AppBadge from '@/Components/shared/AppBadge.vue'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Building2, Mail, Phone, Globe, CreditCard, Calendar, Ban, CheckCircle, UserCog, Trash2 } from 'lucide-vue-next'
import { useToast } from '@/composables/useToast'

const props = defineProps({ tenant: Object, plans: Array, invoices: Array })
const toast = useToast()

const suspend  = () => router.patch(`/admin/tenants/${props.tenant.id}/suspend`,  {}, { onSuccess: () => toast.success('Suspended.') })
const activate = () => router.patch(`/admin/tenants/${props.tenant.id}/activate`, {}, { onSuccess: () => toast.success('Activated.') })

const statusColor = (s) => ({ active:'green', suspended:'red', trial:'yellow', cancelled:'gray' }[s] ?? 'gray')
</script>

<template>
  <AdminLayout :title="tenant.name">
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- Tenant Info Card -->
      <div class="xl:col-span-1 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Tenant Info</h3>
            <AppBadge :color="statusColor(tenant.status)">{{ tenant.status }}</AppBadge>
          </div>
          <div class="space-y-3">
            <div class="flex items-center gap-3 text-sm">
              <Building2 :size="15" class="text-gray-400 shrink-0" />
              <span class="text-gray-700">{{ tenant.name }}</span>
            </div>
            <div class="flex items-center gap-3 text-sm">
              <Mail :size="15" class="text-gray-400 shrink-0" />
              <span class="text-gray-700">{{ tenant.email }}</span>
            </div>
            <div v-if="tenant.phone" class="flex items-center gap-3 text-sm">
              <Phone :size="15" class="text-gray-400 shrink-0" />
              <span class="text-gray-700">{{ tenant.phone }}</span>
            </div>
            <div class="flex items-center gap-3 text-sm">
              <Globe :size="15" class="text-gray-400 shrink-0" />
              <a :href="`http://${tenant.domains?.[0]?.domain}`" target="_blank" class="text-blue-600 hover:underline">
                {{ tenant.domains?.[0]?.domain }}
              </a>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-gray-100 flex flex-col gap-2">
            <button
              v-if="tenant.status === 'active'"
              @click="suspend"
              class="w-full flex items-center justify-center gap-2 text-sm py-2 rounded-lg border border-orange-300 text-orange-600 hover:bg-orange-50 transition"
            >
              <Ban :size="15" /> Suspend
            </button>
            <button
              v-else
              @click="activate"
              class="w-full flex items-center justify-center gap-2 text-sm py-2 rounded-lg border border-green-300 text-green-600 hover:bg-green-50 transition"
            >
              <CheckCircle :size="15" /> Activate
            </button>
          </div>
        </div>

        <!-- Subscription Info -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-800 mb-4">Subscription</h3>
          <div v-if="tenant.subscription" class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Plan</span>
              <span class="font-medium">{{ tenant.subscription.plan?.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Status</span>
              <AppBadge :color="statusColor(tenant.subscription.status)">
                {{ tenant.subscription.status }}
              </AppBadge>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Billing</span>
              <span>{{ tenant.subscription.billing_cycle }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Ends At</span>
              <span>{{ tenant.subscription.ends_at ? new Date(tenant.subscription.ends_at).toLocaleDateString() : '∞' }}</span>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400">No active subscription.</p>
        </div>

        <!-- Modules -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-800 mb-3">Enabled Modules</h3>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="mod in tenant.subscription?.plan?.modules"
              :key="mod"
              class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs rounded-full font-medium capitalize"
            >
              {{ mod }}
            </span>
          </div>
        </div>
      </div>

      <!-- Invoices -->
      <div class="xl:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Subscription Invoices</h3>
          </div>
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Invoice</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Amount</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="inv in invoices" :key="inv.id" class="hover:bg-gray-50">
                <td class="px-5 py-3 font-medium text-gray-700">{{ inv.invoice_no }}</td>
                <td class="px-5 py-3">${{ Number(inv.amount).toLocaleString() }}</td>
                <td class="px-5 py-3">
                  <AppBadge :color="inv.status === 'paid' ? 'green' : inv.status === 'overdue' ? 'red' : 'yellow'">
                    {{ inv.status }}
                  </AppBadge>
                </td>
                <td class="px-5 py-3 text-gray-400">
                  {{ new Date(inv.created_at).toLocaleDateString() }}
                </td>
              </tr>
              <tr v-if="!invoices?.length">
                <td colspan="4" class="px-5 py-8 text-center text-gray-400">No invoices yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>