<script setup>
import { useAuthStore } from "@/stores/auth";
import { storeToRefs } from "pinia";
import { ref } from "vue";

const authStore = useAuthStore();
const { loading, error } = storeToRefs(authStore);
const { login } = authStore;

const form = ref({
  email: null,
  password: null,
});

const loginError = ref(null);
const loginSuccess = ref(false);
const showPassword = ref(false);

const toggleShowPassword = () => {
  showPassword.value = !showPassword.value;
};

const handleSubmit = async () => {
  loginError.value = null;
  loginSuccess.value = false;

  const success = await login(form.value);

  if (!success) {
    loginError.value = error.value;
    form.value.password = null;
    return;
  }

  loginSuccess.value = true;
};
</script>

<template>
  <form class="space-y-6" @submit.prevent="handleSubmit">
    <div v-if="loading" class="fixed inset-0 bg-black/20 z-50 flex items-center justify-center">
      <div class="bg-white rounded-lg shadow-lg px-6 py-4 flex items-center gap-3">
        <svg class="h-5 w-5 text-blue-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <div class="text-sm font-medium text-gray-700">Memproses login...</div>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="loginError" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
      <span class="block sm:inline">{{ loginError }}</span>
    </div>

    <div v-if="loginSuccess" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
      <span class="block sm:inline">Login berhasil</span>
    </div>

    <!-- Email -->
    <div>
      <label for="email" class="block text-sm font-medium text-gray-700"> Email </label>
      <div class="mt-1 relative">
        <input
          v-model="form.email"
          type="email"
          id="email"
          name="email"
          required
          class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
          placeholder="nama@perusahaan.com"
        />
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
          <i data-feather="mail" class="w-4 h-4 text-gray-400"></i>
        </div>
      </div>
    </div>

    <!-- Password -->
    <div>
      <label for="password" class="block text-sm font-medium text-gray-700"> Password </label>
      <div class="mt-1 relative">
        <input
          v-model="form.password"
          :type="showPassword ? 'text' : 'password'"
          id="password"
          name="password"
          required
          class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
          placeholder="••••••••"
        />
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
          <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none" @click="toggleShowPassword">
            <i data-feather="eye" class="w-4 h-4" id="password-toggle"></i>
          </button>
        </div>
      </div>

      <div class="mt-2 flex items-center gap-1.5">
        <input v-model="showPassword" type="checkbox" id="show_password" name="show_password" class="h-3.5 w-3.5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
        <label for="show_password" class="text-xs text-gray-600"> Show password </label>
      </div>
    </div>

    <!-- Remember Me & Forgot Password -->
    <div class="flex items-center justify-between">
      <div class="flex items-center">
        <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
        <label for="remember" class="ml-2 block text-sm text-gray-700"> Ingat saya </label>
      </div>
      <RouterLink :to="{ name: 'forgot-password' }" class="text-sm text-blue-600 hover:text-blue-800"> Lupa password? </RouterLink>
    </div>

    <!-- Submit Button -->
    <div>
      <button
        type="submit"
        :disabled="loading"
        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-60"
        v-motion="{
          initial: { scale: 1 },
          hovered: { scale: 1.05 },
          tapped: { scale: 0.95 },
        }"
      >
        <span v-if="!loading">Masuk</span>
        <span v-else class="inline-flex items-center gap-2">
          <svg class="h-4 w-4 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
          </svg>
          Loading...
        </span>
      </button>
    </div>
  </form>
</template>
