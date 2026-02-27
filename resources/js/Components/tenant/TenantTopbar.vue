<template>
    <header class="h-16 bg-white border-b border-gray-200 flex items-center px-4 gap-4 shrink-0">
        <!-- Hamburger -->
        <button class="text-gray-500 hover:text-gray-900 lg:hidden" @click="$emit('toggle-sidebar')">
            <Menu :size="22" />
        </button>

        <!-- Page title -->
        <span class="font-semibold text-gray-800 hidden sm:block truncate">
            {{ pageTitle }}
        </span>

        <!-- Spacer -->
        <div class="flex-1" />

        <!-- Search -->
        <button class="text-gray-400 hover:text-gray-600 hidden md:block">
            <Search :size="18" />
        </button>

        <!-- Notifications -->
        <div class="relative" ref="notifRef">
            <button
                class="relative text-gray-500 hover:text-gray-900"
                @click="notifOpen = !notifOpen"
            >
                <Bell :size="20" />
                <span
                    v-if="notifications.unreadCount > 0"
                    class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center leading-none"
                >
                    {{ notifications.unreadCount > 9 ? '9+' : notifications.unreadCount }}
                </span>
            </button>

            <!-- Notification dropdown -->
            <Transition name="dropdown">
                <div
                    v-if="notifOpen"
                    class="absolute right-0 top-10 w-80 bg-white border border-gray-200 rounded-lg shadow-xl z-50"
                >
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                        <span class="font-semibold text-sm text-gray-800">Notifications</span>
                        <button
                            v-if="notifications.unreadCount > 0"
                            class="text-xs text-indigo-600 hover:underline"
                            @click="notifications.markAllRead()"
                        >
                            Mark all read
                        </button>
                    </div>
                    <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                        <div
                            v-if="notifications.notifications.length === 0"
                            class="px-4 py-6 text-center text-sm text-gray-400"
                        >
                            No notifications
                        </div>
                        <button
                            v-for="n in notifications.notifications.slice(0, 8)"
                            :key="n.id"
                            class="w-full text-left px-4 py-3 hover:bg-gray-50 transition-colors"
                            :class="{ 'bg-indigo-50': !n.read_at }"
                            @click="notifications.markAsRead(n.id)"
                        >
                            <p class="text-sm text-gray-800" :class="{ 'font-medium': !n.read_at }">
                                {{ n.data?.message ?? n.type }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ timeAgo(n.created_at) }}</p>
                        </button>
                    </div>
                    <div class="px-4 py-2 border-t border-gray-100">
                        <RouterLink
                            :to="{ name: 'notifications' }"
                            class="text-xs text-indigo-600 hover:underline"
                            @click="notifOpen = false"
                        >
                            View all notifications
                        </RouterLink>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- Plan chip -->
        <span
            v-if="settings.currentPlan"
            class="hidden sm:inline-flex items-center text-xs font-medium bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full"
        >
            {{ settings.currentPlan }}
        </span>

        <!-- User dropdown -->
        <div class="relative" ref="menuRef">
            <button
                class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900"
                @click="menuOpen = !menuOpen"
            >
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center">
                    <span class="text-white text-xs font-semibold">{{ initials }}</span>
                </div>
                <span class="hidden md:block max-w-[120px] truncate">{{ auth.user?.name }}</span>
                <ChevronDown :size="16" />
            </button>

            <Transition name="dropdown">
                <div
                    v-if="menuOpen"
                    class="absolute right-0 top-10 w-52 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50"
                >
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-sm font-medium text-gray-900">{{ auth.user?.name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth.user?.email }}</p>
                        <div class="flex flex-wrap gap-1 mt-1">
                            <span
                                v-for="role in auth.roles"
                                :key="role"
                                class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded"
                            >
                                {{ role }}
                            </span>
                        </div>
                    </div>
                    <RouterLink
                        :to="{ name: 'settings' }"
                        class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                        @click="menuOpen = false"
                    >
                        <Settings :size="15" />
                        Settings
                    </RouterLink>
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
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { Menu, Bell, Search, ChevronDown, LogOut, Settings } from 'lucide-vue-next'
import { useAuthStore }          from '@/stores/auth'
import { useSettingsStore }      from '@/stores/settings'
import { useNotificationsStore } from '@/stores/notifications'
import { useToast }              from '@/composables/useToast'

defineEmits(['toggle-sidebar'])

const auth          = useAuthStore()
const settings      = useSettingsStore()
const notifications = useNotificationsStore()
const router        = useRouter()
const route         = useRoute()
const toast         = useToast()

const menuOpen  = ref(false)
const notifOpen = ref(false)
const menuRef   = ref(null)
const notifRef  = ref(null)

const initials = computed(() => {
    const name = auth.user?.name ?? 'U'
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
})

const pageTitle = computed(() => {
    return route.meta?.title ?? String(route.name ?? '').replace(/[-_.]/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
})

async function handleLogout() {
    await auth.logout()
    toast.info('Logged out.')
    router.push({ name: 'login' })
}

function timeAgo(dateStr) {
    const diff = Date.now() - new Date(dateStr).getTime()
    const mins = Math.floor(diff / 60000)
    if (mins < 1)   return 'just now'
    if (mins < 60)  return `${mins}m ago`
    const hrs = Math.floor(mins / 60)
    if (hrs < 24)   return `${hrs}h ago`
    return `${Math.floor(hrs / 24)}d ago`
}

function handleClickOutside(e) {
    if (menuRef.value  && !menuRef.value.contains(e.target))  menuOpen.value  = false
    if (notifRef.value && !notifRef.value.contains(e.target)) notifOpen.value = false
}
onMounted(()  => document.addEventListener('mousedown', handleClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside))
</script>

<style scoped>
.dropdown-enter-active, .dropdown-leave-active { transition: opacity 0.15s, transform 0.15s; }
.dropdown-enter-from, .dropdown-leave-to       { opacity: 0; transform: translateY(-4px); }
</style>