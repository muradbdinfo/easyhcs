import { defineStore } from 'pinia'
import axios from 'axios'

export const useNotificationsStore = defineStore('notifications', {
    state: () => ({
        notifications : [],
        unreadCount   : 0,
        loading       : false,
        /** setInterval handle — kept to allow cleanup on logout */
        _pollTimer    : null,
    }),

    getters: {
        hasUnread : (state) => state.unreadCount > 0,
        unreadList: (state) => state.notifications.filter(n => !n.read_at),
    },

    actions: {
        /** Fetch the latest notifications from the API */
        async fetchNotifications() {
            if (this.loading) return
            this.loading = true
            try {
                const { data } = await axios.get('/api/notifications')
                this.notifications = data.data        ?? []
                this.unreadCount   = data.unread_count ?? 0
            } catch (_) {
                // Silently fail — polling will retry in 30s
            } finally {
                this.loading = false
            }
        },

        /** Mark a single notification as read */
        async markAsRead(id) {
            try {
                await axios.patch(`/api/notifications/${id}/read`)
                const notif = this.notifications.find(n => n.id === id)
                if (notif && !notif.read_at) {
                    notif.read_at = new Date().toISOString()
                    this.unreadCount = Math.max(0, this.unreadCount - 1)
                }
            } catch (_) {}
        },

        /** Mark all notifications as read */
        async markAllRead() {
            try {
                await axios.post('/api/notifications/read-all')
                this.notifications.forEach(n => {
                    n.read_at = n.read_at ?? new Date().toISOString()
                })
                this.unreadCount = 0
            } catch (_) {}
        },

        /** Delete a single notification */
        async deleteNotification(id) {
            try {
                await axios.delete(`/api/notifications/${id}`)
                this.notifications = this.notifications.filter(n => n.id !== id)
                // Recalculate unread
                this.unreadCount = this.notifications.filter(n => !n.read_at).length
            } catch (_) {}
        },

        /** Start polling every 30 seconds. Safe to call multiple times. */
        startPolling() {
            if (this._pollTimer) return
            this.fetchNotifications()
            this._pollTimer = setInterval(() => this.fetchNotifications(), 30_000)
        },

        /** Stop polling (called on layout unmount / logout) */
        stopPolling() {
            if (this._pollTimer) {
                clearInterval(this._pollTimer)
                this._pollTimer = null
            }
        },
    },
})