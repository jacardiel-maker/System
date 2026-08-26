@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-800">Reports</h1>
    <p class="text-slate-400 mt-1">Analytics and insights for your catering operations</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <a href="{{ route('reports.events') }}" class="bg-white rounded-2xl p-6 card-hover border border-slate-100 group">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center text-2xl shadow-lg shadow-blue-200 mb-5 group-hover:scale-110 transition-transform">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <h2 class="text-lg font-bold text-slate-800 mb-1">Event Reports</h2>
        <p class="text-slate-400 text-sm leading-relaxed">View all scheduled and completed events with date filters.</p>
        <div class="mt-4 flex items-center text-blue-600 text-sm font-semibold group-hover:translate-x-1 transition-transform">
            View Report <i class="fas fa-arrow-right ml-2 text-xs"></i>
        </div>
    </a>

    <a href="{{ route('reports.equipment') }}" class="bg-white rounded-2xl p-6 card-hover border border-slate-100 group">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white flex items-center justify-center text-2xl shadow-lg shadow-emerald-200 mb-5 group-hover:scale-110 transition-transform">
            <i class="fas fa-tools"></i>
        </div>
        <h2 class="text-lg font-bold text-slate-800 mb-1">Equipment Usage</h2>
        <p class="text-slate-400 text-sm leading-relaxed">Track equipment allocation and returns per event.</p>
        <div class="mt-4 flex items-center text-emerald-600 text-sm font-semibold group-hover:translate-x-1 transition-transform">
            View Report <i class="fas fa-arrow-right ml-2 text-xs"></i>
        </div>
    </a>

    <a href="{{ route('reports.accountability') }}" class="bg-white rounded-2xl p-6 card-hover border border-slate-100 group">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 text-white flex items-center justify-center text-2xl shadow-lg shadow-orange-200 mb-5 group-hover:scale-110 transition-transform">
            <i class="fas fa-user-shield"></i>
        </div>
        <h2 class="text-lg font-bold text-slate-800 mb-1">Accountability</h2>
        <p class="text-slate-400 text-sm leading-relaxed">Identify staff with missing or damaged equipment.</p>
        <div class="mt-4 flex items-center text-orange-600 text-sm font-semibold group-hover:translate-x-1 transition-transform">
            View Report <i class="fas fa-arrow-right ml-2 text-xs"></i>
        </div>
    </a>

    <a href="{{ route('reports.equipment-logs') }}" class="bg-white rounded-2xl p-6 card-hover border border-slate-100 group">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 to-purple-600 text-white flex items-center justify-center text-2xl shadow-lg shadow-purple-200 mb-5 group-hover:scale-110 transition-transform">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <h2 class="text-lg font-bold text-slate-800 mb-1">Activity Logs</h2>
        <p class="text-slate-400 text-sm leading-relaxed">View all equipment activity logs with filters.</p>
        <div class="mt-4 flex items-center text-purple-600 text-sm font-semibold group-hover:translate-x-1 transition-transform">
            View Report <i class="fas fa-arrow-right ml-2 text-xs"></i>
        </div>
    </a>
</div>
@endsection