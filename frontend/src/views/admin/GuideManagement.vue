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
  <div class="min-h-screen from-gray-50 to-gray-100 py-6">
    <div class="w-full px-0">
      <!-- Header -->
      <div class="mb-8 px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
          <div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-2">Kelola Panduan</h1>
            <p class="text-sm sm:text-base text-gray-600">Tambah, edit, atau hapus panduan troubleshooting IT</p>
          </div>
          <div class="flex gap-3">
            <button @click="openForm()" class="px-4 sm:px-6 py-2.5 sm:py-3 bg-linear-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:shadow-lg transition-all duration-200 font-medium flex items-center gap-2 text-sm sm:text-base">
              <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              <span class="hidden sm:inline">Tambah Panduan</span>
              <span class="sm:hidden">Tambah</span>
            </button>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6 px-4 sm:px-6">
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 sm:p-4">
            <p class="text-xs sm:text-sm text-gray-600">Total Panduan</p>
            <p class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">{{ guides.length }}</p>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 sm:p-4">
            <p class="text-xs sm:text-sm text-gray-600">Panduan Aktif</p>
            <p class="text-2xl sm:text-3xl font-bold text-green-600 mt-1">{{ guides.filter((g) => g.is_active).length }}</p>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 sm:p-4">
            <p class="text-xs sm:text-sm text-gray-600">Panduan Nonaktif</p>
            <p class="text-2xl sm:text-3xl font-bold text-gray-600 mt-1">{{ guides.filter((g) => !g.is_active).length }}</p>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 sm:p-4">
            <p class="text-xs sm:text-sm text-gray-600">Total Kategori</p>
            <p class="text-2xl sm:text-3xl font-bold text-blue-600 mt-1">{{ categories.length }}</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 sm:p-6 mb-6 mx-4 sm:mx-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
            ></path>
          </svg>
          Filter & Cari
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
          <div class="relative sm:col-span-2 md:col-span-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Cari panduan..."
              class="w-full pl-9 sm:pl-10 pr-4 py-2 sm:py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @input="fetchGuides"
            />
          </div>
          <select v-model="filters.category_id" class="pl-3 sm:pl-4 pr-3 sm:pr-4 py-2 sm:py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:col-span-2 md:col-span-1" @change="fetchGuides">
            <option value="">Semua Kategori</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.title }}
            </option>
          </select>
        </div>
      </div>

      <!-- Category Description Alert -->
      <div v-if="filters.category_id && categories.find((c) => c.id == filters.category_id)?.description" class="bg-amber-50 border border-amber-200 rounded-lg p-3 sm:p-4 mb-6 mx-4 sm:mx-6">
        <div class="flex gap-3">
          <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
              clip-rule="evenodd"
            />
          </svg>
          <div>
            <p class="font-semibold text-amber-900">{{ categories.find((c) => c.id == filters.category_id)?.description }}</p>
          </div>
        </div>
      </div>

      <!-- Guides Table -->
      <div v-if="!showForm" class="mx-4 sm:mx-6">
        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-lg shadow-sm border border-gray-100 flex items-center justify-center h-96">
          <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-blue-600 mx-auto mb-4"></div>
            <p class="text-sm sm:text-base text-gray-600 font-medium">Memuat panduan...</p>
          </div>
        </div>

        <!-- Desktop Table View (hidden on mobile) -->
        <div v-else-if="guides.length > 0" class="hidden lg:block bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Judul</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Kategori</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Solusi</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Status</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="guide in guides" :key="guide.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium text-gray-900">{{ guide.title }}</p>
                    <p class="text-xs text-gray-500 mt-1 line-clamp-1">{{ guide.problem }}</p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <span class="text-lg">{{ guide.category?.icon }}</span>
                    <span class="text-sm text-gray-600">{{ guide.category?.title }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5.951-1.429 5.951 1.429a1 1 0 001.169-1.409l-7-14z"></path>
                    </svg>
                    {{ guide.solutions?.length || 0 }} solusi
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-medium', guide.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800']">
                    <span class="w-2 h-2 rounded-full mr-2" :class="guide.is_active ? 'bg-green-600' : 'bg-gray-600'"></span>
                    {{ guide.is_active ? "Aktif" : "Nonaktif" }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <button @click="openForm(guide)" class="px-3 py-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors text-sm font-medium flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                      Edit
                    </button>
                    <button @click="deleteGuide(guide.id)" class="px-3 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors text-sm font-medium flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile/Tablet Card View (shown on small screens) -->
        <div v-else-if="guides.length > 0" class="lg:hidden space-y-4">
          <div v-for="guide in guides" :key="guide.id" class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
            <!-- Header -->
            <div class="flex items-start justify-between gap-3 mb-3">
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-gray-900 text-sm sm:text-base mb-1">{{ guide.title }}</h3>
                <p class="text-xs sm:text-sm text-gray-500 line-clamp-2">{{ guide.problem }}</p>
              </div>
              <span :class="['inline-flex items-center px-2 py-1 rounded-full text-xs font-medium shrink-0', guide.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800']">
                <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="guide.is_active ? 'bg-green-600' : 'bg-gray-600'"></span>
                {{ guide.is_active ? "Aktif" : "Nonaktif" }}
              </span>
            </div>

            <!-- Meta Info -->
            <div class="flex flex-wrap items-center gap-3 mb-3 pb-3 border-b border-gray-100">
              <div class="flex items-center gap-1.5">
                <span class="text-base sm:text-lg">{{ guide.category?.icon }}</span>
                <span class="text-xs sm:text-sm text-gray-600">{{ guide.category?.title }}</span>
              </div>
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5.951-1.429 5.951 1.429a1 1 0 001.169-1.409l-7-14z"></path>
                </svg>
                {{ guide.solutions?.length || 0 }} solusi
              </span>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
              <button @click="openForm(guide)" class="flex-1 px-3 py-2 text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors text-xs sm:text-sm font-medium flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
              </button>
              <button @click="deleteGuide(guide.id)" class="flex-1 px-3 py-2 text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors text-xs sm:text-sm font-medium flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Hapus
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow-sm border border-gray-100 flex flex-col items-center justify-center py-16 px-4">
          <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <p class="text-sm sm:text-base text-gray-600 font-medium">Tidak ada panduan</p>
          <p class="text-xs sm:text-sm text-gray-500 mt-1">Mulai dengan menambahkan panduan baru</p>
        </div>
      </div>

      <!-- Form Modal -->
      <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-0 sm:p-4">
        <div class="bg-white sm:rounded-xl shadow-2xl w-full h-full sm:h-auto sm:max-w-4xl sm:max-h-[90vh] flex flex-col">
          <!-- Modal Header -->
          <div class="bg-linear-to-r from-blue-50 to-blue-100 border-b border-blue-200 px-4 sm:px-6 md:px-8 py-4 sm:py-5 md:py-6 flex justify-between items-start sm:items-center shrink-0">
            <div class="flex-1 min-w-0 pr-2">
              <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">
                {{ editingGuide ? "Edit Panduan" : "Tambah Panduan Baru" }}
              </h2>
              <p class="text-xs sm:text-sm text-gray-600 mt-0.5 sm:mt-1">{{ editingGuide ? "Perbarui panduan yang sudah ada" : "Buat panduan troubleshooting baru" }}</p>
            </div>
            <button @click="closeForm()" class="text-gray-400 hover:text-gray-600 p-1.5 sm:p-2 hover:bg-white rounded-lg transition-colors shrink-0">
              <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <!-- Modal Content - Single Column -->
          <form id="guide-form" @submit.prevent="handleSubmit" class="overflow-y-auto flex-1 p-4 sm:p-6 md:p-8 lg:p-12 space-y-5 sm:space-y-6 md:space-y-8">
            <!-- Kategori & Judul Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 md:gap-6">
              <div>
                <label class="block text-xs sm:text-sm font-semibold text-gray-900 mb-1.5 sm:mb-2">Kategori <span class="text-red-500">*</span></label>
                <select v-model="form.category_id" class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-200 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                  <option :value="null">Pilih Kategori</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.icon }} {{ cat.title }}</option>
                </select>
              </div>

              <div>
                <label class="block text-xs sm:text-sm font-semibold text-gray-900 mb-1.5 sm:mb-2">Judul Panduan <span class="text-red-500">*</span></label>
                <input
                  v-model="form.title"
                  type="text"
                  placeholder="Contoh: Printer Tidak Bisa Print"
                  class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-200 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                />
              </div>
            </div>

            <!-- Deskripsi Masalah -->
            <div>
              <label class="block text-xs sm:text-sm font-semibold text-gray-900 mb-1.5 sm:mb-2">Deskripsi Masalah <span class="text-red-500">*</span></label>
              <textarea
                v-model="form.problem"
                placeholder="Jelaskan masalah yang akan dipecahkan..."
                class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-200 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent h-20 sm:h-24 resize-none"
                required
              ></textarea>
            </div>

            <!-- Solusi Langkah demi Langkah -->
            <div>
              <label class="flex text-xs sm:text-sm font-semibold text-gray-900 items-center gap-1.5 sm:gap-2 mb-3 sm:mb-4">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Solusi Langkah demi Langkah <span class="text-red-500">*</span>
              </label>

              <div class="space-y-3 sm:space-y-4">
                <div v-for="(solution, index) in form.solutions" :key="index" class="flex gap-2 sm:gap-3 md:gap-4 items-start">
                  <div class="shrink-0 mt-2 sm:mt-3 md:mt-4">
                    <span class="inline-flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-full bg-blue-600 text-white text-xs sm:text-sm font-bold">
                      {{ index + 1 }}
                    </span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <textarea
                      v-model="form.solutions[index]"
                      :placeholder="`Langkah ${index + 1} - Jelaskan solusi dengan detail`"
                      class="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 border border-gray-200 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent h-20 sm:h-24 resize-none"
                      required
                    ></textarea>
                  </div>
                  <button v-if="form.solutions.length > 1" type="button" @click="removeSolution(index)" class="shrink-0 mt-2 sm:mt-3 md:mt-4 p-1.5 sm:p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </div>

              <button type="button" @click="addSolution" class="mt-4 sm:mt-5 md:mt-6 inline-flex items-center gap-1.5 sm:gap-2 px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors text-xs sm:text-sm font-medium">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Solusi
              </button>
            </div>

            <!-- Status -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 sm:p-4">
              <label class="flex items-start sm:items-center cursor-pointer">
                <input v-model="form.is_active" type="checkbox" class="w-4 h-4 sm:w-5 sm:h-5 mt-0.5 sm:mt-0 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 shrink-0" />
                <div class="ml-2.5 sm:ml-3">
                  <span class="text-xs sm:text-sm font-medium text-gray-900 block">Panduan Aktif</span>
                  <span class="text-xs text-gray-500 block sm:inline sm:ml-2">(Akan ditampilkan ke pengguna)</span>
                </div>
              </label>
            </div>
          </form>

          <!-- Actions - Fixed at Bottom -->
          <div class="border-t border-gray-200 bg-gray-50 px-4 sm:px-6 md:px-8 py-3 sm:py-4 flex gap-2 sm:gap-3 shrink-0">
            <button type="submit" form="guide-form" class="flex-1 px-3 sm:px-4 py-2.5 sm:py-3 bg-linear-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:shadow-lg transition-all duration-200 font-medium flex items-center justify-center gap-1.5 sm:gap-2 text-xs sm:text-sm md:text-base">
              <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <span class="hidden sm:inline">{{ editingGuide ? "Simpan Perubahan" : "Tambah Panduan" }}</span>
              <span class="sm:hidden">{{ editingGuide ? "Simpan" : "Tambah" }}</span>
            </button>
            <button type="button" @click="closeForm()" class="px-4 sm:px-5 md:px-6 py-2.5 sm:py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium text-xs sm:text-sm md:text-base">Batal</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
