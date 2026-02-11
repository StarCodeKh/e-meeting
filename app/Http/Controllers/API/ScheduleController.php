<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleRequest;
use App\Http\Resources\ScheduleResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    // ១. ទាញយកទិន្នន័យទាំងអស់ (Index)
    public function index(Request $request)
    {
        try {
            $userEmail = auth()->user()->email;
            $date = $request->query('date', Carbon::today()->toDateString());

            $schedules = Schedule::whereDate('date', $date)
                ->where(function ($query) use ($userEmail) {
                    $query->whereJsonContains('participants', $userEmail)
                        ->Where('user_id', auth()->id());
                })
                ->orderBy('start_time', 'asc')->get();

            return ScheduleResource::collection($schedules);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'មិនអាចទាញយកទិន្នន័យកាលវិភាគបានទេ',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function schedulesPublic(Request $request)
    {
        try {
            
            $date = $request->query('date', Carbon::today()->toDateString());
            $schedules = Schedule::whereDate('date', $date)->orderBy('start_time', 'asc')->get();

            return ScheduleResource::collection($schedules);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'មិនអាចទាញយកទិន្នន័យកាលវិភាគបានទេ',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ២. រក្សាទុកទិន្នន័យថ្មី (Store)
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
                if (isset($path)) {
                    Storage::disk('public')->delete($path);
                }
                return response()->json([
                    'status'  => 'error',
                    'message' => 'បរាជ័យក្នុងការរក្សាទុក!',
                    'error'   => $e->getMessage()
                ], 500);
            }
        });
    }

    // ៣. បង្ហាញទិន្នន័យមួយ (Show)
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
            return response()->json([
                'status'  => 'error',
                'message' => 'រកមិនឃើញទិន្នន័យដែលអ្នកស្នើសុំឡើយ',
            ], 404);
        }
    }

    // ៤. កែប្រែទិន្នន័យ (Update)
    public function update(ScheduleRequest $request, Schedule $schedule)
    {
        try {
            if ($schedule->user_id !== auth()->id()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'អ្នកមិនមានសិទ្ធិកែប្រែកាលវិភាគនេះទេ!'
                ], 403);
            }

            if ($request->hasFile('attachment')) {
                // លុប File ចាស់ (បើមាន)
                if ($schedule->attachment) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $schedule->attachment));
                }
                // រក្សាទុក File ថ្មី (ដូចកូដក្នុង store ខាងលើ)
            }

            $schedule->update($request->validated());

            return response()->json([
                'status'  => 'success',
                'message' => 'កែប្រែបានជោគជ័យ!',
                'data'    => new ScheduleResource($schedule)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេស មិនអាចកែប្រែបានទេ!',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ៥. លុបទិន្នន័យ (Destroy)
    public function destroy(Schedule $schedule)
    {
        try {
            if ($schedule->attachment) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $schedule->attachment));
            }
            $schedule->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'លុបកាលវិភាគបានជោគជ័យជានិរន្តរ៍!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'មិនអាចលុបទិន្នន័យនេះបានទេ!',
                'error'   => $e->getMessage() 
            ], 500);
        }
    }
}