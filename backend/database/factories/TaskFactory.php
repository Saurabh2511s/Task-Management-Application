<?php

namespace Database\Factories;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Task::class;
    public function definition(): array
    {
        $statuses = ['pending', 'in_progress', 'completed'];

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->boolean(70) ? $this->faker->paragraph(2) : null,
            'status' => $this->faker->randomElement($statuses),
            'due_date' => $this->faker->boolean(60) ? $this->faker->dateTimeBetween('now', '+20 days')->format('Y-m-d') : null,
        ];
    }
}
