<script setup>
import { ref, reactive } from 'vue'
import { Plus, Edit2, Trash2, X } from 'lucide-vue-next'
import AppBadge from '@/Components/shared/AppBadge.vue'
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue'
import { useToast } from '@/composables/useToast'
import axios from 'axios'

const toast   = useToast()
const users   = ref([])
const modal   = ref(null)
const confirmDelete = ref(null)
const loading = ref(false)

const blankForm = () => ({ name:'', email:'', password:'', password_confirmation:'' })
const form   = reactive(blankForm())
const errors = ref({})

async function fetch() {
  const { data } = await axios.get('/api/admin/admin-users')
  users.value = data.data ?? data
}
fetch()

function openCreate() { Object.assign(form, blankForm()); errors.value = {}; modal.value = 'create' }
function openEdit(u)  { Object.assign(form, { ...u, password:'', password_confirmation:'' }); errors.value = {}; modal.value = u }

async function save() {
  errors.value = {}
  loading.value = true
  try {
    if (modal.value === 'create') {
      await axios.post('/api/admin/admin-users', form)
      toast.success('Admin user created.')
    } else {
      await axios.put(`/api/admin/admin-users/${modal.value.id}`, form)
      toast.success('Updated.')
    }
    modal.value = null
    fetch()
  } catch (err) {
    errors.value = err.response?.data?.errors ?? {}
  } finally { loading.value = false }
}

async function deleteUser(u) {
  try {
    await axios.delete(`/api/admin/admin-users/${u.id}`)
    confirmDelete.value = null
    toast.success('User deleted.')
    fetch()
  } catch (err) {
    confirmDelete.value = null
    toast.error(err.response?.data?.message ?? 'Cannot delete.')
  }
}
</script>

<template>
  <div class="p-6">
    <div class="flex justify-end mb-6">
      <button @click="openCreate"
        class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
        <Plus :size="16" /> Add Admin
      </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Email</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Role</th>
            <th class="text-right px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="!users.length"><td colspan="4" class="px-5 py-10 text-center text-gray-400">No admin users.</td></tr>
          <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-medium text-gray-800">{{ u.name }}</td>
            <td class="px-5 py-3 text-gray-500">{{ u.email }}</td>
            <td class="px-5 py-3">
              <AppBadge :color="u.is_super_admin ? 'purple' : 'blue'">{{ u.is_super_admin ? 'Super Admin' : 'Admin' }}</AppBadge>
            </td>
            <td class="px-5 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <button @click="openEdit(u)" class="p-1.5 rounded hover:bg-blue-50 text-blue-600"><Edit2 :size="15" /></button>
                <button v-if="!u.is_super_admin" @click="confirmDelete = u" class="p-1.5 rounded hover:bg-red-50 text-red-500"><Trash2 :size="15" /></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="modal !== null" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="bg-white rounded-2xl w-full max-w-md mx-4 shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-800">{{ modal === 'create' ? 'New Admin User' : 'Edit Admin User' }}</h3>
          <button @click="modal = null" class="p-1.5 rounded hover:bg-gray-100 text-gray-400"><X :size="18" /></button>
        </div>
        <form @submit.prevent="save" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input v-model="form.name" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" :class="{'border-red-400': errors.name}" />
            <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name[0] }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input v-model="form.email" type="email" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" :class="{'border-red-400': errors.email}" />
            <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email[0] }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password {{ modal !== 'create' ? '(leave blank to keep)' : '' }}</label>
            <input v-model="form.password" type="password" :required="modal === 'create'" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input v-model="form.password_confirmation" type="password" :required="modal === 'create'" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div class="flex gap-3">
            <button type="submit" :disabled="loading"
              class="flex-1 bg-blue-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition">
              {{ loading ? 'Saving…' : 'Save' }}
            </button>
            <button type="button" @click="modal = null"
              class="flex-1 border border-gray-300 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <ConfirmDialog v-if="confirmDelete"
      :title="`Delete ${confirmDelete.name}?`"
      message="This admin user will be permanently removed."
      confirm-label="Delete"
      @confirm="deleteUser(confirmDelete)"
      @cancel="confirmDelete = null" />
  </div>
</template>