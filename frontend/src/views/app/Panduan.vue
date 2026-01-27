<script setup>
import { ref, onMounted, computed } from "vue";
import { axiosInstance } from "@/plugins/axios";

const categories = ref([]);
const guides = ref([]);
const selectedCategory = ref(null);
const currentGuide = ref(null);
const loading = ref(true);
const error = ref(null);

const selectedCategoryGuides = computed(() => {
  if (!selectedCategory.value) return [];
  return guides.value.filter((guide) => guide.category_id === selectedCategory.value.id);
});

onMounted(async () => {
  try {
    loading.value = true;
    // Fetch categories
    const categoriesRes = await axiosInstance.get("/guides/categories");
    categories.value = categoriesRes.data.data;

    // Fetch all guides
    const guidesRes = await axiosInstance.get("/guides");
    guides.value = guidesRes.data.data;

    // Select first category by default
    if (categories.value.length > 0) {
      selectedCategory.value = categories.value[0];
    }
  } catch (err) {
    console.error("Error loading guides:", err);
    error.value = "Gagal memuat panduan. Silakan refresh halaman.";
  } finally {
    loading.value = false;
  }
});

const selectGuide = (guide) => {
  currentGuide.value = guide;
};

const selectCategory = (category) => {
  selectedCategory.value = category;
  currentGuide.value = null;
};
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-6 px-4">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Panduan Troubleshooting IT</h1>
        <p class="text-gray-600">Solusi cepat untuk masalah IT yang umum dihadapi</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center min-h-96">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p class="text-gray-600">Memuat panduan...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-800">
        {{ error }}
      </div>

      <!-- Main Content -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Categories -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sticky top-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Kategori</h2>
            <div class="space-y-2">
              <button
                v-for="category in categories"
                :key="category.id"
                @click="selectCategory(category)"
                :class="['w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-colors', selectedCategory?.id === category.id ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-700 hover:bg-gray-50']"
              >
                <span class="mr-2">{{ category.icon }}</span>
                {{ category.title }}
              </button>
            </div>
          </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-3">
          <!-- Category Guide List -->
          <div v-if="!currentGuide" class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
              <h2 class="text-2xl font-bold text-gray-900 mb-4">
                {{ selectedCategory?.title }}
              </h2>
              <p class="text-gray-600 mb-6">Pilih salah satu masalah di bawah untuk melihat solusinya</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <button v-for="guide in selectedCategoryGuides" :key="guide.id" @click="selectGuide(guide)" class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 hover:shadow-md hover:border-blue-200 transition-all text-left group">
                <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors mb-1">
                  {{ guide.title }}
                </h3>
                <p class="text-sm text-gray-600">{{ guide.problem }}</p>
              </button>
            </div>
          </div>

          <!-- Guide Detail -->
          <div v-else class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <button @click="currentGuide = null" class="inline-flex items-center text-blue-600 hover:text-blue-700 mb-4 font-medium text-sm">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Kembali
              </button>

              <div class="border-t border-gray-200 pt-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ currentGuide.title }}</h1>
                <p class="text-gray-600 mb-6">{{ currentGuide.problem }}</p>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                  <h3 class="font-semibold text-blue-900 mb-3">Solusi:</h3>
                  <ol class="space-y-2">
                    <li v-for="(solution, index) in currentGuide.solutions" :key="index" class="flex items-start">
                      <span class="shrink-0 flex items-center justify-center h-6 w-6 rounded-full bg-blue-600 text-white text-sm font-medium mr-3 mt-0.5">
                        {{ index + 1 }}
                      </span>
                      <span class="text-gray-700">{{ solution }}</span>
                    </li>
                  </ol>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                  <h4 class="font-semibold text-amber-900 mb-2">💡 Tips:</h4>
                  <p class="text-amber-800 text-sm">
                    Jika masalah belum teratasi setelah mencoba solusi di atas, jangan ragu untuk menghubungi Team IT. Sertakan informasi detail tentang masalah yang Anda hadapi untuk bantuan yang lebih cepat.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
