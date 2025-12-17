<script setup>
import { computed, onMounted, ref } from "vue";
import { axiosInstance } from "@/plugins/axios";
import { handleError } from "@/helpers/errorHelper";
import { useAuthStore } from "@/stores/auth";
import { storeToRefs } from "pinia";
import Swal from "sweetalert2";

const loading = ref(false);
const error = ref(null);
const users = ref([]);
const search = ref("");

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const filteredUsers = computed(() => {
  const q = (search.value ?? "").toString().trim().toLowerCase();
  if (!q) return users.value;

  return (users.value ?? []).filter((u) => {
    const haystack = [u?.name, u?.email, u?.division, u?.position].filter(Boolean).join(" ").toLowerCase();
    return haystack.includes(q);
  });
});

const fetchUsers = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await axiosInstance.get("/admin/users");
    users.value = response?.data?.data ?? [];
  } catch (e) {
    error.value = handleError(e);
  } finally {
    loading.value = false;
  }
};

const handleDelete = async (u) => {
  if (!u?.id) return;
  if (user.value?.id && u.id === user.value.id) {
    await Swal.fire({
      icon: "warning",
      title: "Tidak bisa",
      text: "Tidak bisa menghapus akun sendiri",
    });
    return;
  }

  const result = await Swal.fire({
    icon: "warning",
    title: "Hapus user?",
    text: `User ${u?.name ?? ""} akan dihapus beserta data terkait.`,
    showCancelButton: true,
    confirmButtonText: "Ya, hapus",
    cancelButtonText: "Batal",
    confirmButtonColor: "#dc2626",
  });

  if (!result.isConfirmed) return;

  loading.value = true;
  error.value = null;
  try {
    await axiosInstance.delete(`/admin/users/${u.id}`);
    await fetchUsers();

    await Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "User berhasil dihapus",
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

onMounted(() => {
  fetchUsers();
});
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-lg font-semibold text-gray-900">List Users</h1>
        <p class="text-sm text-gray-500">Daftar semua akun user/admin.</p>
      </div>
      <RouterLink
        :to="{ name: 'admin.users.create' }"
        class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
        v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
      >
        Tambah User
      </RouterLink>
    </div>

    <div v-if="error && typeof error === 'string'" class="text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg px-4 py-2">
      {{ error }}
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm font-semibold text-gray-900">Users</div>
        <div class="flex items-center gap-2">
          <div class="relative">
            <input
              v-model="search"
              type="text"
              placeholder="Cari nama, email, divisi, posisi..."
              class="w-64 max-w-[70vw] pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            />
            <i data-feather="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
          </div>
          <button type="button" class="text-sm text-gray-600 hover:text-gray-800" @click="fetchUsers" :disabled="loading" v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }">Refresh</button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Divisi</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posisi</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-50">
            <tr v-if="loading">
              <td class="px-4 py-4 text-sm text-gray-500" colspan="5">Memuat data...</td>
            </tr>
            <tr v-else-if="users.length === 0">
              <td class="px-4 py-4 text-sm text-gray-500" colspan="5">Belum ada user.</td>
            </tr>
            <tr v-else-if="filteredUsers.length === 0">
              <td class="px-4 py-4 text-sm text-gray-500" colspan="5">User tidak ditemukan.</td>
            </tr>
            <tr v-else v-for="u in filteredUsers" :key="u.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm text-gray-900">{{ u.name ?? "-" }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ u.email ?? "-" }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ u.division ?? "-" }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ u.position ?? "-" }}</td>
              <td class="px-4 py-3 text-sm text-right">
                <div class="inline-flex items-center gap-2">
                  <RouterLink :to="{ name: 'admin.users.edit', params: { id: u.id } }" class="text-blue-600 hover:text-blue-800" v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }">Edit</RouterLink>
                  <button
                    type="button"
                    class="text-red-600 hover:text-red-800 disabled:opacity-50"
                    @click="handleDelete(u)"
                    :disabled="loading || (user?.id && u.id === user.id)"
                    v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
                  >
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
