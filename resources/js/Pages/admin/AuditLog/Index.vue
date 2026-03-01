<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AppPagination from '@/Components/shared/AppPagination.vue'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Search } from 'lucide-vue-next'
import { useDebounceFn } from '@vueuse/core'

const props  = defineProps({ logs: Object, filters: Object })
const search = ref(props.filters?.search ?? '')

const apply = useDebounceFn(() => {
  router.get('/admin/audit-log', { search: search.value }, { preserveState: true, replace: true })
}, 350)
watch(search, apply)

const logColor = (desc) => {
  if (desc.includes('deleted')) return 'text-red-600 bg-red-50'
  if (desc.includes('created')) return 'text-green-600 bg-green-50'
  if (desc.includes('updated')) return 'text-blue-600 bg-blue-50'
  return 'text-gray-600 bg-gray-50'
}
</script>

<template>
  <AdminLayout title="Audit Log">
    <div class="flex gap-3 mb-6">
      <div class="relative flex-1 max-w-sm">
        <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input v-model="search" placeholder="Search events…"
          class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
      </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Event</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">By</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Subject</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">When</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50">
            <td class="px-5 py-3">
              <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', logColor(log.description)]">
                {{ log.description }}
              </span>
            </td>
            <td class="px-5 py-3 text-gray-600">
              {{ log.causer?.name ?? 'System' }}
            </td>
            <td class="px-5 py-3 text-gray-500 text-xs font-mono">
              {{ log.subject_type?.split('\\').pop() }} #{{ log.subject_id }}
            </td>
            <td class="px-5 py-3 text-gray-400 text-xs">
              {{ new Date(log.created_at).toLocaleString() }}
            </td>
          </tr>
          <tr v-if="!logs.data.length">
            <td colspan="4" class="px-5 py-10 text-center text-gray-400">No audit logs found.</td>
          </tr>
        </tbody>
      </table>
      <div class="px-5 py-3 border-t border-gray-100">
        <AppPagination :links="logs.links" :meta="logs.meta" />
      </div>
    </div>
  </AdminLayout>
</template>