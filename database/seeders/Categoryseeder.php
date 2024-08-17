<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class Categoryseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name'=> 'Yazılım'
        ]);
        Category::create([
            'name'=> 'Tasarım'
        ]);
        Category::create([
            'name'=> 'Dijital Pazarlama'
        ]);
    }
}
