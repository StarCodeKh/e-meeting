<?php


use Illuminate\Support\Facades\Schedule as TaskSchedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Inspiring;
use App\Services\TelegramService;
use App\Models\Schedule;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

TaskSchedule::call(function () {
    // កំណត់ម៉ោងបច្ចុប្បន្ន + ១៥ នាទី
    $targetTime = Carbon::now('Asia/Phnom_Penh')->addMinutes(15)->format('H:i');
    $today = Carbon::today()->toDateString();

    // ស្វែងរកកិច្ចប្រជុំដែលត្រូវនឹងម៉ោងនោះ
    $meetings = Schedule::whereDate('date', $today)
        ->whereTime('start_time', '=', $targetTime . ':00')
        ->get();

    foreach ($meetings as $meeting) {
        TelegramService::sendScheduleAlert($meeting, 'remind');
    }
})->everyMinute();
