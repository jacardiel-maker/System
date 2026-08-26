@extends('layouts.app')
@section('title', $event->event_name)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- Breadcrumb & Header --}}
    <div class="mb-8">
        <nav class="flex items-center gap-2 text-sm text-slate-500 mb-4">
            <a href="{{ route('waiters.my-profile') }}" class="hover:text-slate-800 transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Profile
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-slate-800 font-medium truncate">{{ $event->event_name }}</span>
        </nav>
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $event->event_name }}</h1>
                <p class="text-slate-500 mt-1 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $event->event_date->format('l, F j, Y') }}
                </p>
            </div>
            @php
                $statusConfig = [
                    'scheduled' => ['bg-sky-50 text-sky-700 border-sky-200', 'Calendar'],
                    'ongoing' => ['bg-violet-50 text-violet-700 border-violet-200', 'Play'],
                    'completed' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'CheckCircle'],
                    'cancelled' => ['bg-rose-50 text-rose-700 border-rose-200', 'XCircle'],
                ];
                [$badgeClass, $iconName] = $statusConfig[$event->status] ?? ['bg-slate-50 text-slate-700 border-slate-200', 'HelpCircle'];
            @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold border {{ $badgeClass }} w-fit">
                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-60"></span>
                {{ ucfirst($event->status) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Event Details --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Event Details
                    </h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="group">
                            <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Date & Time</dt>
                            <dd class="text-slate-900 font-medium text-lg">{{ $event->event_date->format('F j, Y') }}</dd>
                            <dd class="text-slate-600">{{ $event->event_date->format('g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Setup Time</dt>
                            <dd class="text-slate-900 font-medium text-lg">{{ $event->setup_time->format('g:i A') }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Venue</dt>
                            <dd class="text-slate-900 font-medium flex items-start gap-2">
                                <svg class="w-5 h-5 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $event->venue_address }}
                            </dd>
                        </div>
                        @if($event->guest_count)
                        <div>
                            <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Expected Guests</dt>
                            <dd class="text-slate-900 font-medium text-2xl">{{ number_format($event->guest_count) }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Your Role</dt>
                            <dd class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-50 text-amber-700 text-sm font-semibold border border-amber-200">
                                {{ ucfirst($myAssignment->pivot->role) }}
                            </dd>
                        </div>
                    </dl>
                    
                    @if($event->notes)
                    <div class="mt-6 p-4 bg-amber-50/50 border border-amber-100 rounded-xl">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <div>
                                <dt class="text-sm font-semibold text-amber-900">Notes</dt>
                                <dd class="text-sm text-amber-800 mt-1 leading-relaxed">{{ $event->notes }}</dd>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Equipment --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Equipment Assigned
                    </h2>
                    @if($myEquipment->count() > 0)
                        <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">{{ $myEquipment->count() }} items</span>
                    @endif
                </div>
                
                @if($myEquipment->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Qty</th>
                                <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($myEquipment as $eq)
                            @php
                                $eqStatus = $eq->status;
                                $eqStatusClasses = [
                                    'returned' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'allocated' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'partially_returned' => 'bg-orange-50 text-orange-700 border-orange-200',
                                    'lost' => 'bg-rose-50 text-rose-700 border-rose-200',
                                ];
                                $eqBadge = $eqStatusClasses[$eqStatus] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $eq->equipmentItem->name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">{{ $eq->equipmentItem->category->name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-900 font-semibold text-center">{{ $eq->quantity_allocated }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $eqBadge }}">
                                        {{ ucfirst(str_replace('_', ' ', $eq->status)) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-8 text-center">
                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <p class="text-slate-500 text-sm">No equipment assigned for this event.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">
            
            {{-- Participation Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Your Status
                    </h2>
                </div>
                <div class="p-6">
                    @php
                        $confStatus = $myAssignment->pivot->confirmation_status;
                        $confConfig = [
                            'confirmed' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Confirmed', 'CheckCircle'],
                            'pending' => ['bg-amber-50 text-amber-700 border-amber-200', 'Pending Response', 'Clock'],
                            'declined' => ['bg-rose-50 text-rose-700 border-rose-200', 'Declined', 'XCircle'],
                        ];
                        [$confClass, $confLabel, $confIcon] = $confConfig[$confStatus] ?? ['bg-slate-50 text-slate-700 border-slate-200', 'Unknown', 'HelpCircle'];
                    @endphp
                    
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-sm text-slate-500">Confirmation</div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $confClass }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ $confLabel }}
                            </span>
                        </div>
                    </div>

                    @if($myAssignment->pivot->confirmed_at)
                    <p class="text-xs text-slate-400 mb-4 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Confirmed {{ \Carbon\Carbon::parse($myAssignment->pivot->confirmed_at)->diffForHumans() }}
                    </p>
                    @endif

                    @if($confStatus === 'pending')
                    <div class="space-y-2 pt-4 border-t border-slate-100">
                        <form action="{{ route('events.confirm', $event) }}" method="POST" class="block">
                            @csrf
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2.5 rounded-xl transition-all active:scale-[0.98] shadow-sm shadow-emerald-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Confirm Attendance
                            </button>
                        </form>
                        <form action="{{ route('events.confirm', $event) }}" method="POST" class="block">
                            @csrf
                            <input type="hidden" name="status" value="declined">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-white hover:bg-rose-50 text-rose-600 font-medium px-4 py-2.5 rounded-xl border border-rose-200 transition-all active:scale-[0.98]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Decline
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Team Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Team
                        <span class="ml-auto text-xs font-normal text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ $event->waiters->count() }} members</span>
                    </h2>
                </div>
                <div class="p-2">
                    <div class="space-y-1">
                        @foreach($event->waiters as $w)
                        @php
                            $isMe = $w->id === auth()->user()->waiter->id;
                            $wStatus = $w->pivot->confirmation_status;
                            $wStatusColors = [
                                'confirmed' => 'bg-emerald-500',
                                'pending' => 'bg-amber-400',
                                'declined' => 'bg-rose-500',
                            ];
                            $dotColor = $wStatusColors[$wStatus] ?? 'bg-slate-400';
                        @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl transition-colors {{ $isMe ? 'bg-amber-50/80 border border-amber-200' : 'hover:bg-slate-50' }}">
                            <div class="relative">
                                <div class="w-10 h-10 rounded-full {{ $isMe ? 'bg-amber-200 text-amber-800' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center text-sm font-bold">
                                    {{ strtoupper(substr($w->user->name, 0, 1)) }}
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white {{ $dotColor }}"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-slate-900 truncate">
                                    {{ $w->user->name }}
                                    @if($isMe)
                                        <span class="text-amber-600 text-xs font-medium ml-1">(You)</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 capitalize">{{ $w->pivot->role }}</div>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md
                                {{ $wStatus === 'confirmed' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                {{ $wStatus === 'pending' ? 'bg-amber-50 text-amber-700' : '' }}
                                {{ $wStatus === 'declined' ? 'bg-rose-50 text-rose-700' : '' }}">
                                {{ $wStatus }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection