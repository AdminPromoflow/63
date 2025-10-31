(() => {
  const form         = document.getElementById('variationItemsForm');
  const parentSelect = document.getElementById('parent_variations');
  const parentChips  = document.getElementById('parent_chips');
  const variationSel = document.getElementById('variation_child');
  const addBtn       = document.getElementById('add_item');
  const list         = document.getElementById('items_list');
  const resetBtn     = document.getElementById('reset_form');
  const saveBtn      = document.getElementById('save_items');

  // ====== Estado ======
  const itemsState = []; // { id, label, text, highlight, order }
  const makeId = () => Math.random().toString(36).slice(2,10);

  // ====== Helpers ======
  const refreshChips = () => {
    parentChips.innerHTML = '';
    [...parentSelect.selectedOptions].forEach(opt => {
      const span = document.createElement('span');
      span.className = 'cp-chip';
      span.textContent = opt.textContent;
      parentChips.appendChild(span);
    });
  };

  const addItem = (label = '', text = '', highlight = false) => {
    itemsState.push({ id: makeId(), label, text, highlight, order: itemsState.length });
    renderList();
  };

  const removeItem = (id) => {
    const i = itemsState.findIndex(it => it.id === id);
    if (i >= 0) itemsState.splice(i, 1);
    resequence();
    renderList();
  };

  const toggleHighlight = (id) => {
    itemsState.forEach(it => it.highlight = (it.id === id) ? !it.highlight : it.highlight);
    renderList();
  };

  const moveItem = (id, dir) => {
    const i = itemsState.findIndex(it => it.id === id);
    if (i < 0) return;
    const j = i + (dir === 'up' ? -1 : 1);
    if (j < 0 || j >= itemsState.length) return;
    [itemsState[i], itemsState[j]] = [itemsState[j], itemsState[i]];
    resequence();
    renderList();
  };

  const resequence = () => itemsState.forEach((it, idx) => it.order = idx);

  const renderList = () => {
    list.innerHTML = '';
    itemsState.forEach((it, idx) => {
      const card = document.createElement('div');
      card.className = 'cp-card-item';
      card.innerHTML = `
        <div class="row">
          <span class="small">Item #${idx + 1}</span>
          <div class="cp-actions">
            <button type="button" class="btn btn-ghost btn-icon move-up" data-id="${it.id}">↑</button>
            <button type="button" class="btn btn-ghost btn-icon move-down" data-id="${it.id}">↓</button>
            <button type="button" class="btn btn-ghost btn-icon highlight" data-id="${it.id}" aria-pressed="${it.highlight}">
              ${it.highlight ? '★ Highlight' : '☆ Highlight'}
            </button>
            <button type="button" class="btn btn-danger btn-icon remove" data-id="${it.id}">✕</button>
          </div>
        </div>
        <input type="text" class="label-input" data-id="${it.id}" placeholder="Label (optional), e.g., Includes" value="${it.label}">
        <textarea class="text-input" data-id="${it.id}" placeholder="Write the item text shown to customers…">${it.text}</textarea>
      `;
      list.appendChild(card);
    });
  };

  const getSelectedParents = () =>
    [...parentSelect.selectedOptions].map(o => o.value);

  // ====== Eventos ======
  parentSelect.addEventListener('change', refreshChips);
  refreshChips();

  addBtn.addEventListener('click', () => addItem());

  list.addEventListener('click', (e) => {
    const btnUp   = e.target.closest('.move-up');
    const btnDown = e.target.closest('.move-down');
    const btnHi   = e.target.closest('.highlight');
    const btnRem  = e.target.closest('.remove');

    if (btnUp)   moveItem(btnUp.dataset.id, 'up');
    if (btnDown) moveItem(btnDown.dataset.id, 'down');
    if (btnHi)   toggleHighlight(btnHi.dataset.id);
    if (btnRem)  removeItem(btnRem.dataset.id);
  });

  list.addEventListener('input', (e) => {
    const labelEl = e.target.closest('.label-input');
    const textEl  = e.target.closest('.text-input');
    if (labelEl) {
      const it = itemsState.find(x => x.id === labelEl.dataset.id);
      if (it) it.label = labelEl.value.trim();
    } else if (textEl) {
      const it = itemsState.find(x => x.id === textEl.dataset.id);
      if (it) it.text = textEl.value;
    }
  });

  resetBtn.addEventListener('click', () => {
    form.reset();
    itemsState.splice(0, itemsState.length);
    list.innerHTML = '';
    refreshChips();
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (itemsState.length === 0) { alert('Please add at least one item.'); return; }
    // Validación simple: evita items vacíos
    for (const it of itemsState) {
      if (!it.text.trim()) { alert('Item text cannot be empty.'); return; }
    }

    const payload = {
      action: 'save_variation_items',
      parent_ids: getSelectedParents(),
      variation_id: variationSel.value || '',
      items: itemsState.map(({label, text, highlight, order}) => ({ label, text, highlight, order }))
    };

    saveBtn.disabled = true; saveBtn.textContent = 'Saving…';
    try {
      const res = await fetch('../../controller/variations/items_save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json().catch(() => ({}));
      alert(data?.message || 'Items saved successfully.');
    } catch (err) {
      console.error(err);
      alert('There was a problem saving the items.');
    } finally {
      saveBtn.disabled = false; saveBtn.textContent = 'Save items';
    }
  });

  // Inicial: un item de ejemplo opcional
  // addItem('Includes', 'Metal hook and safety breakaway', true);
})();


class Items {
  constructor() {
    document.addEventListener('DOMContentLoaded', () => {
     headerAddProduct.setCurrentHeader('items');
   });

   const next_items = document.getElementById("next_items");

   next_items.addEventListener("click", function(){
     headerAddProduct.goNext('../../view/prices/index.php');
   })
  }
}

const items = new Items();
