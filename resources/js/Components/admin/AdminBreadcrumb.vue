<template>
    <nav v-if="crumbs.length > 1" class="flex items-center gap-1 text-sm text-gray-500">
        <template v-for="(crumb, i) in crumbs" :key="crumb.label">
            <span v-if="i > 0" class="text-gray-300">/</span>
            <RouterLink
                v-if="crumb.to && i < crumbs.length - 1"
                :to="crumb.to"
                class="hover:text-indigo-600 transition-colors"
            >
                {{ crumb.label }}
            </RouterLink>
            <span v-else class="text-gray-800 font-medium">{{ crumb.label }}</span>
        </template>
    </nav>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

const route = useRoute()

const routeLabels = {
    'admin.dashboard'    : 'Dashboard',
    'admin.tenants'      : 'All Tenants',
    'admin.tenants.create': 'Create Tenant',
    'admin.plans'        : 'Plans',
    'admin.subscriptions': 'Subscriptions',
    'admin.transactions' : 'Transactions',
    'admin.refunds'      : 'Refunds',
    'admin.gateways'     : 'Payment Gateways',
    'admin.licenses'     : 'License Keys',
    'admin.settings'     : 'Settings',
    'admin.users'        : 'Admin Users',
    'admin.audit-log'    : 'Audit Log',
    'admin.health'       : 'System Health',
}

const crumbs = computed(() => {
    const list = [{ label: 'Admin', to: { name: 'admin.dashboard' } }]
    const label = routeLabels[route.name]
    if (label && route.name !== 'admin.dashboard') {
        list.push({ label })
    }
    return list
})
</script>