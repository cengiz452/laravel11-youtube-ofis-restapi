<?php

namespace Database\Seeders;

use App\Models\Blog;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class Blogseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Blog::create([
            'category_id'=> '1',
            'name'=> 'Banane Dünya',
            'content'=>'Bu bir Blog Yazısı'
        ]);

    }
}
