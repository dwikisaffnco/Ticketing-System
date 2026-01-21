<script setup>
import { onMounted, watch, nextTick } from "vue";
import { Chart } from "chart.js/auto";
import { useDashboardStore } from "@/stores/dashboard";
import { useTicketStore } from "@/stores/ticket";
import { storeToRefs } from "pinia";
import feather from "feather-icons";
import { capitalize } from "lodash";
import { DateTime } from "luxon";

const dashboardStore = useDashboardStore();
const { statistic } = storeToRefs(dashboardStore);
const { fetchStatistics } = dashboardStore;

const ticketStore = useTicketStore();
const { tickets, pagination } = storeToRefs(ticketStore);
const { fetchTickets } = ticketStore;

const changePage = async (page) => {
  await fetchTickets({ page });
  nextTick(() => {
    feather.replace();
  });
};

const toggleTicketMenu = (ticket) => {
  ticket.showMenu = !ticket.showMenu;
};

let statusChart = null;
let priorityChart = null;
let trendChart = null;

watch(
  statistic,
  () => {
    if (statistic.value) {
      if (statusChart) {
        statusChart.data.datasets[0].data = [statistic.value.status_distribution?.open, statistic.value.status_distribution?.onprogress, statistic.value.status_distribution?.resolved, statistic.value.status_distribution?.rejected];
        statusChart.update();
      }

      if (priorityChart) {
        priorityChart.data.datasets[0].data = [statistic.value.priority_distribution?.low, statistic.value.priority_distribution?.medium, statistic.value.priority_distribution?.high];
        priorityChart.update();
      }

      if (trendChart) {
        trendChart.data.labels = statistic.value.ticket_trends?.map((t) => DateTime.fromISO(t.date).toLocaleString(DateTime.DATE_MED));
        trendChart.data.datasets[0].data = statistic.value.ticket_trends?.map((t) => t.count);
        trendChart.update();
      }
    }
  },
  { deep: true },
);

onMounted(async () => {
  await fetchTickets();
  await fetchStatistics();

  // Status Chart
  const statusCtx = document.getElementById("statusChart")?.getContext("2d");
  if (statusCtx && statistic.value) {
    statusChart = new Chart(statusCtx, {
      type: "doughnut",
      data: {
        labels: ["Open", "On Progress", "Resolved", "Rejected"],
        datasets: [
          {
            data: [statistic.value.status_distribution?.open, statistic.value.status_distribution?.onprogress, statistic.value.status_distribution?.resolved, statistic.value.status_distribution?.rejected],
            backgroundColor: ["#3B82F6", "#F59E0B", "#10B981", "#EF4444"],
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: "bottom" },
        },
        cutout: "70%",
      },
    });
  }

  // Priority Chart
  const priorityCtx = document.getElementById("priorityChart")?.getContext("2d");
  if (priorityCtx && statistic.value) {
    priorityChart = new Chart(priorityCtx, {
      type: "bar",
      data: {
        labels: ["Low", "Medium", "High"],
        datasets: [
          {
            label: "Tiket",
            data: [statistic.value.priority_distribution?.low, statistic.value.priority_distribution?.medium, statistic.value.priority_distribution?.high],
            backgroundColor: ["#94A3B8", "#F59E0B", "#EF4444"],
            borderRadius: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
        },
        scales: {
          y: { beginAtZero: true, grid: { display: false } },
          x: { grid: { display: false } },
        },
      },
    });
  }

  // Trend Chart
  const trendCtx = document.getElementById("trendChart")?.getContext("2d");
  if (trendCtx && statistic.value) {
    trendChart = new Chart(trendCtx, {
      type: "line",
      data: {
        labels: statistic.value.ticket_trends?.map((t) => DateTime.fromISO(t.date).toLocaleString(DateTime.DATE_MED)),
        datasets: [
          {
            label: "Tiket Baru",
            data: statistic.value.ticket_trends?.map((t) => t.count),
            borderColor: "#3B82F6",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: "#3B82F6",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
        },
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1 } },
          x: { grid: { display: false } },
        },
      },
    });
  }

  feather.replace();
});
</script>

