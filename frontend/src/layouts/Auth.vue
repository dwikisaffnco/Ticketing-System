<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const title = computed(() => route.meta.title || "");
</script>

<template>
  <div class="min-h-screen flex">
    <!-- Left Panel -->
    <div class="hidden lg:flex w-1/2 bg-black flex-col items-center justify-center relative overflow-hidden">
      <div class="z-10 text-center">
        <img src="/saffco-logo.png" alt="SAFF & Co. Logo" class="h-60 w-auto mb-6 mx-auto invert brightness-0" />
        <!-- <h1 class="text-4xl font-bold text-white tracking-wider">SAFF & Co.</h1> -->
      </div>
      <!-- Abstract shapes/bg effects if needed, keeping it simple black for now as requested -->
    </div>

    <!-- Right Panel -->
    <div class="w-full lg:w-1/2 bg-slate-50 flex items-center justify-center p-4 sm:p-12">
      <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg">
        <router-view></router-view>
      </div>
    </div>
  </div>
</template>
