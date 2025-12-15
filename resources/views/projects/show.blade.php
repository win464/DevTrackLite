<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $project->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
@if (session('success'))
    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900">{{ $project->title }}</h1>
                <p class="text-gray-600 mt-2">{{ $project->description }}</p>
            </div>
            @can('update', $project)
                <div class="flex gap-3 ml-4 items-center">
                    <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        Edit Project
                    </a>
                    @can('delete', $project)
                        <form method="POST" action="{{ route('projects.destroy', $project) }}" class="inline" onsubmit="return confirm('Delete this project and all its milestones?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition">Delete</button>
                        </form>
                    @endcan
                </div>
            @endcan
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-sm font-semibold text-gray-600 mb-2">Progress</h3>
        <p class="text-3xl font-bold text-blue-600">{{ $project->progress ?? 0 }}%</p>
        <div class="w-full bg-gray-200 rounded-full h-2 mt-4">
            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $project->progress ?? 0 }}%"></div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
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
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">Budget</h3>
            <p class="text-3xl font-bold text-gray-900">GHS {{ number_format($project->budget, 2) }}</p>
            <p class="text-sm text-gray-600 mt-2">Spent: GHS {{ number_format($project->milestones->sum('spent') ?? 0, 2) }}</p>
        </div>
    @endif
</div>

<!-- Team Members Section -->
@if ($project->teamMembers->count() > 0)
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
    <h3 class="text-sm font-semibold text-gray-600 mb-3">Team Members ({{ $project->teamMembers->count() }})</h3>
    <div class="flex flex-wrap gap-2">
        @foreach($project->teamMembers as $member)
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 border border-indigo-200 rounded-full">
                <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-semibold">
                    {{ strtoupper(substr($member->name, 0, 1)) }}
                </div>
                <span class="text-sm font-medium text-indigo-900">{{ $member->name }}</span>
                @if($member->pivot->role)
                    <span class="text-xs text-indigo-600">({{ ucfirst($member->pivot->role) }})</span>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif

@if ($project->start_date || $project->end_date)
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
    <h3 class="text-sm font-semibold text-gray-600 mb-3">Project Timeline</h3>
    <div class="flex flex-col gap-4">
        @if ($project->start_date)
            <div class="flex flex-col gap-1">
                <p class="text-xs text-gray-500">Start Date</p>
                <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($project->start_date)->format('M d, Y') }}</p>
            </div>
        @endif
        @if ($project->end_date)
            <div class="flex flex-col gap-1">
                <p class="text-xs text-gray-500">End Date</p>
                <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($project->end_date)->format('M d, Y') }}</p>
            </div>
        @endif
    </div>
