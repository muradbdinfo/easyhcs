import { defineStore } from 'pinia';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export const useAuthStore = defineStore('auth', () => {
    const page = usePage();

    // ─── State (derived from Inertia shared data) ─────────────────
    const user        = computed(() => page.props.auth?.user ?? null);
    const isLoggedIn  = computed(() => !!user.value);
    const isSuperAdmin = computed(() => user.value?.is_super_admin === true);
    const isTenantOwner = computed(() => hasRole('tenant-owner'));
    const roles       = computed(() => user.value?.roles ?? []);
    const permissions = computed(() => user.value?.permissions ?? []);

    // ─── Getters ──────────────────────────────────────────────────

    function hasRole(roleName) {
        return roles.value.includes(roleName);
    }

    function hasPermission(permissionName) {
        if (isSuperAdmin.value || isTenantOwner.value) return true;
        return permissions.value.includes(permissionName);
    }

    function hasAnyPermission(permList) {
        if (isSuperAdmin.value || isTenantOwner.value) return true;
        return permList.some(p => permissions.value.includes(p));
    }

    const canApproveStep1 = computed(() => hasPermission('pr-approve-step1'));
    const canApproveStep2 = computed(() => hasPermission('pr-approve-step2'));

    // ─── Actions ──────────────────────────────────────────────────

    async function logout() {
        try {
            await axios.post(route('logout'));
            window.location.href = route('login');
        } catch (e) {
            window.location.href = '/login';
        }
    }

    return {
        user,
        isLoggedIn,
        isSuperAdmin,
        isTenantOwner,
        roles,
        permissions,
        hasRole,
        hasPermission,
        hasAnyPermission,
        canApproveStep1,
        canApproveStep2,
        logout,
    };
});