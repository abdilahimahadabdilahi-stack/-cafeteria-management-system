<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Shaah', 'icon' => 'cup-hot'],
            ['name' => 'Fadiirad', 'icon' => 'egg-fried'],
            ['name' => 'Cunto Waddani', 'icon' => 'basket'],
            ['name' => 'Sharaab', 'icon' => 'cup-straw'],
        ] as $category) {
            Category::query()->firstOrCreate(
                ['name' => $category['name']],
                ['icon' => $category['icon']],
            );
        }
    }
}
