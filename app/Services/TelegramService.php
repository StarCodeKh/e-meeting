<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class TelegramService
{
    /**
     * ផ្ញើការជូនដំណឹងទៅ Telegram តាមលក្ខណៈ Dynamic
     * * @param object $schedule (Eloquent Model)
     * @param string $actionType ('create' ឬ 'update')
     */
    public static function sendScheduleAlert($schedule, $actionType = 'create')
    {
        try {
            $token = config('services.telegram.bot_token') ?? env('TELEGRAM_BOT_TOKEN');
            $chatId = config('services.telegram.chat_id') ?? env('TELEGRAM_CHAT_ID');

            if (!$token || !$chatId) {
                Log::warning("⚠️ Telegram Config មិនទាន់បានកំណត់ត្រឹមត្រូវឡើយ។");
                return;
            }

            // ១. កំណត់ Icon និងចំណងជើងតាម
            $header = $actionType === 'create' 
                ? "🔔 <b>កិច្ចប្រជុំថ្មីត្រូវបានបង្កើត</b>" 
                : "🔄 <b>ការធ្វើបច្ចុប្បន្នភាពកិច្ចប្រជុំ</b>";

            // ២. ទាញយកឈ្មោះអ្នកចូលរួម
            $emails = is_array($schedule->participants) ? $schedule->participants : json_decode($schedule->participants, true);
            $names = User::whereIn('email', $emails)->pluck('name')->toArray();
            $participantNames = !empty($names) ? implode(', ', $names) : 'មិនមាន';

            // ៣. រៀបចំម៉ោង
            $startTime = date('H:i', strtotime($schedule->start_time));
            $endTime   = date('H:i', strtotime($schedule->end_time));
            $dayPart   = ((int)date('H', strtotime($schedule->start_time)) >= 12) ? 'រសៀល' : 'ព្រឹក';

            // ៤. Template Message
            $message = "{$header}\n";
            $message .= "━━━━━━━━━━━\n";
            $message .= "📝 <b>ប្រធានបទ:</b> " . ($schedule->title ?? '---') . "\n";
            $message .= "📅 <b>កាលបរិច្ឆេទ:</b> " . date('d-M-Y', strtotime($schedule->date)) . "\n";
            $message .= "⏰ <b>ម៉ោង:</b> {$startTime} {$dayPart} (ដល់ {$endTime})\n";
            $message .= "📍 <b>ទីតាំង:</b> " . ($schedule->location ?? '---') . "\n";
            $message .= "🚪 <b>បន្ទប់:</b> " . ($schedule->room ?? '---') . "\n";
            $message .= "👥 <b>ដឹកនាំដោយ:</b> " . $participantNames . "\n";
            $message .= "━━━━━━━━━━━\n";

            if ($schedule->link) {
                $message .= "🔗 <a href='{$schedule->link}'>ចូលរួមប្រជុំតាមតំណភ្ជាប់</a>\n\n";
            }

            $message .= "📝 <i>" . ($schedule->description ?? 'មិនមានការពិពណ៌នា') . "</i>";

            // ៥. ផ្ញើចេញ (Check Attachment)
            $apiUrl = "https://api.telegram.org/bot{$token}";

            if ($schedule->attachment && Storage::disk('public')->exists($schedule->attachment)) {
                return Http::attach(
                    'document', 
                    Storage::disk('public')->get($schedule->attachment), 
                    basename($schedule->attachment)
                )->post("{$apiUrl}/sendDocument", [
                    'chat_id' => $chatId,
                    'caption' => $message,
                    'parse_mode' => 'HTML'
                ]);
            }

            return Http::post("{$apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Telegram Service Error: " . $e->getMessage());
        }
    }
}