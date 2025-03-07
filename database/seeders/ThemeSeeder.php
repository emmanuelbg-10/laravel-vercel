<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('themes')->insert([
            ['name' => 'Genérico', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tecnología', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ciencia', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Arte', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Deportes', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}