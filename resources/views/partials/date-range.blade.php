{{-- One date-range box for the filter bars (replaces the separate "From" and "To" date inputs).
     Usage: @include('partials.date-range', ['label' => 'Event Date'])
     It still submits ?from=YYYY-MM-DD&to=YYYY-MM-DD, so the controllers did not change. --}}
@php
    $rangeLabel = $label ?? 'Date Range';
    $validDay = fn ($v) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v) ? (string) $v : '';
    $rangeFrom = $validDay(request('from'));
    $rangeTo = $validDay(request('to'));
@endphp
<div class="w-full lg:w-60" data-date-range>
    <label for="f-range" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">{{ $rangeLabel }}</label>
    <div class="relative">
        <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
        <input id="f-range" type="text" readonly placeholder="Any date" autocomplete="off" data-range-input
            class="w-full pl-9 pr-8 py-1.5 border border-slate-200 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
        <button type="button" data-range-clear title="Clear dates" aria-label="Clear dates"
            class="{{ $rangeFrom || $rangeTo ? '' : 'hidden' }} absolute right-2 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 flex items-center justify-center">
            <i class="fas fa-times text-[10px]"></i>
        </button>
    </div>
    <input type="hidden" name="from" value="{{ $rangeFrom }}" data-range-from>
    <input type="hidden" name="to" value="{{ $rangeTo }}" data-range-to>
</div>

@once
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <style>
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange,
        .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover { background: #d97706; border-color: #d97706; }
        .flatpickr-day.inRange { background: #fef3c7; border-color: #fef3c7; box-shadow: -5px 0 0 #fef3c7, 5px 0 0 #fef3c7; }
        .flatpickr-day.today { border-color: #d97706; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-date-range]').forEach(function (box) {
                const input = box.querySelector('[data-range-input]');
                const from = box.querySelector('[data-range-from]');
                const to = box.querySelector('[data-range-to]');
                const clear = box.querySelector('[data-range-clear]');
                const pretty = v => new Date(v + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

                // What the box shows: "Nov 1, 2026 to Nov 30, 2026", one day, or an open-ended range from an old link
                function show() {
                    if (from.value && to.value) input.value = from.value === to.value ? pretty(from.value) : pretty(from.value) + ' to ' + pretty(to.value);
                    else if (from.value) input.value = 'From ' + pretty(from.value);
                    else if (to.value) input.value = 'Until ' + pretty(to.value);
                    else input.value = '';
                    clear.classList.toggle('hidden', !from.value && !to.value);
                }

                if (!window.flatpickr) { // the date picker could not be loaded (no internet): fall back to two plain date boxes
                    input.parentElement.classList.add('hidden');
                    from.type = 'date';
                    to.type = 'date';
                    [from, to].forEach(el => el.className = 'w-full mb-1 px-3 py-1.5 border border-slate-200 rounded-lg text-sm');
                    return;
                }

                const iso = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                const picker = flatpickr(input, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    defaultDate: from.value && to.value ? [from.value, to.value] : (from.value || to.value || null),
                    onChange: function (dates) {
                        if (dates.length === 2) { from.value = iso(dates[0]); to.value = iso(dates[1]); }
                    },
                    // Picking only one day and closing = that single day
                    onClose: function (dates) {
                        if (dates.length === 1) { from.value = iso(dates[0]); to.value = iso(dates[0]); }
                        show();
                    }
                });

                clear.addEventListener('click', function () {
                    picker.clear();
                    from.value = '';
                    to.value = '';
                    show();
                });

                show();
            });
        });
    </script>
@endonce
