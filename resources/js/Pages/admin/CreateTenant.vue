<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const toast  = useToast()
const plans  = ref([])
const errors = ref({})
const loading = ref(false)

const form = reactive({
  name: '', contact_email: '', phone: '', address: '',
  subdomain: '', plan_id: '', trial_days: 14,
})

axios.get('/api/admin/plans').then(r => plans.value = r.data)

const syncSubdomain = () => {
  if (!form.subdomain) {
    form.subdomain = form.name.toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '')
  }
}

async function submit() {
  errors.value = {}
  loading.value = true
  try {
    await axios.get('/sanctum/csrf-cookie') 
    await axios.post('/api/admin/tenants', form)
    toast.success('Tenant created!')
    router.push('/admin/tenants')
  } catch (err) {
    errors.value = err.response?.data?.errors ?? {}
    toast.error('Please fix the errors.')
  } finally { loading.value = false }
}
</script>

<template>
  <div class="p-6 max-w-2xl">
    <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
      <h2 class="text-base font-semibold text-gray-800">New Tenant</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Clinic / Hospital Name</label>
          <input v-model="form.name" @blur="syncSubdomain" type="text"
            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
            :class="{'border-red-400': errors.name}" />
          <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Owner Email</label>
          <input v-model="form.contact_email" type="email"
            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
            :class="{'border-red-400': errors.contact_email}" />
          <p v-if="errors.contact_email" class="text-xs text-red-500 mt-1">{{ errors.contact_email[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
          <input v-model="form.phone" type="text"
            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Subdomain</label>
          <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500"
            :class="{'border-red-400': errors.subdomain}">
            <input v-model="form.subdomain" type="text" placeholder="clinic-name"
              class="flex-1 text-sm px-3 py-2 border-0 focus:outline-none" />
            <span class="bg-gray-50 px-2 py-2 text-xs text-gray-400 border-l border-gray-300 whitespace-nowrap">.easyhcs.com</span>
          </div>
          <p v-if="errors.subdomain" class="text-xs text-red-500 mt-1">{{ errors.subdomain[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
          <select v-model="form.plan_id"
            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
            :class="{'border-red-400': errors.plan_id}">
            <option value="">Select plan…</option>
            <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <p v-if="errors.plan_id" class="text-xs text-red-500 mt-1">{{ errors.plan_id[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Trial Days</label>
          <input v-model="form.trial_days" type="number" min="0" max="90"
            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
        <textarea v-model="form.address" rows="2"
          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
      </div>

      <div class="flex gap-3 pt-2">
        <button type="submit" :disabled="loading"
          class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition">
          {{ loading ? 'Creating…' : 'Create Tenant' }}
        </button>
        <button type="button" @click="router.push('/admin/tenants')"
          class="px-5 py-2 rounded-lg text-sm font-medium border border-gray-300 hover:bg-gray-50 transition">
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>