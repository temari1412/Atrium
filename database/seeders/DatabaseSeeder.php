<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ここで先ほど作った ProductSeeder を呼び出すように指示します
        $this->call([
            ProductSeeder::class,
        ]);
    }
}