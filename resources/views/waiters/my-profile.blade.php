@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">My Profile</h1>
            <p class="mt-1 text-sm text-slate-500">Manage your account, events, and equipment</p>
        </div>
        <a href="{{ route('dashboard') }}"
            class="inline-flex items-center text-sm font-medium text-amber-700 hover:text-amber-800 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Sidebar Profile Card --}}
        <div class="lg:col-span-4 xl:col-span-3">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden lg:sticky lg:top-6">
                {{-- Profile Header --}}
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 px-6 py-8 text-center border-b border-slate-100">
                    <div class="relative inline-block">
                        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto shadow-md ring-4 ring-white">
                            <span class="text-3xl font-bold text-amber-600">
                                {{ strtoupper(substr($waiter->user->name, 0, 1)) }}
                            </span>
                        </div>
                        <span class="absolute bottom-1 right-1 w-5 h-5 rounded-full border-2 border-white {{ $waiter->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    </div>
                    <h2 class="mt-4 text-xl font-bold text-slate-900">{{ $waiter->user->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $waiter->user->email }}</p>
                    <span class="inline-flex items-center mt-3 px-3 py-1 rounded-full text-xs font-semibold {{ $waiter->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $waiter->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }} mr-1.5"></span>
                        {{ ucfirst($waiter->status) }}
                    </span>
                </div>

                {{-- Contact Details --}}
                <div class="p-6 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Phone</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $waiter->phone }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Emergency Contact</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $waiter->emergency_contact ?? 'Not provided' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Date Hired</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $waiter->date_hired?->format('M d, Y') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Address</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700 leading-relaxed">
                                    @if($waiter->region_id || $waiter->province_id || $waiter->city_id || $waiter->barangay_code || $waiter->address)
                                        {{ $waiter->full_address }}
                                    @else
                                        <span class="text-slate-400 font-normal italic">No address on file</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="lg:col-span-8 xl:col-span-9 space-y-6">
            {{-- Event Assignments --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Event Assignments</h2>
                            <p class="text-xs text-slate-500">{{ $waiter->events->count() }} total events</p>
                        </div>
                    </div>
                </div>

                @if($waiter->events->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($waiter->events->sortByDesc('event_date') as $event)
                                    <tr class="hover:bg-slate-50/60 transition-colors group">
                                        <td class="px-6 py-4">
                                            <a href="{{ route('waiters.my-event', $event) }}"
                                                class="text-sm font-semibold text-amber-700 hover:text-amber-800 hover:underline decoration-2 underline-offset-2">
                                                {{ $event->event_name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                            {{ $event->event_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                                {{ ucfirst($event->pivot->role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            @if($event->pivot->time_in && $event->pivot->time_out)
                                                <span class="font-medium text-slate-700">
                                                    {{ \Carbon\Carbon::parse($event->pivot->time_in)->format('h:i A') }}
                                                </span>
                                                <span class="text-slate-400 mx-1">→</span>
                                                <span class="font-medium text-slate-700">
                                                    {{ \Carbon\Carbon::parse($event->pivot->time_out)->format('h:i A') }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 italic">Not recorded</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusConfig = [
                                                    'confirmed' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'Confirmed'],
                                                    'pending' => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'Pending'],
                                                    'declined' => ['bg-rose-50 text-rose-700 ring-rose-600/20', 'Declined'],
                                                ];
                                                $config = $statusConfig[$event->pivot->confirmation_status] ?? ['bg-slate-50 text-slate-600 ring-slate-500/10', ucfirst($event->pivot->confirmation_status)];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset {{ $config[0] }}">
                                                {{ $config[1] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900">No events assigned</h3>
                        <p class="mt-1 text-sm text-slate-500">You haven't been assigned to any events yet.</p>
                    </div>
                @endif
            </div>

            {{-- Equipment Accountability --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Equipment Accountability</h2>
                            <p class="text-xs text-slate-500">Items assigned to you by event</p>
                        </div>
                    </div>
                </div>

                @if($waiter->assignedEquipment->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($waiter->assignedEquipment->load('event', 'equipmentItem') as $eq)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                            {{ $eq->event->event_name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            {{ $eq->equipmentItem->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 font-semibold text-slate-700">
                                                {{ $eq->quantity_allocated }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($eq->status === 'returned')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Returned
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ ucfirst(str_replace('_', ' ', $eq->status)) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900">No equipment assigned</h3>
                        <p class="text-sm text-slate-500">You don't have any equipment checked out.</p>
                    </div>
                @endif
            </div>

            {{-- Change Password --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Security</h2>
                        <p class="text-xs text-slate-500">Update your account password</p>
                    </div>
                </div>

                <div class="p-6">
                    @if(session('password_success'))
                        <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm font-medium text-emerald-800">{{ session('password_success') }}</p>
                        </div>
                    @endif

                    @if(session('password_error'))
                        <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 flex items-start gap-3">
                            <svg class="w-5 h-5 text-rose-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm font-medium text-rose-800">{{ session('password_error') }}</p>
                        </div>
                    @endif

                    <form action="{{ route('waiters.change-password') }}" method="POST" class="space-y-5">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            {{-- Current Password --}}
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-slate-700">Current Password</label>
                                <div class="relative">
                                    <input type="password" name="current_password" id="waiterCurrentPass" required
                                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all">
                                    <button type="button" onclick="toggleEye('waiterCurrentPass', 'waiterEye1')"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-amber-600 transition-colors focus:outline-none">
                                        <svg class="w-4 h-4" id="waiterEye1-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- New Password --}}
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-slate-700">New Password</label>
                                <div class="relative">
                                    <input type="password" name="new_password" id="waiterNewPass" required minlength="6"
                                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all">
                                    <button type="button" onclick="toggleEye('waiterNewPass', 'waiterEye2')"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-amber-600 transition-colors focus:outline-none">
                                        <svg class="w-4 h-4" id="waiterEye2-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-xs text-slate-400">Minimum 6 characters</p>
                            </div>

                            {{-- Confirm Password --}}
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-slate-700">Confirm Password</label>
                                <div class="relative">
                                    <input type="password" name="new_password_confirmation" id="waiterConfirmPass" required
                                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all">
                                    <button type="button" onclick="toggleEye('waiterConfirmPass', 'waiterEye3')"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-amber-600 transition-colors focus:outline-none">
                                        <svg class="w-4 h-4" id="waiterEye3-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 hover:shadow-md hover:shadow-amber-600/30 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleEye(inputId, eyeId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(eyeId + '-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
        }
    }
</script>
@endsection