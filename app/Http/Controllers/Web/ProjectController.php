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
            ? Project::paginate(12)
            : Project::where('owner_id', auth()->id())->paginate(12);

        // Eager load milestones for progress calculation
        $projects->load('milestones');

        return view('projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        // Authorize: allow owner or admin
        if (auth()->user()->id !== $project->owner_id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $project->load('milestones');

        return view('projects.show', compact('project'));
    }

    public function create()
    {
        return view('projects.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
        ]);

        $data['owner_id'] = auth()->id();

        Project::create($data);

        return redirect()->route('projects.index')->with('success', 'Project created successfully!');
    }

    public function edit(Project $project)
    {
        if (auth()->user()->id !== $project->owner_id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        return view('projects.form', compact('project'));
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
            'budget' => 'nullable|numeric|min:0',
        ]);

        $project->update($data);

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
