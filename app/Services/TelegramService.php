<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class TelegramService
{
    /**
     * ផ្ញើការជូនដំណឹងទៅ Telegram តាមលក្ខណៈ Dynamic
     * * @param object $schedule
     * @param string $actionType ('create', 'update', ឬ 'remind')
     */
    public static function sendScheduleAlert($schedule, $actionType = 'create')
    {

        $isEnabled = Setting::get('telegram_enabled', '1') === '1'; 
        if (!$isEnabled) return;

        try {
            $token = config('services.telegram.bot_token') ?? env('TELEGRAM_BOT_TOKEN');
            $chatId = config('services.telegram.chat_id') ?? env('TELEGRAM_CHAT_ID');

            if (!$token || !$chatId) {
                Log::warning("⚠️ Telegram Config មិនទាន់បានកំណត់ត្រឹមត្រូវឡើយ។");
                return;
            }

            // ២. កំណត់ Icon
            $header = match($actionType) {
                'create' => "🔔 <b>កិច្ចប្រជុំថ្មីត្រូវបានបង្កើត</b>",
                'update' => "🔄 <b>ការធ្វើបច្ចុប្បន្នភាពកិច្ចប្រជុំ</b>",
                'remind' => "⏰ <b>ការរំលឹក៖ កិច្ចប្រជុំនឹងចាប់ផ្ដើមឆាប់ៗនេះ</b>",
                default  => "📅 <b>ព័ត៌មានកិច្ចប្រជុំ</b>"
            };

            $emails = is_array($schedule->participants) ? $schedule->participants : json_decode($schedule->participants, true);
            $names = User::whereIn('email', $emails)->pluck('name')->toArray();
            $participantNames = !empty($names) ? implode(', ', $names) : 'មិនមាន';

            $start = Carbon::parse($schedule->start_time);
            $end = Carbon::parse($schedule->end_time);
            $dayPart = ($start->hour >= 12) ? 'រសៀល' : 'ព្រឹក';

            // ៥. Template Message
            $message = "{$header}\n";
            $message .= "━━━━━━━━━━━━━━━━\n";
            $message .= "📝 <b>ប្រធានបទ:</b> " . ($schedule->title ?? '---') . "\n";
            $message .= "📅 <b>កាលបរិច្ឆេទ:</b> " . $start->format('d-M-Y') . "\n";
            $message .= "⏰ <b>ម៉ោង:</b> " . $start->format('H:i') . " {$dayPart} (ដល់ " . $end->format('H:i') . ")\n";
            $message .= "📍 <b>ទីតាំង:</b> " . ($schedule->location ?? '---') . "\n";
            $message .= "🚪 <b>បន្ទប់:</b> " . ($schedule->room ?? '---') . "\n";
            $message .= "👥 <b>អ្នកចូលរួម:</b> " . $participantNames . "\n";
            $message .= "━━━━━━━━━━━━━━━━\n";

            if ($schedule->link) {
                $message .= "🔗 <a href='{$schedule->link}'>ចូលរួមប្រជុំតាមតំណភ្ជាប់ (Online)</a>\n\n";
            }

            $message .= "📝 <i>" . ($schedule->description ?? 'មិនមានការពិពណ៌នា') . "</i>";

            $apiUrl = "https://api.telegram.org/bot{$token}";

            // ៦. ការផ្ញើចេញ (Document ឬ Message)
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
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => false
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Telegram Service Error: " . $e->getMessage());
        }
    }
}