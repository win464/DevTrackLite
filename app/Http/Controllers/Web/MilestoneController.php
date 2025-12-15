<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Milestone;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{
    public function store(Request $request, Project $project)
    {
        // Authorize: only owner or admin can add milestones
        if (auth()->user()->id !== $project->owner_id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed',
            'budget' => 'nullable|numeric|min:0',
            'spent' => 'nullable|numeric|min:0',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        $data['project_id'] = $project->id;

        $milestone = Milestone::create($data);
        
        // Attach assigned users if provided
        if ($request->has('assigned_users')) {
            $milestone->assignedUsers()->attach($request->assigned_users);
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Milestone created successfully!');
    }

    public function update(Request $request, Project $project, Milestone $milestone)
    {
        // Ensure milestone belongs to project
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

        $user = auth()->user();
        $isOwner = $user->id === $project->owner_id;
        $isAdmin = $user->role === 'admin';
        $isAssigned = $milestone->assignedUsers()->where('user_id', $user->id)->exists();
        
        // Full update: only owner or admin
        // Status-only update: owner, admin, or assigned user
        $statusOnlyUpdate = $request->only(['status']) === $request->all() && count($request->all()) === 1;
        
        if (!$isOwner && !$isAdmin) {
            // If not owner/admin, check if they're just updating status and are assigned
            if (!$statusOnlyUpdate || !$isAssigned) {
                abort(403, 'You do not have permission to update this milestone.');
            }
        }

        // Validate based on permission level
        if ($statusOnlyUpdate && $isAssigned && !$isOwner && !$isAdmin) {
            // Assigned users can only update status
            $data = $request->validate([
                'status' => 'required|in:pending,in_progress,completed',
            ]);
        } else {
            // Owner and admin can update everything
            $data = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'deadline' => 'nullable|date',
                'status' => 'sometimes|required|in:pending,in_progress,completed',
                'budget' => 'nullable|numeric|min:0',
                'spent' => 'nullable|numeric|min:0',
                'assigned_users' => 'nullable|array',
                'assigned_users.*' => 'exists:users,id',
            ]);
        }

        $milestone->update($data);
        
        // Sync assigned users (only if provided and user has permission)
        if ($request->has('assigned_users') && ($isOwner || $isAdmin)) {
            $milestone->assignedUsers()->sync($request->assigned_users);
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Milestone updated successfully!');
    }

    public function destroy(Project $project, Milestone $milestone)
    {
        // Authorize: only owner or admin can delete milestones
        if (auth()->user()->id !== $project->owner_id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        // Ensure milestone belongs to project
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

        $milestone->delete();

        return redirect()->route('projects.show', $project)
            ->with('success', 'Milestone deleted successfully!');
    }

    public function updateStatus(Request $request, Project $project, Milestone $milestone)
    {
        // Ensure milestone belongs to project
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

        $user = auth()->user();
        $isOwner = $user->id === $project->owner_id;
        $isAdmin = $user->role === 'admin';
        $isAssigned = $milestone->assignedUsers()->where('user_id', $user->id)->exists();
        
        // Allow owner, admin, or assigned user to update status
        if (!$isOwner && !$isAdmin && !$isAssigned) {
            abort(403, 'You do not have permission to update this milestone status.');
        }

        $data = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $milestone->update($data);

        // Handle JSON responses for AJAX
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Milestone status updated successfully!',
                'milestone' => $milestone
            ]);
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Milestone status updated successfully!');
    }
}
