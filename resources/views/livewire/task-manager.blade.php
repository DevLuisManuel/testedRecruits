<div class="max-w-6xl mx-auto space-y-8">
    <!-- Page Header -->
    <div class="space-y-2">
        <flux:heading size="xl">
            Task Manager
        </flux:heading>
        <flux:subheading>
            Create, organize, and manage your tasks.
        </flux:subheading>
    </div>

    <!-- Project Filter & New Task Form -->
    <div
        class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 shadow-sm p-6 space-y-6">
        <!-- Project Filter -->
        <div class="space-y-3">
            <flux:label>Filter by Project</flux:label>
            <div class="flex flex-wrap gap-2">
                <flux:button
                    wire:click="filterByProject(null)"
                    variant="{{ is_null($filterProjectId) ? 'primary' : 'outline' }}"
                    size="sm">
                    All Projects
                </flux:button>
                @foreach($projects as $project)
                    <flux:button
                        wire:click="filterByProject({{ $project->id }})"
                        variant="{{ $filterProjectId == $project->id ? 'primary' : 'outline' }}"
                        size="sm">
                        {{ $project->name }}
                    </flux:button>
                @endforeach
            </div>
        </div>

        <flux:separator/>

        <!-- New Task Form -->
        <div class="space-y-4">
            <flux:heading size="lg">Create New Task</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Task Name -->
                <flux:field>
                    <flux:label>Task Name</flux:label>
                    <flux:input
                        wire:model="taskName"
                        placeholder="Enter task name..."
                        :error="$errors->first('taskName')"/>
                    <flux:error name="taskName"/>
                </flux:field>

                <!-- Project Selection -->
                <flux:field>
                    <flux:label>Project</flux:label>
                    <div class="flex gap-2">
                        <flux:select
                            wire:model="selectedProjectId"
                            placeholder="Select project..."
                            :error="$errors->first('selectedProjectId')"
                            class="flex-1">
                            <flux:select.option value="">No Project</flux:select.option>
                            @foreach($projects as $project)
                                <flux:select.option value="{{ $project->id }}">{{ $project->name }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:button
                            wire:click="toggleNewProjectForm"
                            variant="outline"
                            size="sm"
                            icon="plus"/>
                    </div>
                    <flux:error name="selectedProjectId"/>
                </flux:field>
            </div>

            <!-- New Project Form -->
            @if($showNewProjectForm)
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-transparent p-4">
                    <flux:heading size="md" class="mb-3">Create New Project</flux:heading>
                    <div class="flex gap-2">
                        <flux:input
                            wire:model="newProjectName"
                            placeholder="Project name..."
                            :error="$errors->first('newProjectName')"
                            class="flex-1"/>
                        <flux:button
                            wire:click="createProject"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50"
                            variant="primary"
                            size="sm">
                            <span wire:loading.remove wire:target="createProject">Create</span>
                            <span wire:loading wire:target="createProject">Creating...</span>
                        </flux:button>
                        <flux:button
                            wire:click="toggleNewProjectForm"
                            variant="outline"
                            size="sm">
                            Cancel
                        </flux:button>
                    </div>
                    <flux:error name="newProjectName"/>
                </div>
            @endif

            <!-- Create Task Button -->
            <div>
                <flux:button
                    wire:click="createTask"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50"
                    variant="primary"
                    icon="plus">
                    <span wire:loading.remove wire:target="createTask">Create Task</span>
                    <span wire:loading wire:target="createTask">Creating...</span>
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div
        class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">
                Tasks
                @if($filterProjectId && $this->filteredProjectName)
                    - {{ $this->filteredProjectName }}
                @endif
            </flux:heading>
            <flux:badge variant="outline">{{ $tasks->count() }} tasks</flux:badge>
        </div>

        @if($tasks->isEmpty())
            <div class="text-center py-12">
                <div
                    class="mx-auto w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                    <flux:icon.clipboard-document-list class="w-8 h-8 text-gray-400 dark:text-gray-600"/>
                </div>
                <flux:heading size="sm" class="mb-1">No tasks</flux:heading>
                <flux:subheading>Get started by creating a new task above.</flux:subheading>
            </div>
        @else
            <div class="space-y-8">
                @foreach($this->groupedTasks as $groupProjectId => $groupTasks)
                    @if(is_null($filterProjectId))
                        <div class="flex items-center justify-between">
                            <flux:heading size="md">
                                {{ $this->getProjectName((int)$groupProjectId) }}
                            </flux:heading>
                            <flux:badge variant="outline">{{ $groupTasks->count() }} tasks</flux:badge>
                        </div>
                    @endif

                    <div
                        class="space-y-3"
                        data-sortable-project="{{ $groupProjectId ?? 'null' }}"
                        wire:loading.class="opacity-50"
                        wire:target="updateTaskOrder">
                        @foreach($groupTasks as $task)
                            <div
                                data-id="{{ $task->id }}"
                                class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-transparent p-0 task-item hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between p-4">
                                    <!-- Task Content -->
                                    <div class="flex items-center flex-1 min-w-0 gap-4">
                                        <flux:icon.bars-3
                                            class="w-5 h-5 text-gray-400 hover:text-gray-600 dark:text-gray-600 dark:hover:text-gray-400 cursor-grab active:cursor-grabbing"/>
                                        <flux:badge variant="primary" size="sm">
                                            #{{ $task->priority }}
                                        </flux:badge>
                                        <div class="flex-1 min-w-0">
                                            @if($editingTaskId === $task->id)
                                                <div class="flex items-center gap-2">
                                                    <flux:input
                                                        wire:model="editingTaskName"
                                                        wire:keydown.enter="updateTask"
                                                        wire:keydown.escape="cancelEditing"
                                                        :error="$errors->first('editingTaskName')"
                                                        class="flex-1"
                                                        autofocus/>
                                                    <flux:button
                                                        wire:click="updateTask"
                                                        wire:loading.attr="disabled"
                                                        wire:loading.class="opacity-50"
                                                        variant="primary"
                                                        size="sm">
                                                        <span wire:loading.remove wire:target="updateTask">Save</span>
                                                        <span wire:loading wire:target="updateTask">Saving...</span>
                                                    </flux:button>
                                                    <flux:button wire:click="cancelEditing" variant="outline" size="sm">
                                                        Cancel
                                                    </flux:button>
                                                </div>
                                                <flux:error name="editingTaskName"/>
                                            @else
                                                <div class="space-y-1">
                                                    <flux:heading size="sm">{{ $task->name }}</flux:heading>
                                                    @if($task->project && is_null($filterProjectId))
                                                        <div class="flex items-center gap-1">
                                                            <flux:icon.folder class="w-4 h-4 text-gray-400"/>
                                                            <flux:subheading
                                                                size="sm">{{ $task->project->name }}</flux:subheading>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    @if($editingTaskId !== $task->id)
                                        <div class="flex items-center gap-2">
                                            <flux:button
                                                wire:click="startEditingTask({{ $task->id }})"
                                                variant="ghost"
                                                size="sm"
                                                icon="pencil"
                                                title="Edit task"/>
                                            <flux:button
                                                wire:click="deleteTask({{ $task->id }})"
                                                wire:confirm="Are you sure you want to delete this task?"
                                                wire:loading.attr="disabled"
                                                wire:loading.class="opacity-50"
                                                variant="ghost"
                                                size="sm"
                                                icon="trash"
                                                title="Delete task"/>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Instructions -->
    <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-900/40 border border-zinc-200/60 dark:border-zinc-800/60">
        <div class="p-4 space-y-2">
            <flux:heading size="sm">How to use:</flux:heading>
            <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex items-center gap-2">
                    <flux:icon.cursor-arrow-rays class="w-4 h-4"/>
                    <span>Drag and drop tasks to reorder them - priority numbers update automatically</span>
                </div>
                <div class="flex items-center gap-2">
                    <flux:icon.pencil class="w-4 h-4"/>
                    <span>Click the edit icon to rename tasks inline</span>
                </div>
                <div class="flex items-center gap-2">
                    <flux:icon.funnel class="w-4 h-4"/>
                    <span>Filter tasks by project using the filter buttons</span>
                </div>
                <div class="flex items-center gap-2">
                    <flux:icon.plus class="w-4 h-4"/>
                    <span>Create new projects on the fly when adding tasks</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Toast Handler
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('toast', (event) => {
            showToast(event.message, event.type);
        });
    });

    function showToast(message, type = 'success') {
        // remove toasts if they exist
        document.querySelectorAll('.toast-notification').forEach(el => el.remove());

        const toast = document.createElement('div');
        toast.className = `toast-notification fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-medium z-50 transform transition-all duration-300 translate-x-full ${
            type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                    type === 'warning' ? 'bg-yellow-500' :
                        'bg-blue-500'
        }`;
        toast.textContent = message;

        document.body.appendChild(toast);
        setTimeout(() => toast.classList.remove('translate-x-full'), 10);
        // remove over 3 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);

        // add click event to close the toast
        toast.addEventListener('click', () => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        });
    }
</script>
