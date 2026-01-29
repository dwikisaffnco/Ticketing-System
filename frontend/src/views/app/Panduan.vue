<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { axiosInstance } from "@/plugins/axios";

const router = useRouter();
const categories = ref([]);
const guides = ref([]);
const selectedCategory = ref(null);
const currentGuide = ref(null);
const loading = ref(true);
const error = ref(null);

const sortedCategories = computed(() => {
  const sorted = [...categories.value].sort((a, b) => {
    // Put "Important" and "Policy & Regulations" at the top
    const priorityTitles = ["Important", "Policy & Regulations"];
    const aIndex = priorityTitles.indexOf(a.title);
    const bIndex = priorityTitles.indexOf(b.title);

    if (aIndex !== -1 && bIndex === -1) return -1;
    if (aIndex === -1 && bIndex !== -1) return 1;
    if (aIndex !== -1 && bIndex !== -1) return aIndex - bIndex;

    // Otherwise sort by order
    return a.order - b.order;
  });
  return sorted;
});

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

const parseText = (text) => {
  if (!text) return [];

  // Regular expression to find URLs and domains
  // Matches: https://example.com, http://example.com, example.com, subdomain.example.com
  const linkRegex = /(https?:\/\/[^\s]+|(?:^|[\s])(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}(?:\.[a-zA-Z]{2,})?)/g;
  const parts = [];
  let lastIndex = 0;
  let match;

  while ((match = linkRegex.exec(text)) !== null) {
    // Add text before link
    if (match.index > lastIndex) {
      parts.push({
        type: "text",
        content: text.slice(lastIndex, match.index),
      });
    }

    let urlText = match[0].trim();
    // Remove trailing punctuation if present
    urlText = urlText.replace(/[.,;:!?\)]+$/, "");

    // Build full URL if it's just a domain
    let fullUrl = urlText;
    if (!urlText.startsWith("http://") && !urlText.startsWith("https://")) {
      fullUrl = "https://" + urlText;
    }

    parts.push({
      type: "link",
      content: urlText,
      url: fullUrl,
    });

    lastIndex = match.index + match[0].length - (match[0].length - urlText.length);
  }

  // Add remaining text
  if (lastIndex < text.length) {
    parts.push({
      type: "text",
      content: text.slice(lastIndex),
    });
  }

  return parts.length > 0 ? parts : [{ type: "text", content: text }];
};
</script>

