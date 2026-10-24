@extends('layouts.app')
@section('title', $event->event_name)

@section('content')
    @php
        $statusConfig = [
            'scheduled' => ['bg-blue-50 text-blue-600', 'Scheduled'],
            'ongoing'   => ['bg-purple-50 text-purple-600', 'Ongoing'],
            'completed' => ['bg-emerald-50 text-emerald-600', 'Completed'],
            'cancelled' => ['bg-red-50 text-red-600', 'Cancelled'],
        ];
        $eventStatus = strtolower(trim((string) $event->status));
        $config = $statusConfig[$eventStatus] ?? ['bg-slate-100 text-slate-600', ucfirst($eventStatus ?: 'scheduled')];

        $allocStatusLabels = [
            'allocated'          => ['bg-blue-50 text-blue-600', 'Allocated'],
            'in_use'             => ['bg-purple-50 text-purple-600', 'In Use'],
            'returned'           => ['bg-emerald-50 text-emerald-600', 'Returned'],
            'partially_returned' => ['bg-amber-50 text-amber-600', 'Partially Returned'],
            'lost'               => ['bg-red-50 text-red-600', 'Lost'],
        ];

        $confConfig = [
            'confirmed' => ['bg-emerald-50 text-emerald-600', 'Confirmed'],
            'pending'   => ['bg-amber-50 text-amber-600', 'Pending'],
            'declined'  => ['bg-red-50 text-red-600', 'Declined'],
        ];

        $roleLabels = ['waiter' => 'Waiter', 'head_waiter' => 'Head Waiter', 'setup_crew' => 'Setup Crew'];

        $readOnly = auth()->user()->isOwner(); // owner: view only, no edit / delete / allocate / attendance

        // The owner has no Events page in the sidebar; he reaches an event from the Event Report,
        // so Back / breadcrumb return there. Admin keeps going to the Events list.
        $backUrl   = $readOnly ? route('reports.events') : route('events.index');
        $backLabel = $readOnly ? 'Event Report' : 'Events';
    @endphp

    {{-- Header --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-1">
                @if($readOnly)
                    <a href="{{ route('reports.index') }}" class="hover:text-amber-700 transition-colors">Reports</a>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                @endif
                <a href="{{ $backUrl }}" class="hover:text-amber-700 transition-colors">{{ $backLabel }}</a>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <span class="text-slate-600 font-medium truncate">{{ $event->event_name }}</span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-bold text-slate-800 leading-tight">{{ $event->event_name }}</h1>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $config[0] }}">{{ $config[1] }}</span>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ $backUrl }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
            @unless($readOnly)
            <a href="{{ route('events.edit', $event) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition">
                <i class="fas fa-pen text-xs"></i> Edit
            </a>
            <form id="delete-event-form" action="{{ route('events.destroy', $event) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="button"
                    onclick="openDeleteDialog('delete-event-form', 'Delete Event?', {{ json_encode($event->event_name) }}, 'This action cannot be undone.')"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 hover:border-red-200 transition">
                    <i class="fas fa-trash text-xs"></i> Delete
                </button>
            </form>
            @endunless
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Event Details --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Event Details</h2>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                                <i class="fas fa-calendar text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Event Date</p>
                                <p class="text-sm font-semibold text-slate-800 leading-tight">{{ $event->event_date?->format('F d, Y') ?? '—' }}</p>
                                <p class="text-xs text-slate-500">{{ $event->event_date?->format('h:i A') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                <i class="fas fa-clock text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Setup Time</p>
                                <p class="text-sm font-semibold text-slate-800 leading-tight">{{ $event->setup_time?->format('F d, Y') ?? '—' }}</p>
                                <p class="text-xs text-slate-500">{{ $event->setup_time?->format('h:i A') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 sm:col-span-2">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-violet-50 flex items-center justify-center text-violet-600">
                                <i class="fas fa-user text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Customer</p>
                                <p class="text-sm font-semibold text-slate-800 leading-snug break-words">{{ $event->customer_name ?: '—' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 {{ $event->guest_count ? '' : 'sm:col-span-2' }}">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 flex items-center justify-center text-slate-500">
                                <i class="fas fa-map-marker-alt text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Venue</p>
                                <p class="text-sm font-semibold text-slate-800 leading-snug break-words">{{ $event->venue_address }}</p>
                            </div>
                        </div>

                        @if($event->guest_count)
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 shrink-0 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                                    <i class="fas fa-user-friends text-xs"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Expected Guests</p>
                                    <p class="text-sm font-semibold text-slate-800 leading-tight">{{ number_format($event->guest_count) }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($event->notes)
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Notes</p>
                            <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $event->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Equipment Allocations --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Equipment Allocations</h2>
                        <p class="text-xs text-slate-400">{{ $event->equipment->count() }} {{ Str::plural('item', $event->equipment->count()) }} allocated</p>
                    </div>
                    @unless($readOnly)
                    <a href="{{ route('event-equipment.create', ['event_id' => $event->id]) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700 transition">
                        <i class="fas fa-plus text-[10px]"></i> Allocate
                    </a>
                    @endunless
                </div>

                @if($event->equipment->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px]">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Assigned To</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-2 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $readOnly ? 'Details' : 'Actions' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($event->equipment as $eq)
                                    @php
                                        $eqStatus = strtolower(trim((string) $eq->status));
                                        $eqConfig = $allocStatusLabels[$eqStatus] ?? ['bg-slate-100 text-slate-600', ucfirst(str_replace('_', ' ', $eqStatus))];
                                        $itemName = $eq->equipmentItem->name ?? 'Deleted item';
                                        $qty = (int) $eq->quantity_allocated;
                                        $assignee = $eq->assignedWaiter->user->name ?? null;
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="px-4 py-2 text-xs font-semibold text-slate-800">{{ $itemName }}</td>
                                        <td class="px-4 py-2 text-xs {{ $assignee ? 'text-slate-600' : 'text-slate-400 italic' }}">{{ $assignee ?? 'Unassigned' }}</td>
                                        <td class="px-4 py-2">
                                            <span class="inline-flex items-center justify-center min-w-[28px] h-6 px-1.5 rounded-md bg-slate-100 text-slate-700 text-xs font-bold">{{ $qty }}</span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap {{ $eqConfig[0] }}">{{ $eqConfig[1] }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <a href="{{ route('event-equipment.show', $eq) }}"
                                                    class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition"
                                                    title="View" aria-label="View">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>
                                                @unless($readOnly)
                                                @if(!in_array($eqStatus, ['returned', 'lost']))
                                                    <button type="button"
                                                        onclick="openReturnModal({{ $eq->id }}, {{ json_encode($itemName) }}, {{ $qty }})"
                                                        class="w-7 h-7 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition"
                                                        title="Record Return" aria-label="Record Return">
                                                        <i class="fas fa-undo text-xs"></i>
                                                    </button>
                                                @endif
                                                @if($eqStatus === 'allocated')
                                                    <a href="{{ route('event-equipment.edit', $eq) }}"
                                                        class="w-7 h-7 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition"
                                                        title="Edit" aria-label="Edit">
                                                        <i class="fas fa-pen text-xs"></i>
                                                    </a>
                                                    <form id="delete-alloc-{{ $eq->id }}" action="{{ route('event-equipment.destroy', $eq) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            onclick="openDeleteDialog('delete-alloc-{{ $eq->id }}', 'Delete Allocation?', {{ json_encode($itemName . ' × ' . $qty) }}, 'Stock will be returned to inventory.')"
                                                            class="w-7 h-7 rounded-md bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition"
                                                            title="Delete" aria-label="Delete">
                                                            <i class="fas fa-trash text-xs"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @endunless
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-4 py-8 text-center text-slate-400">
                        <i class="fas fa-box-open text-2xl mb-2 text-slate-200 block"></i>
                        <p class="text-xs">No equipment allocated yet.</p>
                        @unless($readOnly)<a href="{{ route('event-equipment.create', ['event_id' => $event->id]) }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Allocate equipment</a>@endunless
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar: Assigned Waiters --}}
        <div>
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden lg:sticky lg:top-4">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Assigned Waiters</h2>
                    <p class="text-xs text-slate-400">{{ $event->waiters->count() }} team {{ Str::plural('member', $event->waiters->count()) }}</p>
                </div>

                <div class="p-3 space-y-3 lg:max-h-[calc(100vh-10rem)] lg:overflow-y-auto">
                    @forelse($event->waiters as $waiter)
                        @php
                            $wName = $waiter->user->name ?? 'Unknown';
                            $conf = strtolower((string) $waiter->pivot->confirmation_status);
                            $cConfig = $confConfig[$conf] ?? ['bg-slate-100 text-slate-600', ucfirst($conf ?: 'pending')];
                            $role = $waiter->pivot->role;
                        @endphp
                        <div class="rounded-lg border border-slate-100">
                            <div class="px-3 py-2.5 flex items-center gap-2.5">
                                <div class="w-8 h-8 shrink-0 rounded-lg bg-gradient-to-br from-slate-700 to-slate-600 flex items-center justify-center text-white text-xs font-bold">
                                    {{ strtoupper(mb_substr($wName, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-slate-800 truncate leading-tight">{{ $wName }}</p>
                                    <p class="text-[11px] text-slate-400 truncate leading-tight">
                                        {{ $roleLabels[$role] ?? ucfirst(str_replace('_', ' ', (string) $role)) }}
                                        @if($waiter->phone) • {{ $waiter->phone }} @endif
                                    </p>
                                </div>
                                <span class="shrink-0 inline-block px-2 py-0.5 rounded-full text-[11px] font-medium {{ $cConfig[0] }}">{{ $cConfig[1] }}</span>
                            </div>

                            {{-- Why the waiter declined --}}
                            @if($conf === 'declined' && !empty($waiter->pivot->decline_reason))
                                <div class="mx-3 mb-2 rounded-md bg-red-50 border border-red-100 px-2.5 py-1.5 text-[11px] text-red-700">
                                    <span class="font-semibold">Reason:</span> {{ $waiter->pivot->decline_reason }}
                                </div>
                            @endif

                            {{-- Attendance: only confirmed waiters can have Time In / Time Out --}}
                            @if($conf === 'confirmed' && $readOnly)
                                @php
                                    $tIn  = $waiter->pivot->time_in  ? \Carbon\Carbon::parse($waiter->pivot->time_in)->format('M d, h:i A')  : '—';
                                    $tOut = $waiter->pivot->time_out ? \Carbon\Carbon::parse($waiter->pivot->time_out)->format('M d, h:i A') : '—';
                                @endphp
                                <div class="px-3 py-2 border-t border-slate-100 bg-slate-50/40 grid grid-cols-2 gap-2">
                                    <div>
                                        <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Time In</p>
                                        <p class="text-[11px] text-slate-700 font-medium">{{ $tIn }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Time Out</p>
                                        <p class="text-[11px] text-slate-700 font-medium">{{ $tOut }}</p>
                                    </div>
                                </div>
                            @elseif($conf === 'confirmed')
                            <form action="{{ route('events.waiter.time', [$event, $waiter]) }}" method="POST" class="px-3 pb-3 pt-2 border-t border-slate-100 bg-slate-50/40">
                                @csrf
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Time In</label>
                                        <input type="datetime-local" name="time_in"
                                            value="{{ $waiter->pivot->time_in ? \Carbon\Carbon::parse($waiter->pivot->time_in)->format('Y-m-d\TH:i') : '' }}"
                                            class="w-full px-2 py-1 border border-slate-200 rounded-md text-[11px] bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Time Out</label>
                                        <input type="datetime-local" name="time_out"
                                            value="{{ $waiter->pivot->time_out ? \Carbon\Carbon::parse($waiter->pivot->time_out)->format('Y-m-d\TH:i') : '' }}"
                                            class="w-full px-2 py-1 border border-slate-200 rounded-md text-[11px] bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                    </div>
                                </div>
                                <button type="submit"
                                    class="mt-2 w-full inline-flex justify-center items-center gap-1.5 rounded-md bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700 transition">
                                    <i class="fas fa-check text-[10px]"></i> Save Attendance
                                </button>
                            </form>
                            @else
                                <div class="px-3 py-2 border-t border-slate-100 bg-slate-50/40 text-[11px] text-slate-400 italic">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ $conf === 'declined' ? 'Declined — no attendance to record.' : 'Time in / out is available once the waiter confirms.' }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400">
                            <i class="fas fa-users text-2xl mb-2 text-slate-200 block"></i>
                            <p class="text-xs">No waiters assigned yet.</p>
                            @unless($readOnly)<a href="{{ route('events.edit', $event) }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Assign waiters</a>@endunless
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Return Modal --}}
    <div id="returnModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-emerald-100 text-emerald-600">
                    <i class="fas fa-undo text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Record Equipment Return</h3>
                <p class="text-slate-500 text-sm">Enter how many came back and in what condition.</p>
            </div>

            <div class="bg-slate-50 rounded-xl p-3 mb-4 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-xs text-slate-500 mb-0.5">Equipment</div>
                    <div id="returnItemName" class="font-semibold text-slate-800 truncate"></div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-xs text-slate-500 mb-0.5">Allocated</div>
                    <div id="returnAllocated" class="font-bold text-slate-800"></div>
                </div>
            </div>

            <form id="returnForm" method="POST">
                @csrf
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label for="r-good" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Good</label>
                        <input id="r-good" type="number" name="quantity_returned" min="0" value="0" required
                            class="return-qty w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="r-damaged" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Damaged</label>
                        <input id="r-damaged" type="number" name="quantity_damaged" min="0" value="0" required
                            class="return-qty w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="r-missing" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Missing</label>
                        <input id="r-missing" type="number" name="quantity_missing" min="0" value="0" required
                            class="return-qty w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>
                </div>

                <div id="returnTotal" class="mt-2 text-xs text-slate-500"></div>

                <div class="mt-3">
                    <label for="r-notes" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Notes</label>
                    <textarea id="r-notes" name="notes" rows="2" placeholder="Condition details, issues found..."
                        class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"></textarea>
                </div>

                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="closeReturnModal()"
                        class="flex-1 px-4 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 font-medium transition">
                        Cancel
                    </button>
                    <button type="submit" id="returnSubmitBtn"
                        class="flex-1 px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Save Return
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal (shared by event + allocations) --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-red-100 text-red-600">
                    <i class="fas fa-trash text-xl"></i>
                </div>
                <h3 id="deleteTitle" class="text-lg font-bold text-slate-900 mb-1">Delete?</h3>
                <p id="deleteMessage" class="text-slate-500 text-sm">This action cannot be undone.</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 mb-5">
                <div id="deleteItemName" class="font-semibold text-slate-800"></div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteDialog()"
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
            const baseUrl = @json(url('event-equipment'));

            // ---------- Return modal ----------
            const returnModal = document.getElementById('returnModal');
            const returnForm = document.getElementById('returnForm');
            const qtyInputs = Array.from(document.querySelectorAll('.return-qty'));
            const totalEl = document.getElementById('returnTotal');
            const submitBtn = document.getElementById('returnSubmitBtn');
            let allocatedQty = 0;

            function updateReturnTotal() {
                const total = qtyInputs.reduce((sum, el) => sum + (parseInt(el.value, 10) || 0), 0);
                const over = total > allocatedQty;
                const none = total === 0;
                totalEl.textContent = over
                    ? `Total ${total} is more than the ${allocatedQty} allocated.`
                    : `Accounted for: ${total} of ${allocatedQty}` + (total < allocatedQty && !none ? ' (partial return)' : '');
                totalEl.className = 'mt-2 text-xs ' + (over ? 'text-red-600 font-semibold' : 'text-slate-500');
                submitBtn.disabled = over || none;
            }

            window.openReturnModal = function (id, itemName, qty) {
                allocatedQty = parseInt(qty, 10) || 0;
                returnForm.action = baseUrl + '/' + id + '/return';
                returnForm.reset();
                document.getElementById('returnItemName').textContent = itemName;
                document.getElementById('returnAllocated').textContent = allocatedQty;
                qtyInputs.forEach(el => el.max = allocatedQty);
                document.getElementById('r-good').value = allocatedQty;
                submitBtn.textContent = 'Save Return';
                updateReturnTotal();
                returnModal.classList.remove('hidden');
            };

            window.closeReturnModal = function () { returnModal.classList.add('hidden'); };

            qtyInputs.forEach(el => el.addEventListener('input', updateReturnTotal));
            returnForm.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';
            });
            returnModal.addEventListener('click', e => { if (e.target === returnModal) window.closeReturnModal(); });

            // ---------- Delete modal ----------
            let pendingFormId = null;
            const deleteModal = document.getElementById('deleteModal');

            window.openDeleteDialog = function (formId, title, name, message) {
                pendingFormId = formId;
                document.getElementById('deleteTitle').textContent = title;
                document.getElementById('deleteItemName').textContent = name;
                document.getElementById('deleteMessage').textContent = message;
                deleteModal.classList.remove('hidden');
            };

            window.closeDeleteDialog = function () {
                deleteModal.classList.add('hidden');
                pendingFormId = null;
            };

            document.getElementById('deleteConfirmBtn').addEventListener('click', function () {
                if (!pendingFormId) return;
                this.disabled = true;
                this.textContent = 'Deleting...';
                document.getElementById(pendingFormId).submit();
            });
            deleteModal.addEventListener('click', e => { if (e.target === deleteModal) window.closeDeleteDialog(); });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    window.closeReturnModal();
                    window.closeDeleteDialog();
                }
            });
        })();
    </script>
@endsection