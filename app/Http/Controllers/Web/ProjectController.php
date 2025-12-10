<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        // Get paginated projects for current user (owned by them or all if admin)
        $projects = auth()->user()->role === 'admin'
            ? Project::with('milestones')->withCount('milestones')->paginate(12)
            : Project::where('owner_id', auth()->id())->with('milestones')->withCount('milestones')->paginate(12);

        return view('projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        // Authorize: allow owner or admin
        if (auth()->user()->id !== $project->owner_id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $project->load(['milestones.assignedUsers', 'teamMembers']);

        return view('projects.show', compact('project'));
    }

    public function create()
    {
        $users = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
        return view('projects.form', compact('users'));
    }

    public function store(Request $request)
    {
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
        if (auth()->user()->id !== $project->owner_id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $users = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
        $project->load('teamMembers');
        return view('projects.form', compact('project', 'users'));
    }

    public function update(Request $request, Project $project)
    {
        if (auth()->user()->id !== $project->owner_id && auth()->user()->role !== 'admin') {
            abort(403);
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
        if (auth()->user()->id !== $project->owner_id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully!');
    }
}
