<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use App\Models\Schedule;
use Carbon\Carbon;

class SendMeetingReminders extends Command
{
    protected $signature = 'meeting:remind';
    protected $description = 'ឆែក និងផ្ញើសាររំលឹកកិច្ចប្រជុំមុន ១៥ នាទី';

    public function handle()
    {
        $reminderTime = Carbon::now()->addMinutes(15)->format('H:i');
        $today = Carbon::today()->toDateString();

        $schedules = Schedule::whereDate('date', $today)
            ->whereTime('start_time', '=', $reminderTime . ':00')
            ->get();

        if ($schedules->isEmpty()) {
            $this->info("មិនមានកិច្ចប្រជុំត្រូវរំលឹកនៅឡើយទេ។");
            return;
        }

        foreach ($schedules as $schedule) {
            TelegramService::sendScheduleAlert($schedule, 'remind');
            $this->info("បានផ្ញើសាររំលឹកសម្រាប់៖ " . $schedule->title);
        }
    }
}