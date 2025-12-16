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
            margin-bottom: 5px;
            font-weight: 700;
        }
        .header p {
            color: #666;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 6px;
            border-left: 2px solid #000;
        }
        .meta-item {
            font-size: 11px;
        }
        .meta-item strong {
            display: block;
            color: #666;
            margin-bottom: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 9px;
        }
        .meta-item span {
            display: block;
            font-size: 14px;
            color: #000;
            font-weight: 700;
        }
        .progress-section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #ddd;
            border-radius: 5px;
            margin: 12px 0;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: #000;
            border-radius: 5px;
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
        .badge-overdue {
            background: #f5f5f5;
            color: #000;
            border: 1px solid #000;
        }
        .badge-budget {
            background: #f5f5f5;
            color: #000;
            border: 1px solid #000;
        }
        .section {
            margin: 35px 0;
        }
        .section h2 {
            font-size: 13px;
            color: #000;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 15px 0;
        }
        th {
            background: #f5f5f5;
            padding: 8px;
            text-align: left;
            font-weight: 700;
            color: #000;
            border-bottom: 1px solid #000;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:nth-child(even) {
            background: #f5f5f5;
        }
        tr:nth-child(odd) {
            background: white;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        .description {
            font-size: 11px;
            color: #333;
            margin: 12px 0;
            line-height: 1.5;
            padding: 12px;
            background: #f5f5f5;
            border-left: 2px solid #000;
            border-radius: 3px;
        }
        .status-indicators {
            margin: 15px 0;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $project->title }}</h1>
            <p>Project Report</p>
        </div>

        <div class="meta-grid">
            <div class="meta-item">
                <strong>Status</strong>
                <span>{{ ucfirst($project->status) }}</span>
            </div>
            <div class="meta-item">
                <strong>Progress</strong>
                <span>{{ $project->progress }}%</span>
            </div>
            <div class="meta-item">
                <strong>Budget</strong>
                <span>${{ number_format($project->budget ?? 0, 2) }}</span>
            </div>
            <div class="meta-item">
                <strong>Spent</strong>
                <span>${{ number_format($project->milestones->sum('spent'), 2) }}</span>
            </div>
        </div>

        @if($project->description)
            <div class="section">
                <h2>Description</h2>
                <div class="description">{{ $project->description }}</div>
            </div>
        @endif

        <div class="section">
            <h2>Progress Overview</h2>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $project->progress }}%"></div>
            </div>
            <p style="font-size: 12px; color: #7f8c8d; margin-top: 8px;">{{ $project->progress }}% complete</p>
        </div>

        <div class="section">
            <h2>Status & Alerts</h2>
            <div class="status-indicators">
                <span class="badge badge-{{ $project->status }}">{{ ucfirst($project->status) }}</span>
                @if($project->overdue)
                    <span class="badge badge-overdue">⚠️ Overdue</span>
                @endif
                @if($project->over_budget)
                    <span class="badge badge-budget">💰 Over Budget</span>
                @endif
            </div>
        </div>

        <div class="section">
            <h2>Budget Summary</h2>
            <table>
                <tr>
                    <td style="font-weight: 700;">Total Budget</td>
                    <td style="text-align: right; font-weight: 700;">${{ number_format($project->budget ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 700;">Total Spent</td>
                    <td style="text-align: right; font-weight: 700;">${{ number_format($project->milestones->sum('spent'), 2) }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 700; color: {{ max(0, ($project->budget ?? 0) - $project->milestones->sum('spent')) >= 0 ? '#2c3e50' : '#c0392b' }};">Remaining</td>
                    <td style="text-align: right; font-weight: 700; color: {{ max(0, ($project->budget ?? 0) - $project->milestones->sum('spent')) >= 0 ? '#2c3e50' : '#c0392b' }};">${{ number_format(max(0, ($project->budget ?? 0) - $project->milestones->sum('spent')), 2) }}</td>
                </tr>
            </table>
        </div>

        @if($project->milestones->count() > 0)
            <div class="section">
                <h2>Milestones ({{ $project->milestones->count() }})</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>Budget</th>
                            <th>Spent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->milestones as $milestone)
                            <tr>
                                <td style="font-weight: 600;">{{ $milestone->title }}</td>
                                <td><span class="badge badge-{{ $milestone->status }}" style="display: inline-block;">{{ ucfirst($milestone->status) }}</span></td>
                                <td>{{ $milestone->deadline?->format('M d, Y') ?? 'N/A' }}</td>
                                <td>${{ number_format($milestone->budget ?? 0, 2) }}</td>
                                <td>${{ number_format($milestone->spent ?? 0, 2) }}</td>
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
