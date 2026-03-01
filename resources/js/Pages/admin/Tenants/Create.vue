<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'

const props  = defineProps({ plans: Array })
const toast  = useToast()

const form = useForm({
  name: '', email: '', phone: '', address: '',
  subdomain: '', plan_id: '', trial_days: 14,
})

const submit = () => {
  form.post('/admin/tenants', {
    onSuccess: () => toast.success('Tenant created successfully!'),
    onError: () => toast.error('Please fix the errors below.'),
  })
}

// Auto-fill subdomain from name
const syncSubdomain = () => {
  if (!form.subdomain) {
    form.subdomain = form.name.toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-')
  }
}
</script>

<template>
  <AdminLayout title="Create Tenant">
    <div class="max-w-2xl">
      <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <h2 class="text-base font-semibold text-gray-800">New Tenant</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Clinic / Hospital Name</label>
            <input v-model="form.name" @blur="syncSubdomain" type="text"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
              :class="{'border-red-400': form.errors.name}"
            />
            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Owner Email</label>
            <input v-model="form.email" type="email"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
              :class="{'border-red-400': form.errors.email}"
            />
            <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input v-model="form.phone" type="text"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Subdomain -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Subdomain</label>
            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500"
              :class="{'border-red-400': form.errors.subdomain}">
              <input v-model="form.subdomain" type="text"
                class="flex-1 text-sm px-3 py-2 border-0 focus:outline-none"
                placeholder="clinic-name"
              />
              <span class="bg-gray-50 px-2 py-2 text-xs text-gray-400 border-l border-gray-300 whitespace-nowrap">
                .easyhcs.com
              </span>
            </div>
            <p v-if="form.errors.subdomain" class="text-xs text-red-500 mt-1">{{ form.errors.subdomain }}</p>
          </div>

          <!-- Plan -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
            <select v-model="form.plan_id"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
              :class="{'border-red-400': form.errors.plan_id}"
            >
              <option value="">Select plan…</option>
              <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
            <p v-if="form.errors.plan_id" class="text-xs text-red-500 mt-1">{{ form.errors.plan_id }}</p>
          </div>

          <!-- Trial Days -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Trial Days</label>
            <input v-model="form.trial_days" type="number" min="0" max="90"
              class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <!-- Address -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
          <textarea v-model="form.address" rows="2"
            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div class="flex gap-3 pt-2">
          <button
            type="submit"
            :disabled="form.processing"
            class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition"
          >
            {{ form.processing ? 'Creating…' : 'Create Tenant' }}
          </button>
          <a href="/admin/tenants" class="px-5 py-2 rounded-lg text-sm font-medium border border-gray-300 hover:bg-gray-50 transition">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>