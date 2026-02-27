import { defineStore } from 'pinia';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export const useSettingsStore = defineStore('settings', () => {
    const page = usePage();

    // ─── Tenant data from Inertia shared props ────────────────────
    const tenant = computed(() => page.props.tenant ?? null);

    const tenantName     = computed(() => tenant.value?.name ?? 'EasyHCS');
    const tenantCurrency = computed(() => tenant.value?.currency ?? 'BDT');
    const tenantTimezone = computed(() => tenant.value?.timezone ?? 'Asia/Dhaka');
    const planStatus     = computed(() => tenant.value?.plan_status ?? null);
    const modulesEnabled = computed(() => tenant.value?.modules_enabled ?? ['core', 'accounts']);

    // ─── Getters ──────────────────────────────────────────────────

    function isModuleEnabled(moduleName) {
        return modulesEnabled.value.includes(moduleName);
    }

    const isPlanActive = computed(() =>
        ['active', 'trialing'].includes(planStatus.value)
    );

    const isTrialing = computed(() => planStatus.value === 'trialing');

    const trialEndsAt  = computed(() => tenant.value?.trial_ends_at ?? null);
    const planExpiresAt = computed(() => tenant.value?.plan_expires_at ?? null);

    // ─── Currency formatter ───────────────────────────────────────

    function formatCurrency(amount, currency = null) {
        const curr = currency ?? tenantCurrency.value;
        return new Intl.NumberFormat('en-BD', {
            style: 'currency',
            currency: curr === 'BDT' ? 'BDT' : curr,
            minimumFractionDigits: 2,
        }).format(amount ?? 0);
    }

    return {
        tenant,
        tenantName,
        tenantCurrency,
        tenantTimezone,
        planStatus,
        modulesEnabled,
        isModuleEnabled,
        isPlanActive,
        isTrialing,
        trialEndsAt,
        planExpiresAt,
        formatCurrency,
    };
});