import { defineStore } from "pinia";
import { axiosInstance } from "@/plugins/axios";
import { handleError } from "@/helpers/errorHelper";
import router from "@/router";

export const useTicketStore = defineStore("ticket", {
  state: () => ({
    tickets: [],
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 10,
    },
    loading: false,
    error: null,
    success: null,
  }),

  actions: {
    async fetchTickets(params) {
      this.loading = true;

      try {
        const response = await axiosInstance.get(`ticket`, { params });

        this.tickets = response.data.data;
        this.pagination = response.data.meta;
      } catch (error) {
        this.error = handleError(error);
      } finally {
        this.loading = false;
      }
    },

    async updateTicket(code, payload) {
      this.loading = true;
      this.error = null;
      this.success = null;

      try {
        const response = await axiosInstance.put(`/ticket/${code}`, payload, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });
        this.success = response.data.message;
        return response.data.data;
      } catch (error) {
        this.error = handleError(error);
      } finally {
        this.loading = false;
      }
    },

    async deleteTicket(code) {
      this.loading = true;
      this.error = null;
      this.success = null;

      try {
        const response = await axiosInstance.delete(`/ticket/${code}`);
        this.success = response.data.message;
        return true;
      } catch (error) {
        this.error = handleError(error);
        return false;
      } finally {
        this.loading = false;
      }
    },

    async fetchTicket(code) {
      this.loading = true;

      try {
        const response = await axiosInstance.get(`/ticket/${code}`);

        return response.data.data;
      } catch (error) {
        this.error = handleError(error);
      } finally {
        this.loading = false;
      }
    },

    async createTicket(payload) {
      this.loading = true;

      try {
        const response = await axiosInstance.post("/ticket", payload);

        this.success = response.data.message;

        router.push({ name: "app.dashboard" });
      } catch (error) {
        this.error = handleError(error);
      } finally {
        this.loading = false;
      }
    },

    async createTicketReply(code, payload) {
      this.loading = true;

      try {
        const response = await axiosInstance.post(`/ticket-reply/${code}`, payload);

        this.success = response.data.message;

        return response.data.data;
      } catch (error) {
        this.error = handleError(error);
      } finally {
        this.loading = false;
      }
    },

    async archiveTicket(code) {
      this.loading = true;

      try {
        const response = await axiosInstance.patch(`/ticket/${code}/archive`);
        this.success = response.data.message;
        return response.data.data;
      } catch (error) {
        this.error = handleError(error);
      } finally {
        this.loading = false;
      }
    },

    async unarchiveTicket(code) {
      this.loading = true;

      try {
        const response = await axiosInstance.patch(`/ticket/${code}/unarchive`);
        this.success = response.data.message;
        return response.data.data;
      } catch (error) {
        this.error = handleError(error);
      } finally {
        this.loading = false;
      }
    },
  },
});
