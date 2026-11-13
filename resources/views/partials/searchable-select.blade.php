{{-- Searchable dropdowns: include once on a page, then mark any long <select> with
     data-searchable="placeholder text" (and optionally data-required-message="...").
     The <select> must sit directly inside a `relative` wrapper. It stays in the form and still does the submitting. --}}
<script>
    // Turns a dropdown into a searchable box: type to filter, click or press Enter to pick.
    (function () {
        const enhanced = [];

        function enhance(select) {
            const options = Array.from(select.options)
                .filter(o => o.value !== '')
                .map(o => ({ value: o.value, label: o.textContent.replace(/\s+/g, ' ').trim() }));

            const wrap = select.parentElement;
            const inputClass = select.className.replace('appearance-none', '').replace('cursor-pointer', '');
            const wasRequired = select.required;

            select.classList.add('hidden');
            select.required = false; // a hidden required field cannot be focused, so it is checked on submit instead
            select.tabIndex = -1;

            const input = document.createElement('input');
            input.type = 'text';
            input.autocomplete = 'off';
            input.placeholder = select.dataset.searchable || 'Search...';
            input.className = inputClass + ' pr-9 truncate'; // room for the arrow icon; long names end in "..." instead of running under it
            input.setAttribute('role', 'combobox');
            input.setAttribute('aria-expanded', 'false');
            input.setAttribute('aria-autocomplete', 'list');
            if (select.id) { // keep <label for="..."> pointing at the visible box
                input.id = select.id;
                select.removeAttribute('id');
            }

            const list = document.createElement('ul');
            list.setAttribute('role', 'listbox');
            list.className = 'hidden absolute left-0 right-0 top-full z-40 mt-1 max-h-60 overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg';
            // keep focus in the input when the list (or its scrollbar) is clicked
            list.addEventListener('mousedown', e => e.preventDefault());

            const error = document.createElement('p');
            error.className = 'hidden text-xs text-rose-600 font-medium';
            error.textContent = select.dataset.requiredMessage || 'Please choose one.';

            select.after(input);
            input.after(list);
            wrap.after(error);

            let shown = options;
            let active = -1;

            function current() { return options.find(o => o.value === select.value) || null; }
            function isOpen() { return !list.classList.contains('hidden'); }

            function setActive(i) {
                active = i;
                Array.from(list.children).forEach((li, idx) => li.classList.toggle('bg-amber-50', idx === i));
                const li = list.children[i];
                if (li && li.scrollIntoView) li.scrollIntoView({ block: 'nearest' });
            }

            function render() {
                list.replaceChildren();
                if (!shown.length) {
                    const li = document.createElement('li');
                    li.className = 'px-4 py-2 text-sm text-slate-400';
                    li.textContent = 'No match found';
                    list.appendChild(li);
                    return;
                }
                const picked = current();
                shown.forEach((opt, idx) => {
                    const li = document.createElement('li');
                    li.setAttribute('role', 'option');
                    li.className = 'cursor-pointer break-words px-4 py-2 text-sm leading-snug '
                        + (picked && picked.value === opt.value ? 'font-semibold text-amber-700' : 'text-slate-700');
                    li.textContent = opt.label;
                    li.addEventListener('mousedown', e => { e.preventDefault(); choose(opt); });
                    li.addEventListener('mousemove', () => { if (active !== idx) setActive(idx); });
                    list.appendChild(li);
                });
            }

            function open() {
                list.classList.remove('hidden');
                input.setAttribute('aria-expanded', 'true');
                render();
                const picked = current();
                const idx = picked ? shown.findIndex(o => o.value === picked.value) : 0;
                setActive(shown.length ? Math.max(idx, 0) : -1);
            }

            function close() {
                list.classList.add('hidden');
                input.setAttribute('aria-expanded', 'false');
            }

            function setValue(value) {
                if (select.value === value) return;
                select.value = value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function choose(opt) {
                input.value = opt.label;
                error.classList.add('hidden');
                close();
                setValue(opt.value);
            }

            input.addEventListener('focus', () => { input.select(); shown = options; open(); });
            input.addEventListener('click', () => { if (!isOpen()) { shown = options; open(); } });

            input.addEventListener('input', () => {
                const term = input.value.trim().toLowerCase();
                shown = term ? options.filter(o => o.label.toLowerCase().includes(term)) : options;
                open();
            });

            input.addEventListener('keydown', e => {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!isOpen()) { shown = options; open(); }
                    else if (shown.length) setActive(Math.min(active + 1, shown.length - 1));
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (isOpen() && shown.length) setActive(Math.max(active - 1, 0));
                } else if (e.key === 'Enter') {
                    if (isOpen()) { // Enter picks the highlighted option instead of submitting the form
                        e.preventDefault();
                        if (active >= 0 && shown[active]) choose(shown[active]);
                    }
                } else if (e.key === 'Escape') {
                    if (isOpen()) {
                        e.preventDefault();
                        close();
                        input.value = current() ? current().label : '';
                    }
                }
            });

            input.addEventListener('blur', () => {
                close();
                const hasEmptyOption = Array.from(select.options).some(o => o.value === '');
                if (input.value.trim() === '' && hasEmptyOption) setValue('');   // emptied the box = cleared the choice
                else input.value = current() ? current().label : '';             // half-typed text snaps back to the real choice
            });

            const picked = current();
            input.value = picked ? picked.label : '';

            enhanced.push({ select, input, required: wasRequired, showError: () => error.classList.remove('hidden') });
        }

        document.querySelectorAll('select[data-searchable]').forEach(enhance);

        // Required searchable dropdowns are checked when their form is submitted
        const forms = new Set(enhanced.filter(c => c.required && c.select.form).map(c => c.select.form));
        forms.forEach(form => {
            form.addEventListener('submit', e => {
                const missing = enhanced.filter(c => c.required && c.select.form === form && !c.select.value);
                if (!missing.length) return;
                e.preventDefault();
                missing.forEach(c => c.showError());
                missing[0].input.focus();
            });
        });
    })();
</script>
