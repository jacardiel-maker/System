@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Analytics detail popup: click a bar / slice / point on a chart to see the records behind it; click a row for the full details --}}
    <div id="drillModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 no-print"
        role="dialog" aria-modal="true" aria-labelledby="drillTitle">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[88vh] flex flex-col">
            <div class="shrink-0 px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3">
                <div class="min-w-0 flex items-start gap-3">
                    <button type="button" id="drillBack" aria-label="Back to the list" title="Back to the list"
                        class="hidden shrink-0 mt-0.5 w-8 h-8 rounded-lg border border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-50 flex items-center justify-center">
                        <i class="fas fa-arrow-left text-xs"></i>
                    </button>
                    <div class="min-w-0">
                        <h3 id="drillTitle" class="text-base font-bold text-slate-900 truncate"></h3>
                        <p id="drillSub" class="text-xs text-slate-500"></p>
                    </div>
                </div>
                <button type="button" id="drillClose" aria-label="Close"
                    class="shrink-0 w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- List pane --}}
            <div id="drillListPane" class="flex-1 min-h-0 flex flex-col">
                <div class="shrink-0 px-5 py-2.5 border-b border-slate-100 flex items-center gap-3">
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                        <input id="drillSearch" type="text" placeholder="Search in this list..." autocomplete="off"
                            class="w-full pl-9 pr-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    </div>
                    <span id="drillCount" class="shrink-0 text-xs font-semibold text-slate-500"></span>
                </div>
                <div class="flex-1 min-h-0 overflow-auto">
                    <table class="w-full min-w-[560px]">
                        <thead id="drillHead" class="sticky top-0 bg-slate-50 z-10"></thead>
                        <tbody id="drillBody" class="divide-y divide-slate-50"></tbody>
                    </table>
                    <div id="drillEmpty" class="hidden px-5 py-10 text-center text-sm text-slate-400">Nothing to show here.</div>
                </div>
                <div id="drillNote" class="hidden shrink-0 px-5 py-2 text-[11px] text-amber-700 bg-amber-50 border-t border-amber-100"></div>
            </div>

            {{-- Detail pane --}}
            <div id="drillDetail" class="hidden flex-1 min-h-0 overflow-auto px-5 py-4"></div>

            <div class="shrink-0 px-5 py-3 border-t border-slate-100 flex justify-end">
                <button type="button" id="drillDone"
                    class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 text-sm font-medium transition">Close</button>
            </div>
        </div>
    </div>

    <script>
        window.drill = (function () {
            const $ = id => document.getElementById(id);
            const modal = $('drillModal'), head = $('drillHead'), body = $('drillBody');
            const search = $('drillSearch'), count = $('drillCount'), empty = $('drillEmpty'), note = $('drillNote');
            const listPane = $('drillListPane'), detailPane = $('drillDetail'), backBtn = $('drillBack');
            let current = { columns: [], rows: [], onRow: null, title: '', subtitle: '' };
            let hasList = false, ticket = 0;

            const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const cap = s => s ? s.charAt(0).toUpperCase() + s.slice(1).replace(/_/g, ' ') : '';
            const badge = (text, cls) => '<span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap ' + (cls || 'bg-slate-100 text-slate-600') + '">' + esc(text) + '</span>';
            const setTitle = (t, s) => { $('drillTitle').textContent = t || ''; $('drillSub').textContent = s || ''; };

            // A small static table (used inside the detail pane). Same column format as the list: { label, key | render, align, nowrap }
            function tableHtml(columns, rows, emptyText) {
                if (!rows.length) return '<p class="text-xs text-slate-400 italic py-2">' + esc(emptyText || 'Nothing here yet.') + '</p>';
                return '<div class="overflow-x-auto rounded-lg border border-slate-100"><table class="w-full min-w-[480px]"><thead class="bg-slate-50"><tr>'
                    + columns.map(c => '<th class="px-3 py-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider ' + (c.align === 'center' ? 'text-center' : 'text-left') + '">' + esc(c.label) + '</th>').join('')
                    + '</tr></thead><tbody class="divide-y divide-slate-50">'
                    + rows.map(r => '<tr>' + columns.map(c => '<td class="px-3 py-2 text-xs text-slate-600 ' + (c.align === 'center' ? 'text-center ' : '') + (c.nowrap ? 'whitespace-nowrap' : '') + '">' + (c.render ? c.render(r) : esc(r[c.key])) + '</td>').join('') + '</tr>').join('')
                    + '</tbody></table></div>';
            }
            const stat = (label, value, hint, cls) => '<div class="rounded-xl border border-slate-100 bg-slate-50/60 px-3 py-2.5"><div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">' + esc(label)
                + '</div><div class="text-lg font-bold ' + (cls || 'text-slate-900') + ' leading-tight">' + esc(value) + '</div>' + (hint ? '<div class="text-[11px] text-slate-500">' + esc(hint) + '</div>' : '') + '</div>';
            const section = (title, inner) => '<div class="mt-5"><h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">' + esc(title) + '</h4>' + inner + '</div>';

            function render() {
                const term = search.value.trim().toLowerCase();
                const rows = term ? current.rows.filter(r => r._search.indexOf(term) !== -1) : current.rows;
                body.innerHTML = rows.map(r => '<tr data-i="' + r._i + '" class="hover:bg-slate-50/70' + (current.onRow ? ' cursor-pointer' : '') + '">' + current.columns.map(c =>
                    '<td class="px-4 py-2 text-xs text-slate-600 ' + (c.align === 'center' ? 'text-center ' : '') + (c.nowrap ? 'whitespace-nowrap' : '') + '">' + (c.render ? c.render(r) : esc(r[c.key])) + '</td>'
                ).join('') + '</tr>').join('');
                empty.classList.toggle('hidden', rows.length > 0);
                count.textContent = rows.length + (rows.length === 1 ? ' record' : ' records') + (term ? ' of ' + current.rows.length : '');
            }

            function showList() {
                ticket++;
                detailPane.classList.add('hidden');
                listPane.classList.remove('hidden');
                backBtn.classList.add('hidden');
                setTitle(current.title, current.subtitle);
            }

            function open(cfg) {
                ticket++;
                hasList = true;
                current = {
                    columns: cfg.columns, onRow: cfg.onRow || null, title: cfg.title || '', subtitle: cfg.subtitle || '',
                    rows: cfg.rows.map((r, i) => Object.assign({}, r, { _i: i, _search: Object.keys(r).map(k => r[k]).join(' ').toLowerCase() }))
                };
                head.innerHTML = '<tr class="border-b border-slate-100">' + cfg.columns.map(c =>
                    '<th class="px-4 py-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider ' + (c.align === 'center' ? 'text-center' : 'text-left') + '">' + esc(c.label) + '</th>'
                ).join('') + '</tr>';
                empty.textContent = cfg.empty || 'Nothing to show here.';
                note.textContent = cfg.note || '';
                note.classList.toggle('hidden', !cfg.note);
                search.value = '';
                showList();
                render();
                modal.classList.remove('hidden');
                $('drillDone').focus();
            }

            // Full details of one record. cfg: { title, subtitle, back: true when opened from a list, load: () => Promise<{ html, title?, subtitle? }> }
            function detail(cfg) {
                const mine = ++ticket;
                hasList = !!cfg.back && current.rows.length > 0;
                listPane.classList.add('hidden');
                detailPane.classList.remove('hidden');
                backBtn.classList.toggle('hidden', !hasList);
                setTitle(cfg.title, cfg.subtitle);
                detailPane.scrollTop = 0;
                detailPane.innerHTML = '<div class="py-16 text-center text-sm text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>Loading details...</div>';
                modal.classList.remove('hidden');
                $('drillDone').focus();
                Promise.resolve().then(cfg.load).then(function (res) {
                    if (mine !== ticket) return; // the user already moved on
                    if (res.title) setTitle(res.title, res.subtitle);
                    detailPane.innerHTML = res.html;
                }).catch(function () {
                    if (mine !== ticket) return;
                    detailPane.innerHTML = '<div class="py-16 text-center text-sm text-red-600"><i class="fas fa-exclamation-circle mr-2"></i>Could not load the details. Please try again.</div>';
                });
            }

            function close() { ticket++; modal.classList.add('hidden'); }

            search.addEventListener('input', render);
            body.addEventListener('click', function (e) {
                if (!current.onRow || e.target.closest('a')) return;
                const tr = e.target.closest('tr[data-i]');
                if (tr) current.onRow(current.rows[Number(tr.dataset.i)]);
            });
            backBtn.addEventListener('click', showList);
            $('drillClose').addEventListener('click', close);
            $('drillDone').addEventListener('click', close);
            modal.addEventListener('click', e => { if (e.target === modal) close(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) close(); });

            // Chart.js options that make a chart clickable: pointer cursor on hover, handler(index, element) on click
            function onChart(handler) {
                return {
                    onClick: function (evt, els) { if (els.length) handler(els[0].index, els[0]); },
                    onHover: function (evt, els) { if (evt.native && evt.native.target) evt.native.target.style.cursor = els.length ? 'pointer' : 'default'; }
                };
            }

            return { open: open, detail: detail, close: close, esc: esc, cap: cap, badge: badge, tableHtml: tableHtml, stat: stat, section: section, onChart: onChart };
        })();
    </script>

    @php
        // Owner = same overview as the admin, but look-only (no buttons / links to pages they cannot open)
        $readOnly = auth()->user()->isOwner();
    @endphp

    @if(auth()->user()->isAdmin() || $readOnly)
        {{-- ADMIN / OWNER DASHBOARD --}}
        @php
            $calendarEvents = \App\Models\Event::orderBy('event_date')->get();
            $calendarEventData = $calendarEvents->map(function ($event) use ($readOnly) {
                return [
                    'id' => $event->id,
                    'title' => $event->event_name,
                    'start' => $event->event_date->toIso8601String(),
                    'end' => $event->event_date->toIso8601String(),
                    'status' => $event->status,
                    'time' => $event->event_date->format('h:i A'),
                    'url' => $readOnly ? null : route('events.show', $event),
                ];
            })->values();

            $eventsByMonth = \App\Models\Event::select(
                \Illuminate\Support\Facades\DB::raw('DATE_FORMAT(event_date, "%Y-%m") as month'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as count')
            )
                ->where('event_date', '>=', now()->startOfMonth()->subMonths(5))
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            $months = [];
            $monthCounts = [];
            $monthRanges = [];
            $weekRanges = [];
            for ($i = 5; $i >= 0; $i--) {
                $monthStart = now()->startOfMonth()->subMonths($i);
                $m = $monthStart->format('Y-m');
                $label = $monthStart->format('M Y');
                $months[] = $label;
                $monthRanges[] = ['from' => $monthStart->format('Y-m-d'), 'to' => $monthStart->copy()->endOfMonth()->format('Y-m-d'), 'label' => $label];
                $monthCounts[] = $eventsByMonth[$m] ?? 0;
            }

            $eventsByWeek = \App\Models\Event::select(
                \Illuminate\Support\Facades\DB::raw('YEARWEEK(event_date, 1) as week'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as count')
            )
                ->where('event_date', '>=', now()->subWeeks(7)->startOfWeek())
                ->groupBy('week')->orderBy('week')->pluck('count', 'week')->toArray();
            $weeks = [];
            $weekCounts = [];
            for ($i = 7; $i >= 0; $i--) {
                $date = now()->subWeeks($i)->startOfWeek();
                $key = (int) $date->format('oW');
                $weeks[] = $date->format('M d');
                $weekRanges[] = ['from' => $date->format('Y-m-d'), 'to' => $date->copy()->endOfWeek()->format('Y-m-d'), 'label' => 'Week of ' . $date->format('M d') . ' – ' . $date->copy()->endOfWeek()->format('M d, Y')];
                $weekCounts[] = $eventsByWeek[$key] ?? 0;
            }

            // Stock per category: how many units exist vs. how many are available right now
            $categoryStock = \App\Models\EquipmentItem::select(
                'equipment_categories.name',
                \Illuminate\Support\Facades\DB::raw('SUM(equipment_items.total_quantity) as total'),
                \Illuminate\Support\Facades\DB::raw('SUM(equipment_items.available_quantity) as available')
            )
                ->join('equipment_categories', 'equipment_items.category_id', '=', 'equipment_categories.id')
                ->groupBy('equipment_categories.name')
                ->orderByDesc('total')
                ->get();
            $categoryLabels = $categoryStock->pluck('name')->values()->all();
            $categoryAvailable = $categoryStock->map(fn ($r) => (int) min($r->available, $r->total))->values()->all();
            $categoryInUse = $categoryStock->map(fn ($r) => (int) max($r->total - $r->available, 0))->values()->all();

            $statusCounts = [
                \App\Models\Event::where('status', 'scheduled')->count(),
                \App\Models\Event::where('status', 'ongoing')->count(),
                \App\Models\Event::where('status', 'completed')->count(),
                \App\Models\Event::where('status', 'cancelled')->count(),
            ];

            // Units reported damaged when equipment came back from an event (event_equipment.quantity_damaged), per item.
            // An item's own "condition" field may not change when that happens, so on its own it would hide them.
            $damagedUnitsByItem = \App\Models\EventEquipment::select('equipment_item_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity_damaged) as units'))
                ->groupBy('equipment_item_id')
                ->havingRaw('SUM(quantity_damaged) > 0')
                ->pluck('units', 'equipment_item_id');

            // An item counts as Damaged if it is marked damaged OR damaged units were reported on a return
            $conditionOf = function ($i) use ($damagedUnitsByItem) {
                $c = strtolower(trim((string) $i->condition));
                return ($c === 'damaged' || (int) ($damagedUnitsByItem[$i->id] ?? 0) > 0) ? 'damaged' : $c;
            };
            $conditionTally = \App\Models\EquipmentItem::select('id', 'condition')->get()->countBy($conditionOf);
            $conditionCounts = array_map(fn ($k) => (int) ($conditionTally[$k] ?? 0), ['new', 'good', 'fair', 'damaged']);

            $topEquipment = \App\Models\EventEquipment::select('equipment_items.name', \Illuminate\Support\Facades\DB::raw('SUM(event_equipment.quantity_allocated) as total'))
                ->join('equipment_items', 'event_equipment.equipment_item_id', '=', 'equipment_items.id')
                ->groupBy('equipment_items.name')
                ->orderByDesc('total')
                ->limit(5)
                ->pluck('total', 'name')
                ->toArray();

            // ---- Data behind the analytics popup (shown when a chart is clicked) ----
            $drillEventsTotal = \App\Models\Event::count();
            $drillEvents = \App\Models\Event::withCount(['waiters', 'equipment'])
                ->orderByDesc('event_date')->limit(1000)->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'name' => $e->event_name,
                    'customer' => $e->customer_name,
                    'date' => optional($e->event_date)->format('Y-m-d'),
                    'dateLabel' => optional($e->event_date)->format('M d, Y'),
                    'time' => optional($e->event_date)->format('h:i A'),
                    'venue' => $e->venue_address,
                    'status' => strtolower(trim((string) $e->status)),
                    'waiters' => (int) $e->waiters_count,
                    'equipment' => (int) $e->equipment_count,
                ])->values();

            $drillItems = \App\Models\EquipmentItem::with('category')->orderBy('name')->limit(2000)->get()
                ->map(fn ($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'code' => $i->serial_code,
                    'category' => $i->category->name ?? 'Uncategorized',
                    'total' => (int) $i->total_quantity,
                    'available' => (int) $i->available_quantity,
                    'inUse' => max((int) $i->total_quantity - (int) $i->available_quantity, 0),
                    'condition' => strtolower(trim((string) $i->condition)),
                    'effective' => $conditionOf($i), // 'damaged' also when damaged units were reported on a return
                    'damagedUnits' => (int) ($damagedUnitsByItem[$i->id] ?? 0),
                ])->values();

            $drillTop = \App\Models\EventEquipment::with(['event', 'equipmentItem', 'assignedWaiter.user'])
                ->whereHas('equipmentItem', fn ($q) => $q->whereIn('name', array_keys($topEquipment)))
                ->orderByDesc('created_at')->get()
                ->groupBy(fn ($a) => $a->equipmentItem->name ?? '')
                ->map(fn ($group) => $group->map(fn ($a) => [
                    'eventId' => $a->event_id,
                    'event' => $a->event->event_name ?? 'Deleted event',
                    'dateLabel' => optional(optional($a->event)->event_date)->format('M d, Y'),
                    'waiter' => $a->assignedWaiter->user->name ?? 'Unassigned',
                    'qty' => (int) $a->quantity_allocated,
                    'returned' => (int) $a->quantity_returned,
                    'status' => strtolower(trim((string) $a->status)),
                ])->values());

            // Same rule as the Equipment Items "Low Stock" filter: available below 20% of total
            $lowStockCount = \App\Models\EquipmentItem::where('available_quantity', '>', 0)
                ->whereRaw('available_quantity * 100 < total_quantity * 20')
                ->count();
        @endphp

        <div class="flex flex-col gap-4">

            {{-- ROW A: title + bell + clock --}}
            <div class="shrink-0 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight leading-tight">Dashboard</h1>
                    <p class="text-xs text-slate-500 truncate">{{ $readOnly ? 'Owner view: a read-only overview of your catering operations.' : "Welcome back, here's what's happening today." }}</p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if($readOnly)
                        <a href="{{ route('reports.index') }}"
                            class="hidden sm:inline-flex items-center gap-2 h-11 px-4 rounded-xl bg-amber-600 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition">
                            <i class="fas fa-chart-pie text-xs"></i> View Reports
                        </a>
                    @else
                    {{-- Notification Bell (desktop only; on mobile it lives in the top bar) --}}
                    <a href="{{ route('notifications.index') }}"
                        class="hidden md:flex relative w-11 h-11 items-center justify-center bg-white rounded-xl shadow-sm border border-slate-100 text-slate-500 hover:text-amber-600 hover:shadow-md transition-all">
                        <i class="fas fa-bell"></i>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span
                                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 rounded-full text-[10px] text-white flex items-center justify-center font-bold animate-pulse">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </a>

                    @endif

                    {{-- Live Clock --}}
                    <div class="inline-flex items-center gap-2.5 bg-white rounded-xl shadow-sm border border-slate-100 px-3 h-11">
                        <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-right">
                            <div id="liveClock" class="text-base font-bold text-slate-900 tabular-nums tracking-tight leading-none">--:--:--</div>
                            <div id="liveDate" class="text-[10px] text-slate-500 font-medium mt-0.5">--</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ROW B: stat cards --}}
            <div class="shrink-0 grid grid-cols-2 lg:grid-cols-4 gap-3">
                <a @if($readOnly) href="#" onclick="openOwnerEvents('all'); return false;" @else href="{{ route('events.index') }}" @endif class="group bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3 hover:border-amber-200 hover:shadow-md transition">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $stats['total_events'] }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Total Events</div>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:text-amber-500 ml-auto transition"></i>
                </a>

                <a @if($readOnly) href="#" onclick="openOwnerEvents('scheduled'); return false;" @else href="{{ route('events.index', ['status' => 'scheduled']) }}" @endif class="group bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3 hover:border-amber-200 hover:shadow-md transition">
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $stats['upcoming_events'] }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Upcoming Events</div>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:text-amber-500 ml-auto transition"></i>
                </a>

                <a @unless($readOnly) href="{{ route('waiters.index') }}" @endunless class="group bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3 {{ $readOnly ? '' : 'hover:border-amber-200 hover:shadow-md' }} transition">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $stats['total_waiters'] }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Active Waiters</div>
                    </div>
                    @unless($readOnly)<i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:text-amber-500 ml-auto transition"></i>@endunless
                </a>

                <a @unless($readOnly) href="{{ route('equipment-items.index', ['stock' => 'low']) }}" @endunless class="group bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3 {{ $readOnly ? '' : 'hover:border-amber-200 hover:shadow-md' }} transition">
                    <div class="w-9 h-9 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $lowStockCount }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Low Stock Items</div>
                    </div>
                    @unless($readOnly)<i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:text-amber-500 ml-auto transition"></i>@endunless
                </a>
            </div>

            {{-- ROW C: upcoming events (left) + calendar (right) --}}
            <div class="shrink-0 lg:h-[calc(100vh-15rem)] lg:min-h-[460px] grid grid-cols-1 lg:grid-cols-12 lg:grid-rows-[minmax(0,1fr)] gap-3">

                {{-- Upcoming Events --}}
                <div class="lg:col-span-4 lg:min-h-0 max-h-[360px] lg:max-h-none bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                    <div class="shrink-0 px-4 py-2.5 border-b border-slate-100 flex justify-between items-center gap-2">
                        <div class="min-w-0 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 inline-flex items-center justify-center shrink-0"><i class="fas fa-calendar-day text-xs"></i></span>
                            <div class="min-w-0">
                                <h2 class="text-sm font-bold text-slate-900 leading-tight">Upcoming Events</h2>
                                <p class="text-[11px] text-slate-500">Scheduled in the near future</p>
                            </div>
                        </div>
                        @unless($readOnly)
                        <a href="{{ route('events.create') }}"
                            class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-700 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            New
                        </a>
                        @endunless
                    </div>
                    <div class="flex-1 min-h-0 overflow-y-auto divide-y divide-slate-100">
                        @forelse($upcomingEvents as $event)
                            <div class="px-4 py-2 flex justify-between items-center gap-2 group hover:bg-slate-50/60 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="w-10 h-10 shrink-0 rounded-lg bg-gradient-to-br from-amber-400 to-orange-400 text-white flex flex-col items-center justify-center text-[10px] font-bold shadow-sm">
                                        <span>{{ $event->event_date->format('M') }}</span>
                                        <span class="text-sm leading-none">{{ $event->event_date->format('d') }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-slate-900 group-hover:text-amber-700 transition-colors truncate">
                                            {{ $event->event_name }}</div>
                                        <div class="text-[11px] text-slate-500 truncate">
                                            {{ $event->event_date->format('h:i A') }} • {{ Str::limit($event->venue_address, 25) }}
                                        </div>
                                    </div>
                                </div>
                                <a @if($readOnly) href="#" onclick="dashboardOpenEvent({{ $event->id }}); return false;" @else href="{{ route('events.show', $event) }}" @endif
                                    class="shrink-0 w-7 h-7 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-center">
                                <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-sm text-slate-500">No upcoming events scheduled.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Calendar --}}
                <div class="lg:col-span-8 lg:min-h-0 fit-cal bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                    <div class="shrink-0 px-4 py-2 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="flex items-center gap-2"><span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 inline-flex items-center justify-center shrink-0"><i class="fas fa-calendar-alt text-xs"></i></span><h2 class="text-sm font-bold text-slate-900">Event Calendar</h2></div>
                        <div class="flex items-center gap-2">
                            <select id="calendarView" onchange="calendarSetView(this.value)" class="h-8 text-xs font-semibold text-slate-600 border border-slate-200 rounded-lg px-2 bg-white hover:bg-slate-50 cursor-pointer">
                                <option value="month">Month</option>
                                <option value="week">Week</option>
                                <option value="day">Day</option>
                            </select>
                            <button type="button" onclick="calendarToday()" class="h-8 px-3 text-xs font-semibold text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50">Today</button>
                            <button type="button" onclick="calendarPrev()" class="w-8 h-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Previous"><i class="fas fa-chevron-left text-xs"></i></button>
                            <button type="button" onclick="calendarNext()" class="w-8 h-8 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Next"><i class="fas fa-chevron-right text-xs"></i></button>
                            <div id="calendarMonthLabel" class="min-w-[140px] text-right text-sm font-bold text-slate-800"></div>
                        </div>
                    </div>

                    <div class="flex-1 min-h-0 flex flex-col px-3 py-2">
                        <div id="calendarFilters" class="shrink-0 flex flex-wrap items-center gap-1.5 mb-2"></div>
                        <div class="flex-1 min-h-0 grid grid-cols-7 content-start overflow-y-auto border-l border-t border-slate-200 rounded-lg">
                            <div id="calendarWeekdays" class="contents">
                                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dayName)
                                    <div class="sticky top-0 z-10 bg-slate-50 border-r border-b border-slate-200 px-2 py-1 text-[10px] sm:text-xs font-bold text-slate-500 text-center uppercase tracking-wide">{{ $dayName }}</div>
                                @endforeach
                            </div>
                            <div id="calendarGrid" class="col-span-7 grid grid-cols-7"></div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                window.dashboardCalendarEvents = @json($calendarEventData);
            </script>

            {{-- ROW D: analytics --}}
            <div class="shrink-0 mt-2">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Analytics</h2>
                <p class="text-xs text-slate-500">Events and equipment at a glance. <span class="text-slate-400">Click a bar, slice or point to see the records behind it.</span></p>
            </div>
            <div class="shrink-0 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Events Trend --}}
                <div class="md:col-span-2 bg-white rounded-xl p-5 shadow-sm border border-slate-100 h-[320px] flex flex-col">
                    <div class="shrink-0 flex items-center justify-between gap-2 mb-4">
                        <h2 class="text-sm font-bold text-slate-800">Events Trend</h2>
                        <select onchange="updateEventsTrend(this.value)" class="text-xs font-semibold border border-slate-200 rounded-lg px-2.5 py-1 text-slate-600 bg-white cursor-pointer">
                            <option value="monthly">Monthly</option>
                            <option value="weekly">Weekly</option>
                        </select>
                    </div>
                    <div class="relative flex-1 min-h-0"><canvas id="eventsChart"></canvas></div>
                </div>

                {{-- Event Status --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 h-[320px] flex flex-col">
                    <h2 class="shrink-0 text-sm font-bold text-slate-800 mb-4">Event Status</h2>
                    <div class="relative flex-1 min-h-0"><canvas id="statusChart"></canvas></div>
                </div>

                {{-- Equipment by Category --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 h-[320px] flex flex-col">
                    <h2 class="shrink-0 text-sm font-bold text-slate-800 mb-4">Stock by Category</h2>
                    <div class="relative flex-1 min-h-0"><canvas id="categoryChart"></canvas></div>
                </div>

                {{-- Equipment Condition --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 h-[320px] flex flex-col">
                    <h2 class="shrink-0 text-sm font-bold text-slate-800 mb-1">Equipment Condition</h2>
                    <p class="shrink-0 text-[11px] text-slate-400 mb-3">Items by condition. Damaged also counts items with damaged units reported on returns.</p>
                    <div class="relative flex-1 min-h-0"><canvas id="conditionChart"></canvas></div>
                </div>

                {{-- Most Used Equipment --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 h-[320px] flex flex-col">
                    <h2 class="shrink-0 text-sm font-bold text-slate-800 mb-4">Most Used Equipment</h2>
                    <div class="relative flex-1 min-h-0"><canvas id="topEquipmentChart"></canvas></div>
                </div>
            </div>
        </div>

        <script>
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#475569';
            const legendStyle = { position: 'bottom', labels: { boxWidth: 12, boxHeight: 12, padding: 14, usePointStyle: true, pointStyle: 'rectRounded', font: { size: 12 } } };

            // Big total in the hole of a doughnut (counts only the slices that are not hidden via the legend)
            const centerTotal = {
                id: 'centerTotal',
                afterDraw(chart, args, opts) {
                    if (!opts || !opts.label) return;
                    const a = chart.chartArea;
                    const total = chart.data.datasets[0].data.reduce((sum, v, i) => sum + (chart.getDataVisibility(i) ? Number(v) : 0), 0);
                    const x = (a.left + a.right) / 2, y = (a.top + a.bottom) / 2, ctx = chart.ctx;
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#0f172a';
                    ctx.font = '700 28px ' + Chart.defaults.font.family;
                    ctx.fillText(total, x, y - 9);
                    ctx.fillStyle = '#64748b';
                    ctx.font = '500 12px ' + Chart.defaults.font.family;
                    ctx.fillText(opts.label, x, y + 15);
                    ctx.restore();
                }
            };

            // Number at the end of each bar so you don't have to read it off the axis
            const valueLabels = {
                id: 'valueLabels',
                afterDatasetsDraw(chart, args, opts) {
                    if (!opts || !opts.display) return;
                    const horizontal = chart.options.indexAxis === 'y', ctx = chart.ctx;
                    ctx.save();
                    ctx.fillStyle = '#334155';
                    ctx.font = '600 12px ' + Chart.defaults.font.family;
                    ctx.textAlign = horizontal ? 'left' : 'center';
                    ctx.textBaseline = horizontal ? 'middle' : 'bottom';
                    chart.data.datasets.forEach(function (ds, di) {
                        chart.getDatasetMeta(di).data.forEach(function (bar, i) {
                            ctx.fillText(ds.data[i], horizontal ? bar.x + 8 : bar.x, horizontal ? bar.y : bar.y - 6);
                        });
                    });
                    ctx.restore();
                }
            };


            // ---------- Click a chart -> popup with the records behind it ----------
            const DRILL = { events: @json($drillEvents), items: @json($drillItems), top: @json($drillTop), eventsTotal: {{ (int) $drillEventsTotal }} };
            const TREND_RANGES = { monthly: @json($monthRanges), weekly: @json($weekRanges) };
            const EVENT_URL = @json(url('events')), ITEM_URL = @json(url('equipment-items'));
            const EVENT_DETAIL_URL = @json(url('dashboard/events')), ITEM_DETAIL_URL = @json(url('dashboard/items'));
            const READ_ONLY = @json($readOnly); // the owner reads everything here in the popups; no links to pages outside Dashboard / Reports
            const nameCell = (href, text) => READ_ONLY
                ? '<span class="font-semibold text-slate-800">' + d.esc(text) + '</span>'
                : '<a href="' + href + '" class="font-semibold text-slate-800 hover:text-amber-600">' + d.esc(text) + '</a>';
            let trendRange = 'monthly';
            const d = window.drill;

            const eventStatusCls = { scheduled: 'bg-blue-50 text-blue-600', ongoing: 'bg-purple-50 text-purple-600', completed: 'bg-emerald-50 text-emerald-600', cancelled: 'bg-red-50 text-red-600' };
            const conditionCls = { new: 'bg-emerald-50 text-emerald-600', good: 'bg-blue-50 text-blue-600', fair: 'bg-amber-50 text-amber-600', damaged: 'bg-red-50 text-red-600' };
            const allocCls = { allocated: 'bg-blue-50 text-blue-600', in_use: 'bg-purple-50 text-purple-600', returned: 'bg-emerald-50 text-emerald-600', partially_returned: 'bg-amber-50 text-amber-600', lost: 'bg-red-50 text-red-600' };
            const plural = (n, w) => n + ' ' + w + (n === 1 ? '' : 's');

            const eventColumns = [
                { label: 'Event', render: r => nameCell(EVENT_URL + '/' + r.id, r.name)
                    + (r.customer ? '<div class="text-slate-500">' + d.esc(r.customer) + '</div>' : '') },
                { label: 'Date', nowrap: true, render: r => d.esc(r.dateLabel) + ' <span class="text-slate-400">' + d.esc(r.time) + '</span>' },
                { label: 'Venue', render: r => '<span class="block max-w-[200px] truncate" title="' + d.esc(r.venue) + '">' + d.esc(r.venue || '—') + '</span>' },
                { label: 'Status', render: r => d.badge(d.cap(r.status || 'scheduled'), eventStatusCls[r.status]) },
                { label: 'Waiters', key: 'waiters', align: 'center' },
                { label: 'Equipment', key: 'equipment', align: 'center' }
            ];
            const itemColumns = [
                { label: 'Item', render: r => nameCell(ITEM_URL + '/' + r.id, r.name)
                    + (r.code ? '<div class="font-mono text-[11px] text-slate-400">' + d.esc(r.code) + '</div>' : '') },
                { label: 'Category', key: 'category' },
                { label: 'Available', render: r => '<span class="font-semibold ' + (r.available <= 0 ? 'text-red-600' : 'text-slate-700') + '">' + r.available + '</span> / ' + r.total, align: 'center' },
                { label: 'In use', key: 'inUse', align: 'center' },
                { label: 'Condition', render: r => d.badge(d.cap(r.effective || r.condition || 'unknown'), conditionCls[r.effective || r.condition]) }
            ];
            const damagedUnitsColumn = { label: 'Damaged units', align: 'center',
                render: r => r.damagedUnits ? '<span class="font-semibold text-red-600">' + r.damagedUnits + '</span>' : '<span class="text-slate-300">—</span>' };

            const statusOrder = ['scheduled', 'ongoing', 'completed', 'cancelled'];
            function eventSummary(rows) {
                const parts = statusOrder.map(k => [k, rows.filter(e => e.status === k).length]).filter(x => x[1] > 0).map(x => x[1] + ' ' + x[0]);
                return plural(rows.length, 'event') + (parts.length ? ' · ' + parts.join(' · ') : '') + (rows.length ? ' · click a row for full details' : '');
            }
            function showEvents(title, rows, extraNote) {
                const truncated = DRILL.events.length < DRILL.eventsTotal;
                d.open({
                    title: title, subtitle: eventSummary(rows), columns: eventColumns, rows: rows, empty: 'No events here.',
                    onRow: r => openEvent(r.id, true),
                    note: truncated && extraNote ? 'Showing the latest ' + DRILL.events.length + ' of ' + DRILL.eventsTotal + ' events.' : ''
                });
            }

            // ---------- Full details of one event (fetched when opened) ----------
            const confCls = { confirmed: 'bg-emerald-50 text-emerald-600', pending: 'bg-amber-50 text-amber-600', declined: 'bg-red-50 text-red-600' };
            function fetchJson(url) {
                return fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); });
            }
            function eventDetailHtml(data) {
                const ev = data.event, w = data.waiters, eq = data.equipment;
                const n = k => w.filter(x => x.conf === k).length;
                const sum = k => eq.reduce((t, x) => t + x[k], 0);
                const units = sum('qty'), ret = sum('returned'), dmg = sum('damaged'), mis = sum('missing');
                const pending = Math.max(units - ret - dmg - mis, 0);
                const line = (icon, text) => text ? '<div class="flex items-start gap-2 text-sm text-slate-600"><i class="fas ' + icon + ' w-4 mt-0.5 text-slate-300 text-xs"></i><span>' + d.esc(text) + '</span></div>' : '';

                const waiterCols = [
                    { label: 'Waiter', render: r => '<span class="font-semibold text-slate-800">' + d.esc(r.name) + '</span>' + (r.phone ? '<div class="text-slate-400">' + d.esc(r.phone) + '</div>' : '') },
                    { label: 'Role', key: 'role' },
                    { label: 'Response', render: r => d.badge(d.cap(r.conf), confCls[r.conf]) + (r.conf === 'declined' && r.reason ? '<div class="mt-1 text-[11px] text-red-600">Reason: ' + d.esc(r.reason) + '</div>' : '') },
                    { label: 'Time in', render: r => d.esc(r.timeIn || '—'), nowrap: true },
                    { label: 'Time out', render: r => d.esc(r.timeOut || '—'), nowrap: true },
                    { label: 'Hours', render: r => d.esc(r.hours || '—'), align: 'center', nowrap: true }
                ];
                const equipCols = [
                    { label: 'Item', render: r => '<span class="font-semibold text-slate-800">' + d.esc(r.item) + '</span>' + (r.code ? '<div class="font-mono text-[11px] text-slate-400">' + d.esc(r.code) + '</div>' : '')
                        + (r.notes ? '<div class="text-[11px] text-slate-500 italic">' + d.esc(r.notes) + '</div>' : '') },
                    { label: 'Assigned to', render: r => d.esc(r.assignee) },
                    { label: 'Qty', key: 'qty', align: 'center' },
                    { label: 'Returned', key: 'returned', align: 'center' },
                    { label: 'Damaged', render: r => r.damaged ? '<span class="font-semibold text-amber-600">' + r.damaged + '</span>' : '0', align: 'center' },
                    { label: 'Missing', render: r => r.missing ? '<span class="font-semibold text-red-600">' + r.missing + '</span>' : '0', align: 'center' },
                    { label: 'Status', render: r => d.badge(d.cap(r.status || 'unknown'), allocCls[r.status]) }
                ];

                return '<div class="flex flex-wrap items-center gap-2 mb-3">' + d.badge(d.cap(ev.status), eventStatusCls[ev.status]) + '</div>'
                    + '<div class="space-y-1">' + line('fa-calendar-day', ev.dateLabel + (ev.time ? ' · ' + ev.time : '')) + line('fa-location-dot', ev.venue) + line('fa-user', ev.customer ? 'Customer: ' + ev.customer : '') + '</div>'
                    + '<div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-2">'
                    + d.stat('Waiters', w.length, n('confirmed') + ' confirmed · ' + n('pending') + ' pending · ' + n('declined') + ' declined')
                    + d.stat('Equipment', eq.length + (eq.length === 1 ? ' item' : ' items'), units + ' units allocated')
                    + d.stat('Returned', ret + ' / ' + units, pending > 0 ? pending + ' still out' : (units ? 'all accounted for' : ''), units && ret === units ? 'text-emerald-600' : 'text-slate-900')
                    + d.stat('Damaged / missing', dmg + ' / ' + mis, '', (dmg + mis) > 0 ? 'text-red-600' : 'text-slate-900')
                    + '</div>'
                    + d.section('Assigned waiters (' + w.length + ')', d.tableHtml(waiterCols, w, 'No waiters assigned yet.'))
                    + d.section('Equipment (' + eq.length + ')', d.tableHtml(equipCols, eq, 'No equipment allocated yet.'))
                    + (READ_ONLY ? '' : '<div class="mt-5 text-right"><a href="' + EVENT_URL + '/' + ev.id + '" class="text-xs font-semibold text-amber-700 hover:underline">Open the full event page <i class="fas fa-arrow-right text-[10px]"></i></a></div>');
            }
            function openEvent(id, fromList) {
                const known = DRILL.events.find(e => Number(e.id) === Number(id));
                d.detail({
                    title: known ? known.name : 'Event details', subtitle: known ? known.dateLabel + ' · ' + known.time : '', back: fromList,
                    load: () => fetchJson(EVENT_DETAIL_URL + '/' + id).then(data => ({ html: eventDetailHtml(data), title: data.event.name, subtitle: data.event.dateLabel + ' · ' + data.event.time }))
                });
            }

            // ---------- Full details of one equipment item ----------
            function itemDetailHtml(data) {
                const it = data.item, h = data.history;
                const pct = it.total > 0 ? Math.max(0, Math.min(100, Math.round(it.available / it.total * 100))) : 0;
                const bar = (it.available <= 0 || it.low) ? 'bg-red-500' : (pct < 50 ? 'bg-amber-500' : 'bg-emerald-500');
                const cols = [
                    { label: 'Event', render: r => '<span class="font-semibold text-slate-800">' + d.esc(r.event) + '</span>' + (r.dateLabel ? '<div class="text-slate-400">' + d.esc(r.dateLabel) + '</div>' : '') },
                    { label: 'Waiter', key: 'waiter' },
                    { label: 'Qty', key: 'qty', align: 'center' },
                    { label: 'Returned', key: 'returned', align: 'center' },
                    { label: 'Damaged', render: r => r.damaged ? '<span class="font-semibold text-amber-600">' + r.damaged + '</span>' : '0', align: 'center' },
                    { label: 'Missing', render: r => r.missing ? '<span class="font-semibold text-red-600">' + r.missing + '</span>' : '0', align: 'center' },
                    { label: 'Status', render: r => d.badge(d.cap(r.status || 'unknown'), allocCls[r.status]) }
                ];
                return '<div class="flex flex-wrap items-center gap-2 mb-3">' + d.badge(d.cap(it.condition || 'unknown'), conditionCls[it.condition])
                    + (it.damagedUnits > 0 ? d.badge(it.damagedUnits + ' damaged ' + (it.damagedUnits === 1 ? 'unit' : 'units') + ' reported', 'bg-red-50 text-red-600') : '')
                    + d.badge(it.category, 'bg-slate-100 text-slate-600')
                    + (it.available <= 0 ? d.badge('Out of stock', 'bg-red-100 text-red-700') : (it.low ? d.badge('Low stock', 'bg-red-50 text-red-600') : '')) + '</div>'
                    + (it.code ? '<div class="text-xs text-slate-500 mb-3">Item code: <span class="font-mono text-slate-700">' + d.esc(it.code) + '</span></div>' : '')
                    + '<div class="grid grid-cols-3 gap-2">' + d.stat('Total stock', it.total) + d.stat('Available', it.available, pct + '% of stock', (it.available <= 0 || it.low) ? 'text-red-600' : 'text-emerald-600') + d.stat('In use', it.inUse) + '</div>'
                    + '<div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full ' + bar + '" style="width:' + pct + '%"></div></div>'
                    + d.section(h.length > 20 ? 'Allocations (latest 20, plus any with damaged or missing units — ' + h.length + ' shown)' : 'Recent allocations (' + h.length + ')', d.tableHtml(cols, h, 'This item has not been allocated to any event yet.'))
                    + (READ_ONLY ? '' : '<div class="mt-5 text-right"><a href="' + ITEM_URL + '/' + it.id + '" class="text-xs font-semibold text-amber-700 hover:underline">Open the full item page <i class="fas fa-arrow-right text-[10px]"></i></a></div>');
            }
            function openItem(id, fromList) {
                const known = DRILL.items.find(i => Number(i.id) === Number(id));
                d.detail({
                    title: known ? known.name : 'Item details', subtitle: known ? known.category : '', back: fromList,
                    load: () => fetchJson(ITEM_DETAIL_URL + '/' + id).then(data => ({ html: itemDetailHtml(data), title: data.item.name, subtitle: data.item.category }))
                });
            }

            // Owner: the Total / Upcoming Events cards, the Upcoming list and the calendar open a popup instead of a page
            window.openOwnerEvents = function (which) {
                if (which === 'scheduled') {
                    showEvents('Upcoming events', DRILL.events.filter(e => e.status === 'scheduled').sort((a, b) => a.date < b.date ? -1 : 1), true);
                } else {
                    showEvents('All events', DRILL.events, true);
                }
            };
            window.dashboardOpenEvent = function (id) { openEvent(id, false); };

            function showItems(title, rows) {
                const units = rows.reduce((t, i) => t + i.total, 0), avail = rows.reduce((t, i) => t + i.available, 0);
                d.open({
                    title: title, columns: rows.some(r => r.damagedUnits > 0) ? itemColumns.concat([damagedUnitsColumn]) : itemColumns,
                    rows: rows, empty: 'No equipment items here.', onRow: r => openItem(r.id, true),
                    subtitle: plural(rows.length, 'item') + (rows.length ? ' · ' + units + ' units · ' + avail + ' available · click a row for full details' : '')
                });
            }

            // Events Trend (Weekly / Monthly) -- a line, because it is a trend over time
            const eventsTrendData = {
                weekly: { labels: {!! json_encode($weeks) !!}, data: {!! json_encode($weekCounts) !!} },
                monthly: { labels: {!! json_encode($months) !!}, data: {!! json_encode($monthCounts) !!} }
            };

            const eventsChart = new Chart(document.getElementById('eventsChart'), {
                type: 'line',
                data: { labels: eventsTrendData.monthly.labels, datasets: [{
                    label: 'Number of Events', data: eventsTrendData.monthly.data,
                    borderColor: '#d97706', backgroundColor: 'rgba(217, 119, 6, 0.1)',
                    fill: true, tension: 0.35, borderWidth: 2.5, pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#d97706'
                }]},
                options: Object.assign({ responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                    interaction: { mode: 'nearest', axis: 'x', intersect: false },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 12 }, padding: 8 }, grid: { color: '#f1f5f9' } },
                        x: { ticks: { font: { size: 12 }, padding: 6 }, grid: { display: false } }
                    }
                }, d.onChart(function (i) {
                    const r = TREND_RANGES[trendRange][i];
                    if (!r) return;
                    showEvents('Events — ' + r.label, DRILL.events.filter(e => e.date >= r.from && e.date <= r.to).sort((a, b) => a.date < b.date ? -1 : 1));
                })
            )});
            window.updateEventsTrend = function(range) {
                trendRange = range;
                eventsChart.data.labels = eventsTrendData[range].labels;
                eventsChart.data.datasets[0].data = eventsTrendData[range].data;
                eventsChart.update();
            };

            // Event Status -- doughnut: parts of one whole (all events), total in the middle
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Scheduled', 'Ongoing', 'Completed', 'Cancelled'],
                    datasets: [{
                        data: {!! json_encode($statusCounts) !!},
                        backgroundColor: ['#3b82f6', '#a855f7', '#10b981', '#ef4444'],
                        borderWidth: 2, borderColor: '#fff', hoverOffset: 4
                    }]
                },
                options: Object.assign({ responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: legendStyle, centerTotal: { label: 'events' } } },
                    d.onChart(function (i) {
                        const key = ['scheduled', 'ongoing', 'completed', 'cancelled'][i];
                        showEvents(d.cap(key) + ' events', DRILL.events.filter(e => e.status === key), true);
                    })),
                plugins: [centerTotal]
            });

            // Stock by Category -- stacked horizontal bars: how much of each category is available vs in use
            new Chart(document.getElementById('categoryChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($categoryLabels) !!},
                    datasets: [
                        { label: 'Available', data: {!! json_encode($categoryAvailable) !!}, backgroundColor: '#10b981', borderRadius: 4, borderSkipped: false },
                        { label: 'In use', data: {!! json_encode($categoryInUse) !!}, backgroundColor: '#f59e0b', borderRadius: 4, borderSkipped: false }
                    ]
                },
                options: Object.assign({
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    datasets: { bar: { maxBarThickness: 26 } },
                    interaction: { mode: 'index', axis: 'y', intersect: false },
                    plugins: {
                        legend: legendStyle,
                        tooltip: { callbacks: { footer: function (items) {
                            return 'Total: ' + items.reduce(function (sum, i) { return sum + Number(i.raw); }, 0);
                        } } }
                    },
                    scales: {
                        x: { stacked: true, beginAtZero: true, ticks: { font: { size: 12 }, padding: 6 }, grid: { color: '#f1f5f9' } },
                        y: { stacked: true, ticks: { font: { size: 12 }, autoSkip: false }, grid: { display: false } }
                    }
                }, d.onChart(function (i) {
                    const name = {!! json_encode($categoryLabels) !!}[i];
                    showItems('Stock — ' + name, DRILL.items.filter(it => it.category === name));
                }))
            });

            // Equipment Condition -- columns in order New -> Damaged (an ordered scale, so not a pie)
            new Chart(document.getElementById('conditionChart'), {
                type: 'bar',
                data: {
                    labels: ['New', 'Good', 'Fair', 'Damaged'],
                    datasets: [{
                        label: 'Items',
                        data: {!! json_encode($conditionCounts) !!},
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                        borderRadius: 6, maxBarThickness: 56
                    }]
                },
                options: Object.assign({
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { top: 22 } },
                    interaction: { mode: 'nearest', axis: 'x', intersect: false },
                    plugins: { legend: { display: false }, valueLabels: { display: true } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0, font: { size: 12 }, padding: 6 }, grid: { color: '#f1f5f9' } },
                        x: { ticks: { font: { size: 12 }, padding: 4 }, grid: { display: false } }
                    }
                }, d.onChart(function (i) {
                    const key = ['new', 'good', 'fair', 'damaged'][i];
                    showItems(d.cap(key) + ' condition', DRILL.items.filter(it => it.effective === key));
                })),
                plugins: [valueLabels]
            });

            // Most Used Equipment -- ranked horizontal bars (long names read better sideways)
            const topEquipmentCanvas = document.getElementById('topEquipmentChart');
            if (topEquipmentCanvas) {
                new Chart(topEquipmentCanvas, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode(array_keys($topEquipment)) !!},
                        datasets: [{
                            label: 'Units Allocated',
                            data: {!! json_encode(array_values($topEquipment)) !!},
                            backgroundColor: '#d97706',
                            borderRadius: 6, maxBarThickness: 28
                        }]
                    },
                    options: Object.assign({
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { right: 36 } },
                        interaction: { mode: 'nearest', axis: 'y', intersect: false },
                        plugins: { legend: { display: false }, valueLabels: { display: true } },
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0, font: { size: 12 }, padding: 6 }, grid: { color: '#f1f5f9' } },
                            y: {
                                grid: { display: false },
                                ticks: {
                                    autoSkip: false,
                                    font: { size: 12 },
                                    callback: function (value) {
                                        const label = String(this.getLabelForValue(value));
                                        const lines = [];
                                        let line = '';
                                        label.split(' ').forEach(function (word) {
                                            if ((line + ' ' + word).trim().length > 20 && line) {
                                                lines.push(line);
                                                line = word;
                                            } else {
                                                line = (line + ' ' + word).trim();
                                            }
                                        });
                                        if (line) lines.push(line);
                                        return lines;
                                    }
                                }
                            }
                        }
                    }, d.onChart(function (i) {
                        const name = {!! json_encode(array_keys($topEquipment)) !!}[i];
                        const rows = DRILL.top[name] || [];
                        d.open({
                            title: name + ' — where it was used', empty: 'No allocations for this item.', rows: rows,
                            subtitle: plural(rows.length, 'allocation') + ' · ' + rows.reduce((t, r) => t + r.qty, 0) + ' units' + (rows.length ? ' · click a row for the event details' : ''),
                            onRow: r => { if (r.eventId) openEvent(r.eventId, true); },
                            columns: [
                                { label: 'Event', render: r => (r.eventId ? nameCell(EVENT_URL + '/' + r.eventId, r.event) : d.esc(r.event)) },
                                { label: 'Date', nowrap: true, render: r => d.esc(r.dateLabel || '—') },
                                { label: 'Waiter', key: 'waiter' },
                                { label: 'Qty', key: 'qty', align: 'center' },
                                { label: 'Returned', key: 'returned', align: 'center' },
                                { label: 'Status', render: r => d.badge(d.cap(r.status || 'unknown'), allocCls[r.status]) }
                            ]
                        });
                    })),
                    plugins: [valueLabels]
                });
            }
        </script>

    @else
        {{-- WAITER DASHBOARD (single-screen on desktop) --}}
        @php
            $waiter = auth()->user()->waiter;
            $myEvents = $waiter ? $waiter->events()->orderBy('event_date')->get() : collect();
            $pendingConfirmations = $myEvents->where('pivot.confirmation_status', 'pending');
            $confirmedEvents = $myEvents->where('pivot.confirmation_status', 'confirmed');

            // "Next Up" = events from today onward that I still need to attend (declined / cancelled ones are left out)
            $today = now()->startOfDay();
            $nextUp = $myEvents->filter(function ($e) use ($today) {
                $conf = strtolower(trim((string) $e->pivot->confirmation_status));
                return $e->event_date
                    && $e->event_date->gte($today)
                    && strtolower(trim((string) $e->status)) !== 'cancelled'
                    && $conf !== 'declined';
            })->sortBy('event_date')->values();
            $nextUpPending = $nextUp->filter(fn ($e) => strtolower(trim((string) $e->pivot->confirmation_status)) === 'pending')->count();

            $myEquipment = $waiter ? $waiter->assignedEquipment()->with(['equipmentItem', 'event'])->get() : collect();
            $equipmentStatus = [
                'returned' => $myEquipment->where('status', 'returned')->count(),
                'allocated' => $myEquipment->where('status', 'allocated')->count(),
                'partially_returned' => $myEquipment->where('status', 'partially_returned')->count(),
                'lost' => $myEquipment->where('status', 'lost')->count(),
            ];
            $eventsByStatus = [
                $myEvents->where('pivot.confirmation_status', 'confirmed')->count(),
                $myEvents->where('pivot.confirmation_status', 'pending')->count(),
                $myEvents->where('pivot.confirmation_status', 'declined')->count(),
            ];

            $confColors = ['confirmed' => '#059669', 'pending' => '#d97706', 'declined' => '#dc2626'];
            $roleNames = ['waiter' => 'Waiter', 'head_waiter' => 'Head Waiter', 'setup_crew' => 'Setup Crew'];
            // One calendar entry per assigned event, colored by MY confirmation (confirmed / pending / declined)
            $calendarEventData = $myEvents->sortBy('event_date')->map(function ($event) use ($confColors, $roleNames) {
                $conf = strtolower(trim((string) $event->pivot->confirmation_status)) ?: 'pending';
                $evStatus = strtolower(trim((string) $event->status));
                $in = $event->pivot->time_in ? \Carbon\Carbon::parse($event->pivot->time_in)->format('h:i A') : null;
                $out = $event->pivot->time_out ? \Carbon\Carbon::parse($event->pivot->time_out)->format('h:i A') : null;

                return [
                    'id' => $event->id,
                    'title' => $event->event_name,
                    'start' => $event->event_date->toIso8601String(),
                    'url' => route('waiters.my-event', $event),
                    'color' => $confColors[$conf] ?? '#64748b',
                    'classNames' => ($conf === 'declined' || $evStatus === 'cancelled') ? ['gc-faded'] : [],
                    'extendedProps' => [
                        'confirmation' => $conf,
                        'eventStatus' => $evStatus ?: 'scheduled',
                        'role' => $roleNames[$event->pivot->role] ?? ucfirst(str_replace('_', ' ', (string) $event->pivot->role)),
                        'venue' => $event->venue_address,
                        'customer' => $event->customer_name,
                        'timeIn' => $in,
                        'timeOut' => $out,
                    ],
                ];
            })->values();
        @endphp

        <div class="flex flex-col gap-4">

            {{-- ROW A: welcome + bell + clock --}}
            <div class="shrink-0 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight leading-tight truncate">Welcome, {{ auth()->user()->name }}!</h1>
                    <p class="text-xs text-slate-500">Here's your schedule and assignments.</p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    {{-- Notification Bell (desktop only; on mobile it lives in the top bar) --}}
                    <a href="{{ route('notifications.index') }}"
                        class="hidden md:flex relative w-11 h-11 items-center justify-center bg-white rounded-xl shadow-sm border border-slate-100 text-slate-500 hover:text-amber-600 hover:shadow-md transition-all">
                        <i class="fas fa-bell"></i>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span
                                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 rounded-full text-[10px] text-white flex items-center justify-center font-bold animate-pulse">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </a>

                    {{-- Live Clock --}}
                    <div class="inline-flex items-center gap-2.5 bg-white rounded-xl shadow-sm border border-slate-100 px-3 h-11">
                        <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-right">
                            <div id="liveClock" class="text-base font-bold text-slate-900 tabular-nums tracking-tight leading-none">--:--:--</div>
                            <div id="liveDate" class="text-[10px] text-slate-500 font-medium mt-0.5">--</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ROW B: stat cards --}}
            <div class="shrink-0 grid grid-cols-3 gap-3">
                <div class="bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $myEvents->count() }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Total Assignments</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $pendingConfirmations->count() }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Pending</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl px-3 py-2 shadow-sm border border-slate-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xl font-bold text-slate-900 leading-none">{{ $confirmedEvents->count() }}</div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 truncate">Confirmed</div>
                    </div>
                </div>
            </div>

            {{-- ROW C: calendar (left) + action required / my events (right) --}}
            <div class="shrink-0 lg:h-[calc(100vh-15rem)] lg:min-h-[460px] grid grid-cols-1 lg:grid-cols-12 lg:grid-rows-[minmax(0,1fr)] gap-3">

                {{-- Calendar (Google-Calendar style) --}}
                <div class="lg:col-span-7 lg:min-h-0 bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                    <div class="shrink-0 px-4 py-2.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2"><span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 inline-flex items-center justify-center shrink-0"><i class="fas fa-calendar-alt text-xs"></i></span><h2 class="text-sm font-bold text-slate-900">My Event Calendar</h2></div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600"><span class="w-2.5 h-2.5 rounded-full" style="background:#059669"></span> Confirmed <span class="text-slate-400">{{ $confirmedEvents->count() }}</span></span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600"><span class="w-2.5 h-2.5 rounded-full" style="background:#d97706"></span> Pending <span class="text-slate-400">{{ $pendingConfirmations->count() }}</span></span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600"><span class="w-2.5 h-2.5 rounded-full" style="background:#dc2626"></span> Declined <span class="text-slate-400">{{ $myEvents->where('pivot.confirmation_status', 'declined')->count() }}</span></span>
                        </div>
                    </div>
                    <div class="relative flex-1 min-h-[460px] lg:min-h-0">
                        <div id="waiterCal" class="gcal px-3 py-3 lg:absolute lg:inset-0"></div>
                    </div>
                </div>

                <style>
        /* Google-Calendar-style look for the FullCalendar views */
        .gcal {
            --fc-border-color: #e8eaed;
            --fc-page-bg-color: #fff;
            --fc-neutral-bg-color: #f8f9fa;
            --fc-today-bg-color: transparent;
            --fc-now-indicator-color: #ea4335;
            --fc-event-border-color: transparent;
            --fc-event-text-color: #fff;
            --fc-more-link-bg-color: transparent;
            --fc-more-link-text-color: #3c4043;
            --fc-list-event-hover-bg-color: #f8f9fa;
            --fc-button-bg-color: #fff;
            --fc-button-border-color: #dadce0;
            --fc-button-text-color: #3c4043;
            --fc-button-hover-bg-color: #f1f3f4;
            --fc-button-hover-border-color: #dadce0;
            --fc-button-active-bg-color: #fef3c7;
            --fc-button-active-border-color: #fcd34d;
            font-family: inherit;
            font-size: 13px;
            color: #3c4043;
        }

        /* ---- Toolbar: [Today] ( < > ) Month Year ............ ( Month | Week | Schedule ) ---- */
        .gcal .fc-header-toolbar { margin-bottom: 1rem; gap: .5rem; }
        .gcal .fc-toolbar-chunk { display: flex; align-items: center; gap: 4px; }
        .gcal .fc-toolbar-title { margin: 0 0 0 .5rem; font-size: 1.375rem; font-weight: 400; color: #3c4043; }
        .gcal .fc-button { height: 2.25rem; padding: 0 1rem; border-radius: 9999px; font-size: .875rem; font-weight: 500; text-transform: none; box-shadow: none !important; }
        .gcal .fc-today-button:disabled { opacity: .55; }
        .gcal .fc-prev-button, .gcal .fc-next-button { width: 2.25rem; padding: 0; margin-left: 0; display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 9999px; background: transparent; color: #5f6368; }
        .gcal .fc-prev-button:hover, .gcal .fc-next-button:hover { background: #f1f3f4; color: #202124; }
        .gcal .fc-toolbar-chunk:last-child .fc-button-group { overflow: hidden; border: 1px solid #dadce0; border-radius: 9999px; background: #fff; }
        .gcal .fc-toolbar-chunk:last-child .fc-button { margin-left: 0; border: 0; border-radius: 0; }
        .gcal .fc-toolbar-chunk:last-child .fc-button + .fc-button { border-left: 1px solid #dadce0; }
        .gcal .fc-button-primary:not(:disabled).fc-button-active,
        .gcal .fc-button-primary:not(:disabled):active { background: #fef3c7; color: #92400e; }

        /* ---- Grid ---- */
        .gcal .fc-theme-standard .fc-scrollgrid { border: 0; }
        .gcal .fc-theme-standard td, .gcal .fc-theme-standard th { border-color: #e8eaed; }
        .gcal .fc-col-header-cell-cushion { padding: 8px 4px; font-size: 11px; font-weight: 500; letter-spacing: .8px; text-transform: uppercase; color: #70757a; text-decoration: none; }

        /* Month: centered day numbers, today = filled circle */
        .gcal .fc-daygrid-day-frame { min-height: 6.75rem; }
        .gcal .fc-daygrid-day-top { justify-content: center; }
        .gcal .fc-daygrid-day-number { width: 1.75rem; height: 1.75rem; margin: 4px 0 2px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 9999px; font-size: 12px; font-weight: 500; color: #3c4043; text-decoration: none; }
        .gcal .fc-day-other .fc-daygrid-day-number { color: #9aa0a6; }
        .gcal .fc-day-today .fc-daygrid-day-number { background: #d97706; color: #fff; }

        /* ---- Events ---- */
        .gcal .fc-event { border-radius: 4px; cursor: pointer; font-weight: 500; transition: box-shadow .12s, filter .12s; }
        .gcal .fc-event:hover { filter: brightness(.94); box-shadow: 0 1px 3px rgba(60, 64, 67, .35); }
        .gcal .fc-daygrid-block-event { margin: 1px 4px; padding: 1px 6px; font-size: 12px; line-height: 1.5; }
        .gcal .fc-daygrid-block-event .fc-event-time { margin-right: 4px; font-weight: 500; opacity: .9; }
        .gcal .fc-more-link { margin: 0 6px; font-size: 11px; font-weight: 600; color: #3c4043; }
        .gcal .fc-more-link:hover { text-decoration: underline; background: transparent; }
        .fc-popover { border: 0; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 28px rgba(60, 64, 67, .28); }
        .fc-popover-header { background: #fff; border-bottom: 1px solid #f1f3f4; font-weight: 500; color: #3c4043; }

        /* ---- Week view: small weekday over a big day number, hour labels, red "now" line ---- */
        .gcal .fc-timegrid .fc-col-header-cell-cushion { padding: 0; }
        .gcal .gc-head { padding: 6px 0 8px; text-align: center; }
        .gcal .gc-dow { font-size: 11px; font-weight: 500; letter-spacing: .8px; text-transform: uppercase; color: #70757a; }
        .gcal .gc-dom { width: 2.75rem; height: 2.75rem; margin: 2px auto 0; border-radius: 9999px; line-height: 2.75rem; font-size: 1.5rem; font-weight: 400; color: #3c4043; }
        .gcal .gc-dom-today { background: #d97706; color: #fff; }
        .gcal .fc-timegrid-slot { height: 1.5rem; border-color: #e8eaed; }
        .gcal .fc-timegrid-slot-minor { border-top-color: transparent; }
        .gcal .fc-timegrid-slot-label-cushion { padding: 0 8px; font-size: 10px; font-weight: 500; text-transform: uppercase; color: #70757a; }
        .gcal .fc-timegrid-event { border-radius: 6px; box-shadow: 0 0 0 1px #fff; }
        .gcal .fc-timegrid-event .fc-event-main { padding: 3px 6px; font-size: 12px; }
        .gcal .fc-timegrid-now-indicator-line { border-width: 2px 0 0; }
        .gcal .fc-timegrid-now-indicator-arrow { width: 12px; height: 12px; margin: -6px 0 0 -6px; border: 0; border-radius: 9999px; background: #ea4335; }

        /* ---- Schedule (list) view ---- */
        .gcal .fc-list { border: 0; }
        .gcal .fc-list-day-cushion { padding: 12px 16px; display: flex; gap: 12px; background: #fff; }
        .gcal .fc-list-day-text, .gcal .fc-list-day-side-text { font-size: 13px; font-weight: 500; color: #3c4043; text-decoration: none; }
        .gcal .fc-list-event td { padding: 10px 16px; border-color: #f1f3f4; }
        .gcal .fc-list-event-time { color: #70757a; }
        .gcal .fc-list-event-title { font-weight: 500; color: #202124; }
        .gcal .fc-list-empty { background: #fff; color: #70757a; }

        @media (max-width: 640px) {
            .gcal .fc-header-toolbar { flex-direction: column; align-items: stretch; }
            .gcal .fc-toolbar-chunk { justify-content: space-between; }
            .gcal .fc-toolbar-title { font-size: 1.125rem; }
            .gcal .fc-toolbar-chunk:last-child .fc-button-group { width: 100%; }
            .gcal .fc-toolbar-chunk:last-child .fc-button { flex: 1; }
        }
        
        /* Keep the time readable ("2pm") and let only the title get the ellipsis */
        .gcal .fc-daygrid-block-event .fc-event-time { flex: none; overflow: visible; }

        /* Declined / cancelled events stay visible but look "off" */
        .gcal .fc-event.gc-faded { opacity: .6; }
        .gcal .fc-event.gc-faded .fc-event-title,
        .gcal .fc-list-event.gc-faded .fc-list-event-title { text-decoration: line-through; }
                </style>
                <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
                <script>
        (function () {
            const el = document.getElementById('waiterCal');
            if (!el || !window.FullCalendar) return;

            // Week view header like Google Calendar: small weekday over a big day number
            function weekHeader(arg) {
                const box = document.createElement('div');
                box.className = 'gc-head';
                const dow = document.createElement('div');
                dow.className = 'gc-dow';
                dow.textContent = arg.date.toLocaleDateString('en-US', { weekday: 'short' });
                const dom = document.createElement('div');
                dom.className = 'gc-dom' + (arg.isToday ? ' gc-dom-today' : '');
                dom.textContent = arg.date.getDate();
                box.appendChild(dow);
                box.appendChild(dom);
                return { domNodes: [box] };
            }

            const calendar = new FullCalendar.Calendar(el, {
                initialView: window.innerWidth < 640 ? 'listMonth' : 'dayGridMonth',
                headerToolbar: { left: 'today prev,next title', center: '', right: 'dayGridMonth,timeGridWeek,listMonth' },
                buttonText: { today: 'Today', month: 'Month', week: 'Week', list: 'Schedule' },
                height: window.matchMedia('(min-width: 1024px)').matches ? '100%' : 'auto',
                firstDay: 0,
                fixedWeekCount: false,
                nowIndicator: true,
                eventDisplay: 'block',
                displayEventEnd: false,
                dayMaxEvents: true,
                moreLinkContent: function (arg) { return '+' + arg.num + ' more'; },
                allDaySlot: false,
                slotDuration: '00:30:00',
                slotLabelFormat: { hour: 'numeric', meridiem: 'short' },
                slotMinTime: '06:00:00',
                slotMaxTime: '24:00:00',
                scrollTime: '08:00:00',
                eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short', omitZeroMinute: true },
                noEventsContent: 'No events assigned in this period.',
                views: {
                    dayGridMonth: { dayHeaderFormat: { weekday: 'short' } },
                    timeGridWeek: { dayHeaderContent: weekHeader }
                },
                events: @json($calendarEventData),
                eventDidMount: function (info) {
                    const p = info.event.extendedProps || {};
                    const lines = [info.event.title];
                    if (p.customer) lines.push('Customer: ' + p.customer);
                    if (p.venue) lines.push('Venue: ' + p.venue);
                    lines.push('My role: ' + p.role + ' | ' + p.confirmation);
                    if (p.timeIn || p.timeOut) lines.push('Check in: ' + (p.timeIn || '—') + '  |  Check out: ' + (p.timeOut || '—'));
                    if (p.eventStatus === 'cancelled') lines.push('This event was cancelled');
                    info.el.title = lines.join('\n');
                }
            });
            calendar.render();
            const wide = window.matchMedia('(min-width: 1024px)');
            const onWide = e => calendar.setOption('height', e.matches ? '100%' : 'auto');
            wide.addEventListener ? wide.addEventListener('change', onWide) : wide.addListener(onWide);
            window.myCalendar = calendar;
        })();
                </script>

                {{-- Right column: Next Up (upcoming only; pending ones can be confirmed / declined right here) --}}
                <div class="lg:col-span-5 lg:min-h-0 max-h-[560px] lg:max-h-none bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                    <div class="shrink-0 px-4 py-2.5 border-b border-slate-100 flex justify-between items-center gap-2">
                        <div class="min-w-0 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 inline-flex items-center justify-center shrink-0"><i class="fas fa-bolt text-xs"></i></span>
                            <div class="min-w-0">
                                <h2 class="text-sm font-bold text-slate-900 leading-tight">Next Up</h2>
                                <p class="text-[11px] text-slate-500">Your upcoming events</p>
                            </div>
                        </div>
                        <a href="{{ route('waiters.my-profile') }}"
                            class="shrink-0 inline-flex items-center gap-1 text-xs font-semibold text-amber-700 hover:text-amber-800 transition-colors">
                            Work History
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>

                    @if($nextUpPending > 0)
                        <div class="shrink-0 px-4 py-2 bg-amber-50 border-b border-amber-100 flex items-center gap-2 text-xs font-semibold text-amber-800">
                            <i class="fas fa-exclamation-triangle text-amber-500"></i>
                            {{ $nextUpPending }} {{ Str::plural('event', $nextUpPending) }} waiting for your response
                        </div>
                    @endif

                    <div class="flex-1 min-h-0 overflow-y-auto divide-y divide-slate-100">
                        @forelse($nextUp as $event)
                            @php
                                $conf = strtolower(trim((string) $event->pivot->confirmation_status)) ?: 'pending';
                                $daysAway = (int) abs(round($today->diffInDays($event->event_date->copy()->startOfDay())));
                                $whenLabel = $daysAway === 0 ? 'Today' : ($daysAway === 1 ? 'Tomorrow' : 'In ' . $daysAway . ' days');
                                $pill = [
                                    'confirmed' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'Confirmed'],
                                    'pending' => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'Pending'],
                                ][$conf] ?? ['bg-slate-50 text-slate-600 ring-slate-500/10', ucfirst($conf)];
                            @endphp
                            <div class="px-4 py-3 flex items-start gap-3 hover:bg-slate-50/60 transition-colors">
                                <div class="w-11 h-11 shrink-0 rounded-lg bg-gradient-to-br from-amber-400 to-orange-400 text-white flex flex-col items-center justify-center text-[10px] font-bold shadow-sm">
                                    <span>{{ $event->event_date->format('M') }}</span>
                                    <span class="text-base leading-none">{{ $event->event_date->format('d') }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <a href="{{ route('waiters.my-event', $event) }}"
                                            class="text-sm font-semibold text-slate-900 hover:text-amber-700 transition-colors truncate">{{ $event->event_name }}</a>
                                        <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 ring-inset {{ $pill[0] }}">{{ $pill[1] }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 truncate">
                                        <span class="font-semibold {{ $daysAway <= 1 ? 'text-amber-700' : 'text-slate-600' }}">{{ $whenLabel }}</span>
                                        • {{ $event->event_date->format('D, h:i A') }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate">
                                        {{ ucfirst(str_replace('_', ' ', (string) $event->pivot->role)) }}@if($event->venue_address) • {{ Str::limit($event->venue_address, 40) }}@endif
                                    </div>

                                    @if($conf === 'pending')
                                        <div class="mt-2 flex gap-1.5">
                                            <button type="button"
                                                onclick="openConfirmDialog({{ $event->id }}, {{ json_encode($event->event_name) }}, 'confirmed')"
                                                class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Confirm
                                            </button>
                                            <button type="button"
                                                onclick="openConfirmDialog({{ $event->id }}, {{ json_encode($event->event_name) }}, 'declined')"
                                                class="inline-flex items-center gap-1 rounded-md bg-rose-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-rose-700 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Decline
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-center">
                                <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-sm text-slate-500">No upcoming events.</p>
                                <p class="text-xs text-slate-400 mt-1">Past events are in your Work History.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ROW D: analytics --}}
            <div class="shrink-0 mt-2">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Analytics</h2>
                <p class="text-xs text-slate-500">Your participation and equipment at a glance. <span class="text-slate-400">Click a bar or slice to see the records behind it.</span></p>
            </div>
            <div class="shrink-0 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 h-[320px] flex flex-col">
                    <h2 class="shrink-0 text-sm font-bold text-slate-800 mb-4">My Participation</h2>
                    <div class="relative flex-1 min-h-0"><canvas id="waiterStatusChart"></canvas></div>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 h-[320px] flex flex-col">
                    <h2 class="shrink-0 text-sm font-bold text-slate-800 mb-4">My Equipment Status</h2>
                    <div class="relative flex-1 min-h-0"><canvas id="waiterEquipmentChart"></canvas></div>
                </div>
            </div>
        </div>

        @php
            // ---- Data behind the analytics popup (shown when a chart is clicked) ----
            $drillMine = $myEvents->sortBy('event_date')->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->event_name,
                'customer' => $e->customer_name,
                'date' => optional($e->event_date)->format('Y-m-d'),
                'dateLabel' => optional($e->event_date)->format('M d, Y'),
                'time' => optional($e->event_date)->format('h:i A'),
                'venue' => $e->venue_address,
                'role' => $roleNames[$e->pivot->role] ?? ucfirst(str_replace('_', ' ', (string) $e->pivot->role)),
                'conf' => strtolower(trim((string) $e->pivot->confirmation_status)) ?: 'pending',
                'status' => strtolower(trim((string) $e->status)) ?: 'scheduled',
                'url' => route('waiters.my-event', $e),
            ])->values();

            $drillEquip = $myEquipment->map(fn ($a) => [
                'item' => $a->equipmentItem->name ?? 'Deleted item',
                'event' => $a->event->event_name ?? 'Deleted event',
                'url' => $a->event ? route('waiters.my-event', $a->event) : null,
                'dateLabel' => optional(optional($a->event)->event_date)->format('M d, Y'),
                'qty' => (int) $a->quantity_allocated,
                'returned' => (int) $a->quantity_returned,
                'status' => strtolower(trim((string) $a->status)),
            ])->values();
        @endphp

        <script>
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#475569';

            const centerTotal = {
                id: 'centerTotal',
                afterDraw(chart, args, opts) {
                    if (!opts || !opts.label) return;
                    const a = chart.chartArea;
                    const total = chart.data.datasets[0].data.reduce((sum, v, i) => sum + (chart.getDataVisibility(i) ? Number(v) : 0), 0);
                    const x = (a.left + a.right) / 2, y = (a.top + a.bottom) / 2, ctx = chart.ctx;
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#0f172a';
                    ctx.font = '700 28px ' + Chart.defaults.font.family;
                    ctx.fillText(total, x, y - 9);
                    ctx.fillStyle = '#64748b';
                    ctx.font = '500 12px ' + Chart.defaults.font.family;
                    ctx.fillText(opts.label, x, y + 15);
                    ctx.restore();
                }
            };
            const valueLabels = {
                id: 'valueLabels',
                afterDatasetsDraw(chart, args, opts) {
                    if (!opts || !opts.display) return;
                    const ctx = chart.ctx;
                    ctx.save();
                    ctx.fillStyle = '#334155';
                    ctx.font = '600 12px ' + Chart.defaults.font.family;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    chart.data.datasets.forEach(function (ds, di) {
                        chart.getDatasetMeta(di).data.forEach(function (bar, i) { ctx.fillText(ds.data[i], bar.x, bar.y - 6); });
                    });
                    ctx.restore();
                }
            };


            // ---------- Click a chart -> popup with the records behind it ----------
            const MINE = @json($drillMine), EQUIP = @json($drillEquip);
            const d = window.drill;
            const plural = (n, w) => n + ' ' + w + (n === 1 ? '' : 's');
            const confCls = { confirmed: 'bg-emerald-50 text-emerald-600', pending: 'bg-amber-50 text-amber-600', declined: 'bg-red-50 text-red-600' };
            const eventStatusCls = { scheduled: 'bg-blue-50 text-blue-600', ongoing: 'bg-purple-50 text-purple-600', completed: 'bg-emerald-50 text-emerald-600', cancelled: 'bg-red-50 text-red-600' };
            const allocCls = { allocated: 'bg-blue-50 text-blue-600', in_use: 'bg-purple-50 text-purple-600', returned: 'bg-emerald-50 text-emerald-600', partially_returned: 'bg-amber-50 text-amber-600', lost: 'bg-red-50 text-red-600' };

            // My Participation -- doughnut: confirmed / pending / declined out of all my assignments
            new Chart(document.getElementById('waiterStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Confirmed', 'Pending', 'Declined'],
                    datasets: [{
                        data: {!! json_encode($eventsByStatus) !!},
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 2, borderColor: '#fff', hoverOffset: 4
                    }]
                },
                options: Object.assign({
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, boxHeight: 12, padding: 14, usePointStyle: true, pointStyle: 'rectRounded', font: { size: 12 } } },
                        centerTotal: { label: 'assignments' }
                    }
                }, d.onChart(function (i) {
                    const key = ['confirmed', 'pending', 'declined'][i];
                    const rows = MINE.filter(e => e.conf === key);
                    d.open({
                        title: d.cap(key) + ' assignments', subtitle: plural(rows.length, 'event'), empty: 'No events here.', rows: rows,
                        columns: [
                            { label: 'Event', render: r => '<a href="' + d.esc(r.url) + '" class="font-semibold text-slate-800 hover:text-amber-600">' + d.esc(r.name) + '</a>'
                                + (r.customer ? '<div class="text-slate-500">' + d.esc(r.customer) + '</div>' : '') },
                            { label: 'Date', nowrap: true, render: r => d.esc(r.dateLabel) + ' <span class="text-slate-400">' + d.esc(r.time) + '</span>' },
                            { label: 'Venue', render: r => '<span class="block max-w-[200px] truncate" title="' + d.esc(r.venue) + '">' + d.esc(r.venue || '—') + '</span>' },
                            { label: 'My role', key: 'role' },
                            { label: 'My response', render: r => d.badge(d.cap(r.conf), confCls[r.conf]) },
                            { label: 'Event status', render: r => d.badge(d.cap(r.status), eventStatusCls[r.status]) }
                        ]
                    });
                })),
                plugins: [centerTotal]
            });

            // My Equipment Status -- columns with the count on top
            new Chart(document.getElementById('waiterEquipmentChart'), {
                type: 'bar',
                data: {
                    labels: ['Returned', 'Allocated', 'Partial', 'Lost'],
                    datasets: [{
                        label: 'Count',
                        data: {!! json_encode(array_values($equipmentStatus)) !!},
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                        borderRadius: 6, maxBarThickness: 56
                    }]
                },
                options: Object.assign({
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { top: 22 } },
                    interaction: { mode: 'nearest', axis: 'x', intersect: false },
                    plugins: { legend: { display: false }, valueLabels: { display: true } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0, font: { size: 12 }, padding: 6 }, grid: { color: '#f1f5f9' } },
                        x: { ticks: { font: { size: 12 }, padding: 4 }, grid: { display: false } }
                    }
                }, d.onChart(function (i) {
                    const key = ['returned', 'allocated', 'partially_returned', 'lost'][i];
                    const rows = EQUIP.filter(a => a.status === key);
                    d.open({
                        title: d.cap(key) + ' equipment', subtitle: plural(rows.length, 'allocation'), empty: 'No equipment here.', rows: rows,
                        columns: [
                            { label: 'Item', key: 'item' },
                            { label: 'Event', render: r => (r.url ? '<a href="' + d.esc(r.url) + '" class="font-semibold text-slate-800 hover:text-amber-600">' + d.esc(r.event) + '</a>' : d.esc(r.event))
                                + (r.dateLabel ? '<div class="text-slate-400">' + d.esc(r.dateLabel) + '</div>' : '') },
                            { label: 'Qty', key: 'qty', align: 'center' },
                            { label: 'Returned', key: 'returned', align: 'center' },
                            { label: 'Status', render: r => d.badge(d.cap(r.status), allocCls[r.status]) }
                        ]
                    });
                })),
                plugins: [valueLabels]
            });
        </script>

        {{-- Hidden Forms for Dialog Submission --}}
        @foreach($pendingConfirmations as $event)
            <form id="confirm-form-{{ $event->id }}" action="{{ route('events.confirm', $event) }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="status" value="confirmed">
            </form>
            <form id="decline-form-{{ $event->id }}" action="{{ route('events.confirm', $event) }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="status" value="declined">
                <input type="hidden" name="decline_reason" class="decline-reason-input">
            </form>
        @endforeach

        {{-- Confirmation Dialog Modal --}}
        <div id="confirmModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 transform transition-all">
                <div class="text-center mb-6">
                    <div id="modalIcon" class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl">
                        <!-- Icon injected by JS -->
                    </div>
                    <h3 id="modalTitle" class="text-xl font-bold text-slate-900 mb-2">Are you sure?</h3>
                    <p id="modalMessage" class="text-slate-500 text-sm">Please confirm your action.</p>
                </div>

                <div class="bg-slate-50 rounded-xl p-4 mb-6">
                    <div class="text-sm text-slate-500 mb-1">Event</div>
                    <div id="modalEventName" class="font-semibold text-slate-800 text-lg"></div>
                    <div id="modalActionText" class="text-sm mt-2 font-medium"></div>
                </div>

                {{-- Reason (only shown when declining) --}}
                <div id="declineReasonBox" class="hidden mb-6">
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

                <div class="flex gap-3">
                    <button type="button" onclick="closeConfirmDialog()" class="flex-1 px-4 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 font-medium transition">
                        Cancel
                    </button>
                    <button type="button" id="modalConfirmBtn" class="flex-1 px-4 py-2.5 text-white rounded-xl font-medium transition">
                        Yes, I'm Sure
                    </button>
                </div>
            </div>
        </div>
    @endif


    <style>
        #calendarGrid > .calendar-cell {
            min-height: 72px;
        }
        .calendar-cell {
            position: relative;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 3px;
            background: #fff;
        }
        .calendar-cell.clickable { cursor: pointer; }
        .calendar-cell.clickable:hover { background: #fffbeb; }
        .calendar-cell.muted {
            background: #f8fafc;
        }
        .calendar-cell.today {
            background: #fffdf5;
        }
        .calendar-day {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            border-radius: 9999px;
            margin-bottom: 3px;
        }
        .calendar-cell.today .calendar-day {
            background: #f59e0b;
            color: white;
        }
        .calendar-event {
            display: block;
            width: 100%;
            text-align: left;
            border-radius: 5px;
            padding: 2px 4px;
            margin-top: 2px;
            font-size: 9px;
            line-height: 1.1;
            font-weight: 600;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            text-decoration: none;
        }
        #calendarGrid > .calendar-cell.tall { min-height: 160px; }
        .calendar-event.large {
            font-size: 12px;
            line-height: 1.3;
            padding: 6px 8px;
            margin-top: 4px;
            white-space: normal;
        }
        .calendar-event.scheduled { background:#dbeafe; color:#1d4ed8; }
        .calendar-event.ongoing { background:#f3e8ff; color:#7e22ce; }
        .calendar-event.completed { background:#dcfce7; color:#15803d; }
        .calendar-event.cancelled { background:#f1f5f9; color:#475569; }

        .calendar-more {
            display: block;
            font-size: 9px;
            font-weight: 600;
            color: #d97706;
            margin-top: 2px;
            padding: 0 4px;
            background: none;
            border: 0;
            cursor: pointer;
        }
        .calendar-more:hover { text-decoration: underline; }

        .cal-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            border: 1px solid #e2e8f0;
            background: #fff;
            font-size: 11px;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            transition: all .15s;
        }
        .cal-chip:hover { background: #f8fafc; }
        .cal-chip .dot { width: 8px; height: 8px; border-radius: 9999px; }
        .cal-chip .count { font-size: 10px; color: #94a3b8; font-weight: 700; }
        .cal-chip.off { opacity: .45; }
        .cal-chip.off .label { text-decoration: line-through; }
        .cal-chip.all { background: #f1f5f9; }

        @media (min-width: 1024px) {
            .fit-cal #calendarGrid { grid-auto-rows: minmax(72px, auto); }
            .fit-cal #calendarGrid > .calendar-cell.tall { overflow-y: auto; }
        }
    </style>
    <script>
        (function () {
            const events = window.dashboardCalendarEvents || [];
            const READ_ONLY_CAL = events.length > 0 && events.every(ev => !ev.url); // owner: events carry no page link

            const statusList = [
                { key: 'scheduled', label: 'Scheduled', color: '#3b82f6' },
                { key: 'ongoing',   label: 'Ongoing',   color: '#a855f7' },
                { key: 'completed', label: 'Completed', color: '#10b981' },
                { key: 'cancelled', label: 'Cancelled', color: '#64748b' },
            ];
            const statusKeys = statusList.map(s => s.key);
            const activeStatuses = new Set(statusKeys);

            // Normalize status (case/spacing) and treat unknown values as "scheduled"
            // so no event silently disappears when "All" is selected.
            function eventStatus(event) {
                const s = String(event.status || 'scheduled').trim().toLowerCase();
                return statusKeys.includes(s) ? s : 'scheduled';
            }

            function renderFilters() {
                const box = document.getElementById('calendarFilters');
                if (!box) return;

                box.innerHTML = '';

                const allOn = activeStatuses.size === statusList.length;
                const allBtn = document.createElement('button');
                allBtn.type = 'button';
                allBtn.className = 'cal-chip all' + (allOn ? '' : ' off');
                allBtn.innerHTML = '<span class="label">All</span><span class="count">' + events.length + '</span>';
                allBtn.addEventListener('click', () => {
                    statusList.forEach(s => activeStatuses.add(s.key));
                    renderFilters();
                    renderCalendar();
                });
                box.appendChild(allBtn);

                statusList.forEach(s => {
                    const count = events.filter(e => eventStatus(e) === s.key).length;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'cal-chip' + (activeStatuses.has(s.key) ? '' : ' off');
                    btn.innerHTML =
                        '<span class="dot" style="background:' + s.color + '"></span>' +
                        '<span class="label">' + s.label + '</span>' +
                        '<span class="count">' + count + '</span>';
                    btn.addEventListener('click', () => {
                        if (activeStatuses.has(s.key)) {
                            activeStatuses.delete(s.key);
                        } else {
                            activeStatuses.add(s.key);
                        }
                        renderFilters();
                        renderCalendar();
                    });
                    box.appendChild(btn);
                });
            }
            let view = 'month';
            let current = new Date();
            current.setHours(0, 0, 0, 0);

            function dateKey(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            function addDays(date, n) {
                const x = new Date(date);
                x.setDate(x.getDate() + n);
                return x;
            }

            function startOfWeek(date) {
                return addDays(date, -date.getDay());
            }

            function eventsFor(date) {
                const key = dateKey(date);
                return events.filter(ev =>
                    activeStatuses.has(eventStatus(ev)) && dateKey(new Date(ev.start)) === key
                );
            }

            function openDay(date) {
                current = new Date(date);
                current.setHours(0, 0, 0, 0);
                view = 'day';
                renderCalendar();
            }

            function buildEventLink(ev, large) {
                const status = eventStatus(ev);
                const link = document.createElement('a');
                if (ev.url) {
                    link.href = ev.url;
                } else if (READ_ONLY_CAL) {
                    // owner: no event page, show the details in a popup instead
                    link.style.cursor = 'pointer';
                    link.tabIndex = 0;
                    const open = () => { if (window.dashboardOpenEvent) window.dashboardOpenEvent(ev.id); };
                    link.addEventListener('click', e => { e.preventDefault(); open(); });
                    link.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); open(); } });
                } else {
                    link.style.cursor = 'default';
                }
                link.className = 'calendar-event ' + status + (large ? ' large' : '');
                link.title = `${ev.title} • ${ev.time}`;
                link.textContent = large
                    ? `${ev.time} — ${ev.title} (${status})`
                    : `${ev.time} ${ev.title}`;
                link.addEventListener('click', e => e.stopPropagation());
                return link;
            }

            function buildCell(date, opts) {
                opts = opts || {};
                const cell = document.createElement('div');
                cell.className = 'calendar-cell' + (opts.muted ? ' muted' : '') + (opts.tall ? ' tall' : '');

                if (dateKey(date) === dateKey(new Date())) {
                    cell.classList.add('today');
                }

                const day = document.createElement('div');
                day.className = 'calendar-day';
                day.textContent = date.getDate();
                cell.appendChild(day);

                const dayEvents = eventsFor(date);
                const max = opts.max || 2;
                dayEvents.slice(0, max).forEach(ev => cell.appendChild(buildEventLink(ev, false)));

                if (dayEvents.length > max) {
                    const more = document.createElement('button');
                    more.type = 'button';
                    more.className = 'calendar-more';
                    more.textContent = `+${dayEvents.length - max} more`;
                    more.addEventListener('click', e => { e.stopPropagation(); openDay(date); });
                    cell.appendChild(more);
                }

                // Month view: click a day to open it in Day view
                if (!opts.tall) {
                    cell.classList.add('clickable');
                    cell.addEventListener('click', () => openDay(date));
                }
                return cell;
            }

            function updateLabel() {
                const label = document.getElementById('calendarMonthLabel');
                if (!label) return;

                if (view === 'month') {
                    label.textContent = current.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                } else if (view === 'week') {
                    const start = startOfWeek(current);
                    const end = addDays(start, 6);
                    const short = { month: 'short', day: 'numeric' };
                    if (start.getFullYear() !== end.getFullYear()) {
                        label.textContent = `${start.toLocaleDateString('en-US', { ...short, year: 'numeric' })} – ${end.toLocaleDateString('en-US', { ...short, year: 'numeric' })}`;
                    } else if (start.getMonth() !== end.getMonth()) {
                        label.textContent = `${start.toLocaleDateString('en-US', short)} – ${end.toLocaleDateString('en-US', short)}, ${end.getFullYear()}`;
                    } else {
                        label.textContent = `${start.toLocaleDateString('en-US', short)} – ${end.getDate()}, ${end.getFullYear()}`;
                    }
                } else {
                    label.textContent = current.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                }
            }

            function renderCalendar() {
                const grid = document.getElementById('calendarGrid');
                const weekdays = document.getElementById('calendarWeekdays');
                if (!grid) return;

                updateLabel();
                grid.innerHTML = '';

                const select = document.getElementById('calendarView');
                if (select) select.value = view;

                if (view === 'day') {
                    if (weekdays) weekdays.style.display = 'none';
                    grid.className = 'col-span-7 grid grid-cols-1';

                    const cell = document.createElement('div');
                    cell.className = 'calendar-cell tall' + (dateKey(current) === dateKey(new Date()) ? ' today' : '');

                    const dayEvents = eventsFor(current);
                    if (dayEvents.length === 0) {
                        const empty = document.createElement('div');
                        empty.className = 'text-xs text-slate-400 p-3';
                        empty.textContent = 'No events on this day.';
                        cell.appendChild(empty);
                    } else {
                        dayEvents.forEach(ev => cell.appendChild(buildEventLink(ev, true)));
                    }
                    grid.appendChild(cell);
                    return;
                }

                if (weekdays) weekdays.style.display = 'contents';
                grid.className = 'col-span-7 grid grid-cols-7';

                if (view === 'week') {
                    const start = startOfWeek(current);
                    for (let i = 0; i < 7; i++) {
                        grid.appendChild(buildCell(addDays(start, i), { tall: true, max: 10 }));
                    }
                    return;
                }

                // month view
                const first = new Date(current.getFullYear(), current.getMonth(), 1);
                const start = startOfWeek(first);
                for (let i = 0; i < 42; i++) {
                    const date = addDays(start, i);
                    grid.appendChild(buildCell(date, { muted: date.getMonth() !== current.getMonth() }));
                }
            }

            function shiftMonth(delta) {
                const targetFirst = new Date(current.getFullYear(), current.getMonth() + delta, 1);
                const daysInTarget = new Date(targetFirst.getFullYear(), targetFirst.getMonth() + 1, 0).getDate();
                current = new Date(targetFirst.getFullYear(), targetFirst.getMonth(), Math.min(current.getDate(), daysInTarget));
            }

            function shift(delta) {
                if (view === 'month') shiftMonth(delta);
                else if (view === 'week') current = addDays(current, delta * 7);
                else current = addDays(current, delta);
                renderCalendar();
            }

            window.calendarPrev = function () { shift(-1); };
            window.calendarNext = function () { shift(1); };

            window.calendarToday = function () {
                current = new Date();
                current.setHours(0, 0, 0, 0);
                renderCalendar();
            };

            window.calendarSetView = function (v) {
                view = ['month', 'week', 'day'].includes(v) ? v : 'month';
                renderCalendar();
            };

            function initCalendar() {
                renderFilters();
                renderCalendar();
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCalendar);
            } else {
                initCalendar();
            }
        })();
    </script>

    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const dateString = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });

            document.querySelectorAll('#liveClock').forEach(el => el.textContent = timeString);
            document.querySelectorAll('#liveDate').forEach(el => el.textContent = dateString);
        }
        updateClock();
        setInterval(updateClock, 1000);

        let currentFormId = null;

        function openConfirmDialog(eventId, eventName, action) {
            const modal = document.getElementById('confirmModal');
            const eventNameEl = document.getElementById('modalEventName');
            const actionTextEl = document.getElementById('modalActionText');
            const iconEl = document.getElementById('modalIcon');
            const titleEl = document.getElementById('modalTitle');
            const messageEl = document.getElementById('modalMessage');
            const confirmBtn = document.getElementById('modalConfirmBtn');

            eventNameEl.textContent = eventName;
            currentFormId = (action === 'confirmed' ? 'confirm-form-' : 'decline-form-') + eventId;

            const reasonBox = document.getElementById('declineReasonBox');
            const reasonEl = document.getElementById('declineReason');
            document.getElementById('declineReasonError').classList.add('hidden');
            if (action === 'declined') {
                reasonBox.classList.remove('hidden');
                reasonEl.value = '';
                document.getElementById('declineReasonCount').textContent = '0';
                setTimeout(() => reasonEl.focus(), 50);
            } else {
                reasonBox.classList.add('hidden');
            }

            if (action === 'confirmed') {
                iconEl.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center bg-emerald-100 text-emerald-600';
                iconEl.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                titleEl.textContent = 'Confirm Participation?';
                messageEl.textContent = 'You are about to confirm your attendance for this event.';
                actionTextEl.textContent = 'Action: CONFIRM participation';
                actionTextEl.className = 'text-sm mt-2 font-medium text-emerald-600';
                confirmBtn.className = 'flex-1 px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 transition';
            } else {
                iconEl.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center bg-red-100 text-red-600';
                iconEl.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                titleEl.textContent = 'Decline Participation?';
                messageEl.textContent = 'The owner will be notified that you cannot attend. Please tell them why.';
                actionTextEl.textContent = 'Action: DECLINE participation';
                actionTextEl.className = 'text-sm mt-2 font-medium text-red-600';
                confirmBtn.className = 'flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition';
            }

            modal.classList.remove('hidden');
        }

        function closeConfirmDialog() {
            const modal = document.getElementById('confirmModal');
            if (modal) modal.classList.add('hidden');
            currentFormId = null;
        }

        // Modal only exists on the waiter dashboard, so guard against null on admin.
        const modalConfirmBtn = document.getElementById('modalConfirmBtn');
        if (modalConfirmBtn) {
            const reasonEl = document.getElementById('declineReason');
            reasonEl.addEventListener('input', function () {
                document.getElementById('declineReasonCount').textContent = this.value.length;
                if (this.value.trim().length >= 5) document.getElementById('declineReasonError').classList.add('hidden');
            });

            modalConfirmBtn.addEventListener('click', function () {
                if (!currentFormId) return;
                const form = document.getElementById(currentFormId);

                // Declining needs a reason
                if (currentFormId.startsWith('decline-form-')) {
                    const text = reasonEl.value.trim();
                    if (text.length < 5) {
                        document.getElementById('declineReasonError').classList.remove('hidden');
                        reasonEl.focus();
                        return;
                    }
                    form.querySelector('.decline-reason-input').value = text;
                }

                this.disabled = true;
                this.textContent = 'Saving...';
                form.submit();
            });
        }

        const confirmModalEl = document.getElementById('confirmModal');
        if (confirmModalEl) {
            // Close modal on backdrop click
            confirmModalEl.addEventListener('click', function (e) {
                if (e.target === this) {
                    closeConfirmDialog();
                }
            });
        }

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeConfirmDialog();
            }
        });
    </script>
@endsection