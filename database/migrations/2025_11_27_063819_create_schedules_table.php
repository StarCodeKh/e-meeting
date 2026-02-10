<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('type'); 
            $table->string('title');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->json('participants')->nullable(); 
            $table->string('location')->nullable();
            $table->string('room')->nullable();
            $table->text('link')->nullable();
            $table->string('color_id')->nullable(); 
            $table->text('description')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('user_id')->constrained() ->cascadeOnDelete(); 
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
