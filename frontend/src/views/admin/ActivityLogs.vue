<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { axiosInstance } from "@/plugins/axios";
import { handleError } from "@/helpers/errorHelper";
import feather from "feather-icons";
import { DateTime } from "luxon";
import { debounce } from "lodash";

const loading = ref(false);
const error = ref(null);

const search = ref("");
const page = ref(1);
const perPage = ref(20);

const rows = ref([]);
const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
});

const fromMeta = computed(() => {
  const p = meta.value?.current_page ?? 1;
  const pp = meta.value?.per_page ?? 20;
  const total = meta.value?.total ?? 0;
  if (!total) return 0;
  return (p - 1) * pp + 1;
});

const toMeta = computed(() => {
  const p = meta.value?.current_page ?? 1;
  const pp = meta.value?.per_page ?? 20;
  const total = meta.value?.total ?? 0;
  return Math.min(p * pp, total);
});

const fetchLogs = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await axiosInstance.get("/admin/activity-logs", {
      params: {
        search: search.value || undefined,
        page: page.value,
        per_page: perPage.value,
      },
    });

    rows.value = response?.data?.data ?? [];

    const m = response?.data?.meta ?? null;
    if (m) {
      meta.value = {
        current_page: m.current_page,
        last_page: m.last_page,
        per_page: m.per_page,
        total: m.total,
      };
    }

    await Promise.resolve();
    feather.replace();
  } catch (e) {
    error.value = handleError(e);
  } finally {
    loading.value = false;
  }
};

watch(
  search,
  debounce(async () => {
    page.value = 1;
    await fetchLogs();
  }, 300)
);

watch(perPage, async () => {
  page.value = 1;
  await fetchLogs();
});

const prevPage = async () => {
  if ((meta.value?.current_page ?? 1) <= 1) return;
  page.value = (meta.value?.current_page ?? 1) - 1;
  await fetchLogs();
};

const nextPage = async () => {
  if ((meta.value?.current_page ?? 1) >= (meta.value?.last_page ?? 1)) return;
  page.value = (meta.value?.current_page ?? 1) + 1;
  await fetchLogs();
};

onMounted(async () => {
  await fetchLogs();
});
</script>

<template>
  <div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-lg font-semibold text-gray-900">Activity Logs</h1>
        <p class="text-sm text-gray-500">Semua aktivitas user/admin yang tercatat.</p>
      </div>
    </div>

    <div v-if="error && typeof error === 'string'" class="text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg px-4 py-2">
      {{ error }}
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm font-semibold text-gray-900">Logs</div>
        <div class="flex items-center gap-2">
          <div class="relative">
            <input
              v-model="search"
              type="text"
              placeholder="Cari action/description..."
              class="w-64 max-w-[70vw] pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            />
            <i data-feather="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
          </div>

          <select v-model.number="perPage" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>

          <button type="button" class="text-sm text-gray-600 hover:text-gray-800" @click="fetchLogs" :disabled="loading" v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }">Refresh</button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-50">
            <tr v-if="loading">
              <td class="px-4 py-4 text-sm text-gray-500" colspan="5">Memuat data...</td>
            </tr>
            <tr v-else-if="rows.length === 0">
              <td class="px-4 py-4 text-sm text-gray-500" colspan="5">Belum ada log.</td>
            </tr>
            <tr v-else v-for="r in rows" :key="r.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                {{ r?.created_at ? DateTime.fromISO(r.created_at).toFormat("dd MMM yyyy HH:mm") : "-" }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">
                <div class="text-gray-900">{{ r?.user?.name ?? "-" }}</div>
                <div class="text-xs text-gray-500">{{ r?.user?.email ?? "" }}</div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ r?.action ?? "-" }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ r?.description ?? "-" }}</td>
              <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ r?.ip_address ?? "-" }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
        <div class="text-sm text-gray-600">Menampilkan {{ fromMeta }}-{{ toMeta }} dari {{ meta?.total ?? 0 }}</div>
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="prevPage"
            :disabled="loading || (meta?.current_page ?? 1) <= 1"
            class="px-3 py-2 rounded-lg border border-gray-200 text-sm text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
          >
            Prev
          </button>
          <div class="text-sm text-gray-600">{{ meta?.current_page ?? 1 }} / {{ meta?.last_page ?? 1 }}</div>
          <button
            type="button"
            @click="nextPage"
            :disabled="loading || (meta?.current_page ?? 1) >= (meta?.last_page ?? 1)"
            class="px-3 py-2 rounded-lg border border-gray-200 text-sm text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            v-motion="{ initial: { scale: 1 }, hovered: { scale: 1.05 }, tapped: { scale: 0.95 } }"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
