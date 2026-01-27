<?php

namespace Database\Seeders;

use App\Models\Guide;
use App\Models\GuideCategory;
use Illuminate\Database\Seeder;

class GuideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories
        $categories = [
            [
                'title' => 'Jaringan & Koneksi',
                'icon' => '🌐',
                'order' => 0,
            ],
            [
                'title' => 'Email & Komunikasi',
                'icon' => '📧',
                'order' => 1,
            ],
            [
                'title' => 'Hardware & Perangkat',
                'icon' => '🖥️',
                'order' => 2,
            ],
            [
                'title' => 'Software & Aplikasi',
                'icon' => '💻',
                'order' => 3,
            ],
            [
                'title' => 'Data & File',
                'icon' => '📁',
                'order' => 4,
            ],
            [
                'title' => 'Keamanan & Password',
                'icon' => '🔐',
                'order' => 5,
            ],
            [
                'title' => 'Printer & Scanner',
                'icon' => '🖨️',
                'order' => 6,
            ],
            [
                'title' => 'Mobile & Smartphone',
                'icon' => '📱',
                'order' => 7,
            ],
        ];

        $categoryMap = [];
        foreach ($categories as $cat) {
            $category = GuideCategory::create($cat);
            $categoryMap[$cat['title']] = $category->id;
        }

        // Create guides
        $guides = [
            // Jaringan & Koneksi
            [
                'category_id' => $categoryMap['Jaringan & Koneksi'],
                'title' => 'WiFi Tidak Bisa Terhubung',
                'problem' => 'Saya tidak bisa terhubung ke WiFi, apa yang harus saya lakukan?',
                'solutions' => [
                    'Pastikan WiFi Anda sudah diaktifkan di device',
                    'Coba restart router dengan menunggu 30 detik sebelum hidupkan kembali',
                    'Coba lupa network kemudian reconnect kembali dengan password yang benar',
                    'Jika masih tidak bisa, coba restart device Anda',
                ],
                'is_active' => true,
                'order' => 0,
            ],
            [
                'category_id' => $categoryMap['Jaringan & Koneksi'],
                'title' => 'Internet Lambat',
                'problem' => 'Koneksi internet saya sangat lambat, bagaimana cara mempercepat?',
                'solutions' => [
                    'Tutup aplikasi yang menggunakan bandwidth besar seperti video streaming atau download',
                    'Cek berapa banyak device yang terhubung ke WiFi, disconnect device yang tidak perlu',
                    'Restart router dengan mematikan selama 30 detik',
                    'Coba ubah channel WiFi di settings router ke channel yang lebih sepi',
                    'Hubungi IT Support jika masalah tetap berlanjut',
                ],
                'is_active' => true,
                'order' => 1,
            ],
            [
                'category_id' => $categoryMap['Jaringan & Koneksi'],
                'title' => 'VPN Connection Error',
                'problem' => 'Saya mendapat error saat mencoba connect ke VPN perusahaan',
                'solutions' => [
                    'Pastikan koneksi internet Anda stabil terlebih dahulu',
                    'Coba logout dari VPN kemudian login kembali',
                    'Restart aplikasi VPN Anda',
                    'Coba gunakan port berbeda jika tersedia di settings VPN',
                    'Jika terus error, hubungi IT Support untuk bantuan',
                ],
                'is_active' => true,
                'order' => 2,
            ],

            // Email & Komunikasi
            [
                'category_id' => $categoryMap['Email & Komunikasi'],
                'title' => 'Email Tidak Terima Pesan',
                'problem' => 'Saya tidak bisa menerima email, folder inbox saya kosong atau tidak menerima pesan baru',
                'solutions' => [
                    'Cek folder spam/junk untuk email yang mungkin terfilter',
                    'Refresh email client atau reload halaman webmail',
                    'Coba logout dan login kembali ke email Anda',
                    'Pastikan koneksi internet Anda stabil',
                    'Jika folder inbox benar-benar kosong, hubungi IT Support untuk cek server',
                ],
                'is_active' => true,
                'order' => 0,
            ],
            [
                'category_id' => $categoryMap['Email & Komunikasi'],
                'title' => 'Email Tidak Bisa Dikirim',
                'problem' => 'Saya mendapat error saat mencoba mengirim email, pesan gagal dikirim',
                'solutions' => [
                    'Pastikan Anda telah terkoneksi ke internet dengan stabil',
                    'Cek ukuran attachment, jika terlalu besar coba kurangi atau kompres file',
                    'Coba refresh email client dan kirim ulang',
                    'Cek recipient email address apakah sudah benar dan valid',
                    'Jika terus gagal, hubungi IT Support untuk bantuan teknis',
                ],
                'is_active' => true,
                'order' => 1,
            ],

            // Hardware & Perangkat
            [
                'category_id' => $categoryMap['Hardware & Perangkat'],
                'title' => 'Printer Tidak Bisa Cetak',
                'problem' => 'Saya tidak bisa mencetak dokumen ke printer, apa yang harus dilakukan?',
                'solutions' => [
                    'Pastikan printer sudah menyala dan terhubung dengan jaringan',
                    'Cek apakah ada pesan error di printer display',
                    'Coba reset print queue di komputer Anda',
                    'Restart printer dan komputer',
                    'Jika masih tidak bisa, hubungi IT Support',
                ],
                'is_active' => true,
                'order' => 0,
            ],
            [
                'category_id' => $categoryMap['Hardware & Perangkat'],
                'title' => 'Keyboard/Mouse Tidak Berfungsi',
                'problem' => 'Keyboard atau mouse saya tidak responsif atau tidak berfungsi',
                'solutions' => [
                    'Untuk wireless device, cek battery apakah masih penuh',
                    'Coba reconnect device dengan receiver/bluetooth',
                    'Coba gunakan keyboard/mouse yang berbeda untuk test',
                    'Restart komputer',
                    'Jika tetap tidak berfungsi, bawa ke IT Support untuk penggantian',
                ],
                'is_active' => true,
                'order' => 1,
            ],
            [
                'category_id' => $categoryMap['Hardware & Perangkat'],
                'title' => 'Monitor Tidak Ada Signal',
                'problem' => 'Monitor saya tidak menampilkan gambar atau berkata "No Signal"',
                'solutions' => [
                    'Cek kabel VGA/HDMI apakah sudah terhubung dengan benar ke komputer dan monitor',
                    'Coba tancapkan ulang kabel di kedua ujung',
                    'Jika menggunakan laptop, coba tekan kombinasi tombol untuk trigger external display (biasanya Windows+P)',
                    'Coba gunakan monitor yang berbeda',
                    'Hubungi IT Support jika masalah tetap terjadi',
                ],
                'is_active' => true,
                'order' => 2,
            ],

            // Software & Aplikasi
            [
                'category_id' => $categoryMap['Software & Aplikasi'],
                'title' => 'Aplikasi Crash/Freeze',
                'problem' => 'Aplikasi saya sering crash atau freeze, bagaimana cara mengatasinya?',
                'solutions' => [
                    'Coba close aplikasi dan buka kembali',
                    'Pastikan Anda menggunakan versi aplikasi terbaru, update jika ada update tersedia',
                    'Coba restart komputer Anda',
                    'Jika aplikasi crash saat buka file tertentu, coba buka file yang berbeda',
                    'Jika crash terus berlanjut, hubungi IT Support atau developer aplikasi',
                ],
                'is_active' => true,
                'order' => 0,
            ],
            [
                'category_id' => $categoryMap['Software & Aplikasi'],
                'title' => 'Error Saat Install Aplikasi',
                'problem' => 'Saya mendapat error saat mencoba install aplikasi baru',
                'solutions' => [
                    'Cek apakah Anda memiliki permission/admin access untuk install',
                    'Coba close semua aplikasi sebelum melakukan install',
                    'Disable antivirus sementara saat install (jika diperlukan)',
                    'Coba download installer kembali, file mungkin corrupt',
                    'Hubungi IT Support jika terus mengalami error',
                ],
                'is_active' => true,
                'order' => 1,
            ],
            [
                'category_id' => $categoryMap['Software & Aplikasi'],
                'title' => 'Browser Lemot/Hang',
                'problem' => 'Browser saya sangat lambat atau sering hang saat browsing',
                'solutions' => [
                    'Cek berapa banyak tab yang terbuka, close tab yang tidak perlu',
                    'Clear cache dan cookies browser Anda',
                    'Disable extension browser yang tidak perlu digunakan',
                    'Restart browser',
                    'Jika masalah tetap ada, coba browser yang berbeda',
                ],
                'is_active' => true,
                'order' => 2,
            ],

            // Data & File
            [
                'category_id' => $categoryMap['Data & File'],
                'title' => 'File Hilang',
                'problem' => 'File saya hilang, tidak bisa ditemukan di komputer atau folder yang biasa',
                'solutions' => [
                    'Cek Recycle Bin apakah file masih ada di sana',
                    'Gunakan fitur search (Windows Search atau Spotlight) untuk mencari file',
                    'Cek apakah file mungkin tersimpan di folder lain atau desktop',
                    'Jika menggunakan cloud storage, cek folder cloud Anda',
                    'Hubungi IT Support jika file penting masih tidak ditemukan',
                ],
                'is_active' => true,
                'order' => 0,
            ],
            [
                'category_id' => $categoryMap['Data & File'],
                'title' => 'Storage Penuh',
                'problem' => 'Storage saya penuh, saya tidak bisa menyimpan file baru',
                'solutions' => [
                    'Delete file yang sudah tidak diperlukan lagi',
                    'Kosongkan Recycle Bin/Trash untuk free up space',
                    'Pindahkan file besar ke external storage atau cloud storage',
                    'Clear browser cache untuk free up space',
                    'Hubungi IT Support untuk opsi storage tambahan',
                ],
                'is_active' => true,
                'order' => 1,
            ],
            [
                'category_id' => $categoryMap['Data & File'],
                'title' => 'Permission/Access Denied',
                'problem' => 'Saya tidak bisa buka file atau akses folder, muncul pesan "Access Denied"',
                'solutions' => [
                    'Pastikan Anda adalah owner dari file tersebut',
                    'Cek apakah Anda memiliki permission yang tepat untuk akses file',
                    'Coba right-click file dan pilih "Properties" lalu atur permission',
                    'Jika file di shared folder, hubungi folder owner untuk minta akses',
                    'Jika terus tidak bisa, hubungi IT Support',
                ],
                'is_active' => true,
                'order' => 2,
            ],

            // Keamanan & Password
            [
                'category_id' => $categoryMap['Keamanan & Password'],
                'title' => 'Lupa Password',
                'problem' => 'Saya lupa password akun saya, bagaimana cara reset?',
                'solutions' => [
                    'Buka halaman login dan pilih "Forgot Password"',
                    'Ikuti instruksi yang dikirim ke email Anda',
                    'Jika tidak menerima email, cek folder spam',
                    'Jika tetap tidak bisa reset, hubungi IT Support dengan verifikasi identitas',
                ],
                'is_active' => true,
                'order' => 0,
            ],
            [
                'category_id' => $categoryMap['Keamanan & Password'],
                'title' => 'Akun Terkunci',
                'problem' => 'Akun saya terkunci setelah gagal login berkali-kali',
                'solutions' => [
                    'Tunggu 15-30 menit dan coba login kembali',
                    'Pastikan Caps Lock tidak aktif saat mengetik password',
                    'Jika masih terkunci, hubungi IT Support untuk unlock',
                    'Minta IT Support untuk reset password jika Anda lupa',
                ],
                'is_active' => true,
                'order' => 1,
            ],
            [
                'category_id' => $categoryMap['Keamanan & Password'],
                'title' => '2FA/Two-Factor Authentication',
                'problem' => 'Saya tidak bisa login karena 2FA code error atau tidak menerima code',
                'solutions' => [
                    'Pastikan waktu di device Anda sudah correct',
                    'Cek email atau SMS untuk 2FA code terbaru',
                    'Coba gunakan backup code jika tersedia',
                    'Hubungi IT Support jika app 2FA tidak berfungsi dengan baik',
                ],
                'is_active' => true,
                'order' => 2,
            ],

            // Printer & Scanner
            [
                'category_id' => $categoryMap['Printer & Scanner'],
                'title' => 'Printer Tidak Cetak Sama Sekali',
                'problem' => 'Printer sama sekali tidak merespon atau tidak cetak, padahal sudah power on',
                'solutions' => [
                    'Cek koneksi power dan pastikan printer sudah menyala (lihat indikator LED)',
                    'Cek koneksi kabel USB atau network cable ke komputer/router',
                    'Coba reset printer dengan power cycle (matikan 30 detik kemudian nyalakan)',
                    'Reinstall driver printer di komputer',
                    'Hubungi IT Support jika printer tidak juga merespon',
                ],
                'is_active' => true,
                'order' => 0,
            ],
            [
                'category_id' => $categoryMap['Printer & Scanner'],
                'title' => 'Scanner Tidak Terdeteksi',
                'problem' => 'Scanner tidak terdeteksi oleh komputer atau software scanning',
                'solutions' => [
                    'Pastikan scanner sudah connect ke komputer dengan USB cable',
                    'Reinstall driver scanner terbaru dari website manufacturer',
                    'Coba test dengan USB port yang berbeda',
                    'Restart komputer setelah install driver',
                    'Jika masih tidak terdeteksi, hubungi IT Support',
                ],
                'is_active' => true,
                'order' => 1,
            ],
            [
                'category_id' => $categoryMap['Printer & Scanner'],
                'title' => 'Kualitas Cetak Jelek',
                'problem' => 'Hasil cetak tidak bagus, ada garis-garis atau warna tidak sempurna',
                'solutions' => [
                    'Cek apakah tinta atau toner sudah hampir habis',
                    'Lakukan cleaning pada print head di menu maintenance printer',
                    'Pastikan Anda menggunakan quality setting yang tepat di print dialog',
                    'Coba dengan kertas yang berbeda untuk test',
                    'Jika masih tidak baik, bawa printer ke technician',
                ],
                'is_active' => true,
                'order' => 2,
            ],
            [
                'category_id' => $categoryMap['Printer & Scanner'],
                'title' => 'Kualitas Scan Buruk',
                'problem' => 'Hasil scan tidak bagus, ada noise atau blur',
                'solutions' => [
                    'Bersihkan glass scanner dengan kain lembut',
                    'Letakkan dokumen dengan benar di tengah glass scanner',
                    'Coba ubah resolution scan ke nilai yang lebih tinggi',
                    'Cek brightness/contrast setting di software scanner',
                    'Coba scan dokumen lain untuk confirm apakah masalah pada scanner atau dokumen',
                ],
                'is_active' => true,
                'order' => 3,
            ],
            [
                'category_id' => $categoryMap['Printer & Scanner'],
                'title' => 'Paper Jam di Printer',
                'problem' => 'Kertas macet di dalam printer atau muncul pesan "Paper Jam"',
                'solutions' => [
                    'Matikan printer terlebih dahulu',
                    'Buka semua panel akses di printer dengan hati-hati',
                    'Cari dan keluarkan kertas yang macet dengan perlahan',
                    'Periksa apakah ada sisa kertas atau debris yang tertinggal',
                    'Tutup semua panel dan hidupkan printer kembali',
                    'Jika pesan error masih muncul, hubungi IT Support',
                ],
                'is_active' => true,
                'order' => 4,
            ],

            // Mobile & Smartphone
            [
                'category_id' => $categoryMap['Mobile & Smartphone'],
                'title' => 'iPhone WiFi Tidak Bisa Connect',
                'problem' => 'iPhone saya tidak bisa connect ke WiFi network',
                'solutions' => [
                    'Pastikan WiFi network terlihat di daftar networks yang tersedia',
                    'Coba lupa network di Settings > WiFi, kemudian connect ulang',
                    'Restart iPhone dengan force restart (tekan volume up, volume down, kemudian tahan power)',
                    'Reset network settings di Settings > General > Reset > Reset Network Settings',
                    'Jika masih tidak bisa, update iOS ke versi terbaru',
                ],
                'is_active' => true,
                'order' => 0,
            ],
            [
                'category_id' => $categoryMap['Mobile & Smartphone'],
                'title' => 'Android WiFi Tidak Bisa Connect',
                'problem' => 'Android phone saya tidak bisa connect ke WiFi',
                'solutions' => [
                    'Pastikan WiFi sudah diaktifkan di Settings > WiFi',
                    'Coba lupa network di Advanced settings, kemudian reconnect',
                    'Restart phone Anda',
                    'Clear WiFi network cache di Settings > Apps > WiFi > Storage > Clear Cache',
                    'Update Android ke versi terbaru jika tersedia',
                ],
                'is_active' => true,
                'order' => 1,
            ],
            [
                'category_id' => $categoryMap['Mobile & Smartphone'],
                'title' => 'iPhone Email Configuration',
                'problem' => 'Saya tidak bisa setup email di iPhone, atau email tidak bisa send/receive',
                'solutions' => [
                    'Pergi ke Settings > Mail > Accounts dan pilih Add Account',
                    'Masukkan email address dan password Anda',
                    'Jika manual setup diperlukan, gunakan server settings yang benar (IMAP/POP3, SMTP)',
                    'Pastikan Anda allowed access ke account dari third-party apps',
                    'Hubungi IT Support jika tetap tidak bisa setup',
                ],
                'is_active' => true,
                'order' => 2,
            ],
            [
                'category_id' => $categoryMap['Mobile & Smartphone'],
                'title' => 'Android Email Configuration',
                'problem' => 'Saya tidak bisa setup email di Android atau email tidak sync',
                'solutions' => [
                    'Buka Gmail app atau email app yang Anda gunakan',
                    'Tap Menu > Settings > Add account',
                    'Masukkan email dan password Anda',
                    'Pilih account type (IMAP/POP3) sesuai instruksi dari IT',
                    'Pastikan sync diaktifkan di account settings',
                ],
                'is_active' => true,
                'order' => 3,
            ],
            [
                'category_id' => $categoryMap['Mobile & Smartphone'],
                'title' => 'Storage Penuh di Phone',
                'problem' => 'Storage di iPhone/Android saya penuh, tidak bisa install app atau ambil foto',
                'solutions' => [
                    'Delete app yang tidak sering digunakan',
                    'Delete photos/videos lama, atau backup ke cloud storage terlebih dahulu',
                    'Clear cache aplikasi di Settings > Apps > Storage > Clear Cache',
                    'Enable automatic cloud backup untuk photos (Google Photos atau iCloud)',
                    'Transfer file besar ke cloud atau computer',
                ],
                'is_active' => true,
                'order' => 4,
            ],
            [
                'category_id' => $categoryMap['Mobile & Smartphone'],
                'title' => 'App Crash atau Lag',
                'problem' => 'Aplikasi di phone saya sering crash atau very slow',
                'solutions' => [
                    'Force close app dan buka kembali',
                    'Cek apakah ada app update tersedia di app store',
                    'Clear app cache di Settings > Apps > [app name] > Storage > Clear Cache',
                    'Restart phone Anda',
                    'Uninstall dan reinstall app jika masalah tetap terjadi',
                ],
                'is_active' => true,
                'order' => 5,
            ],
            [
                'category_id' => $categoryMap['Mobile & Smartphone'],
                'title' => 'Phone Performance Lemot',
                'problem' => 'Phone saya sangat lambat, lag saat buka app atau menggunakan',
                'solutions' => [
                    'Restart phone Anda untuk clear memory',
                    'Delete app yang tidak perlu dan free up storage space',
                    'Close app yang berjalan di background di task manager',
                    'Disable automatic sync untuk app yang tidak penting',
                    'Update OS ke versi terbaru untuk optimasi performa',
                ],
                'is_active' => true,
                'order' => 6,
            ],
        ];

        foreach ($guides as $guide) {
            Guide::create($guide);
        }
    }
}
