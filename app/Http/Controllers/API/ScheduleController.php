<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\ScheduleRequest;
use App\Http\Resources\ScheduleResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    use AuthorizesRequests;

    // ទាញយកទិន្នន័យថ្ងៃនេះ (Index today)
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $date = $request->query('date', Carbon::today()->toDateString());

            $query = Schedule::whereDate('date', $date);

            if (!$user->hasRole('admin')) {
                $query->where('user_id', $user->id);
            }

            $schedules = $query->orderBy('start_time', 'asc')->get();

            return ScheduleResource::collection($schedules)->additional([
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Schedule Index Error: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'មិនអាចទាញយកទិន្នន័យបានទេ',
            ], 500);
        }
    }
    
    // បង្ហាញទិន្នន័យជាសាធារណៈ (Public)
    /**
     * បង្ហាញទិន្នន័យកិច្ចប្រជុំជាសាធារណៈ (Public - Meetings Only)
     */
    public function schedulesPublic(Request $request)
    {
        try {
            $date = $request->query('date', Carbon::today()->toDateString());

            $schedules = Schedule::whereDate('date', $date)
                ->where('type', 'meeting')
                ->orderBy('start_time', 'asc')
                ->get();

            return ScheduleResource::collection($schedules)->additional([
                'status' => 'success',
                'meta'   => [
                    'date' => $date,
                    'count' => $schedules->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Public Meetings Error: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'មិនអាចទាញយកទិន្នន័យកិច្ចប្រជុំបានទេ',
            ], 500);
        }
    }

    // បង្ហាញប្រតិទិន១ខែ (Calendar Show Month)
    public function calendarShow(Request $request)
    {
        try {
            $user = auth()->user();
            $month = $request->query('month', date('m'));
            $year = $request->query('year', date('Y'));

            $query = Schedule::whereYear('date', $year)
                ->whereMonth('date', $month);

            if (!$user->hasRole('admin')) {
                $query->where('user_id', $user->id);
            }

            $schedules = $query->orderBy('date', 'asc')->orderBy('start_time', 'asc')->get();

            return ScheduleResource::collection($schedules)->additional([
                'status' => 'success'
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ CalendarShow Error: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេស មិនអាចទាញយកទិន្នន័យកាលវិភាគបានទេ',
            ], 500);
        }
    }

    // ទាញយកទិន្នន័យទាំងអស់ (calendar All)
    public function scheduleAll(Request $request)
    {
        try {
            $user = auth()->user();
            $query = Schedule::query();

            if (!$user->hasRole('admin')) {
                $query->where('user_id', $user->id);
            }

            if ($request->filled(['month', 'year'])) {
                $query->whereMonth('date', $request->month)
                    ->whereYear('date', $request->year);
            } elseif ($request->filled('date')) {
                $query->whereDate('date', $request->date);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('location', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            $perPage = $request->integer('per_page', 15);
            $schedules = $query->orderBy('date', 'desc')->orderBy('start_time', 'asc')->paginate($perPage)->withQueryString();

            return ScheduleResource::collection($schedules)->additional([
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            \Log::error("❌ Schedule Access Error: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'មិនអាចទាញយកទិន្នន័យបានទេ',
            ], 500);
        }
    }

    public function participants(Request $request)
    {
        $users = User::select('id', 'name', 'email')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
            })->orderBy('name', 'asc')->limit(100)->get();

        return response()->json([
            'status' => 'success',
            'data'   => $users
        ]);
    }

    // រក្សាទុកទិន្នន័យថ្មី (Store)
    public function store(ScheduleRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $path = null; 
            try {
                $data = $request->validated();
                $data['user_id'] = auth()->id();

                // ១. ចាត់ចែងការ Upload File
                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $nameWithoutExtension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $filename = $nameWithoutExtension . '_' . now()->format('Y-m-d_H-i-s') . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('attachments', $filename, 'public');
                    $data['attachment'] = $path; 
                }

                // ២. បង្កើត Schedule
                $schedule = Schedule::create($data);

                // ៣. ផ្ញើ Alert ទៅ Telegram (ប្រើអថេរ $schedule)
                // យើងប្រើ try-catch តូចមួយនៅទីនេះ ដើម្បីកុំឱ្យបញ្ហាផ្ញើ Telegram ធ្វើឱ្យ Error ទាំងមូល
                try {
                    TelegramService::sendScheduleAlert($schedule);
                } catch (\Exception $te) {
                    Log::warning("⚠️ Telegram Alert Failed: " . $te->getMessage());
                }

                return response()->json([
                    'status'  => 'success',
                    'message' => 'រក្សាទុកបានជោគជ័យ!',
                    'data'    => new ScheduleResource($schedule)
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("❌ Store Error: " . $e->getMessage());

                if ($path) {
                    Storage::disk('public')->delete($path);
                }

                return response()->json([
                    'status'  => 'error',
                    'message' => 'បរាជ័យក្នុងការរក្សាទុក៖ ' . $e->getMessage(),
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
        $this->authorize('update', $schedule);

        return DB::transaction(function () use ($request, $schedule) {
            $oldPath = $schedule->attachment; 
            
            try {
                $data = $request->validated();

                if ($request->hasFile('attachment')) {
                    if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }

                    $file = $request->file('attachment');
                    $nameWithoutExtension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $filename = $nameWithoutExtension . '_' . now()->format('Y-m-d_H-i-s') . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('attachments', $filename, 'public');
                    $data['attachment'] = $path; 
                }

                $schedule->update($data);

                try {
                    TelegramService::sendScheduleAlert($schedule->fresh(), 'update');
                } catch (\Exception $te) {
                    Log::warning("⚠️ Telegram Alert Update Failed: " . $te->getMessage());
                }

                return response()->json([
                    'status'  => 'success',
                    'message' => 'កែប្រែបានជោគជ័យ!',
                    'data'    => new ScheduleResource($schedule->load('user'))
                ], 200);

            } catch (\Exception $e) {
                Log::error("❌ Update Error: " . $e->getMessage());
                
                return response()->json([
                    'status'  => 'error',
                    'message' => 'មានបញ្ហាបច្ចេកទេស មិនអាចកែប្រែបានទេ!',
                ], 500);
            }
        });
    }

    // លុបទិន្នន័យ (Destroy)
    public function destroy(Schedule $schedule)
    {
        $this->authorize('delete', $schedule);

        try {
            $filePath = $schedule->getRawOriginal('attachment');
            
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            $schedule->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'លុបកាលវិភាគបានជោគជ័យ!'
            ], 200);

        } catch (\Exception $e) {
            Log::error("❌ Destroy Error: [ID: {$schedule->id}] " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេស មិនអាចលុបទិន្នន័យនេះបានទេ!',
            ], 500);
        }
    }
}