<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'
import { Save } from 'lucide-vue-next'

const props = defineProps({ settings: Object })
const toast = useToast()

const form = useForm({ settings: { ...props.settings } })

const save = () => {
  form.post('/admin/settings', {
    onSuccess: () => toast.success('Settings saved successfully.'),
    onError:   () => toast.error('Failed to save settings.'),
  })
}
</script>

<template>
  <AdminLayout title="System Settings">
    <form @submit.prevent="save" class="max-w-2xl space-y-6">
      <!-- General -->
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">General</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">App Name</label>
            <input v-model="form.settings.app_name" type="text"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
            <input v-model="form.settings.support_email" type="email"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Default Currency</label>
            <input v-model="form.settings.default_currency" type="text"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Default Trial Days</label>
            <input v-model="form.settings.default_trial_days" type="number" min="0"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
        </div>
        <div class="mt-4 flex gap-6">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.settings.registration_open"
              class="rounded text-blue-600 focus:ring-blue-500" />
            <span class="text-sm text-gray-700">Open Registration</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.settings.maintenance_mode"
              class="rounded text-blue-600 focus:ring-blue-500" />
            <span class="text-sm text-gray-700">Maintenance Mode</span>
          </label>
        </div>
      </div>

      <!-- Mail -->
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Mail (SMTP)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Host</label>
            <input v-model="form.settings.smtp_host" type="text"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Port</label>
            <input v-model="form.settings.smtp_port" type="number"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Username</label>
            <input v-model="form.settings.smtp_user" type="text"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Password</label>
            <input v-model="form.settings.smtp_pass" type="password"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
        </div>
      </div>

      <!-- SMS -->
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">SMS</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
            <select v-model="form.settings.sms_provider"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
              <option value="">None</option>
              <option value="twilio">Twilio</option>
              <option value="bulsms">BulSMS</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
            <input v-model="form.settings.sms_api_key" type="text"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From Name/Number</label>
            <input v-model="form.settings.sms_from" type="text"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <button
        type="submit"
        :disabled="form.processing"
        class="flex items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition"
      >
        <Save :size="16" />
        {{ form.processing ? 'Saving…' : 'Save Settings' }}
      </button>
    </form>
  </AdminLayout>
</template>