@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Notifications</h1>
        <div class="space-x-2">
            <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Mark All
                    Read</button>
            </form>
            <form action="{{ route('notifications.clear-all') }}" method="POST" class="inline"
                onsubmit="return confirm('Clear all notifications?');">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">Clear
                    All</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow divide-y">
        @forelse($notifications as $notification)
            <div class="p-4 flex items-center justify-between {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}">
                <div class="flex-1 flex items-start gap-3">
                    {{-- Icon based on type --}}
                    <div class="mt-1">
                        @if(($notification->data['type'] ?? '') === 'waiter_confirmed')
                            <span class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                                <i class="fas fa-check-circle text-sm"></i>
                            </span>
                        @elseif(($notification->data['title'] ?? '') === 'New Event Assignment')
                            <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                <i class="fas fa-calendar-check text-sm"></i>
                            </span>
                        @elseif(($notification->data['type'] ?? '') === 'update')
                            <span class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center">
                                <i class="fas fa-edit text-sm"></i>
                            </span>
                        @else
                            <span class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center">
                                <i class="fas fa-bell text-sm"></i>
                            </span>
                        @endif
                    </div>

                    <a href="{{ route('notifications.show', $notification->id) }}" class="block flex-1">
                        <div class="font-medium {{ $notification->read_at ? 'text-gray-700' : 'text-blue-900' }}">
                            {{ $notification->data['title'] ?? 'Notification' }}
                        </div>
                        <div class="text-sm text-gray-500">{{ $notification->data['message'] ?? '' }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                    </a>
                </div>
                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="ml-4">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500">No notifications.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
@endsection