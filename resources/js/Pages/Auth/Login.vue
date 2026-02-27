<template>
  <AuthLayout>
    <div class="space-y-6">
      <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
        <p class="text-sm text-gray-500 mt-1">Sign in to your account</p>
      </div>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
          <div class="relative">
            <Mail :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="you@example.com"
              class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              :class="{ 'border-red-400': errors.email }"
            />
          </div>
          <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
        </div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <router-link to="/forgot-password" class="text-xs text-blue-600 hover:underline">Forgot password?</router-link>
          </div>
          <div class="relative">
            <Lock :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="••••••••"
              class="w-full pl-9 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              :class="{ 'border-red-400': errors.password }"
            />
            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <Eye v-if="!showPassword" :size="15" />
              <EyeOff v-else :size="15" />
            </button>
          </div>
          <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</p>
        </div>
        <div class="flex items-center gap-2">
          <input id="remember" v-model="form.remember" type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
          <label for="remember" class="text-sm text-gray-600">Remember me for 30 days</label>
        </div>
        <button
          type="submit"
          :disabled="loading"
          class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold py-2.5 rounded-lg text-sm transition"
        >
          <LoaderCircle v-if="loading" :size="16" class="animate-spin" />
          <LogIn v-else :size="16" />
          {{ loading ? 'Signing in…' : 'Sign in' }}
        </button>
      </form>
      <p class="text-center text-sm text-gray-500">
        New to EasyHCS?
        <router-link to="/register" class="text-blue-600 font-medium hover:underline">Create an account</router-link>
      </p>
    </div>
  </AuthLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { Eye, EyeOff, Lock, LogIn, LoaderCircle, Mail } from 'lucide-vue-next'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const auth   = useAuthStore()
const toast  = useToast()

const loading      = ref(false)
const showPassword = ref(false)
const errors       = reactive({})

const form = reactive({ email: '', password: '', remember: false })

async function submit() {
  Object.keys(errors).forEach(k => delete errors[k])
  loading.value = true
  try {
    const result = await auth.login(form)
    if (result.two_factor_required) {
      toast.info('Verification code sent to your email.')
      router.push('/two-factor-challenge')
      return
    }
    toast.success('Welcome back, ' + result.user.name + '!')
    router.push(result.redirect)
  } catch (err) {
    const data = err.response?.data
    if (data?.errors) Object.assign(errors, data.errors)
    else toast.error(data?.message ?? 'Login failed. Please try again.')
  } finally {
    loading.value = false
  }
}
</script>