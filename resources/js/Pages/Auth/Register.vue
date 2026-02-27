<template>
  <AuthLayout>
    <div class="space-y-6">
      <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900">Create your clinic account</h2>
        <p class="text-sm text-gray-500 mt-1">Start your free trial today</p>
      </div>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
          <input v-model="form.name" type="text" required placeholder="Dr. John Smith"
            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
            :class="{ 'border-red-400': errors.name }" />
          <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Clinic / Hospital Name</label>
          <input v-model="form.clinic_name" type="text" required placeholder="City Medical Center"
            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
            :class="{ 'border-red-400': errors.clinic_name }" />
          <p v-if="errors.clinic_name" class="text-red-500 text-xs mt-1">{{ errors.clinic_name[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input v-model="form.email" type="email" required placeholder="admin@yourclinic.com"
            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
            :class="{ 'border-red-400': errors.email }" />
          <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <div class="relative">
            <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required placeholder="Min. 8 characters"
              class="w-full pr-10 px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
              :class="{ 'border-red-400': errors.password }" />
            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
              <Eye v-if="!showPassword" :size="15" /><EyeOff v-else :size="15" />
            </button>
          </div>
          <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
          <input v-model="form.password_confirmation" type="password" required placeholder="Repeat password"
            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
        </div>
        <button type="submit" :disabled="loading"
          class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-semibold py-2.5 rounded-lg text-sm transition">
          <LoaderCircle v-if="loading" :size="16" class="animate-spin" />
          <UserPlus v-else :size="16" />
          {{ loading ? 'Creating account…' : 'Create Account' }}
        </button>
      </form>
      <p class="text-center text-sm text-gray-500">
        Already have an account?
        <router-link to="/login" class="text-blue-600 font-medium hover:underline">Sign in</router-link>
      </p>
    </div>
  </AuthLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { Eye, EyeOff, LoaderCircle, UserPlus } from 'lucide-vue-next'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { useToast } from '@/composables/useToast'
import axios from 'axios'

const router = useRouter()
const toast  = useToast()

const loading      = ref(false)
const showPassword = ref(false)
const errors       = reactive({})

const form = reactive({ name: '', clinic_name: '', email: '', password: '', password_confirmation: '' })

async function submit() {
  Object.keys(errors).forEach(k => delete errors[k])
  loading.value = true
  try {
    await axios.get('/sanctum/csrf-cookie')
    await axios.post('/register', form)
    toast.success('Account created! Please verify your email.')
    router.push('/verify-email')
  } catch (err) {
    const data = err.response?.data
    if (data?.errors) Object.assign(errors, data.errors)
    else toast.error(data?.message ?? 'Registration failed.')
  } finally {
    loading.value = false
  }
}
</script>