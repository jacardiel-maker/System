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
        $eventStatus = strtolower(trim((string) $event->status)) ?: 'scheduled';
        $eConfig = $statusConfig[$eventStatus] ?? ['bg-slate-100 text-slate-600', ucfirst($eventStatus)];

        $confConfig = [
            'confirmed' => ['bg-emerald-50 text-emerald-600', 'Confirmed', 'fa-check-circle', 'text-emerald-500'],
            'pending'   => ['bg-amber-50 text-amber-600', 'Pending Response', 'fa-clock', 'text-amber-500'],
            'declined'  => ['bg-red-50 text-red-600', 'Declined', 'fa-times-circle', 'text-red-500'],
        ];
        $confStatus = strtolower((string) $myAssignment->pivot->confirmation_status) ?: 'pending';
        [$confClass, $confLabel, $confIcon, $confIconColor] = $confConfig[$confStatus] ?? ['bg-slate-100 text-slate-600', ucfirst($confStatus), 'fa-question-circle', 'text-slate-400'];

        $allocConfig = [
            'allocated'          => ['bg-blue-50 text-blue-600', 'Allocated'],
            'in_use'             => ['bg-purple-50 text-purple-600', 'In Use'],
            'returned'           => ['bg-emerald-50 text-emerald-600', 'Returned'],
            'partially_returned' => ['bg-amber-50 text-amber-600', 'Partially Returned'],
            'lost'               => ['bg-red-50 text-red-600', 'Lost'],
        ];

        $roleLabels = ['waiter' => 'Waiter', 'head_waiter' => 'Head Waiter', 'setup_crew' => 'Setup Crew'];
        $roleLabel = fn ($r) => $roleLabels[$r] ?? ucfirst(str_replace('_', ' ', (string) $r));

        $myWaiterId = auth()->user()->waiter->id ?? null;
        $isPast = $event->event_date && $event->event_date->isPast();
        $canRespond = $confStatus === 'pending' && !in_array($eventStatus, ['completed', 'cancelled']);

        $timeIn = $myAssignment->pivot->time_in ? \Carbon\Carbon::parse($myAssignment->pivot->time_in)->format('M d, h:i A') : null;
        $timeOut = $myAssignment->pivot->time_out ? \Carbon\Carbon::parse($myAssignment->pivot->time_out)->format('M d, h:i A') : null;

        // Team: me first, then by name
        $team = $event->waiters->sortBy(fn ($w) => [$w->id === $myWaiterId ? 0 : 1, $w->user->name ?? '']);
        $teamConfirmed = $event->waiters->filter(fn ($w) => strtolower((string) $w->pivot->confirmation_status) === 'confirmed')->count();
    @endphp

    {{-- Header --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-1">
                <a href="{{ route('waiters.my-profile') }}" class="hover:text-amber-700 transition-colors">My Profile</a>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <span class="text-slate-600 font-medium truncate">{{ $event->event_name }}</span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-bold text-slate-800 leading-tight">{{ $event->event_name }}</h1>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $eConfig[0] }}">{{ $eConfig[1] }}</span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                <i class="fas fa-calendar text-xs text-slate-400 mr-1"></i>
                {{ $event->event_date?->format('l, F j, Y · g:i A') }}
            </p>
        </div>
        <a href="{{ route('waiters.my-profile') }}"
            class="self-start sm:self-auto px-3 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition inline-flex items-center gap-2">
            <i class="fas fa-arrow-left text-xs"></i> Back
        </a>
    </div>

    {{-- Action needed banner --}}
    @if($canRespond)
        <div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 shrink-0 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                    <i class="fas fa-exclamation text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-amber-900">Please confirm your attendance</p>
                    <p class="text-xs text-amber-700">You were assigned as <strong>{{ $roleLabel($myAssignment->pivot->role) }}</strong> for this event.</p>
                </div>
            </div>
            <div class="flex gap-2 shrink-0">
                <button type="button" onclick="openResponseDialog('confirmed')"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                    <i class="fas fa-check text-[10px]"></i> Confirm
                </button>
                <button type="button" onclick="openResponseDialog('declined')"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-white border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition">
                    <i class="fas fa-times text-[10px]"></i> Decline
                </button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- ================= LEFT ================= --}}
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
                                <p class="text-sm font-semibold text-slate-800 leading-tight">{{ $event->event_date?->format('F j, Y') ?? '—' }}</p>
                                <p class="text-xs text-slate-500">{{ $event->event_date?->format('g:i A') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                <i class="fas fa-clock text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Setup Time</p>
                                <p class="text-sm font-semibold text-slate-800 leading-tight">{{ $event->setup_time?->format('g:i A') ?? '—' }}</p>
                                <p class="text-xs text-slate-500">{{ $event->setup_time?->format('F j, Y') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 sm:col-span-2">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 flex items-center justify-center text-slate-500">
                                <i class="fas fa-map-marker-alt text-xs"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Venue</p>
                                <p class="text-sm font-semibold text-slate-800 leading-snug break-words">{{ $event->venue_address }}</p>
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($event->venue_address) }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1 mt-1 text-xs font-semibold text-amber-700 hover:underline">
                                    <i class="fas fa-directions text-[10px]"></i> Open in Maps
                                </a>
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

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                                <i class="fas fa-id-badge text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Your Role</p>
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-xs font-semibold">
                                    {{ $roleLabel($myAssignment->pivot->role) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($event->notes)
                        <div class="mt-4 rounded-lg bg-amber-50/60 border border-amber-100 p-3">
                            <p class="text-[10px] font-semibold text-amber-700 uppercase tracking-wider mb-1">
                                <i class="fas fa-sticky-note mr-1"></i> Notes
                            </p>
                            <p class="text-sm text-amber-900 leading-relaxed whitespace-pre-line">{{ $event->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Equipment Assigned --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Equipment Assigned to You</h2>
                    <p class="text-xs text-slate-400">
                        {{ $myEquipment->count() }} {{ Str::plural('item', $myEquipment->count()) }}
                        · {{ (int) $myEquipment->sum('quantity_allocated') }} total units
                    </p>
                </div>

                @if($myEquipment->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[480px]">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Category</th>
                                    <th class="px-4 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($myEquipment as $eq)
                                    @php
                                        $eqStatus = strtolower(trim((string) $eq->status));
                                        $eqConfig = $allocConfig[$eqStatus] ?? ['bg-slate-100 text-slate-600', ucfirst(str_replace('_', ' ', $eqStatus))];
                                        $item = $eq->equipmentItem;
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="px-4 py-2">
                                            <div class="flex items-center gap-2.5">
                                                @if($item?->image)
                                                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-8 h-8 shrink-0 rounded-md object-cover bg-slate-100">
                                                @else
                                                    <div class="w-8 h-8 shrink-0 rounded-md bg-amber-50 flex items-center justify-center text-amber-500">
                                                        <i class="fas fa-box text-xs"></i>
                                                    </div>
                                                @endif
                                                <span class="text-xs font-semibold text-slate-800">{{ $item->name ?? 'Deleted item' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-xs text-slate-500">{{ $item?->category?->name ?? '—' }}</td>
                                        <td class="px-4 py-2 text-center">
                                            <span class="inline-flex items-center justify-center min-w-[28px] h-6 px-1.5 rounded-md bg-slate-100 text-slate-700 text-xs font-bold">
                                                {{ (int) $eq->quantity_allocated }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap {{ $eqConfig[0] }}">{{ $eqConfig[1] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-2 bg-slate-50/50 border-t border-slate-100 text-[11px] text-slate-500">
                        <i class="fas fa-info-circle mr-1"></i> You are responsible for returning these items after the event.
                    </div>
                @else
                    <div class="px-4 py-8 text-center text-slate-400">
                        <i class="fas fa-box-open text-2xl mb-2 text-slate-200 block"></i>
                        <p class="text-xs">No equipment assigned to you for this event.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ================= RIGHT ================= --}}
        <div class="space-y-4">

            {{-- Your Status --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Your Status</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex items-center gap-3">
                        <i class="fas {{ $confIcon }} text-2xl {{ $confIconColor }}"></i>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Confirmation</p>
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $confClass }}">{{ $confLabel }}</span>
                        </div>
                    </div>

                    @if($myAssignment->pivot->confirmed_at && $confStatus !== 'pending')
                        <p class="text-[11px] text-slate-400">
                            <i class="fas fa-history mr-1"></i>
                            {{ $confStatus === 'declined' ? 'Declined' : 'Confirmed' }} {{ \Carbon\Carbon::parse($myAssignment->pivot->confirmed_at)->diffForHumans() }}
                        </p>
                    @endif

                    {{-- Attendance (recorded by admin) — only once the waiter has confirmed --}}
                    @if($confStatus === 'confirmed')
                    <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2">
                        <div class="rounded-lg bg-slate-50 px-3 py-2">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Time In</p>
                            <p class="text-xs font-semibold {{ $timeIn ? 'text-slate-700' : 'text-slate-400 italic font-normal' }}">{{ $timeIn ?? 'Not yet' }}</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 px-3 py-2">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Time Out</p>
                            <p class="text-xs font-semibold {{ $timeOut ? 'text-slate-700' : 'text-slate-400 italic font-normal' }}">{{ $timeOut ?? 'Not yet' }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Confirm / Decline buttons live only in the yellow banner at the top --}}
                    @if($canRespond)
                        <p class="pt-3 border-t border-slate-100 text-[11px] text-amber-700">
                            <i class="fas fa-arrow-up mr-1"></i> Use the Confirm or Decline button at the top of the page.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Team --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Team</h2>
                        <p class="text-xs text-slate-400">{{ $teamConfirmed }} of {{ $event->waiters->count() }} confirmed</p>
                    </div>
                </div>
                <div class="p-2 space-y-1">
                    @foreach($team as $w)
                        @php
                            $isMe = $w->id === $myWaiterId;
                            $wName = $w->user->name ?? 'Unknown';
                            $wStatus = strtolower((string) $w->pivot->confirmation_status) ?: 'pending';
                            $wConf = $confConfig[$wStatus] ?? ['bg-slate-100 text-slate-600', ucfirst($wStatus)];
                            $dot = ['confirmed' => 'bg-emerald-500', 'pending' => 'bg-amber-400', 'declined' => 'bg-red-500'][$wStatus] ?? 'bg-slate-400';
                        @endphp
                        <div class="flex items-center gap-2.5 px-2 py-2 rounded-lg {{ $isMe ? 'bg-amber-50 border border-amber-200' : 'hover:bg-slate-50' }}">
                            <div class="relative shrink-0">
                                @if($w->photo)
                                    <img src="{{ asset('storage/' . $w->photo) }}" alt="{{ $wName }}" class="w-8 h-8 rounded-full object-cover bg-slate-100">
                                @else
                                    <div class="w-8 h-8 rounded-full {{ $isMe ? 'bg-amber-200 text-amber-800' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center text-xs font-bold">
                                        {{ strtoupper(mb_substr($wName, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white {{ $dot }}"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-slate-800 truncate leading-tight">
                                    {{ $wName }}
                                    @if($isMe)<span class="text-amber-600 font-medium">(You)</span>@endif
                                </p>
                                <p class="text-[11px] text-slate-400 leading-tight">{{ $roleLabel($w->pivot->role) }}</p>
                            </div>
                            <span class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $wConf[0] }}">
                                {{ $wStatus === 'pending' ? 'Pending' : $wConf[1] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden forms for confirm / decline --}}
    @if($canRespond)
        <form id="respond-confirmed" action="{{ route('events.confirm', $event) }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="status" value="confirmed">
        </form>
        <form id="respond-declined" action="{{ route('events.confirm', $event) }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="status" value="declined">
            <input type="hidden" name="decline_reason" id="declineReasonInput">
        </form>

        {{-- Confirmation Dialog --}}
        <div id="responseModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
                <div class="text-center mb-5">
                    <div id="responseIcon" class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center"></div>
                    <h3 id="responseTitle" class="text-lg font-bold text-slate-900 mb-1"></h3>
                    <p id="responseMessage" class="text-slate-500 text-sm"></p>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 mb-5">
                    <div class="text-xs text-slate-500 mb-0.5">Event</div>
                    <div class="font-semibold text-slate-800">{{ $event->event_name }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">{{ $event->event_date?->format('M d, Y · g:i A') }}</div>
                </div>

                {{-- Reason (only shown when declining) --}}
                <div id="declineReasonBox" class="hidden mb-5">
                    <label for="declineReason" class="block text-xs font-semibold text-slate-700 mb-1">
                        Reason for declining <span class="text-red-500">*</span>
                    </label>
                    <textarea id="declineReason" rows="3" maxlength="500"
                        placeholder="e.g. I have a class on that day / I am sick / family emergency"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent"></textarea>
                    <div class="flex justify-between mt-1">
                        <p id="declineReasonError" class="hidden text-xs text-red-600 font-medium">Please enter at least 5 characters.</p>
                        <p class="text-[11px] text-slate-400 ml-auto"><span id="declineReasonCount">0</span>/500</p>
                    </div>
                </div>

                @error('decline_reason')
                    <p class="mb-3 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror

                <div class="flex gap-3">
                    <button type="button" onclick="closeResponseDialog()"
                        class="flex-1 px-4 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 font-medium transition">
                        Cancel
                    </button>
                    <button type="button" id="responseConfirmBtn"
                        class="flex-1 px-4 py-2.5 text-white rounded-xl font-medium transition">
                        Yes, I'm Sure
                    </button>
                </div>
            </div>
        </div>

        <script>
            (function () {
                const modal = document.getElementById('responseModal');
                const icon = document.getElementById('responseIcon');
                const title = document.getElementById('responseTitle');
                const message = document.getElementById('responseMessage');
                const btn = document.getElementById('responseConfirmBtn');
                const reasonBox = document.getElementById('declineReasonBox');
                const reason = document.getElementById('declineReason');
                const reasonError = document.getElementById('declineReasonError');
                const reasonCount = document.getElementById('declineReasonCount');
                let pending = null;

                reason.addEventListener('input', () => {
                    reasonCount.textContent = reason.value.length;
                    if (reason.value.trim().length >= 5) reasonError.classList.add('hidden');
                });

                window.openResponseDialog = function (action) {
                    pending = action;
                    if (action === 'confirmed') {
                        icon.className = 'w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-emerald-100 text-emerald-600';
                        icon.innerHTML = '<i class="fas fa-check text-xl"></i>';
                        title.textContent = 'Confirm Attendance?';
                        message.textContent = 'You are confirming that you will work at this event.';
                        reasonBox.classList.add('hidden');
                        btn.className = 'flex-1 px-4 py-2.5 text-white rounded-xl font-medium transition bg-emerald-600 hover:bg-emerald-700';
                    } else {
                        icon.className = 'w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-red-100 text-red-600';
                        icon.innerHTML = '<i class="fas fa-times text-xl"></i>';
                        title.textContent = 'Decline This Event?';
                        message.textContent = 'The owner will be notified that you cannot attend. Please tell them why.';
                        reasonBox.classList.remove('hidden');
                        reasonError.classList.add('hidden');
                        setTimeout(() => reason.focus(), 50);
                        btn.className = 'flex-1 px-4 py-2.5 text-white rounded-xl font-medium transition bg-red-600 hover:bg-red-700';
                    }
                    btn.disabled = false;
                    btn.textContent = "Yes, I'm Sure";
                    modal.classList.remove('hidden');
                };

                window.closeResponseDialog = function () {
                    modal.classList.add('hidden');
                    pending = null;
                };

                btn.addEventListener('click', function () {
                    if (!pending) return;
                    if (pending === 'declined') {
                        const text = reason.value.trim();
                        if (text.length < 5) {
                            reasonError.classList.remove('hidden');
                            reason.focus();
                            return;
                        }
                        document.getElementById('declineReasonInput').value = text;
                    }
                    this.disabled = true;
                    this.textContent = 'Saving...';
                    document.getElementById('respond-' + pending).submit();
                });

                modal.addEventListener('click', e => { if (e.target === modal) window.closeResponseDialog(); });
                document.addEventListener('keydown', e => { if (e.key === 'Escape') window.closeResponseDialog(); });

                // Server rejected the reason: reopen the decline dialog with what they typed
                @if($errors->has('decline_reason'))
                    window.openResponseDialog('declined');
                    reason.value = @json(old('decline_reason', ''));
                    reasonCount.textContent = reason.value.length;
                @endif
            })();
        </script>
    @endif
@endsection