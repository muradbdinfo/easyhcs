<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AppBadge from '@/Components/shared/AppBadge.vue'
import AppPagination from '@/Components/shared/AppPagination.vue'
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue'
import { router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { Search, Plus, Eye, Ban, CheckCircle, Trash2, UserCog } from 'lucide-vue-next'
import { useToast } from '@/composables/useToast'
import { useDebounceFn } from '@vueuse/core'

const props = defineProps({ tenants: Object, filters: Object })
const toast  = useToast()

const search  = ref(props.filters?.search ?? '')
const status  = ref(props.filters?.status ?? '')
const confirmDelete = ref(null)

const applyFilters = useDebounceFn(() => {
  router.get('/admin/tenants', { search: search.value, status: status.value }, { preserveState: true, replace: true })
}, 350)

watch([search, status], applyFilters)

const suspend = (tenant) => {
  router.patch(`/admin/tenants/${tenant.id}/suspend`, {}, {
    onSuccess: () => toast.success(`${tenant.name} suspended.`)
  })
}

const activate = (tenant) => {
  router.patch(`/admin/tenants/${tenant.id}/activate`, {}, {
    onSuccess: () => toast.success(`${tenant.name} activated.`)
  })
}

const deleteTenant = (tenant) => {
  router.delete(`/admin/tenants/${tenant.id}`, {
    onSuccess: () => { confirmDelete.value = null; toast.success('Tenant deleted.') }
  })
}

const statusColor = (s) => ({ active:'green', suspended:'red', trial:'yellow' }[s] ?? 'gray')
</script>

<template>
  <AdminLayout title="Tenants">
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
      <div class="relative flex-1">
        <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input
          v-model="search"
          type="text"
          placeholder="Search tenants..."
          class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
      </div>
      <select v-model="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
        <option value="">All Statuses</option>
        <option value="active">Active</option>
        <option value="trial">Trial</option>
        <option value="suspended">Suspended</option>
      </select>
      
        href="/admin/tenants/create"
        class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition"
      >
        <Plus :size="16" />
        New Tenant
      </a>
    </div>

    <!-- Table -->
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
          <tr v-for="tenant in tenants.data" :key="tenant.id" class="hover:bg-gray-50">
            <td class="px-5 py-3">
              <p class="font-medium text-gray-800">{{ tenant.name }}</p>
              <p class="text-xs text-gray-400">{{ tenant.email }}</p>
            </td>
            <td class="px-5 py-3 text-gray-600">
              {{ tenant.subscription?.plan?.name ?? '—' }}
            </td>
            <td class="px-5 py-3">
              <AppBadge :color="statusColor(tenant.status)">{{ tenant.status }}</AppBadge>
            </td>
            <td class="px-5 py-3 text-gray-400">
              {{ new Date(tenant.created_at).toLocaleDateString() }}
            </td>
            <td class="px-5 py-3">
              <div class="flex items-center justify-end gap-2">
                <a :href="`/admin/tenants/${tenant.id}`" class="p-1.5 rounded hover:bg-blue-50 text-blue-600">
                  <Eye :size="15" />
                </a>
                <button
                  v-if="tenant.status === 'active'"
                  @click="suspend(tenant)"
                  class="p-1.5 rounded hover:bg-orange-50 text-orange-500"
                  title="Suspend"
                >
                  <Ban :size="15" />
                </button>
                <button
                  v-else
                  @click="activate(tenant)"
                  class="p-1.5 rounded hover:bg-green-50 text-green-600"
                  title="Activate"
                >
                  <CheckCircle :size="15" />
                </button>
                <button
                  @click="confirmDelete = tenant"
                  class="p-1.5 rounded hover:bg-red-50 text-red-500"
                  title="Delete"
                >
                  <Trash2 :size="15" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!tenants.data.length">
            <td colspan="5" class="px-5 py-10 text-center text-gray-400">No tenants found.</td>
          </tr>
        </tbody>
      </table>
      <div class="px-5 py-3 border-t border-gray-100">
        <AppPagination :links="tenants.links" :meta="tenants.meta" />
      </div>
    </div>

    <!-- Confirm Delete Dialog -->
    <ConfirmDialog
      v-if="confirmDelete"
      :title="`Delete ${confirmDelete.name}?`"
      message="This will permanently delete the tenant and all associated data. This cannot be undone."
      confirm-label="Delete"
      confirm-class="bg-red-600 hover:bg-red-700"
      @confirm="deleteTenant(confirmDelete)"
      @cancel="confirmDelete = null"
    />
  </AdminLayout>
</template>