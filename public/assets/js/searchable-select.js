// Searchable select — progressively enhances any <select data-searchable-select>
// into a text-filterable combobox. The original <select> stays in the DOM
// (visually hidden but still focusable/validated) so form submission and
// required-field validation keep working exactly as before.
//
// Usage: <select data-searchable-select data-placeholder="Cari...">...</select>

(function () {
  function each(list, fn) {
    Array.prototype.forEach.call(list, fn);
  }

  function optionLabel(option) {
    return (option.textContent || '').trim();
  }

  function enhance(select) {
    if (select.dataset.searchableReady) return;
    select.dataset.searchableReady = 'true';

    var placeholder = select.dataset.placeholder || 'Cari...';

    var wrap = document.createElement('div');
    wrap.className = 'ss-wrap';

    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(select);
    select.classList.add('ss-native-select');

    var input = document.createElement('input');
    input.type = 'text';
    input.className = select.className.replace('ss-native-select', '').trim() || 'input w-full';
    input.classList.add('ss-input');
    input.autocomplete = 'off';
    input.placeholder = placeholder;
    input.setAttribute('aria-expanded', 'false');
    input.setAttribute('role', 'combobox');

    var menu = document.createElement('div');
    menu.className = 'ss-menu';
    menu.hidden = true;

    var list = document.createElement('ul');
    menu.appendChild(list);

    wrap.appendChild(input);
    wrap.appendChild(menu);

    function buildItems() {
      list.innerHTML = '';
      each(select.querySelectorAll('option'), function (option) {
        if (option.value === '') return;

        var li = document.createElement('li');
        li.textContent = optionLabel(option);
        li.dataset.value = option.value;
        if (option.selected) li.setAttribute('aria-selected', 'true');

        li.addEventListener('mousedown', function (event) {
          event.preventDefault();
          selectOption(option.value, optionLabel(option));
        });

        list.appendChild(li);
      });
    }

    function selectOption(value, label) {
      select.value = value;
      select.dispatchEvent(new Event('change', { bubbles: true }));
      input.value = label;
      closeMenu();
    }

    function syncFromSelect() {
      var selected = select.options[select.selectedIndex];
      input.value = selected && selected.value !== '' ? optionLabel(selected) : '';
    }

    function filterMenu(term) {
      var query = term.trim().toLowerCase();
      var visibleCount = 0;

      each(list.querySelectorAll('li'), function (li) {
        var matches = query === '' || li.textContent.toLowerCase().indexOf(query) !== -1;
        li.hidden = !matches;
        if (matches) visibleCount++;
      });

      var empty = list.querySelector('.ss-empty');
      if (visibleCount === 0) {
        if (!empty) {
          empty = document.createElement('li');
          empty.className = 'ss-empty';
          empty.textContent = 'Tidak ditemukan';
          list.appendChild(empty);
        }
      } else if (empty) {
        empty.remove();
      }
    }

    function openMenu() {
      menu.hidden = false;
      input.setAttribute('aria-expanded', 'true');
      filterMenu(input.value === optionLabelOfSelected() ? '' : input.value);
    }

    function optionLabelOfSelected() {
      var selected = select.options[select.selectedIndex];
      return selected && selected.value !== '' ? optionLabel(selected) : '';
    }

    function closeMenu() {
      menu.hidden = true;
      input.setAttribute('aria-expanded', 'false');
      syncFromSelect();
    }

    input.addEventListener('focus', function () {
      buildItems();
      openMenu();
    });

    input.addEventListener('input', function () {
      menu.hidden = false;
      input.setAttribute('aria-expanded', 'true');
      filterMenu(input.value);
    });

    input.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeMenu();
        input.blur();
      } else if (event.key === 'Enter') {
        event.preventDefault();
        var firstVisible = list.querySelector('li:not([hidden]):not(.ss-empty)');
        if (firstVisible) selectOption(firstVisible.dataset.value, firstVisible.textContent);
      }
    });

    document.addEventListener('mousedown', function (event) {
      if (!wrap.contains(event.target)) closeMenu();
    });

    buildItems();
    syncFromSelect();
  }

  function init(root) {
    each((root || document).querySelectorAll('select[data-searchable-select]'), enhance);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      init(document);
    });
  } else {
    init(document);
  }
})();
