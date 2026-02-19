<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\Setting;
use App\Services\TelegramService;
use Carbon\Carbon;

class SendMeetingReminders extends Command
{
    protected $signature = 'meeting:remind';

    public function handle()
    {
        // ១. ឆែកមើលថា User បិទ ឬ បើក
        if (Setting::get('telegram_enabled', '1') === '0') return;

        // ២. ទាញយកនាទីរំលឹកពី Setting
        $minutes = (int) Setting::get('telegram_reminder_minutes', 15);
    
        $targetTime = Carbon::now('Asia/Phnom_Penh')->addMinutes($minutes)->format('H:i');
        $today = Carbon::today()->toDateString();

        $schedules = Schedule::whereDate('date', $today)
            ->whereTime('start_time', '=', $targetTime . ':00')
            ->get();

        foreach ($schedules as $schedule) {
            TelegramService::sendScheduleAlert($schedule, 'remind');
            $this->info("Reminded: " . $schedule->title);
        }
    }
}