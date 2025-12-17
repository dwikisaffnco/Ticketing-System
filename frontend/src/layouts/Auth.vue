<script setup>
import { axiosInstance } from "@/plugins/axios";
import { useRoute } from "vue-router";
import { computed } from "vue";

const route = useRoute();
const title = computed(() => route.meta.title || "");

const backendBaseUrl = (axiosInstance.defaults.baseURL ?? "").replace(/\/api\/?$/, "");
const logoUrl = `${backendBaseUrl}/logo/Logotype%20Black.png`;
</script>
<template>
  <div class="bg-gray-50 min-h-screen flex flex-col">
    <div class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8">
        <div class="text-center">
          <div class="flex justify-center">
            <img :src="logoUrl" alt="Logo" class="h-10 w-auto" />
          </div>
          <h2 class="mt-6 text-3xl font-bold text-gray-900">&nbsp;</h2>
          <p class="mt-2 text-sm text-gray-600">{{ title }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8" v-motion-slide-visible-bottom>
          <div class="text-center mb-6">
            <h1 class="text-xl font-bold text-gray-900">Ticketing IT</h1>
          </div>
          <router-view></router-view>
        </div>
      </div>
    </div>

    <footer class="mt-auto border-t border-gray-100 bg-white">
      <div class="max-w-md w-full mx-auto px-4 sm:px-6 lg:px-8 py-4 text-xs text-gray-500 text-center">© 2025 Ticketing SAFF & Co. All rights reserved.</div>
    </footer>
  </div>
</template>
