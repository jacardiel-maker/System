@extends('layouts.app')
@section('title', 'Reports')

@section('content')
    @php
        // Quick numbers shown on each card
        $eventCount = \App\Models\Event::count();
        $allocationCount = \App\Models\EventEquipment::count();
        $issueUnits = (int) \App\Models\EventEquipment::sum('quantity_damaged')
            + (int) \App\Models\EventEquipment::sum('quantity_missing');
        $logCount = class_exists(\App\Models\EquipmentLog::class) ? \App\Models\EquipmentLog::count() : 0;

        $reports = [
            [
                'route' => 'reports.events',
                'icon'  => 'fa-calendar-alt',
                'color' => 'bg-blue-50 text-blue-600',
                'title' => 'Event Reports',
                'desc'  => 'All scheduled, ongoing, completed and cancelled events with date and status filters.',
                'stat'  => number_format($eventCount) . ' ' . Str::plural('event', $eventCount),
            ],
            [
                'route' => 'reports.equipment',
                'icon'  => 'fa-tools',
                'color' => 'bg-emerald-50 text-emerald-600',
                'title' => 'Equipment Usage',
                'desc'  => 'Equipment allocated per event, with returned, damaged, missing and pending counts.',
                'stat'  => number_format($allocationCount) . ' ' . Str::plural('allocation', $allocationCount),
            ],
            [
                'route' => 'reports.accountability',
                'icon'  => 'fa-user-shield',
                'color' => 'bg-orange-50 text-orange-600',
                'title' => 'Accountability',
                'desc'  => 'Staff with pending returns, damaged or missing equipment, ranked by severity.',
                'stat'  => $issueUnits > 0
                    ? number_format($issueUnits) . ' damaged/missing ' . Str::plural('unit', $issueUnits)
                    : 'No damaged or missing units',
            ],
            [
                'route' => 'reports.equipment-logs',
                'icon'  => 'fa-clipboard-list',
                'color' => 'bg-purple-50 text-purple-600',
                'title' => 'Activity Logs',
                'desc'  => 'Every equipment movement — allocated, returned, damaged, missing — with remarks.',
                'stat'  => number_format($logCount) . ' log ' . Str::plural('entry', $logCount),
            ],
        ];
    @endphp

    {{-- Header --}}
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-slate-800 leading-tight">Reports</h1>
        <p class="text-slate-400 text-sm">Analytics and printable reports for your catering operations</p>
    </div>

    {{-- Report cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        @foreach($reports as $r)
            <a href="{{ route($r['route']) }}"
                class="group bg-white rounded-xl border border-slate-100 shadow-sm p-4 flex flex-col hover:border-amber-200 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="w-10 h-10 shrink-0 rounded-lg {{ $r['color'] }} flex items-center justify-center">
                        <i class="fas {{ $r['icon'] }}"></i>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-500 bg-slate-50 px-2 py-0.5 rounded-full whitespace-nowrap">
                        {{ $r['stat'] }}
                    </span>
                </div>

                <h2 class="text-sm font-bold text-slate-800 group-hover:text-amber-700 transition">{{ $r['title'] }}</h2>
                <p class="text-xs text-slate-500 leading-relaxed mt-1 flex-1">{{ $r['desc'] }}</p>

                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                    <span class="text-amber-700 inline-flex items-center gap-1.5">
                        View Report
                        <i class="fas fa-arrow-right text-[10px] transition-transform group-hover:translate-x-1"></i>
                    </span>
                    <span class="text-slate-400 inline-flex items-center gap-1" title="Printable">
                        <i class="fas fa-print text-[10px]"></i> Printable
                    </span>
                </div>
            </a>
        @endforeach
    </div>
@endsection