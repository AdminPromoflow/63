(() => {
  // DOM
  const crumbsEl = document.getElementById('sp_breadcrumbs');
  const thumbsEl = document.getElementById('sp_thumbs');
  const mainEl   = document.getElementById('sp_main');

  const titleEl  = document.getElementById('sp-title');
  const skuEl    = document.getElementById('sp_sku');
  const brandEl  = document.getElementById('sp-brand');

  const currencyEl = document.getElementById('sp_currency');
  const fromEl     = document.getElementById('sp_from');

  const varSel  = document.getElementById('sp_variation');
  const qtyEl   = document.getElementById('sp_qty');

  const bullets = document.getElementById('sp_features');
  const descEl  = document.getElementById('sp_desc');
  const tiersTb = document.getElementById('sp_tiers');

  const bbUnit  = document.getElementById('bb_unit');
  const bbTotal = document.getElementById('bb_total');
  const bbAdd   = document.getElementById('bb_add');
  const bbBuy   = document.getElementById('bb_buy');

  // State
  let DATA = null;
  let CHILD = null;

  // Helpers
  const money = (v, c) => new Intl.NumberFormat(undefined, { style:'currency', currency:c || 'USD' }).format(Number(v || 0));

  const renderCrumbs = (arr=[]) => {
    crumbsEl.innerHTML = '';
    (arr.length ? arr : ['Store','Category']).forEach((name, i, a) => {
      const li = document.createElement('li');
      li.innerHTML = i < a.length-1 ? `<a href="#">${name}</a>` : name;
      crumbsEl.appendChild(li);
    });
  };

  const setMain = (url, alt='Product image') => {
    mainEl.innerHTML = '';
    if (!url) { mainEl.innerHTML = `<div class="cp-empty">No images</div>`; return; }
    const img = document.createElement('img');
    img.src = url; img.alt = alt;
    mainEl.appendChild(img);
  };

  const renderThumbs = (images=[]) => {
    thumbsEl.innerHTML = '';
    images.forEach((im, idx) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = idx===0 ? 'active' : '';
      btn.innerHTML = `<img src="${im.url}" alt="${im.alt || ('Image '+(idx+1))}">`;
      btn.addEventListener('click', () => {
        [...thumbsEl.querySelectorAll('button')].forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        setMain(im.url, im.alt);
      });
      thumbsEl.appendChild(btn);
    });
    if (images[0]) setMain(images[0].url, images[0].alt);
  };

  const renderBullets = (items=[]) => {
    bullets.innerHTML = '';
    if (!items.length) { bullets.innerHTML = `<li class="cp-hint">No features</li>`; return; }
    items.forEach(it => {
      const li = document.createElement('li');
      if (it.highlight) li.classList.add('highlight');
      li.textContent = (it.label ? `${it.label}: ` : '') + it.text;
      bullets.appendChild(li);
    });
  };

  const renderTiers = (intervals=[], cur='USD') => {
    tiersTb.innerHTML = '';
    intervals.forEach(t => {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${t.min_amount}</td><td>${t.max_amount}</td><td>${money(t.unit_price, cur)}</td>`;
      tiersTb.appendChild(tr);
    });
  };

  const getTierForQty = (intervals=[], qty=1) =>
    intervals.find(t => qty >= Number(t.min_amount) && qty <= Number(t.max_amount)) || null;

  const updatePrices = () => {
    const cur = DATA?.currency || 'USD';
    const q = Number(qtyEl.value || 0);
    const tier = CHILD ? getTierForQty(CHILD.intervals || [], q) : null;
    const unit = tier ? Number(tier.unit_price) : null;

    bbUnit.textContent  = unit != null ? money(unit, cur) : '—';
    bbTotal.textContent = unit != null ? money(unit * q, cur) : '—';
  };

  const computeFrom = (child) => {
    const cur = DATA?.currency || 'USD';
    const min = Math.min(...(child?.intervals || []).map(x=>Number(x.unit_price)));
    return Number.isFinite(min) ? money(min, cur) : '—';
  };

  const selectChild = (id) => {
    const c = (DATA?.children || []).find(x => x.id === id) || null;
    CHILD = c;
    if (!c) {
      renderThumbs([]); renderBullets([]); renderTiers([]); updatePrices(); return;
    }
    const images = (c.images || []).slice().sort((a,b)=> (b.cover?1:0)-(a.cover?1:0));
    renderThumbs(images);
    renderBullets(c.items || []);
    renderTiers(c.intervals || [], DATA?.currency || 'USD');
    fromEl.textContent = computeFrom(c);
    updatePrices();
  };

  const fillChildSelect = () => {
    varSel.innerHTML = '';
    const arr = DATA?.children || [];
    if (!arr.length) { varSel.innerHTML = `<option value="">No variations</option>`; selectChild(null); return; }
    arr.forEach(ch => {
      const opt = document.createElement('option');
      opt.value = ch.id; opt.textContent = ch.name;
      varSel.appendChild(opt);
    });
    const def = arr.find(x => x.id === DATA?.default_child_id) || arr[0];
    varSel.value = def.id;
    selectChild(def.id);
  };

  // Events
  varSel.addEventListener('change', () => selectChild(varSel.value));
  qtyEl.addEventListener('input', updatePrices);
  bbAdd.addEventListener('click', () => alert('Preview only: cart disabled.'));
  bbBuy.addEventListener('click', () => alert('Preview only: checkout disabled.'));

  // Init
  (async function init(){
    try {
      const res = await fetch('../../controller/product/preview.php', {
        method:'POST',
        headers:{ 'Content-Type':'application/json' },
        credentials:'same-origin',
        body: JSON.stringify({ action:'get_product_preview' })
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      DATA = await res.json();

      // Header/meta
      titleEl.textContent = DATA?.product?.name || '—';
      brandEl.textContent = DATA?.product?.brand || 'Brand';
      skuEl.textContent   = 'SKU: ' + (DATA?.product?.sku || '—');
      descEl.textContent  = DATA?.product?.description || '—';
      currencyEl.textContent = DATA?.currency || 'USD';

      // Breadcrumb
      const path = DATA?.product?.category_path || [];
      crumbsEl.innerHTML = '';
      path.forEach((name,i)=> {
        const li = document.createElement('li');
        li.innerHTML = i<path.length-1 ? `<a href="#">${name}</a>` : name;
        crumbsEl.appendChild(li);
      });

      // Variaciones
      fillChildSelect();
    } catch (e) {
      console.error(e);
      mainEl.innerHTML = `<div class="cp-empty">Could not load product</div>`;
    }
  })();
})();
