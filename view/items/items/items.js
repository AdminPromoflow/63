/**
 * ======================================================
 * Class: Items
 * Manages variation menu (top dropdown) and the item editor
 * All comments use British English.
 * ======================================================
 */
class Items {
  constructor() {

    // Wait for the DOM to be ready before wiring anything up
    document.addEventListener('DOMContentLoaded', () => {

      this.setupVariationMenu(); // dropdown: open/close, select, navigate
      this.init();               // form refs, state, events, initial data load
    });
  }

  // ==========================================================
  // == VARIATION MENU (TOP DROPDOWN)
  // ==========================================================
  setupVariationMenu() {
    const menuBtn  = document.getElementById('menu_btn');
    const menuList = document.getElementById('menu_list');

    // Cache elements on the instance for reuse elsewhere
    this.menuBtn = menuBtn;
    this.menuList = menuList;

    if (!(menuBtn && menuList)) return;

    /** Open/close the menu with a click on the button */
    menuBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isHidden = menuList.hidden;
      menuList.hidden = !isHidden;
      menuBtn.setAttribute('aria-expanded', String(!isHidden));
    });

    /** Close the menu when clicking anywhere outside it */
    document.addEventListener('click', (e) => {
      if (!menuBtn.contains(e.target) && !menuList.contains(e.target)) {
        if (!menuList.hidden) {
          menuList.hidden = true;
          menuBtn.setAttribute('aria-expanded', 'false');
        }
      }
    });

    /** Close the menu with the Escape key, then return focus to the button */
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !menuList.hidden) {
        menuList.hidden = true;
        menuBtn.setAttribute('aria-expanded', 'false');
        menuBtn.focus();
      }
    });

    /** Handle a click on any <li> within the menu */
    menuList.addEventListener('click', (e) => {
      const li = e.target.closest('li');
      if (!li || !menuList.contains(li)) return;

      // Clear previous selection, if any
      menuList.querySelectorAll('.is-selected').forEach(el => {
        el.classList.remove('is-selected');
        el.setAttribute('aria-selected', 'false');
      });

      // Mark the newly-selected item
      li.classList.add('is-selected');
      li.setAttribute('aria-selected', 'true');

      // Derive name and SKU from the list item’s text
      const { name, sku } = this.parseNameSkuFromText(li.textContent);

      // Close the menu UI
      menuList.hidden = true;
      menuBtn.setAttribute('aria-expanded', 'false');

      // Navigate to the same page but with the selected variation in the query string
      const params = new URLSearchParams(window.location.search);
      const sku_product = params.get('sku');

      window.location.href =
        `../../view/items/index.php?sku=${encodeURIComponent(sku_product)}&sku_variation=${encodeURIComponent(sku)}`;
    });
  }

  // ==========================================================
  // == INITIALISATION (FORM, STATE, EVENTS, DATA)
  // ==========================================================
  init() {
    // --- References ---
    this.form     = document.getElementById('variationItemsForm');
    this.addBtn   = document.getElementById('add_item');
    this.list     = document.getElementById('items_list');
    this.resetBtn = document.getElementById('reset_form');
    this.saveBtn  = document.getElementById('save_items');
    this.nextBtn  = document.getElementById('next_items');

    // --- State (each item: { id, label, text, highlight, order }) ---
    this.itemsState = [];

    // Highlight the current header step, if available
    if (window.headerAddProduct) {
      headerAddProduct.setCurrentHeader('items');
    }

    // Wire events, then fetch variations to populate the menu
    this.bindEvents();
    this.getItemsDetails();
    this.getVariationDetails();
  }

  // Fetch current product/variation details and menu options from the server,
  // then render the variation menu and select the current one based on the URL.
  getVariationDetails() {
    const params = new URLSearchParams(window.location.search);
    const sku = params.get('sku');
    const sku_variation = params.get('sku_variation');

    const url = "../../controller/products/variations.php";
    const data = {
      action: "get_variation_details",
      sku: sku,
      sku_variation: sku_variation
    };

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
    })
    .then(response => {
      if (response.ok) return response.json();
      throw new Error("Network error.");
    })
    .then(data => {
      if (data?.success) {
        this.renderMenuTop(data.variations);
        // Must run AFTER rendering the <li> items
        this.selectMenuCurrentItemBySku();
      }
    })
    .catch(error => {
      console.error("Error:", error);
    });
  }



  // Fetch current product/variation details and menu options from the server,
  // then render the variation menu and select the current one based on the URL.
  getItemsDetails() {
    const params = new URLSearchParams(window.location.search);
    const sku_variation = params.get('sku_variation');

    const url = "../../controller/products/item.php";
    const data = {
      action: "get_items_details",
      sku_variation: sku_variation
    };

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
    })
    .then(response => {
      if (response.ok) return response.json();
      throw new Error("Network error.");
    })
    .then(data => {
    //  alert(JSON.stringify(data));

      if (data?.success) {
        items.drawItems(data.items);
      }
    })
    .catch(error => {
      console.error("Error:", error);
    });
  }

  // Mark the <li> in the menu that matches ?sku_variation=... in the current URL
  selectMenuCurrentItemBySku() {
    const currentUrl = new URL(window.location.href);
    const skuv = currentUrl.searchParams.get('sku_variation');
    if (!skuv) return false;

    const ul  = this.menuList || document.getElementById('menu_list');
    const btn = this.menuBtn  || document.getElementById('menu_btn');
    if (!ul) return false;

    // Clear any previous selection
    ul.querySelectorAll('.is-selected').forEach(el => el.classList.remove('is-selected'));

    const norm   = s => String(s || '').trim().toUpperCase();
    const wanted = norm(skuv);

    // Try to match either data-sku or the SKU shown in the <small> text
    for (const li of ul.querySelectorAll('li')) {
      const skuData = li.dataset?.sku;
      const skuText = li.querySelector('small')?.textContent?.replace(/^—\s*/, '');
      const candidate = norm(skuData || skuText);

      if (candidate && candidate === wanted) {
        li.classList.add('is-selected');
        li.setAttribute('aria-selected', 'true');
        ul.hidden = true;
        if (btn) btn.setAttribute('aria-expanded', 'false');
        return true;
      }
    }
    return false;
  }

  // Extract { name, sku } from a line of menu text (robust to a few formats)
  parseNameSkuFromText(text) {
    const raw = (text ?? '').toString().trim();
    if (raw === '') return { name: '', sku: '' };

    // Example SKU pattern: ABC-20250101-123456-654321-1A2B3C4D5E
    const skuPattern = /[A-Z]{3,}-\d{8}-\d{6}-\d{6}-[A-F0-9]{10}/i;

    // Case 1: explicit SKU match in the text
    const anyMatch = raw.match(skuPattern);
    if (anyMatch) {
      const sku  = anyMatch[0].trim();
      const name = raw.replace(skuPattern, '').replace(/[—–\-:()\s]+$/,'').trim();
      return { name: name.replace(/[—–\-:]\s*$/,'').trim(), sku };
    }

    // Case 2: split on dash-like separators and assume the last part is the SKU
    const sepPattern = /\s*[—–\-:]\s*/;
    const parts = raw.split(sepPattern).filter(Boolean);
    if (parts.length >= 2) {
      const last = parts[parts.length - 1].trim();
      if (skuPattern.test(last)) {
        parts.pop();
        const sku  = last;
        const name = parts.join(' — ').trim();
        return { name, sku };
      }
    }

    // Case 3: SKU in parentheses at the end
    const mParen = raw.match(/\(([^)]+)\)\s*$/);
    if (mParen && skuPattern.test(mParen[1])) {
      const sku  = mParen[1].trim();
      const name = raw.slice(0, mParen.index).trim();
      return { name, sku };
    }

    // Fallback: no SKU detected, return the whole string as the name
    return { name: raw, sku: '' };
  }

  // Render the top menu list with the available variations
  renderMenuTop(items) {
    const ul = document.getElementById('menu_list');
    if (!ul) return;

    // Accept either a plain array or an object containing a .variations array
    const list = Array.isArray(items) ? items
               : (items && Array.isArray(items.variations) ? items.variations : []);

    ul.innerHTML = '';
    ul.setAttribute('role', 'menu');

    if (list.length === 0) {
      const li = document.createElement('li');
      li.textContent = 'No items to show';
      li.setAttribute('role', 'menuitem');
      li.setAttribute('tabindex', '-1');
      ul.appendChild(li);
      return;
    }

    // Minimal HTML-escape for safety
    const escape = (s) => String(s ?? '').replace(/[&<>"']/g, m =>
      ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])
    );

    const frag = document.createDocumentFragment();

    list.forEach((it) => {
      const name = (it?.name ?? '(unnamed)').trim() || '(unnamed)';
      const sku  = (it?.SKU ?? it?.sku ?? '').trim();

      const li = document.createElement('li');
      li.setAttribute('role', 'menuitem');
      li.setAttribute('tabindex', '-1');
      li.dataset.name = name;
      li.dataset.sku  = sku;

      li.innerHTML = `<strong>${escape(name)}</strong>${sku ? ` <small style="color:var(--muted)">— ${escape(sku)}</small>` : ''}`;
      frag.appendChild(li);
    });

    ul.appendChild(frag);
  }

  // ==========================================================
  // == ITEM LIST (FORM LOGIC)
  // ==========================================================
  makeId() {
    return Math.random().toString(36).slice(2, 10);
  }

  resequence() {
    this.itemsState.forEach((it, idx) => it.order = idx);
  }

  renderList() {
    this.list.innerHTML = '';
    this.itemsState.forEach((it, idx) => {
      const card = document.createElement('div');
      card.className = 'cp-card-item';
      card.innerHTML = `
        <div class="row">
          <span class="small">Item #${idx + 1}</span>
          <div class="cp-actions">
            <button type="button" class="btn btn-danger btn-icon remove" data-id="${it.id}">✕</button>
          </div>
        </div>
        <input type="text" class="label-input" data-id="${it.id}" placeholder="Label (optional), e.g., Includes" value="${it.label}">
        <textarea class="text-input" data-id="${it.id}" placeholder="Write the item text shown to customers…">${it.text}</textarea>
      `;
      this.list.appendChild(card);
    });
  }

  drawItems(items) {
    // Actualiza el estado con las claves del backend
    this.itemsState = (Array.isArray(items) ? items : []).map(it => ({
      id: String(it.item_id || ''),
      label: it.name || '',
      text: it.description || ''
    }));

    // Limpia el contenedor
    this.list.innerHTML = '';

    // Escape mínimo para evitar inyección
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
      '&': '&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));

    // Dibuja
    this.itemsState.forEach((it, i) => {
      const div = document.createElement('div');
      div.className = 'cp-card-item';
      div.innerHTML = `
        <div class="row">
          <span class="small">Item #${i + 1}</span>
          <div class="cp-actions">
            <button type="button" class="btn btn-danger btn-icon remove" data-id="${esc(it.id)}">✕</button>
          </div>
        </div>
        <input type="text" class="label-input" data-id="${esc(it.id)}" value="${esc(it.label)}" placeholder="Label (optional)">
        <textarea class="text-input" data-id="${esc(it.id)}" placeholder="Item text…">${esc(it.text)}</textarea>
      `;
      this.list.appendChild(div);
    });
  }


  addItem(label = '', text = '', highlight = false) {
    this.itemsState.push({
      id: this.makeId(),
      label,
      text,
      highlight,
      order: this.itemsState.length
    });
    this.renderList();
  }

  removeItem(id_item) {
    if (Number.isInteger( parseInt(id_item, 10))) {
      const url = "../../controller/products/item.php";
      const data = {
        action: "delete_item",
        id_item: id_item
      };

      fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
      })
      .then(response => {
        if (response.ok) return response.json();
        throw new Error("Network error.");
      })
      .then(data => {
        alert(JSON.stringify(data));

        if (data?.success) {
          }
      })
      .catch(error => {
        console.error("Error:", error);
      });
    }
    alert("The item has been successfully removed.");
    location.reload();
  }

  toggleHighlight(id) {
    this.itemsState.forEach(it => {
      if (it.id === id) it.highlight = !it.highlight;
    });
    this.renderList();
  }

  moveItem(id, dir) {
    const i = this.itemsState.findIndex(it => it.id === id);
    if (i < 0) return;
    const j = i + (dir === 'up' ? -1 : 1);
    if (j < 0 || j >= this.itemsState.length) return;
    [this.itemsState[i], this.itemsState[j]] = [this.itemsState[j], this.itemsState[i]];
    this.resequence();
    this.renderList();
  }

  resetForm() {
    this.form.reset();
    this.itemsState = [];
    this.list.innerHTML = '';
  }

  // Persist the current list of items to the server
  async saveItems(e) {

    e.preventDefault();

    if (this.itemsState.length === 0) {
      alert('Please add at least one item.');
      return;
    }

    // Basic validation: item text must not be empty
    for (const it of this.itemsState) {
      if (!it.text.trim()) {
        alert('Item text cannot be empty.');
        return;
      }
    }

    const labelInput = document.querySelectorAll(".label-input");
    const textInput = document.querySelectorAll(".text-input");

    // Creamos arrays vacíos
    const labels = [];
    const texts = [];

    // Recorremos todos los elementos
    for (let i = 0; i < labelInput.length; i++) {
      // Guardamos los valores en los arrays
      labels.push(labelInput[i].value);
      texts.push(textInput[i].value);
    }


  //  this.saveBtn.disabled = true;
  //  this.saveBtn.textContent = 'Saving…';


    const params = new URLSearchParams(window.location.search);
    const sku_variation = params.get('sku_variation');


    const url = "../../controller/products/item.php";
    const data = {
      action: "create_items",
      sku_variation: sku_variation,
      labels: labels,
      texts: texts
      };
    // Make a fetch request to the given URL with the specified data.
    fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data)
    })
      .then(response => {
        // Check if the response is okay, if so, return the response text.
        if (response.ok) {
          return response.text();
        }
        // If the response is not okay, throw an error.
        throw new Error("Network error.");
      })
      .then(data => {
        alert(data);
        var data = JSON.parse(data);

       if (data["success"]) {
         alert("The items have been saved successfully.");
        }
      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });











  }

  // Wire all UI events for the item editor
  bindEvents() {
    if (this.addBtn) {
      this.addBtn.addEventListener('click', () => this.addItem());
    }

    if (this.list) {
      // Delegate clicks for remove/highlight/move buttons
      this.list.addEventListener('click', (e) => {
        const btnUp   = e.target.closest('.move-up');
        const btnDown = e.target.closest('.move-down');
        const btnHi   = e.target.closest('.highlight');
        const btnRem  = e.target.closest('.remove');

        if (btnUp)   this.moveItem(btnUp.dataset.id, 'up');
        if (btnDown) this.moveItem(btnDown.dataset.id, 'down');
        if (btnHi)   this.toggleHighlight(btnHi.dataset.id);
        if (btnRem)  this.removeItem(btnRem.dataset.id);
      });

      // Keep state in sync with inputs
      this.list.addEventListener('input', (e) => {
        const labelEl = e.target.closest('.label-input');
        const textEl  = e.target.closest('.text-input');
        if (labelEl) {
          const it = this.itemsState.find(x => x.id === labelEl.dataset.id);
          if (it) it.label = labelEl.value.trim();
        } else if (textEl) {
          const it = this.itemsState.find(x => x.id === textEl.dataset.id);
          if (it) it.text = textEl.value;
        }
      });
    }

    if (this.resetBtn) {
      this.resetBtn.addEventListener('click', () => this.resetForm());
    }

    if (this.form) {
      this.form.addEventListener('submit', (e) => this.saveItems(e));
    }

    // Optional: proceed to the next step in the header wizard
    if (this.nextBtn && window.headerAddProduct) {
      this.nextBtn.addEventListener('click', () => {
        //alert("This page is being built — stay tuned!");
        headerAddProduct.goNext('../../view/prices/index.php');
      });
    }
  }
}

// Single global instance (used by menu event handlers and elsewhere)
const items = new Items();
