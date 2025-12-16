<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Get public stats (aggregated system data, no auth required)
     */
    public function publicStats(): JsonResponse
    {
        $projects = Project::with('milestones')->get();

        $totalProjects = $projects->count();
        $activeCount = $projects->where('status', 'active')->count();
        $completedCount = $projects->where('status', 'closed')->count();
        $overdueCount = $projects->filter(fn($p) => $p->overdue)->count();

        $totalBudget = $projects->sum(fn($p) => $p->budget ?? 0);
        $totalSpent = $projects->sum(fn($p) => $p->milestones->sum('spent'));
        $budgetPercent = $totalBudget > 0
            ? min(100, round(($totalSpent / $totalBudget) * 100))
            : null;

        $averageProgress = $projects->count() > 0
            ? (int) round($projects->avg('progress'))
            : 0;

        // Milestone breakdown
        $milestoneStats = [
            'pending' => Milestone::where('status', 'pending')->count(),
            'in_progress' => Milestone::where('status', 'in_progress')->count(),
            'completed' => Milestone::where('status', 'completed')->count(),
        ];

        return response()->json([
            'data' => [
                'projects' => [
                    'total' => $totalProjects,
                    'active' => $activeCount,
                    'completed' => $completedCount,
                    'overdue' => $overdueCount,
                ],
                'budget' => [
                    'total' => (float) $totalBudget,
                    'spent' => (float) $totalSpent,
                    'remaining' => (float) max(0, $totalBudget - $totalSpent),
                    'percent_used' => $budgetPercent,
                ],
                'progress' => [
                    'average' => $averageProgress,
                ],
                'milestones' => $milestoneStats,
                'users' => [
                    'total' => User::count(),
                    'active' => User::where('role', '!=', 'viewer')->count(),
                ],
            ],
        ]);
    }

    /**
     * Get authenticated user's stats (their visible projects)
     */
    public function userStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';

        // Get projects visible to the user
        if ($isAdmin) {
            $projects = Project::with('milestones')->get();
        } elseif ($user->role === 'manager') {
            $projects = Project::where(function($query) use ($user) {
                $query->where('owner_id', $user->id)
                      ->orWhereHas('teamMembers', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->with('milestones')
            ->get();
        } else {
            // Viewer
            $projects = Project::where(function($query) use ($user) {
                $query->whereHas('teamMembers', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orWhereHas('milestones.assignedUsers', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->with('milestones')
            ->get();
        }

        $projectIds = $projects->pluck('id');

        $totalProjects = $projects->count();
        $ownedProjects = $projects->where('owner_id', $user->id)->count();
        $activeCount = $projects->where('status', 'active')->count();
        $completedCount = $projects->where('status', 'closed')->count();
        $overdueCount = $projects->filter(fn($p) => $p->overdue)->count();

        $totalBudget = $projects->sum(fn($p) => $p->budget ?? 0);
        $totalSpent = $projects->sum(fn($p) => $p->milestones->sum('spent'));
        $budgetPercent = $totalBudget > 0
            ? min(100, round(($totalSpent / $totalBudget) * 100))
            : null;

        $averageProgress = $projects->count() > 0
            ? (int) round($projects->avg('progress'))
            : 0;

        // Milestone breakdown for visible projects
        $milestoneStats = $projectIds->isEmpty()
            ? ['pending' => 0, 'in_progress' => 0, 'completed' => 0]
            : Milestone::whereIn('project_id', $projectIds)
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status')
                ->toArray();

        // Progress by month (last 6 months)
        $progressByMonth = $projects
            ->groupBy(fn($p) => Carbon::parse($p->created_at)->format('Y-m'))
            ->map(function($group, $ym) {
                $label = Carbon::createFromFormat('Y-m', $ym)->format('M Y');
                $avg = (int) round($group->avg(fn($p) => $p->progress));
                return ['label' => $label, 'value' => $avg];
            })
            ->sortKeys()
            ->values()
            ->take(-6)
            ->toArray();

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'projects' => [
                    'total' => $totalProjects,
                    'owned' => $ownedProjects,
                    'assigned' => $totalProjects - $ownedProjects,
                    'active' => $activeCount,
                    'completed' => $completedCount,
                    'overdue' => $overdueCount,
                ],
                'budget' => [
                    'total' => (float) $totalBudget,
                    'spent' => (float) $totalSpent,
                    'remaining' => (float) max(0, $totalBudget - $totalSpent),
                    'percent_used' => $budgetPercent,
                ],
                'progress' => [
                    'average' => $averageProgress,
                    'by_month' => $progressByMonth,
                ],
                'milestones' => [
                    'pending' => (int) ($milestoneStats['pending'] ?? 0),
                    'in_progress' => (int) ($milestoneStats['in_progress'] ?? 0),
                    'completed' => (int) ($milestoneStats['completed'] ?? 0),
                ],
                'tasks' => [
                    'assigned_to_me' => $user->milestones()->count(),
                    'overdue_assigned' => $user->milestones()
                        ->where('status', '!=', 'completed')
                        ->whereNotNull('deadline')
                        ->where('deadline', '<', now()->toDateString())
                        ->count(),
                ],
            ],
        ]);
    }
}
