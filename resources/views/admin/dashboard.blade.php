@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
            <div>
                <div class="text-sm font-medium text-gray-500">Total Tickets</div>
                <div class="text-3xl font-bold text-gray-900">{{ $totalTickets }}</div>
            </div>
            {{-- You can add a relevant icon here if you use an icon library --}}
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
            <div>
                <div class="text-sm font-medium text-gray-500">Currently Open</div>
                <div class="text-3xl font-bold text-blue-600">{{ $openTickets }}</div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
            <div>
                <div class="text-sm font-medium text-gray-500">Unassigned</div>
                <div class="text-3xl font-bold text-red-600">{{ $unassignedTickets }}</div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
            <div>
                <div class="text-sm font-medium text-gray-500">Resolved Today</div>
                <div class="text-3xl font-bold text-green-600">{{ $resolvedToday }}</div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Tickets by Status</h3>
            <div class="w-full h-80 flex items-center justify-center">
                <canvas id="ticketsByStatusChart"></canvas>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Tickets by Category</h3>
            <div class="w-full h-80 flex items-center justify-center">
                <canvas id="ticketsByCategoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tickets by Status Chart (Donut Chart)
        const statusCtx = document.getElementById('ticketsByStatusChart');
        if (statusCtx) {
            new Chart(statusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: @json($statusLabels),
                    datasets: [{
                        label: 'Tickets by Status',
                        data: @json($statusData),
                        backgroundColor: @json($statusColors),
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
        }

        // Tickets by Category Chart (Bar Chart)
        const categoryCtx = document.getElementById('ticketsByCategoryChart');
        if (categoryCtx) {
            new Chart(categoryCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($categoryLabels),
                    datasets: [{
                        label: '# of Tickets',
                        data: @json($categoryData),
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0 // Ensure y-axis has only integer steps
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false // Hide legend for single-dataset bar charts
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection