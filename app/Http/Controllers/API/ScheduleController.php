<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleRequest;
use App\Http\Resources\ScheduleResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    // ទាញយកទិន្នន័យទាំងអស់ (Index)
    public function index(Request $request)
    {
        try {
            $userEmail = auth()->user()->email;
            $date = $request->query('date', Carbon::today()->toDateString());

            $schedules = Schedule::whereDate('date', $date)
                ->where(function ($query) use ($userEmail) {
                    $query->whereJsonContains('participants', $userEmail)
                        ->orWhere('user_id', auth()->id());
                })
                ->orderBy('start_time', 'asc')->get();

            return ScheduleResource::collection($schedules);

        } catch (\Exception $e) {
            Log::error("❌ Index Error: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'មិនអាចទាញយកទិន្នន័យកាលវិភាគបានទេ',
            ], 500);
        }
    }
    // បង្ហាញទិន្នន័យជាសាធារណៈ (Public)
    public function schedulesPublic(Request $request)
    {
        try {
            $date = $request->query('date', Carbon::today()->toDateString());
            $schedules = Schedule::whereDate('date', $date)->orderBy('start_time', 'asc')->get();

            return ScheduleResource::collection($schedules);

        } catch (\Exception $e) {
            Log::error("❌ Public Schedules Error: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'មិនអាចទាញយកទិន្នន័យកាលវិភាគបានទេ',
            ], 500);
        }
    }

    // បង្ហាញប្រតិទិន១ខែ (Calendar Show Month)
    public function calendarShow(Request $request)
    {
        try {
            $month = $request->query('month', date('m'));
            $year = $request->query('year', date('Y'));

            $schedules = Schedule::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();
            return ScheduleResource::collection($schedules);
            
        } catch (\Exception $e) {
            Log::error("❌ CalendarShow Error: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេស មិនអាចទាញយកទិន្នន័យកាលវិភាគបានទេ',
            ], 500);
        }
    }

    // រក្សាទុកទិន្នន័យថ្មី (Store)
    public function store(ScheduleRequest $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $data = $request->validated();
                $data['user_id'] = auth()->id();

                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $path = $file->store('attachments', 'public');
                    $data['attachment'] = $path; 
                }

                $schedule = Schedule::create($data);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'រក្សាទុកបានជោគជ័យ!',
                    'data'    => new ScheduleResource($schedule)
                ], 201);

            } catch (\Exception $e) {
                Log::error("❌ Store Error: " . $e->getMessage());
                if (isset($path)) {
                    Storage::disk('public')->delete($path);
                }
                return response()->json([
                    'status'  => 'error',
                    'message' => 'បរាជ័យក្នុងការរក្សាទុក!',
                ], 500);
            }
        });
    }

    // បង្ហាញទិន្នន័យមួយ (Show)
    public function show(Schedule $schedule)
    {
        try {
            if ($schedule->user_id !== auth()->id()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'អ្នកមិនមានសិទ្ធិចូលមើលទិន្នន័យនេះទេ!'
                ], 403); 
            }

            return new ScheduleResource($schedule);

        } catch (\Exception $e) {
            Log::error("❌ Show Error: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'រកមិនឃើញទិន្នន័យដែលអ្នកស្នើសុំឡើយ',
            ], 404);
        }
    }

    // កែប្រែទិន្នន័យ (Update)
    public function update(ScheduleRequest $request, Schedule $schedule)
    {
        try {
            if ($schedule->user_id !== auth()->id()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'អ្នកមិនមានសិទ្ធិកែប្រែកាលវិភាគនេះទេ!'
                ], 403);
            }

            $data = $request->validated();

            if ($request->hasFile('attachment')) {
                if ($schedule->getRawOriginal('attachment')) {
                    Storage::disk('public')->delete($schedule->getRawOriginal('attachment'));
                }
                $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
            }

            $schedule->update($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'កែប្រែបានជោគជ័យ!',
                'data'    => new ScheduleResource($schedule)
            ], 200);

        } catch (\Exception $e) {
            Log::error("❌ Update Error: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេស មិនអាចកែប្រែបានទេ!',
            ], 500);
        }
    }

    // លុបទិន្នន័យ (Destroy)
    public function destroy(Schedule $schedule)
    {
        try {
            if ($schedule->user_id !== auth()->id()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            if ($schedule->getRawOriginal('attachment')) {
                Storage::disk('public')->delete($schedule->getRawOriginal('attachment'));
            }
            $schedule->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'លុបកាលវិភាគបានជោគជ័យ!'
            ], 200);

        } catch (\Exception $e) {
            Log::error("❌ Destroy Error: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'មិនអាចលុបទិន្នន័យនេះបានទេ!',
            ], 500);
        }
    }
}