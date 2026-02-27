<template>
  <AuthLayout>
    <div class="space-y-6">
      <div class="text-center">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3">
          <ShieldCheck :size="24" class="text-blue-600" />
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Two-Factor Verification</h2>
        <p class="text-sm text-gray-500 mt-1">Enter the 6-digit code sent to your email address.</p>
      </div>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
          <input v-model="code" type="text" inputmode="numeric" maxlength="6" autocomplete="one-time-code"
            placeholder="000000"
            class="w-full text-center text-2xl font-mono tracking-widest py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
            :class="{ 'border-red-400': errorMsg }" />
          <p v-if="errorMsg" class="text-red-500 text-xs mt-1 text-center">{{ errorMsg }}</p>
        </div>
        <button type="submit" :disabled="loading || code.length !== 6"
          class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-semibold py-2.5 rounded-lg text-sm transition">
          <LoaderCircle v-if="loading" :size="16" class="animate-spin" />
          <ShieldCheck v-else :size="16" />
          {{ loading ? 'Verifying…' : 'Verify & Continue' }}
        </button>
        <div class="text-center">
          <button type="button" :disabled="resendCountdown > 0 || resendLoading" @click="resend"
            class="text-sm text-blue-600 hover:underline disabled:text-gray-400">
            <span v-if="resendCountdown > 0">Resend in {{ resendCountdown }}s</span>
            <span v-else>Resend verification code</span>
          </button>
        </div>
      </form>
      <div class="text-center">
        <router-link to="/login" class="text-sm text-gray-500 hover:text-gray-700 flex items-center justify-center gap-1">
          <ArrowLeft :size="14" /> Back to sign in
        </router-link>
      </div>
    </div>
  </AuthLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, LoaderCircle, ShieldCheck } from 'lucide-vue-next'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const auth   = useAuthStore()
const toast  = useToast()

const code            = ref('')
const errorMsg        = ref('')
const loading         = ref(false)
const resendLoading   = ref(false)
const resendCountdown = ref(30)
let countdownTimer    = null

onMounted(() => startCountdown())
onUnmounted(() => clearInterval(countdownTimer))

function startCountdown() {
  resendCountdown.value = 30
  countdownTimer = setInterval(() => {
    if (resendCountdown.value > 0) resendCountdown.value--
    else clearInterval(countdownTimer)
  }, 1000)
}

async function submit() {
  errorMsg.value = ''
  loading.value  = true
  try {
    const result = await auth.verifyTwoFactor(code.value)
    toast.success('Verified! Welcome, ' + result.user.name + '.')
    router.push(result.redirect)
  } catch (err) {
    const data = err.response?.data
    errorMsg.value = data?.errors?.code?.[0] ?? data?.message ?? 'Invalid code.'
  } finally {
    loading.value = false
  }
}

async function resend() {
  resendLoading.value = true
  try {
    await auth.resendTwoFactor()
    toast.success('A new code has been sent.')
    startCountdown()
  } catch {
    toast.error('Failed to resend. Please try again.')
  } finally {
    resendLoading.value = false
  }
}
</script>