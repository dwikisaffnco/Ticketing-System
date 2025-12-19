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
const selectedDivision = ref("");
const fileInput = ref(null);
const uploadProgress = ref(0);
const selectedUsers = ref([]);








const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const divisions = computed(() => {
  const divs = new Set(users.value.map((u) => u.division).filter(Boolean));
  return Array.from(divs).sort();
});

const filteredUsers = computed(() => {
  let result = users.value ?? [];

  if (selectedDivision.value) {
    result = result.filter((u) => u.division === selectedDivision.value);
  }

  const q = (search.value ?? "").toString().trim().toLowerCase();
  if (!q) return result;

  return result.filter((u) => {
    const haystack = [u?.name, u?.email, u?.division, u?.position].filter(Boolean).join(" ").toLowerCase();
    return haystack.includes(q);
  });
});

const isAllSelected = computed(() => {
  const deletableUsers = filteredUsers.value.filter((u) => u.id !== user.value?.id);
  if (deletableUsers.length === 0) return false;
  return deletableUsers.every((u) => selectedUsers.value.includes(u.id));
});

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedUsers.value = [];
  } else {
    selectedUsers.value = filteredUsers.value
      .filter((u) => u.id !== user.value?.id)
      .map((u) => u.id);
  }
};

const toggleSelection = (id) => {
  if (selectedUsers.value.includes(id)) {
    selectedUsers.value = selectedUsers.value.filter((uId) => uId !== id);
  } else {
    selectedUsers.value.push(id);
  }
};



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

const handleDownloadTemplate = async () => {
  try {
    const response = await axiosInstance.get("/admin/users/import/template", {
      responseType: "blob",
    });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "users_template.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (e) {
    console.error(e);
    Swal.fire("Gagal", "Gagal mengunduh template.", "error");
  }
};

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append("file", file);

  loading.value = true;
  uploadProgress.value = 0;
  try {
    const response = await axiosInstance.post("/admin/users/import", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
      onUploadProgress: (progressEvent) => {
        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        uploadProgress.value = percentCompleted;
      },
    });

    const msg = response?.data?.message || "Import berhasil";
    const errors = response?.data?.errors || [];

    let text = msg;
    if (errors.length > 0) {
      text += "\n\nError:\n" + errors.join("\n");
    }

    await Swal.fire({
      icon: errors.length > 0 ? "warning" : "success",
      title: "Import Selesai",
      text: text,
      width: errors.length > 0 ? "800px" : undefined,
    });

    await fetchUsers();
  } catch (e) {
    const err = handleError(e);
    Swal.fire("Gagal", typeof err === "string" ? err : "Terjadi kesalahan saat import.", "error");
  } finally {
    loading.value = false;
    uploadProgress.value = 0;
    event.target.value = ""; // convert to empty string to allow re-uploading same file
  }
};

const handleBulkDelete = async () => {
  if (selectedUsers.value.length === 0) return;

  const result = await Swal.fire({
    icon: "warning",
    title: "Hapus user terpilih?",
    text: `${selectedUsers.value.length} users akan dihapus.`,
    showCancelButton: true,
    confirmButtonText: "Ya, hapus",
    cancelButtonText: "Batal",
    confirmButtonColor: "#dc2626",
  });

  if (!result.isConfirmed) return;

  loading.value = true;
  try {
    await axiosInstance.delete("/admin/users/bulk", { data: { ids: selectedUsers.value } });
    await fetchUsers();
    selectedUsers.value = [];
    Swal.fire("Berhasil", "Users berhasil dihapus", "success");
  } catch (e) {
    const err = handleError(e);
    Swal.fire("Gagal", typeof err === "string" ? err : "Terjadi kesalahan", "error");
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
      <div class="flex gap-2 ml-2">
        <button
          @click="handleDownloadTemplate"
          class="hidden sm:inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50"
          v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
        >
          Template CSV
        </button>
        <button
          @click="triggerFileInput"
          class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700"
          v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
        >
          Import CSV
        </button>
        <input type="file" ref="fileInput" class="hidden" accept=".csv" @change="handleFileUpload" />
        <RouterLink
            :to="{ name: 'admin.users.create' }"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
            v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
        >
            Tambah User
        </RouterLink>
      </div>
    </div>

    <div v-if="error && typeof error === 'string'" class="text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg px-4 py-2">
      {{ error }}
    </div>

    <!-- Progress Bar -->
    <!-- Progress Bar -->
    <div v-if="loading && uploadProgress > 0" class="mb-4">
      <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div 
          class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" 
          :style="{ width: `${uploadProgress}%` }"
        ></div>
      </div>
      <div class="text-xs text-center mt-2 text-gray-600">{{ uploadProgress }}% Uploading...</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm font-semibold text-gray-900">Users</div>
        <div class="flex items-center gap-2">
            <button
            v-if="selectedUsers.length > 0"
            @click="handleBulkDelete"
            class="text-sm text-red-600 hover:text-red-800 font-medium mr-2"
          >
            Hapus ({{ selectedUsers.length }})
          </button>
           <select
            v-model="selectedDivision"
            class="pl-3 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
          >
            <option value="">Semua Divisi</option>
            <option v-for="div in divisions" :key="div" :value="div">{{ div }}</option>
          </select>
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
              <th class="px-4 py-3 text-left">
                <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Divisi</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posisi</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-50">
            <tr v-if="loading">
              <td class="px-4 py-4 text-sm text-gray-500" colspan="6">Memuat data...</td>
            </tr>
            <tr v-else-if="users.length === 0">
              <td class="px-4 py-4 text-sm text-gray-500" colspan="6">Belum ada user.</td>
            </tr>
            <tr v-else-if="filteredUsers.length === 0">
              <td class="px-4 py-4 text-sm text-gray-500" colspan="6">User tidak ditemukan.</td>
            </tr>
            <tr v-else v-for="u in filteredUsers" :key="u.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <input 
                  type="checkbox" 
                  :checked="selectedUsers.includes(u.id)" 
                  @change="toggleSelection(u.id)"
                  :disabled="user?.id === u.id"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 disabled:opacity-50" 
                />
              </td>
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
