document.addEventListener('DOMContentLoaded', function () {
    const FIELD_ID = [
        'search_origin',
        'search_destination',
        'trip_origin',
        'trip_destination'
    ];
    FIELD_ID.forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;
        autocomplete(input);
    });

    function autocomplete(input) {
        let timer = null;
        let curIndex = -1;
        const dropdown = document.createElement('ul');
        dropdown.className = 'list-group position-absolutew-100 shadow-sm';
        dropdown.style.cssText = [
            'z-index: 1050',
            'top: 100%',
            'left: 0',
            'display: none',
            'max-height: 220px',
            'overflow-y: auto',
            'border-radius: 0.5rem',
            'margin-top: 2px',
        ].join(';');

        input.parentElement.style.position = 'relative';
        input.parentElement.appendChild(dropdown);
        input.addEventListener('input', function () {
            clearTimeout(timer);
            curIndex = -1;
            const q = this.value.trim();
            if (q.length < 2) {
                hideDropdown();
                return;
            }
            timer = setTimeout(() => fetchCities(q), 350);
        });

        input.addEventListener('keydown', function (e) {
            const items = dropdown.querySelectorAll('.list-group-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                curIndex = Math.min(curIndex + 1, items.length - 1);
                updateActiveItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                curIndex = Math.max(curIndex - 1, 0);
                updateActiveItem(items);
            } else if (e.key === 'Enter' && curIndex >= 0) {
                e.preventDefault();
                items[curIndex]?.click();
            } else if (e.key === 'Escape') {
                hideDropdown();
            }
        });

        document.addEventListener('click', function (e) {
            if (!input.parentElement.contains(e.target)) {
                hideDropdown();
            }
        });

        async function fetchCities(query) {
            try {
                const res = await fetch(`/api/cities/search?q=${encodeURIComponent(query)}`);
                if (!res.ok) {
                    hideDropdown();
                    return;
                }
                const cities = await res.json();
                renderDropdown(cities);
            } catch (err) {
                hideDropdown();
            }
        }

        function renderDropdown(cities) {
            dropdown.innerHTML = '';
            curIndex = -1;
            if (cities.length === 0) {
                hideDropdown();
                return;
            }
            cities.forEach(city => {
                const li = document.createElement('li');
                li.className = 'list-group-item list-group-item-action py-2 px-3';
                li.style.cursor = 'pointer';
                li.style.fontSize = '0.9rem';
                li.textContent = city.city;

                li.addEventListener('mouseenter', () => {
                    dropdown.querySelectorAll('.list-group-item').forEach(i => i.classList.remove('active'));
                    li.classList.add('active');
                });
                li.addEventListener('mouseleave', () => li.classList.remove('active'));
                li.addEventListener('click', () => {
                    input.value = city.city;
                    input.dispatchEvent(new Event('change'));
                    hideDropdown();
                });
                dropdown.appendChild(li);
            });
            dropdown.style.display = 'block';
        }

        function updateActiveItem(items) {
            items.forEach((item, i) => item.classList.toggle('active', i === curIndex));
        }

        function hideDropdown() {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
            curIndex = -1;
        }
    }
});
