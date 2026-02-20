<?php


use Illuminate\Support\Facades\Schedule as TaskSchedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Inspiring;
use App\Services\TelegramService;
use App\Models\Schedule;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

TaskSchedule::call(function () {
    $now = Carbon::now('Asia/Phnom_Penh');
    $today = $now->toDateString();
    
    // ១. កំណត់ម៉ោងគោលដៅ (បច្ចុប្បន្ន + ១៥ នាទី)
    $targetTime = $now->copy()->addMinutes(15)->format('H:i');

    // ២. ទាញយកកិច្ចប្រជុំ
    $meetings = Schedule::whereDate('date', $today)
        ->whereTime('start_time', 'LIKE', $targetTime . '%')
        ->get();

    foreach ($meetings as $meeting) {
        // ៣. បង្កើត Cache Key សម្រាប់កិច្ចប្រជុំនេះ និងនាទីនេះ
        $cacheKey = "sent_remind_{$meeting->id}_{$targetTime}";

        // ៤. ឆែកមើលថាបានផ្ញើរួចហើយឬនៅ បើមិនទាន់ទេទើបផ្ញើ
        if (!Cache::has($cacheKey)) {
            TelegramService::sendScheduleAlert($meeting, 'remind');
            
            // រក្សាទុកក្នុង Cache រយៈពេល ២ នាទី (ដើម្បីឱ្យផុតនាទីហ្នឹងសិន)
            Cache::put($cacheKey, true, 120);
        }
    }
})->everyMinute();
