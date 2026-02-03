<script setup>
import { onMounted } from "vue";
import feather from "feather-icons";

const sheetUrl = "https://docs.google.com/spreadsheets/d/e/2PACX-1vRsLop-Kuh81fk3Ys4eUE8LOXJUzX5qelcvEFhC-Zil78oShUO8c--AxLs4nvsZ_gPJxiUyVKIZsWkB/pubhtml?widget=true&amp;headers=false";

onMounted(() => {
  feather.replace();
});
</script>

<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h1 class="text-lg font-semibold text-gray-900">IT Assets</h1>
        <p class="text-sm text-gray-500">Embedded Google Sheets.</p>
      </div>
      <a
        :href="sheetUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200"
        v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
      >
        <i data-feather="external-link" class="w-4 h-4 mr-2"></i>
        Buka di Tab Baru
      </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" style="height: calc(100vh - 180px)">
      <iframe :src="sheetUrl" class="w-full h-full" frameborder="0"></iframe>
    </div>
  </div>
</template>
