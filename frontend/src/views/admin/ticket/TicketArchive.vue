<script setup>
import { onMounted, ref, watch } from "vue";
import { useTicketStore } from "@/stores/ticket";
import { storeToRefs } from "pinia";
import { capitalize, debounce } from "lodash";
import feather from "feather-icons";
import { DateTime } from "luxon";
import Swal from "sweetalert2";

const ticketStore = useTicketStore();
const { tickets } = storeToRefs(ticketStore);
const { fetchTickets, unarchiveTicket } = ticketStore;

const filters = ref({
  search: "",
  status: "",
  priority: "",
});

watch(
  filters,
  debounce(async () => {
    await fetchTickets({ ...filters.value, archived: 1 });
  }, 300),
  { deep: true }
);

watch(
  tickets,
  async () => {
    await Promise.resolve();
    feather.replace();
  },
  { deep: true }
);

onMounted(async () => {
  await fetchTickets({ archived: 1 });
  feather.replace();
});

const handleUnarchive = async (ticket) => {
  if (!ticket?.code) return;

  const result = await Swal.fire({
    icon: "question",
    title: "Kembalikan tiket?",
    text: `Tiket #${ticket.code} akan dikembalikan ke menu Ticket.`,
    showCancelButton: true,
    confirmButtonText: "Ya, kembalikan",
    cancelButtonText: "Batal",
    confirmButtonColor: "#2563eb",
  });

  if (!result.isConfirmed) return;

  await unarchiveTicket(ticket.code);
  await fetchTickets({ ...filters.value, archived: 1 });

  await Swal.fire({
    icon: "success",
    title: "Berhasil",
    text: "Tiket berhasil dikembalikan",
    timer: 1500,
    showConfirmButton: false,
  });
};
</script>

<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-lg font-semibold text-gray-900">Archive</h1>
        <p class="text-sm text-gray-500">Daftar tiket yang sudah di-archive.</p>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="relative">
            <input type="text" v-model="filters.search" placeholder="Cari tiket..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
            <i data-feather="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
          </div>

          <select v-model="filters.status" class="border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="open">Open</option>
            <option value="onprogress">On Progress</option>
            <option value="resolved">Resolved</option>
            <option value="rejected">Rejected</option>
          </select>

          <select v-model="filters.priority" class="border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            <option value="">Semua Prioritas</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
          </select>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Tiket</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelapor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioritas</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-for="ticket in tickets" :key="ticket.code" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">#{{ ticket.code }}</td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-800">{{ ticket.title }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <img :src="`https://ui-avatars.com/api/?name=${ticket.user.name}&background=0D8ABC&color=fff`" :alt="ticket.user.name" class="w-6 h-6 rounded-full" />
                  <span class="ml-2 text-sm text-gray-800">{{ ticket.user.name }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-3 py-1 text-xs font-medium rounded-full"
                  :class="{
                    'text-blue-700 bg-blue-100': ticket.status === 'open',
                    'text-yellow-700 bg-yellow-100': ticket.status === 'onprogress',
                    'text-green-700 bg-green-100': ticket.status === 'resolved',
                    'text-red-700 bg-red-100': ticket.status === 'rejected',
                  }"
                >
                  {{ capitalize(ticket.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-3 py-1 text-xs font-medium rounded-full"
                  :class="{
                    'text-red-700 bg-red-100': ticket.priority === 'high',
                    'text-yellow-700 bg-yellow-100': ticket.priority === 'medium',
                    'text-green-700 bg-green-100': ticket.priority === 'low',
                  }"
                >
                  {{ capitalize(ticket.priority) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                {{ DateTime.fromISO(ticket.created_at).toFormat("dd MMMM yyyy HH:mm") }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex items-center gap-2">
                  <RouterLink
                    :to="{ name: 'admin.ticket.detail', params: { code: ticket.code } }"
                    class="inline-flex items-center px-3 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
                  >
                    <i data-feather="eye" class="w-4 h-4 mr-2"></i>
                    Lihat
                  </RouterLink>

                  <button
                    type="button"
                    @click="handleUnarchive(ticket)"
                    class="inline-flex items-center px-3 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
                  >
                    <i data-feather="corner-up-left" class="w-4 h-4 mr-2"></i>
                    Unarchive
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="(tickets ?? []).length === 0">
              <td class="px-6 py-6 text-sm text-gray-500" colspan="7">Belum ada tiket yang di-archive.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
