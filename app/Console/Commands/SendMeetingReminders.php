<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\PushNotificationService;
use App\Services\TelegramService;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class SendMeetingReminders extends Command
{
    protected $signature = 'meeting:remind';
    protected $description = 'ផ្ញើសាររំលឹកកិច្ចប្រជុំទៅកាន់ Telegram និង Mobile App';

    public function handle()
    {
        $tgSettings = Setting::getByGroup('telegram');
        $pushSettings = Setting::getByGroup('push');

        $isTgEnabled = ($tgSettings['telegram_enabled'] ?? '1') === '1';
        $isPushEnabled = ($pushSettings['push_enabled'] ?? '1') === '1';

        if (!$isTgEnabled && !$isPushEnabled) return;

        $now = Carbon::now('Asia/Phnom_Penh');
        $currentTime = $now->format('H:i');
        $remindMinutes = (int) ($tgSettings['telegram_reminder_minutes'] ?? 15);
        $targetTime = $now->copy()->addMinutes($remindMinutes)->format('H:i');

        $schedules = Schedule::whereDate('date', $now->toDateString())
            ->where(function($query) use ($currentTime, $targetTime) {
                $query->whereTime('start_time', $currentTime)
                      ->orWhereTime('start_time', $targetTime);
            })->get();

        if ($schedules->isEmpty()) return;

        foreach ($schedules as $schedule) {
            $isStartNow = (Carbon::parse($schedule->start_time)->format('H:i') === $currentTime);
            $type = $isStartNow ? 'start_now' : 'remind';

            $cacheKey = "meeting_alert_sent_{$schedule->id}_{$type}";
            if (Cache::has($cacheKey)) continue;

            // --- ផ្នែកផ្ញើ Notification ---

            if ($isTgEnabled) {
                try {
                    TelegramService::sendScheduleAlert($schedule, $type);
                } catch (\Exception $e) {
                    Log::error("❌ Telegram Reminder Error: " . $e->getMessage());
                }
            }

            if ($isPushEnabled) {
                $this->sendToMobileApp($schedule, $type);
            }

            Cache::put($cacheKey, true, 3600);
            $this->info("✅ Alert sent for: {$schedule->title} ({$type})");
        }
    }

    /**
     * មុខងារជំនួយសម្រាប់ផ្ញើ Push Notification ទៅកាន់ User ពាក់ព័ន្ធ
     */
    private function sendToMobileApp($schedule, $type)
    {
        try {
            $title = $type === 'start_now' ? "🔴 កិច្ចប្រជុំកំពុងចាប់ផ្ដើម" : "⏰ រំលឹកកិច្ចប្រជុំ (ជិតដល់ម៉ោង)";
            $body = "កិច្ចប្រជុំ៖ {$schedule->title} នៅសាលប្រជុំ៖ " . ($schedule->location ?? 'មិនទាន់កំណត់');

            $users = User::whereNotNull('fcm_token')->get();

            foreach ($users as $user) {
                PushNotificationService::send(
                    $user->fcm_token,
                    $title,
                    $body,
                    ['schedule_id' => (string) $schedule->id, 'type' => $type]
                );
            }
        } catch (\Exception $e) {
            Log::error("❌ Push Reminder Error: " . $e->getMessage());
        }
    }
}