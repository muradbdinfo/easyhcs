<script setup>
import { ref, reactive } from 'vue'
import { Save } from 'lucide-vue-next'
import { useToast } from '@/composables/useToast'
import axios from 'axios'

const toast    = useToast()
const loading  = ref(false)
const settings = reactive({
  app_name:'', support_email:'', support_phone:'', default_currency:'BDT',
  default_timezone:'Asia/Dhaka', default_trial_days:14,
  smtp_host:'', smtp_port:'', smtp_user:'', smtp_pass:'',
  sms_provider:'', sms_api_key:'', sms_from:'',
  maintenance_mode: false, registration_open: true,
})

async function fetchSettings() {
  const { data } = await axios.get('/api/admin/settings')
  Object.assign(settings, data)
}
fetchSettings()

async function save() {
  loading.value = true
  try {
    await axios.put('/api/admin/settings', { settings })
    toast.success('Settings saved.')
  } catch { toast.error('Failed to save.') }
  finally { loading.value = false }
}
</script>

<template>
  <div class="p-6 max-w-2xl">
    <form @submit.prevent="save" class="space-y-6">
      <!-- General -->
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">General</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">App Name</label>
            <input v-model="settings.app_name" type="text" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
            <input v-model="settings.support_email" type="email" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Default Currency</label>
            <input v-model="settings.default_currency" type="text" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Default Trial Days</label>
            <input v-model="settings.default_trial_days" type="number" min="0" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
        </div>
        <div class="mt-4 flex gap-6">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="settings.registration_open" class="rounded text-blue-600 focus:ring-blue-500" />
            <span class="text-sm text-gray-700">Open Registration</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="settings.maintenance_mode" class="rounded text-blue-600 focus:ring-blue-500" />
            <span class="text-sm text-gray-700">Maintenance Mode</span>
          </label>
        </div>
      </div>

      <!-- Mail -->
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Mail (SMTP)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Host</label>
            <input v-model="settings.smtp_host" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
            <input v-model="settings.smtp_port" type="number" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input v-model="settings.smtp_user" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input v-model="settings.smtp_pass" type="password" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
        </div>
      </div>

      <!-- SMS -->
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">SMS</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
            <select v-model="settings.sms_provider" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
              <option value="">None</option>
              <option value="twilio">Twilio</option>
              <option value="bulsms">BulSMS</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
            <input v-model="settings.sms_api_key" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
            <input v-model="settings.sms_from" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
        </div>
      </div>

      <button type="submit" :disabled="loading"
        class="flex items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition">
        <Save :size="16" />{{ loading ? 'Saving…' : 'Save Settings' }}
      </button>
    </form>
  </div>
</template>