<template>
  <!-- Statistics Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Total Tiket</p>
          <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ statistic?.total_tickets }}</h3>
        </div>
        <div class="p-3 bg-blue-50 rounded-lg">
          <i data-feather="tag" class="w-6 h-6 text-blue-600"></i>
        </div>
      </div>
      <div class="mt-4 flex items-center text-sm">
        <span class="text-green-500 flex items-center">
          <i data-feather="trending-up" class="w-4 h-4 mr-1"></i>
          12%
        </span>
        <span class="text-gray-500 ml-2">vs bulan lalu</span>
      </div>
    </div>

    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Tiket Aktif</p>
          <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ statistic?.active_tickets }}</h3>
        </div>
        <div class="p-3 bg-yellow-50 rounded-lg">
          <i data-feather="clock" class="w-6 h-6 text-yellow-600"></i>
        </div>
      </div>
      <div class="mt-4 flex items-center text-sm">
        <span class="text-red-500 flex items-center">
          <i data-feather="trending-down" class="w-4 h-4 mr-1"></i>
          3%
        </span>
        <span class="text-gray-500 ml-2">vs bulan lalu</span>
      </div>
    </div>

    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Selesai</p>
          <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ statistic?.resolved_tickets }}</h3>
        </div>
        <div class="p-3 bg-green-50 rounded-lg">
          <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
        </div>
      </div>
      <div class="mt-4 flex items-center text-sm">
        <span class="text-green-500 flex items-center">
          <i data-feather="trending-up" class="w-4 h-4 mr-1"></i>
          8%
        </span>
        <span class="text-gray-500 ml-2">vs bulan lalu</span>
      </div>
    </div>

    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Rata-rata Waktu</p>
          <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ statistic?.avg_resolution_time }} Jam</h3>
        </div>
        <div class="p-3 bg-purple-50 rounded-lg">
          <i data-feather="clock" class="w-6 h-6 text-purple-600"></i>
        </div>
      </div>
      <div class="mt-4 flex items-center text-sm">
        <span class="text-green-500 flex items-center">
          <i data-feather="trending-down" class="w-4 h-4 mr-1"></i>
          15%
        </span>
        <span class="text-gray-500 ml-2">vs bulan lalu</span>
      </div>
    </div>

    <div class="stat-card bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Archive</p>
          <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ statistic?.archived_tickets ?? 0 }}</h3>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg">
          <i data-feather="archive" class="w-6 h-6 text-gray-700"></i>
        </div>
      </div>
      <div class="mt-4 flex items-center text-sm">
        <span class="text-gray-500">vs bulan lalu</span>
      </div>
    </div>
  </div>

  <!-- Ticket Trend Chart -->
  <div class="grid grid-cols-12 gap-6 mt-6">
    <div class="col-span-12 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-800">Tren Tiket (7 Hari Terakhir)</h3>
        <div class="flex items-center space-x-2">
          <span class="flex items-center text-xs text-gray-500">
            <span class="w-3 h-3 bg-blue-500 rounded-full mr-1"></span>
            Tiket Baru
          </span>
        </div>
      </div>
      <div class="relative w-full h-72">
        <canvas id="trendChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Recent Tickets and Sidebar Charts -->
  <div class="grid grid-cols-12 gap-6 mt-6 items-start">
    <!-- Recent Tickets -->
    <div class="col-span-12 lg:col-span-8 bg-white rounded-xl shadow-sm border border-gray-100">
      <div class="p-6 border-b border-gray-100">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-800">Tiket Terbaru</h3>
          <RouterLink :to="{ name: 'admin.ticket' }" class="text-sm text-blue-600 hover:text-blue-800 font-medium" v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }">Lihat Semua</RouterLink>
        </div>
      </div>
      <div class="max-h-[500px] overflow-y-auto divide-y divide-gray-100 custom-scrollbar">
        <div v-for="ticket in tickets" :key="ticket.code" class="relative group">
          <RouterLink :to="{ name: 'admin.ticket.detail', params: { code: ticket.code } }" class="block p-4 hover:bg-gray-50 transition-colors">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-sm font-medium text-gray-800">{{ ticket.title }}</h4>
                <p class="text-xs text-gray-500 mt-1">#{{ ticket.code }}</p>
                <div class="flex items-center mt-2 space-x-2">
                  <span
                    :class="{
                      'text-blue-700 bg-blue-100': ticket.status === 'open',
                      'text-yellow-700 bg-yellow-100': ticket.status === 'onprogress',
                      'text-green-700 bg-green-100': ticket.status === 'resolved',
                      'text-red-700 bg-red-100': ticket.status === 'rejected',
                    }"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ capitalize(ticket.status) }}
                  </span>
                  <span class="text-xs text-gray-500">
                    {{ DateTime.fromISO(ticket.created_at).toRelative() }}
                  </span>
                </div>
              </div>
            </div>
          </RouterLink>

          <div class="absolute right-4 top-4">
            <div class="relative">
              <button
                @click.stop="toggleTicketMenu(ticket)"
                class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100 transition-colors"
                v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.1 }, tapped: { scale: 0.95 } }"
              >
                <i data-feather="more-vertical" class="w-5 h-5"></i>
              </button>
              <div v-show="ticket.showMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                <RouterLink
                  :to="{ name: 'admin.ticket.detail', params: { code: ticket.code } }"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                  v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
                >
                  <i data-feather="eye" class="w-4 h-4 inline-block mr-2"></i>
                  Lihat Detail
                </RouterLink>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div v-if="pagination.last_page > 1" class="p-4 border-t border-gray-100 bg-gray-50/50 rounded-b-xl flex items-center justify-between">
        <p class="text-xs text-gray-500">Menampilkan {{ tickets.length }} dari {{ pagination.total }} tiket</p>
        <div class="flex items-center space-x-2">
          <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="p-2 text-gray-500 hover:text-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            <i data-feather="chevron-left" class="w-4 h-4"></i>
          </button>
          <span class="text-xs font-medium text-gray-700"> Halaman {{ pagination.current_page }} dari {{ pagination.last_page }} </span>
          <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="p-2 text-gray-500 hover:text-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            <i data-feather="chevron-right" class="w-4 h-4"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Sidebar Charts -->
    <div class="col-span-12 lg:col-span-4 space-y-6">
      <!-- Status Distribution Chart -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Status</h3>
        <div class="relative w-full h-64">
          <canvas id="statusChart"></canvas>
        </div>
      </div>

      <!-- Priority Distribution Chart -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Prioritas</h3>
        <div class="relative w-full h-64">
          <canvas id="priorityChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
</style>
