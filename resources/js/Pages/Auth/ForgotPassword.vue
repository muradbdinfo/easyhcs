<template>
  <AuthLayout>
    <div class="space-y-6">
      <div class="text-center">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3">
          <KeyRound :size="24" class="text-blue-600" />
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Reset your password</h2>
        <p class="text-sm text-gray-500 mt-1">Enter your email and we'll send a reset link.</p>
      </div>
      <div v-if="sent" class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
        <CircleCheck :size="20" class="text-green-500 mx-auto mb-2" />
        <p class="text-sm text-green-700 font-medium">Reset link sent!</p>
        <p class="text-xs text-green-600 mt-1">Check your inbox and follow the instructions.</p>
      </div>
      <form v-else @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
          <input v-model="email" type="email" required placeholder="you@example.com"
            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
            :class="{ 'border-red-400': errorMsg }" />
          <p v-if="errorMsg" class="text-red-500 text-xs mt-1">{{ errorMsg }}</p>
        </div>
        <button type="submit" :disabled="loading"
          class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-semibold py-2.5 rounded-lg text-sm transition">
          <LoaderCircle v-if="loading" :size="16" class="animate-spin" />
          <Send v-else :size="16" />
          {{ loading ? 'Sending…' : 'Send Reset Link' }}
        </button>
      </form>
      <div class="text-center">
        <router-link to="/login" class="text-sm text-blue-600 hover:underline flex items-center justify-center gap-1">
          <ArrowLeft :size="14" /> Back to sign in
        </router-link>
      </div>
    </div>
  </AuthLayout>
</template>

<script setup>
import { ref } from 'vue'
import { ArrowLeft, CircleCheck, KeyRound, LoaderCircle, Send } from 'lucide-vue-next'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { useToast } from '@/composables/useToast'
import axios from 'axios'

const toast    = useToast()
const email    = ref('')
const loading  = ref(false)
const sent     = ref(false)
const errorMsg = ref('')

async function submit() {
  errorMsg.value = ''
  loading.value  = true
  try {
    await axios.post('/forgot-password', { email: email.value })
    sent.value = true
  } catch (err) {
    errorMsg.value = err.response?.data?.errors?.email?.[0] ?? 'Failed to send reset link.'
  } finally {
    loading.value = false
  }
}
</script>