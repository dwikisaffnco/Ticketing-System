<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue";
import { useTicketStore } from "@/stores/ticket";
import { storeToRefs } from "pinia";
import { capitalize } from "lodash";
import feather from "feather-icons";
import { DateTime } from "luxon";
import { useRoute } from "vue-router";
import { axiosInstance as axios } from "@/plugins/axios";

const ticketStore = useTicketStore();
const { success, error, loading } = storeToRefs(ticketStore);
const { fetchTicket, createTicketReply } = ticketStore;

const route = useRoute();

// TODO: Create refs for ticket and form
// Hint: You'll need ticket object and form with content field
const ticket = ref({});
const form = ref({
  content: "",
  attachment: null,
});

const attachmentPreviewUrl = ref(null);

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

const fetchTicketDetail = async () => {
  const response = await fetchTicket(route.params.code);

  ticket.value = response;
  form.value.status = response.status;
};

const handleSubmit = async () => {
  const fd = new FormData();
  fd.append("content", form.value.content ?? "");
  if (form.value.attachment) {
    fd.append("attachment", form.value.attachment);
  }

  await createTicketReply(route.params.code, fd);

  error.value = null;
  form.value.content = null;
  form.value.attachment = null;

  if (attachmentPreviewUrl.value) {
    URL.revokeObjectURL(attachmentPreviewUrl.value);
    attachmentPreviewUrl.value = null;
  }

  await fetchTicketDetail();
};

const downloadTicketAttachment = async () => {
  if (!ticket.value.code) return;

  try {
    const response = await axios.get(`/ticket/${ticket.value.code}/attachment/download`, {
      responseType: "blob",
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;

    const contentDisposition = response.headers["content-disposition"];
    let filename = `ticket-${ticket.value.code}-attachment`;
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="(.+)"/);
      if (filenameMatch) {
        filename = filenameMatch[1];
      }
    }

    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (err) {
    console.error("Download error:", err);
  }
};

const downloadReplyAttachment = async (replyId) => {
  if (!ticket.value.code || !replyId) return;

  try {
    const response = await axios.get(`/ticket-reply/${ticket.value.code}/${replyId}/attachment/download`, {
      responseType: "blob",
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;

    const contentDisposition = response.headers["content-disposition"];
    let filename = `ticket-${ticket.value.code}-reply-${replyId}-attachment`;
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="(.+)"/);
      if (filenameMatch) {
        filename = filenameMatch[1];
      }
    }

    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (err) {
    console.error("Download error:", err);
  }
};

onMounted(async () => {
  await fetchTicketDetail();

  feather.replace();
});

onBeforeUnmount(() => {
  if (attachmentPreviewUrl.value) {
    URL.revokeObjectURL(attachmentPreviewUrl.value);
  }
});
</script>

<template>
  <!-- Back Button -->
  <div class="mb-6">
    <RouterLink :to="{ name: 'app.dashboard' }" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-800">
      <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
      Kembali ke Daftar Tiket
    </RouterLink>
  </div>

  <!-- Ticket Info -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
    <div class="p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-800">{{ ticket.title }}</h3>
          <div v-if="ticket.description" class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ ticket.description }}</div>
          <div class="mt-4 flex items-center space-x-4">
            <span
              class="px-3 py-1 text-sm rounded-lg"
              :class="{
                'text-blue-700 bg-blue-100': ticket.status === 'open',
                'text-yellow-700 bg-yellow-100': ticket.status === 'onprogress',
                'text-green-700 bg-green-100': ticket.status === 'resolved',
                'text-red-700 bg-red-100': ticket.status === 'rejected',
              }"
            >
              {{ capitalize(ticket.status) }}
            </span>

            <span
              class="px-3 py-1 text-sm rounded-lg"
              :class="{
                'text-red-700 bg-red-100': ticket.priority === 'high',
                'text-yellow-700 bg-yellow-100': ticket.priority === 'medium',
                'text-green-700 bg-green-100': ticket.priority === 'low',
              }"
            >
              {{ capitalize(ticket.priority) }}
            </span>

            <span class="text-sm text-gray-500">#{{ ticket.code }}</span>
          </div>
          <div class="mt-2 text-sm text-gray-500">Dibuat pada {{ DateTime.fromISO(ticket.created_at).toFormat("dd MMMM yyyy, HH:mm") }}</div>
        </div>

        <div class="flex items-center justify-end space-x-4">
          <button v-if="ticket.attachment_url" @click="downloadTicketAttachment" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
            <i data-feather="download" class="w-4 h-4 inline-block mr-2"></i>
            Lampiran
          </button>
        </div>
      </div>
      <div v-if="ticket.attachment_url" class="mt-4">
        <a :href="ticket.attachment_url" target="_blank" class="inline-block">
          <img :src="ticket.attachment_url" alt="Lampiran Ticket" class="h-28 w-auto rounded-lg border border-gray-200" />
        </a>
      </div>
    </div>
  </div>

  <!-- Discussion Thread -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <!-- Thread Header -->
    <div class="p-6 border-b border-gray-100" v-for="reply in ticket.ticket_replies" :key="reply.id" v-motion-slide-visible-left>
      <div class="flex items-start space-x-4">
        <img :src="`https://ui-avatars.com/api/?name=${reply.user.name}&background=0D8ABC&color=fff`" alt="User" class="w-10 h-10 rounded-full" />
        <div class="flex-1">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-sm font-medium text-gray-800">{{ reply.user.name }}</h4>
              <p class="text-xs text-gray-500">
                {{ DateTime.fromISO(reply.created_at).toFormat("dd MMMM yyyy, HH:mm") }}
              </p>
            </div>
          </div>
          <div class="mt-3 text-sm text-gray-800">
            <p>{{ reply.content }}</p>
          </div>
          <div v-if="reply.attachment_url" class="mt-3">
            <div class="flex items-center space-x-2">
              <a :href="reply.attachment_url" target="_blank" class="inline-block">
                <img :src="reply.attachment_url" alt="Lampiran Reply" class="h-28 w-auto rounded-lg border border-gray-200" />
              </a>
              <button @click="downloadReplyAttachment(reply.id)" class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50" title="Download lampiran">
                <i data-feather="download" class="w-4 h-4"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reply Form -->
    <div class="p-6 border-t border-gray-100" v-if="ticket.status !== 'resolved'">
      <h4 class="text-sm font-medium text-gray-800 mb-4">Tambah Balasan</h4>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="group">
          <textarea
            v-model="form.content"
            class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            :class="{ 'border-red-500 ring-red-500': error?.content }"
            rows="4"
            placeholder="Tulis balasan Anda di sini..."
            minlength="3"
          ></textarea>
          <p class="mt-1 text-xs text-red-500" v-if="error?.content">
            {{ error?.content?.join(", ") }}
          </p>

          <div v-if="attachmentPreviewUrl" class="mt-3">
            <img :src="attachmentPreviewUrl" alt="Preview Lampiran" class="h-24 w-auto rounded-lg border border-gray-200" />
          </div>
        </div>
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <label class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 cursor-pointer">
              <i data-feather="paperclip" class="w-4 h-4 inline-block mr-2"></i>
              Lampiran
              <input type="file" accept="image/*" class="hidden" @change="handleAttachmentChange" />
            </label>
          </div>
          <button class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700" v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }">
            <i data-feather="send" class="w-4 h-4 inline-block mr-2"></i>
            <span v-if="!loading"> Kirim Balasan </span>
            <span v-else> Loading... </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
