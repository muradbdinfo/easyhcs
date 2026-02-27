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

const moduleRoots = {
    pharmacy   : { label: 'Pharmacy',   to: { name: 'pharmacy.pos' } },
    diagnostic : { label: 'Diagnostic', to: { name: 'diagnostic.test-orders' } },
    hospital   : { label: 'Hospital',   to: { name: 'hospital.appointments' } },
    accounts   : { label: 'Accounts',   to: { name: 'accounts' } },
}

const routeLabels = {
    'dashboard'                  : 'Dashboard',
    'patients'                   : 'Patients',
    'users'                      : 'Users & Roles',
    'settings'                   : 'Settings',
    'audit-log'                  : 'Audit Log',
    'billing'                    : 'Billing & Plan',
    'notifications'              : 'Notifications',
    'pharmacy.pos'               : 'POS',
    'pharmacy.medicines'         : 'Medicines',
    'pharmacy.purchase-requests' : 'Purchase Requests',
    'pharmacy.purchase-orders'   : 'Purchase Orders',
    'pharmacy.stock-alerts'      : 'Stock Alerts',
    'pharmacy.suppliers'         : 'Suppliers',
    'diagnostic.test-orders'     : 'Test Orders',
    'diagnostic.test-catalog'    : 'Test Catalog',
    'diagnostic.samples'         : 'Sample Collection',
    'diagnostic.results'         : 'Results',
    'hospital.appointments'      : 'Appointments',
    'hospital.queue'             : 'Queue',
    'hospital.patients'          : 'Patients',
    'hospital.admissions'        : 'Admissions',
    'hospital.wards'             : 'Wards / Beds',
    'hospital.ot'                : 'OT',
    'hospital.doctors'           : 'Doctors',
    'hospital.emr'               : 'EMR',
    'hospital.prescriptions'     : 'Prescriptions',
    'accounts'                   : 'Accounts',
    'accounts.invoices'          : 'Invoices',
    'accounts.insurance'         : 'Insurance Claims',
    'accounts.reports'           : 'Reports',
}

const crumbs = computed(() => {
    const list    = [{ label: 'Home', to: { name: 'dashboard' } }]
    const name    = route.name ?? ''
    const module  = route.meta?.module

    if (module && moduleRoots[module] && name !== moduleRoots[module].to.name) {
        list.push(moduleRoots[module])
    }

    const label = routeLabels[name]
    if (label && name !== 'dashboard') list.push({ label })

    return list
})
</script>