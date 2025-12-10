<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- SUMMARY CARDS -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Summary</h3>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <!-- Tile -->
                    <div class="bg-white border rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-5 
                                aspect-square flex flex-col justify-between">
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-500">Total Projects</p>
                            <x-heroicon-o-folder class="w-5 h-5 text-indigo-500" />
                        </div>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $summary['total'] }}</p>
                    </div>

                    <div class="bg-white border rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-5 
                                aspect-square flex flex-col justify-between">
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-500">Active</p>
                            <x-heroicon-o-bolt class="w-5 h-5 text-blue-500" />
                        </div>
                        <p class="text-4xl font-bold text-blue-600 mt-2">{{ $summary['active'] }}</p>
                    </div>

                    <div class="bg-white border rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-5 
                                aspect-square flex flex-col justify-between">
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-500">Completed</p>
                            <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-500" />
                        </div>
                        <p class="text-4xl font-bold text-emerald-600 mt-2">{{ $summary['completed'] }}</p>
                    </div>

                    <div class="bg-white border rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-5 
                                aspect-square flex flex-col justify-between">
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-500">Overdue</p>
                            <x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />
                        </div>
                        <p class="text-4xl font-bold text-red-600 mt-2">{{ $summary['overdue'] }}</p>
                    </div>
                </div>
            </div>


            <!-- BUDGET SECTION -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Budget</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div class="bg-white border rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-6 
                                flex flex-col justify-between aspect-square">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm text-gray-500">Budget Consumption</p>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $summary['budget']['percent'] > 80 ? 'bg-red-100 text-red-700' : ($summary['budget']['percent'] > 60 ? 'bg-yellow-100 text-yellow-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    {{ $summary['budget']['percent'] }}%
                                </span>
                            </div>
                            <p class="text-xl font-bold text-gray-900 mt-1">
                                GHS {{ number_format($summary['budget']['spent'], 2) }}
                                <span class="text-sm text-gray-500">
                                    / GHS {{ number_format($summary['budget']['total'], 2) }}
                                </span>
                            </p>
                        </div>

                        <div class="mt-4">
                            <p class="text-xs text-gray-500 mb-2">Progress</p>
                            <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-3 bg-gradient-to-r from-indigo-500 to-blue-600"
                                     style="width: {{ $summary['budget']['percent'] }}%">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Budget Insights Chart -->
                    <div class="bg-white border rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-6 aspect-square flex flex-col">
                        <h4 class="text-sm font-semibold text-gray-700 mb-4">Budget Insights</h4>
                        <div class="flex-1 flex items-center justify-center">
                            <div class="w-full max-w-[200px]">
                                <canvas id="budgetInsightChart"></canvas>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <div class="flex items-center gap-1">
                                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                    <span class="text-gray-600">Spent</span>
                                </div>
                                <p class="font-semibold text-gray-900 mt-1">GHS {{ number_format($summary['budget']['spent'], 0) }}</p>
                            </div>
                            <div>
                                <div class="flex items-center gap-1">
                                    <span class="w-3 h-3 rounded-full bg-gray-300"></span>
                                    <span class="text-gray-600">Remaining</span>
                                </div>
                                <p class="font-semibold text-gray-900 mt-1">GHS {{ number_format($summary['budget']['total'] - $summary['budget']['spent'], 0) }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Team Workload Section (Admin Only) -->
            @if(auth()->user()->role === 'admin' && $teamWorkload->count() > 0)
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Team Workload</h3>
                <div class="bg-white border rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-6">
                    <div class="space-y-4">
                        @foreach($teamWorkload as $member)
                            <div class="flex items-center justify-between pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $member->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $member->email }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 text-sm">
                                    <div class="text-right">
                                        <p class="font-semibold text-indigo-600">{{ $member->projects_count }}</p>
                                        <p class="text-xs text-gray-500">Projects</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-emerald-600">{{ $member->milestones_count }}</p>
                                        <p class="text-xs text-gray-500">Milestones</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif


            <!-- CHARTS SECTION -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Charts</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                    <!-- Progress Chart -->
                    <div class="bg-white border rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-6 aspect-square flex flex-col">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Progress Over Time</h4>
                        <div style="flex: 1; position: relative; min-height: 0;">
                            <canvas id="progressChart"></canvas>
                        </div>
                    </div>

                    <!-- Budget Chart -->
                    <div class="bg-white border rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-6 aspect-square flex flex-col">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Budget Usage (Top Projects)</h4>
                        <div style="flex: 1; position: relative; min-height: 0;">
                            <canvas id="budgetChart"></canvas>
                        </div>
                    </div>

                    <!-- Milestone Chart -->
                    <div class="bg-white border rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-6 aspect-square flex flex-col">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Milestones Status</h4>
                        <div style="flex: 1; position: relative; min-height: 0;">
                            <canvas id="milestoneChart"></canvas>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>


    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const progressData = @json($progressByMonth);
        const budgetData = @json($budgetChart);
        const milestoneData = @json($milestoneChart);

        // PROGRESS CHART
        new Chart(document.getElementById('progressChart'), {
            type: 'line',
            data: {
                labels: progressData.map(p => p.label),
                datasets: [{
                    label: 'Average Progress (%)',
                    data: progressData.map(p => p.value),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#4f46e5',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { stepSize: 25 },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // BUDGET CHART
        new Chart(document.getElementById('budgetChart'), {
            type: 'bar',
            data: {
                labels: budgetData.labels,
                datasets: [
                    {
                        label: 'Budget',
                        data: budgetData.budget,
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderRadius: 6,
                        borderSkipped: false
                    },
                    {
                        label: 'Spent',
                        data: budgetData.spent,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderRadius: 6,
                        borderSkipped: false
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, usePointStyle: true }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': GHS ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // MILESTONE CHART
        new Chart(document.getElementById('milestoneChart'), {
            type: 'doughnut',
            data: {
                labels: milestoneData.labels,
                datasets: [{
                    data: milestoneData.counts,
                    backgroundColor: [
                        '#eab308',
                        '#3b82f6',
                        '#10b981'
                    ],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 8
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, usePointStyle: true }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        cornerRadius: 8
                    }
                }
            }
        });

        // BUDGET INSIGHT CHART (mini doughnut)
        new Chart(document.getElementById('budgetInsightChart'), {
            type: 'doughnut',
            data: {
                labels: ['Spent', 'Remaining'],
                datasets: [{
                    data: [
                        {{ $summary['budget']['spent'] }},
                        {{ $summary['budget']['total'] - $summary['budget']['spent'] }}
                    ],
                    backgroundColor: ['#10b981', '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: {
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': GHS ' + context.parsed.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>

</x-app-layout>
