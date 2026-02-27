<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import AuthLayout       from '@/Layouts/AuthLayout.vue'
import StepRequirements from './steps/StepRequirements.vue'
import StepDatabase     from './steps/StepDatabase.vue'
import StepAdmin        from './steps/StepAdmin.vue'
import StepAppConfig    from './steps/StepAppConfig.vue'
import StepMail         from './steps/StepMail.vue'
import StepFinish       from './steps/StepFinish.vue'

const currentStep = ref(1)
const installing  = ref(false)
const installed   = ref(false)
const error       = ref(null)
const TOTAL_STEPS = 6

const formData = ref({
  database: {
    host:     '127.0.0.1',
    port:     3306,
    database: 'easyhcs_system',
    username: 'root',
    password: '',
  },
  admin: {
    name:                  '',
    email:                 '',
    password:              '',
    password_confirmation: '',
  },
  app: {
    name:     'EasyHCS',
    url:      window.location.origin,
    timezone: 'Asia/Dhaka',
  },
  mail: {
    mailer:       'smtp',
    host:         'smtp.mailtrap.io',
    port:         2525,
    username:     '',
    password:     '',
    encryption:   'tls',
    from_address: 'noreply@easyhcs.local',
  },
})

const steps = [
  { number: 1, label: 'Requirements' },
  { number: 2, label: 'Database' },
  { number: 3, label: 'Admin Account' },
  { number: 4, label: 'App Settings' },
  { number: 5, label: 'Mail' },
  { number: 6, label: 'Install' },
]

const progressPercent = computed(() =>
  Math.round(((currentStep.value - 1) / (TOTAL_STEPS - 1)) * 100)
)

function next()    { if (currentStep.value < TOTAL_STEPS) currentStep.value++ }
function back()    { if (currentStep.value > 1) currentStep.value-- }
function goStep(n) { if (n < currentStep.value) currentStep.value = n }

async function runInstall() {
  installing.value = true
  error.value      = null

  try {
    const { data } = await axios.post('/install/run', formData.value)

    if (data.success) {
      installed.value = true
      // Hard redirect after 1.5s — forces full PHP reload with new .env + APP_KEY
      setTimeout(() => {
        window.location.href = '/login'
      }, 1500)
    } else {
      error.value      = data.message
      installing.value = false
    }
  } catch (e) {
    error.value      = e.response?.data?.message ?? e.message
    installing.value = false
  }
}
</script>

<template>
  <AuthLayout>
    <div class="w-full max-w-2xl">
      <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

        <!-- Title bar -->
        <div class="bg-blue-600 px-8 py-6">
          <h1 class="text-white text-2xl font-bold">EasyHCS Installation Wizard</h1>
          <p class="text-blue-100 text-sm mt-1">Complete the steps below to set up your system.</p>
        </div>

        <!-- Progress -->
        <div class="px-8 pt-6">
          <div class="flex justify-between mb-2">
            <button
              v-for="step in steps"
              :key="step.number"
              @click="goStep(step.number)"
              class="flex flex-col items-center gap-1 flex-1"
              :class="step.number > currentStep ? 'pointer-events-none' : 'cursor-pointer'"
            >
              <span
                class="w-7 h-7 rounded-full text-xs font-bold flex items-center justify-center transition-colors"
                :class="{
                  'bg-blue-600 text-white':    step.number === currentStep,
                  'bg-green-500 text-white':   step.number < currentStep,
                  'bg-gray-200 text-gray-500': step.number > currentStep,
                }"
              >{{ step.number < currentStep ? '✓' : step.number }}</span>
              <span
                class="text-[10px] hidden sm:block font-medium"
                :class="{
                  'text-blue-600':  step.number === currentStep,
                  'text-green-600': step.number < currentStep,
                  'text-gray-400':  step.number > currentStep,
                }"
              >{{ step.label }}</span>
            </button>
          </div>

          <div class="h-1.5 bg-gray-100 rounded-full mt-1 mb-6">
            <div
              class="h-1.5 bg-blue-600 rounded-full transition-all duration-500"
              :style="{ width: progressPercent + '%' }"
            />
          </div>
        </div>

        <!-- Step content -->
        <div class="px-8 pb-8">
          <StepRequirements v-if="currentStep === 1"
            @next="next"
          />
          <StepDatabase v-else-if="currentStep === 2"
            v-model="formData.database"
            @next="next" @back="back"
          />
          <StepAdmin v-else-if="currentStep === 3"
            v-model="formData.admin"
            @next="next" @back="back"
          />
          <StepAppConfig v-else-if="currentStep === 4"
            v-model="formData.app"
            @next="next" @back="back"
          />
          <StepMail v-else-if="currentStep === 5"
            v-model="formData.mail"
            @next="next" @back="back"
          />
          <StepFinish v-else-if="currentStep === 6"
            :form-data="formData"
            :installing="installing"
            :installed="installed"
            :error="error"
            @install="runInstall"
            @back="back"
          />
        </div>

      </div>
    </div>
  </AuthLayout>
</template>