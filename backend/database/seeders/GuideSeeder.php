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
            [
                'title' => 'Important',
                'icon' => '⭐',
                'description' => '⚠️ MUST READ - Panduan penting yang wajib dibaca oleh semua pengguna',
                'order' => 8,
            ],
            [
                'title' => 'Policy & Regulations',
                'icon' => '📋',
                'description' => '⚠️ MUST READ - Kebijakan dan regulasi perusahaan yang harus dipatuhi',
                'order' => 9,
            ],
        ];

        foreach ($categories as $cat) {
            GuideCategory::create($cat);
        }
    }
}
