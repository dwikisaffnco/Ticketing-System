<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import { useTicketStore } from "@/stores/ticket";
import { storeToRefs } from "pinia";
import feather from "feather-icons";

const ticketStore = useTicketStore();
const { success, error, loading } = storeToRefs(ticketStore);
const { createTicket } = ticketStore;

// TODO: Create form ref with ticket fields
// Hint: You'll need title, description, priority
const form = ref({
  title: "",
  description: "",
  priority: "",
  attachment: null,
});

const attachmentInputRef = ref(null);
const attachmentPreviewUrl = ref(null);

const openAttachmentPicker = () => {
  attachmentInputRef.value?.click?.();
};

const handleAttachmentChange = (e) => {
  const file = e.target.files?.[0] ?? null;
  form.value.attachment = file;

  if (attachmentPreviewUrl.value) {
    URL.revokeObjectURL(attachmentPreviewUrl.value);
    attachmentPreviewUrl.value = null;
  }

  if (file) {
    attachmentPreviewUrl.value = URL.createObjectURL(file);
  }
};

// TODO: Implement handleSubmit function
// Hint: This should call the createTicket function from ticket store
// and handle any errors
const handleSubmit = async () => {
  const fd = new FormData();
  fd.append("title", form.value.title ?? "");
  fd.append("description", form.value.description ?? "");
  fd.append("priority", form.value.priority ?? "");
  if (form.value.attachment) {
    fd.append("attachment", form.value.attachment);
  }

  await createTicket(fd);
};

// TODO: Implement onMounted hook
// Hint: Initialize feather icons
onMounted(async () => {
  feather.replace();
});

onUnmounted(() => {
  if (attachmentPreviewUrl.value) {
    URL.revokeObjectURL(attachmentPreviewUrl.value);
  }
});
</script>

<template>
  <div class="mb-6">
    <RouterLink :to="{ name: 'app.dashboard' }" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-800" v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }">
      <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
      Kembali ke Daftar Tiket
    </RouterLink>
  </div>

  <!-- Create Ticket Form -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-6 border-b border-gray-100">
      <h1 class="text-2xl font-bold text-gray-800">Buat Tiket Baru</h1>
      <p class="text-sm text-gray-500 mt-1">Isi form di bawah ini untuk membuat tiket baru</p>
    </div>
    <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
      <!-- Judul Tiket -->
      <div>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Tiket</label>
        <input
          type="text"
          id="title"
          v-model="form.title"
          placeholder="Contoh: Gangguan Jaringan WiFi"
          class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
        />
        <div v-if="error?.title" class="flex items-center mt-2">
          <p class="text-xs text-red-500">{{ error.title[0] }}</p>
        </div>
      </div>

      <!-- Deskripsi -->
      <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Masalah</label>
        <textarea
          id="description"
          v-model="form.description"
          rows="6"
          placeholder="Jelaskan masalah Anda secara detail. Sertakan informasi seperti:&#10;- Kapan masalah mulai terjadi&#10;- Apa yang sudah Anda coba&#10;- Dampak masalah terhadap pekerjaan"
          class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
        ></textarea>
        <div v-if="error?.description" class="flex items-center mt-2">
          <p class="text-xs text-red-500">{{ error.description[0] }}</p>
        </div>
      </div>

      <!-- Prioritas -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Prioritas</label>
        <div class="grid grid-cols-3 gap-4">
          <label class="relative flex cursor-pointer rounded-lg border overflow-hidden" :class="[form.priority === 'low' ? 'border-green-200 bg-green-50' : 'border-gray-200']">
            <input type="radio" v-model="form.priority" value="low" class="sr-only" />
            <div class="flex w-full items-center justify-between p-3 sm:p-4">
              <div class="flex items-center min-w-0">
                <div class="text-sm">
                  <p class="font-medium text-gray-900">Rendah</p>
                  <p class="text-gray-500 text-xs sm:text-sm">Tidak mendesak</p>
                </div>
              </div>
              <div class="shrink-0 text-green-600 ml-2" v-show="form.priority === 'low'">
                <i data-feather="check-circle" class="w-5 h-5"></i>
              </div>
            </div>
          </label>
          <label class="relative flex cursor-pointer rounded-lg border overflow-hidden" :class="[form.priority === 'medium' ? 'border-yellow-200 bg-yellow-50' : 'border-gray-200']">
            <input type="radio" v-model="form.priority" value="medium" class="sr-only" />
            <div class="flex w-full items-center justify-between p-3 sm:p-4">
              <div class="flex items-center min-w-0">
                <div class="text-sm">
                  <p class="font-medium text-gray-900">Sedang</p>
                  <p class="text-gray-500 text-xs sm:text-sm">Normal</p>
                </div>
              </div>
              <div class="shrink-0 text-yellow-600 ml-2" v-show="form.priority === 'medium'">
                <i data-feather="check-circle" class="w-5 h-5"></i>
              </div>
            </div>
          </label>
          <label class="relative flex cursor-pointer rounded-lg border overflow-hidden" :class="[form.priority === 'high' ? 'border-red-200 bg-red-50' : 'border-gray-200']">
            <input type="radio" v-model="form.priority" value="high" class="sr-only" />
            <div class="flex w-full items-center justify-between p-3 sm:p-4">
              <div class="flex items-center min-w-0">
                <div class="text-sm">
                  <p class="font-medium text-gray-900">Tinggi</p>
                  <p class="text-gray-500 text-xs sm:text-sm">Mendesak</p>
                </div>
              </div>
              <div class="shrink-0 text-red-600 ml-2" v-show="form.priority === 'high'">
                <i data-feather="check-circle" class="w-5 h-5"></i>
              </div>
            </div>
          </label>
        </div>
        <div v-if="error?.priority" class="flex items-center mt-2">
          <p class="text-xs text-red-500">{{ error.priority[0] }}</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran (Gambar, max 5MB)</label>

        <input ref="attachmentInputRef" type="file" accept="image/*" @change="handleAttachmentChange" class="hidden" />

        <button
          type="button"
          @click="openAttachmentPicker"
          class="inline-flex items-center px-4 py-2 border border-gray-200 rounded-lg text-sm bg-white hover:bg-gray-50"
          v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
        >
          <i data-feather="paperclip" class="w-4 h-4 mr-2"></i>
          Lampiran
        </button>

        <div v-if="form.attachment" class="mt-3">
          <div class="text-xs text-gray-500">{{ form.attachment?.name }}</div>
          <img v-if="attachmentPreviewUrl" :src="attachmentPreviewUrl" alt="Preview" class="mt-2 w-40 h-40 object-cover rounded-lg border border-gray-200" />
        </div>

        <div v-if="error?.attachment" class="flex items-center mt-2">
          <p class="text-xs text-red-500">{{ error.attachment[0] }}</p>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end space-x-4">
        <RouterLink :to="{ name: 'app.dashboard' }" class="px-6 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50" v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }">
          Batal
        </RouterLink>
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700" :disabled="loading" v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }">
          <i data-feather="send" class="w-4 h-4 inline-block mr-2"></i>
          {{ loading ? "Mengirim..." : "Kirim Tiket" }}
        </button>
      </div>
    </form>
  </div>
</template>
