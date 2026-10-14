@extends('layouts.app')
@section('title', $waiter->user->name ?? 'Waiter')

@section('content')
    @php
        $name = $waiter->user->name ?? 'Unknown';
        $email = $waiter->user->email ?? '—';
        $initial = strtoupper(mb_substr($name, 0, 1));
        $photoUrl = $waiter->photo ? asset('storage/' . $waiter->photo) : null;

        $status = strtolower(trim((string) $waiter->status));
        $isActive = $status === 'active';

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
    @endphp

    {{-- Header --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-1">
                <a href="{{ route('waiters.index') }}" class="hover:text-amber-700 transition-colors">Waiters</a>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <span class="text-slate-600 font-medium truncate">{{ $name }}</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 leading-tight">Waiter Profile</h1>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('waiters.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
            <a href="{{ route('waiters.edit', $waiter) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition">
                <i class="fas fa-pen text-xs"></i> Edit
            </a>
            <form id="delete-waiter-form" action="{{ route('waiters.destroy', $waiter) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="button" onclick="openDeleteDialog()"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 hover:border-red-200 transition">
                    <i class="fas fa-trash text-xs"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        {{-- Profile Sidebar --}}
        <div class="lg:col-span-4 xl:col-span-3">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden lg:sticky lg:top-4">
                {{-- Photo + name --}}
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 px-4 py-6 text-center border-b border-slate-100">
                    <div class="relative inline-block">
                        @if($photoUrl)
                            <button type="button" onclick="openPhotoViewer()" class="block rounded-full focus:outline-none focus:ring-4 focus:ring-amber-300" title="View photo">
                                <img id="waiterPhoto" src="{{ $photoUrl }}" alt="{{ $name }}"
                                    class="w-28 h-28 rounded-full object-cover mx-auto shadow-md ring-4 ring-white bg-white"
                                    onerror="this.closest('button').replaceWith(document.getElementById('photoFallbackTpl').content.cloneNode(true)); document.getElementById('photoBrokenNote')?.classList.remove('hidden');">
                            </button>
                        @else
                            <div class="w-28 h-28 bg-white rounded-full flex items-center justify-center mx-auto shadow-md ring-4 ring-white">
                                <span class="text-4xl font-bold text-amber-600">{{ $initial }}</span>
                            </div>
                        @endif
                        <span class="absolute bottom-1.5 right-1.5 w-5 h-5 rounded-full border-2 border-white {{ $isActive ? 'bg-emerald-500' : 'bg-slate-400' }}"
                            title="{{ $isActive ? 'Active' : 'Inactive' }}"></span>
                    </div>

                    {{-- Fallback used if the image file can't be loaded --}}
                    <template id="photoFallbackTpl">
                        <div class="w-28 h-28 bg-white rounded-full flex items-center justify-center mx-auto shadow-md ring-4 ring-white">
                            <span class="text-4xl font-bold text-amber-600">{{ $initial }}</span>
                        </div>
                    </template>
                    <p id="photoBrokenNote" class="hidden mt-2 text-[11px] text-red-600">
                        Photo file not found. Run <code class="font-mono">php artisan storage:link</code>.
                    </p>

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
                            @if($waiter->phone)
                                <a href="tel:{{ $waiter->phone }}" class="text-sm font-semibold text-slate-700 hover:text-amber-700">{{ $waiter->phone }}</a>
                            @else
                                <p class="text-sm text-slate-400">—</p>
                            @endif
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
                            <p class="text-sm font-semibold text-slate-700 leading-snug break-words">{{ $waiter->full_address }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="lg:col-span-8 xl:col-span-9 space-y-4">

            {{-- Event History --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Event History</h2>
                    <p class="text-xs text-slate-400">{{ $waiter->events->count() }} {{ Str::plural('event', $waiter->events->count()) }}</p>
                </div>

                @if($waiter->events->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[520px]">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Event</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Role</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($waiter->events->sortByDesc('event_date') as $event)
                                    @php
                                        $conf = strtolower((string) $event->pivot->confirmation_status);
                                        $cConfig = $confConfig[$conf] ?? ['bg-slate-100 text-slate-600', ucfirst($conf ?: 'pending')];
                                        $role = $event->pivot->role;
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="px-4 py-2">
                                            <a href="{{ route('events.show', $event) }}" class="text-xs font-semibold text-slate-800 hover:text-amber-600 transition">{{ $event->event_name }}</a>
                                        </td>
                                        <td class="px-4 py-2 text-xs text-slate-500 whitespace-nowrap">{{ $event->event_date?->format('M d, Y') }}</td>
                                        <td class="px-4 py-2">
                                            <span class="inline-block px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600 whitespace-nowrap">
                                                {{ $roleLabels[$role] ?? ucfirst(str_replace('_', ' ', (string) $role)) }}
                                            </span>
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
                        <p class="text-xs">This waiter hasn't been assigned to any events yet.</p>
                    </div>
                @endif
            </div>

            {{-- Equipment Accountability --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Equipment Accountability</h2>
                    <p class="text-xs text-slate-400">{{ $waiter->assignedEquipment->count() }} {{ Str::plural('item', $waiter->assignedEquipment->count()) }}</p>
                </div>

                @if($waiter->assignedEquipment->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[520px]">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Event</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($waiter->assignedEquipment as $eq)
                                    @php
                                        $eqStatus = strtolower(trim((string) $eq->status));
                                        $eqConfig = $allocConfig[$eqStatus] ?? ['bg-slate-100 text-slate-600', ucfirst(str_replace('_', ' ', $eqStatus))];
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="px-4 py-2 text-xs font-semibold text-slate-800">
                                            {{ $eq->event->event_name ?? 'Deleted event' }}
                                        </td>
                                        <td class="px-4 py-2 text-xs text-slate-600">
                                            {{ $eq->equipmentItem->name ?? 'Deleted item' }}
                                        </td>
                                        <td class="px-4 py-2">
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
                        <p class="text-xs">This waiter doesn't have any equipment checked out.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Photo Viewer --}}
    @if($photoUrl)
        <div id="photoViewer" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4" onclick="closePhotoViewer()">
            <img src="{{ $photoUrl }}" alt="{{ $name }}" class="max-w-full max-h-[85vh] rounded-xl shadow-2xl object-contain">
            <button type="button" onclick="closePhotoViewer()" aria-label="Close"
                class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/10 text-white hover:bg-white/20 flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-red-100 text-red-600">
                    <i class="fas fa-trash text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Delete Waiter?</h3>
                <p class="text-slate-500 text-sm">This also deletes their login account. This action cannot be undone.</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 mb-5">
                <div class="text-xs text-slate-500 mb-0.5">Waiter</div>
                <div class="font-semibold text-slate-800">{{ $name }}</div>
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
            const deleteModal = document.getElementById('deleteModal');
            const viewer = document.getElementById('photoViewer');

            window.openDeleteDialog = () => deleteModal.classList.remove('hidden');
            window.closeDeleteDialog = () => deleteModal.classList.add('hidden');
            window.openPhotoViewer = () => viewer && viewer.classList.remove('hidden');
            window.closePhotoViewer = () => viewer && viewer.classList.add('hidden');

            document.getElementById('deleteConfirmBtn').addEventListener('click', function () {
                this.disabled = true;
                this.textContent = 'Deleting...';
                document.getElementById('delete-waiter-form').submit();
            });

            deleteModal.addEventListener('click', e => { if (e.target === deleteModal) window.closeDeleteDialog(); });

            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') {
                    window.closeDeleteDialog();
                    window.closePhotoViewer();
                }
            });
        })();
    </script>
@endsection