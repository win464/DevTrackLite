<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // per_page query param with validation and capping
        $perPage = (int) $request->query('per_page', 15);

        // enforce sensible bounds: at least 1, at most 100
        $perPage = max(1, min($perPage, 100));

        $projects = Project::latest()->paginate($perPage);

        return ProjectResource::collection($projects)->response()->setStatusCode(200);
    }

    public function show(Project $project): JsonResponse
    {
        // Eager-load milestones so computed attributes (progress, overdue, over_budget) work correctly
        $project->load('milestones');

        return (new ProjectResource($project))->response();
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $data['owner_id'] = $request->user()->id ?? null;

        $project = Project::create($data);

        return (new ProjectResource($project))->response()->setStatusCode(201);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $project->update($data);

    return (new ProjectResource($project))->response();
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json([], 204);
    }
}
