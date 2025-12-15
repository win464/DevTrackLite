<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $tab = $request->get('tab', 'owned'); // Default tab
        
        // Admin: see owned, assigned, and other projects
        if ($user->role === 'admin') {
            $ownedProjects = Project::where('owner_id', $user->id)
                ->with('milestones')
                ->withCount('milestones')
                ->paginate(12, ['*'], 'page_owned');
            
            $assignedProjects = Project::whereHas('teamMembers', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('owner_id', '!=', $user->id)
            ->with('milestones')
            ->withCount('milestones')
            ->paginate(12, ['*'], 'page_assigned');
            
            $otherProjects = Project::where('owner_id', '!=', $user->id)
                ->whereDoesntHave('teamMembers', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('milestones')
                ->withCount('milestones')
                ->paginate(12, ['*'], 'page_other');
            
            return view('projects.index', compact('ownedProjects', 'assignedProjects', 'otherProjects', 'tab', 'user'));
        }
        
        // Manager: see owned + team member projects
        elseif ($user->role === 'manager') {
            $ownedProjects = Project::where('owner_id', $user->id)
                ->with('milestones')
                ->withCount('milestones')
                ->paginate(12, ['*'], 'page_owned');
            
            $assignedProjects = Project::whereHas('teamMembers', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('owner_id', '!=', $user->id)
            ->with('milestones')
            ->withCount('milestones')
            ->paginate(12, ['*'], 'page_assigned');
            
            return view('projects.index', compact('ownedProjects', 'assignedProjects', 'tab', 'user'));
        }
        
        // Viewer: see only assigned projects
        else {
            $assignedProjects = Project::where(function($query) use ($user) {
                $query->whereHas('teamMembers', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orWhereHas('milestones.assignedUsers', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->with('milestones')
            ->withCount('milestones')
            ->paginate(12);
            
            return view('projects.index', compact('assignedProjects', 'user'));
        }
    }

    public function show(Project $project)
    {
        $user = auth()->user();
        
        // Authorize: allow owner, admin, team member, or milestone assignee
        $isOwner = $user->id === $project->owner_id;
        $isAdmin = $user->role === 'admin';
        $isTeamMember = $project->teamMembers()->where('user_id', $user->id)->exists();
        $isMilestoneAssignee = $project->milestones()->whereHas('assignedUsers', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->exists();
        
        if (!$isOwner && !$isAdmin && !$isTeamMember && !$isMilestoneAssignee) {
            abort(403);
        }

        $project->load(['milestones.assignedUsers', 'teamMembers']);

        return view('projects.show', compact('project'));
    }

    public function create()
    {
        // Only managers and admins can create projects
        if (!in_array(auth()->user()->role, ['admin', 'manager'])) {
            abort(403, 'Only managers and administrators can create projects.');
        }
        
        $users = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
        return view('projects.form', compact('users'));
    }

    public function store(Request $request)
    {
        // Only managers and admins can create projects
        if (!in_array(auth()->user()->role, ['admin', 'manager'])) {
            abort(403, 'Only managers and administrators can create projects.');
        }
        
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'team_members' => 'nullable|array',
            'team_members.*' => 'exists:users,id',
        ]);

        $data['owner_id'] = auth()->id();

        $project = Project::create($data);
        
        // Attach team members if provided
        if ($request->has('team_members')) {
            $project->teamMembers()->attach($request->team_members);
        }

        return redirect()->route('projects.index')->with('success', 'Project created successfully!');
    }

    public function edit(Project $project)
    {
        // Only owner or admin can edit
        if (auth()->user()->id !== $project->owner_id && auth()->user()->role !== 'admin') {
            abort(403, 'You do not have permission to edit this project.');
        }

        $users = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
        $project->load('teamMembers');
        return view('projects.form', compact('project', 'users'));
    }

    public function update(Request $request, Project $project)
    {
        // Only owner or admin can update
        if (auth()->user()->id !== $project->owner_id && auth()->user()->role !== 'admin') {
            abort(403, 'You do not have permission to update this project.');
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'team_members' => 'nullable|array',
            'team_members.*' => 'exists:users,id',
        ]);

        $project->update($data);
        
        // Sync team members
        if ($request->has('team_members')) {
            $project->teamMembers()->sync($request->team_members);
        } else {
            $project->teamMembers()->detach();
        }

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully!');
    }

    public function destroy(Project $project)
    {
        $user = auth()->user();
        
        // Admin can delete any project, managers can delete their own projects
        $canDelete = $user->role === 'admin' || 
                    ($user->id === $project->owner_id && $user->role === 'manager');
        
        if (!$canDelete) {
            abort(403, 'You do not have permission to delete this project.');
        }

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully!');
    }
}
