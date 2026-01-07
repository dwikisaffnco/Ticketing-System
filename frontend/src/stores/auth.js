import { handleError } from "@/helpers/errorHelper";
import { axiosInstance } from "@/plugins/axios";
import Cookies from "js-cookie";
import { defineStore } from "pinia";
import router from "@/router";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    loading: false,
    error: null,
    success: null,
    redirecting: false,
  }),

  getters: {
    token: () => Cookies.get("token"),
    isAuthenticated: (state) => !!state.user || !!Cookies.get("token"),
  },

  actions: {
    /* ================= LOGIN ================= */
    async login(credentials) {
      this.loading = true;
      this.error = null;
      this.redirecting = false;

      try {
        const response = await axiosInstance.post("/login", credentials);
        const { token, user } = response.data.data;

        // simpan token
        Cookies.set("token", token, { path: "/" });
        axiosInstance.defaults.headers.common.Authorization = `Bearer ${token}`;

        // simpan user
        this.user = user;
        this.success = response.data.message;

        // hard refresh to ensure cookies/guards are applied consistently
        this.redirecting = true;
        const target = user.role === "admin" ? { name: "admin.dashboard" } : { name: "app.dashboard" };
        const href = router.resolve(target).href;
        window.location.assign(href);

        return true;
      } catch (error) {
        if (error.response?.status === 401) {
          this.error = "Email atau password salah";
        } else {
          this.error = handleError(error);
        }
        return false;
      } finally {
        if (!this.redirecting) {
          this.loading = false;
        }
      }
    },

    /* ================= CHECK AUTH (PENTING) ================= */
    async checkAuth() {
      const token = Cookies.get("token");
      if (!token) return null;

      axiosInstance.defaults.headers.common.Authorization = `Bearer ${token}`;

      try {
        const response = await axiosInstance.get("/me");
        this.user = response.data.data;
        return this.user;
      } catch (error) {
        Cookies.remove("token", { path: "/" });
        this.user = null;
        throw error;
      }
    },

    /* ================= LOGOUT ================= */
    async logout() {
      this.loading = true;
      try {
        await axiosInstance.post("/logout");
      } catch (_) {
        // ignore error
      } finally {
        Cookies.remove("token", { path: "/" });
        delete axiosInstance.defaults.headers.common.Authorization;
        this.user = null;
        this.loading = false;
        router.push({ name: "login" });
      }
    },

    /* ================= FORGOT PASSWORD ================= */
    async forgotPassword(payload) {
      this.loading = true;
      this.error = null;
      this.success = null;

      try {
        const response = await axiosInstance.post("/forgot-password", payload);
        this.success = response.data.message;
        return true;
      } catch (error) {
        this.error = handleError(error);
        return false;
      } finally {
        this.loading = false;
      }
    },

    /* ================= RESET PASSWORD ================= */
    async resetPassword(payload) {
      this.loading = true;
      this.error = null;
      this.success = null;

      try {
        const response = await axiosInstance.post("/reset-password", payload);
        this.success = response.data.message;
        return true;
      } catch (error) {
        this.error = handleError(error);
        return false;
      } finally {
        this.loading = false;
      }
    },

    /* ================= UPDATE PROFILE ================= */
    async updateProfile(payload) {
      this.loading = true;
      this.error = null;
      this.success = null;

      try {
        const response = await axiosInstance.patch("/me", payload);
        this.user = response.data.data;
        this.success = response.data.message;
        return true;
      } catch (error) {
        this.error = handleError(error);
        return false;
      } finally {
        this.loading = false;
      }
    },

    /* ================= CHANGE PASSWORD ================= */
    async changePassword(payload) {
      this.loading = true;
      this.error = null;
      this.success = null;

      try {
        const response = await axiosInstance.post("/change-password", payload);
        this.success = response.data.message;
        return true;
      } catch (error) {
        this.error = handleError(error);
        return false;
      } finally {
        this.loading = false;
      }
    },
  },
});
