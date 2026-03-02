<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotificationSettingController extends Controller
{
    /**
     * ទាញយកការកំណត់តាមក្រុម (Standard Index)
     * ប្រើប្រាស់ Dynamic Grouping ដើម្បីផ្ដល់ទិន្នន័យតាមប្រភេទ
     */
    public function index()
    {
        try {

            $telegramSettings = Setting::getByGroup('telegram');
            $pushSettings     = Setting::getByGroup('push');

            $data = [
                'telegram' => [
                    'enabled'       => ($telegramSettings['telegram_enabled'] ?? '1') === '1',
                    'reminder_time' => (int) ($telegramSettings['telegram_reminder_minutes'] ?? 15),
                ],
                'push_notification' => [
                    'enabled' => ($pushSettings['push_enabled'] ?? '1') === '1',
                ],
                'meta' => [
                    'updated_at' => now()->toDateTimeString(),
                    'server_timezone' => config('app.timezone')
                ]
            ];

            return response()->json([
                'status' => 'success',
                'data'   => $data
            ], 200);

        } catch (\Exception $e) {
            Log::error("❌ Fetch Settings Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'មិនអាចទាញយកទិន្នន័យបានទេ'], 500);
        }
    }

    /**
     * រក្សាទុកការកំណត់ (Standard Update)
     * បញ្ចូលទិន្នន័យទៅក្នុង Database តាម Dynamic Groups
     */
    public function update(Request $request)
    {
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json(['status' => 'error', 'message' => 'គ្មានសិទ្ធិចូលប្រើប្រាស់'], 403);
        }

        $validator = Validator::make($request->all(), [
            'telegram_enabled' => 'required|boolean',
            'push_enabled'     => 'required|boolean',
            'reminder_time'    => 'required|integer|in:5,15,30,60',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {

            Setting::set('telegram_enabled', $request->telegram_enabled ? '1' : '0', 'telegram');
            Setting::set('telegram_reminder_minutes', $request->reminder_time, 'telegram');
            
            Setting::set('push_enabled', $request->push_enabled ? '1' : '0', 'push');

            return response()->json([
                'status'  => 'success',
                'message' => 'រក្សាទុកការកំណត់ជោគជ័យ!',
                'data'    => $request->only(['telegram_enabled', 'push_enabled', 'reminder_time'])
            ], 200);

        } catch (\Exception $e) {
            Log::error("❌ Update Settings Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'បរាជ័យក្នុងការរក្សាទុក'], 500);
        }
    }
}