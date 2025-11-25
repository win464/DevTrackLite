<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Projects') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold">My Projects</h1>
                <a href="{{ route('projects.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + New Project
                </a>
            </div>

@if ($projects->isEmpty())
    <p class="text-gray-500">No projects yet. <a href="{{ route('projects.create') }}" class="text-blue-600 hover:underline">Create one</a></p>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($projects as $project)
            <a href="{{ route('projects.show', $project) }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $project->title }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $project->description ?? 'No description' }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                        @if ($project->status === 'active') bg-green-100 text-green-800
                        @elseif ($project->status === 'closed') bg-gray-100 text-gray-800
                        @else bg-yellow-100 text-yellow-800
                        @endif">
                        {{ ucfirst($project->status ?? 'pending') }}
                    </span>
                </div>

                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-gray-700">Progress</span>
                        <span class="text-sm font-bold text-blue-600">{{ $project->progress ?? 0 }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $project->progress ?? 0 }}%"></div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if ($project->overdue)
                        <span class="inline-block px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded">⚠️ Overdue</span>
                    @endif
                    @if ($project->over_budget)
                        <span class="inline-block px-2 py-1 text-xs font-semibold bg-orange-100 text-orange-800 rounded">💰 Over Budget</span>
                    @endif
                </div>

                <div class="mt-4 text-sm text-gray-500 flex justify-between">
                    <span>{{ $project->milestones_count ?? 0 }} milestones</span>
                    <span class="text-xs">{{ $project->created_at->format('M d, Y') }}</span>
                </div>
            </a>
        @endforeach
    </div>

    {{ $projects->links() }}
@endif
        </div>
    </div>
</x-app-layout>
