<script setup>
import { storeToRefs } from "pinia";
import { computed, ref, watch } from "vue";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const { user, loading, error, success } = storeToRefs(authStore);
const { updateProfile } = authStore;

const form = ref({
  name: null,
  email: null,
  notify_email_on_ticket_created: true,
  notify_email_on_ticket_reply: true,
  notify_email_on_ticket_closed: true,
  notify_email_on_ticket_updated: true,
});

watch(
  () => user.value,
  (u) => {
    form.value.name = u?.name ?? null;
    form.value.email = u?.email ?? null;
    form.value.notify_email_on_ticket_created = u?.notify_email_on_ticket_created ?? true;
    form.value.notify_email_on_ticket_reply = u?.notify_email_on_ticket_reply ?? true;
    form.value.notify_email_on_ticket_closed = u?.notify_email_on_ticket_closed ?? true;
    form.value.notify_email_on_ticket_updated = u?.notify_email_on_ticket_updated ?? true;
  },
  { immediate: true },
);

const initialsName = computed(() => user.value?.name ?? "User");

// Master toggle untuk semua notifikasi email
const allNotificationsEnabled = computed({
  get: () => {
    return form.value.notify_email_on_ticket_created && form.value.notify_email_on_ticket_reply && form.value.notify_email_on_ticket_closed && form.value.notify_email_on_ticket_updated;
  },
  set: (value) => {
    form.value.notify_email_on_ticket_created = value;
    form.value.notify_email_on_ticket_reply = value;
    form.value.notify_email_on_ticket_closed = value;
    form.value.notify_email_on_ticket_updated = value;
  },
});

const handleSubmit = async () => {
  await updateProfile(form.value);
};
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <div class="flex items-center">
        <img :src="`https://ui-avatars.com/api/?name=${initialsName}&background=0D8ABC&color=fff`" alt="Profile" class="w-14 h-14 rounded-full" />
        <div class="ml-4">
          <h1 class="text-lg font-semibold text-gray-900">Profil</h1>
          <p class="text-sm text-gray-500">Kelola informasi akunmu</p>
        </div>
      </div>

      <div class="mt-6 space-y-2">
        <div class="text-sm">
          <div class="text-gray-500">Nama</div>
          <div class="text-gray-900 font-medium">{{ user?.name ?? "-" }}</div>
        </div>
        <div class="text-sm">
          <div class="text-gray-500">Email</div>
          <div class="text-gray-900 font-medium">{{ user?.email ?? "-" }}</div>
        </div>
        <div class="text-sm">
          <div class="text-gray-500">Role</div>
          <div class="text-gray-900 font-medium">{{ user?.role ?? "-" }}</div>
        </div>
      </div>

      <RouterLink :to="{ name: 'app.panduan' }" class="mt-6 w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747S17.5 6.253 12 6.253z"></path>
        </svg>
        Lihat Panduan
      </RouterLink>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
      <h2 class="text-base font-semibold text-gray-900">Edit profil</h2>
      <p class="text-sm text-gray-500 mt-1">Ubah nama/email akun.</p>

      <div v-if="success" class="mt-4 text-sm text-green-700 bg-green-50 border border-green-100 rounded-lg px-4 py-2">
        {{ success }}
      </div>

      <div v-if="error && typeof error === 'string'" class="mt-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg px-4 py-2">
        {{ error }}
      </div>

      <form class="mt-6 space-y-4" @submit.prevent="handleSubmit">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            class="mt-1 w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            placeholder="Nama lengkap"
            :class="{ 'border-red-500 ring-red-500': error?.name }"
          />

          <p class="mt-1 text-xs text-red-500" v-if="error?.name">
            {{ error?.name?.join(", ") }}
          </p>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="mt-1 w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            placeholder="nama@perusahaan.com"
            :class="{ 'border-red-500 ring-red-500': error?.email }"
          />

          <p class="mt-1 text-xs text-red-500" v-if="error?.email">
            {{ error?.email?.join(", ") }}
          </p>
        </div>

        <div class="border-t border-gray-200 pt-6 mt-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-sm font-semibold text-gray-900">Preferensi Notifikasi Email</h3>
              <p class="text-xs text-gray-500 mt-1">Aktifkan untuk menerima semua notifikasi email</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="allNotificationsEnabled" type="checkbox" class="sr-only peer" />
              <div
                class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"
              ></div>
            </label>
          </div>

          <div class="bg-gray-50 rounded-lg p-4 space-y-2">
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <span :class="allNotificationsEnabled ? 'text-gray-900 font-medium' : 'text-gray-600'">Notifikasi saat tiket dibuat</span>
            </div>
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <span :class="allNotificationsEnabled ? 'text-gray-900 font-medium' : 'text-gray-600'">Notifikasi saat ada balasan tiket</span>
            </div>
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <span :class="allNotificationsEnabled ? 'text-gray-900 font-medium' : 'text-gray-600'">Notifikasi saat tiket ditutup</span>
            </div>
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <span :class="allNotificationsEnabled ? 'text-gray-900 font-medium' : 'text-gray-600'">Notifikasi saat tiket diperbarui</span>
            </div>
          </div>
        </div>

        <div class="pt-2">
          <div class="flex items-center gap-3">
            <button
              type="submit"
              :disabled="loading"
              class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed"
              v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
            >
              Simpan
            </button>

            <RouterLink
              :to="{ name: 'app.dashboard' }"
              class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200"
              v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
            >
              Kembali
            </RouterLink>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
