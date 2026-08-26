@extends('layouts.app')
@section('title', 'Events')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Events</h1>
        <p class="text-slate-400 mt-1">Manage all your catering events</p>
    </div>
    <a href="{{ route('events.create') }}" class="btn-primary text-white px-6 py-3 rounded-xl text-sm font-semibold inline-flex items-center gap-2 self-start">
        <i class="fas fa-plus"></i> Schedule Event
    </a>
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
                @foreach($events as $event)
                <tr class="table-row group">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-800 group-hover:text-orange-600 transition">{{ $event->event_name }}</div>
                        <div class="text-sm text-slate-400">{{ Str::limit($event->venue_address, 30) }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        <div class="font-medium">{{ $event->event_date->format('M d, Y') }}</div>
                        <div class="text-xs text-slate-400">{{ $event->event_date->format('h:i A') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="status-badge px-3 py-1 rounded-full text-xs
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
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $events->links('pagination::tailwind') }}
</div>
@endsection