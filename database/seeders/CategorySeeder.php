<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Musik',
            'Teknologi',
            'Olahraga',
            'Seni & Budaya',
            'Bisnis',
            'Pendidikan',
            'Kesehatan',
            'Komunitas',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}
