<script setup>
import { computed, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { axiosInstance } from "@/plugins/axios";

const authStore = useAuthStore();
const { logout } = authStore;

const route = useRoute();

const userMenuOpen = ref(false);
const userMenuRoutes = ["admin.users.index", "admin.users.create", "admin.users.edit", "admin.users.role"];
const isUserMenuActive = computed(() => userMenuRoutes.includes(route.name));

watch(
  isUserMenuActive,
  (active) => {
    if (active) userMenuOpen.value = true;
  },
  { immediate: true }
);

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value;
};

const handleLogout = async () => {
  await logout();
};

const backendBaseUrl = (axiosInstance.defaults.baseURL ?? "").replace(/\/api\/?$/, "");
const logoUrl = `${backendBaseUrl}/logo/Logotype%20Black.png`;
</script>

<template>
  <aside class="w-full lg:w-64 bg-white shadow-lg h-screen">
    <div class="p-4 lg:p-6 border-b border-gray-100">
      <div class="flex items-center">
        <RouterLink :to="{ name: 'admin.dashboard' }" class="inline-flex items-center">
          <img :src="logoUrl" alt="Logo" class="h-6 lg:h-7 w-auto" />
        </RouterLink>
      </div>
    </div>
    <nav class="mt-6">
      <RouterLink
        :to="{ name: 'admin.dashboard' }"
        class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50 hover:border-l-4 hover:border-gray-200"
        :class="{ 'bg-blue-50 border-l-4 border-blue-600': $route.name === 'admin.dashboard' }"
        v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
      >
        <i data-feather="home" class="w-5 h-5 mr-3"></i>
        Dashboard
      </RouterLink>
      <RouterLink
        :to="{ name: 'admin.ticket' }"
        class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50 hover:border-l-4 hover:border-gray-200"
        :class="{ 'bg-blue-50 border-l-4 border-blue-600': $route.name === 'admin.ticket' }"
        v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
      >
        <i data-feather="tag" class="w-5 h-5 mr-3"></i>
        Tiket
      </RouterLink>

      <RouterLink
        :to="{ name: 'admin.ticket.archive' }"
        class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50 hover:border-l-4 hover:border-gray-200"
        :class="{ 'bg-blue-50 border-l-4 border-blue-600': $route.name === 'admin.ticket.archive' }"
        v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
      >
        <i data-feather="archive" class="w-5 h-5 mr-3"></i>
        Archive
      </RouterLink>

      <RouterLink
        :to="{ name: 'admin.it-assets' }"
        class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50 hover:border-l-4 hover:border-gray-200"
        :class="{ 'bg-blue-50 border-l-4 border-blue-600': $route.name === 'admin.it-assets' }"
        v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
      >
        <i data-feather="grid" class="w-5 h-5 mr-3"></i>
        IT Assets
      </RouterLink>

      <RouterLink
        :to="{ name: 'admin.activity-logs' }"
        class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50 hover:border-l-4 hover:border-gray-200"
        :class="{ 'bg-blue-50 border-l-4 border-blue-600': $route.name === 'admin.activity-logs' }"
        v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
      >
        <i data-feather="activity" class="w-5 h-5 mr-3"></i>
        Activity Logs
      </RouterLink>

      <RouterLink
        :to="{ name: 'admin.report' }"
        class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50 hover:border-l-4 hover:border-gray-200"
        :class="{ 'bg-blue-50 border-l-4 border-blue-600': $route.name === 'admin.report' }"
        v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
      >
        <i data-feather="file-text" class="w-5 h-5 mr-3"></i>
        Report
      </RouterLink>

      <div>
        <button
          type="button"
          @click="toggleUserMenu"
          class="w-full flex items-center justify-between px-6 py-3 text-gray-600 hover:bg-gray-50 hover:border-l-4 hover:border-gray-200"
          :class="{ 'bg-blue-50 border-l-4 border-blue-600': isUserMenuActive }"
        >
          <div class="flex items-center">
            <i data-feather="users" class="w-5 h-5 mr-3"></i>
            User
          </div>

          <i data-feather="chevron-down" class="w-4 h-4" :class="{ 'rotate-180': userMenuOpen }"></i>
        </button>

        <div v-show="userMenuOpen" class="mt-1">
          <RouterLink :to="{ name: 'admin.users.index' }" class="flex items-center pl-14 pr-6 py-2 text-sm text-gray-600 hover:bg-gray-50" :class="{ 'text-blue-700 bg-blue-50': $route.name === 'admin.users.index' }">
            <i data-feather="list" class="w-4 h-4 mr-2"></i>
            Daftar User
          </RouterLink>
          <RouterLink :to="{ name: 'admin.users.create' }" class="flex items-center pl-14 pr-6 py-2 text-sm text-gray-600 hover:bg-gray-50" :class="{ 'text-blue-700 bg-blue-50': $route.name === 'admin.users.create' }">
            <i data-feather="user-plus" class="w-4 h-4 mr-2"></i>
            Tambah User
          </RouterLink>
          <RouterLink :to="{ name: 'admin.users.role' }" class="flex items-center pl-14 pr-6 py-2 text-sm text-gray-600 hover:bg-gray-50" :class="{ 'text-blue-700 bg-blue-50': $route.name === 'admin.users.role' }">
            <i data-feather="shield" class="w-4 h-4 mr-2"></i>
            Role
          </RouterLink>
        </div>
      </div>

      <a @click="handleLogout" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50 hover:border-l-4 hover:border-gray-200 mt-6" v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }">
        <i data-feather="log-out" class="w-5 h-5 mr-3"></i>
        Logout
      </a>
    </nav>
  </aside>
</template>
