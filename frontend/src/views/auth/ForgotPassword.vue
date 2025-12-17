<script setup>
import { useAuthStore } from "@/stores/auth";
import { storeToRefs } from "pinia";
import { ref } from "vue";

const authStore = useAuthStore();
const { loading, error } = storeToRefs(authStore);
const { forgotPassword } = authStore;

const email = ref("");
const successMessage = ref(null);

const handleSubmit = async () => {
  successMessage.value = null;
  const ok = await forgotPassword({ email: email.value });
  if (ok) {
    successMessage.value = "Email reset password berhasil dikirim";
  }
};
</script>

<template>
  <div class="space-y-6">
    <div class="text-center">
      <p class="mt-2 text-sm text-gray-600">Masukkan email yang terdaftar untuk mendapatkan link reset password.</p>
    </div>

    <form class="space-y-6" @submit.prevent="handleSubmit">
      <div v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ successMessage }}</span>
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <div class="mt-1 relative">
          <input
            v-model="email"
            type="email"
            id="email"
            name="email"
            required
            class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            placeholder="nama@perusahaan.com"
            :class="{ 'border-red-500 ring-red-500': error?.email }"
          />
          <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <i data-feather="mail" class="w-4 h-4 text-gray-400"></i>
          </div>
          <p class="mt-1 text-xs text-red-500" v-if="error?.email">
            {{ error?.email?.join(", ") }}
          </p>
        </div>
      </div>

      <!-- Submit Button -->
      <div>
        <button
          type="submit"
          :disabled="loading"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          v-motion="{
            initial: { scale: 1 },
            hovered: { scale: 1.05 },
            tapped: { scale: 0.95 },
          }"
        >
          <span v-if="!loading"> Kirim Link </span>
          <span v-else> Loading... </span>
        </button>
      </div>
    </form>

    <!-- Back to Login -->
    <div class="mt-6 text-center">
      <RouterLink :to="{ name: 'login' }" class="text-sm font-medium text-blue-600 hover:text-blue-800"> Kembali ke Login </RouterLink>
    </div>
  </div>
</template>
