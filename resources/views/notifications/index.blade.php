@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
@php
    // One look per notification kind: [icon, icon bg, icon text, unread row bg, label]
    $kinds = [
        'declined'   => ['fa-calendar-xmark',  'bg-red-50',     'text-red-600',     'bg-red-50/40',     'Declined'],
        'confirmed'  => ['fa-calendar-check',  'bg-emerald-50', 'text-emerald-600', 'bg-emerald-50/40', 'Confirmed'],
        'assignment' => ['fa-calendar-plus',   'bg-amber-50',   'text-amber-600',   'bg-amber-50/40',   'Assignment'],
        'reminder'   => ['fa-clock',           'bg-purple-50',  'text-purple-600',  'bg-purple-50/40',  'Reminder'],
        'update'     => ['fa-pen-to-square',   'bg-orange-50',  'text-orange-600',  'bg-orange-50/40',  'Update'],
        'general'    => ['fa-bell',            'bg-blue-50',    'text-blue-600',    'bg-blue-50/40',    'Notice'],
    ];

    $kindOf = function ($n) {
        $type  = strtolower((string) ($n->data['type'] ?? ''));
        $title = (string) ($n->data['title'] ?? '');
        return match (true) {
            in_array($type, ['event_declined', 'waiter_declined'], true)   => 'declined',
            in_array($type, ['event_confirmed', 'waiter_confirmed'], true) => 'confirmed',
            $title === 'New Event Assignment' => 'assignment',
            $title === 'Event Reminder'       => 'reminder',
            $title === 'Event Update'         => 'update',
            default                           => 'general',
        };
    };

    $unreadCount = auth()->user()->unreadNotifications()->count();
    $hasAny = $notifications->total() > 0;
@endphp

<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Notifications</h1>
            <p class="text-sm text-slate-500">
                @if($unreadCount > 0)
                    You have <span class="font-semibold text-amber-700">{{ $unreadCount }}</span> unread {{ \Illuminate\Support\Str::plural('notification', $unreadCount) }}.
                @else
                    You're all caught up.
                @endif
            </p>
        </div>

        @if($hasAny)
            <div class="flex items-center gap-2 shrink-0">
                @if($unreadCount > 0)
                    <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                            <i class="fas fa-check-double text-xs"></i> Mark all read
                        </button>
                    </form>
                @endif
                <button type="button" onclick="openClearModal()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 hover:border-red-200 transition">
                    <i class="fas fa-trash text-xs"></i> Clear all
                </button>
            </div>
        @endif
    </div>

    {{-- List --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        @forelse($notifications as $notification)
            @php
                $k = $kinds[$kindOf($notification)];
                $isUnread = is_null($notification->read_at);
                $title = $notification->data['title'] ?? 'Notification';
                $message = $notification->data['message'] ?? '';
            @endphp
            <div class="group relative flex items-start gap-3 px-4 py-3 border-b border-slate-100 last:border-b-0 transition hover:bg-slate-50/70 {{ $isUnread ? $k[3] : '' }}">
                {{-- Unread marker --}}
                @if($isUnread)
                    <span class="absolute left-0 top-0 bottom-0 w-1 bg-amber-500" aria-hidden="true"></span>
                @endif

                <div class="w-9 h-9 rounded-lg {{ $k[1] }} {{ $k[2] }} flex items-center justify-center shrink-0">
                    <i class="fas {{ $k[0] }} text-sm"></i>
                </div>

                <a href="{{ route('notifications.show', $notification->id) }}" class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-sm truncate {{ $isUnread ? 'font-bold text-slate-900' : 'font-medium text-slate-600' }}">{{ $title }}</span>
                        @if($isUnread)
                            <span class="shrink-0 w-2 h-2 rounded-full bg-amber-500" title="Unread"></span>
                        @endif
                    </div>
                    @if($message)
                        <p class="text-xs text-slate-500 truncate mt-0.5">{{ $message }}</p>
                    @endif
                    <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                        <span class="px-1.5 py-0.5 rounded {{ $k[1] }} {{ $k[2] }} font-semibold">{{ $k[4] }}</span>
                        <span title="{{ $notification->created_at->format('M d, Y h:i A') }}">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                </a>

                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" title="Delete" aria-label="Delete notification"
                        class="w-7 h-7 rounded-md text-slate-300 hover:bg-red-50 hover:text-red-600 flex items-center justify-center transition">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        @empty
            <div class="px-4 py-12 text-center">
                <div class="w-14 h-14 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-bell-slash text-xl text-slate-300"></i>
                </div>
                <p class="text-sm font-semibold text-slate-700">No notifications</p>
                <p class="text-xs text-slate-500">New assignments, confirmations and reminders will show up here.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-3">
            {{ $notifications->links('pagination::tailwind') }}
        </div>
    @endif
</div>

{{-- Clear All Modal --}}
@if($hasAny)
    <form id="clearAllForm" action="{{ route('notifications.clear-all') }}" method="POST" class="hidden">
        @csrf
    </form>

    <div id="clearModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-red-100 text-red-600">
                    <i class="fas fa-trash text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Clear all notifications?</h3>
                <p class="text-slate-500 text-sm">All {{ $notifications->total() }} {{ \Illuminate\Support\Str::plural('notification', $notifications->total()) }} will be deleted. This cannot be undone.</p>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeClearModal()"
                    class="flex-1 px-4 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 font-medium transition">
                    Cancel
                </button>
                <button type="button" id="clearConfirmBtn"
                    class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition">
                    Yes, Clear All
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('clearModal');
            window.openClearModal = () => modal.classList.remove('hidden');
            window.closeClearModal = () => modal.classList.add('hidden');
            document.getElementById('clearConfirmBtn').addEventListener('click', function () {
                this.disabled = true;
                this.textContent = 'Clearing...';
                document.getElementById('clearAllForm').submit();
            });
            modal.addEventListener('click', e => { if (e.target === modal) window.closeClearModal(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') window.closeClearModal(); });
        })();
    </script>
@endif
@endsection