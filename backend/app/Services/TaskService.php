<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = Task::query()->latest();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Task
    {
        $data['status'] = $data['status'] ?? 'pending';
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
