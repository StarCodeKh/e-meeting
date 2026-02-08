<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Http\Requests\ScheduleStoreRequest;
use App\Http\Requests\ScheduleUpdateRequest;
use App\Http\Resources\ScheduleResource;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    // GET /api/schedules
    public function index(Request $request)
    {
        $query = Schedule::with('tasks')
            ->where('created_by', auth()->id());

        if ($request->from) {
            $query->where('start_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->where('end_at', '<=', $request->to);
        }

        return ScheduleResource::collection(
            $query->paginate(20)
        );
    }

    // POST /api/schedules
    public function store(ScheduleStoreRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        $schedule = Schedule::create($data);

        return new ScheduleResource($schedule);
    }

    // GET /api/schedules/{schedule}
    public function show(Schedule $schedule)
    {
        $this->authorize('view', $schedule);

        return new ScheduleResource(
            $schedule->load('tasks')
        );
    }

    // PUT /api/schedules/{schedule}
    public function update(ScheduleUpdateRequest $request, Schedule $schedule)
    {
        $this->authorize('update', $schedule);

        $schedule->update($request->validated());

        return new ScheduleResource($schedule);
    }

    // DELETE /api/schedules/{schedule}
    public function destroy(Schedule $schedule)
    {
        $this->authorize('delete', $schedule);

        $schedule->delete();

        return response()->json(null, 204);
    }
}
