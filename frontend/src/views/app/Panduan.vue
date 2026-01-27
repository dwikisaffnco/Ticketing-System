<script setup>
import { ref } from "vue";

const selectedCategory = ref(0);

const categories = [
  {
    id: 0,
    title: "Jaringan & Koneksi",
    icon: "🌐",
    guides: [
      {
        id: "wifi-disconnect",
        title: "WiFi Terputus",
        problem: "Perangkat tidak bisa terhubung ke WiFi atau sering terputus",
        solutions: [
          "Cek apakah WiFi router sudah menyala",
          "Restart WiFi router dengan mematikan selama 30 detik",
          "Lupa password WiFi? Cek stiker di belakang router",
          "Coba forget network dan koneksikan ulang",
          "Periksa apakah signal WiFi cukup kuat di lokasi Anda",
          "Jika masalah berlanjut, hubungi Team IT untuk bantuan",
        ],
      },
      {
        id: "internet-slow",
        title: "Internet Lambat",
        problem: "Kecepatan internet sangat lambat atau sering lag",
        solutions: [
          "Restart router dengan mematikan 30 detik kemudian nyalakan",
          "Jauhkan perangkat dari microwave atau perangkat elektronik lain",
          "Kurangi jumlah perangkat yang terhubung ke WiFi",
          "Cek apakah ada update browser yang tertunda",
          "Clear browser cache dan cookies",
          "Hubungi Team IT jika kecepatan tetap lambat",
        ],
      },
      {
        id: "vpn-issue",
        title: "VPN Tidak Bisa Terhubung",
        problem: "Tidak bisa login atau terhubung ke VPN perusahaan",
        solutions: [
          "Pastikan koneksi internet stabil terlebih dahulu",
          "Cek username dan password VPN yang benar",
          "Restart aplikasi VPN client",
          "Update VPN client ke versi terbaru",
          "Disable antivirus sementara untuk test koneksi",
          "Hubungi Team IT dengan detail error yang muncul",
        ],
      },
    ],
  },
  {
    id: 1,
    title: "Email & Komunikasi",
    icon: "📧",
    guides: [
      {
        id: "email-not-receive",
        title: "Email Tidak Terima",
        problem: "Email tidak diterima atau masuk ke folder lain",
        solutions: [
          "Refresh email client atau reload browser",
          "Cek folder Spam/Junk untuk email yang masuk salah",
          "Periksa apakah email sudah dikirim di bagian 'Sent'",
          "Pastikan mailbox tidak penuh (cek storage)",
          "Whitelist sender di pengaturan email untuk menghindari spam",
          "Hubungi Team IT jika masalah berlanjut",
        ],
      },
      {
        id: "email-cannot-send",
        title: "Email Tidak Bisa Dikirim",
        problem: "Email gagal dikirim atau error saat mengirim",
        solutions: [
          "Pastikan koneksi internet stabil",
          "Cek apakah penerima email benar",
          "Jangan gunakan karakter spesial dalam subject",
          "Kurangi ukuran attachment jika terlalu besar",
          "Clear email cache dan restart email client",
          "Hubungi Team IT untuk bantuan lebih lanjut",
        ],
      },
    ],
  },
  {
    id: 2,
    title: "Hardware & Perangkat",
    icon: "🖥️",
    guides: [
      {
        id: "printer-issue",
        title: "Printer Tidak Bisa Print",
        problem: "Dokumen tidak tercetak atau printer offline",
        solutions: [
          "Cek apakah printer sudah menyala",
          "Pastikan printer terhubung ke jaringan (WiFi/Kabel)",
          "Restart printer dengan matikan 10 detik",
          "Clear print queue di Settings > Devices > Printers",
          "Reinstall driver printer dari website resmi",
          "Hubungi Team IT jika masalah persisten",
        ],
      },
      {
        id: "keyboard-mouse",
        title: "Keyboard/Mouse Tidak Responsif",
        problem: "Keyboard atau mouse tidak berfungsi dengan baik",
        solutions: [
          "Periksa koneksi kabel atau baterai perangkat wireless",
          "Coba ke port USB yang berbeda",
          "Restart komputer",
          "Update driver input device",
          "Bersihkan keyboard/mouse dari debu atau noda",
          "Hubungi Team IT untuk penggantian perangkat",
        ],
      },
      {
        id: "monitor-issue",
        title: "Monitor Tidak Menampilkan Gambar",
        problem: "Layar monitor gelap, tidak ada sinyal, atau bergetar",
        solutions: [
          "Cek kabel monitor sudah terhubung dengan benar",
          "Pastikan monitor sudah dinyalakan",
          "Restart komputer",
          "Coba gunakan port video yang berbeda (HDMI/VGA/DP)",
          "Jika menggunakan docking station, restart docking",
          "Hubungi Team IT untuk bantuan hardware",
        ],
      },
    ],
  },
  {
    id: 3,
    title: "Software & Aplikasi",
    icon: "💻",
    guides: [
      {
        id: "app-crash",
        title: "Aplikasi Crash atau Hang",
        problem: "Aplikasi tidak responsif, crash, atau freeze",
        solutions: [
          "Tutup aplikasi dan buka kembali",
          "Restart komputer Anda",
          "Update aplikasi ke versi terbaru",
          "Uninstall dan install ulang aplikasi",
          "Cek storage disk apakah masih cukup",
          "Hubungi Team IT dengan nama aplikasi dan error message",
        ],
      },
      {
        id: "app-install",
        title: "Tidak Bisa Install Aplikasi",
        problem: "Instalasi aplikasi diblokir atau gagal",
        solutions: [
          "Pastikan Anda punya akses admin di komputer",
          "Hubungi Team IT untuk request install aplikasi",
          "Jangan uninstall antivirus saat install",
          "Cek apakah storage cukup untuk instalasi",
          "Download dari source resmi saja",
          "Hubungi Team IT jika tetap gagal",
        ],
      },
      {
        id: "browser-issue",
        title: "Browser Lambat atau Error",
        problem: "Browser sering crash, loading lambat, atau error",
        solutions: ["Clear browser cache dan cookies", "Disable extension yang tidak digunakan", "Update browser ke versi terbaru", "Restart browser", "Coba buka halaman di incognito/private mode", "Hubungi Team IT jika masalah berlanjut"],
      },
    ],
  },
  {
    id: 4,
    title: "Data & File",
    icon: "📁",
    guides: [
      {
        id: "file-lost",
        title: "File Hilang atau Terhapus",
        problem: "File atau folder tidak bisa ditemukan",
        solutions: [
          "Cek Recycle Bin untuk file yang terhapus",
          "Gunakan file search untuk mencari file",
          "Cek backup atau cloud storage (jika tersedia)",
          "Jangan install software baru dulu sebelum konsultasi IT",
          "Hubungi Team IT segera untuk data recovery",
          "Sediakan informasi file: nama, lokasi, kapan hilang",
        ],
      },
      {
        id: "storage-full",
        title: "Storage Penuh",
        problem: "Disk atau storage hampir penuh",
        solutions: [
          "Cek folder Downloads dan hapus file yang tidak perlu",
          "Clear Recycle Bin",
          "Hapus file temporary di C:\\Temp (Windows)",
          "Uninstall aplikasi yang sudah tidak digunakan",
          "Pindahkan file ke external drive atau cloud storage",
          "Hubungi Team IT untuk upgrade storage",
        ],
      },
      {
        id: "file-permission",
        title: "Tidak Bisa Akses File/Folder",
        problem: "Error permission denied saat membuka file",
        solutions: [
          "Pastikan Anda sudah login dengan akun yang benar",
          "Cek apakah Anda punya akses ke folder tersebut",
          "Hubungi pemilik file untuk share akses",
          "Jika folder jaringan, cek koneksi jaringan",
          "Restart komputer dan coba lagi",
          "Hubungi Team IT untuk masalah permission jaringan",
        ],
      },
    ],
  },
  {
    id: 5,
    title: "Keamanan & Password",
    icon: "🔐",
    guides: [
      {
        id: "password-reset",
        title: "Lupa atau Reset Password",
        problem: "Lupa password akun atau perlu reset",
        solutions: [
          "Klik link 'Lupa Password' di halaman login",
          "Gunakan email recovery untuk reset password",
          "Jika sudah lupa email, hubungi Team IT",
          "Password harus minimal 8 karakter",
          "Gunakan kombinasi huruf besar, kecil, angka, dan simbol",
          "Segera ganti password jika merasa akun terkompromi",
        ],
      },
      {
        id: "account-locked",
        title: "Akun Terkunci",
        problem: "Akun terkunci setelah gagal login berkali-kali",
        solutions: [
          "Tunggu 30 menit untuk unlock otomatis",
          "Jangan coba login lagi sampai waktu habis",
          "Jika terburu-buru, hubungi Team IT untuk unlock manual",
          "Pastikan Caps Lock tidak aktif saat login",
          "Cek password dengan cermat sebelum submit",
          "Hubungi Team IT jika terus terjadi",
        ],
      },
      {
        id: "2fa-issue",
        title: "2FA/OTP Tidak Bekerja",
        problem: "Tidak menerima kode OTP atau 2FA error",
        solutions: [
          "Periksa jam sistem apakah sudah benar",
          "Refresh aplikasi authenticator",
          "Minta backup code dari Team IT",
          "Jika tidak pernah setup 2FA, skip dan hubungi IT",
          "Periksa spam folder untuk email OTP",
          "Hubungi Team IT untuk setup ulang 2FA",
        ],
      },
    ],
  },
  {
    id: 6,
    title: "Printer & Scanner",
    icon: "🖨️",
    guides: [
      {
        id: "printer-not-print",
        title: "Printer Tidak Bisa Print",
        problem: "Dokumen tidak tercetak atau printer offline",
        solutions: [
          "Cek apakah printer sudah menyala dan siap",
          "Pastikan printer terhubung ke jaringan (WiFi/Kabel Ethernet)",
          "Restart printer dengan matikan 10-15 detik kemudian nyalakan",
          "Clear print queue di printer settings (Windows: Settings > Devices > Printers)",
          "Pastikan driver printer sudah terinstall dengan benar",
          "Coba print dari aplikasi berbeda untuk memastikan masalah",
          "Hubungi Team IT untuk bantuan lebih lanjut",
        ],
      },
      {
        id: "scanner-not-detect",
        title: "Scanner Tidak Terdeteksi",
        problem: "Komputer tidak mengenali atau menemukan scanner",
        solutions: [
          "Cek kabel USB scanner sudah terhubung dengan benar",
          "Coba port USB yang berbeda di komputer",
          "Pastikan scanner sudah menyala dan dalam mode siap",
          "Install atau update driver scanner dari website resmi",
          "Restart komputer setelah install driver",
          "Coba scan menggunakan aplikasi bawaan Windows Scanner",
          "Jika masih gagal, hubungi Team IT dengan model printer/scanner",
        ],
      },
      {
        id: "printer-quality-issue",
        title: "Hasil Cetak Buruk atau Tidak Jelas",
        problem: "Hasil print gelap, pucat, bergaris-garis, atau kusam",
        solutions: [
          "Periksa level tinta/toner, mungkin perlu penggantian",
          "Bersihkan head printer dengan automatic cleaning function",
          "Pastikan menggunakan jenis kertas yang tepat",
          "Cek dan sesuaikan pengaturan kualitas cetak di print settings",
          "Coba nozzle cleaning dari control panel printer",
          "Jika ada error code di printer, catat dan beri tahu Team IT",
          "Hubungi Team IT untuk service atau penggantian kartrid",
        ],
      },
      {
        id: "scanner-quality-issue",
        title: "Hasil Scan Buruk atau Tidak Jelas",
        problem: "Hasil scan gelap, pucat, miring, atau tidak terbaca",
        solutions: [
          "Bersihkan kaca scanner dengan kain lembut dan cairan pembersih khusus",
          "Pastikan dokumen diletakkan dengan benar di scanner",
          "Coba ubah setting DPI/resolution ke nilai lebih tinggi",
          "Gunakan auto-crop atau straighten untuk hasil lebih rapi",
          "Sesuaikan brightness dan contrast di aplikasi scanner",
          "Coba scan ulang dengan pengaturan berbeda",
          "Hubungi Team IT jika scanner masih bermasalah",
        ],
      },
      {
        id: "printer-paper-jam",
        title: "Paper Jam atau Error Kertas",
        problem: "Kertas macet atau error kertas di printer",
        solutions: [
          "Matikan printer terlebih dahulu untuk keamanan",
          "Buka semua panel akses sesuai panduan printer",
          "Tarik kertas macet dengan hati-hati, jangan rusak",
          "Bersihkan area paper feed dari sisa kertas dan debu",
          "Periksa dan sesuaikan paper tray dengan benar",
          "Isi tray dengan kertas standar (jangan terlalu penuh)",
          "Nyalakan kembali dan coba print",
          "Hubungi Team IT jika masalah berlanjut",
        ],
      },
    ],
  },
  {
    id: 7,
    title: "Mobile & Smartphone",
    icon: "📱",
    guides: [
      {
        id: "iphone-wifi-issue",
        title: "iPhone WiFi Tidak Konek",
        problem: "iPhone tidak bisa terhubung ke WiFi atau sering disconnect",
        solutions: [
          "Matikan WiFi dan hidupkan kembali di Settings > WiFi",
          "Forget network: tap (i) icon, pilih Forget This Network, dan reconnect",
          "Pastikan password WiFi sudah benar (perhatian CAPS)",
          "Restart iPhone dengan shut down dan power on kembali",
          "Restart WiFi router dengan unplug 30 detik",
          "Update iOS ke versi terbaru di Settings > General > Software Update",
          "Reset network settings: Settings > General > Reset > Reset Network Settings",
          "Hubungi Team IT jika masih bermasalah",
        ],
      },
      {
        id: "android-wifi-issue",
        title: "Android WiFi Tidak Konek",
        problem: "Android tidak bisa terhubung ke WiFi atau sering putus",
        solutions: [
          "Matikan WiFi dan hidupkan kembali di Settings > WiFi",
          "Forget network: tap and hold network, pilih Forget",
          "Cek apakah password WiFi sudah benar (perhatian CAPS)",
          "Restart Android dengan power off dan power on kembali",
          "Restart WiFi router (unplug 30 detik)",
          "Update Android ke versi terbaru di Settings > About > System Update",
          "Clear cache: Settings > Apps > WiFi > Storage > Clear Cache",
          "Hubungi Team IT jika terus bermasalah",
        ],
      },
      {
        id: "iphone-email-issue",
        title: "iPhone Email Tidak Sync",
        problem: "Email tidak masuk di iPhone atau tidak sync dengan laptop",
        solutions: [
          "Cek koneksi internet WiFi atau data seluler",
          "Buka Settings > Mail > Accounts dan pilih akun email",
          "Tap Account, kemudian pilih server yang sesuai",
          "Matikan dan hidupkan kembali email app",
          "Hapus akun dan tambah ulang: Settings > Mail > Accounts > Edit > Delete > Add Account",
          "Pastikan app Mail sudah up-to-date dari App Store",
          "Jika pakai corporate email, hubungi Team IT untuk setup",
        ],
      },
      {
        id: "android-email-issue",
        title: "Android Email Tidak Sync",
        problem: "Email tidak masuk di Android atau tidak sync dengan laptop",
        solutions: [
          "Cek koneksi internet WiFi atau data seluler stabil",
          "Buka Settings > Accounts & passwords > pilih akun email",
          "Tap Account, ubah sync frequency jika perlu",
          "Matikan dan hidupkan app email",
          "Hapus dan tambah ulang akun: Settings > Accounts > pilih email > Remove > Add account",
          "Clear cache app: Settings > Apps > email app > Storage > Clear Cache",
          "Update app email dari Play Store",
          "Hubungi Team IT untuk troubleshoot lebih lanjut",
        ],
      },
      {
        id: "mobile-storage-full",
        title: "Storage iPhone/Android Penuh",
        problem: "Memory penuh, app error, atau tidak bisa update",
        solutions: [
          "Buka Settings > Storage untuk lihat apa yang mengambil space banyak",
          "Hapus app yang sudah tidak digunakan",
          "Hapus foto/video lama, atau backup ke cloud (iCloud/Google Photos)",
          "Clear cache app di Settings > Apps atau iPhone Storage",
          "Kosongkan folder Download atau recent files",
          "Pindahkan media ke cloud storage (Google Drive, OneDrive, dsb)",
          "Jika masih penuh, hubungi Team IT untuk konsultasi",
        ],
      },
      {
        id: "mobile-app-crash",
        title: "App Sering Crash atau Error",
        problem: "Aplikasi crash, hang, atau force close",
        solutions: [
          "Force close app dan buka kembali",
          "Restart phone dengan restart/power off dan power on",
          "Update app ke versi terbaru dari App Store/Play Store",
          "Uninstall dan install ulang app",
          "Bersihkan cache app di Settings",
          "Pastikan storage phone masih cukup",
          "Pastikan OS phone sudah update ke versi terbaru",
          "Hubungi Team IT jika app masih crash",
        ],
      },
      {
        id: "mobile-slow-performance",
        title: "Phone Lambat atau Hang",
        problem: "iPhone atau Android lambat, lag, atau tidak responsif",
        solutions: [
          "Restart phone dengan power off dan power on",
          "Tutup app-app yang tidak perlu atau berjalan di background",
          "Hapus app yang berat atau tidak digunakan",
          "Bersihkan cache dan file temporary",
          "Kurangi brightness screen jika terlalu tinggi",
          "Matikan fitur yang menggunakan banyak resource (Bluetooth, location, dsb)",
          "Update OS ke versi terbaru",
          "Hubungi Team IT jika performance tetap buruk",
        ],
      },
    ],
  },
];
const currentGuide = ref(null);

const selectGuide = (guide) => {
  currentGuide.value = guide;
};

const selectCategory = (category) => {
  selectedCategory.value = category.id;
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

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Categories -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sticky top-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Kategori</h2>
            <div class="space-y-2">
              <button
                v-for="category in categories"
                :key="category.id"
                @click="selectCategory(category)"
                :class="['w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-colors', selectedCategory === category.id ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-700 hover:bg-gray-50']"
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
                {{ categories[selectedCategory].title }}
              </h2>
              <p class="text-gray-600 mb-6">Pilih salah satu masalah di bawah untuk melihat solusinya</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <button
                v-for="guide in categories[selectedCategory].guides"
                :key="guide.id"
                @click="selectGuide(guide)"
                class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 hover:shadow-md hover:border-blue-200 transition-all text-left group"
              >
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
