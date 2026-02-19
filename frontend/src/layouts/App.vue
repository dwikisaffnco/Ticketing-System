<script setup>
import { onMounted, onUnmounted } from "vue";
import Navbar from "@/components/app/Navbar.vue";
import AIChatBubble from "@/components/app/AIChatBubble.vue";
import { useNotificationStore } from "@/stores/notification";

const notificationStore = useNotificationStore();

onMounted(() => {
  notificationStore.startPolling();
});

onUnmounted(() => {
  notificationStore.stopPolling();
});
</script>

<template>
  <div class="bg-gray-50 min-h-screen flex flex-col">
    <Navbar />

    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">
      <div v-motion-fade>
        <router-view></router-view>
      </div>
    </main>

    <AIChatBubble />

    <footer class="mt-auto border-t border-gray-100 bg-white">
      <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-4 text-xs text-gray-500 text-center">© 2025 Ticketing SAFF & Co. All rights reserved.</div>
    </footer>

  </div>
</template>
