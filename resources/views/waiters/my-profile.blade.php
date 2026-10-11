@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
    @php
        $name = $waiter->user->name ?? 'Unknown';
        $email = $waiter->user->email ?? '—';
        $initial = strtoupper(mb_substr($name, 0, 1));
        $photoUrl = $waiter->photo ? asset('storage/' . $waiter->photo) : null;

        $status = strtolower(trim((string) $waiter->status));
        $isActive = $status === 'active';

        $hasAddress = $waiter->region_id || $waiter->province_id || $waiter->city_id || $waiter->barangay_code || $waiter->address;

        $confConfig = [
            'confirmed' => ['bg-emerald-50 text-emerald-600', 'Confirmed'],
            'pending'   => ['bg-amber-50 text-amber-600', 'Pending'],
            'declined'  => ['bg-red-50 text-red-600', 'Declined'],
        ];
        $allocConfig = [
            'allocated'          => ['bg-blue-50 text-blue-600', 'Allocated'],
            'in_use'             => ['bg-purple-50 text-purple-600', 'In Use'],
            'returned'           => ['bg-emerald-50 text-emerald-600', 'Returned'],
            'partially_returned' => ['bg-amber-50 text-amber-600', 'Partially Returned'],
            'lost'               => ['bg-red-50 text-red-600', 'Lost'],
        ];
        $roleLabels = ['waiter' => 'Waiter', 'head_waiter' => 'Head Waiter', 'setup_crew' => 'Setup Crew'];

        $events = $waiter->events->sortByDesc('event_date');
        $equipment = $waiter->assignedEquipment;

        // Quick stats
        $upcomingCount = $waiter->events->filter(fn ($e) => $e->event_date && $e->event_date->isFuture())->count();
        $pendingConfirm = $waiter->events->filter(fn ($e) => strtolower((string) $e->pivot->confirmation_status) === 'pending')->count();
        $itemsOut = (int) $equipment
            ->filter(fn ($r) => !in_array(strtolower((string) $r->status), ['returned', 'lost']))
            ->sum(fn ($r) => max(0, (int) $r->quantity_allocated - (int) $r->quantity_returned - (int) $r->quantity_damaged - (int) $r->quantity_missing));
    @endphp

    {{-- Header --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 leading-tight">My Profile</h1>
            <p class="text-slate-400 text-sm">Your account, events and equipment</p>
        </div>
        <a href="{{ route('dashboard') }}"
            class="self-start sm:self-auto px-3 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition inline-flex items-center gap-2">
            <i class="fas fa-arrow-left text-xs"></i> Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        {{-- ================= SIDEBAR ================= --}}
        <div class="lg:col-span-4 xl:col-span-3">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden lg:sticky lg:top-4">
                {{-- Photo + name --}}
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 px-4 py-6 text-center border-b border-slate-100">
                    <div class="relative inline-block">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="{{ $name }}" id="profilePhoto"
                                class="w-28 h-28 rounded-full object-cover mx-auto shadow-md ring-4 ring-white bg-white"
                                onerror="this.classList.add('hidden'); document.getElementById('profileInitial').classList.remove('hidden');">
                        @endif
                        <div id="profileInitial" class="{{ $photoUrl ? 'hidden' : '' }} w-28 h-28 bg-white rounded-full flex items-center justify-center mx-auto shadow-md ring-4 ring-white">
                            <span class="text-4xl font-bold text-amber-600">{{ $initial }}</span>
                        </div>
                        <span class="absolute bottom-1.5 right-1.5 w-5 h-5 rounded-full border-2 border-white {{ $isActive ? 'bg-emerald-500' : 'bg-slate-400' }}"
                            title="{{ $isActive ? 'Active' : 'Inactive' }}"></span>
                    </div>

                    <h2 class="mt-3 text-lg font-bold text-slate-800 leading-tight">{{ $name }}</h2>
                    <p class="text-xs text-slate-500 truncate">{{ $email }}</p>
                    <span class="inline-flex items-center mt-2 px-2 py-0.5 rounded-full text-xs font-medium {{ $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-emerald-500' : 'bg-slate-400' }} mr-1.5"></span>
                        {{ ucfirst($status ?: 'inactive') }}
                    </span>
                </div>

                {{-- Contact Details --}}
                <div class="p-4 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-phone text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Phone</p>
                            <p class="text-sm font-semibold {{ $waiter->phone ? 'text-slate-700' : 'text-slate-400 font-normal' }}">{{ $waiter->phone ?: '—' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-first-aid text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Emergency Contact</p>
                            <p class="text-sm font-semibold {{ $waiter->emergency_contact ? 'text-slate-700' : 'text-slate-400 font-normal italic' }}">
                                {{ $waiter->emergency_contact ?: 'Not provided' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-calendar-check text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Date Hired</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $waiter->date_hired?->format('M d, Y') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 pt-3 border-t border-slate-100">
                        <div class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-map-marker-alt text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Address</p>
                            @if($hasAddress)
                                <p class="text-sm font-semibold text-slate-700 leading-snug break-words">{{ $waiter->full_address }}</p>
                            @else
                                <p class="text-sm text-slate-400 italic">No address on file</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= MAIN ================= --}}
        <div class="lg:col-span-8 xl:col-span-9 space-y-4">

            {{-- Quick stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
                    <div class="text-lg font-bold text-slate-800 leading-none">{{ $waiter->events->count() }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">Total Events</div>
                </div>
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
                    <div class="text-lg font-bold text-blue-600 leading-none">{{ $upcomingCount }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">Upcoming</div>
                </div>
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
                    <div class="text-lg font-bold {{ $pendingConfirm ? 'text-amber-600' : 'text-slate-800' }} leading-none">{{ $pendingConfirm }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">Awaiting Confirmation</div>
                </div>
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
                    <div class="text-lg font-bold {{ $itemsOut ? 'text-orange-600' : 'text-slate-800' }} leading-none">{{ $itemsOut }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">Items to Return</div>
                </div>
            </div>

            {{-- Event Assignments --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Event Assignments</h2>
                    <p class="text-xs text-slate-400">{{ $waiter->events->count() }} total {{ Str::plural('event', $waiter->events->count()) }}</p>
                </div>

                @if($events->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[620px]">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Event</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Role</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Time In / Out</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($events as $event)
                                    @php
                                        $conf = strtolower((string) $event->pivot->confirmation_status);
                                        $cConfig = $confConfig[$conf] ?? ['bg-slate-100 text-slate-600', ucfirst($conf ?: 'pending')];
                                        $role = $event->pivot->role;
                                        $timeIn = $event->pivot->time_in ? \Carbon\Carbon::parse($event->pivot->time_in)->format('h:i A') : null;
                                        $timeOut = $event->pivot->time_out ? \Carbon\Carbon::parse($event->pivot->time_out)->format('h:i A') : null;
                                        $isUpcoming = $event->event_date && $event->event_date->isFuture();
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="px-4 py-2">
                                            <a href="{{ route('waiters.my-event', $event) }}"
                                                class="text-xs font-semibold text-slate-800 hover:text-amber-600 transition">{{ $event->event_name }}</a>
                                            @if($isUpcoming)
                                                <span class="ml-1 align-middle text-[10px] font-semibold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">Upcoming</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <span class="text-xs font-medium text-slate-600">{{ $event->event_date?->format('M d, Y') }}</span>
                                            <span class="text-xs text-slate-400 ml-1">{{ $event->event_date?->format('h:i A') }}</span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="inline-block px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600 whitespace-nowrap">
                                                {{ $roleLabels[$role] ?? ucfirst(str_replace('_', ' ', (string) $role)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-xs">
                                            @if($timeIn || $timeOut)
                                                <span class="font-medium text-slate-700">{{ $timeIn ?? '—' }}</span>
                                                <span class="text-slate-400 mx-1">→</span>
                                                <span class="font-medium text-slate-700">{{ $timeOut ?? '—' }}</span>
                                            @else
                                                <span class="text-slate-400 italic">Not recorded</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap {{ $cConfig[0] }}">{{ $cConfig[1] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-4 py-8 text-center text-slate-400">
                        <i class="fas fa-calendar-times text-2xl mb-2 text-slate-200 block"></i>
                        <p class="text-xs">You haven't been assigned to any events yet.</p>
                    </div>
                @endif
            </div>

            {{-- Equipment Accountability --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Equipment Accountability</h2>
                    <p class="text-xs text-slate-400">Items assigned to you by event</p>
                </div>

                @if($equipment->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[520px]">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Event</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($equipment as $eq)
                                    @php
                                        $eqStatus = strtolower(trim((string) $eq->status));
                                        $eqConfig = $allocConfig[$eqStatus] ?? ['bg-slate-100 text-slate-600', ucfirst(str_replace('_', ' ', $eqStatus))];
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="px-4 py-2 text-xs font-semibold text-slate-800">{{ $eq->event->event_name ?? 'Deleted event' }}</td>
                                        <td class="px-4 py-2 text-xs text-slate-600">{{ $eq->equipmentItem->name ?? 'Deleted item' }}</td>
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
                @else
                    <div class="px-4 py-8 text-center text-slate-400">
                        <i class="fas fa-box-open text-2xl mb-2 text-slate-200 block"></i>
                        <p class="text-xs">You don't have any equipment checked out.</p>
                    </div>
                @endif
            </div>

            {{-- Account / Change Email --}}
            @php
                $emailErrors = $errors->updateEmail;
                $emailFormOpen = $emailErrors->any();
            @endphp
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Account</h2>
                        <p class="text-xs text-slate-400">The email you use to log in</p>
                    </div>
                    <button type="button" id="emailEditBtn" onclick="toggleEmailForm(true)"
                        class="{{ $emailFormOpen ? 'hidden' : '' }} inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                        <i class="fas fa-pen text-[10px]"></i> Edit Email
                    </button>
                </div>

                <div class="p-4">
                    @if(session('email_success'))
                        <div class="mb-3 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 flex items-center gap-2 text-sm text-emerald-800">
                            <i class="fas fa-check-circle text-emerald-500"></i>
                            <span class="font-medium">{{ session('email_success') }}</span>
                        </div>
                    @endif

                    {{-- Current email (read-only view) --}}
                    <div id="emailView" class="{{ $emailFormOpen ? 'hidden' : '' }} flex items-center gap-3">
                        <div class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-envelope text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Email</p>
                            <p class="text-sm font-semibold text-slate-700 truncate">{{ $email }}</p>
                        </div>
                    </div>

                    {{-- Edit form --}}
                    <form id="emailForm" action="{{ route('waiters.update-email') }}" method="POST" class="{{ $emailFormOpen ? '' : 'hidden' }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label for="newEmail" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">New Email</label>
                                <input type="email" name="email" id="newEmail" required autocomplete="email"
                                    value="{{ old('email', $waiter->user->email ?? '') }}"
                                    class="w-full px-3 py-1.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent {{ $emailErrors->has('email') ? 'border-red-400' : 'border-slate-200' }}">
                                @if($emailErrors->has('email'))
                                    <p class="mt-1 text-[11px] text-red-600 font-medium">{{ $emailErrors->first('email') }}</p>
                                @endif
                            </div>
                            <div>
                                <label for="emailCurrentPass" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Current Password</label>
                                <div class="relative">
                                    <input type="password" name="current_password" id="emailCurrentPass" required autocomplete="current-password"
                                        class="w-full pl-3 pr-9 py-1.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent {{ $emailErrors->has('current_password') ? 'border-red-400' : 'border-slate-200' }}">
                                    <button type="button" onclick="toggleEye('emailCurrentPass', this)" aria-label="Show password"
                                        class="absolute inset-y-0 right-0 w-9 flex items-center justify-center text-slate-400 hover:text-amber-600">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                </div>
                                @if($emailErrors->has('current_password'))
                                    <p class="mt-1 text-[11px] text-red-600 font-medium">{{ $emailErrors->first('current_password') }}</p>
                                @else
                                    <p class="mt-1 text-[11px] text-slate-400">Needed to confirm it's really you.</p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 flex items-center gap-2">
                            <button type="submit" id="emailSubmit"
                                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-save text-xs"></i> Save Email
                            </button>
                            <button type="button" onclick="toggleEmailForm(false)"
                                class="px-4 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Security / Change Password --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Security</h2>
                    <p class="text-xs text-slate-400">Update your account password</p>
                </div>

                <div class="p-4">
                    @if(session('password_success'))
                        <div class="mb-3 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 flex items-center gap-2 text-sm text-emerald-800">
                            <i class="fas fa-check-circle text-emerald-500"></i>
                            <span class="font-medium">{{ session('password_success') }}</span>
                        </div>
                    @endif

                    @if(session('password_error'))
                        <div class="mb-3 rounded-lg bg-red-50 border border-red-200 px-3 py-2 flex items-center gap-2 text-sm text-red-800">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                            <span class="font-medium">{{ session('password_error') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('waiters.change-password') }}" method="POST" id="passwordForm">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            @foreach([
                                ['current_password', 'waiterCurrentPass', 'Current Password', 'current-password', null],
                                ['new_password', 'waiterNewPass', 'New Password', 'new-password', 'Minimum 6 characters'],
                                ['new_password_confirmation', 'waiterConfirmPass', 'Confirm Password', 'new-password', null],
                            ] as [$field, $id, $label, $autocomplete, $hint])
                                <div>
                                    <label for="{{ $id }}" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">{{ $label }}</label>
                                    <div class="relative">
                                        <input type="password" name="{{ $field }}" id="{{ $id }}" required autocomplete="{{ $autocomplete }}"
                                            @if($field === 'new_password') minlength="6" @endif
                                            class="w-full pl-3 pr-9 py-1.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent {{ $errors->has($field) ? 'border-red-400' : 'border-slate-200' }}">
                                        <button type="button" onclick="toggleEye('{{ $id }}', this)" aria-label="Show password"
                                            class="absolute inset-y-0 right-0 w-9 flex items-center justify-center text-slate-400 hover:text-amber-600">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>
                                    </div>
                                    @error($field)
                                        <p class="mt-1 text-[11px] text-red-600 font-medium">{{ $message }}</p>
                                    @else
                                        @if($hint)<p class="mt-1 text-[11px] text-slate-400">{{ $hint }}</p>@endif
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        <p id="passwordMismatch" class="hidden mt-2 text-[11px] text-red-600 font-medium">Passwords do not match.</p>

                        <div class="mt-3">
                            <button type="submit" id="passwordSubmit"
                                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-key text-xs"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleEye(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = 'fas ' + (show ? 'fa-eye-slash' : 'fa-eye') + ' text-xs';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        }

        function toggleEmailForm(open) {
            const form = document.getElementById('emailForm');
            document.getElementById('emailView').classList.toggle('hidden', open);
            document.getElementById('emailEditBtn').classList.toggle('hidden', open);
            form.classList.toggle('hidden', !open);
            if (open) {
                document.getElementById('newEmail').focus();
            } else {
                form.reset();
            }
        }

        document.getElementById('emailForm').addEventListener('submit', function () {
            const btn = document.getElementById('emailSubmit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> Saving...';
        });

        (function () {
            // Live check that the new password and confirmation match
            const newPass = document.getElementById('waiterNewPass');
            const confirmPass = document.getElementById('waiterConfirmPass');
            const mismatch = document.getElementById('passwordMismatch');
            const form = document.getElementById('passwordForm');
            const submit = document.getElementById('passwordSubmit');

            function check() {
                const bad = confirmPass.value.length > 0 && newPass.value !== confirmPass.value;
                mismatch.classList.toggle('hidden', !bad);
                confirmPass.classList.toggle('border-red-400', bad);
                return !bad;
            }

            newPass.addEventListener('input', check);
            confirmPass.addEventListener('input', check);

            form.addEventListener('submit', function (e) {
                if (!check()) { e.preventDefault(); confirmPass.focus(); return; }
                submit.disabled = true;
                submit.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> Updating...';
            });
        })();
    </script>
@endsection