{{--
    Export menu for report pages: Print · PDF · Word · Excel.

    Usage (inside a report view):
        @include('reports.partials.export', [
            'title'       => 'EVENT REPORT',
            'filename'    => 'event-report',
            'orientation' => 'portrait',            // or 'landscape'
            'meta'        => ['Period' => '...', 'Status' => '...'],
            'summary'     => ['Total Events' => 10, 'Completed' => 4],
        ])

    It reads the table that has the class "report-table" on the page.
    Columns / elements marked "no-print" are left out of every export,
    and when a cell has a "print-only" part (e.g. full address) that full text is used.
--}}
@php
    $exportConfig = [
        'business'    => "Steph's Catering by Chef Nono",
        'title'       => $title ?? 'REPORT',
        'filename'    => ($filename ?? 'report') . '-' . now()->format('Y-m-d'),
        'orientation' => $orientation ?? 'portrait',
        'meta'        => array_merge($meta ?? [], [
            'Generated'   => now()->format('F d, Y h:i A'),
            'Prepared by' => auth()->user()->name,
        ]),
        'summary'     => $summary ?? [],
        'preparedBy'  => auth()->user()->name,
        'preparedRole'=> ucfirst((string) auth()->user()->role),
    ];
@endphp

<div class="relative no-print" id="exportMenuWrap">
    <button type="button" id="exportMenuBtn" aria-haspopup="true" aria-expanded="false"
        class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 transition">
        <i class="fas fa-file-export text-xs"></i> Export
        <i class="fas fa-chevron-down text-[10px]"></i>
    </button>

    <div id="exportMenu" role="menu"
        class="hidden absolute right-0 mt-1 w-56 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-30">
        <button type="button" data-export="print" role="menuitem"
            class="w-full flex items-center gap-3 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 text-left">
            <span class="w-7 h-7 rounded-md bg-slate-100 text-slate-600 flex items-center justify-center"><i class="fas fa-print text-xs"></i></span>
            <span><span class="block font-semibold">Print</span><span class="block text-[11px] text-slate-400">Send to printer</span></span>
        </button>
        <button type="button" data-export="pdf" role="menuitem"
            class="w-full flex items-center gap-3 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 text-left">
            <span class="w-7 h-7 rounded-md bg-red-50 text-red-600 flex items-center justify-center"><i class="fas fa-file-pdf text-xs"></i></span>
            <span><span class="block font-semibold">PDF</span><span class="block text-[11px] text-slate-400">Download .pdf</span></span>
        </button>
        <button type="button" data-export="word" role="menuitem"
            class="w-full flex items-center gap-3 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 text-left">
            <span class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-file-word text-xs"></i></span>
            <span><span class="block font-semibold">Word</span><span class="block text-[11px] text-slate-400">Download .doc (editable)</span></span>
        </button>
        <button type="button" data-export="excel" role="menuitem"
            class="w-full flex items-center gap-3 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 text-left">
            <span class="w-7 h-7 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fas fa-file-excel text-xs"></i></span>
            <span><span class="block font-semibold">Excel</span><span class="block text-[11px] text-slate-400">Download .csv</span></span>
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
<script>
    (function () {
        const CFG = @json($exportConfig);

        // ---------- dropdown ----------
        const btn = document.getElementById('exportMenuBtn');
        const menu = document.getElementById('exportMenu');
        const close = () => { menu.classList.add('hidden'); btn.setAttribute('aria-expanded', 'false'); };
        btn.addEventListener('click', e => {
            e.stopPropagation();
            const open = menu.classList.toggle('hidden') === false;
            btn.setAttribute('aria-expanded', String(open));
        });
        document.addEventListener('click', e => { if (!menu.contains(e.target)) close(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });

        // ---------- read the report table ----------
        function cellText(cell) {
            const clone = cell.cloneNode(true);
            clone.querySelectorAll('.no-print, script, style, i.fas, i.fa').forEach(el => el.remove());
            const full = clone.querySelector('.print-only');
            const source = full || clone;
            // keep line breaks between stacked lines (e.g. "Items Involved")
            const html = source.innerHTML
                .replace(/<br\s*\/?>/gi, '\n')
                .replace(/<\/(div|p|li)>/gi, '\n');
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            return (tmp.textContent || '')
                .split('\n')
                .map(line => line.replace(/\s+/g, ' ').trim())
                .filter(Boolean)
                .join('\n');
        }

        function readTable() {
            const table = document.querySelector('.report-table');
            if (!table) return { head: [], body: [], foot: [] };

            const keep = el => !el.classList.contains('no-print');

            const head = [];
            table.querySelectorAll('thead tr').forEach(tr => {
                head.push(Array.from(tr.children).filter(keep).map(cellText));
            });

            const body = [];
            table.querySelectorAll('tbody tr').forEach(tr => {
                const cells = Array.from(tr.children).filter(keep);
                // skip the "No records found" row
                if (cells.length === 1 && Number(cells[0].getAttribute('colspan') || 1) > 1) return;
                body.push(cells.map(cellText));
            });

            const foot = [];
            table.querySelectorAll('tfoot tr').forEach(tr => {
                const row = [];
                Array.from(tr.children).filter(keep).forEach(td => {
                    const span = Number(td.getAttribute('colspan') || 1);
                    row.push({ content: cellText(td), colSpan: span });
                });
                foot.push(row);
            });

            return { head, body, foot };
        }

        // footer row as plain cells (expand colspans) for CSV / Word
        const flatFoot = foot => foot.map(row => {
            const out = [];
            row.forEach(c => { out.push(c.content); for (let i = 1; i < c.colSpan; i++) out.push(''); });
            return out;
        });

        function download(blob, name) {
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = name;
            document.body.appendChild(a);
            a.click();
            setTimeout(() => { URL.revokeObjectURL(a.href); a.remove(); }, 1000);
        }

        const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

        // ---------- PDF ----------
        function exportPdf() {
            if (!window.jspdf || !window.jspdf.jsPDF) {
                alert('The PDF tool could not load (no internet?). Please use Print and choose "Save as PDF".');
                return;
            }
            const { head, body, foot } = readTable();
            const doc = new window.jspdf.jsPDF({ orientation: CFG.orientation, unit: 'mm', format: 'a4' });
            const pageW = doc.internal.pageSize.getWidth();
            const margin = 12;
            let y = 15;

            doc.setFont('helvetica', 'bold').setFontSize(15).text(CFG.business, pageW / 2, y, { align: 'center' });
            y += 6;
            doc.setFontSize(11).setTextColor(70).text(CFG.title, pageW / 2, y, { align: 'center' });
            doc.setTextColor(0);
            y += 7;

            // meta (two columns)
            doc.setFont('helvetica', 'normal').setFontSize(9);
            const metaEntries = Object.entries(CFG.meta);
            metaEntries.forEach(([k, v], i) => {
                const x = i % 2 === 0 ? margin : pageW / 2 + 4;
                if (i % 2 === 0 && i > 0) y += 5;
                doc.setFont('helvetica', 'bold').text(k + ':', x, y);
                doc.setFont('helvetica', 'normal').text(String(v), x + doc.getTextWidth(k + ': ') + 1, y);
            });
            y += 4;

            // summary boxes
            const sum = Object.entries(CFG.summary);
            if (sum.length) {
                doc.autoTable({
                    startY: y,
                    body: [sum.map(([k, v]) => k + ': ' + v)],
                    theme: 'grid',
                    styles: { fontSize: 8.5, cellPadding: 2, textColor: 20 },
                    margin: { left: margin, right: margin },
                });
                y = doc.lastAutoTable.finalY + 4;
            }

            // main table
            doc.autoTable({
                startY: y,
                head: head,
                body: body.length ? body : [[{ content: 'No records found.', colSpan: (head[0] || ['']).length, styles: { halign: 'center' } }]],
                foot: foot.length ? foot : undefined,
                showFoot: 'lastPage',
                theme: 'grid',
                headStyles: { fillColor: [241, 245, 249], textColor: 20, fontStyle: 'bold', fontSize: 8 },
                footStyles: { fillColor: [241, 245, 249], textColor: 20, fontStyle: 'bold', fontSize: 8.5 },
                styles: { fontSize: 8.5, cellPadding: 2, textColor: 20, valign: 'top', overflow: 'linebreak' },
                margin: { left: margin, right: margin },
            });
            y = doc.lastAutoTable.finalY + 16;

            // signatures
            const pageH = doc.internal.pageSize.getHeight();
            if (y + 25 > pageH) { doc.addPage(); y = 20; }
            doc.setFontSize(9).setFont('helvetica', 'normal');
            doc.text('Prepared by:', margin, y);
            doc.text('Noted by:', pageW / 2 + 4, y);
            y += 12;
            doc.line(margin, y, margin + 60, y);
            doc.line(pageW / 2 + 4, y, pageW / 2 + 64, y);
            y += 4;
            doc.setFont('helvetica', 'bold').text(CFG.preparedBy, margin, y);
            doc.setFont('helvetica', 'normal').text('Owner / Manager', pageW / 2 + 4, y);
            y += 4;
            doc.setTextColor(90).text(CFG.preparedRole, margin, y);

            // page numbers
            const pages = doc.getNumberOfPages();
            for (let i = 1; i <= pages; i++) {
                doc.setPage(i);
                doc.setFontSize(8).setTextColor(120);
                doc.text(CFG.business + ' — ' + CFG.title, margin, pageH - 6);
                doc.text('Page ' + i + ' of ' + pages, pageW - margin, pageH - 6, { align: 'right' });
            }

            doc.save(CFG.filename + '.pdf');
        }

        // ---------- Word (.doc that Word opens and edits) ----------
        function exportWord() {
            const { head, body, foot } = readTable();
            const row = (cells, tag) => '<tr>' + cells.map(c => `<${tag}>${esc(c).replace(/\n/g, '<br>')}</${tag}>`).join('') + '</tr>';
            const meta = Object.entries(CFG.meta).map(([k, v]) => `<b>${esc(k)}:</b> ${esc(v)}`).join('<br>');
            const summary = Object.entries(CFG.summary).map(([k, v]) => `<td><b>${esc(k)}:</b> ${esc(v)}</td>`).join('');
            const landscape = CFG.orientation === 'landscape';

            const html = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head><meta charset="utf-8"><title>${esc(CFG.title)}</title>
<style>
  @page Section1 { size: ${landscape ? '29.7cm 21cm; mso-page-orientation: landscape' : '21cm 29.7cm'}; margin: 1.5cm; }
  div.Section1 { page: Section1; }
  body { font-family: Calibri, Arial, sans-serif; font-size: 10pt; }
  h1 { font-size: 16pt; text-align: center; margin: 0; }
  h2 { font-size: 11pt; text-align: center; margin: 2pt 0 10pt; color: #444; }
  table { border-collapse: collapse; width: 100%; }
  table.data th, table.data td { border: 1px solid #94a3b8; padding: 3pt 5pt; vertical-align: top; }
  table.data th { background: #f1f5f9; }
  table.sum td { border: 1px solid #94a3b8; padding: 3pt 5pt; }
</style></head>
<body><div class="Section1">
  <h1>${esc(CFG.business)}</h1>
  <h2>${esc(CFG.title)}</h2>
  <p>${meta}</p>
  ${summary ? `<table class="sum"><tr>${summary}</tr></table><br>` : ''}
  <table class="data">
    <thead>${head.map(r => row(r, 'th')).join('')}</thead>
    <tbody>${body.length ? body.map(r => row(r, 'td')).join('') : '<tr><td colspan="' + ((head[0] || []).length || 1) + '">No records found.</td></tr>'}</tbody>
    ${foot.length ? '<tfoot>' + flatFoot(foot).map(r => row(r, 'th')).join('') + '</tfoot>' : ''}
  </table>
  <br><br>
  <table><tr>
    <td style="width:50%">Prepared by:<br><br><br>______________________<br><b>${esc(CFG.preparedBy)}</b><br>${esc(CFG.preparedRole)}</td>
    <td style="width:50%">Noted by:<br><br><br>______________________<br><br>Owner / Manager</td>
  </tr></table>
</div></body></html>`;

            download(new Blob(['﻿', html], { type: 'application/msword' }), CFG.filename + '.doc');
        }

        // ---------- Excel (.csv) ----------
        function exportExcel() {
            const { head, body, foot } = readTable();
            const csvCell = v => {
                const s = String(v ?? '').replace(/\n/g, '; ');
                return /[",;\n]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s;
            };
            const lines = [];
            lines.push([CFG.business]);
            lines.push([CFG.title]);
            Object.entries(CFG.meta).forEach(([k, v]) => lines.push([k, v]));
            Object.entries(CFG.summary).forEach(([k, v]) => lines.push([k, v]));
            lines.push([]);
            head.forEach(r => lines.push(r));
            body.forEach(r => lines.push(r));
            flatFoot(foot).forEach(r => lines.push(r));

            const csv = lines.map(r => r.map(csvCell).join(',')).join('\r\n');
            download(new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' }), CFG.filename + '.csv');
        }

        menu.querySelectorAll('[data-export]').forEach(item => {
            item.addEventListener('click', () => {
                close();
                const type = item.dataset.export;
                if (type === 'print') window.print();
                if (type === 'pdf') exportPdf();
                if (type === 'word') exportWord();
                if (type === 'excel') exportExcel();
            });
        });
    })();
</script>