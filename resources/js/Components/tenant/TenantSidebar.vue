<template>
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
            <div class="flex items-center gap-2 min-w-0">
                <img
                    v-if="settings.tenantLogo"
                    :src="settings.tenantLogo"
                    class="w-8 h-8 rounded-lg object-cover"
                    alt="logo"
                />
                <div v-else class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shrink-0">
                    <span class="text-white font-bold text-sm">H</span>
                </div>
                <span class="font-semibold text-white truncate text-sm">{{ settings.tenantName }}</span>
            </div>
            <button class="lg:hidden text-gray-400 hover:text-white shrink-0 ml-2" @click="$emit('close')">
                <X :size="20" />
            </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-5">
            <div v-for="group in visibleGroups" :key="group.label">
                <p class="px-3 mb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    {{ group.label }}
                </p>
                <div class="space-y-0.5">
                    <template v-for="item in group.items" :key="item.name">
                        <!-- Locked module item -->
                        <div
                            v-if="item.module && !settings.isModuleEnabled(item.module)"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 cursor-not-allowed select-none"
                            :title="`Upgrade to unlock ${item.module} module`"
                        >
                            <component :is="item.icon" :size="18" class="shrink-0" />
                            <span class="flex-1">{{ item.label }}</span>
                            <Lock :size="13" class="shrink-0" />
                        </div>

                        <!-- Active nav item -->
                        <RouterLink
                            v-else
                            :to="item.to"
                            :class="[
                                'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                                isActive(item.to)
                                    ? 'bg-indigo-600 text-white'
                                    : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                            ]"
                        >
                            <component :is="item.icon" :size="18" class="shrink-0" />
                            <span class="flex-1">{{ item.label }}</span>
                            <!-- Badge for pending counts -->
                            <span
                                v-if="item.badge && item.badge > 0"
                                class="text-xs bg-red-500 text-white px-1.5 py-0.5 rounded-full min-w-[1.25rem] text-center"
                            >
                                {{ item.badge }}
                            </span>
                        </RouterLink>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Plan chip at bottom -->
        <div class="px-4 py-3 border-t border-gray-700">
            <TenantModuleBadge />
        </div>
    </aside>
</template>

<script setup>
import { computed }      from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import {
    LayoutDashboard, ShoppingCart, Pill, ClipboardList,
    PackageOpen, AlertTriangle, Truck, TestTube, BookOpen,
    Droplets, FileCheck, Calendar, ListOrdered, Users,
    BedDouble, Building2, Scissors, UserCog, FileHeart,
    FileText, BarChart3, Receipt, Shield, TrendingUp,
    Settings, CreditCard, X, Lock,
} from 'lucide-vue-next'
import { useSettingsStore }      from '@/stores/settings'
import { usePurchaseApprovalStore } from '@/stores/purchaseApproval'
import TenantModuleBadge         from '@/Components/tenant/TenantModuleBadge.vue'

defineProps({ open: Boolean })
defineEmits(['close'])

const route    = useRoute()
const settings = useSettingsStore()

// PR badge — will be wired in P10
let prStore = null
try { prStore = usePurchaseApprovalStore() } catch (_) {}
const pendingPRs = computed(() => prStore?.pendingManagerCount ?? 0)

const allGroups = computed(() => [
    {
        label   : 'Main',
        module  : null,
        items   : [
            { label: 'Dashboard', icon: LayoutDashboard, to: { name: 'dashboard' } },
        ],
    },
    {
        label  : 'Pharmacy',
        module : 'pharmacy',
        items  : [
            { label: 'POS',               icon: ShoppingCart,  to: { name: 'pharmacy.pos' },              module: 'pharmacy' },
            { label: 'Medicines',         icon: Pill,          to: { name: 'pharmacy.medicines' },        module: 'pharmacy' },
            { label: 'Purchase Requests', icon: ClipboardList, to: { name: 'pharmacy.purchase-requests' }, module: 'pharmacy', badge: pendingPRs.value },
            { label: 'Purchase Orders',   icon: PackageOpen,   to: { name: 'pharmacy.purchase-orders' },  module: 'pharmacy' },
            { label: 'Stock Alerts',      icon: AlertTriangle, to: { name: 'pharmacy.stock-alerts' },     module: 'pharmacy' },
            { label: 'Suppliers',         icon: Truck,         to: { name: 'pharmacy.suppliers' },        module: 'pharmacy' },
        ],
    },
    {
        label  : 'Diagnostic',
        module : 'diagnostic',
        items  : [
            { label: 'Test Orders',      icon: TestTube,    to: { name: 'diagnostic.test-orders' },  module: 'diagnostic' },
            { label: 'Test Catalog',     icon: BookOpen,    to: { name: 'diagnostic.test-catalog' }, module: 'diagnostic' },
            { label: 'Sample Collection',icon: Droplets,    to: { name: 'diagnostic.samples' },      module: 'diagnostic' },
            { label: 'Results',          icon: FileCheck,   to: { name: 'diagnostic.results' },      module: 'diagnostic' },
        ],
    },
    {
        label  : 'Hospital',
        module : 'hospital',
        items  : [
            { label: 'Appointments',  icon: Calendar,    to: { name: 'hospital.appointments' },  module: 'hospital' },
            { label: 'Queue',         icon: ListOrdered, to: { name: 'hospital.queue' },         module: 'hospital' },
            { label: 'Patients',      icon: Users,       to: { name: 'hospital.patients' },      module: 'hospital' },
            { label: 'Admissions',    icon: BedDouble,   to: { name: 'hospital.admissions' },    module: 'hospital' },
            { label: 'Wards / Beds',  icon: Building2,   to: { name: 'hospital.wards' },         module: 'hospital' },
            { label: 'OT',            icon: Scissors,    to: { name: 'hospital.ot' },            module: 'hospital' },
            { label: 'Doctors',       icon: UserCog,     to: { name: 'hospital.doctors' },       module: 'hospital' },
            { label: 'EMR',           icon: FileHeart,   to: { name: 'hospital.emr' },           module: 'hospital' },
            { label: 'Prescriptions', icon: FileText,    to: { name: 'hospital.prescriptions' }, module: 'hospital' },
        ],
    },
    {
        label  : 'Accounts',
        module : 'accounts',
        items  : [
            { label: 'Accounts',          icon: BarChart3,  to: { name: 'accounts' },          module: 'accounts' },
            { label: 'Invoices',          icon: Receipt,    to: { name: 'accounts.invoices' },  module: 'accounts' },
            { label: 'Insurance Claims',  icon: Shield,     to: { name: 'accounts.insurance' }, module: 'accounts' },
            { label: 'Reports',           icon: TrendingUp, to: { name: 'accounts.reports' },   module: 'accounts' },
        ],
    },
    {
        label  : 'System',
        module : null,
        items  : [
            { label: 'Users & Roles', icon: UserCog,    to: { name: 'users' } },
            { label: 'Settings',      icon: Settings,   to: { name: 'settings' } },
            { label: 'Audit Log',     icon: FileText,   to: { name: 'audit-log' } },
            { label: 'Billing & Plan',icon: CreditCard, to: { name: 'billing' } },
        ],
    },
])

/** Show group if: always-visible (null module) OR module exists in enabled list (even if items show lock)
    Core (pharmacy/etc.) section header should always show so user can see "upgrade" prompt. */
const visibleGroups = computed(() => allGroups.value)

function isActive(to) {
    if (!to?.name) return false
    if (to.name === 'dashboard') return route.name === 'dashboard'
    return route.name === to.name
}
</script>