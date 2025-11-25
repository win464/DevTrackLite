<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $project->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold">{{ $project->title }}</h1>
        <p class="text-gray-600 mt-2">{{ $project->description }}</p>
    </div>
    @if (auth()->user()->id === $project->owner_id || auth()->user()->role === 'admin')
        <div class="flex gap-2">
            <a href="{{ route('projects.edit', $project) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Edit
            </a>
            <form method="POST" action="{{ route('projects.destroy', $project) }}" class="inline" onsubmit="return confirm('Delete this project?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Delete</button>
            </form>
        </div>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-semibold text-gray-600 mb-2">Progress</h3>
        <p class="text-3xl font-bold text-blue-600">{{ $project->progress ?? 0 }}%</p>
        <div class="w-full bg-gray-200 rounded-full h-2 mt-4">
            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $project->progress ?? 0 }}%"></div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-semibold text-gray-600 mb-2">Status</h3>
        <span class="text-lg font-bold inline-block px-3 py-1 rounded-full
            @if ($project->status === 'active') bg-green-100 text-green-800
            @elseif ($project->status === 'closed') bg-gray-100 text-gray-800
            @else bg-yellow-100 text-yellow-800
            @endif">
            {{ ucfirst($project->status ?? 'pending') }}
        </span>
        @if ($project->overdue)
            <span class="block mt-2 px-2 py-1 text-sm font-semibold bg-red-100 text-red-800 rounded">⚠️ Overdue</span>
        @endif
        @if ($project->over_budget)
            <span class="block mt-2 px-2 py-1 text-sm font-semibold bg-orange-100 text-orange-800 rounded">💰 Over Budget</span>
        @endif
    </div>

    @if ($project->budget)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">Budget</h3>
            <p class="text-3xl font-bold text-gray-900">${{ number_format($project->budget, 2) }}</p>
            <p class="text-sm text-gray-600 mt-2">Spent: ${{ number_format($project->milestones->sum('spent') ?? 0, 2) }}</p>
        </div>
    @endif
</div>

<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">Milestones</h2>
        @if (auth()->user()->id === $project->owner_id || auth()->user()->role === 'admin')
            <button onclick="showMilestoneForm()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                + Add Milestone
            </button>
        @endif
    </div>

    @if ($project->milestones->isEmpty())
        <p class="text-gray-500">No milestones yet.</p>
    @else
        <div class="space-y-4">
            @foreach ($project->milestones as $milestone)
                <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-bold text-gray-900">{{ $milestone->title }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ $milestone->description }}</p>
                            <div class="mt-2 flex gap-4 text-sm text-gray-600">
                                @if ($milestone->deadline)
                                    <span>📅 {{ $milestone->deadline->format('M d, Y') }}</span>
                                @endif
                                @if ($milestone->budget)
                                    <span>💵 ${{ number_format($milestone->budget, 2) }}</span>
                                @endif
                                <span>
                                    @if ($milestone->status === 'completed')
                                        ✅ Completed
                                    @elseif ($milestone->status === 'in_progress')
                                        ⏳ In Progress
                                    @else
                                        ⭕ Pending
                                    @endif
                                </span>
                            </div>
                        </div>
                        @if (auth()->user()->id === $project->owner_id || auth()->user()->role === 'admin')
                            <div class="flex gap-2">
                                <button onclick="editMilestone({{ $milestone->id }})" class="text-blue-600 hover:text-blue-900 text-sm">Edit</button>
                                <form method="POST" action="/milestones/{{ $milestone->id }}" class="inline" onsubmit="return confirm('Delete milestone?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="mt-8">
    <a href="{{ route('projects.index') }}" class="text-blue-600 hover:text-blue-900">← Back to Projects</a>
</div>

@if (auth()->user()->id === $project->owner_id || auth()->user()->role === 'admin')
    <script>
        function showMilestoneForm() {
            alert('Create milestone modal would open here.');
        }
        
        function editMilestone(id) {
            alert('Edit milestone ' + id + ' modal would open here.');
        }
    </script>
@endif
        </div>
    </div>
</x-app-layout>
