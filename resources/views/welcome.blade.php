<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet"/>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @fluxAppearance
</head>
<body class="bg-gray-50 min-h-screen dark:bg-gray-900">
<!-- Header -->
<flux:header
    class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-4">
    <!-- Left content -->
    <div class="flex items-center gap-4">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left"/>

        <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
                <div
                    class="w-10 h-10 bg-indigo-600 dark:bg-indigo-500 rounded-xl flex items-center justify-center shadow-sm">
                    <flux:icon.rectangle-stack class="w-4 h-4 text-white"/>
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Task Manager</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">Create your own task and projects</p>
            </div>
        </div>
    </div>

    <!-- Right Content-->
    <div class="flex items-center gap-4">
        <div class="hidden lg:block text-sm text-gray-500 dark:text-gray-400">
            Technical test done by:
            <a href="https://www.linkedin.com/in/devluism/" class="font-bold hover:underline" target="_blank">Luis
                Zuñiga
            </a>
        </div>

        <!-- Dark Mode Toggle -->
        <flux:button variant="ghost" size="sm"
                     x-data="{ dark: localStorage.getItem('dark') === 'true' }"
                     x-init="document.documentElement.classList.toggle('dark', dark)"
                     @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('dark', dark)">
            <flux:icon.sun class="w-5 h-5" x-show="!dark"/>
            <flux:icon.moon class="w-5 h-5" x-show="dark"/>
        </flux:button>
    </div>
</flux:header>

<!-- Main Content -->
<flux:main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <livewire:task-manager/>
</flux:main>

<!-- Footer Content -->
<flux:footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex justify-between items-center">
            <!-- Left -->
            <div class="text-sm text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} Developed by: <a href="https://www.linkedin.com/in/devluism/"
                                                        class="font-bold hover:underline" target="_blank">Luis Manuel
                    Zuñiga Moreno
                </a>
            </div>
            <!-- Right -->
            <div class="text-sm text-gray-400 dark:text-gray-500">
                Made with Laravel, Livewire & FluxUI
            </div>
        </div>
    </div>
</flux:footer>
@livewireScripts
@fluxScripts
</body>
</html>
