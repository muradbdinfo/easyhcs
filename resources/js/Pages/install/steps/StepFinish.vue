<script setup>
import { computed } from 'vue'
import { ChevronLeft, Rocket, CheckCircle, XCircle, Loader } from 'lucide-vue-next'

const props = defineProps({
  formData:   Object,
  installing: Boolean,
  installed:  Boolean,
  error:      String,
})
const emit = defineEmits(['install', 'back'])

const summary = computed(() => [
  { label: 'Database',  value: `${props.formData.database.host}/${props.formData.database.database}` },
  { label: 'Admin',     value: props.formData.admin.email },
  { label: 'App Name',  value: props.formData.app.name },
  { label: 'App URL',   value: props.formData.app.url },
  { label: 'Timezone',  value: props.formData.app.timezone },
  { label: 'Mail Host', value: `${props.formData.mail.host}:${props.formData.mail.port}` },
])
</script>

<template>
  <div>

    <!-- Success screen -->
    <div v-if="installed" class="text-center py-8">
      <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 mb-4">
        <CheckCircle :size="44" class="text-green-600" />
      </div>
      <h2 class="text-2xl font-bold text-gray-800 mb-2">Installation Complete!</h2>
      <p class="text-gray-500 mb-3">EasyHCS has been installed successfully.</p>
      <p class="text-sm text-blue-600 font-medium animate-pulse">
        Redirecting to login page…
      </p>
    </div>

    <!-- Pre-install screen -->
    <div v-else>
      <h2 class="text-lg font-semibold text-gray-800 mb-1">Review &amp; Install</h2>
      <p class="text-sm text-gray-500 mb-4">
        Review your settings. Clicking <strong>Install Now</strong> will write
        <code class="bg-gray-100 px-1 rounded text-xs">.env</code>,
        run migrations, seed data, and create the super-admin account.
      </p>

      <!-- Summary table -->
      <div class="bg-gray-50 rounded-xl overflow-hidden border border-gray-200 mb-5">
        <div
          v-for="item in summary"
          :key="item.label"
          class="flex items-center px-4 py-2.5 border-b border-gray-100 last:border-0"
        >
          <span class="text-xs font-medium text-gray-500 w-32">{{ item.label }}</span>
          <span class="text-sm text-gray-800 font-mono">{{ item.value }}</span>
        </div>
      </div>

      <!-- What will happen -->
      <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-5 text-xs text-blue-900 space-y-1">
        <p class="font-semibold text-sm mb-2">This will:</p>
        <p>✓ Write <code>.env</code> and generate APP_KEY</p>
        <p>✓ Run system database migrations</p>
        <p>✓ Seed plans, payment gateways, and default data</p>
        <p>✓ Create your super-admin account</p>
        <p>✓ Redirect you to the login page</p>
      </div>

      <!-- Error message -->
      <div
        v-if="error"
        class="flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-800 mb-4"
      >
        <XCircle :size="16" class="shrink-0 mt-0.5" />
        <div>
          <p class="font-medium">Installation failed</p>
          <p class="text-xs mt-0.5 font-mono break-all">{{ error }}</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-between pt-4 border-t border-gray-100">
        <button
          @click="emit('back')"
          :disabled="installing"
          class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-40"
        >
          <ChevronLeft :size="16" /> Back
        </button>
        <button
          @click="emit('install')"
          :disabled="installing"
          class="flex items-center gap-2 px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <Loader v-if="installing" :size="16" class="animate-spin" />
          <Rocket v-else            :size="16" />
          {{ installing ? 'Installing…' : 'Install Now' }}
        </button>
      </div>
    </div>

  </div>
</template>