<template>
  <div class="min-h-screen from-gray-50 to-gray-100 py-4 md:py-8 px-3 md:px-4">
    <div class="max-w-7xl mx-auto">
      <!-- Header Section -->
      <div class="mb-5 md:mb-10">
        <div class="flex justify-between items-start mb-6">
          <div>
            <div class="flex flex-col items-start gap-2 md:gap-3 mb-2">
              <div class="w-9 h-9 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 md:w-6 md:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747S17.5 6.253 12 6.253z"></path>
                </svg>
              </div>
              <div>
                <h1 class="text-xl md:text-4xl font-bold text-gray-900 leading-tight">Panduan Troubleshooting IT</h1>
                <p class="text-gray-600 text-xs md:text-sm mt-0.5 md:mt-1">Solusi cepat untuk masalah IT yang umum dihadapi</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="hidden md:grid grid-cols-3 gap-4 mb-6">
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-600">Total Kategori</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ categories.length }}</p>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-600">Total Panduan</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ guides.length }}</p>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-600">Panduan Kategori Ini</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ selectedCategoryGuides.length }}</p>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center min-h-96">
        <div class="text-center">
          <div class="animate-spin rounded-full h-16 w-16 border-4 border-gray-200 border-t-blue-600 mx-auto mb-4"></div>
          <p class="text-gray-600 font-medium">Memuat panduan...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border-l-4 border-red-500 rounded-lg p-6 text-red-800">
        <div class="flex items-start">
          <svg class="w-6 h-6 text-red-500 mr-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd"
            ></path>
          </svg>
          <p>{{ error }}</p>
        </div>
      </div>

      <!-- Main Content -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-4 gap-4 lg:gap-6">
        <!-- Sidebar Categories -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 md:p-4 lg:p-6 lg:sticky lg:top-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"
                ></path>
              </svg>
              Kategori
            </h2>
            <div class="space-y-2">
              <button
                v-for="category in sortedCategories"
                :key="category.id"
                @click="selectCategory(category)"
                :class="[
                  'w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2',
                  selectedCategory?.id === category.id ? 'bg-blue-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 border border-transparent hover:border-gray-200',
                ]"
              >
                <span class="text-lg">{{ category.icon }}</span>
                <span class="flex-1">{{ category.title }}</span>
                <span :class="['text-xs px-2 py-1 rounded font-semibold', selectedCategory?.id === category.id ? 'bg-white text-blue-600' : 'bg-gray-300 text-gray-700']">{{
                  guides.filter((g) => g.category_id === category.id).length
                }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-3 space-y-4 md:space-y-6">
          <!-- Category Guide List -->
          <div v-if="!currentGuide" class="space-y-4 md:space-y-6">
            <!-- Category Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-6 lg:p-8">
              <div class="flex flex-col md:flex-row items-start md:items-center gap-3 md:gap-4 mb-4">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center text-xl md:text-2xl shrink-0">
                  {{ selectedCategory?.icon }}
                </div>
                <div>
                  <h2 class="text-xl md:text-3xl font-bold text-gray-900">{{ selectedCategory?.title }}</h2>
                  <p class="text-gray-600 mt-1 text-sm">{{ selectedCategoryGuides.length }} panduan tersedia</p>
                </div>
              </div>
              <div v-if="selectedCategory?.description" class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4 flex gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd"
                  />
                </svg>
                <p class="text-amber-900 font-semibold text-sm">{{ selectedCategory.description }}</p>
              </div>
              <p class="text-gray-600 text-sm mt-4">Pilih salah satu panduan di bawah untuk melihat solusi lengkap dan langkah-langkah yang detail</p>
            </div>

            <!-- Guides Grid -->
            <div v-if="selectedCategoryGuides.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
              <button
                v-for="guide in selectedCategoryGuides"
                :key="guide.id"
                @click="selectGuide(guide)"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-6 hover:shadow-lg hover:border-blue-300 transition-all duration-200 text-left group"
              >
                <div class="flex items-start justify-between mb-2">
                  <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors text-sm md:text-base flex-1">
                    {{ guide.title }}
                  </h3>
                  <svg class="w-4 h-4 md:w-5 md:h-5 text-gray-400 group-hover:text-blue-600 transition-colors shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </div>
                <p class="text-xs md:text-sm text-gray-600 line-clamp-2">{{ guide.problem }}</p>
                <div class="mt-3 pt-3 border-t border-gray-100">
                  <div class="flex items-center text-xs text-gray-500">
                    <svg class="w-3 h-3 md:w-4 md:h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M5 9V7a1 1 0 011-1h8a1 1 0 011 1v2m0 0a2 2 0 100-4H5a2 2 0 000 4m0 0V5a1 1 0 011-1h8a1 1 0 011 1v4m-6 4v2m0 0v2m0-6v-2m6 6v2m0 0v2m0-6v-2" clip-rule="evenodd"></path>
                    </svg>
                    {{ guide.solutions?.length || 0 }} solusi
                  </div>
                </div>
              </button>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
              <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                ></path>
              </svg>
              <p class="text-gray-600 font-medium">Tidak ada panduan dalam kategori ini</p>
              <p class="text-gray-500 text-sm mt-1">Pilih kategori lain untuk melihat panduan yang tersedia</p>
            </div>
          </div>

          <!-- Guide Detail -->
          <div v-else class="space-y-4 md:space-y-6">
            <!-- Back Button -->
            <button @click="currentGuide = null" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium text-sm group">
              <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
              </svg>
              Kembali ke Daftar
            </button>

            <!-- Guide Content -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-6 lg:p-8">
              <!-- Header -->
              <div class="mb-4 md:mb-6 lg:mb-8">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-3 mb-2 md:mb-3">
                  <span class="text-2xl md:text-3xl">{{ selectedCategory?.icon }}</span>
                  <span class="text-xs md:text-sm font-medium text-blue-600 bg-blue-50 px-2 md:px-3 py-1 rounded-full">
                    {{ selectedCategory?.title }}
                  </span>
                </div>
                <h1 class="text-lg md:text-4xl font-bold text-gray-900 mb-2 md:mb-3">{{ currentGuide.title }}</h1>
                <p class="text-sm md:text-lg text-gray-600">{{ currentGuide.problem }}</p>
              </div>

              <!-- Solutions Section -->
              <div class="mb-4 md:mb-6 lg:mb-8 border-t border-gray-200 pt-4 md:pt-6 lg:pt-8">
                <h3 class="text-lg md:text-xl font-bold text-gray-900 mb-3 md:mb-4 flex items-center gap-2">
                  <span class="flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full bg-blue-600 text-white text-xs md:text-sm font-bold">✓</span>
                  Solusi Langkah demi Langkah
                </h3>
                <div class="space-y-3 md:space-y-4">
                  <div v-for="(solution, index) in currentGuide.solutions" :key="index" class="flex gap-2 md:gap-3">
                    <div class="flex items-start shrink-0">
                      <span class="flex items-center justify-center w-6 h-6 md:w-7 md:h-7 lg:w-8 lg:h-8 rounded-full bg-linear-to-br from-blue-600 to-blue-700 text-white text-xs font-bold mt-0 md:mt-1 shrink-0">
                        {{ index + 1 }}
                      </span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="text-gray-700 text-sm md:text-base leading-relaxed whitespace-pre-wrap">
                        <template v-for="part in parseText(solution)" :key="part.content">
                          <span v-if="part.type === 'text'">{{ part.content }}</span>
                          <a v-else :href="part.url" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-700 underline font-medium">{{ part.content }}</a>
                        </template>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tip Section -->
              <div class="bg-linear-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-lg p-3 md:p-4 lg:p-6">
                <div class="flex gap-2 md:gap-3">
                  <div class="text-lg md:text-xl lg:text-2xl shrink-0 mt-0.5">💡</div>
                  <div>
                    <h4 class="font-bold text-amber-900 mb-1 md:mb-2 text-xs md:text-sm lg:text-base">Tips Penting</h4>
                    <p class="text-amber-800 text-xs md:text-sm leading-relaxed">
                      Jika masalah belum teratasi setelah mencoba semua solusi di atas, jangan ragu untuk menghubungi Team IT. Sertakan informasi detail tentang masalah yang Anda hadapi, screenshot error (jika ada), dan langkah-langkah yang
                      sudah Anda coba untuk bantuan yang lebih cepat.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
