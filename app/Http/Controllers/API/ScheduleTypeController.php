<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ScheduleType;
use App\Models\Priority;

class ScheduleTypeController extends Controller
{
    /**
     * Get all schedule options (Standard Index)
     */
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

    /**
     * Update Schedule Type Color (កែសម្រួលចេញពី Logic Priority របស់បង)
     */
    public function updateTypeColor(Request $request): JsonResponse
    {
        // ១. Validation (ប្តូរ table ទៅ schedule_types)
        $request->validate([
            'slug'      => 'required|exists:schedule_types,slug',
            'color_hex' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        try {
            // ២. ស្វែងរកក្នុង Model ScheduleType
            $type = ScheduleType::where('slug', $request->slug)->first();
            
            if (!$type) {
                return response()->json(['status' => 'error', 'message' => 'រកមិនឃើញទិន្នន័យ'], 404);
            }

            // ៣. Update ពណ៌ និង Gradient (បើចង់ឱ្យ Card មើលទៅស្អាត)
            $type->update([
                'color_hex'      => $request->color_hex,
                'color_gradient' => "linear-gradient(135deg, {$request->color_hex}cc, {$request->color_hex})"
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'ប្រភេទកិច្ចការត្រូវបានធ្វើបច្ចុប្បន្នភាព!',
                'data' => $type
            ], 200);

        } catch (\Exception $e) {
            return $this->handleError($e, 'មិនអាចធ្វើបច្ចុប្បន្នភាពពណ៌ប្រភេទកិច្ចការបានទេ។');
        }
    }

    /**
     * Update Priority Color (Standard Dynamic API)
     */
    public function updatePriority(Request $request): JsonResponse
    {
        $request->validate([
            'slug'      => 'required|exists:priorities,slug',
            'color_hex' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        try {
            $priority = Priority::where('slug', $request->slug)->first();
            
            if (!$priority) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'រកមិនឃើញទិន្នន័យ'
                ], 404);
            }

            $priority->update([
                'color_hex' => $request->color_hex
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'កម្រិតអាទិភាពត្រូវបានធ្វើបច្ចុប្បន្នភាព!',
                'data' => [
                    'id'        => $priority->id,
                    'slug'      => $priority->slug,
                    'color_hex' => $priority->color_hex,
                ]
            ], 200);

        } catch (\Exception $e) {
            return $this->handleError($e, 'មិនអាចធ្វើបច្ចុប្បន្នភាពពណ៌បានទេ។');
        }
    }

    /**
     * Centralized Error Handling (Standard Practice)
     */
    private function handleError(\Exception $e, string $userMessage): JsonResponse
    {
        Log::error("ScheduleTypeController Error: " . $e->getMessage(), [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status'  => 'error',
            'message' => $userMessage
        ], 500);
    }
}