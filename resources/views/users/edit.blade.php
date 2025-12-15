<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit User Role') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h3>
                        <p class="text-gray-600 mt-1">{{ $user->email }}</p>
                        <p class="text-sm text-gray-500 mt-2">Joined {{ $user->created_at->format('M d, Y') }}</p>
                    </div>

                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">User Role</label>
                            <select id="role" name="role" required class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="viewer" {{ $user->role === 'viewer' ? 'selected' : '' }}>
                                    Viewer - Read-only access to assigned projects
                                </option>
                                <option value="manager" {{ $user->role === 'manager' ? 'selected' : '' }}>
                                    Manager - Can create and manage projects
                                </option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>
                                    Admin - Full system access and user management
                                </option>
                            </select>
                            @error('role')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                            <h4 class="font-semibold text-blue-900 mb-2">Role Permissions:</h4>
                            <div id="permissions" class="text-sm text-blue-800 space-y-1">
                                <div>
                                    <strong>Viewer:</strong> View only assigned projects and update milestone status
                                </div>
                                <div>
                                    <strong>Manager:</strong> Create/edit/delete own projects, assign team members
                                </div>
                                <div>
                                    <strong>Admin:</strong> Full access to all projects, manage all users
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" style="background-color: #2563eb !important; color: white !important;" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                                Update Role
                            </button>
                            <a href="{{ route('users.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
