<template>
    <!-- Sidebar container -->
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-30 flex flex-col w-64 bg-gray-900 text-white',
            'transition-transform duration-300 ease-in-out',
            'lg:relative lg:translate-x-0 lg:flex',
            open ? 'translate-x-0' : '-translate-x-full',
        ]"
    >
        <!-- Header -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-700 shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-sm">H</span>
                </div>
                <span class="font-semibold text-white">EasyHCS Admin</span>
            </div>
            <button class="lg:hidden text-gray-400 hover:text-white" @click="$emit('close')">
                <X :size="20" />
            </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-6">
            <div v-for="group in navGroups" :key="group.label">
                <p class="px-3 mb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    {{ group.label }}
                </p>
                <div class="space-y-0.5">
                    <RouterLink
                        v-for="item in group.items"
                        :key="item.name"
                        :to="item.to"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                            isActive(item.to)
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                        ]"
                    >
                        <component :is="item.icon" :size="18" class="shrink-0" />
                        {{ item.label }}
                    </RouterLink>
                </div>
            </div>
        </nav>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-700 text-xs text-gray-500">
            EasyHCS v1.0 · 2026
        </div>
    </aside>
</template>

<script setup>
import { RouterLink, useRoute } from 'vue-router'
import {
    LayoutDashboard, Building2, PlusCircle,
    CreditCard, RefreshCw, ArrowLeftRight, RotateCcw,
    Wallet, Key, Settings, Users, FileText, Activity, X,
} from 'lucide-vue-next'

defineProps({ open: Boolean })
defineEmits(['close'])

const route = useRoute()

const navGroups = [
    {
        label: 'Overview',
        items: [
            { label: 'Dashboard',   icon: LayoutDashboard, to: { name: 'admin.dashboard' } },
        ],
    },
    {
        label: 'Tenants',
        items: [
            { label: 'All Tenants',    icon: Building2,   to: { name: 'admin.tenants' } },
            { label: 'Create Tenant',  icon: PlusCircle,  to: { name: 'admin.tenants.create' } },
        ],
    },
    {
        label: 'Plans & Billing',
        items: [
            { label: 'Plans',          icon: CreditCard,       to: { name: 'admin.plans' } },
            { label: 'Subscriptions',  icon: RefreshCw,        to: { name: 'admin.subscriptions' } },
            { label: 'Transactions',   icon: ArrowLeftRight,   to: { name: 'admin.transactions' } },
            { label: 'Refunds',        icon: RotateCcw,        to: { name: 'admin.refunds' } },
        ],
    },
    {
        label: 'Gateways',
        items: [
            { label: 'Payment Gateways', icon: Wallet, to: { name: 'admin.gateways' } },
        ],
    },
    {
        label: 'Licenses',
        items: [
            { label: 'License Keys', icon: Key, to: { name: 'admin.licenses' } },
        ],
    },
    {
        label: 'System',
        items: [
            { label: 'Settings',    icon: Settings,  to: { name: 'admin.settings' } },
            { label: 'Admin Users', icon: Users,     to: { name: 'admin.users' } },
            { label: 'Audit Log',   icon: FileText,  to: { name: 'admin.audit-log' } },
            { label: 'Health',      icon: Activity,  to: { name: 'admin.health' } },
        ],
    },
]

function isActive(to) {
    const resolved = to?.name
    if (!resolved) return false
    // Exact match for dashboard, prefix match for others
    if (resolved === 'admin.dashboard') return route.name === 'admin.dashboard'
    return route.name?.startsWith(resolved) || route.name === resolved
}
</script>