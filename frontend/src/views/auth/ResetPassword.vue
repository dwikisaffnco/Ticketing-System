<script setup>
import { computed, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { storeToRefs } from "pinia";

const route = useRoute();
const router = useRouter();

const authStore = useAuthStore();
const { loading, error } = storeToRefs(authStore);
const { resetPassword } = authStore;

const token = computed(() => (route.query.token ? String(route.query.token) : ""));
const email = computed(() => (route.query.email ? String(route.query.email) : ""));

const form = ref({
  password: "",
  password_confirmation: "",
});

const successMessage = ref(null);
const isDone = ref(false);

const isLinkValid = computed(() => !!token.value && !!email.value);

const handleSubmit = async () => {
  successMessage.value = null;

  const ok = await resetPassword({
    token: token.value,
    email: email.value,
    password: form.value.password,
    password_confirmation: form.value.password_confirmation,
  });

  if (ok) {
    successMessage.value = "Password berhasil direset. Silakan login kembali.";
    form.value.password = "";
    form.value.password_confirmation = "";
    isDone.value = true;
  }
};

const handleBackToLogin = () => {
  router.push({ name: "login" });
};

const fieldError = (key) => {
  if (!error.value) return null;
  if (typeof error.value === "string") return null;
  if (typeof error.value === "object" && error.value[key]) {
    return Array.isArray(error.value[key]) ? error.value[key].join(", ") : String(error.value[key]);
  }
  return null;
};
</script>

<template>
  <div class="space-y-6">
    <div class="text-center">
      <p class="mt-2 text-sm text-gray-600">Masukkan password baru untuk akun kamu.</p>
    </div>

    <div v-if="!isLinkValid" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
      <span class="block sm:inline">Link reset password tidak valid. Silakan ulangi proses lupa password.</span>
    </div>

    <form v-else class="space-y-6" @submit.prevent="handleSubmit">
      <div v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ successMessage }}</span>
      </div>

      <div v-if="error && typeof error === 'string'" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ error }}</span>
      </div>

      <template v-if="!isDone">
        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
          <div class="mt-1 relative">
            <input
              v-model="form.password"
              type="password"
              id="password"
              name="password"
              required
              class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              placeholder="••••••••"
              :class="{ 'border-red-500 ring-red-500': fieldError('password') }"
            />
            <p v-if="fieldError('password')" class="mt-1 text-xs text-red-500">{{ fieldError("password") }}</p>
          </div>
        </div>

        <!-- Confirm Password -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
          <div class="mt-1 relative">
            <input
              v-model="form.password_confirmation"
              type="password"
              id="password_confirmation"
              name="password_confirmation"
              required
              class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              placeholder="••••••••"
              :class="{ 'border-red-500 ring-red-500': fieldError('password_confirmation') }"
            />
            <p v-if="fieldError('password_confirmation')" class="mt-1 text-xs text-red-500">{{ fieldError("password_confirmation") }}</p>
          </div>
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
            <span v-if="!loading">Reset Password</span>
            <span v-else>Loading...</span>
          </button>
        </div>
      </template>

      <div class="mt-6 text-center">
        <button type="button" class="text-sm font-medium text-blue-600 hover:text-blue-800" @click="handleBackToLogin">Kembali ke Login</button>
      </div>
    </form>
  </div>
</template>
