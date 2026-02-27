<script setup>
import { ref, computed } from 'vue'
import { ChevronRight, ChevronLeft, Eye, EyeOff } from 'lucide-vue-next'

const props = defineProps({ modelValue: Object })
const emit  = defineEmits(['update:modelValue', 'next', 'back'])

const form      = ref({ ...props.modelValue })
const showPass  = ref(false)
const submitted = ref(false)

function update(field, value) {
  form.value[field] = value
  emit('update:modelValue', { ...form.value })
}

const errors = computed(() => {
  if (!submitted.value) return {}
  const e = {}
  if (!form.value.name)    e.name = 'Name is required.'
  if (!form.value.email)   e.email = 'Email is required.'
  else if (!/\S+@\S+\.\S+/.test(form.value.email)) e.email = 'Enter a valid email.'
  if (!form.value.password) e.password = 'Password is required.'
  else if (form.value.password.length < 8) e.password = 'Minimum 8 characters.'
  if (form.value.password !== form.value.password_confirmation) e.confirm = 'Passwords do not match.'
  return e
})

function next() {
  submitted.value = true
  if (Object.keys(errors.value).length === 0) emit('next')
}
</script>

<template>
  <div>
    <h2 class="text-lg font-semibold text-gray-800 mb-1">Super Admin Account</h2>
    <p class="text-sm text-gray-500 mb-5">
      This account will have full access to the EasyHCS admin panel.
    </p>

    <div class="space-y-4">
      <!-- Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
        <input
          type="text"
          :value="form.name"
          @input="update('name', $event.target.value)"
          class="w-full rounded-lg text-sm focus:ring-blue-500"
          :class="errors.name ? 'border-red-400' : 'border-gray-300'"
          placeholder="Administrator"
        />
        <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
        <input
          type="email"
          :value="form.email"
          @input="update('email', $event.target.value)"
          class="w-full rounded-lg text-sm focus:ring-blue-500"
          :class="errors.email ? 'border-red-400' : 'border-gray-300'"
          placeholder="admin@easyhcs.com"
        />
        <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email }}</p>
      </div>

      <!-- Password -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <div class="relative">
          <input
            :type="showPass ? 'text' : 'password'"
            :value="form.password"
            @input="update('password', $event.target.value)"
            class="w-full rounded-lg text-sm pr-10 focus:ring-blue-500"
            :class="errors.password ? 'border-red-400' : 'border-gray-300'"
            placeholder="Minimum 8 characters"
          />
          <button
            type="button"
            @click="showPass = !showPass"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
          >
            <Eye v-if="!showPass" :size="16" />
            <EyeOff v-else        :size="16" />
          </button>
        </div>
        <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password }}</p>
      </div>

      <!-- Confirm password -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
        <input
          type="password"
          :value="form.password_confirmation"
          @input="update('password_confirmation', $event.target.value)"
          class="w-full rounded-lg text-sm focus:ring-blue-500"
          :class="errors.confirm ? 'border-red-400' : 'border-gray-300'"
          placeholder="Repeat password"
        />
        <p v-if="errors.confirm" class="mt-1 text-xs text-red-600">{{ errors.confirm }}</p>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
      <button @click="emit('back')" class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
        <ChevronLeft :size="16" /> Back
      </button>
      <button
        @click="next"
        class="flex items-center gap-2 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
      >
        Continue <ChevronRight :size="16" />
      </button>
    </div>
  </div>
</template>