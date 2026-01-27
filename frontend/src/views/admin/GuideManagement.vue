<script setup>
import { onMounted, ref } from "vue";
import { axiosInstance } from "@/plugins/axios";

const guides = ref([]);
const categories = ref([]);
const loading = ref(false);
const showForm = ref(false);
const editingGuide = ref(null);
const filters = ref({
  search: "",
  category_id: "",
});

const form = ref({
  category_id: null,
  title: "",
  problem: "",
  solutions: [""],
  is_active: true,
});

const fetchGuides = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (filters.value.search) params.append("search", filters.value.search);
    if (filters.value.category_id) params.append("category_id", filters.value.category_id);

    const response = await axiosInstance.get(`/admin/guides?${params}`);
    guides.value = response.data.data;
  } catch (error) {
    console.error("Error fetching guides:", error);
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await axiosInstance.get("/guides/categories");
    categories.value = response.data.data;
  } catch (error) {
    console.error("Error fetching categories:", error);
  }
};

const openForm = (guide = null) => {
  if (guide) {
    editingGuide.value = guide.id;
    form.value = {
      category_id: guide.category_id,
      title: guide.title,
      problem: guide.problem,
      solutions: guide.solutions || [""],
      is_active: guide.is_active,
    };
  } else {
    editingGuide.value = null;
    form.value = {
      category_id: null,
      title: "",
      problem: "",
      solutions: [""],
      is_active: true,
    };
  }
  showForm.value = true;
};

const closeForm = () => {
  showForm.value = false;
  editingGuide.value = null;
};

const handleSubmit = async () => {
  try {
    if (editingGuide.value) {
      await axiosInstance.put(`/admin/guides/${editingGuide.value}`, form.value);
    } else {
      await axiosInstance.post("/admin/guides", form.value);
    }
    closeForm();
    await fetchGuides();
  } catch (error) {
    console.error("Error saving guide:", error);
  }
};

const deleteGuide = async (id) => {
  if (confirm("Apakah Anda yakin ingin menghapus panduan ini?")) {
    try {
      await axiosInstance.delete(`/admin/guides/${id}`);
      await fetchGuides();
    } catch (error) {
      console.error("Error deleting guide:", error);
    }
  }
};

const addSolution = () => {
  form.value.solutions.push("");
};

const removeSolution = (index) => {
  form.value.solutions.splice(index, 1);
};

onMounted(() => {
  fetchCategories();
  fetchGuides();
});
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Kelola Panduan</h1>
        <p class="text-sm text-gray-600 mt-1">Kelola panduan troubleshooting IT</p>
      </div>
      <button @click="openForm()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">+ Tambah Panduan</button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input v-model="filters.search" type="text" placeholder="Cari panduan..." class="border border-gray-200 rounded px-3 py-2 text-sm" @input="fetchGuides" />
        <select v-model="filters.category_id" class="border border-gray-200 rounded px-3 py-2 text-sm" @change="fetchGuides">
          <option value="">Semua Kategori</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.title }}
          </option>
        </select>
      </div>
    </div>

    <!-- Guides Table -->
    <div v-if="!showForm" class="bg-white rounded-lg shadow-sm border border-gray-100">
      <div v-if="loading" class="p-6 text-center text-gray-500">Loading...</div>
      <table v-else class="w-full">
        <thead class="border-b border-gray-200">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Judul</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Kategori</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="guide in guides" :key="guide.id" class="border-b border-gray-100 hover:bg-gray-50">
            <td class="px-6 py-3 text-sm text-gray-900">{{ guide.title }}</td>
            <td class="px-6 py-3 text-sm text-gray-600">{{ guide.category?.title }}</td>
            <td class="px-6 py-3 text-sm">
              <span :class="['px-2 py-1 rounded text-xs font-medium', guide.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800']">
                {{ guide.is_active ? "Aktif" : "Nonaktif" }}
              </span>
            </td>
            <td class="px-6 py-3 text-sm space-x-2">
              <button @click="openForm(guide)" class="text-blue-600 hover:text-blue-700 font-medium">Edit</button>
              <button @click="deleteGuide(guide.id)" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="guides.length === 0" class="p-6 text-center text-gray-500">Tidak ada panduan</div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold text-gray-900">
            {{ editingGuide ? "Edit Panduan" : "Tambah Panduan" }}
          </h2>
          <button @click="closeForm()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
            <select v-model="form.category_id" class="w-full border border-gray-200 rounded px-3 py-2 text-sm" required>
              <option :value="null">Pilih Kategori</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.title }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
            <input v-model="form.title" type="text" class="w-full border border-gray-200 rounded px-3 py-2 text-sm" required />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Masalah</label>
            <textarea v-model="form.problem" class="w-full border border-gray-200 rounded px-3 py-2 text-sm h-24" required></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Solusi</label>
            <div class="space-y-2">
              <div v-for="(solution, index) in form.solutions" :key="index" class="flex gap-2">
                <textarea v-model="form.solutions[index]" class="flex-1 border border-gray-200 rounded px-3 py-2 text-sm h-16" :placeholder="`Solusi ${index + 1}`" required></textarea>
                <button v-if="form.solutions.length > 1" type="button" @click="removeSolution(index)" class="text-red-600 hover:text-red-700 font-medium px-2">Hapus</button>
              </div>
            </div>
            <button type="button" @click="addSolution" class="mt-2 text-blue-600 hover:text-blue-700 text-sm font-medium">+ Tambah Solusi</button>
          </div>

          <div>
            <label class="flex items-center">
              <input v-model="form.is_active" type="checkbox" class="w-4 h-4 border-gray-300 rounded" />
              <span class="ml-2 text-sm text-gray-700">Aktifkan panduan ini</span>
            </label>
          </div>

          <div class="flex gap-3 pt-4 border-t border-gray-200">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
              {{ editingGuide ? "Simpan Perubahan" : "Tambah Panduan" }}
            </button>
            <button type="button" @click="closeForm()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Batal</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
