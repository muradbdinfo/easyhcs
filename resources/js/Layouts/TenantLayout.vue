<template>
    <div class="flex h-screen overflow-hidden bg-gray-100">

        <!-- Sidebar overlay (mobile) -->
        <Transition name="fade">
            <div
                v-if="sidebarOpen && isMobile"
                class="fixed inset-0 z-20 bg-black/50 lg:hidden"
                @click="sidebarOpen = false"
            />
        </Transition>

        <!-- Sidebar -->
        <TenantSidebar
            :open="sidebarOpen"
            @close="sidebarOpen = false"
        />

        <!-- Main content -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
            <TenantTopbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

            <main class="flex-1 overflow-y-auto p-6">
                <TenantBreadcrumb class="mb-4" />
                <RouterView />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterView } from 'vue-router'
import TenantSidebar    from '@/components/tenant/TenantSidebar.vue'
import TenantTopbar     from '@/components/tenant/TenantTopbar.vue'
import TenantBreadcrumb from '@/components/tenant/TenantBreadcrumb.vue'
import { useNotificationsStore } from '@/stores/notifications'

const sidebarOpen     = ref(true)
const isMobile        = ref(false)
const notifications   = useNotificationsStore()

function checkMobile() {
    isMobile.value = window.innerWidth < 1024
    if (isMobile.value) sidebarOpen.value = false
    else                sidebarOpen.value = true
}

onMounted(() => {
    checkMobile()
    window.addEventListener('resize', checkMobile)
    notifications.startPolling()
})
onUnmounted(() => {
    window.removeEventListener('resize', checkMobile)
    notifications.stopPolling()
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }
</style>