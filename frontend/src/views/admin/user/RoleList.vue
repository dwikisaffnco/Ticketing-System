<script setup>
import { onMounted } from "vue";
import feather from "feather-icons";

onMounted(async () => {
  feather.replace();
});
</script>

<template>
  <div class="p-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div class="p-6 border-b border-gray-100">
        <h1 class="text-lg font-semibold text-gray-900">Role</h1>
        <p class="text-sm text-gray-500">Daftar role yang tersedia untuk user.</p>
      </div>

      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-900">Admin</div>
                <div class="text-sm text-gray-500">Akses penuh dashboard admin.</div>
              </div>
              <i data-feather="shield" class="w-5 h-5 text-blue-600"></i>
            </div>
          </div>

          <div class="rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-900">User</div>
                <div class="text-sm text-gray-500">Akses dashboard user untuk membuat tiket.</div>
              </div>
              <i data-feather="user" class="w-5 h-5 text-green-600"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
