<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AppBadge from '@/Components/shared/AppBadge.vue'
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Plus, Edit2, Power, X } from 'lucide-vue-next'
import { useToast } from '@/composables/useToast'

const props  = defineProps({ gateways: Array })
const toast  = useToast()
const modal  = ref(null)

const blankForm = () => ({
  name: '', slug: '', is_active: true, sort_order: 0,
  credentials: { key: '', secret: '', mode: 'sandbox' }
})

const form = useForm(blankForm())

const openEdit = (gw) => {
  Object.assign(form, {
    ...gw,
    credentials: { ...(gw.credentials ?? {}) }
  })
  modal.value = gw
}

const openCreate = () => { form.reset(); Object.assign(form, blankForm()); modal.value = 'create' }

const save = () => {
  if (modal.value === 'create') {
    form.post('/admin/gateways', { onSuccess: () => { modal.value = null; toast.success('Gateway added.') } })
  } else {
    form.put(`/admin/gateways/${modal.value.id}`, { onSuccess: () => { modal.value = null; toast.success('Gateway updated.') } })
  }
}

const toggle = (gw) => {
  router.patch(`/admin/gateways/${gw.id}/toggle`, {}, {
    onSuccess: () => toast.success(`Gateway ${gw.is_active ? 'disabled' : 'enabled'}.`)
  })
}
</script>

<template>
  <AdminLayout title="Payment Gateways">
    <div class="flex justify-end mb-6">
      <button @click="openCreate"
        class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
        <Plus :size="16" /> Add Gateway
      </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="gw in gateways"
        :key="gw.id"
        class="bg-white rounded-xl border border-gray-200 p-5"
      >
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-semibold text-gray-800 capitalize">{{ gw.name }}</h3>
          <AppBadge :color="gw.is_active ? 'green' : 'gray'">
            {{ gw.is_active ? 'Active' : 'Inactive' }}
          </AppBadge>
        </div>
        <p class="text-xs text-gray-400 font-mono mb-4">slug: {{ gw.slug }}</p>
        <div class="flex gap-2">
          <button @click="openEdit(gw)"
            class="flex-1 flex items-center justify-center gap-1.5 text-xs py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-600 transition">
            <Edit2 :size="13" /> Configure
          </button>
          <button @click="toggle(gw)"
            :class="gw.is_active ? 'border-red-200 text-red-500 hover:bg-red-50' : 'border-green-200 text-green-600 hover:bg-green-50'"
            class="flex items-center justify-center px-3 text-xs py-1.5 rounded-lg border transition">
            <Power :size="13" />
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="modal !== null" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="bg-white rounded-2xl w-full max-w-md mx-4 shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-800">
            {{ modal === 'create' ? 'Add Gateway' : `Configure ${modal.name}` }}
          </h3>
          <button @click="modal = null" class="p-1.5 rounded hover:bg-gray-100 text-gray-400">
            <X :size="18" />
          </button>
        </div>
        <form @submit.prevent="save" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input v-model="form.name" type="text" required
                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
              <input v-model="form.slug" type="text" required :disabled="modal !== 'create'"
                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 disabled:bg-gray-50" />
            </div>
          </div>

          <div class="border-t border-gray-100 pt-4">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Credentials</p>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                <input v-model="form.credentials.key" type="text"
                  class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">API Secret</label>
                <input v-model="form.credentials.secret" type="password"
                  class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                <select v-model="form.credentials.mode"
                  class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                  <option value="sandbox">Sandbox</option>
                  <option value="live">Live</option>
                </select>
              </div>
            </div>
          </div>

          <div class="flex gap-3">
            <button type="submit" :disabled="form.processing"
              class="flex-1 bg-blue-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition">
              {{ form.processing ? 'Saving…' : 'Save' }}
            </button>
            <button type="button" @click="modal = null"
              class="flex-1 border border-gray-300 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>