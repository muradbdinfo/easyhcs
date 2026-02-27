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
        <AdminSidebar
            :open="sidebarOpen"
            @close="sidebarOpen = false"
        />

        <!-- Main content -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
            <AdminTopbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

            <main class="flex-1 overflow-y-auto p-6">
                <AdminBreadcrumb class="mb-4" />
                <RouterView />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterView } from 'vue-router'
import AdminSidebar    from '@/components/admin/AdminSidebar.vue'
import AdminTopbar     from '@/components/admin/AdminTopbar.vue'
import AdminBreadcrumb from '@/components/admin/AdminBreadcrumb.vue'

const sidebarOpen = ref(true)
const isMobile    = ref(false)

function checkMobile() {
    isMobile.value = window.innerWidth < 1024
    if (isMobile.value) sidebarOpen.value = false
    else                sidebarOpen.value = true
}

onMounted(() => {
    checkMobile()
    window.addEventListener('resize', checkMobile)
})
onUnmounted(() => window.removeEventListener('resize', checkMobile))
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }
</style>