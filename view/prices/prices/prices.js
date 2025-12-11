/**
 * ======================================================
 * Class: Prices
 * - Filas de ancho completo
 * - Campos por fila: min_qty, max_qty, price
 * - IDs: si vienen de DB => ID real; si no => random
 * Endpoints:
 *   - ../../controller/products/variations.php  (get_variation_details)
 *   - ../../controller/products/price.php       (get_prices_details, create_prices, delete_price)
 * ======================================================
 */
class Prices {
  constructor() {
    document.addEventListener('DOMContentLoaded', () => {
      this.setupVariationMenu();
      this.init();
    });
  }

  // ========= Menú de variaciones =========
  setupVariationMenu() {
    const menuBtn  = document.getElementById('menu_btn');
    const menuList = document.getElementById('menu_list');
    this.menuBtn = menuBtn;
    this.menuList = menuList;
    if (!(menuBtn && menuList)) return;

    menuBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isHidden = menuList.hidden;
      menuList.hidden = !isHidden;
      menuBtn.setAttribute('aria-expanded', String(!isHidden));
    });

    document.addEventListener('click', (e) => {
      if (!menuBtn.contains(e.target) && !menuList.contains(e.target)) {
        if (!menuList.hidden) {
          menuList.hidden = true;
          menuBtn.setAttribute('aria-expanded', 'false');
        }
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !menuList.hidden) {
        menuList.hidden = true;
        menuBtn.setAttribute('aria-expanded', 'false');
        menuBtn.focus();
      }
    });

    menuList.addEventListener('click', (e) => {
      const li = e.target.closest('li');
      if (!li || !menuList.contains(li)) return;

      menuList.querySelectorAll('.is-selected').forEach(el => {
        el.classList.remove('is-selected');
        el.setAttribute('aria-selected', 'false');
      });
      li.classList.add('is-selected');
      li.setAttribute('aria-selected', 'true');

      const { sku } = this.parseNameSkuFromText(li.textContent);
      menuList.hidden = true;
      menuBtn.setAttribute('aria-expanded', 'false');

      const params = new URLSearchParams(window.location.search);
      const sku_product = params.get('sku');
      window.location.href =
        `../../view/prices/index.php?sku=${encodeURIComponent(sku_product)}&sku_variation=${encodeURIComponent(sku)}`;
    });
  }

  // ========= Init =========
  init() {
    this.form    = document.getElementById('variationPricesForm');
    this.addBtn  = document.getElementById('add_price');
    this.list    = document.getElementById('prices_list');
    this.saveBtn = document.getElementById('save_prices');
    this.nextBtn = document.getElementById('next_prices');

    // Estado: cada fila => { id, min_qty, max_qty, price, order }
    this.rows = [];

    if (window.headerAddProduct) {
      headerAddProduct.setCurrentHeader('prices');
    }

    this.bindEvents();
    this.getPricesDetails();
    this.getVariationDetails();
  }

  // ========= Fetch de variaciones =========
  getVariationDetails() {
    const params = new URLSearchParams(window.location.search);
    const sku = params.get('sku');
    const sku_variation = params.get('sku_variation');

    const url = "../../controller/products/variations.php";
    const data = { action: "get_variation_details", sku, sku_variation };

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
    })
    .then(r => r.ok ? r.json() : Promise.reject(new Error("Network error.")))
    .then(data => {
      if (data?.success) {
        this.renderMenuTop(data.variations);
        this.selectMenuCurrentItemBySku();
      }
    })
    .catch(err => console.error("Error:", err));
  }

  // ========= Fetch de precios =========
  getPricesDetails() {
    const params = new URLSearchParams(window.location.search);
    const sku_variation = params.get('sku_variation');

    const url = "../../controller/products/price.php";
    const data = { action: "get_prices_details", sku_variation };

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
    })
    .then(r => r.ok ? r.json() : Promise.reject(new Error("Network error.")))
    .then(data => {
      if (data?.success && Array.isArray(data.prices) && data.prices.length) {
        this.drawRows(data.prices);   // pinta filas con IDs de BD
      } else {
        // Sin precios: mantener vacío; el usuario decide si agrega o no.
        this.rows = [];
        this.renderRows();
      }
    })
    .catch(() => {
      // Error de red: mantener vacío
      this.rows = [];
      this.renderRows();
    });
  }

  // ========= Utilidades =========
  makeId() {
    return Math.random().toString(36).slice(2, 10);
  }

  parseNameSkuFromText(text) {
    const raw = (text ?? '').toString().trim();
    if (raw === '') return { name:'', sku:'' };
    const skuPattern = /[A-Z]{3,}-\d{8}-\d{6}-\d{6}-[A-F0-9]{10}/i;

    const any = raw.match(skuPattern);
    if (any) return { name: raw.replace(skuPattern,'').trim(), sku:any[0].trim() };

    const sep = /\s*[—–\-:]\s*/;
    const parts = raw.split(sep).filter(Boolean);
    if (parts.length >= 2) {
      const last = parts[parts.length - 1].trim();
      if (skuPattern.test(last)) {
        parts.pop();
        return { name: parts.join(' — ').trim(), sku:last };
      }
    }

    const par = raw.match(/\(([^)]+)\)\s*$/);
    if (par && skuPattern.test(par[1])) {
      return { name: raw.slice(0, par.index).trim(), sku:par[1].trim() };
    }

    return { name: raw, sku: '' };
  }

  renderMenuTop(items) {
    const ul = document.getElementById('menu_list');
    if (!ul) return;
    const list = Array.isArray(items) ? items
               : (items && Array.isArray(items.variations) ? items.variations : []);
    ul.innerHTML = '';
    ul.setAttribute('role','menu');

    if (!list.length) {
      const li = document.createElement('li');
      li.textContent = 'No items to show';
      li.setAttribute('role','menuitem'); li.setAttribute('tabindex','-1');
      ul.appendChild(li); return;
    }

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, m =>
      ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])
    );

    const frag = document.createDocumentFragment();
    list.forEach((it) => {
      const name = (it?.name ?? '(unnamed)').trim() || '(unnamed)';
      const sku  = (it?.SKU ?? it?.sku ?? '').trim();

      const li = document.createElement('li');
      li.setAttribute('role','menuitem'); li.setAttribute('tabindex','-1');
      li.dataset.name = name; li.dataset.sku = sku;
      li.innerHTML = `<strong>${esc(name)}</strong>${sku ? ` <small style="color:var(--muted)">— ${esc(sku)}</small>` : ''}`;
      frag.appendChild(li);
    });
    ul.appendChild(frag);
  }

  selectMenuCurrentItemBySku() {
    const skuv = new URL(window.location.href).searchParams.get('sku_variation');
    if (!skuv) return false;

    const ul  = this.menuList || document.getElementById('menu_list');
    const btn = this.menuBtn  || document.getElementById('menu_btn');
    if (!ul) return false;

    ul.querySelectorAll('.is-selected').forEach(el => el.classList.remove('is-selected'));
    const norm = s => String(s || '').trim().toUpperCase();
    const wanted = norm(skuv);

    for (const li of ul.querySelectorAll('li')) {
      const skuData = li.dataset?.sku;
      const skuText = li.querySelector('small')?.textContent?.replace(/^—\s*/, '');
      const candidate = norm(skuData || skuText);
      if (candidate && candidate === wanted) {
        li.classList.add('is-selected');
        li.setAttribute('aria-selected','true');
        ul.hidden = true;
        if (btn) btn.setAttribute('aria-expanded','false');
        return true;
      }
    }
    return false;
  }

  // ========= Filas =========
  addRow(min_qty = '', max_qty = '', price = '') {
    this.rows.push({
      id: this.makeId(),             // random para nuevas filas (no-DB)
      min_qty: String(min_qty ?? ''),
      max_qty: String(max_qty ?? ''),
      price:   String(price ?? ''),
      order: this.rows.length
    });
    this.renderRows();
  }

  drawRows(rowsFromDB) {
    this.rows = (rowsFromDB || []).map(r => ({
      id: String(r.price_id ?? r.id ?? ''),                           // ID real de BD
      min_qty: String(r.min_quantity ?? r.min_qty ?? r.min ?? ''),    // CONSISTENTE con el modelo
      max_qty: String(r.max_quantity ?? r.max_qty ?? r.max ?? ''),    // CONSISTENTE con el modelo
      price:   String(r.price   ?? r.amount ?? ''),
      order: Number(r.order ?? 0)
    }));
    this.renderRows(); // si está vacío, se verá vacío
  }

  renderRows() {
    if (!this.list) return;
    this.list.innerHTML = '';
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));

    this.rows.forEach((row, idx) => {
      const wrap = document.createElement('div');
      wrap.className = 'price-row';
      wrap.innerHTML = `
        <div class="meta">Row #${idx + 1}</div>

        <input type="number" class="min-input" data-id="${esc(row.id)}"
               placeholder="Min qty" min="0" step="1" value="${esc(row.min_qty)}" aria-label="Min quantity">

        <input type="number" class="max-input" data-id="${esc(row.id)}"
               placeholder="Max qty" min="0" step="1" value="${esc(row.max_qty)}" aria-label="Max quantity">

        <input type="number" class="price-input" data-id="${esc(row.id)}"
               placeholder="Price" min="0" step="0.01" value="${esc(row.price)}" aria-label="Price">

        <button type="button" class="btn btn-danger btn-icon remove"
                data-id="${esc(row.id)}" title="Remove" aria-label="Remove">✕</button>
      `;
      this.list.appendChild(wrap);
    });
  }

  removeRow(id_price) {
    // Si el id es numérico (DB), se elimina en servidor
    if (Number.isInteger(parseInt(id_price, 10))) {
      const url = "../../controller/products/price.php";
      const data = { action: "delete_price", id_price };
      fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
      })
      .then(r => r.ok ? r.json() : Promise.reject(new Error("Network error.")))
      .then(() => {})
      .catch(err => console.error("Error:", err));
    }
    alert("The price row has been successfully removed.");
    location.reload();
  }

  // ========= Guardado =========
  async save(e) {
    e.preventDefault();

    // Permite guardar con 0 filas (arrays vacíos) para dejar la variación sin precios
    const all = sel => Array.from(this.list.querySelectorAll(sel));
    const ids    = all('.remove').map(b => b.dataset.id || '');
    const mins   = all('.min-input').map(el => el.value);
    const maxs   = all('.max-input').map(el => el.value);
    const prices = all('.price-input').map(el => el.value);

    // Validación solo si hay filas
    if (ids.length > 0) {
      for (let i = 0; i < ids.length; i++) {
        const hasMin = mins[i] !== '';
        const hasMax = maxs[i] !== '';
        const prc    = Number(prices[i]);

        if (!(Number.isFinite(prc)) || prc < 0) {
          alert('Price must be a non-negative number.');
          return;
        }
        if (hasMin) {
          const minQ = parseInt(mins[i], 10);
          if (!Number.isInteger(minQ) || minQ < 0) { alert('Min qty must be an integer ≥ 0.'); return; }
        }
        if (hasMax) {
          const maxQ = parseInt(maxs[i], 10);
          if (!Number.isInteger(maxQ) || maxQ < 0) { alert('Max qty must be an integer ≥ 0.'); return; }
        }
        if (hasMin && hasMax) {
          const minQ = parseInt(mins[i], 10);
          const maxQ = parseInt(maxs[i], 10);
          if (maxQ < minQ) { alert('Max qty cannot be less than min qty.'); return; }
        }
      }
    }

    const params = new URLSearchParams(window.location.search);
    const sku_variation = params.get('sku_variation');

    const url = "../../controller/products/price.php";
    const payload = {
      action: "create_prices",
      sku_variation,
      ids,
      min_qty: mins,
      max_qty: maxs,
      prices
    };

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    })
    .then(r => r.ok ? r.text() : Promise.reject(new Error("Network error.")))
    .then(txt => {
      let data; try { data = JSON.parse(txt); } catch { data = { success:false }; }
      if (data.success) {
        alert("The prices have been saved successfully.");
        location.reload();
      } else {
        alert("There was a problem saving price rows.");
      }
    })
    .catch(err => console.error("Error:", err));
  }

  // ========= Eventos =========
  bindEvents() {
    if (this.addBtn) {
      this.addBtn.addEventListener('click', () => this.addRow('', '', ''));
    }

    if (this.list) {
      // Eliminar
      this.list.addEventListener('click', (e) => {
        const btnRem = e.target.closest('.remove');
        if (btnRem) this.removeRow(btnRem.dataset.id);
      });

      // Sincronizar estado con inputs (por ID)
      this.list.addEventListener('input', (e) => {
        const maps = [
          ['min-input','min_qty'],
          ['max-input','max_qty'],
          ['price-input','price']
        ];
        for (const [cls, key] of maps) {
          const el = e.target.closest(`.${cls}`);
          if (el) {
            const id = el.dataset.id;
            const row = this.rows.find(x => x.id === id);
            if (row) row[key] = el.value;
            break;
          }
        }
      });
    }

    if (this.form) {
      this.form.addEventListener('submit', (e) => this.save(e));
    }

    if (this.nextBtn && window.headerAddProduct) {
      this.nextBtn.addEventListener('click', () => {
        alert("This page is being built — stay tuned!");
        // headerAddProduct.goNext('../../view/nextSection/index.php');
      });
    }
  }
}

// Instancia global
const prices = new Prices();
