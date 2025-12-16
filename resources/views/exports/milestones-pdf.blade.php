<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #000;
            line-height: 1.6;
            background: white;
            font-size: 12px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 25px;
            margin-bottom: 35px;
        }
        .header h1 {
            color: #000;
            font-size: 22px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .header p {
            color: #666;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            margin: 4px 6px 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #f5f5f5;
            color: #000;
            border: 1px solid #000;
        }
        .badge-active {
            background: #f5f5f5;
            color: #000;
            border: 1px solid #000;
        }
        .badge-pending {
            background: #f5f5f5;
            color: #000;
            border: 1px solid #000;
        }
        .badge-completed {
            background: #f5f5f5;
            color: #000;
            border: 1px solid #000;
        }
        .milestone {
            margin-bottom: 30px;
            padding: 20px;
            border-left: 2px solid #000;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .milestone h3 {
            color: #000;
            font-size: 13px;
            margin-bottom: 12px;
            font-weight: 700;
        }
        .milestone-meta {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            font-size: 11px;
            margin: 15px 0;
        }
        .meta-item {
            color: #333;
        }
        .meta-item strong {
            display: block;
            margin-bottom: 4px;
            color: #666;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #ecf0f1;
            border-radius: 4px;
            margin: 10px 0;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4f46e5 0%, #6366f1 100%);
        }
        .description {
            font-size: 12px;
            color: #555;
            margin: 10px 0;
            line-height: 1.6;
            padding: 10px;
            background: white;
            border-radius: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 25px;
        }
        th {
            background: #f5f5f5;
            padding: 8px;
            text-align: left;
            font-weight: 700;
            color: #000;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background: #f5f5f5;
        }
        tr:nth-child(odd) {
            background: white;
        }
        .footer {
            margin-top: 50px;
            padding-top: 25px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        .summary {
            margin: 25px 0;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 6px;
            border-left: 2px solid #000;
        }
        .summary-stat {
            margin: 8px 0;
            font-size: 11px;
        }
        .summary-stat strong {
            display: inline-block;
            min-width: 150px;
            color: #666;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 9px;
        }
        .section h2 {
            font-size: 13px;
            color: #000;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .no-data {
            color: #999;
            text-align: center;
            padding: 40px 0;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Milestones: {{ $project->title }}</h1>
            <p>Project Report</p>
        </div>

        @if($project->milestones->count() === 0)
            <div class="no-data">
                <p>No milestones found for this project.</p>
            </div>
        @else
            <div class="summary">
                <div class="summary-stat"><strong>Total Milestones:</strong> {{ $project->milestones->count() }}</div>
                <div class="summary-stat"><strong>Completed:</strong> {{ $project->milestones->where('status', 'completed')->count() }}</div>
                <div class="summary-stat"><strong>In Progress:</strong> {{ $project->milestones->where('status', 'active')->count() }}</div>
                <div class="summary-stat"><strong>Total Budget:</strong> ${{ number_format($project->milestones->sum('budget') ?? 0, 2) }}</div>
                <div class="summary-stat"><strong>Total Spent:</strong> ${{ number_format($project->milestones->sum('spent') ?? 0, 2) }}</div>
            </div>

            <div style="margin-top: 35px;">
                @foreach($project->milestones as $milestone)
                    <div class="milestone">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h3>{{ $milestone->title }}</h3>
                            <span class="badge badge-{{ $milestone->status }}">{{ ucfirst($milestone->status) }}</span>
                        </div>

                        <div class="milestone-meta">
                            <div class="meta-item">
                                <strong>Deadline</strong>
                                {{ $milestone->deadline?->format('M d, Y') ?? 'No deadline' }}
                            </div>
                            <div class="meta-item">
                                <strong>Budget</strong>
                                ${{ number_format($milestone->budget ?? 0, 2) }}
                            </div>
                            <div class="meta-item">
                                <strong>Spent</strong>
                                ${{ number_format($milestone->spent ?? 0, 2) }}
                            </div>
                        </div>

                        @if($milestone->description)
                            <div class="description">{{ $milestone->description }}</div>
                        @endif

                        <div style="margin-top: 12px;">
                            <strong style="font-size: 11px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Progress</strong>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ $milestone->progress ?? 0 }}%"></div>
                            </div>
                            <p style="font-size: 11px; color: #7f8c8d; margin-top: 4px;">{{ $milestone->progress ?? 0 }}% complete</p>
                        </div>

                        @if($milestone->assignedUsers()->count() > 0)
                            <div style="margin-top: 12px;">
                                <strong style="font-size: 11px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Assigned To</strong>
                                <p style="font-size: 12px; color: #555;">
                                    {{ $milestone->assignedUsers->pluck('name')->join(', ') }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="section" style="margin-top: 40px;">
                <h2>Summary Table</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Milestone</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>Budget</th>
                            <th>Spent</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->milestones as $milestone)
                            <tr>
                                <td style="font-weight: 600;">{{ $milestone->title }}</td>
                                <td>{{ ucfirst($milestone->status) }}</td>
                                <td>{{ $milestone->deadline?->format('M d, Y') ?? 'N/A' }}</td>
                                <td>${{ number_format($milestone->budget ?? 0, 2) }}</td>
                                <td>${{ number_format($milestone->spent ?? 0, 2) }}</td>
                                <td>{{ $milestone->progress ?? 0 }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="footer">
            <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }}</p>
            <p style="margin-top: 5px; font-size: 10px;">DevTrackLite Project Management System</p>
        </div>
    </div>
</body>
</html>
