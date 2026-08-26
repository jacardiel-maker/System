@extends('layouts.app')
@section('title', 'Notification')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold mb-4">{{ $notification->data['title'] ?? 'Notification' }}</h1>
    
    <div class="text-gray-700 mb-6">{{ $notification->data['message'] ?? '' }}</div>
    
    @if(isset($notification->data['event_id']))
    <div class="mb-6">
        <a href="{{ route('events.show', $notification->data['event_id']) }}" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">View Event</a>
    </div>
    @endif

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