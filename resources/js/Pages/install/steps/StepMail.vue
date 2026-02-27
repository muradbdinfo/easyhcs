<script setup>
import { ref } from 'vue'
import { ChevronRight, ChevronLeft, Info } from 'lucide-vue-next'

const props = defineProps({ modelValue: Object })
const emit  = defineEmits(['update:modelValue', 'next', 'back'])

const form = ref({ ...props.modelValue })

function update(field, value) {
  form.value[field] = value
  emit('update:modelValue', { ...form.value })
}
</script>

<template>
  <div>
    <h2 class="text-lg font-semibold text-gray-800 mb-1">Mail Configuration</h2>
    <p class="text-sm text-gray-500 mb-2">
      Used for notifications, PR approvals, and subscription emails.
    </p>

    <!-- Mailtrap tip -->
    <div class="flex items-start gap-2 bg-blue-50 border border-blue-100 rounded-lg p-3 text-sm text-blue-800 mb-5">
      <Info :size="15" class="shrink-0 mt-0.5" />
      <span>
        For development use <strong>Mailtrap</strong> (mailtrap.io). You can update mail settings
        from tenant settings later.
      </span>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <!-- Mailer -->
      <div class="col-span-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Mailer</label>
        <select
          :value="form.mailer"
          @change="update('mailer', $event.target.value)"
          class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="smtp">SMTP</option>
          <option value="log">Log (dev only)</option>
          <option value="sendmail">Sendmail</option>
        </select>
      </div>

      <!-- Encryption -->
      <div class="col-span-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Encryption</label>
        <select
          :value="form.encryption"
          @change="update('encryption', $event.target.value)"
          class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="tls">TLS</option>
          <option value="ssl">SSL</option>
          <option value="">None</option>
        </select>
      </div>

      <!-- Host -->
      <div class="col-span-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Host</label>
        <input
          type="text"
          :value="form.host"
          @input="update('host', $event.target.value)"
          class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
          placeholder="smtp.mailtrap.io"
        />
      </div>

      <!-- Port -->
      <div class="col-span-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
        <input
          type="number"
          :value="form.port"
          @input="update('port', parseInt($event.target.value))"
          class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
          placeholder="2525"
        />
      </div>

      <!-- Username -->
      <div class="col-span-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input
          type="text"
          :value="form.username"
          @input="update('username', $event.target.value)"
          class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
        />
      </div>

      <!-- Password -->
      <div class="col-span-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input
          type="password"
          :value="form.password"
          @input="update('password', $event.target.value)"
          class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
        />
      </div>

      <!-- From address -->
      <div class="col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">From Address</label>
        <input
          type="email"
          :value="form.from_address"
          @input="update('from_address', $event.target.value)"
          class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
          placeholder="noreply@easyhcs.local"
        />
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
      <button @click="emit('back')" class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
        <ChevronLeft :size="16" /> Back
      </button>
      <button
        @click="emit('next')"
        class="flex items-center gap-2 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
      >
        Continue <ChevronRight :size="16" />
      </button>
    </div>
  </div>
</template>