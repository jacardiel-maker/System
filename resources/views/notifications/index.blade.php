@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Notifications</h1>
    <div class="space-x-2">
        <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Mark All Read</button>
        </form>
        <form action="{{ route('notifications.clear-all') }}" method="POST" class="inline" onsubmit="return confirm('Clear all notifications?');">
            @csrf
            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">Clear All</button>
        </form>
    </div>
</div>

<div class="bg-white rounded-lg shadow divide-y">
    @forelse($notifications as $notification)
    @php
        $type = $notification->data['type'] ?? 'general';
        $title = $notification->data['title'] ?? 'Notification';
        
        $icon = 'fa-bell';
        $bgColor = 'bg-blue-50';
        $textColor = 'text-blue-700';

        if ($type === 'event_declined') {
            $icon = 'fa-calendar-xmark';
            $bgColor = 'bg-red-50';
            $textColor = 'text-red-700';
        } elseif ($type === 'event_confirmed') {
            $icon = 'fa-calendar-check';
            $bgColor = 'bg-green-50';
            $textColor = 'text-green-700';
        } elseif ($title === 'New Event Assignment') {
            $icon = 'fa-calendar-plus';
            $bgColor = 'bg-amber-50';
            $textColor = 'text-amber-700';
        } elseif ($title === 'Event Reminder') {
            $icon = 'fa-clock';
            $bgColor = 'bg-purple-50';
            $textColor = 'text-purple-700';
        } elseif ($title === 'Event Update') {
            $icon = 'fa-pen-to-square';
            $bgColor = 'bg-orange-50';
            $textColor = 'text-orange-700';
        }
    @endphp
    <div class="p-4 flex items-start gap-4 {{ $notification->read_at ? 'bg-white' : $bgColor }}">
        <div class="w-10 h-10 rounded-full {{ $bgColor }} flex items-center justify-center flex-shrink-0">
            <i class="fas {{ $icon }} {{ $textColor }}"></i>
        </div>
        <div class="flex-1 min-w-0">
            <a href="{{ route('notifications.show', $notification->id) }}" class="block">
                <div class="font-medium {{ $notification->read_at ? 'text-gray-700' : $textColor }}">
                    {{ $title }}
                </div>
                <div class="text-sm text-gray-500 truncate">{{ $notification->data['message'] ?? '' }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
            </a>
        </div>
        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-gray-300 hover:text-red-600"><i class="fas fa-trash"></i></button>
        </form>
    </div>
    @empty
    <div class="p-8 text-center text-gray-500">
        <i class="fas fa-bell-slash text-3xl mb-2 text-gray-200"></i>
        <p>No notifications.</p>
    </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $notifications->links() }}
</div>
@endsection