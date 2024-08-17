<?php

namespace Database\Seeders;

use App\Models\Career;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CareerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Career::create([
            'title' => 'Yazılım Uzmanı',
            'company' => 'Boztepe Anonim Şirketi',
            'start_date' => '2022-01-01',
            'end_date' => '2023-01-01',
            'description' => 'Developed web applications using laravel.',
            'status' => 1,

        ]);
    }
}
