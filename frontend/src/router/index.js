import Admin from "@/layouts/Admin.vue";
import Auth from "@/layouts/Auth.vue";
import { useAuthStore } from "@/stores/auth";
import Dashboard from "@/views/admin/Dashboard.vue";
import AdminProfile from "@/views/admin/Profile.vue";
import AdminSettings from "@/views/admin/Settings.vue";
import AdminCreateUser from "@/views/admin/user/CreateUser.vue";
import AdminEditUser from "@/views/admin/user/EditUser.vue";
import AdminUserList from "@/views/admin/user/UserList.vue";
import AdminRoleList from "@/views/admin/user/RoleList.vue";
import AdminReport from "@/views/admin/Report.vue";
import TicketList from "@/views/admin/ticket/TicketList.vue";
import TicketDetail from "@/views/admin/ticket/TicketDetail.vue";
import TicketArchive from "@/views/admin/ticket/TicketArchive.vue";
import ItAssetsSheet from "@/views/admin/ItAssetsSheet.vue";
import ActivityLogs from "@/views/admin/ActivityLogs.vue";
import Login from "@/views/auth/Login.vue";
import { createRouter, createWebHistory } from "vue-router";
import App from "@/layouts/App.vue";
import AppDashboard from "@/views/app/Dashboard.vue";
import AppTicketDetail from "@/views/app/TicketDetail.vue";
import AppTicketCreate from "@/views/app/TicketCreate.vue";
import AppProfile from "@/views/app/Profile.vue";
import AppSettings from "@/views/app/Settings.vue";
import Register from "@/views/auth/Register.vue";
import ForgotPassword from "@/views/auth/ForgotPassword.vue";
import ResetPassword from "@/views/auth/ResetPassword.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      component: App,
      children: [
        {
          path: "",
          name: "app.dashboard",
          component: AppDashboard,
          meta: {
            requiresAuth: true,
            role: "user",
            title: "Dashboard",
          },
        },
        {
          path: "ticket/:code",
          name: "app.ticket.detail",
          component: AppTicketDetail,
          meta: {
            requiresAuth: true,
            role: "user",
            title: "Ticket Detail",
          },
        },
        {
          path: "ticket/create",
          name: "app.ticket.create",
          component: AppTicketCreate,
          meta: {
            requiresAuth: true,
            role: "user",
            title: "Buat Ticket",
          },
        },
        {
          path: "profile",
          name: "app.profile",
          component: AppProfile,
          meta: {
            requiresAuth: true,
            role: "user",
            title: "Profil",
          },
        },
        {
          path: "settings",
          name: "app.settings",
          component: AppSettings,
          meta: {
            requiresAuth: true,
            role: "user",
            title: "Pengaturan",
          },
        },
      ],
    },
    {
      path: "/admin",
      component: Admin,
      children: [
        {
          path: "dashboard",
          name: "admin.dashboard",
          component: Dashboard,
          meta: {
            requiresAuth: true,
            role: "admin",
            title: "Dashboard",
          },
        },
        {
          path: "ticket",
          name: "admin.ticket",
          component: TicketList,
          meta: {
            requiresAuth: true,
            role: "admin",
            title: "Ticket",
          },
        },
        {
          path: "ticket/archive",
          name: "admin.ticket.archive",
          component: TicketArchive,
          meta: {
            requiresAuth: true,
            role: "admin",
            title: "Archive",
          },
        },
        {
          path: "ticket/:code",
          name: "admin.ticket.detail",
          component: TicketDetail,
          meta: {
            requiresAuth: true,
            role: "admin",
            title: "Ticket Detail",
          },
        },
        {
          path: "profile",
          name: "admin.profile",
          component: AdminProfile,
          meta: {
            requiresAuth: true,
            role: "admin",
            title: "Profil",
          },
        },
        {
          path: "users",
          name: "admin.users.index",
          component: AdminUserList,
          meta: {
            requiresAuth: true,
            role: "admin",
            title: "List Users",
          },
        },
        {
          path: "users/create",
          name: "admin.users.create",
          component: AdminCreateUser,
          meta: {
            requiresAuth: true,
            role: "admin",
            title: "Buat Akun",
          },
        },
        {
          path: "users/:id/edit",
          name: "admin.users.edit",
          component: AdminEditUser,
          meta: {
            requiresAuth: true,
            role: "admin",
            title: "Edit User",
          },
        },
        {
          path: "users/role",
          name: "admin.users.role",
          component: AdminRoleList,
          meta: {
            requiresAuth: true,
            role: "admin",
            title: "Role",
          },
        },
        {
          path: "settings",
          name: "admin.settings",
          component: AdminSettings,
          meta: {
            requiresAuth: true,
            role: "admin",
            title: "Pengaturan",
          },
        },
        {
          path: "it-assets",
          name: "admin.it-assets",
          component: ItAssetsSheet,
          meta: {
            requiresAuth: true,
            role: "admin",
            title: "IT Assets",
          },
        },
        {
          path: "activity-logs",
          name: "admin.activity-logs",
          component: ActivityLogs,
          meta: {
            requiresAuth: true,
            role: "admin",
            title: "Activity Logs",
          },
        },
        {
          path: "report",
          name: "admin.report",
          component: AdminReport,
          meta: {
            requiresAuth: true,
            role: "admin",
            title: "Report",
          },
        },
      ],
    },
    {
      path: "/auth",
      component: Auth,
      children: [
        {
          path: "login",
          name: "login",
          component: Login,
          // meta: { title: "Masuk ke akun anda" },
        },
        {
          path: "register",
          name: "register",
          component: Register,
          meta: { title: "Daftar akun baru" },
        },
        {
          path: "forgot-password",
          name: "forgot-password",
          component: ForgotPassword,
          meta: { title: "Reset Password" },
        },
        {
          path: "reset-password",
          name: "reset-password",
          component: ResetPassword,
          meta: { title: "Reset Password" },
        },
      ],
    },
  ],
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth) {
    if (authStore.token) {
      try {
        if (!authStore.user) {
          await authStore.checkAuth();
        }

        const roleRequired = to.meta.role;
        const userRole = authStore.user?.role;

        if (roleRequired === "admin" && userRole !== "admin") {
          next({ name: "app.dashboard" });
          return;
        }

        if (roleRequired === "user" && userRole === "admin") {
          next({ name: "admin.dashboard" });
          return;
        }

        next();
      } catch (error) {
        next({ name: "login" });
      }
    } else {
      next({ name: "login" });
    }
  } else if (to.meta.requiresUnauth && authStore.token) {
    try {
      if (!authStore.user) {
        await authStore.checkAuth();
      }

      if (authStore.user?.role === "admin") {
        next({ name: "admin.dashboard" });
      } else {
        next({ name: "app.dashboard" });
      }
    } catch (error) {
      next();
    }
  } else {
    next();
  }
});

export default router;
