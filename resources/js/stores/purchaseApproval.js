import { defineStore } from 'pinia'

/**
 * Purchase Approval Store — stub for P7.
 * Full implementation comes in P10 (Pharmacy module).
 */
export const usePurchaseApprovalStore = defineStore('purchaseApproval', {
    state: () => ({
        pendingPRs : [],
        myPRs      : [],
    }),

    getters: {
        pendingManagerCount : (state) => state.pendingPRs.filter(pr => pr.status === 'pending_manager').length,
        pendingAdminCount   : (state) => state.pendingPRs.filter(pr => pr.status === 'pending_admin').length,
    },

    actions: {
        // Implemented in P10
        async fetchPendingPRs()           {},
        async submitPR(_payload)          {},
        async approveStep1(_id, _remarks) {},
        async rejectStep1(_id, _remarks)  {},
        async approveStep2(_id, _remarks) {},
        async rejectStep2(_id, _remarks)  {},
        async cancelPR(_id)               {},
    },
})
