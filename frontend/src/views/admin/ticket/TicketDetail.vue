<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue";
import { useTicketStore } from "@/stores/ticket";
import { storeToRefs } from "pinia";
import { capitalize } from "lodash";
import feather from "feather-icons";
import { DateTime } from "luxon";
import { useRoute, useRouter } from "vue-router";
import Swal from "sweetalert2";

const route = useRoute();
const router = useRouter();

const ticket = ref({});
const form = ref({
  status: "",
  content: "",
  attachment: null,
});

const editMode = ref(false);
const editForm = ref({
  title: "",
  description: "",
  priority: "",
  attachment: null,
});
const editAttachmentPreviewUrl = ref(null);

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

const handleEditAttachmentChange = (e) => {
  const file = e.target.files?.[0] ?? null;
  editForm.value.attachment = file;

  if (editAttachmentPreviewUrl.value) {
    URL.revokeObjectURL(editAttachmentPreviewUrl.value);
    editAttachmentPreviewUrl.value = null;
  }

  if (file) {
    editAttachmentPreviewUrl.value = URL.createObjectURL(file);
  }
};

const ticketStore = useTicketStore();
const { success, error, loading } = storeToRefs(ticketStore);
const { fetchTicket, createTicketReply, updateTicket, deleteTicket } = ticketStore;

const fetchTicketDetail = async () => {
  const response = await fetchTicket(route.params.code);

  ticket.value = response;
  form.value.status = response.status;

  if (!editMode.value) {
    editForm.value.title = response.title ?? "";
    editForm.value.description = response.description ?? "";
    editForm.value.priority = response.priority ?? "";
    editForm.value.attachment = null;

    if (editAttachmentPreviewUrl.value) {
      URL.revokeObjectURL(editAttachmentPreviewUrl.value);
      editAttachmentPreviewUrl.value = null;
    }
  }
};

const startEdit = async () => {
  editMode.value = true;
  await Promise.resolve();
  feather.replace();
};

const cancelEdit = async () => {
  editMode.value = false;
  await fetchTicketDetail();
  await Promise.resolve();
  feather.replace();
};

const handleSaveEdit = async () => {
  const fd = new FormData();
  fd.append("title", editForm.value.title ?? "");
  fd.append("description", editForm.value.description ?? "");
  fd.append("priority", editForm.value.priority ?? "");
  if (editForm.value.attachment) {
    fd.append("attachment", editForm.value.attachment);
  }

  const updated = await updateTicket(route.params.code, fd);
  if (!updated) return;

  editMode.value = false;
  await fetchTicketDetail();

  await Swal.fire({
    icon: "success",
    title: "Berhasil",
    text: "Tiket berhasil diperbarui",
    timer: 1500,
    showConfirmButton: false,
  });

  await Promise.resolve();
  feather.replace();
};

const handleDeleteTicket = async () => {
  if (!ticket.value?.code) return;

  const result = await Swal.fire({
    icon: "warning",
    title: "Hapus tiket?",
    text: `Tiket #${ticket.value.code} akan dihapus permanen.`,
    showCancelButton: true,
    confirmButtonText: "Ya, hapus",
    cancelButtonText: "Batal",
    confirmButtonColor: "#dc2626",
  });

  if (!result.isConfirmed) return;

  const ok = await deleteTicket(ticket.value.code);
  if (!ok) return;

  await Swal.fire({
    icon: "success",
    title: "Berhasil",
    text: "Tiket berhasil dihapus",
    timer: 1500,
    showConfirmButton: false,
  });

  router.push({ name: "admin.ticket" });
};

// TODO: Implement handleSubmit function
// Hint: This should call createTicketReply with code and form
// Then refetch ticket details
const handleSubmit = async () => {
  const fd = new FormData();
  fd.append("status", form.value.status ?? "");
  fd.append("content", form.value.content ?? "");
  if (form.value.attachment) {
    fd.append("attachment", form.value.attachment);
  }

  await createTicketReply(route.params.code, fd);

  form.value.content = "";
  form.value.attachment = null;

  if (attachmentPreviewUrl.value) {
    URL.revokeObjectURL(attachmentPreviewUrl.value);
    attachmentPreviewUrl.value = null;
  }

  await fetchTicketDetail();
};

const resolveTicket = async () => {
  if (!confirm("Apakah Anda yakin ingin menyelesaikan tiket ini?")) return;

  const fd = new FormData();
  fd.append("status", "resolved");
  fd.append("content", "Tiket diselesaikan oleh admin."); // Optional system message

  await createTicketReply(route.params.code, fd);
  await fetchTicketDetail();
};

// TODO: Implement onMounted hook
// Hint: Fetch initial ticket details and initialize feather icons
onMounted(async () => {
  await fetchTicketDetail();

  feather.replace();
});

onBeforeUnmount(() => {
  if (attachmentPreviewUrl.value) {
    URL.revokeObjectURL(attachmentPreviewUrl.value);
  }

  if (editAttachmentPreviewUrl.value) {
    URL.revokeObjectURL(editAttachmentPreviewUrl.value);
  }
});
</script>

