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
        // Authorize: only owner or admin can update milestones
        if (auth()->user()->id !== $project->owner_id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        // Ensure milestone belongs to project
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

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

        $milestone->update($data);
        
        // Sync assigned users
        if ($request->has('assigned_users')) {
            $milestone->assignedUsers()->sync($request->assigned_users);
        } else {
            $milestone->assignedUsers()->detach();
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
}