</div>
@endif

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900">Milestones</h2>
            @can('update', $project)
                <button type="button" onclick="showMilestoneForm()" style="background-color: #4f46e5 !important; color: white !important;" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Milestone
                </button>
            @endcan
        </div>
    </div>

    <div class="p-6">
    @if ($project->milestones->isEmpty())
        <p class="text-gray-500 text-center py-8">No milestones yet. Click "Add Milestone" to get started.</p>
    @else
        <div class="space-y-3">
            @foreach ($project->milestones as $milestone)
                <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 hover:shadow-sm transition">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 pr-4">
                            <div class="flex items-center justify-between">
                                <h4 class="font-semibold text-gray-900 text-lg">{{ $milestone->title }}</h4>
                                <span class="text-sm font-semibold px-2 py-1 rounded-full
                                    @if ($milestone->status === 'completed') bg-green-100 text-green-800
                                    @elseif ($milestone->status === 'in_progress') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if ($milestone->status === 'completed')
                                        Completed
                                    @elseif ($milestone->status === 'in_progress')
                                        In Progress
                                    @else
                                        Pending
                                    @endif
                                </span>
                            </div>

                            @if ($milestone->description)
                                <p class="text-sm text-gray-600 mt-2">{{ $milestone->description }}</p>
                            @endif

                            <div class="mt-3 flex gap-4 text-sm text-gray-600">
                                @if ($milestone->deadline)
                                    <span class="flex items-center gap-1">📅 <span>{{ $milestone->deadline->format('M d, Y') }}</span></span>
                                @endif
                                @if ($milestone->budget)
                                    <span class="flex items-center gap-1">💵 <span>GHS {{ number_format($milestone->budget, 2) }}</span></span>
                                @endif
                                <span class="flex items-center gap-1">💸 <span>GHS {{ number_format($milestone->spent ?? 0, 2) }}</span></span>
                            </div>

                            @if ($milestone->assignedUsers->count() > 0)
                                <div class="mt-3 flex items-center gap-2">
                                    <span class="text-xs text-gray-500">Assigned to:</span>
                                    <div class="flex -space-x-2">
                                        @foreach($milestone->assignedUsers->take(3) as $user)
                                            <div class="w-7 h-7 rounded-full bg-indigo-600 border-2 border-white flex items-center justify-center text-white text-xs font-semibold" title="{{ $user->name }}">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endforeach
                                        @if($milestone->assignedUsers->count() > 3)
                                            <div class="w-7 h-7 rounded-full bg-gray-400 border-2 border-white flex items-center justify-center text-white text-xs font-semibold">
                                                +{{ $milestone->assignedUsers->count() - 3 }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex-shrink-0 flex items-center gap-2">
                            @can('update', $milestone)
                                <button type="button" onclick="editMilestone({{ $milestone->id }}, {{ Js::from([
                                    'title' => $milestone->title,
                                    'description' => $milestone->description,
                                    'deadline' => $milestone->deadline?->format('Y-m-d'),
                                    'status' => $milestone->status,
                                    'budget' => $milestone->budget,
                                    'spent' => $milestone->spent,
                                    'assigned_users' => $milestone->assignedUsers->pluck('id')->toArray()
                                ]) }})" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md text-sm font-semibold text-indigo-600 hover:bg-indigo-50 transition">Edit</button>
                            @elsecan('updateStatus', $milestone)
                                <button type="button" onclick="showStatusUpdateForm({{ $milestone->id }}, '{{ $milestone->status }}')" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md text-sm font-semibold text-white hover:bg-indigo-700 transition">Update Status</button>
                            @endcan

                            @can('delete', $milestone)
                                <form method="POST" action="{{ route('projects.milestones.destroy', [$project, $milestone]) }}" class="inline" onsubmit="return confirm('Delete this milestone?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded-md text-sm font-semibold hover:bg-red-700 transition">Delete</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    </div>
</div>

<div class="mt-6">
    <a href="{{ route('projects.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Projects
    </a>
</div>

<!-- Milestone Modal -->
<div id="milestoneModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto w-11/12 max-w-lg">
        <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 id="modalTitle" class="text-lg font-bold text-white">Add Milestone</h3>
                    <button type="button" onclick="closeMilestoneModal()" class="text-indigo-200 hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body -->
            <form id="milestoneForm" method="POST" action="{{ route('projects.milestones.store', $project) }}" class="p-6">
                @csrf
                <input type="hidden" id="milestoneId" name="milestone_id" value="">
                <input type="hidden" id="formMethod" name="_method" value="">
                
                <div class="mb-5">
                <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Title *</label>
                <input type="text" id="title" name="title" required 
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
            </div>
            
            <div class="mb-5">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="3"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition resize-none"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label for="deadline" class="block text-sm font-semibold text-gray-700 mb-2">Deadline</label>
                    <input type="date" id="deadline" name="deadline"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                    <select id="status" name="status" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Assign To</label>
                <div class="border border-gray-300 rounded-lg p-3 max-h-40 overflow-y-auto bg-gray-50">
                    @if($project->teamMembers->count() > 0)
                        @foreach($project->teamMembers as $member)
                            <label class="flex items-center py-1 hover:bg-white px-2 rounded cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="assigned_users[]" 
                                    value="{{ $member->id }}"
                                    class="milestone-assign-checkbox mr-2 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span class="text-sm text-gray-700">{{ $member->name }}</span>
                            </label>
                        @endforeach
                    @else
                        <p class="text-sm text-gray-500">No team members assigned to this project</p>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-1">Select team members to work on this milestone</p>
            </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="budget" class="block text-sm font-semibold text-gray-700 mb-2">Budget (GHS)</label>
                    <input type="number" id="budget" name="budget" step="0.01" min="0" placeholder="0.00"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                </div>
                
                <div>
                    <label for="spent" class="block text-sm font-semibold text-gray-700 mb-2">Spent ($)</label>
                    <input type="number" id="spent" name="spent" step="0.01" min="0" placeholder="0.00"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeMilestoneModal()" 
                    class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 transition">
                    Cancel
                </button>
                <button type="submit" 
                    style="background-color: #4f46e5 !important; color: white !important;"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    Save Milestone
                </button>
            </div>
        </form>
        </div>
    </div>
