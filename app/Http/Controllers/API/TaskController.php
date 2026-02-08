<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Schedule;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{
    // GET /api/tasks
    public function index(Request $request)
    {
        $query = Task::with('schedule')
            ->where(function($q){
                $q->whereHas('schedule', function($q2){
                    $q2->where('created_by', auth()->id());
                });
            });

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        return TaskResource::collection(
            $query->paginate(20)
        );
    }

    // POST /api/tasks
    public function store(TaskStoreRequest $request)
    {
        $data = $request->validated();

        // check if schedule belongs to user
        $schedule = Schedule::findOrFail($data['schedule_id']);
        $this->authorize('update', $schedule);

        $task = Task::create($data);

        return new TaskResource($task);
    }

    // GET /api/tasks/{task}
    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return new TaskResource($task->load('schedule'));
    }

    // PUT /api/tasks/{task}
    public function update(TaskUpdateRequest $request, Task $task)
    {
        $this->authorize('update', $task);
        $task->update($request->validated());
        return new TaskResource($task);
    }

    // DELETE /api/tasks/{task}
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        return response()->json(null, 204);
    }
}
