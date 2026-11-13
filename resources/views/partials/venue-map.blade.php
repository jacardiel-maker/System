{{-- A venue address that opens a map pop-up when clicked.
     Usage: @include('partials.venue-map', ['address' => $event->venue_address])
     The map is Google Maps searched by the address text (no API key needed, but it needs internet). --}}
@php
    $venueText = trim((string) ($address ?? ''));
    // Addresses are typed without the country; adding it keeps the search inside the Philippines
    $venueQuery = $venueText !== '' && stripos($venueText, 'philippines') === false ? $venueText . ', Philippines' : $venueText;
@endphp

@if($venueText === '')
    <p class="text-sm text-slate-400">—</p>
@else
    <button type="button" data-venue-map="{{ $venueQuery }}" data-venue-label="{{ $venueText }}" title="Show on map"
        class="group/venue text-left text-sm font-semibold text-slate-800 leading-snug break-words hover:text-amber-700 transition">
        <span class="underline decoration-dotted decoration-slate-300 underline-offset-4 group-hover/venue:decoration-amber-500">{{ $venueText }}</span>
        <span class="ml-1 inline-flex items-center gap-1 text-xs font-semibold text-amber-700 whitespace-nowrap"><i class="fas fa-map-marker-alt text-[10px]"></i> View map</span>
    </button>
@endif

@once
    <div id="venueMapModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 no-print"
        role="dialog" aria-modal="true" aria-labelledby="venueMapTitle">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col">
            <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Venue</p>
                    <h3 id="venueMapTitle" class="text-sm font-bold text-slate-900 break-words"></h3>
                </div>
                <button type="button" id="venueMapClose" aria-label="Close map"
                    class="shrink-0 w-8 h-8 rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="relative bg-slate-100 h-[60vh] min-h-[320px]">
                <div class="absolute inset-0 flex items-center justify-center text-sm text-slate-400">
                    <span><i class="fas fa-map-marker-alt mr-1"></i> Loading map... (needs an internet connection)</span>
                </div>
                <iframe id="venueMapFrame" title="Venue map" class="relative w-full h-full border-0" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>

            <div class="px-5 py-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                <p class="text-[11px] text-slate-400">The pin is found from the address text, so it is only as exact as the address typed for the event.</p>
                <a id="venueMapLink" href="#" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700 transition">
                    <i class="fas fa-directions text-[10px]"></i> Open in Google Maps
                </a>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('venueMapModal');
            const frame = document.getElementById('venueMapFrame');
            const title = document.getElementById('venueMapTitle');
            const link = document.getElementById('venueMapLink');
            let opener = null;

            function open(button) {
                const query = encodeURIComponent(button.dataset.venueMap);
                opener = button;
                title.textContent = button.dataset.venueLabel || button.dataset.venueMap;
                frame.src = 'https://maps.google.com/maps?q=' + query + '&z=15&output=embed';
                link.href = 'https://www.google.com/maps/search/?api=1&query=' + query;
                modal.classList.remove('hidden');
                document.getElementById('venueMapClose').focus();
            }

            function close() {
                if (modal.classList.contains('hidden')) return;
                modal.classList.add('hidden');
                frame.src = 'about:blank'; // stop loading the map once it is closed
                if (opener) opener.focus();
            }

            document.addEventListener('click', function (e) {
                const button = e.target.closest('[data-venue-map]');
                if (button) { e.preventDefault(); open(button); }
            });
            document.getElementById('venueMapClose').addEventListener('click', close);
            modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
        })();
    </script>
@endonce
