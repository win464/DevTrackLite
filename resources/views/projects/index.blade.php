<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Projects') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">My Projects</h2>
                    <p class="text-sm text-gray-600 mt-1">Manage your projects and track progress</p>
                </div>
                <a href="{{ route('projects.create') }}" style="background-color: #2563eb !important; color: white !important;" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    + New Project
                </a>
            </div>

@if ($projects->isEmpty())
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <p class="text-gray-500">No projects yet. <a href="{{ route('projects.create') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Create your first project</a> to get started.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($projects as $project)
            <a href="{{ route('projects.show', $project) }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $project->title }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $project->description ?? 'No description' }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if ($project->status === 'active') bg-green-100 text-green-800
                        @elseif ($project->status === 'closed') bg-gray-100 text-gray-800
                        @else bg-yellow-100 text-yellow-800
                        @endif">
                        {{ ucfirst($project->status ?? 'pending') }}
                    </span>
                </div>

                <div class="mb-4">
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-xs font-medium text-gray-500">Progress</span>
                        <span class="text-xs font-semibold text-indigo-600">{{ $project->progress ?? 0 }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                        <div class="bg-indigo-600 h-1.5 rounded-full transition-all" style="width: {{ $project->progress ?? 0 }}%"></div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5 min-h-[24px]">
                    @if ($project->overdue)
                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800 rounded">⚠️ Overdue</span>
                    @endif
                    @if ($project->over_budget)
                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-orange-100 text-orange-800 rounded">💰 Over Budget</span>
                    @endif
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 text-xs text-gray-500 flex justify-between">
                    <span class="font-medium">{{ $project->milestones_count ?? 0 }} milestone{{ $project->milestones_count !== 1 ? 's' : '' }}</span>
                    <span>{{ $project->created_at->format('M d, Y') }}</span>
                </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $projects->links() }}
    </div>
@endif
        </div>
    </div>
</x-app-layout>
