<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Models\ScheduleType;
use App\Models\Priority;

class ScheduleTypeController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $types = ScheduleType::active()->get();
            $priorities = Priority::ordered()->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'types'      => $types,
                    'priorities' => $priorities
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error("Schedule Type Index Error: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេសក្នុងការទាញយកទិន្នន័យ។' 
            ], 500);
        }
    }
}