@extends('layouts.app')
@section('title', 'Notification')

@section('content')
@php
    $data  = $notification->data ?? [];
    $type  = strtolower((string) ($data['type'] ?? ''));
    $title = $data['title'] ?? 'Notification';

    $kind = match (true) {
        in_array($type, ['event_declined', 'waiter_declined'], true)   => 'declined',
        in_array($type, ['event_confirmed', 'waiter_confirmed'], true) => 'confirmed',
        $title === 'New Event Assignment' => 'assignment',
        $title === 'Event Reminder'       => 'reminder',
        $title === 'Event Update'         => 'update',
        default                           => 'general',
    };

    // [icon, icon bg, icon text, label, details box classes]
    $kinds = [
        'declined'   => ['fa-calendar-xmark', 'bg-red-50',     'text-red-600',     'Declined',   'bg-red-50/60 border-red-100'],
        'confirmed'  => ['fa-calendar-check', 'bg-emerald-50', 'text-emerald-600', 'Confirmed',  'bg-emerald-50/60 border-emerald-100'],
        'assignment' => ['fa-calendar-plus',  'bg-amber-50',   'text-amber-600',   'Assignment', 'bg-amber-50/60 border-amber-100'],
        'reminder'   => ['fa-clock',          'bg-purple-50',  'text-purple-600',  'Reminder',   'bg-purple-50/60 border-purple-100'],
        'update'     => ['fa-pen-to-square',  'bg-orange-50',  'text-orange-600',  'Update',     'bg-orange-50/60 border-orange-100'],
        'general'    => ['fa-bell',           'bg-blue-50',    'text-blue-600',    'Notice',     'bg-slate-50 border-slate-100'],
    ];
    $k = $kinds[$kind];

    // Safe date formatting: a missing or bad date shows "—" instead of crashing the page
    $fmt = function ($value) {
        if (empty($value)) return null;
        try { return \Carbon\Carbon::parse($value)->format('M d, Y h:i A'); }
        catch (\Throwable $e) { return null; }
    };

    // Details shown in the box (only the ones that exist)
    $details = array_filter([
        'Waiter'      => $data['waiter_name'] ?? null,
        'Event'       => $data['event_name'] ?? null,
        'Event Date'  => $fmt($data['event_date'] ?? null),
        'Declined At' => $kind === 'declined' ? $fmt($data['declined_at'] ?? null) : null,
    ]);

    $eventId = $data['event_id'] ?? null;
@endphp

<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="min-w-0">
            <div class="flex items-center gap-1.5 text-xs text-slate-500 mb-1">
                <a href="{{ route('notifications.index') }}" class="hover:text-amber-700 transition-colors">Notifications</a>
                <i class="fas fa-chevron-right text-[9px] text-slate-300"></i>
                <span class="text-slate-700 font-medium">Details</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Notification</h1>
        </div>
        <a href="{{ route('notifications.index') }}"
            class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
            <i class="fas fa-arrow-left text-xs"></i> Back
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        {{-- Title --}}
        <div class="px-5 py-4 border-b border-slate-100 flex items-start gap-3">
            <div class="w-10 h-10 rounded-lg {{ $k[1] }} {{ $k[2] }} flex items-center justify-center shrink-0">
                <i class="fas {{ $k[0] }}"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-bold text-slate-900 leading-tight">{{ $title }}</h2>
                <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                    <span class="px-1.5 py-0.5 rounded {{ $k[1] }} {{ $k[2] }} font-semibold">{{ $k[3] }}</span>
                    <span>{{ $notification->created_at->format('M d, Y h:i A') }}</span>
                    <span>• {{ $notification->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        <div class="p-5 space-y-4">
            {{-- Message --}}
            @if(!empty($data['message']))
                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $data['message'] }}</p>
            @endif

            {{-- Details --}}
            @if(count($details))
                <dl class="rounded-lg border {{ $k[4] }} divide-y divide-white/60">
                    @foreach($details as $label => $value)
                        <div class="flex items-center justify-between gap-3 px-4 py-2">
                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $label }}</dt>
                            <dd class="text-sm font-semibold text-slate-800 text-right">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </div>

        {{-- Actions --}}
        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-2">
            <div>
                @if($eventId)
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('events.show', $eventId) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition">
                            <i class="fas fa-eye text-xs"></i> View Event
                        </a>
                    @else
                        <a href="{{ route('waiters.my-event', $eventId) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition">
                            <i class="fas fa-eye text-xs"></i> View My Event
                        </a>
                    @endif
                @endif
            </div>

            <button type="button" onclick="openDeleteModal()"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 hover:border-red-200 transition">
                <i class="fas fa-trash text-xs"></i> Delete
            </button>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<form id="deleteForm" action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="text-center mb-5">
            <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-red-100 text-red-600">
                <i class="fas fa-trash text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-1">Delete Notification?</h3>
            <p class="text-slate-500 text-sm">This action cannot be undone.</p>
        </div>
        <div class="bg-slate-50 rounded-xl p-3 mb-5">
            <div class="text-xs text-slate-500 mb-0.5">Notification</div>
            <div class="font-semibold text-slate-800">{{ $title }}</div>
        </div>
        <div class="flex gap-3">
            <button type="button" onclick="closeDeleteModal()"
                class="flex-1 px-4 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 font-medium transition">
                Cancel
            </button>
            <button type="button" id="deleteConfirmBtn"
                class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition">
                Yes, Delete
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('deleteModal');
        window.openDeleteModal = () => modal.classList.remove('hidden');
        window.closeDeleteModal = () => modal.classList.add('hidden');
        document.getElementById('deleteConfirmBtn').addEventListener('click', function () {
            this.disabled = true;
            this.textContent = 'Deleting...';
            document.getElementById('deleteForm').submit();
        });
        modal.addEventListener('click', e => { if (e.target === modal) window.closeDeleteModal(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') window.closeDeleteModal(); });
    })();
</script>
@endsection