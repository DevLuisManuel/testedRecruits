<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Task;
use App\Traits\HasToastNotifications;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

/**
 * TaskManager Livewire Component
 *
 * Manages task creation, editing, deletion, and organization within projects.
 * Features include drag-and-drop reordering, project filtering, and inline editing.
 *
 */
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

    /**
     * Component initialization
     *
     * Loads all projects and tasks when the component is first mounted.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->loadProjects();
    }

    /**
     * Load all projects from database
     *
     * Retrieves projects ordered by name and triggers task loading.
     * Called when projects data needs to be refreshed.
     *
     * @return void
     */
    public function loadProjects(): void
    {
        $this->projects = Project::query()
            ->orderBy('name')
            ->get();
        $this->loadTasks();
    }

    /**
     * Load tasks with intelligent sorting
     *
     * Loads tasks with their associated projects. When filtered by project,
     * tasks are sorted by priority. When showing all projects, tasks are
     * grouped by project with unassigned tasks at the bottom.
     *
     * @return void
     */
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

    /**
     * Create a new task
     *
     * Validates input, calculates the next priority number for the selected
     * project (or unassigned tasks), creates the task, and provides user feedback.
     *
     * @return void
     */
    public function createTask(): void
    {
        $this->validate([
            'taskName' => 'required|string|max:255',
            'selectedProjectId' => 'nullable|exists:projects,id',
        ]);

        try {
            $maxPriority = $this->getMaxPriorityForProject($this->selectedProjectId);

            $task = Task::create([
                'name' => $this->taskName,
                'project_id' => $this->selectedProjectId,
                'priority' => $maxPriority + 1,
            ]);

            logger()->info('Task created successfully', ['task_id' => $task->id]);

            $this->reset(['taskName']);
            $this->loadTasks();

            $this->dispatchSuccessToast(__('Task created successfully.'));
        } catch (Exception $e) {
            logger()->error('Error creating task: ', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatchErrorToast(__('Error creating task: ' . $e->getMessage()));
        }
    }

    /**
     * Create a new project
     *
     * Validates the project name for uniqueness, creates the project,
     * automatically selects it for the next task, and closes the form.
     *
     * @return void
     */
    public function createProject(): void
    {
        $this->validate([
            'newProjectName' => 'required|string|max:255|unique:projects,name',
        ]);

        try {
            $project = Project::create([
                'name' => $this->newProjectName,
            ]);

            $this->selectedProjectId = $project->id;
            $this->reset(['newProjectName', 'showNewProjectForm']);
            $this->loadProjects();

            $this->dispatchSuccessToast(__('Project created successfully.'));
        } catch (Exception $e) {
            logger()->error('Error creating project: ', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatchErrorToast(__('Error creating project: ' . $e->getMessage()));
        }
    }

    /**
     * Initialize task editing mode
     *
     * Sets up the editing state by loading the task data into the editing
     * properties. The UI will switch to inline editing mode.
     *
     * @param int $taskId The ID of the task to edit
     * @return void
     */
    public function startEditingTask(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        $this->editingTaskId = $taskId;
        $this->editingTaskName = $task->name;
    }

    /**
     * Save task changes
     *
     * Validates the new task name, updates the database record,
     * exits editing mode, and refreshes the task list.
     *
     * @return void
     */
    public function updateTask(): void
    {
        $this->validate([
            'editingTaskName' => 'required|string|max:255',
        ]);

        try {
            $task = Task::findOrFail($this->editingTaskId);
            $task->update([
                'name' => $this->editingTaskName,
            ]);

            $this->reset(['editingTaskId', 'editingTaskName']);
            $this->loadTasks();

            $this->dispatchSuccessToast(__('Task updated successfully.'));
        } catch (Exception $e) {
            logger()->error('Error updating task: ', [
                'task_id' => $this->editingTaskId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatchErrorToast(__('Error updating task: ' . $e->getMessage()));
        }
    }


    /**
     * Cancel task editing
     *
     * Exits editing mode without saving changes by resetting
     * the editing state properties.
     *
     * @return void
     */
    public function cancelEditing(): void
    {
        $this->reset(['editingTaskId', 'editingTaskName']);
    }

    /**
     * Delete a task
     *
     * Removes the specified task from the database after confirmation.
     * Refreshes the task list and provides user feedback.
     *
     * @param int $taskId The ID of the task to delete
     * @return void
     */
    public function deleteTask(int $taskId): void
    {
        try {
            $task = Task::findOrFail($taskId);
            $task->delete();

            $this->loadTasks();
            $this->dispatchSuccessToast(__('Task deleted successfully.'));
        } catch (Exception $e) {
            logger()->error('Error deleting task: ', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatchErrorToast(__('Error deleting task: ' . $e->getMessage()));
        }
    }

    /**
     * Filter tasks by project
     *
     * Sets the active project filter and reloads tasks. When projectId
     * is null, shows all tasks grouped by project.
     *
     * @param int|null $projectId The project ID to filter by, or null for all
     * @return void
     */
    public function filterByProject(?int $projectId): void
    {
        $this->filterProjectId = $projectId;
        $this->loadTasks();
    }

    /**
     * Update task order via drag-and-drop
     *
     * Processes the reordered task IDs from the frontend drag-and-drop
     * functionality. Updates priority values atomically within a transaction
     * to maintain data consistency.
     *
     * @param int|null $projectId The project containing the reordered tasks
     * @param array $orderedIds Array of task IDs in their new order
     * @return void
     */
    public function updateTaskOrder(?int $projectId, array $orderedIds): void
    {
        try {
            DB::transaction(function () use ($projectId, $orderedIds) {
                foreach ($orderedIds as $index => $taskId) {
                    Task::where('id', $taskId)
                        ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                        ->when(is_null($projectId), fn($q) => $q->whereNull('project_id'))
                        ->update(['priority' => $index + 1]);
                }
            });

            $this->loadTasks();
            $this->dispatchSuccessToast(__('Task order updated successfully.'));
        } catch (Exception|Throwable $e) {
            logger()->error('Error updating task order: ', [
                'project_id' => $projectId,
                'ordered_ids' => $orderedIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatchErrorToast(__('Error updating task order: ' . $e->getMessage()));
        }
    }

    /**
     * Toggle new project form visibility
     *
     * Shows or hides the new project creation form. When hiding,
     * resets the project name input to clear any entered data.
     *
     * @return void
     */
    public function toggleNewProjectForm(): void
    {
        $this->showNewProjectForm = !$this->showNewProjectForm;
        if (!$this->showNewProjectForm) {
            $this->reset(['newProjectName']);
        }
    }

    /**
     * Get tasks grouped by project
     *
     * Computed property that returns tasks organized by project ID.
     * When filtering by a specific project, returns a single-item collection.
     * When showing all projects, groups tasks by their project_id.
     *
     * @return Collection<int|null, Collection<Task>>
     */
    #[Computed]
    public function groupedTasks(): Collection
    {
        return $this->filterProjectId
            ? collect([$this->filterProjectId => $this->tasks])
            : $this->tasks->groupBy(fn($t) => $t->project_id);
    }

    /**
     * Get the name of the currently filtered project
     *
     * Computed property that returns the project name when filtering
     * by a specific project, or null when showing all projects.
     *
     * @return string|null
     */
    #[Computed]
    public function filteredProjectName(): ?string
    {
        return $this->filterProjectId
            ? $this->projects->firstWhere('id', $this->filterProjectId)?->name
            : null;
    }

    /**
     * Get project name by ID
     *
     * Helper method to retrieve a project's name by its ID.
     * Returns "No Project" for null or non-existent project IDs.
     *
     * @param int|null $projectId The project ID to look up
     * @return string The project name or "No Project"
     */
    public function getProjectName(?int $projectId): string
    {
        return $this->projects->firstWhere('id', $projectId)?->name ?? 'No Project';
    }

    /**
     * Get the highest priority number for a project
     *
     * Calculates the maximum priority value within a specific project
     * or among unassigned tasks. Used to determine the priority for new tasks.
     *
     * @param int|null $projectId The project to check, or null for unassigned tasks
     * @return int The highest priority number (0 if no tasks exist)
     */
    private function getMaxPriorityForProject(?int $projectId): int
    {
        return Task::when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->when(is_null($projectId), fn($q) => $q->whereNull('project_id'))
            ->max('priority') ?? 0;
    }

    /**
     * Render the component view
     *
     * @return Factory|\Illuminate\Contracts\View\View|View
     */
    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.task-manager');
    }
}
