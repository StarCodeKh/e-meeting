<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class NotificationSettingController extends Controller
{
    /**
     * ទាញយកការកំណត់បច្ចុប្បន្ន
     */
    public function index()
    {
        return response()->json([
            'enabled' => Setting::get('telegram_enabled', '1') === '1',
            'reminder_time' => (int) Setting::get('telegram_reminder_minutes', 15),
        ]);
    }

    /**
     * រក្សាទុកការកំណត់ថ្មី
     */
    public function update(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'សុំទោស! មានតែអភិបាលប្រព័ន្ធ (Admin) ប៉ុណ្ណោះដែលអាចកែប្រែការកំណត់នេះបាន។'
            ], 403);
        }
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'reminder_time' => 'required|integer|in:5,15,30,60',
        ]);

        try {

            Setting::set('telegram_enabled', $request->enabled ? '1' : '0');
            Setting::set('telegram_reminder_minutes', $request->reminder_time);

            return response()->json([
                'status' => 'success',
                'message' => 'រក្សាទុកការកំណត់ជោគជ័យ!'
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error updating settings: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេស មិនអាចរក្សាទុកបានទេ។'
            ], 500);
        }
    }
}