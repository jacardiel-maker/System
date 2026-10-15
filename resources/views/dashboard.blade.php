@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @if(auth()->user()->isAdmin())
        {{-- ADMIN DASHBOARD (single-screen on desktop) --}}
        @php
            $calendarEvents = \App\Models\Event::orderBy('event_date')->get();
            $calendarEventData = $calendarEvents->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->event_name,
                    'start' => $event->event_date->toIso8601String(),
                    'end' => $event->event_date->toIso8601String(),
                    'status' => $event->status,
                    'time' => $event->event_date->format('h:i A'),
                    'url' => route('events.show', $event),
                ];
            })->values();

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

            $eventsByWeek = \App\Models\Event::select(
                \Illuminate\Support\Facades\DB::raw('YEARWEEK(event_date, 1) as week'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as count')
            )
                ->where('event_date', '>=', now()->subWeeks(7)->startOfWeek())
                ->groupBy('week')->orderBy('week')->pluck('count', 'week')->toArray();
            $weeks = [];
            $weekCounts = [];
            for ($i = 7; $i >= 0; $i--) {
                $date = now()->subWeeks($i)->startOfWeek();
                $key = (int) $date->format('oW');
                $weeks[] = $date->format('M d');
                $weekCounts[] = $eventsByWeek[$key] ?? 0;
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

            // Same rule as the Equipment Items "Low Stock" filter: available below 20% of total
            $lowStockCount = \App\Models\EquipmentItem::where('available_quantity', '>', 0)
                ->whereRaw('available_quantity * 100 < total_quantity * 20')
                ->count();
        @endphp

        <div class="flex flex-col gap-3 lg:h-[calc(100vh-2rem)] lg:min-h-[560px]">

            {{-- ROW A: title + bell + clock --}}
            <div class="shrink-0 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight leading-tight">Dashboard</h1>
                    <p class="text-xs text-slate-500 truncate">Welcome back, here's what's happening today.</p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    {{-- Notification Bell (desktop only; on mobile it lives in the top bar) --}}
                    <a href="{{ route('notifications.index') }}"
                        class="hidden md:flex relative w-11 h-11 items-center justify-center bg-white rounded-xl shadow-sm border border-slate-100 text-slate-500 hover:text-amber-600 hover:shadow-md transition-all">
                        <i class="fas fa-bell"></i>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span
                                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 rounded-full text-[10px] text-white flex items-center justify-center font-bold animate-pulse">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </a>

                    {{-- Live Clock --}}
                    <div class="inline-flex items-center gap-2.5 bg-white rounded-xl shadow-sm border border-slate-100 px-3 h-11">
                        <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-right">
                            <div id="liveClock" class="text-base font-bold text-slate-900 tabular-nums tracking-tight leading-none">--:--:--</div>
                            <div id="liveDate" class="text-[10px] text-slate-500 font-medium mt-0.5">--</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ROW B: stat cards --}}
            <div class="shrink-0 grid grid-cols-2 lg:grid-cols-4 gap-3">
                <a href="{{ route('events.index') }}" class="group bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3 hover:border-amber-200 hover:shadow-md transition">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $stats['total_events'] }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Total Events</div>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:text-amber-500 ml-auto transition"></i>
                </a>

                <a href="{{ route('events.index', ['status' => 'scheduled']) }}" class="group bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3 hover:border-amber-200 hover:shadow-md transition">
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $stats['upcoming_events'] }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Upcoming Events</div>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:text-amber-500 ml-auto transition"></i>
                </a>

                <a href="{{ route('waiters.index') }}" class="group bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3 hover:border-amber-200 hover:shadow-md transition">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $stats['total_waiters'] }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Active Waiters</div>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:text-amber-500 ml-auto transition"></i>
                </a>

                <a href="{{ route('equipment-items.index', ['stock' => 'low']) }}" class="group bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3 hover:border-amber-200 hover:shadow-md transition">
                    <div class="w-9 h-9 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $lowStockCount }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Low Stock Items</div>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:text-amber-500 ml-auto transition"></i>
                </a>
            </div>

            {{-- ROW C: upcoming events (left) + calendar (right) --}}
            <div class="lg:flex-1 lg:min-h-0 grid grid-cols-1 lg:grid-cols-12 lg:grid-rows-[minmax(0,1fr)] gap-3">

                {{-- Upcoming Events --}}
                <div class="lg:col-span-4 lg:min-h-0 max-h-[360px] lg:max-h-none bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                    <div class="shrink-0 px-4 py-2.5 border-b border-slate-100 flex justify-between items-center gap-2">
                        <div class="min-w-0 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 inline-flex items-center justify-center shrink-0"><i class="fas fa-calendar-day text-xs"></i></span>
                            <div class="min-w-0">
                                <h2 class="text-sm font-bold text-slate-900 leading-tight">Upcoming Events</h2>
                                <p class="text-[11px] text-slate-500">Scheduled in the near future</p>
                            </div>
                        </div>
                        <a href="{{ route('events.create') }}"
                            class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-700 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            New
                        </a>
                    </div>
                    <div class="flex-1 min-h-0 overflow-y-auto divide-y divide-slate-100">
                        @forelse($upcomingEvents as $event)
                            <div class="px-4 py-2 flex justify-between items-center gap-2 group hover:bg-slate-50/60 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="w-10 h-10 shrink-0 rounded-lg bg-gradient-to-br from-amber-400 to-orange-400 text-white flex flex-col items-center justify-center text-[10px] font-bold shadow-sm">
                                        <span>{{ $event->event_date->format('M') }}</span>
                                        <span class="text-sm leading-none">{{ $event->event_date->format('d') }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-slate-900 group-hover:text-amber-700 transition-colors truncate">
                                            {{ $event->event_name }}</div>
                                        <div class="text-[11px] text-slate-500 truncate">
                                            {{ $event->event_date->format('h:i A') }} • {{ Str::limit($event->venue_address, 25) }}
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('events.show', $event) }}"
                                    class="shrink-0 w-7 h-7 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-center">
                                <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-sm text-slate-500">No upcoming events scheduled.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Calendar --}}
                <div class="lg:col-span-8 lg:min-h-0 fit-cal bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                    <div class="shrink-0 px-4 py-2 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="flex items-center gap-2"><span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 inline-flex items-center justify-center shrink-0"><i class="fas fa-calendar-alt text-xs"></i></span><h2 class="text-sm font-bold text-slate-900">Event Calendar</h2></div>
                        <div class="flex items-center gap-2">
                            <select id="calendarView" onchange="calendarSetView(this.value)" class="h-8 text-xs font-semibold text-slate-600 border border-slate-200 rounded-lg px-2 bg-white hover:bg-slate-50 cursor-pointer">
                                <option value="month">Month</option>
                                <option value="week">Week</option>
                                <option value="day">Day</option>
                            </select>
                            <button type="button" onclick="calendarToday()" class="h-8 px-3 text-xs font-semibold text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50">Today</button>
                            <button type="button" onclick="calendarPrev()" class="w-8 h-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Previous"><i class="fas fa-chevron-left text-xs"></i></button>
                            <button type="button" onclick="calendarNext()" class="w-8 h-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Next"><i class="fas fa-chevron-right text-xs"></i></button>
                            <div id="calendarMonthLabel" class="min-w-[140px] text-right text-sm font-bold text-slate-800"></div>
                        </div>
                    </div>

                    <div class="flex-1 min-h-0 flex flex-col px-3 py-2">
                        <div id="calendarFilters" class="shrink-0 flex flex-wrap items-center gap-1.5 mb-2"></div>
                        <div class="flex-1 min-h-0 grid grid-cols-7 content-start overflow-y-auto border-l border-t border-slate-200 rounded-lg">
                            <div id="calendarWeekdays" class="contents">
                                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dayName)
                                    <div class="sticky top-0 z-10 bg-slate-50 border-r border-b border-slate-200 px-2 py-1 text-[10px] sm:text-xs font-bold text-slate-500 text-center uppercase tracking-wide">{{ $dayName }}</div>
                                @endforeach
                            </div>
                            <div id="calendarGrid" class="col-span-7 grid grid-cols-7"></div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                window.dashboardCalendarEvents = @json($calendarEventData);
            </script>

            {{-- ROW D: charts --}}
            <div class="shrink-0 grid grid-cols-2 lg:grid-cols-7 gap-3 lg:h-[165px]">
                {{-- Events Trend --}}
                <div class="col-span-2 bg-white rounded-xl p-3 shadow-sm border border-slate-100 h-[190px] lg:h-full flex flex-col">
                    <div class="shrink-0 flex items-center justify-between gap-2 mb-1">
                        <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wide">Events Trend</h2>
                        <select onchange="updateEventsTrend(this.value)" class="text-[10px] font-semibold border border-slate-200 rounded-md px-1.5 py-0.5 text-slate-600 bg-white">
                            <option value="monthly">Monthly</option>
                            <option value="weekly">Weekly</option>
                        </select>
                    </div>
                    <div class="relative flex-1 min-h-0"><canvas id="eventsChart"></canvas></div>
                </div>

                {{-- Event Status --}}
                <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 h-[190px] lg:h-full flex flex-col">
                    <h2 class="shrink-0 text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Event Status</h2>
                    <div class="relative flex-1 min-h-0"><canvas id="statusChart"></canvas></div>
                </div>

                {{-- Equipment by Category --}}
                <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 h-[190px] lg:h-full flex flex-col">
                    <h2 class="shrink-0 text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Equipment by Category</h2>
                    <div class="relative flex-1 min-h-0"><canvas id="categoryChart"></canvas></div>
                </div>

                {{-- Equipment Condition --}}
                <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 h-[190px] lg:h-full flex flex-col">
                    <h2 class="shrink-0 text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Equipment Condition</h2>
                    <div class="relative flex-1 min-h-0"><canvas id="conditionChart"></canvas></div>
                </div>

                {{-- Most Used Equipment --}}
                <div class="col-span-2 lg:col-span-2 bg-white rounded-xl p-3 shadow-sm border border-slate-100 h-[190px] lg:h-full flex flex-col">
                    <h2 class="shrink-0 text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">Most Used Equipment</h2>
                    <div class="relative flex-1 min-h-0"><canvas id="topEquipmentChart"></canvas></div>
                </div>
            </div>
        </div>

        <script>
            const smallLegend = { position: 'right', labels: { boxWidth: 8, boxHeight: 8, padding: 6, font: { size: 9 } } };

            // Events Trend (Weekly / Monthly)
            const eventsTrendData = {
                weekly: { labels: {!! json_encode($weeks) !!}, data: {!! json_encode($weekCounts) !!} },
                monthly: { labels: {!! json_encode($months) !!}, data: {!! json_encode($monthCounts) !!} }
            };

            const eventsChart = new Chart(document.getElementById('eventsChart'), {
                type: 'line',
                data: { labels: eventsTrendData.monthly.labels, datasets: [{
                    label: 'Number of Events', data: eventsTrendData.monthly.data,
                    borderColor: '#d97706', backgroundColor: 'rgba(217, 119, 6, 0.1)',
                    fill: true, tension: 0.35, pointRadius: 3, pointBackgroundColor: '#d97706'
                }]},
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 }, padding: 4 } },
                        x: { ticks: { font: { size: 9 }, padding: 1 } }
                    }
                }
            });
            window.updateEventsTrend = function(range) {
                eventsChart.data.labels = eventsTrendData[range].labels;
                eventsChart.data.datasets[0].data = eventsTrendData[range].data;
                eventsChart.update();
            };

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
                options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: smallLegend } }
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
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { font: { size: 9 } } },
                        x: { ticks: { font: { size: 8 }, maxRotation: 30 } }
                    }
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
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: smallLegend } }
            });

            // Most Used Equipment (Horizontal Bar)
            const topEquipmentCanvas = document.getElementById('topEquipmentChart');
            if (topEquipmentCanvas) {
                new Chart(topEquipmentCanvas, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode(array_keys($topEquipment)) !!},
                        datasets: [{
                            label: 'Units Allocated',
                            data: {!! json_encode(array_values($topEquipment)) !!},
                            backgroundColor: '#d97706',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, ticks: { font: { size: 9 } } },
                            y: {
                                ticks: {
                                    autoSkip: false,
                                    font: { size: 9 },
                                    callback: function (value) {
                                        const label = String(this.getLabelForValue(value));
                                        const lines = [];
                                        let line = '';
                                        label.split(' ').forEach(function (word) {
                                            if ((line + ' ' + word).trim().length > 16 && line) {
                                                lines.push(line);
                                                line = word;
                                            } else {
                                                line = (line + ' ' + word).trim();
                                            }
                                        });
                                        if (line) lines.push(line);
                                        return lines;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        </script>

    @else
        {{-- WAITER DASHBOARD (single-screen on desktop) --}}
        @php
            $waiter = auth()->user()->waiter;
            $myEvents = $waiter ? $waiter->events()->orderBy('event_date')->get() : collect();
            $pendingConfirmations = $myEvents->where('pivot.confirmation_status', 'pending');
            $confirmedEvents = $myEvents->where('pivot.confirmation_status', 'confirmed');

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

            $calendarEvents = $myEvents->sortBy('event_date');
            $calendarEventData = $calendarEvents->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->event_name,
                    'start' => $event->event_date->toIso8601String(),
                    'end' => $event->event_date->toIso8601String(),
                    'status' => $event->status,
                    'time' => $event->event_date->format('h:i A'),
                    'url' => route('waiters.my-event', $event),
                ];
            })->values();
        @endphp

        <div class="flex flex-col gap-3 lg:h-[calc(100vh-2rem)] lg:min-h-[560px]">

            {{-- ROW A: welcome + bell + clock --}}
            <div class="shrink-0 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight leading-tight truncate">Welcome, {{ auth()->user()->name }}!</h1>
                    <p class="text-xs text-slate-500">Here's your schedule and assignments.</p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    {{-- Notification Bell (desktop only; on mobile it lives in the top bar) --}}
                    <a href="{{ route('notifications.index') }}"
                        class="hidden md:flex relative w-11 h-11 items-center justify-center bg-white rounded-xl shadow-sm border border-slate-100 text-slate-500 hover:text-amber-600 hover:shadow-md transition-all">
                        <i class="fas fa-bell"></i>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span
                                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 rounded-full text-[10px] text-white flex items-center justify-center font-bold animate-pulse">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </a>

                    {{-- Live Clock --}}
                    <div class="inline-flex items-center gap-2.5 bg-white rounded-xl shadow-sm border border-slate-100 px-3 h-11">
                        <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-right">
                            <div id="liveClock" class="text-base font-bold text-slate-900 tabular-nums tracking-tight leading-none">--:--:--</div>
                            <div id="liveDate" class="text-[10px] text-slate-500 font-medium mt-0.5">--</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ROW B: stat cards --}}
            <div class="shrink-0 grid grid-cols-3 gap-3">
                <div class="bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $myEvents->count() }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Total Assignments</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $pendingConfirmations->count() }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Pending</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $confirmedEvents->count() }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Confirmed</div>
                    </div>
                </div>
            </div>

            {{-- ROW C: calendar (left) + action required / my events (right) --}}
            <div class="lg:flex-1 lg:min-h-0 grid grid-cols-1 lg:grid-cols-12 lg:grid-rows-[minmax(0,1fr)] gap-3">

                {{-- Calendar --}}
                <div class="lg:col-span-7 lg:min-h-0 fit-cal bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                    <div class="shrink-0 px-4 py-2 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="flex items-center gap-2"><span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 inline-flex items-center justify-center shrink-0"><i class="fas fa-calendar-alt text-xs"></i></span><h2 class="text-sm font-bold text-slate-900">My Event Calendar</h2></div>
                        <div class="flex items-center gap-2">
                            <select id="calendarView" onchange="calendarSetView(this.value)" class="h-8 text-xs font-semibold text-slate-600 border border-slate-200 rounded-lg px-2 bg-white hover:bg-slate-50 cursor-pointer">
                                <option value="month">Month</option>
                                <option value="week">Week</option>
                                <option value="day">Day</option>
                            </select>
                            <button type="button" onclick="calendarToday()" class="h-8 px-3 text-xs font-semibold text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50">Today</button>
                            <button type="button" onclick="calendarPrev()" class="w-8 h-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Previous"><i class="fas fa-chevron-left text-xs"></i></button>
                            <button type="button" onclick="calendarNext()" class="w-8 h-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Next"><i class="fas fa-chevron-right text-xs"></i></button>
                            <div id="calendarMonthLabel" class="min-w-[140px] text-right text-sm font-bold text-slate-800"></div>
                        </div>
                    </div>

                    <div class="flex-1 min-h-0 flex flex-col px-3 py-2">
                        <div id="calendarFilters" class="shrink-0 flex flex-wrap items-center gap-1.5 mb-2"></div>
                        <div class="flex-1 min-h-0 grid grid-cols-7 content-start overflow-y-auto border-l border-t border-slate-200 rounded-lg">
                            <div id="calendarWeekdays" class="contents">
                                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dayName)
                                    <div class="sticky top-0 z-10 bg-slate-50 border-r border-b border-slate-200 px-2 py-1 text-[10px] sm:text-xs font-bold text-slate-500 text-center uppercase tracking-wide">{{ $dayName }}</div>
                                @endforeach
                            </div>
                            <div id="calendarGrid" class="col-span-7 grid grid-cols-7"></div>
                        </div>
                    </div>
                </div>

                <script>
                    window.dashboardCalendarEvents = @json($calendarEventData);
                </script>

                {{-- Right column --}}
                <div class="lg:col-span-5 lg:min-h-0 flex flex-col gap-3">

                    @if($pendingConfirmations->count() > 0)
                        <div class="shrink-0 max-h-[40%] bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                            <div class="shrink-0 px-4 py-2 bg-amber-50 border-b border-amber-100 flex items-center gap-2">
                                <div class="w-6 h-6 rounded-md bg-amber-100 flex items-center justify-center text-amber-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <h2 class="text-sm font-bold text-amber-800">Action Required</h2>
                            </div>
                            <div class="min-h-0 overflow-y-auto divide-y divide-slate-100">
                                @foreach($pendingConfirmations as $event)
                                    <div class="px-4 py-2 flex items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-slate-900 truncate">{{ $event->event_name }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $event->event_date->format('M d, Y h:i A') }}</div>
                                        </div>
                                        <div class="flex gap-1.5 shrink-0">
                                            <button type="button"
                                                onclick="openConfirmDialog({{ $event->id }}, {{ json_encode($event->event_name) }}, 'confirmed')"
                                                class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Confirm
                                            </button>
                                            <button type="button"
                                                onclick="openConfirmDialog({{ $event->id }}, {{ json_encode($event->event_name) }}, 'declined')"
                                                class="inline-flex items-center gap-1 rounded-md bg-rose-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-rose-700 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Decline
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- My Events --}}
                    <div class="flex-1 min-h-0 max-h-[360px] lg:max-h-none bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                        <div class="shrink-0 px-4 py-2.5 border-b border-slate-100 flex justify-between items-center">
                            <div class="flex items-center gap-2"><span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 inline-flex items-center justify-center shrink-0"><i class="fas fa-list-ul text-xs"></i></span><h2 class="text-sm font-bold text-slate-900">My Events</h2></div>
                            <a href="{{ route('waiters.my-profile') }}"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 hover:text-amber-800 transition-colors">
                                View Profile
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                        <div class="flex-1 min-h-0 overflow-y-auto divide-y divide-slate-100">
                            @forelse($myEvents->sortBy('event_date') as $event)
                                <div class="px-4 py-2 flex items-center justify-between gap-2 hover:bg-slate-50/60 transition-colors">
                                    <div class="min-w-0">
                                        <a href="{{ route('waiters.my-event', $event) }}"
                                            class="text-sm font-semibold text-slate-900 hover:text-amber-700 transition-colors truncate block">{{ $event->event_name }}</a>
                                        <div class="text-[11px] text-slate-500 truncate">
                                            {{ $event->event_date->format('M d, Y h:i A') }} • {{ ucfirst(str_replace('_', ' ', $event->pivot->role)) }}
                                            {{-- Time in / out only applies once the waiter has confirmed --}}
                                            @if(strtolower((string) $event->pivot->confirmation_status) === 'confirmed')
                                                • In: {{ $event->pivot->time_in ? \Carbon\Carbon::parse($event->pivot->time_in)->format('h:i A') : '—' }}
                                                • Out: {{ $event->pivot->time_out ? \Carbon\Carbon::parse($event->pivot->time_out)->format('h:i A') : '—' }}
                                            @endif
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
                                    <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 ring-inset {{ $config[0] }}">
                                        {{ $config[1] }}
                                    </span>
                                </div>
                            @empty
                                <div class="px-6 py-10 text-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            </div>

            {{-- ROW D: charts --}}
            <div class="shrink-0 grid grid-cols-1 sm:grid-cols-2 gap-3 lg:h-[165px]">
                <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 h-[190px] lg:h-full flex flex-col">
                    <h2 class="shrink-0 text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">My Participation</h2>
                    <div class="relative flex-1 min-h-0"><canvas id="waiterStatusChart"></canvas></div>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 h-[190px] lg:h-full flex flex-col">
                    <h2 class="shrink-0 text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">My Equipment Status</h2>
                    <div class="relative flex-1 min-h-0"><canvas id="waiterEquipmentChart"></canvas></div>
                </div>
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
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: { legend: { position: 'right', labels: { boxWidth: 8, boxHeight: 8, padding: 8, font: { size: 10 } } } }
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
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } } },
                        x: { ticks: { font: { size: 9 } } }
                    }
                }
            });
        </script>

        {{-- Hidden Forms for Dialog Submission --}}
        @foreach($pendingConfirmations as $event)
            <form id="confirm-form-{{ $event->id }}" action="{{ route('events.confirm', $event) }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="status" value="confirmed">
            </form>
            <form id="decline-form-{{ $event->id }}" action="{{ route('events.confirm', $event) }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="status" value="declined">
                <input type="hidden" name="decline_reason" class="decline-reason-input">
            </form>
        @endforeach

        {{-- Confirmation Dialog Modal --}}
        <div id="confirmModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 transform transition-all">
                <div class="text-center mb-6">
                    <div id="modalIcon" class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl">
                        <!-- Icon injected by JS -->
                    </div>
                    <h3 id="modalTitle" class="text-xl font-bold text-slate-900 mb-2">Are you sure?</h3>
                    <p id="modalMessage" class="text-slate-500 text-sm">Please confirm your action.</p>
                </div>

                <div class="bg-slate-50 rounded-xl p-4 mb-6">
                    <div class="text-sm text-slate-500 mb-1">Event</div>
                    <div id="modalEventName" class="font-semibold text-slate-800 text-lg"></div>
                    <div id="modalActionText" class="text-sm mt-2 font-medium"></div>
                </div>

                {{-- Reason (only shown when declining) --}}
                <div id="declineReasonBox" class="hidden mb-6">
                    <label for="declineReason" class="block text-xs font-semibold text-slate-700 mb-1">
                        Reason for declining <span class="text-red-500">*</span>
                    </label>
                    <textarea id="declineReason" rows="3" maxlength="500"
                        placeholder="e.g. I have a class on that day / I am sick / family emergency"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent"></textarea>
                    <div class="flex justify-between mt-1">
                        <p id="declineReasonError" class="hidden text-xs text-red-600 font-medium">Please enter at least 5 characters.</p>
                        <p class="text-[11px] text-slate-400 ml-auto"><span id="declineReasonCount">0</span>/500</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeConfirmDialog()" class="flex-1 px-4 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 font-medium transition">
                        Cancel
                    </button>
                    <button type="button" id="modalConfirmBtn" class="flex-1 px-4 py-2.5 text-white rounded-xl font-medium transition">
                        Yes, I'm Sure
                    </button>
                </div>
            </div>
        </div>
    @endif


    <style>
        #calendarGrid > .calendar-cell {
            min-height: 72px;
        }
        .calendar-cell {
            position: relative;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 3px;
            background: #fff;
        }
        .calendar-cell.clickable { cursor: pointer; }
        .calendar-cell.clickable:hover { background: #fffbeb; }
        .calendar-cell.muted {
            background: #f8fafc;
        }
        .calendar-cell.today {
            background: #fffdf5;
        }
        .calendar-day {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            border-radius: 9999px;
            margin-bottom: 3px;
        }
        .calendar-cell.today .calendar-day {
            background: #f59e0b;
            color: white;
        }
        .calendar-event {
            display: block;
            width: 100%;
            text-align: left;
            border-radius: 5px;
            padding: 2px 4px;
            margin-top: 2px;
            font-size: 9px;
            line-height: 1.1;
            font-weight: 600;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            text-decoration: none;
        }
        #calendarGrid > .calendar-cell.tall { min-height: 160px; }
        .calendar-event.large {
            font-size: 12px;
            line-height: 1.3;
            padding: 6px 8px;
            margin-top: 4px;
            white-space: normal;
        }
        .calendar-event.scheduled { background:#dbeafe; color:#1d4ed8; }
        .calendar-event.ongoing { background:#f3e8ff; color:#7e22ce; }
        .calendar-event.completed { background:#dcfce7; color:#15803d; }
        .calendar-event.cancelled { background:#f1f5f9; color:#475569; }

        .calendar-more {
            display: block;
            font-size: 9px;
            font-weight: 600;
            color: #d97706;
            margin-top: 2px;
            padding: 0 4px;
            background: none;
            border: 0;
            cursor: pointer;
        }
        .calendar-more:hover { text-decoration: underline; }

        .cal-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            border: 1px solid #e2e8f0;
            background: #fff;
            font-size: 11px;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            transition: all .15s;
        }
        .cal-chip:hover { background: #f8fafc; }
        .cal-chip .dot { width: 8px; height: 8px; border-radius: 9999px; }
        .cal-chip .count { font-size: 10px; color: #94a3b8; font-weight: 700; }
        .cal-chip.off { opacity: .45; }
        .cal-chip.off .label { text-decoration: line-through; }
        .cal-chip.all { background: #f1f5f9; }

        @media (min-width: 1024px) {
            .fit-cal #calendarGrid { grid-auto-rows: minmax(72px, auto); }
            .fit-cal #calendarGrid > .calendar-cell.tall { overflow-y: auto; }
        }
    </style>
    <script>
        (function () {
            const events = window.dashboardCalendarEvents || [];

            const statusList = [
                { key: 'scheduled', label: 'Scheduled', color: '#3b82f6' },
                { key: 'ongoing',   label: 'Ongoing',   color: '#a855f7' },
                { key: 'completed', label: 'Completed', color: '#10b981' },
                { key: 'cancelled', label: 'Cancelled', color: '#64748b' },
            ];
            const statusKeys = statusList.map(s => s.key);
            const activeStatuses = new Set(statusKeys);

            // Normalize status (case/spacing) and treat unknown values as "scheduled"
            // so no event silently disappears when "All" is selected.
            function eventStatus(event) {
                const s = String(event.status || 'scheduled').trim().toLowerCase();
                return statusKeys.includes(s) ? s : 'scheduled';
            }

            function renderFilters() {
                const box = document.getElementById('calendarFilters');
                if (!box) return;

                box.innerHTML = '';

                const allOn = activeStatuses.size === statusList.length;
                const allBtn = document.createElement('button');
                allBtn.type = 'button';
                allBtn.className = 'cal-chip all' + (allOn ? '' : ' off');
                allBtn.innerHTML = '<span class="label">All</span><span class="count">' + events.length + '</span>';
                allBtn.addEventListener('click', () => {
                    statusList.forEach(s => activeStatuses.add(s.key));
                    renderFilters();
                    renderCalendar();
                });
                box.appendChild(allBtn);

                statusList.forEach(s => {
                    const count = events.filter(e => eventStatus(e) === s.key).length;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'cal-chip' + (activeStatuses.has(s.key) ? '' : ' off');
                    btn.innerHTML =
                        '<span class="dot" style="background:' + s.color + '"></span>' +
                        '<span class="label">' + s.label + '</span>' +
                        '<span class="count">' + count + '</span>';
                    btn.addEventListener('click', () => {
                        if (activeStatuses.has(s.key)) {
                            activeStatuses.delete(s.key);
                        } else {
                            activeStatuses.add(s.key);
                        }
                        renderFilters();
                        renderCalendar();
                    });
                    box.appendChild(btn);
                });
            }
            let view = 'month';
            let current = new Date();
            current.setHours(0, 0, 0, 0);

            function dateKey(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            function addDays(date, n) {
                const x = new Date(date);
                x.setDate(x.getDate() + n);
                return x;
            }

            function startOfWeek(date) {
                return addDays(date, -date.getDay());
            }

            function eventsFor(date) {
                const key = dateKey(date);
                return events.filter(ev =>
                    activeStatuses.has(eventStatus(ev)) && dateKey(new Date(ev.start)) === key
                );
            }

            function openDay(date) {
                current = new Date(date);
                current.setHours(0, 0, 0, 0);
                view = 'day';
                renderCalendar();
            }

            function buildEventLink(ev, large) {
                const status = eventStatus(ev);
                const link = document.createElement('a');
                link.href = ev.url;
                link.className = 'calendar-event ' + status + (large ? ' large' : '');
                link.title = `${ev.title} • ${ev.time}`;
                link.textContent = large
                    ? `${ev.time} — ${ev.title} (${status})`
                    : `${ev.time} ${ev.title}`;
                link.addEventListener('click', e => e.stopPropagation());
                return link;
            }

            function buildCell(date, opts) {
                opts = opts || {};
                const cell = document.createElement('div');
                cell.className = 'calendar-cell' + (opts.muted ? ' muted' : '') + (opts.tall ? ' tall' : '');

                if (dateKey(date) === dateKey(new Date())) {
                    cell.classList.add('today');
                }

                const day = document.createElement('div');
                day.className = 'calendar-day';
                day.textContent = date.getDate();
                cell.appendChild(day);

                const dayEvents = eventsFor(date);
                const max = opts.max || 2;
                dayEvents.slice(0, max).forEach(ev => cell.appendChild(buildEventLink(ev, false)));

                if (dayEvents.length > max) {
                    const more = document.createElement('button');
                    more.type = 'button';
                    more.className = 'calendar-more';
                    more.textContent = `+${dayEvents.length - max} more`;
                    more.addEventListener('click', e => { e.stopPropagation(); openDay(date); });
                    cell.appendChild(more);
                }

                // Month view: click a day to open it in Day view
                if (!opts.tall) {
                    cell.classList.add('clickable');
                    cell.addEventListener('click', () => openDay(date));
                }
                return cell;
            }

            function updateLabel() {
                const label = document.getElementById('calendarMonthLabel');
                if (!label) return;

                if (view === 'month') {
                    label.textContent = current.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                } else if (view === 'week') {
                    const start = startOfWeek(current);
                    const end = addDays(start, 6);
                    const short = { month: 'short', day: 'numeric' };
                    if (start.getFullYear() !== end.getFullYear()) {
                        label.textContent = `${start.toLocaleDateString('en-US', { ...short, year: 'numeric' })} – ${end.toLocaleDateString('en-US', { ...short, year: 'numeric' })}`;
                    } else if (start.getMonth() !== end.getMonth()) {
                        label.textContent = `${start.toLocaleDateString('en-US', short)} – ${end.toLocaleDateString('en-US', short)}, ${end.getFullYear()}`;
                    } else {
                        label.textContent = `${start.toLocaleDateString('en-US', short)} – ${end.getDate()}, ${end.getFullYear()}`;
                    }
                } else {
                    label.textContent = current.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                }
            }

            function renderCalendar() {
                const grid = document.getElementById('calendarGrid');
                const weekdays = document.getElementById('calendarWeekdays');
                if (!grid) return;

                updateLabel();
                grid.innerHTML = '';

                const select = document.getElementById('calendarView');
                if (select) select.value = view;

                if (view === 'day') {
                    if (weekdays) weekdays.style.display = 'none';
                    grid.className = 'col-span-7 grid grid-cols-1';

                    const cell = document.createElement('div');
                    cell.className = 'calendar-cell tall' + (dateKey(current) === dateKey(new Date()) ? ' today' : '');

                    const dayEvents = eventsFor(current);
                    if (dayEvents.length === 0) {
                        const empty = document.createElement('div');
                        empty.className = 'text-xs text-slate-400 p-3';
                        empty.textContent = 'No events on this day.';
                        cell.appendChild(empty);
                    } else {
                        dayEvents.forEach(ev => cell.appendChild(buildEventLink(ev, true)));
                    }
                    grid.appendChild(cell);
                    return;
                }

                if (weekdays) weekdays.style.display = 'contents';
                grid.className = 'col-span-7 grid grid-cols-7';

                if (view === 'week') {
                    const start = startOfWeek(current);
                    for (let i = 0; i < 7; i++) {
                        grid.appendChild(buildCell(addDays(start, i), { tall: true, max: 10 }));
                    }
                    return;
                }

                // month view
                const first = new Date(current.getFullYear(), current.getMonth(), 1);
                const start = startOfWeek(first);
                for (let i = 0; i < 42; i++) {
                    const date = addDays(start, i);
                    grid.appendChild(buildCell(date, { muted: date.getMonth() !== current.getMonth() }));
                }
            }

            function shiftMonth(delta) {
                const targetFirst = new Date(current.getFullYear(), current.getMonth() + delta, 1);
                const daysInTarget = new Date(targetFirst.getFullYear(), targetFirst.getMonth() + 1, 0).getDate();
                current = new Date(targetFirst.getFullYear(), targetFirst.getMonth(), Math.min(current.getDate(), daysInTarget));
            }

            function shift(delta) {
                if (view === 'month') shiftMonth(delta);
                else if (view === 'week') current = addDays(current, delta * 7);
                else current = addDays(current, delta);
                renderCalendar();
            }

            window.calendarPrev = function () { shift(-1); };
            window.calendarNext = function () { shift(1); };

            window.calendarToday = function () {
                current = new Date();
                current.setHours(0, 0, 0, 0);
                renderCalendar();
            };

            window.calendarSetView = function (v) {
                view = ['month', 'week', 'day'].includes(v) ? v : 'month';
                renderCalendar();
            };

            function initCalendar() {
                renderFilters();
                renderCalendar();
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCalendar);
            } else {
                initCalendar();
            }
        })();
    </script>

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

        let currentFormId = null;

        function openConfirmDialog(eventId, eventName, action) {
            const modal = document.getElementById('confirmModal');
            const eventNameEl = document.getElementById('modalEventName');
            const actionTextEl = document.getElementById('modalActionText');
            const iconEl = document.getElementById('modalIcon');
            const titleEl = document.getElementById('modalTitle');
            const messageEl = document.getElementById('modalMessage');
            const confirmBtn = document.getElementById('modalConfirmBtn');

            eventNameEl.textContent = eventName;
            currentFormId = (action === 'confirmed' ? 'confirm-form-' : 'decline-form-') + eventId;

            const reasonBox = document.getElementById('declineReasonBox');
            const reasonEl = document.getElementById('declineReason');
            document.getElementById('declineReasonError').classList.add('hidden');
            if (action === 'declined') {
                reasonBox.classList.remove('hidden');
                reasonEl.value = '';
                document.getElementById('declineReasonCount').textContent = '0';
                setTimeout(() => reasonEl.focus(), 50);
            } else {
                reasonBox.classList.add('hidden');
            }

            if (action === 'confirmed') {
                iconEl.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center bg-emerald-100 text-emerald-600';
                iconEl.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                titleEl.textContent = 'Confirm Participation?';
                messageEl.textContent = 'You are about to confirm your attendance for this event.';
                actionTextEl.textContent = 'Action: CONFIRM participation';
                actionTextEl.className = 'text-sm mt-2 font-medium text-emerald-600';
                confirmBtn.className = 'flex-1 px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 transition';
            } else {
                iconEl.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center bg-red-100 text-red-600';
                iconEl.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                titleEl.textContent = 'Decline Participation?';
                messageEl.textContent = 'The owner will be notified that you cannot attend. Please tell them why.';
                actionTextEl.textContent = 'Action: DECLINE participation';
                actionTextEl.className = 'text-sm mt-2 font-medium text-red-600';
                confirmBtn.className = 'flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition';
            }

            modal.classList.remove('hidden');
        }

        function closeConfirmDialog() {
            const modal = document.getElementById('confirmModal');
            if (modal) modal.classList.add('hidden');
            currentFormId = null;
        }

        // Modal only exists on the waiter dashboard, so guard against null on admin.
        const modalConfirmBtn = document.getElementById('modalConfirmBtn');
        if (modalConfirmBtn) {
            const reasonEl = document.getElementById('declineReason');
            reasonEl.addEventListener('input', function () {
                document.getElementById('declineReasonCount').textContent = this.value.length;
                if (this.value.trim().length >= 5) document.getElementById('declineReasonError').classList.add('hidden');
            });

            modalConfirmBtn.addEventListener('click', function () {
                if (!currentFormId) return;
                const form = document.getElementById(currentFormId);

                // Declining needs a reason
                if (currentFormId.startsWith('decline-form-')) {
                    const text = reasonEl.value.trim();
                    if (text.length < 5) {
                        document.getElementById('declineReasonError').classList.remove('hidden');
                        reasonEl.focus();
                        return;
                    }
                    form.querySelector('.decline-reason-input').value = text;
                }

                this.disabled = true;
                this.textContent = 'Saving...';
                form.submit();
            });
        }

        const confirmModalEl = document.getElementById('confirmModal');
        if (confirmModalEl) {
            // Close modal on backdrop click
            confirmModalEl.addEventListener('click', function (e) {
                if (e.target === this) {
                    closeConfirmDialog();
                }
            });
        }

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeConfirmDialog();
            }
        });
    </script>
@endsection