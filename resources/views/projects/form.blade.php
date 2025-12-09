<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($project) ? 'Edit Project' : 'Create Project' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
<div class="max-w-2xl">
    <h1 class="text-3xl font-bold mb-8">{{ isset($project) ? 'Edit Project' : 'Create Project' }}</h1>

    <form method="POST" action="{{ isset($project) ? route('projects.update', $project) : route('projects.store') }}" class="bg-white rounded-lg shadow p-8">
        @csrf
        @if (isset($project))
            @method('PUT')
        @endif

        <div class="mb-6">
            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Title</label>
            <input
                type="text"
                id="title"
                name="title"
                value="{{ old('title', $project->title ?? '') }}"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-600"
            />
        </div>

        <div class="mb-6">
            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
            <textarea
                id="description"
                name="description"
                rows="4"
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-600"
            >{{ old('description', $project->description ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select
                    id="status"
                    name="status"
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-600"
                >
                    <option value="">Select Status</option>
                    <option value="pending" {{ old('status', $project->status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ old('status', $project->status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="closed" {{ old('status', $project->status ?? '') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <div>
                <label for="budget" class="block text-sm font-semibold text-gray-700 mb-2">Budget (GHS)</label>
                <input
                    type="number"
                    id="budget"
                    name="budget"
                    step="0.01"
                    min="0"
                    value="{{ old('budget', $project->budget ?? '') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-600"
                    placeholder="0.00"
                />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">Start Date</label>
                <input
                    type="date"
                    id="start_date"
                    name="start_date"
                    value="{{ old('start_date', $project->start_date ?? '') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-600"
                />
            </div>

            <div>
                <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-2">End Date</label>
                <input
                    type="date"
                    id="end_date"
                    name="end_date"
                    value="{{ old('end_date', $project->end_date ?? '') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-600"
                />
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" style="background-color: #2563eb !important; color: white !important;" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-semibold">
                {{ isset($project) ? 'Update' : 'Create' }} Project
            </button>
            <a href="{{ isset($project) ? route('projects.show', $project) : route('projects.index') }}" class="text-gray-600 hover:text-gray-900 px-6 py-2">
                Cancel
            </a>
        </div>
    </form>
</div>
        </div>
    </div>
</x-app-layout>