<template>
  <div class="p-6">
    <!-- Ticket Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <div v-if="!editMode">
              <h3 class="text-lg font-semibold text-gray-800">{{ ticket.title }}</h3>
              <div v-if="ticket.description" class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ ticket.description }}</div>
            </div>

            <div v-else class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                <input v-model="editForm.title" type="text" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea v-model="editForm.description" rows="4" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
                <select v-model="editForm.priority" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lampiran (opsional)</label>
                <input type="file" accept="image/*" @change="handleEditAttachmentChange" class="block w-full text-sm text-gray-600" />
                <div v-if="editAttachmentPreviewUrl" class="mt-2">
                  <img :src="editAttachmentPreviewUrl" alt="Preview Lampiran" class="h-24 w-auto rounded-lg border border-gray-200" />
                </div>
              </div>
            </div>
            <div class="mt-4 flex items-center space-x-4" v-if="!editMode">
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

              <span class="text-sm text-gray-500">Dilaporkan oleh {{ ticket.user?.name }}</span>
            </div>
          </div>
          <div class="flex items-center justify-end space-x-2 sm:space-x-4">
            <button v-if="!editMode && ticket.attachment_url" type="button" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50" @click="() => window.open(ticket.attachment_url, '_blank')">
              <i data-feather="download" class="w-4 h-4 inline-block mr-2"></i>
              Lampiran
            </button>

            <button
              v-if="!editMode"
              type="button"
              @click="startEdit"
              class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50"
              v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
            >
              <i data-feather="edit" class="w-4 h-4 inline-block mr-2"></i>
              Edit
            </button>

            <button v-if="editMode" type="button" @click="cancelEdit" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50">Batal</button>

            <button
              v-if="editMode"
              type="button"
              @click="handleSaveEdit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700"
              :disabled="loading"
              v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
            >
              Simpan
            </button>

            <button
              v-if="!editMode"
              type="button"
              @click="handleDeleteTicket"
              class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700"
              v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
            >
              <i data-feather="trash-2" class="w-4 h-4 inline-block mr-2"></i>
              Hapus
            </button>

            <button
              v-if="!editMode && ticket.status !== 'resolved'"
              @click="resolveTicket"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700"
              v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
            >
              <i data-feather="check-circle" class="w-4 h-4 inline-block mr-2"></i>
              Selesaikan Tiket
            </button>
          </div>
          <div v-if="ticket.attachment_url" class="mt-4">
            <a :href="ticket.attachment_url" target="_blank" class="inline-block">
              <img :src="ticket.attachment_url" alt="Lampiran Ticket" class="h-28 w-auto rounded-lg border border-gray-200" />
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Discussion Thread -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div v-for="reply in ticket.ticket_replies" class="p-6 border-b border-gray-100" v-if="ticket.ticket_replies?.length > 0" v-motion-slide-visible-left>
        <div class="flex items-start space-x-4">
          <img :src="`https://ui-avatars.com/api/?name=${reply.user.name}&background=0D8ABC&color=fff`" :alt="reply.user.name" class="w-10 h-10 rounded-full" />
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
              <a :href="reply.attachment_url" target="_blank" class="inline-block">
                <img :src="reply.attachment_url" alt="Lampiran Reply" class="h-28 w-auto rounded-lg border border-gray-200" />
              </a>
            </div>
          </div>
        </div>
      </div>
      <div v-else>
        <div class="p-6">
          <p class="text-sm text-gray-500">Belum ada tanggapan</p>
        </div>
      </div>

      <div class="p-6 border-t border-gray-100" v-if="ticket.status !== 'resolved'">
        <h4 class="text-sm font-medium text-gray-800 mb-4">Tambah Balasan</h4>
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="grid grid-cols-1 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status Tiket</label>
              <select v-model="form.status" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <option value="open" class="text-blue-700">Open</option>
                <option value="onprogress" class="text-yellow-700">On Progress</option>
                <option value="resolved" class="text-green-700">Resolved</option>
                <option value="rejected" class="text-red-700">Rejected</option>
              </select>
            </div>
          </div>
          <div>
            <textarea
              v-model="form.content"
              class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              :class="{ 'border-red-500 ring-red-500': error?.content }"
              rows="4"
              placeholder="Tulis jawaban Anda di sini..."
            ></textarea>
            <p class="mt-1 text-xs text-red-500" v-if="error?.content">
              {{ error?.content?.join(", ") }}
            </p>

            <div v-if="attachmentPreviewUrl" class="mt-3">
              <img :src="attachmentPreviewUrl" alt="Preview Lampiran" class="h-24 w-auto rounded-lg border border-gray-200" />
            </div>
          </div>
          <div class="flex items-center justify-between gap-3">
            <div class="flex items-center space-x-4">
              <label class="inline-flex items-center whitespace-nowrap px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 cursor-pointer">
                <i data-feather="paperclip" class="w-4 h-4 inline-block mr-2"></i>
                Lampiran
                <input type="file" accept="image/*" class="hidden" @change="handleAttachmentChange" />
              </label>
            </div>
            <button
              type="submit"
              class="inline-flex items-center justify-center whitespace-nowrap px-4 sm:px-6 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700"
              v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
            >
              <i data-feather="send" class="w-4 h-4 inline-block mr-2"></i>
              <span v-if="!loading"> Kirim Balasan </span>
              <span v-else> Loading... </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
