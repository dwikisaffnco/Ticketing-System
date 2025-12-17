<script setup>
import { onMounted, ref, watch } from "vue";
import { useTicketStore } from "@/stores/ticket";
import { storeToRefs } from "pinia";
import { debounce, capitalize } from "lodash";
import feather from "feather-icons";
import { DateTime } from "luxon";

const ticketStore = useTicketStore();
const { tickets, loading } = storeToRefs(ticketStore);
const { fetchTickets } = ticketStore;

const filters = ref({
  search: "",
  status: "",
  priority: "",
  archived: 0,
});

watch(
  filters,
  debounce(async () => {
    await fetchTickets(filters.value);
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
  await fetchTickets(filters.value);
  feather.replace();
});

const escapeCsv = (value) => {
  const s = value == null ? "" : String(value);
  const escaped = s.replace(/"/g, '""');
  return `"${escaped}"`;
};

const exportCsv = () => {
  const rows = (tickets.value ?? []).map((t) => {
    return [t.code, t.title, t.user?.name, t.status, t.priority, t.created_at, t.updated_at];
  });

  const header = ["code", "title", "user", "status", "priority", "created_at", "updated_at"];
  const csv = [header.map(escapeCsv).join(","), ...rows.map((r) => r.map(escapeCsv).join(","))].join("\n");

  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);

  const a = document.createElement("a");
  a.href = url;
  a.download = `report_tickets_${DateTime.now().toFormat("yyyyLLdd_HHmm")}.csv`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);

  URL.revokeObjectURL(url);
};

const exportPdf = () => {
  const data = tickets.value ?? [];
  const now = DateTime.now().toFormat("dd LLLL yyyy HH:mm");

  const rowsHtml = data
    .map((t) => {
      const created = t.created_at ? DateTime.fromISO(t.created_at).toFormat("dd LLL yyyy HH:mm") : "";
      return `
        <tr>
          <td>${t.code ?? ""}</td>
          <td>${(t.title ?? "").replace(/</g, "&lt;").replace(/>/g, "&gt;")}</td>
          <td>${(t.user?.name ?? "").replace(/</g, "&lt;").replace(/>/g, "&gt;")}</td>
          <td>${t.status ?? ""}</td>
          <td>${t.priority ?? ""}</td>
          <td>${created}</td>
        </tr>
      `;
    })
    .join("");

  const html = `
    <html>
      <head>
        <title>Report Tickets</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 24px; }
          h1 { margin: 0 0 6px 0; font-size: 18px; }
          .meta { color: #555; font-size: 12px; margin-bottom: 16px; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid #ddd; padding: 8px; font-size: 12px; vertical-align: top; }
          th { background: #f5f5f5; text-align: left; }
        </style>
      </head>
      <body>
        <h1>Report Tickets</h1>
        <div class="meta">Generated: ${now}</div>
        <table>
          <thead>
            <tr>
              <th>Code</th>
              <th>Title</th>
              <th>User</th>
              <th>Status</th>
              <th>Priority</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
            ${rowsHtml || `<tr><td colspan="6">No data</td></tr>`}
          </tbody>
        </table>
      </body>
    </html>
  `;

  const w = window.open("", "_blank");
  if (!w) return;
  w.document.open();
  w.document.write(html);
  w.document.close();
  w.focus();
  w.print();
};
</script>

<template>
  <div class="p-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
      <div class="p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-lg font-semibold text-gray-900">Report</h1>
            <p class="text-sm text-gray-500">Export report tiket ke CSV atau PDF.</p>
          </div>

          <div class="flex items-center gap-2">
            <button type="button" @click="exportCsv" class="inline-flex items-center px-4 py-2 border border-gray-200 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
              <i data-feather="download" class="w-4 h-4 mr-2"></i>
              Export CSV
            </button>
            <button type="button" @click="exportPdf" class="inline-flex items-center px-4 py-2 bg-blue-600 text-sm font-medium rounded-lg text-white hover:bg-blue-700">
              <i data-feather="file-text" class="w-4 h-4 mr-2"></i>
              Export PDF
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
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

          <select v-model="filters.archived" class="border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            <option :value="0">Aktif</option>
            <option :value="1">Archive</option>
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
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-for="t in tickets" :key="t.code" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">#{{ t.code }}</td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-800">{{ t.title }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-800">{{ t.user?.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-3 py-1 text-xs font-medium rounded-full"
                  :class="{
                    'text-blue-700 bg-blue-100': t.status === 'open',
                    'text-yellow-700 bg-yellow-100': t.status === 'onprogress',
                    'text-green-700 bg-green-100': t.status === 'resolved',
                    'text-red-700 bg-red-100': t.status === 'rejected',
                  }"
                >
                  {{ capitalize(t.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-3 py-1 text-xs font-medium rounded-full"
                  :class="{
                    'text-red-700 bg-red-100': t.priority === 'high',
                    'text-yellow-700 bg-yellow-100': t.priority === 'medium',
                    'text-green-700 bg-green-100': t.priority === 'low',
                  }"
                >
                  {{ capitalize(t.priority) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                {{ DateTime.fromISO(t.created_at).toFormat("dd MMMM yyyy HH:mm") }}
              </td>
            </tr>

            <tr v-if="(tickets ?? []).length === 0 && !loading">
              <td class="px-6 py-6 text-sm text-gray-500" colspan="6">Tidak ada data.</td>
            </tr>
            <tr v-if="loading">
              <td class="px-6 py-6 text-sm text-gray-500" colspan="6">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
