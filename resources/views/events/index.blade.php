@extends('layouts.app')
@section('title', 'Events')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Events</h1>
        <p class="text-slate-400 mt-1">Manage all your catering events</p>
    </div>
    <a href="{{ route('events.create') }}" class="btn-primary text-white px-6 py-3 rounded-xl text-sm font-semibold inline-flex items-center gap-2 self-start bg-amber-600 hover:bg-amber-700">
        <i class="fas fa-plus"></i> Schedule Event
    </a>
</div>

{{-- Search & Filter Bar --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
    <form action="{{ route('events.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
        
        {{-- Search by name --}}
        <div class="flex-1 w-full">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Search</label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Search event name or venue..." 
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>
        </div>

        {{-- Filter by Status --}}
        <div class="w-full lg:w-44">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Status</label>
            <select name="status" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                <option value="">All Status</option>
                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        {{-- Date From --}}
        <div class="w-full lg:w-40">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">From</label>
            <input type="date" name="from" value="{{ request('from') }}" 
                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
        </div>

        {{-- Date To --}}
        <div class="w-full lg:w-40">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">To</label>
            <input type="date" name="to" value="{{ request('to') }}" 
                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
        </div>

        {{-- Buttons --}}
        <div class="flex gap-2 w-full lg:w-auto">
            <button type="submit" class="px-5 py-2.5 bg-amber-600 text-white rounded-xl text-sm font-semibold hover:bg-amber-700 transition flex items-center gap-2">
                <i class="fas fa-filter text-xs"></i> Filter
            </button>
            <a href="{{ route('events.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition flex items-center gap-2">
                <i class="fas fa-undo text-xs"></i> Reset
            </a>
        </div>
    </form>

    {{-- Active Filters Display --}}
    @if(request('search') || request('status') || request('from') || request('to'))
    <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2">
        <span class="text-xs text-slate-400 font-medium">Active filters:</span>
        @if(request('search'))
            <span class="px-3 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-medium">
                Search: "{{ request('search') }}" <a href="{{ route('events.index', array_diff_key(request()->all(), ['search' => 1])) }}" class="ml-1 hover:text-amber-900"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('status'))
            <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                Status: {{ ucfirst(request('status')) }} <a href="{{ route('events.index', array_diff_key(request()->all(), ['status' => 1])) }}" class="ml-1 hover:text-blue-900"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('from'))
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                From: {{ request('from') }} <a href="{{ route('events.index', array_diff_key(request()->all(), ['from' => 1])) }}" class="ml-1 hover:text-emerald-900"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('to'))
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                To: {{ request('to') }} <a href="{{ route('events.index', array_diff_key(request()->all(), ['to' => 1])) }}" class="ml-1 hover:text-emerald-900"><i class="fas fa-times"></i></a>
            </span>
        @endif
    </div>
    @endif
</div>

{{-- Results Count --}}
<div class="mb-4 flex items-center justify-between">
    <p class="text-sm text-slate-500">
        Showing <span class="font-semibold text-slate-700">{{ $events->firstItem() ?? 0 }}–{{ $events->lastItem() ?? 0 }}</span> of <span class="font-semibold text-slate-700">{{ $events->total() }}</span> events
    </p>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Event</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Waiters</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Equipment</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($events as $event)
                <tr class="table-row group hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-800 group-hover:text-amber-600 transition">{{ $event->event_name }}</div>
                        <div class="text-sm text-slate-400">{{ Str::limit($event->venue_address, 30) }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        <div class="font-medium">{{ $event->event_date->format('M d, Y') }}</div>
                        <div class="text-xs text-slate-400">{{ $event->event_date->format('h:i A') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="status-badge px-3 py-1 rounded-full text-xs font-medium
                            {{ $event->status === 'scheduled' ? 'bg-blue-50 text-blue-600' : '' }}
                            {{ $event->status === 'ongoing' ? 'bg-purple-50 text-purple-600' : '' }}
                            {{ $event->status === 'completed' ? 'bg-emerald-50 text-emerald-600' : '' }}
                            {{ $event->status === 'cancelled' ? 'bg-red-50 text-red-600' : '' }}">
                            {{ ucfirst($event->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        <div class="flex items-center gap-1">
                            <i class="fas fa-users text-slate-300 text-xs"></i> {{ $event->waiters_count }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        <div class="flex items-center gap-1">
                            <i class="fas fa-box text-slate-300 text-xs"></i> {{ $event->equipment_count }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('events.show', $event) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition" title="View">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('events.edit', $event) }}" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition" title="Edit">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <form action="{{ route('events.destroy', $event) }}" method="POST" class="inline" onsubmit="return confirm('Delete this event?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition" title="Delete">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                        <i class="fas fa-calendar-xmark text-4xl mb-3 text-slate-200"></i>
                        <p class="text-sm">No events found matching your criteria.</p>
                        <a href="{{ route('events.index') }}" class="text-amber-600 hover:underline text-sm mt-1 inline-block">Clear filters</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $events->appends(request()->query())->links('pagination::tailwind') }}
</div>
@endsection