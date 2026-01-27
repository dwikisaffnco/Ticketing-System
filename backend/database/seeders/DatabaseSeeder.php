<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'khair@saffnco.com'],
            [
                'name' => 'Khair',
                'password' => bcrypt('password'),
                'role' => 'user'
            ]
        );

        User::updateOrCreate(
            ['email' => 'dwiki@saffnco.com'],
            [
                'name' => 'Dwiki (Team IT)',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );

        User::updateOrCreate(
            ['email' => 'arnal@saffnco.com'],
            [
                'name' => 'Putra (Team IT)',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );

        // Seed guides and categories
        $this->call(GuideSeeder::class);
    }
}
