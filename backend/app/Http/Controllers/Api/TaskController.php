<?php

namespace App\Http\Controllers\Api;
use App\Services\TaskService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private TaskService $taskService;
    public function __construct(TaskService $taskService) {
        $this->taskService = $taskService;
    }

    public function index(Request $request)
    {
        $tasks = $this->taskService->paginate($request->only('status'), 10);
        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->create($request->validated());

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Task $task): TaskResource
    {
        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $task = $this->taskService->update($task, $request->validated());
        return new TaskResource($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->delete($task);
        return response()->json(null, 204);
    }
}
