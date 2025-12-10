<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user && $user->role === 'admin';

        $projectsQuery = $isAdmin
            ? Project::query()
            : Project::where('owner_id', $user->id);

        $projects = $projectsQuery
            ->with('milestones')
            ->get();

        $projectIds = $projects->pluck('id');

        $totalProjects = $projects->count();
        $activeCount = $projects->where('status', 'active')->count();
        $completedCount = $projects->where('status', 'closed')->count();
        $overdueCount = $projects->filter(fn ($p) => $p->overdue)->count();

        $totalBudget = $projects->sum(fn ($p) => $p->budget ?? 0);
        $totalSpent = $projects->sum(fn ($p) => $p->milestones->sum('spent'));
        $budgetConsumptionPct = $totalBudget > 0
            ? min(100, round(($totalSpent / $totalBudget) * 100))
            : null;

        // Progress over time (average progress by created-at month, last 6 months)
        $progressByMonth = $projects
            ->groupBy(fn ($p) => Carbon::parse($p->created_at)->format('Y-m'))
            ->map(function ($group, $ym) {
                $label = Carbon::createFromFormat('Y-m', $ym)->format('M Y');
                $avg = (int) round($group->avg(fn ($p) => $p->progress));
                return ['label' => $label, 'value' => $avg];
            })
            ->sortKeys()
            ->values()
            ->take(-6); // last 6

        // Budget usage (top 6 by budget)
        $budgetProjects = $projects
            ->sortByDesc(fn ($p) => $p->budget ?? 0)
            ->take(6)
            ->values();

        $budgetChart = [
            'labels' => $budgetProjects->pluck('title'),
            'budget' => $budgetProjects->map(fn ($p) => (float) ($p->budget ?? 0)),
            'spent' => $budgetProjects->map(fn ($p) => (float) $p->milestones->sum('spent')),
        ];

        // Milestone status breakdown
        $milestoneStatusCounts = $projectIds->isEmpty()
            ? collect()
            : Milestone::whereIn('project_id', $projectIds)
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status');

        $milestoneChart = [
            'labels' => ['Pending', 'In Progress', 'Completed'],
            'counts' => [
                (int) ($milestoneStatusCounts['pending'] ?? 0),
                (int) ($milestoneStatusCounts['in_progress'] ?? 0),
                (int) ($milestoneStatusCounts['completed'] ?? 0),
            ],
        ];

        // Team workload - Get users with their project and milestone counts
        $teamWorkload = $isAdmin 
            ? \App\Models\User::withCount(['projects', 'milestones'])
                ->get()
                ->filter(fn($user) => $user->projects_count > 0 || $user->milestones_count > 0)
                ->sortByDesc('milestones_count')
                ->take(5)
            : collect();

        return view('dashboard', [
            'summary' => [
                'total' => $totalProjects,
                'active' => $activeCount,
                'completed' => $completedCount,
                'overdue' => $overdueCount,
                'budget' => [
                    'total' => $totalBudget,
                    'spent' => $totalSpent,
                    'percent' => $budgetConsumptionPct,
                ],
            ],
            'progressByMonth' => $progressByMonth,
            'budgetChart' => $budgetChart,
            'milestoneChart' => $milestoneChart,
            'teamWorkload' => $teamWorkload,
        ]);
    }
}
