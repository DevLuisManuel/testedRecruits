<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Task;
use App\Traits\HasToastNotifications;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

class TaskManager extends Component
{
    use HasToastNotifications;

    #[Validate('required|string|max:255')]
    public string $taskName = '';
    #[Validate('nullable|exists:projects,id')]
    public ?int $selectedProjectId = null;
    #[Validate('required|string|max:255')]
    public string $newProjectName = '';
    public bool $showNewProjectForm = false;
    public ?int $editingTaskId = null;
    #[Validate('required|string|max:255')]
    public string $editingTaskName = '';
    public ?int $filterProjectId = null;
    public Collection $projects;
    public Collection $tasks;

    public function mount(): void
    {
        $this->loadProjects();
    }

    public function loadProjects(): void
    {
        $this->projects = Project::query()
            ->orderBy('name')
            ->get();
        $this->loadTasks();
    }

    public function loadTasks(): void
    {
        $query = Task::with('project');

        $this->tasks = $query->when($this->filterProjectId, function ($q) {
            return $q->where('project_id', $this->filterProjectId)
                ->orderBy('priority');
        }, fn($q) => $q->leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
            ->orderByRaw('CASE WHEN tasks.project_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('projects.name')
            ->orderBy('tasks.priority')
            ->select('tasks.*')
        )->get();
    }

    public function createTask(): void
    {
        try {
            $maxPriority = $this->getMaxPriorityProject($this->selectedProjectId);

            Task::query()->create([
                'name' => $this->taskName,
                'project_id' => $this->selectedProjectId,
                'priority' => $maxPriority + 1,
            ]);

            $this->reset(['taskName']);
            $this->loadTasks();

            $this->dispatchSuccess(__('Task created successfully.'));
        } catch (\Exception $e) {
            $this->dispatchError(__('Error creating task.'));
            logger()->error('Error creating task: ', [
                'error' => $e->getTraceAsString(),
            ]);
        }
    }

    public function createProject(): void
    {
        $this->validate([
            'newProjectName' => 'required|string|max:255|unique:projects,name',
        ]);

        try {
            $project = Project::query()->create([
                'name' => $this->newProjectName,
            ]);

            $this->selectedProjectId = $project->id;
            $this->reset(['newProjectName', 'showNewProjectForm']);
            $this->loadProjects();

            $this->dispatchSuccess(__('Project created successfully.'));
        } catch (\Exception $e) {
            $this->dispatchError(__('Error creating project.'));
            logger()->error('Error creating project: ', [
                'error' => $e->getTraceAsString(),
            ]);;
        }
    }

    public function startEditingTask(int $taskId): void
    {
        $task = Task::query()->findOrFail($taskId);
        $this->editingTaskId = $taskId;
        $this->editingTaskName = $task->name;
    }

    public function updateTask(): void
    {
        $this->validate([
            'editingTaskName' => 'required|string|max:255',
        ]);

        try {
            $task = Task::query()->findOrFail($this->editingTaskId);
            $task->update([
                'name' => $this->editingTaskName,
            ]);

            $this->reset(['editingTaskId', 'editingTaskName']);
            $this->loadTasks();

            $this->dispatchSuccess(__('Task updated successfully.'));
        } catch (\Exception $e) {
            $this->dispatchError(__('Error updating task.'));
            logger()->error('Error updating task: ', [
                'task_id' => $this->editingTaskId,
                'error' => $e->getTraceAsString(),
            ]);
        }
    }

    public function cancelEditing(): void
    {
        $this->reset(['editingTaskId', 'editingTaskName']);
    }

    public function deleteTask(int $taskId): void
    {
        try {
            $task = Task::query()->findOrFail($taskId);
            $task->delete();

            $this->loadTasks();
            $this->dispatchSuccess(__('Task deleted successfully.'));
        } catch (\Exception $e) {
            $this->dispatchError(__('Error deleting task.'));
            logger()->error('Error deleting task: ', [
                'task_id' => $taskId,
                'error' => $e->getTraceAsString(),
            ]);
        }
    }

    public function filterByProject(?int $projectId): void
    {
        $this->filterProjectId = $projectId;
        $this->loadTasks();
    }

    /**
     * Update task order within a project
     *
     * @param int|null $projectId
     * @param array $orderedIds
     * @return void
     * @throws Throwable
     */
    public function updateTaskOrder(?int $projectId, array $orderedIds): void
    {
        try {
            DB::transaction(function () use ($projectId, $orderedIds) {
                foreach ($orderedIds as $index => $taskId) {
                    Task::query()->where('id', $taskId)
                        ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                        ->when(is_null($projectId), fn($q) => $q->whereNull('project_id'))
                        ->update(['priority' => $index + 1]);
                }
            });

            $this->loadTasks();
            $this->dispatchSuccess(__('Task order updated successfully.'));
        } catch (\Exception $e) {
            $this->dispatchError(__('Error updating task order.'));
            logger()->error('Error updating task order: ', [
                'project_id' => $projectId,
                'ordered_ids' => $orderedIds,
                'error' => $e->getTraceAsString(),
            ]);;
        }
    }

    public function toggleNewProjectForm(): void
    {
        $this->showNewProjectForm = !$this->showNewProjectForm;
        if (!$this->showNewProjectForm) {
            $this->reset(['newProjectName']);
        }
    }

    /**
     * Get maximum priority for a given project
     *
     * @param int|null $projectId
     * @return int
     */
    private function getMaxPriorityProject(?int $projectId): int
    {
        return Task::query()->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->when(is_null($projectId), fn($q) => $q->whereNull('project_id'))
            ->max('priority') ?? 0;
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.task-manager');
    }
}
