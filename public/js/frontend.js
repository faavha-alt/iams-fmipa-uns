// frontend.js — vanilla JS, tanpa framework/library eksternal

document.addEventListener('submit', function (event) {
    var form = event.target;
    var message = form.getAttribute('data-confirm');

    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});

// Typeahead generik: <div class="typeahead" data-typeahead-url="..."><input><div class="typeahead__results"></div></div>
// Dipakai buat cari Kode BMN tanpa nge-load seluruh tabel ke halaman (lihat BmnCodeController::search).
document.querySelectorAll('.typeahead').forEach(function (wrapper) {
    var input = wrapper.querySelector('input');
    var results = wrapper.querySelector('.typeahead__results');
    var url = wrapper.getAttribute('data-typeahead-url');
    var debounceTimer = null;
    var activeController = null;

    function hideResults() {
        results.hidden = true;
        results.innerHTML = '';
    }

    function renderResults(items) {
        if (!items.length) {
            hideResults();
            return;
        }

        results.innerHTML = '';
        items.forEach(function (item) {
            var option = document.createElement('div');
            option.className = 'typeahead__option';
            option.textContent = item.kode + ' — ' + item.nama;
            option.addEventListener('mousedown', function (event) {
                event.preventDefault();
                input.value = item.kode;
                hideResults();
            });
            results.appendChild(option);
        });
        results.hidden = false;
    }

    input.addEventListener('input', function () {
        var query = input.value.trim();

        clearTimeout(debounceTimer);

        if (query.length < 2) {
            hideResults();
            return;
        }

        debounceTimer = setTimeout(function () {
            if (activeController) {
                activeController.abort();
            }
            activeController = new AbortController();

            fetch(url + '?q=' + encodeURIComponent(query), { signal: activeController.signal })
                .then(function (response) { return response.json(); })
                .then(renderResults)
                .catch(function (error) {
                    if (error.name !== 'AbortError') {
                        hideResults();
                    }
                });
        }, 250);
    });

    input.addEventListener('blur', hideResults);
});

// Select yang bisa disaring: <div class="select-search"><input class="select-search__filter"><select>...</select></div>
// Ketik di input untuk menyaring <option>/<optgroup> di <select> bawahnya. Murni client-side,
// nilai yang dikirim tetap <option value> (id), bukan teks. Dipakai di form finalisasi (cari ruangan).
document.querySelectorAll('.select-search').forEach(function (wrapper) {
    var filter = wrapper.querySelector('.select-search__filter');
    var select = wrapper.querySelector('select');
    if (!filter || !select) {
        return;
    }

    var groups = Array.prototype.slice.call(select.querySelectorAll('optgroup'));
    var options = Array.prototype.slice.call(select.querySelectorAll('option'));

    filter.addEventListener('input', function () {
        var query = filter.value.trim().toLowerCase();

        options.forEach(function (option) {
            if (!option.value) {
                return; // biarkan placeholder "-- Pilih Lokasi --"
            }
            var haystack = (option.textContent + ' ' + (option.parentNode.label || '')).toLowerCase();
            option.hidden = query !== '' && haystack.indexOf(query) === -1;
        });

        groups.forEach(function (group) {
            group.hidden = !Array.prototype.slice.call(group.children).some(function (o) { return !o.hidden; });
        });

        var current = select.options[select.selectedIndex];
        if (current && current.hidden) {
            select.value = '';
        }
    });
});
