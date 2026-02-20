<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;
use App\Services\TelegramService;
use App\Models\Schedule;
use App\Models\Setting;
use Carbon\Carbon;

class SendMeetingReminders extends Command
{
    protected $signature = 'meeting:remind';

    public function handle()
    {
        if (Setting::get('telegram_enabled', '1') === '0') return;

        $now = Carbon::now('Asia/Phnom_Penh');
        $today = $now->toDateString();
        $currentTime = $now->format('H:i');

        $remindMinutes = (int) Setting::get('telegram_reminder_minutes', 15);
        $targetReminderTime = $now->copy()->addMinutes($remindMinutes)->format('H:i');

        $schedules = Schedule::whereDate('date', $today)
            ->where(function($query) use ($currentTime, $targetReminderTime) {
                $query->whereTime('start_time', 'LIKE', $currentTime . '%')
                    ->orWhereTime('start_time', 'LIKE', $targetReminderTime . '%');
            })
            ->get();

        foreach ($schedules as $schedule) {
            $cacheKey = "telegram_sent_" . $schedule->id . "_" . $currentTime;
            
            if (!Cache::has($cacheKey)) {
                $type = ($schedule->start_time == $currentTime . ':00') ? 'start_now' : 'remind';
                
                TelegramService::sendScheduleAlert($schedule, $type);
                
                Cache::put($cacheKey, true, 120);
                $this->info("Sent alert for: " . $schedule->title);
            }
        }
    }
}