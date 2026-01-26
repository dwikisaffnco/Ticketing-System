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
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Preferensi Notifikasi Email</h3>
          <div class="space-y-3">
            <label class="flex items-center cursor-pointer">
              <input v-model="form.notify_email_on_ticket_created" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer" />
              <span class="ml-3 text-sm text-gray-700">Notifikasi saat tiket dibuat</span>
            </label>
            <label class="flex items-center cursor-pointer">
              <input v-model="form.notify_email_on_ticket_reply" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer" />
              <span class="ml-3 text-sm text-gray-700">Notifikasi saat ada balasan tiket</span>
            </label>
            <label class="flex items-center cursor-pointer">
              <input v-model="form.notify_email_on_ticket_closed" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer" />
              <span class="ml-3 text-sm text-gray-700">Notifikasi saat tiket ditutup</span>
            </label>
            <label class="flex items-center cursor-pointer">
              <input v-model="form.notify_email_on_ticket_updated" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer" />
              <span class="ml-3 text-sm text-gray-700">Notifikasi saat tiket diperbarui</span>
            </label>
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
