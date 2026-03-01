<script setup>
import { ref, reactive } from 'vue'
import { Plus, Edit2, Trash2, X } from 'lucide-vue-next'
import AppBadge from '@/Components/shared/AppBadge.vue'
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue'
import { useToast } from '@/composables/useToast'
import axios from 'axios'

const toast   = useToast()
const plans   = ref([])
const modal   = ref(null) // null | 'create' | plan object
const confirmDelete = ref(null)
const allModules = ['core','pharmacy','diagnostic','hospital','accounts']

const blankForm = () => ({ name:'', description:'', price_monthly:'', price_yearly:'', modules:['core','accounts'], is_active:true, sort_order:0 })
const form = reactive(blankForm())
const errors = ref({})
const loading = ref(false)

async function fetchPlans() {
  const { data } = await axios.get('/api/admin/plans')
  plans.value = data
}
fetchPlans()

function openCreate() { Object.assign(form, blankForm()); errors.value = {}; modal.value = 'create' }
function openEdit(p)  { Object.assign(form, { ...p, modules: [...(p.modules ?? [])] }); errors.value = {}; modal.value = p }

async function save() {
  errors.value = {}
  loading.value = true
  try {
    if (modal.value === 'create') {
      await axios.post('/api/admin/plans', form)
      toast.success('Plan created.')
    } else {
      await axios.put(`/api/admin/plans/${modal.value.id}`, form)
      toast.success('Plan updated.')
    }
    modal.value = null
    fetchPlans()
  } catch (err) {
    errors.value = err.response?.data?.errors ?? {}
  } finally { loading.value = false }
}

async function deletePlan(p) {
  try {
    await axios.delete(`/api/admin/plans/${p.id}`)
    confirmDelete.value = null
    toast.success('Plan deleted.')
    fetchPlans()
  } catch (err) {
    confirmDelete.value = null
    toast.error(err.response?.data?.message ?? 'Cannot delete.')
  }
}
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <p class="text-sm text-gray-500">Manage SaaS subscription plans</p>
      <button @click="openCreate"
        class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
        <Plus :size="16" /> New Plan
      </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="plan in plans" :key="plan.id" class="bg-white rounded-xl border border-gray-200 p-5 flex flex-col">
        <div class="flex items-start justify-between mb-2">
          <div>
            <h3 class="font-semibold text-gray-800">{{ plan.name }}</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ plan.description }}</p>
          </div>
          <AppBadge :color="plan.is_active ? 'green' : 'gray'">{{ plan.is_active ? 'Active' : 'Inactive' }}</AppBadge>
        </div>
        <div class="flex gap-4 my-3">
          <div><p class="text-xs text-gray-400">Monthly</p><p class="text-lg font-bold text-gray-800">${{ plan.price_monthly }}</p></div>
          <div><p class="text-xs text-gray-400">Yearly</p><p class="text-lg font-bold text-gray-800">${{ plan.price_yearly }}</p></div>
        </div>
        <div class="flex flex-wrap gap-1.5 mb-3">
          <span v-for="mod in plan.modules" :key="mod"
            class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full capitalize">{{ mod }}</span>
        </div>
        <p class="text-xs text-gray-400 mt-auto mb-3">{{ plan.subscriptions_count ?? 0 }} subscribers</p>
        <div class="flex gap-2 pt-3 border-t border-gray-100">
          <button @click="openEdit(plan)"
            class="flex-1 flex items-center justify-center gap-1.5 text-xs py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-600 transition">
            <Edit2 :size="13" /> Edit
          </button>
          <button @click="confirmDelete = plan"
            class="flex items-center justify-center px-3 text-xs py-1.5 rounded-lg border border-red-200 hover:bg-red-50 text-red-500 transition">
            <Trash2 :size="13" />
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="modal !== null" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="bg-white rounded-2xl w-full max-w-lg mx-4 shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-800">{{ modal === 'create' ? 'Create Plan' : 'Edit Plan' }}</h3>
          <button @click="modal = null" class="p-1.5 rounded hover:bg-gray-100 text-gray-400"><X :size="18" /></button>
        </div>
        <form @submit.prevent="save" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input v-model="form.name" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <input v-model="form.description" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Price ($)</label>
              <input v-model="form.price_monthly" type="number" step="0.01" min="0" required
                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Yearly Price ($)</label>
              <input v-model="form.price_yearly" type="number" step="0.01" min="0" required
                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Modules</label>
            <div class="flex flex-wrap gap-3">
              <label v-for="mod in allModules" :key="mod" class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" :value="mod" v-model="form.modules" class="rounded text-blue-600 focus:ring-blue-500" />
                <span class="text-sm text-gray-700 capitalize">{{ mod }}</span>
              </label>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" id="is_active" v-model="form.is_active" class="rounded text-blue-600 focus:ring-blue-500" />
            <label for="is_active" class="text-sm text-gray-700">Active</label>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="submit" :disabled="loading"
              class="flex-1 bg-blue-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition">
              {{ loading ? 'Saving…' : 'Save Plan' }}
            </button>
            <button type="button" @click="modal = null"
              class="flex-1 border border-gray-300 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <ConfirmDialog v-if="confirmDelete"
      :title="`Delete '${confirmDelete.name}'?`"
      message="Plans with active subscribers cannot be deleted."
      confirm-label="Delete"
      @confirm="deletePlan(confirmDelete)"
      @cancel="confirmDelete = null" />
  </div>
</template>