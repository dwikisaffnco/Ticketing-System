<script setup>
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { axiosInstance } from "@/plugins/axios";
import { handleError } from "@/helpers/errorHelper";
import Swal from "sweetalert2";

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const fetching = ref(false);
const error = ref(null);
const success = ref(null);

const form = ref({
  name: null,
  email: null,
  password: null,
  role: "user",
  division: null,
  position: null,
});

const fetchUser = async () => {
  fetching.value = true;
  error.value = null;
  success.value = null;

  try {
    const response = await axiosInstance.get(`/admin/users/${route.params.id}`);
    const u = response?.data?.data ?? null;

    form.value = {
      name: u?.name ?? null,
      email: u?.email ?? null,
      password: null,
      role: u?.role ?? "user",
      division: u?.division ?? null,
      position: u?.position ?? null,
    };
  } catch (e) {
    error.value = handleError(e);
  } finally {
    fetching.value = false;
  }
};

const handleSubmit = async () => {
  loading.value = true;
  error.value = null;
  success.value = null;

  try {
    const payload = { ...form.value };
    if (!payload.password) {
      delete payload.password;
    }

    const response = await axiosInstance.put(`/admin/users/${route.params.id}`, payload);
    success.value = response?.data?.message ?? "User berhasil diperbarui";

    await Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: success.value,
      timer: 1500,
      showConfirmButton: false,
    });
  } catch (e) {
    error.value = handleError(e);

    await Swal.fire({
      icon: "error",
      title: "Gagal",
      text: typeof error.value === "string" ? error.value : "Terjadi kesalahan",
    });
  } finally {
    loading.value = false;
  }
};

const handleBack = () => {
  router.push({ name: "admin.users.index" });
};

onMounted(() => {
  fetchUser();
});
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-lg font-semibold text-gray-900">Edit User</h1>
        <p class="text-sm text-gray-500">Ubah data akun user/admin.</p>
      </div>
    </div>

    <div v-if="success" class="mt-4 text-sm text-green-700 bg-green-50 border border-green-100 rounded-lg px-4 py-2">
      {{ success }}
    </div>

    <div v-if="error && typeof error === 'string'" class="mt-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg px-4 py-2">
      {{ error }}
    </div>

    <div v-if="fetching" class="mt-6 text-sm text-gray-500">Memuat data...</div>

    <form v-else class="mt-6 space-y-4 max-w-xl" @submit.prevent="handleSubmit">
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

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password (opsional)</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          class="mt-1 w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
          placeholder="Kosongkan jika tidak ingin mengubah"
          :class="{ 'border-red-500 ring-red-500': error?.password }"
        />
        <p class="mt-1 text-xs text-red-500" v-if="error?.password">
          {{ error?.password?.join(", ") }}
        </p>
      </div>

      <div>
        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
        <select
          id="role"
          v-model="form.role"
          class="mt-1 w-full px-4 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
          :class="{ 'border-red-500 ring-red-500': error?.role }"
        >
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
        <p class="mt-1 text-xs text-red-500" v-if="error?.role">
          {{ error?.role?.join(", ") }}
        </p>
      </div>

      <div>
        <label for="division" class="block text-sm font-medium text-gray-700">Divisi</label>
        <input
          id="division"
          v-model="form.division"
          type="text"
          class="mt-1 w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
          placeholder="Contoh: IT"
          :class="{ 'border-red-500 ring-red-500': error?.division }"
        />
        <p class="mt-1 text-xs text-red-500" v-if="error?.division">
          {{ error?.division?.join(", ") }}
        </p>
      </div>

      <div>
        <label for="position" class="block text-sm font-medium text-gray-700">Posisi</label>
        <input
          id="position"
          v-model="form.position"
          type="text"
          class="mt-1 w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
          placeholder="Contoh: Team IT"
          :class="{ 'border-red-500 ring-red-500': error?.position }"
        />
        <p class="mt-1 text-xs text-red-500" v-if="error?.position">
          {{ error?.position?.join(", ") }}
        </p>
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

          <button
            type="button"
            @click="handleBack"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200"
            v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
          >
            Kembali
          </button>
        </div>
      </div>
    </form>
  </div>
</template>
