import { defineStore } from "pinia";
import { axiosInstance } from "@/plugins/axios";
import { handleError } from "@/helpers/errorHelper";

export const useNotificationStore = defineStore("notification", {
  state: () => ({
    notifications: [],
    loading: false,
    error: null,
    unreadCountServer: 0,
    pollIntervalId: null,
  }),
  getters: {
    unreadCount: (state) => state.unreadCountServer,
  },
  actions: {
    async fetchNotifications() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axiosInstance.get("/notifications");
        this.notifications = response?.data?.data?.notifications ?? [];
        this.unreadCountServer = response?.data?.data?.unreadCount ?? 0;
      } catch (error) {
        this.error = handleError(error);
      } finally {
        this.loading = false;
      }
    },

    async markAllRead() {
      try {
        await axiosInstance.post("/notifications/read-all");
      } catch (error) {
        this.error = handleError(error);
      } finally {
        await this.fetchNotifications();
      }
    },

    async markRead(id) {
      try {
        await axiosInstance.post(`/notifications/${id}/read`);
      } catch (error) {
        this.error = handleError(error);
      } finally {
        await this.fetchNotifications();
      }
    },

    async clearAll() {
      try {
        await axiosInstance.post("/notifications/clear");
      } catch (error) {
        this.error = handleError(error);
      } finally {
        await this.fetchNotifications();
      }
    },

    startPolling(intervalMs = 10000) {
      if (this.pollIntervalId) return;

      this.fetchNotifications();

      this.pollIntervalId = window.setInterval(() => {
        this.fetchNotifications();
      }, intervalMs);
    },

    stopPolling() {
      if (!this.pollIntervalId) return;
      window.clearInterval(this.pollIntervalId);
      this.pollIntervalId = null;
    },
  },
});
