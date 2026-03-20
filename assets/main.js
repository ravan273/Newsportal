function debounce(fn, waitMs) {
  let t = null;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), waitMs);
  };
}

function ensureSuggestBox(input) {
  const wrapper = input.parentElement;
  if (!wrapper) return null;
  wrapper.style.position = 'relative';

  let box = wrapper.querySelector('.asura-suggest');
  if (box) return box;

  box = document.createElement('div');
  box.className = 'asura-suggest list-group position-absolute start-0 end-0 shadow-sm';
  box.style.top = '100%';
  box.style.zIndex = '2000';
  box.style.display = 'none';
  wrapper.appendChild(box);
  return box;
}

async function fetchSuggestions(q) {
  const url = `${window.location.origin}${window.location.pathname.replace(/\/[^/]*$/, '')}/api/suggest.php?q=${encodeURIComponent(q)}`;
  const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
  if (!res.ok) return [];
  const data = await res.json();
  return Array.isArray(data.items) ? data.items : [];
}

document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('globalSearch');
  if (!input) return;

  const box = ensureSuggestBox(input);
  if (!box) return;

  const hide = () => { box.style.display = 'none'; box.innerHTML = ''; };

  const render = (items) => {
    if (!items.length) return hide();
    box.innerHTML = '';
    items.forEach((it) => {
      const a = document.createElement('a');
      a.className = 'list-group-item list-group-item-action';
      a.href = `news.php?slug=${encodeURIComponent(it.slug)}`;
      a.textContent = it.title;
      box.appendChild(a);
    });
    box.style.display = 'block';
  };

  const onInput = debounce(async () => {
    const q = (input.value || '').trim();
    if (q.length < 2) return hide();
    try {
      const items = await fetchSuggestions(q);
      render(items);
    } catch {
      hide();
    }
  }, 200);

  input.addEventListener('input', onInput);
  input.addEventListener('blur', () => setTimeout(hide, 120));
});

