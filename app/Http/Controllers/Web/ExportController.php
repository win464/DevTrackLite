<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Milestone;
use PDF;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportController extends Controller
{
    /**
     * Export a single project as PDF with details and milestones
     */
    public function projectPdf(Project $project)
    {
        $this->authorize('view', $project);
        
        $project->load(['milestones', 'teamMembers', 'owner']);

        $pdf = PDF::loadView('exports.project-pdf', [
            'project' => $project,
        ])
        ->setPaper('a4')
        ->setOption('margin-top', 20)
        ->setOption('margin-bottom', 20)
        ->setOption('margin-left', 15)
        ->setOption('margin-right', 15);

        return $pdf->download('project-' . $project->id . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export all visible projects as Excel spreadsheet
     */
    public function projectsExcel(Request $request)
    {
        $user = $request->user();

        // Get visible projects based on role
        if ($user->role === 'admin') {
            $projects = Project::with('milestones', 'owner')
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->role === 'manager') {
            $projects = Project::where(function($query) use ($user) {
                $query->where('owner_id', $user->id)
                      ->orWhereHas('teamMembers', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->with('milestones', 'owner')
            ->orderBy('created_at', 'desc')
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
            ->with('milestones', 'owner')
            ->orderBy('created_at', 'desc')
            ->get();
        }

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Projects');

        // Add header row
        $headers = ['ID', 'Title', 'Description', 'Owner', 'Status', 'Budget', 'Spent', 'Progress %', 'Milestones', 'Overdue', 'Over Budget', 'Created', 'Updated'];
        $sheet->fromArray([$headers], null, 'A1');

        // Style header row
        $headerStyle = $sheet->getStyle('1:1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF4F46E5');
        $headerStyle->getFont()->getColor()->setARGB('FFFFFFFF');

        // Add data rows
        $row = 2;
        foreach ($projects as $project) {
            $sheet->fromArray([[
                $project->id,
                $project->title,
                $project->description ?? '',
                $project->owner?->name ?? 'N/A',
                $project->status,
                $project->budget ?? 0,
                $project->milestones->sum('spent'),
                $project->progress,
                $project->milestones->count(),
                $project->overdue ? 'Yes' : 'No',
                $project->over_budget ? 'Yes' : 'No',
                $project->created_at->format('Y-m-d H:i'),
                $project->updated_at->format('Y-m-d H:i'),
            ]], null, 'A' . $row);
            $row++;
        }

        // Auto-fit columns
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        // Create temporary file and write
        $tempPath = storage_path('app/temp-export-' . uniqid() . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        // Download and delete
        return response()
            ->download($tempPath, 'projects-export-' . now()->format('Y-m-d') . '.xlsx')
            ->deleteFileAfterSend(true);
    }

    /**
     * Export project milestones as Excel
     */
    public function milestonesPdf(Project $project)
    {
        $this->authorize('view', $project);

        $project->load('milestones');

        $pdf = PDF::loadView('exports.milestones-pdf', [
            'project' => $project,
        ])
        ->setPaper('a4')
        ->setOption('margin-top', 20)
        ->setOption('margin-bottom', 20)
        ->setOption('margin-left', 15)
        ->setOption('margin-right', 15);

        return $pdf->download('milestones-' . $project->id . '-' . now()->format('Y-m-d') . '.pdf');
    }
}
