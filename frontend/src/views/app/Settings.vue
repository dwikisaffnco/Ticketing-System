<script setup>
import { storeToRefs } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const { loading, error, success } = storeToRefs(authStore);
const { changePassword } = authStore;

const form = ref({
  current_password: null,
  password: null,
  password_confirmation: null,
});

const showCurrentPassword = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const handleSubmit = async () => {
  await changePassword(form.value);

  form.value.current_password = null;
  form.value.password = null;
  form.value.password_confirmation = null;
};
</script>

<template>
  <div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <h1 class="text-lg font-semibold text-gray-900">Pengaturan</h1>
      <p class="text-sm text-gray-500 mt-1">Kelola keamanan akunmu.</p>

      <div class="mt-6">
        <h2 class="text-base font-semibold text-gray-900">Ubah password</h2>

        <div v-if="success" class="mt-4 text-sm text-green-700 bg-green-50 border border-green-100 rounded-lg px-4 py-2">
          {{ success }}
        </div>

        <div v-if="error && typeof error === 'string'" class="mt-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg px-4 py-2">
          {{ error }}
        </div>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700">Password saat ini</label>
            <div class="mt-1 relative">
              <input
                id="current_password"
                v-model="form.current_password"
                :type="showCurrentPassword ? 'text' : 'password'"
                class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                placeholder="••••••••"
                :class="{ 'border-red-500 ring-red-500': error?.current_password }"
              />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none" @click="showCurrentPassword = !showCurrentPassword">
                  <i :data-feather="showCurrentPassword ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                </button>
              </div>
            </div>

            <p class="mt-1 text-xs text-red-500" v-if="error?.current_password">
              {{ error?.current_password?.join(", ") }}
            </p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password baru</label>
            <div class="mt-1 relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                placeholder="••••••••"
                :class="{ 'border-red-500 ring-red-500': error?.password }"
              />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none" @click="showPassword = !showPassword">
                  <i :data-feather="showPassword ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                </button>
              </div>
            </div>

            <p class="mt-1 text-xs text-red-500" v-if="error?.password">
              {{ error?.password?.join(", ") }}
            </p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi password baru</label>
            <div class="mt-1 relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showPasswordConfirmation ? 'text' : 'password'"
                class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                placeholder="••••••••"
                :class="{ 'border-red-500 ring-red-500': error?.password_confirmation }"
              />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none" @click="showPasswordConfirmation = !showPasswordConfirmation">
                  <i :data-feather="showPasswordConfirmation ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                </button>
              </div>
            </div>

            <p class="mt-1 text-xs text-red-500" v-if="error?.password_confirmation">
              {{ error?.password_confirmation?.join(", ") }}
            </p>
          </div>

          <div class="pt-2">
            <div class="flex items-center gap-3">
              <button type="submit" :disabled="loading" class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed">
                Simpan perubahan
              </button>

              <RouterLink :to="{ name: 'app.dashboard' }" class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200"> Kembali </RouterLink>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
