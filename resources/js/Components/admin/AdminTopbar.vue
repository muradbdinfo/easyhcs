<template>
    <header class="h-16 bg-white border-b border-gray-200 flex items-center px-4 gap-4 shrink-0">
        <!-- Hamburger -->
        <button
            class="text-gray-500 hover:text-gray-900 lg:hidden"
            @click="$emit('toggle-sidebar')"
        >
            <Menu :size="22" />
        </button>

        <!-- Title -->
        <span class="font-semibold text-gray-800 hidden sm:block">EasyHCS Admin</span>

        <!-- Spacer -->
        <div class="flex-1" />

        <!-- Env badge -->
        <span
            v-if="isDev"
            class="hidden sm:inline-flex text-xs font-medium bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full"
        >
            DEV
        </span>

        <!-- Notifications -->
        <button class="relative text-gray-500 hover:text-gray-900">
            <Bell :size="20" />
        </button>

        <!-- User menu -->
        <div class="relative" ref="menuRef">
            <button
                class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900"
                @click="menuOpen = !menuOpen"
            >
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center">
                    <span class="text-white text-xs font-semibold">{{ initials }}</span>
                </div>
                <ChevronDown :size="16" />
            </button>

            <!-- Dropdown -->
            <Transition name="dropdown">
                <div
                    v-if="menuOpen"
                    class="absolute right-0 top-10 w-48 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50"
                >
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-sm font-medium text-gray-900">{{ auth.user?.name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth.user?.email }}</p>
                    </div>
                    <button
                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                        @click="handleLogout"
                    >
                        <LogOut :size="15" />
                        Logout
                    </button>
                </div>
            </Transition>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { Menu, Bell, ChevronDown, LogOut } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import { useToast }     from '@/composables/useToast'

defineEmits(['toggle-sidebar'])

const auth    = useAuthStore()
const router  = useRouter()
const toast   = useToast()
const menuOpen = ref(false)
const menuRef  = ref(null)
const isDev    = import.meta.env.DEV

const initials = computed(() => {
    const name = auth.user?.name ?? 'A'
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
})

async function handleLogout() {
    await auth.logout()
    toast.info('Logged out.')
    router.push({ name: 'login' })
}

function handleClickOutside(e) {
    if (menuRef.value && !menuRef.value.contains(e.target)) menuOpen.value = false
}
onMounted(()  => document.addEventListener('mousedown', handleClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside))
</script>

<style scoped>
.dropdown-enter-active, .dropdown-leave-active { transition: opacity 0.15s, transform 0.15s; }
.dropdown-enter-from, .dropdown-leave-to       { opacity: 0; transform: translateY(-4px); }
</style>