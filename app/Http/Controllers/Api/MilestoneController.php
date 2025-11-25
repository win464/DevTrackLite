<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Milestone;
use App\Http\Resources\MilestoneResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MilestoneController extends Controller
{
    public function index(Project $project, Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $milestones = $project->milestones()->latest()->paginate($perPage);

        return MilestoneResource::collection($milestones)->response();
    }

    public function store(Project $project, Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! ($user->role === 'admin' || $project->owner_id === $user->id)) {
            abort(403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'nullable|in:pending,in_progress,completed',
            'budget' => 'nullable|numeric|min:0',
            'spent' => 'nullable|numeric|min:0',
        ]);

        $data['project_id'] = $project->id;

        $milestone = Milestone::create($data);

        return (new MilestoneResource($milestone))->response()->setStatusCode(201);
    }

    public function update(Milestone $milestone, Request $request): JsonResponse
    {
        $this->authorize('update', $milestone);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'nullable|in:pending,in_progress,completed',
            'budget' => 'nullable|numeric|min:0',
            'spent' => 'nullable|numeric|min:0',
        ]);

        $milestone->update($data);

        return (new MilestoneResource($milestone))->response();
    }

    public function destroy(Milestone $milestone): JsonResponse
    {
        $this->authorize('delete', $milestone);

        $milestone->delete();

        return response()->json([], 204);
    }
}
