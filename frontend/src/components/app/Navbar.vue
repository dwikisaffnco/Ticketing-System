<script setup>
import { computed, ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import { storeToRefs } from "pinia";
import { useNotificationStore } from "@/stores/notification";
import { axiosInstance } from "@/plugins/axios";

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
const { logout } = authStore;

const notificationStore = useNotificationStore();
const { notifications } = storeToRefs(notificationStore);
const unreadCount = computed(() => notificationStore.unreadCount);

const showUserMenu = ref(false);
const showNotificationMenu = ref(false);

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value;
};

const handleLogout = async () => {
  await logout();
};

const toggleNotificationMenu = () => {
  showNotificationMenu.value = !showNotificationMenu.value;
  if (showNotificationMenu.value) {
    showUserMenu.value = false;
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

const backendBaseUrl = (axiosInstance.defaults.baseURL ?? "").replace(/\/api\/?$/, "");
const logoUrl = `${backendBaseUrl}/logo/Logotype%20Black.png`;
</script>

<template>
  <nav class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex items-center">
          <RouterLink :to="{ name: 'app.dashboard' }" class="flex items-center">
            <img :src="logoUrl" alt="Logo" class="h-6 md:h-7 w-auto block object-contain" />
          </RouterLink>
        </div>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <button @click="toggleNotificationMenu" class="relative p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-full" v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.1 }, tapped: { scale: 0.95 } }">
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
          <div class="relative" x-data="{ open: false }">
            <button @click="toggleUserMenu()" class="flex items-center bg-gray-50 px-4 py-2 rounded-full hover:bg-gray-100" v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }">
              <img :src="`https://ui-avatars.com/api/?name=${user?.name}&background=0D8ABC&color=fff`" alt="Profile" class="w-8 h-8 rounded-full" />
              <span class="ml-2 text-sm font-medium text-gray-700">{{ user?.name }}</span>
              <i data-feather="chevron-down" class="w-4 h-4 ml-2 text-gray-500"></i>
            </button>
            <!-- Dropdown Menu -->
            <div v-if="showUserMenu" v-motion-pop class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
              <RouterLink :to="{ name: 'app.profile' }" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="showUserMenu = false"> Profil </RouterLink>
              <RouterLink :to="{ name: 'app.settings' }" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="showUserMenu = false"> Pengaturan </RouterLink>
              <RouterLink :to="{ name: 'app.panduan' }" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="showUserMenu = false"> Panduan </RouterLink>
              <div class="border-t border-gray-100 my-1"></div>
              <a href="#" @click.prevent="handleLogout" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-50"> Keluar </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>
