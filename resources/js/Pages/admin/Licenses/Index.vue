<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AppBadge from '@/Components/shared/AppBadge.vue'
import AppPagination from '@/Components/shared/AppPagination.vue'
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Plus, X, XCircle } from 'lucide-vue-next'
import { useToast } from '@/composables/useToast'

const props  = defineProps({ licenses: Object, filters: Object })
const toast  = useToast()
const modal  = ref(false)
const form   = useForm({ tenant_id: '', expires_at: '', max_users: '' })

const submit = () => {
  form.post('/admin/licenses', {
    onSuccess: () => { modal.value = false; toast.success('License key generated.') }
  })
}

const revoke = (lic) => {
  if (!confirm(`Revoke license ${lic.key}?`)) return
  router.patch(`/admin/licenses/${lic.id}/revoke`, {}, {
    onSuccess: () => toast.success('License revoked.')
  })
}

const statusColor = (s) => ({ active:'green', revoked:'red', expired:'gray' }[s] ?? 'gray')
</script>

<template>
  <AdminLayout title="License Keys">
    <div class="flex justify-end mb-6">
      <button @click="modal = true"
        class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
        <Plus :size="16" /> Generate License
      </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Key</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Tenant</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Expires</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Max Users</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="lic in licenses.data" :key="lic.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ lic.key }}</td>
            <td class="px-5 py-3 text-gray-700">{{ lic.tenant?.name }}</td>
            <td class="px-5 py-3">
              <AppBadge :color="statusColor(lic.status)">{{ lic.status }}</AppBadge>
            </td>
            <td class="px-5 py-3 text-gray-400">
              {{ lic.expires_at ? new Date(lic.expires_at).toLocaleDateString() : 'Never' }}
            </td>
            <td class="px-5 py-3 text-gray-500">{{ lic.max_users ?? '∞' }}</td>
            <td class="px-5 py-3 text-right">
              <button
                v-if="lic.status === 'active'"
                @click="revoke(lic)"
                class="p-1.5 rounded hover:bg-red-50 text-red-500 inline-flex"
                title="Revoke"
              >
                <XCircle :size="15" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="px-5 py-3 border-t border-gray-100">
        <AppPagination :links="licenses.links" :meta="licenses.meta" />
      </div>
    </div>

    <!-- Generate Modal -->
    <div v-if="modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="bg-white rounded-2xl w-full max-w-sm mx-4 shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-800">Generate License Key</h3>
          <button @click="modal = false" class="p-1.5 rounded hover:bg-gray-100 text-gray-400">
            <X :size="18" />
          </button>
        </div>
        <form @submit.prevent="submit" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tenant ID</label>
            <input v-model="form.tenant_id" type="text" required
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
            <p v-if="form.errors.tenant_id" class="text-xs text-red-500 mt-1">{{ form.errors.tenant_id }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Expires At (optional)</label>
            <input v-model="form.expires_at" type="date"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Max Users (optional)</label>
            <input v-model="form.max_users" type="number" min="1"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div class="flex gap-3">
            <button type="submit" :disabled="form.processing"
              class="flex-1 bg-blue-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition">
              {{ form.processing ? 'Generating…' : 'Generate' }}
            </button>
            <button type="button" @click="modal = false"
              class="flex-1 border border-gray-300 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>