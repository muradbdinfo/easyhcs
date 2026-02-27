<template>
  <AuthLayout>
    <div class="space-y-6">
      <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900">Set new password</h2>
        <p class="text-sm text-gray-500 mt-1">Choose a strong password for your account.</p>
      </div>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
          <input v-model="form.password" type="password" required placeholder="Min. 8 characters"
            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
            :class="{ 'border-red-400': errors.password }" />
          <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
          <input v-model="form.password_confirmation" type="password" required placeholder="Repeat new password"
            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
        </div>
        <button type="submit" :disabled="loading"
          class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-semibold py-2.5 rounded-lg text-sm transition">
          <LoaderCircle v-if="loading" :size="16" class="animate-spin" />
          <KeyRound v-else :size="16" />
          {{ loading ? 'Updating…' : 'Update Password' }}
        </button>
      </form>
    </div>
  </AuthLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { KeyRound, LoaderCircle } from 'lucide-vue-next'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { useToast } from '@/composables/useToast'
import axios from 'axios'

const router = useRouter()
const route  = useRoute()
const toast  = useToast()

const loading = ref(false)
const errors  = reactive({})
const form    = reactive({
  token:                 route.query.token ?? '',
  email:                 route.query.email ?? '',
  password:              '',
  password_confirmation: '',
})

async function submit() {
  Object.keys(errors).forEach(k => delete errors[k])
  loading.value = true
  try {
    await axios.post('/reset-password', form)
    toast.success('Password updated. Please sign in.')
    router.push('/login')
  } catch (err) {
    const data = err.response?.data
    if (data?.errors) Object.assign(errors, data.errors)
    else toast.error(data?.message ?? 'Failed to reset password.')
  } finally {
    loading.value = false
  }
}
</script>