</div>

@if (auth()->user()->id === $project->owner_id || auth()->user()->role === 'admin')
    <script>
        const modal = document.getElementById('milestoneModal');
        const form = document.getElementById('milestoneForm');
        const modalTitle = document.getElementById('modalTitle');
        
        function showMilestoneForm() {
            // Reset form for create
            form.reset();
            form.action = "{{ route('projects.milestones.store', $project) }}";
            document.getElementById('formMethod').value = '';
            document.getElementById('milestoneId').value = '';
            modalTitle.textContent = 'Add Milestone';
            
            // Uncheck all assigned users
            document.querySelectorAll('.milestone-assign-checkbox').forEach(cb => cb.checked = false);
            
            modal.classList.remove('hidden');
        }
        
        function editMilestone(id, data) {
            // Populate form for edit
            modalTitle.textContent = 'Edit Milestone';
            form.action = `/projects/{{ $project->id }}/milestones/${id}`;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('milestoneId').value = id;
            document.getElementById('title').value = data.title;
            document.getElementById('description').value = data.description || '';
            document.getElementById('deadline').value = data.deadline || '';
            document.getElementById('status').value = data.status;
            document.getElementById('budget').value = data.budget || '';
            document.getElementById('spent').value = data.spent || '';
            
            // Handle assigned users
            document.querySelectorAll('.milestone-assign-checkbox').forEach(cb => {
                cb.checked = data.assigned_users && data.assigned_users.includes(parseInt(cb.value));
            });
            
            modal.classList.remove('hidden');
        }
        
        function closeMilestoneModal() {
            modal.classList.add('hidden');
        }
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeMilestoneModal();
            }
        });
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeMilestoneModal();
            }
        });
    </script>
@endif

<script>
    // These functions must be outside the conditional to be available to all users
    function showStatusUpdateForm(id, currentStatus) {
        // Create a simple status update form in an overlay
        const modal = document.createElement('div');
        modal.id = 'statusModal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        
        modal.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 mx-4">
                <h3 class="text-lg font-semibold mb-4">Update Milestone Status</h3>
                <form id="statusForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="statusSelect" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="pending" ${currentStatus === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="in_progress" ${currentStatus === 'in_progress' ? 'selected' : ''}>In Progress</option>
                            <option value="completed" ${currentStatus === 'completed' ? 'selected' : ''}>Completed</option>
                        </select>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300" onclick="document.getElementById('statusModal').remove()">Cancel</button>
                        <button type="button" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700" onclick="submitStatusUpdate(${id})">Update</button>
                    </div>
                </form>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close modal on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }
    
    function submitStatusUpdate(milestoneId) {
        const status = document.getElementById('statusSelect').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch(`/projects/{{ $project->id }}/milestones/${milestoneId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: status
            })
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Error updating status. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating status: ' + error);
        });
    }
</script>
        </div>
    </div>
</x-app-layout>
