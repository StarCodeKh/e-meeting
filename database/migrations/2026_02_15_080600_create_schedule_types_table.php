<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ១. បង្កើត Table សម្រាប់ Schedule Types (Meeting, Appointment, Task)
        Schema::create('schedule_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('slug')->unique(); 
            $table->string('color_hex', 7); 
            $table->string('color_gradient')->nullable(); 
            $table->string('icon'); 
            $table->boolean('is_active')->default(true); 
            $table->integer('sort_order')->default(0); 
            $table->timestamps();
        });

        // ២. បង្កើត Table សម្រាប់ Priorities (បន្ទាន់, មធ្យម, ធម្មតា)
        Schema::create('priorities', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // red, yellow, green
            $table->string('name'); // បន្ទាន់...
            $table->string('color_hex', 7);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // ៣. បញ្ចូលទិន្នន័យសម្រាប់ Schedule Types
        DB::table('schedule_types')->insert([
            [
                'name' => 'កិច្ចប្រជុំ',
                'slug' => 'meeting',
                'color_hex' => '#e54d42',
                'color_gradient' => 'linear-gradient(135deg, #ff6b6b, #e54d42)',
                'icon' => 'bi bi-camera-video',
                'sort_order' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'name' => 'ការណាត់',
                'slug' => 'appointment',
                'color_hex' => '#fcc419',
                'color_gradient' => 'linear-gradient(135deg, #ffd43b, #fcc419)',
                'icon' => 'bi bi-calendar-event',
                'sort_order' => 2,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'name' => 'ការងារ',
                'slug' => 'task',
                'color_hex' => '#34a853',
                'color_gradient' => 'linear-gradient(135deg, #51cf66, #34a853)',
                'icon' => 'bi bi-check2-circle',
                'sort_order' => 3,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        // ៤. បញ្ចូលទិន្នន័យសម្រាប់ Priorities (COLOR_OPTIONS)
        DB::table('priorities')->insert([
            [
                'slug' => 'red',
                'name' => 'បន្ទាន់',
                'color_hex' => '#ff6b6b',
                'sort_order' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'slug' => 'yellow',
                'name' => 'មធ្យម',
                'color_hex' => '#fcc419',
                'sort_order' => 2,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'slug' => 'green',
                'name' => 'ធម្មតា',
                'color_hex' => '#51cf66',
                'sort_order' => 3,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('priorities');
        Schema::dropIfExists('schedule_types');
    }
};