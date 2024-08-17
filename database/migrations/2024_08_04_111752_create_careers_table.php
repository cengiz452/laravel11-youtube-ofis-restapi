<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCareersTable extends Migration
{
    public function up()
    {
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('company');
            $table->string('image')->nullable();// Bu kolon eklenmeli
            $table->date('start_date'); // Bu kolon eklenmeli
            $table->date('end_date')->nullable(); // Bu kolon eklenmeli
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1); // Bu kolon eklenmeli
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('careers');
    }
}
