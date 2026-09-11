@extends('layouts.app')
@section('title', 'Notification')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    
    {{-- Icon & Title --}}
    <div class="flex items-center gap-3 mb-4">
        @php
            $type = $notification->data['type'] ?? 'general';
        @endphp

        @if($type === 'waiter_confirmed')
            <span class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                <i class="fas fa-check-circle"></i>
            </span>
        @elseif($type === 'event_declined')
            <span class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                <i class="fas fa-calendar-xmark"></i>
            </span>
        @else
            <span class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                <i class="fas fa-bell"></i>
            </span>
        @endif
        <h1 class="text-2xl font-bold">{{ $notification->data['title'] ?? 'Notification' }}</h1>
    </div>
    
    <div class="text-gray-700 mb-6 text-lg">{{ $notification->data['message'] ?? '' }}</div>
    
    {{-- Waiter Confirmed Specific Details --}}
    @if($type === 'waiter_confirmed')
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 space-y-2">
        <div class="flex justify-between">
            <span class="text-gray-500 text-sm">Waiter:</span>
            <span class="font-medium">{{ $notification->data['waiter_name'] ?? 'N/A' }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500 text-sm">Event:</span>
            <span class="font-medium">{{ $notification->data['event_name'] ?? 'N/A' }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500 text-sm">Date:</span>
            <span class="font-medium">{{ \Carbon\Carbon::parse($notification->data['event_date'])->format('F d, Y h:i A') }}</span>
        </div>
    </div>
    @endif

    {{-- Event Declined Specific Details --}}
    @if($type === 'event_declined')
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 space-y-2">
        <div class="flex justify-between">
            <span class="text-gray-500 text-sm">Waiter:</span>
            <span class="font-medium text-red-700">{{ $notification->data['waiter_name'] ?? 'N/A' }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500 text-sm">Event:</span>
            <span class="font-medium text-red-700">{{ $notification->data['event_name'] ?? 'N/A' }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500 text-sm">Declined At:</span>
            <span class="font-medium text-red-700">{{ \Carbon\Carbon::parse($notification->data['declined_at'])->format('F d, Y h:i A') }}</span>
        </div>
    </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex gap-3 mb-6">
        @if(isset($notification->data['event_id']))
            @if(auth()->user()->isAdmin())
                <a href="{{ route('events.show', $notification->data['event_id']) }}" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">
                    <i class="fas fa-eye mr-1"></i> View Event
                </a>
            @else
                <a href="{{ route('waiters.my-event', $notification->data['event_id']) }}" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">
                    <i class="fas fa-eye mr-1"></i> View My Event
                </a>
            @endif
        @endif
    </div>

    <div class="text-sm text-gray-500 mb-4">Received: {{ $notification->created_at->format('F d, Y h:i A') }}</div>

    <div class="flex justify-between">
        <a href="{{ route('notifications.index') }}" class="px-4 py-2 border rounded hover:bg-gray-50">&larr; Back</a>
        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
        </form>
    </div>
</div>
@endsection