@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @if(auth()->user()->isAdmin())
        {{-- ADMIN DASHBOARD --}}
        <div class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
                <p class="text-sm text-slate-500 mt-1">Welcome back, here's what's happening today.</p>
            </div>
            {{-- Live Clock --}}
            <div class="inline-flex items-center gap-3 bg-white rounded-2xl shadow-sm border border-slate-200 px-5 py-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-right">
                    <div id="liveClock" class="text-xl font-bold text-slate-900 tabular-nums tracking-tight leading-none">
                        --:--:--</div>
                    <div id="liveDate" class="text-xs text-slate-500 font-medium mt-0.5">--</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 relative overflow-hidden group hover:shadow-md transition-all">
                <div
                    class="absolute top-0 right-0 w-24 h-24 bg-amber-50 rounded-full -mr-8 -mt-8 transition-transform group-hover:scale-110">
                </div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-slate-900">{{ $stats['total_events'] }}</div>
                    <div class="text-sm text-slate-500 font-medium mt-1">Total Events</div>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 relative overflow-hidden group hover:shadow-md transition-all">
                <div
                    class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-full -mr-8 -mt-8 transition-transform group-hover:scale-110">
                </div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-slate-900">{{ $stats['upcoming_events'] }}</div>
                    <div class="text-sm text-slate-500 font-medium mt-1">Upcoming Events</div>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 relative overflow-hidden group hover:shadow-md transition-all">
                <div
                    class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -mr-8 -mt-8 transition-transform group-hover:scale-110">
                </div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-slate-900">{{ $stats['total_waiters'] }}</div>
                    <div class="text-sm text-slate-500 font-medium mt-1">Active Waiters</div>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 relative overflow-hidden group hover:shadow-md transition-all">
                <div
                    class="absolute top-0 right-0 w-24 h-24 bg-rose-50 rounded-full -mr-8 -mt-8 transition-transform group-hover:scale-110">
                </div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-slate-900">{{ $stats['low_stock_items'] }}</div>
                    <div class="text-sm text-slate-500 font-medium mt-1">Low Stock Items</div>
                </div>
            </div>
        </div>

        {{-- ANALYTICS CHARTS ROW --}}
        @php
            $eventsByMonth = \App\Models\Event::select(
                \Illuminate\Support\Facades\DB::raw('DATE_FORMAT(event_date, "%Y-%m") as month'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as count')
            )
                ->where('event_date', '>=', now()->subMonths(5))
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            $months = [];
            $monthCounts = [];
            for ($i = 5; $i >= 0; $i--) {
                $m = now()->subMonths($i)->format('Y-m');
                $label = now()->subMonths($i)->format('M Y');
                $months[] = $label;
                $monthCounts[] = $eventsByMonth[$m] ?? 0;
            }

            $equipmentByCategory = \App\Models\EquipmentItem::select('equipment_categories.name', \Illuminate\Support\Facades\DB::raw('SUM(equipment_items.total_quantity) as total'))
                ->join('equipment_categories', 'equipment_items.category_id', '=', 'equipment_categories.id')
                ->groupBy('equipment_categories.name')
                ->pluck('total', 'name')
                ->toArray();

            $statusCounts = [
                \App\Models\Event::where('status', 'scheduled')->count(),
                \App\Models\Event::where('status', 'ongoing')->count(),
                \App\Models\Event::where('status', 'completed')->count(),
                \App\Models\Event::where('status', 'cancelled')->count(),
            ];

            $conditionCounts = [
                \App\Models\EquipmentItem::where('condition', 'new')->count(),
                \App\Models\EquipmentItem::where('condition', 'good')->count(),
                \App\Models\EquipmentItem::where('condition', 'fair')->count(),
                \App\Models\EquipmentItem::where('condition', 'damaged')->count(),
            ];

            $topEquipment = \App\Models\EventEquipment::select('equipment_items.name', \Illuminate\Support\Facades\DB::raw('SUM(event_equipment.quantity_allocated) as total'))
                ->join('equipment_items', 'event_equipment.equipment_item_id', '=', 'equipment_items.id')
                ->groupBy('equipment_items.name')
                ->orderByDesc('total')
                ->limit(5)
                ->pluck('total', 'name')
                ->toArray();
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Events Trend Chart --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Events Trend (Last 6 Months)</h2>
                <canvas id="eventsChart" height="120"></canvas>
            </div>

            {{-- Event Status Distribution --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Event Status Distribution</h2>
                <div class="flex items-center justify-center" style="max-height: 200px;">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            {{-- Equipment by Category --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Equipment by Category</h2>
                <canvas id="categoryChart" height="200"></canvas>
            </div>

            {{-- Equipment Condition --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Equipment Condition</h2>
                <canvas id="conditionChart" height="200"></canvas>
            </div>

            {{-- Top Used Equipment --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Most Used Equipment</h2>
                <canvas id="topEquipmentChart" height="200"></canvas>
            </div>
        </div>

        <script>
            // Events Trend (Line Chart)
            new Chart(document.getElementById('eventsChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($months) !!},
                    datasets: [{
                        label: 'Events',
                        data: {!! json_encode($monthCounts) !!},
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217, 119, 6, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#d97706'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });

            // Event Status (Doughnut)
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Scheduled', 'Ongoing', 'Completed', 'Cancelled'],
                    datasets: [{
                        data: {!! json_encode($statusCounts) !!},
                        backgroundColor: ['#3b82f6', '#a855f7', '#10b981', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '65%',
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
                }
            });

            // Equipment by Category (Bar)
            new Chart(document.getElementById('categoryChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($equipmentByCategory)) !!},
                    datasets: [{
                        label: 'Total Quantity',
                        data: {!! json_encode(array_values($equipmentByCategory)) !!},
                        backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6'],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });

            // Equipment Condition (Pie)
            new Chart(document.getElementById('conditionChart'), {
                type: 'pie',
                data: {
                    labels: ['New', 'Good', 'Fair', 'Damaged'],
                    datasets: [{
                        data: {!! json_encode($conditionCounts) !!},
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
                }
            });

            // Top Equipment (Horizontal Bar)
            new Chart(document.getElementById('topEquipmentChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($topEquipment)) !!},
                    datasets: [{
                        label: 'Times Allocated',
                        data: {!! json_encode(array_values($topEquipment)) !!},
                        backgroundColor: '#d97706',
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true } }
                }
            });
        </script>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Upcoming Events</h2>
                        <p class="text-sm text-slate-500">Events scheduled in the near future</p>
                    </div>
                    <a href="{{ route('events.create') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Event
                    </a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($upcomingEvents as $event)
                        <div class="px-6 py-4 flex justify-between items-center group hover:bg-slate-50/60 transition-colors">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-400 text-white flex flex-col items-center justify-center text-xs font-bold shadow-sm">
                                    <span>{{ $event->event_date->format('M') }}</span>
                                    <span class="text-lg leading-none">{{ $event->event_date->format('d') }}</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-900 group-hover:text-amber-700 transition-colors">
                                        {{ $event->event_name }}</div>
                                    <div class="text-sm text-slate-500 flex items-center gap-2 mt-0.5">
                                        <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg> {{ $event->event_date->format('h:i A') }}</span>
                                        <span class="text-slate-300">•</span>
                                        <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg> {{ Str::limit($event->venue_address, 25) }}</span>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('events.show', $event) }}"
                                class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="text-sm text-slate-500">No upcoming events scheduled.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="space-y-6">
                {{-- Quick Actions --}}
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 text-white shadow-lg">
                    <h3 class="font-bold text-lg mb-1">Quick Actions</h3>
                    <p class="text-slate-400 text-sm mb-4">Manage your catering operations</p>
                    <div class="space-y-2">
                        <a href="{{ route('events.index') }}"
                            class="flex items-center gap-3 p-3 rounded-xl bg-white/10 hover:bg-white/20 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm font-medium">Manage Events</span>
                        </a>
                        <a href="{{ route('waiters.index') }}"
                            class="flex items-center gap-3 p-3 rounded-xl bg-white/10 hover:bg-white/20 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="text-sm font-medium">Staff Management</span>
                        </a>
                        <a href="{{ route('equipment-items.index') }}"
                            class="flex items-center gap-3 p-3 rounded-xl bg-white/10 hover:bg-white/20 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="text-sm font-medium">Inventory</span>
                        </a>
                        <a href="{{ route('reports.index') }}"
                            class="flex items-center gap-3 p-3 rounded-xl bg-white/10 hover:bg-white/20 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                            </svg>
                            <span class="text-sm font-medium">View Reports</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    @else
        {{-- WAITER DASHBOARD --}}
        @php
            $waiter = auth()->user()->waiter;
            $myEvents = $waiter ? $waiter->events()->orderBy('event_date')->get() : collect();
            $pendingConfirmations = $myEvents->where('pivot.confirmation_status', 'pending');
            $confirmedEvents = $myEvents->where('pivot.confirmation_status', 'confirmed');
        @endphp

        <div class="max-w-5xl mx-auto">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Welcome, {{ auth()->user()->name }}!</h1>
                    <p class="text-sm text-slate-500 mt-1">Here's your schedule and assignments.</p>
                </div>
                {{-- Live Clock --}}
                <div class="inline-flex items-center gap-3 bg-white rounded-2xl shadow-sm border border-slate-200 px-5 py-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="text-right">
                        <div id="liveClock" class="text-xl font-bold text-slate-900 tabular-nums tracking-tight leading-none">
                            --:--:--</div>
                        <div id="liveDate" class="text-xs text-slate-500 font-medium mt-0.5">--</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 group hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-slate-900">{{ $myEvents->count() }}</div>
                    <div class="text-sm text-slate-500 font-medium mt-1">Total Assignments</div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 group hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-slate-900">{{ $pendingConfirmations->count() }}</div>
                    <div class="text-sm text-slate-500 font-medium mt-1">Pending</div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 group hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-slate-900">{{ $confirmedEvents->count() }}</div>
                    <div class="text-sm text-slate-500 font-medium mt-1">Confirmed</div>
                </div>
            </div>

            {{-- WAITER ANALYTICS --}}
            @php
                $myEquipment = $waiter ? $waiter->assignedEquipment()->with('equipmentItem')->get() : collect();
                $equipmentStatus = [
                    'returned' => $myEquipment->where('status', 'returned')->count(),
                    'allocated' => $myEquipment->where('status', 'allocated')->count(),
                    'partially_returned' => $myEquipment->where('status', 'partially_returned')->count(),
                    'lost' => $myEquipment->where('status', 'lost')->count(),
                ];
                $eventsByStatus = [
                    $myEvents->where('pivot.confirmation_status', 'confirmed')->count(),
                    $myEvents->where('pivot.confirmation_status', 'pending')->count(),
                    $myEvents->where('pivot.confirmation_status', 'declined')->count(),
                ];
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">My Participation</h2>
                    <canvas id="waiterStatusChart" height="180"></canvas>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">My Equipment Status</h2>
                    <canvas id="waiterEquipmentChart" height="180"></canvas>
                </div>
            </div>

            <script>
                new Chart(document.getElementById('waiterStatusChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Confirmed', 'Pending', 'Declined'],
                        datasets: [{
                            data: {!! json_encode($eventsByStatus) !!},
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        cutout: '60%',
                        plugins: { legend: { position: 'bottom' } }
                    }
                });

                new Chart(document.getElementById('waiterEquipmentChart'), {
                    type: 'bar',
                    data: {
                        labels: ['Returned', 'Allocated', 'Partial', 'Lost'],
                        datasets: [{
                            label: 'Count',
                            data: {!! json_encode(array_values($equipmentStatus)) !!},
                            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            </script>

            @if($pendingConfirmations->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-amber-50 border-b border-amber-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold text-amber-800">Action Required</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($pendingConfirmations as $event)
                            <div class="px-6 py-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                                <div>
                                    <div class="font-semibold text-slate-900">{{ $event->event_name }}</div>
                                    <div class="text-sm text-slate-500">{{ $event->event_date->format('M d, Y h:i A') }}</div>
                                </div>
                                <div class="flex gap-2">
                                    <form action="{{ route('events.confirm', $event) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="confirmed">
                                        <button
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            Confirm
                                        </button>
                                    </form>
                                    <form action="{{ route('events.confirm', $event) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="declined">
                                        <button
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Decline
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-slate-900">My Events</h2>
                    <a href="{{ route('waiters.my-profile') }}"
                        class="inline-flex items-center gap-1 text-sm font-semibold text-amber-700 hover:text-amber-800 transition-colors">
                        View Profile
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($myEvents->sortBy('event_date') as $event)
                        <div
                            class="px-6 py-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 hover:bg-slate-50/60 transition-colors">
                            <div>
                                <a href="{{ route('waiters.my-event', $event) }}"
                                    class="font-semibold text-slate-900 hover:text-amber-700 transition-colors">{{ $event->event_name }}</a>
                                <div class="text-sm text-slate-500 mt-1">
                                    {{ $event->event_date->format('M d, Y h:i A') }} • Role: {{ ucfirst($event->pivot->role) }}
                                </div>
                                <div class="flex items-center gap-3 text-xs text-slate-500 mt-1.5">
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                        </svg>
                                        {{ $event->pivot->time_in ? \Carbon\Carbon::parse($event->pivot->time_in)->format('h:i A') : '—' }}
                                    </span>
                                    <span class="text-slate-300">|</span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        {{ $event->pivot->time_out ? \Carbon\Carbon::parse($event->pivot->time_out)->format('h:i A') : '—' }}
                                    </span>
                                </div>
                            </div>
                            @php
                                $statusConfig = [
                                    'confirmed' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'Confirmed'],
                                    'pending' => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'Pending'],
                                    'declined' => ['bg-rose-50 text-rose-700 ring-rose-600/20', 'Declined'],
                                ];
                                $config = $statusConfig[$event->pivot->confirmation_status] ?? ['bg-slate-50 text-slate-600 ring-slate-500/10', ucfirst($event->pivot->confirmation_status)];
                            @endphp
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset {{ $config[0] }}">
                                {{ $config[1] }}
                            </span>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="text-sm text-slate-500">No events assigned yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const dateString = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });

            document.querySelectorAll('#liveClock').forEach(el => el.textContent = timeString);
            document.querySelectorAll('#liveDate').forEach(el => el.textContent = dateString);
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
@endsection