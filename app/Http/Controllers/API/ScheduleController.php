<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleRequest;
use App\Http\Resources\ScheduleResource;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Carbon\Carbon;


class ScheduleController extends Controller
{
    // ១. ទាញយកទិន្នន័យទាំងអស់ (Index)
   public function index()
    {
        $userEmail = auth()->user()->email;
        $schedules = Schedule::whereDate('date', Carbon::today())
            ->whereJsonContains('participants', $userEmail)
            ->orderBy('start_time', 'asc')
            ->paginate(10);

        return ScheduleResource::collection($schedules);
    }

    // ២. រក្សាទុកទិន្នន័យថ្មី (Store)
    public function store(ScheduleRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $schedule = Schedule::create($data);

        return response()->json([
            'message' => 'រក្សាទុកបានជោគជ័យ!',
            'data'    => new ScheduleResource($schedule)
        ], 210);
    }

    // ៣. បង្ហាញទិន្នន័យមួយ (Show)
    public function show(Schedule $schedule)
    {
        return new ScheduleResource($schedule);
    }

    // ៤. កែប្រែទិន្នន័យ (Update)
    public function update(ScheduleRequest $request, Schedule $schedule)
    {
        $schedule->update($request->validated());

        return response()->json([
            'message' => 'កែប្រែបានជោគជ័យ!',
            'data'    => new ScheduleResource($schedule)
        ]);
    }

    // ៥. លុបទិន្នន័យ (Destroy)
    public function destroy(Schedule $schedule)
    {
        $schedule->forceDelete(); 
        return response()->json(['message' => 'លុបទិន្នន័យរួចរាល់!']);
    }

}