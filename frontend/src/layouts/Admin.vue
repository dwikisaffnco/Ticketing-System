<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import Sidebar from "@/components/admin/Sidebar.vue";
import { useAuthStore } from "@/stores/auth";
import { useNotificationStore } from "@/stores/notification";
import { storeToRefs } from "pinia";
import { useRoute } from "vue-router";
import feather from "feather-icons";

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
const { logout } = authStore;

const notificationStore = useNotificationStore();
const { notifications } = storeToRefs(notificationStore);
const unreadCount = computed(() => notificationStore.unreadCount);

const showNotificationMenu = ref(false);
const showUserMenu = ref(false);

const sidebarOpen = ref(false);
const route = useRoute();

watch(
  () => route.fullPath,
  () => {
    sidebarOpen.value = false;
  }
);

watch(
  sidebarOpen,
  async (open) => {
    if (!open) return;
    await nextTick();
    feather.replace();
  },
  { flush: "post" }
);

const toggleNotificationMenu = () => {
  showNotificationMenu.value = !showNotificationMenu.value;
  if (showNotificationMenu.value) {
    notificationStore.fetchNotifications();
  }
};

const handleMarkAllRead = () => {
  notificationStore.markAllRead();
};

const handleClearAll = () => {
  notificationStore.clearAll();
};

const handleOpenNotification = (n) => {
  notificationStore.markRead(n.id);
  showNotificationMenu.value = false;
};

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value;
  if (showUserMenu.value) {
    showNotificationMenu.value = false;
  }
};

const handleLogout = async () => {
  await logout();
};

onMounted(() => {
  notificationStore.startPolling();
});

onUnmounted(() => {
  notificationStore.stopPolling();
});
</script>

<template>
  <div class="flex h-screen">
    <div class="hidden lg:block">
      <Sidebar />
    </div>

    <div v-if="sidebarOpen" class="lg:hidden fixed inset-0 z-40">
      <div class="absolute inset-0 bg-black/40" @click="sidebarOpen = false"></div>
      <div class="relative w-[85vw] max-w-sm h-full bg-white shadow-lg">
        <div class="absolute top-4 right-4">
          <button type="button" class="p-2 rounded-md hover:bg-gray-100" @click="sidebarOpen = false">
            <svg class="w-5 h-5 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M18 6L6 18"></path>
              <path d="M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <Sidebar />
      </div>
    </div>

    <!-- Main Content -->
    <main class="flex-1 overflow-x-hidden overflow-y-auto flex flex-col">
      <!-- Topbar -->
      <div class="bg-white shadow-sm sticky top-0 z-10">
        <div class="flex items-center justify-between px-6 py-4">
          <div class="flex items-center gap-3">
            <button type="button" class="lg:hidden p-2 rounded-md hover:bg-gray-100" @click="sidebarOpen = true">
              <svg class="w-6 h-6 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 12h18"></path>
                <path d="M3 6h18"></path>
                <path d="M3 18h18"></path>
              </svg>
            </button>
            <h2 class="text-xl font-semibold text-gray-800">{{ $route.meta.title }}</h2>
          </div>
          <div class="flex items-center space-x-4">
            <div class="relative">
              <button @click="toggleNotificationMenu" class="relative p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-full">
                <i data-feather="bell" class="w-6 h-6"></i>
                <span v-if="unreadCount > 0" class="absolute top-0 right-0 min-w-4 h-4 px-1 bg-red-500 rounded-full text-[10px] leading-4 text-white text-center">
                  {{ unreadCount }}
                </span>
              </button>

              <div
                v-if="showNotificationMenu"
                v-motion-pop
                class="fixed left-4 right-4 top-16 mt-2 w-auto max-w-sm mx-auto bg-white rounded-lg shadow-lg border border-gray-100 z-50 md:absolute md:left-auto md:right-0 md:top-auto md:mt-2 md:w-80 md:max-w-none md:mx-0"
              >
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                  <div>
                    <div class="text-sm font-semibold text-gray-900">Notifikasi</div>
                    <div class="text-xs text-gray-500" v-if="unreadCount > 0">{{ unreadCount }} belum dibaca</div>
                    <div class="text-xs text-gray-500" v-else>Semua sudah dibaca</div>
                  </div>
                  <div class="flex items-center gap-2">
                    <button type="button" class="text-xs text-gray-600 hover:text-gray-800" @click="handleMarkAllRead">Tandai semua</button>
                    <button type="button" class="text-xs text-red-600 hover:text-red-800" @click="handleClearAll">Bersihkan</button>
                  </div>
                </div>

                <div class="max-h-96 overflow-auto">
                  <div v-if="notifications.length === 0" class="px-4 py-6 text-sm text-gray-500">Belum ada notifikasi.</div>
                  <div v-else>
                    <div v-for="n in notifications" :key="n.id" class="px-4 py-3 border-b border-gray-50 hover:bg-gray-50">
                      <RouterLink v-if="n.href" :to="n.href" class="block" @click="handleOpenNotification(n)">
                        <div class="flex items-start gap-3">
                          <span class="mt-1 w-2 h-2 rounded-full" :class="n.readAt ? 'bg-gray-300' : 'bg-blue-600'"></span>
                          <div class="min-w-0">
                            <div class="text-sm font-medium text-gray-900 truncate">{{ n.title }}</div>
                            <div class="text-xs text-gray-500 mt-0.5 wrap-break-word">{{ n.message }}</div>
                          </div>
                        </div>
                      </RouterLink>

                      <button v-else type="button" class="block w-full text-left" @click="handleOpenNotification(n)">
                        <div class="flex items-start gap-3">
                          <span class="mt-1 w-2 h-2 rounded-full" :class="n.readAt ? 'bg-gray-300' : 'bg-blue-600'"></span>
                          <div class="min-w-0">
                            <div class="text-sm font-medium text-gray-900 truncate">{{ n.title }}</div>
                            <div class="text-xs text-gray-500 mt-0.5 wrap-break-word">{{ n.message }}</div>
                          </div>
                        </div>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="relative">
              <button @click="toggleUserMenu" class="flex items-center bg-gray-50 px-4 py-2 rounded-full hover:bg-gray-100">
                <img :src="`https://ui-avatars.com/api/?name=${user?.name ?? 'Admin'}&background=0D8ABC&color=fff`" alt="Profile" class="w-8 h-8 rounded-full" />
                <span class="ml-2 text-sm font-medium text-gray-700">{{ user?.name ?? "Admin" }}</span>
                <i data-feather="chevron-down" class="w-4 h-4 ml-2 text-gray-500"></i>
              </button>

              <div v-if="showUserMenu" v-motion-pop class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                <RouterLink :to="{ name: 'admin.profile' }" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="showUserMenu = false"> Profil </RouterLink>
                <RouterLink :to="{ name: 'admin.settings' }" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="showUserMenu = false"> Pengaturan </RouterLink>
                <div class="border-t border-gray-100 my-1"></div>
                <a href="#" @click.prevent="handleLogout" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-50"> Logout </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="p-4 lg:p-6 space-y-6 flex-1">
        <div v-motion-fade>
          <router-view></router-view>
        </div>
      </div>

      <footer class="mt-auto border-t border-gray-100 bg-white">
        <div class="px-4 lg:px-6 py-4 text-xs text-gray-500">© 2025 Ticketing SAFF & Co. All rights reserved.</div>
      </footer>
    </main>
  </div>
</template>
