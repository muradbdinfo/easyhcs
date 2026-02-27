import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useNotificationStore = defineStore('notifications', () => {
    // ─── State ────────────────────────────────────────────────────
    const notifications = ref([]);
    const unreadCount   = ref(0);
    const loading       = ref(false);
    let   pollInterval  = null;

    // ─── Getters ──────────────────────────────────────────────────
    const unreadNotifications = computed(() =>
        notifications.value.filter(n => !n.read_at)
    );

    const recentNotifications = computed(() =>
        notifications.value.slice(0, 10)
    );

    // ─── Actions ──────────────────────────────────────────────────

    async function fetchNotifications() {
        loading.value = true;
        try {
            const { data } = await axios.get(route('api.notifications.index'));
            notifications.value = data.data ?? [];
            unreadCount.value   = data.unread_count ?? 0;
        } catch (e) {
            console.error('Failed to fetch notifications', e);
        } finally {
            loading.value = false;
        }
    }

    async function markAsRead(id) {
        try {
            await axios.post(route('notifications.read', { id }));
            const n = notifications.value.find(n => n.id === id);
            if (n) {
                n.read_at = new Date().toISOString();
                unreadCount.value = Math.max(0, unreadCount.value - 1);
            }
        } catch (e) {
            console.error('Failed to mark notification as read', e);
        }
    }

    async function markAllRead() {
        try {
            await axios.post(route('notifications.read-all'));
            notifications.value.forEach(n => {
                n.read_at = new Date().toISOString();
            });
            unreadCount.value = 0;
        } catch (e) {
            console.error('Failed to mark all as read', e);
        }
    }

    function startPolling(intervalMs = 30000) {
        if (pollInterval) return; // already polling
        fetchNotifications();
        pollInterval = setInterval(fetchNotifications, intervalMs);
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    return {
        notifications,
        unreadCount,
        loading,
        unreadNotifications,
        recentNotifications,
        fetchNotifications,
        markAsRead,
        markAllRead,
        startPolling,
        stopPolling,
    };
});