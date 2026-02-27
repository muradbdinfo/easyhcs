<template>
  <AuthLayout>
    <div class="space-y-6 text-center">
      <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto">
        <MailCheck :size="28" class="text-blue-600" />
      </div>
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Verify your email</h2>
        <p class="text-sm text-gray-500 mt-2 leading-relaxed">
          We've sent a verification link to your email address. Please click the link to activate your account.
        </p>
      </div>
      <div v-if="sent" class="bg-green-50 border border-green-200 rounded-lg px-4 py-3">
        <p class="text-sm text-green-700">Verification email resent successfully!</p>
      </div>
      <button @click="resend" :disabled="loading || cooldown > 0"
        class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-semibold py-2.5 rounded-lg text-sm transition">
        <LoaderCircle v-if="loading" :size="16" class="animate-spin" />
        <RefreshCcw v-else :size="16" />
        <span v-if="cooldown > 0">Resend in {{ cooldown }}s</span>
        <span v-else>Resend Verification Email</span>
      </button>
      <button @click="logout" class="w-full text-sm text-gray-500 hover:text-gray-700 flex items-center justify-center gap-1">
        <LogOut :size="14" /> Sign out
      </button>
    </div>
  </AuthLayout>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { LoaderCircle, LogOut, MailCheck, RefreshCcw } from 'lucide-vue-next'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import axios from 'axios'

const router  = useRouter()
const auth    = useAuthStore()
const toast   = useToast()

const loading  = ref(false)
const sent     = ref(false)
const cooldown = ref(0)
let timer = null

async function resend() {
  loading.value = true
  try {
    await axios.post('/email/verification-notification')
    sent.value     = true
    cooldown.value = 60
    timer = setInterval(() => {
      if (cooldown.value > 0) cooldown.value--
      else clearInterval(timer)
    }, 1000)
  } catch {
    toast.error('Failed to resend. Please try again.')
  } finally {
    loading.value = false
  }
}

async function logout() {
  await auth.logout()
  router.push('/login')
}

onUnmounted(() => clearInterval(timer))
</script>