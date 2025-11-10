class Images {
  constructor() {
    document.addEventListener('DOMContentLoaded', () => {
      // ===== Toggle menú "Variations" =====
      const menuBtn  = document.getElementById('menu_btn');
      const menuList = document.getElementById('menu_list');
      this.menuBtn = menuBtn;
      this.menuList = menuList;

      if (menuBtn && menuList) {
        // Abrir/cerrar con clic
        menuBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          const isHidden = menuList.hidden;
          menuList.hidden = !isHidden;
          menuBtn.setAttribute('aria-expanded', String(!isHidden));
        });

        // Cerrar al hacer clic fuera
        document.addEventListener('click', (e) => {
          if (!menuBtn.contains(e.target) && !menuList.contains(e.target)) {
            if (!menuList.hidden) {
              menuList.hidden = true;
              menuBtn.setAttribute('aria-expanded', 'false');
            }
          }
        });

        // Cerrar con Escape
        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape' && !menuList.hidden) {
            menuList.hidden = true;
            menuBtn.setAttribute('aria-expanded', 'false');
            menuBtn.focus();
          }
        });

        // Click del menú
        menuList.addEventListener('click', (e) => {
          const li = e.target.closest('li');
          if (!li || !menuList.contains(li)) return;

          // limpiar selección previa
          menuList.querySelectorAll('.is-selected').forEach(el => {
            el.classList.remove('is-selected');
            el.setAttribute('aria-selected', 'false');
          });

          // marcar seleccionado
          li.classList.add('is-selected');
          li.setAttribute('aria-selected', 'true');

          const { name, sku } = imagesClass.parseNameSkuFromText(li.textContent);

          // cerrar menú
          menuList.hidden = true;
          menuBtn.setAttribute('aria-expanded', 'false');

          const params = new URLSearchParams(window.location.search);
          const sku_product = params.get('sku');

          window.location.href =
            `../../view/images/index.php?sku=${encodeURIComponent(sku_product)}&sku_variation=${encodeURIComponent(sku)}`;
        });
      }

      // Inicia el resto (DOM ya listo)
      this.init();
      // Trae imágenes del servidor y apéndelas (sin limpiar)
      this.getImagesDetails();
    });
  }

  // ====== Estado y utilidades ======
  items = []; // {id, url, name, size, origin: 'server'|'local', file?}

  uid() { return Math.random().toString(36).slice(2,10); }

  humanSize(bytes) {
    const n = Number(bytes);
    if (!Number.isFinite(n)) return '';
    const u = ['B','KB','MB','GB','TB'];
    let i = 0, v = n;
    while (v >= 1024 && i < u.length - 1) { v /= 1024; i++; }
    return `${v.toFixed(v < 10 && i > 0 ? 1 : 0)} ${u[i]}`;
  }

  // ====== Inicialización de UI ======
  init() {
    if (window.headerAddProduct) {
      window.headerAddProduct.setCurrentHeader('images');
      const nextBtn = document.getElementById('next_images');
      nextBtn && nextBtn.addEventListener('click',  e => {

        e.preventDefault();
        const params = new URLSearchParams(window.location.search);
        const sku_variation = params.get('sku_variation');
        const sku_product   = params.get('sku');

        const locals = this.items.filter(it => it.origin === 'local');
        if (locals.length) {
          const fd = new FormData();
          fd.append('action', 'create_update_images');
          fd.append('sku_variation', sku_variation ?? '');
          fd.append('sku_product',   sku_product ?? '');
          fd.append('meta', JSON.stringify(locals.map((it,i)=>({ order:i, filename: it.name }))));

          locals.forEach(it => fd.append('images[]', it.file, it.name));

          this.createUpdateImages(fd);
        }



        setTimeout(() => {
          window.headerAddProduct.goNext('../../view/items/index.php');
        }, 1300);


      });
    }

    const $ = s => document.querySelector(s);
    const form      = $('#variationImagesForm');
    if (!form) return;

    const dropzone  = $('#dropzone');
    const pickBtn   = $('#pick_files');
    const fileInput = $('#images_input');
    const gallery   = $('#gallery');
    this.gallery = gallery;

    const resetBtn  = $('#reset_form');

    // Config
    this.MAX_SIZE = 10 * 1024 * 1024; // 10MB
    this.ACCEPTED = ['image/jpeg','image/png','image/webp'];

    // Eventos (append-only)
    pickBtn?.addEventListener('click', () => fileInput.click());
    fileInput?.addEventListener('change', e => this.addLocalFiles(e.target.files));

    const block = e => { e.preventDefault(); e.stopPropagation(); };
    ['dragenter','dragover','dragleave','drop'].forEach(ev => dropzone?.addEventListener(ev, block));
    ['dragenter','dragover'].forEach(ev => dropzone?.addEventListener(ev, () => dropzone.classList.add('is-dragover')));
    ['dragleave','drop'].forEach(ev => dropzone?.addEventListener(ev, () => dropzone.classList.remove('is-dragover')));
    dropzone?.addEventListener('drop', e => this.addLocalFiles(e.dataTransfer.files));

    // Delegación para quitar
    this.gallery?.addEventListener('click', e => {
      const btn = e.target.closest('.rm');
      if (btn) this.removeById(btn.dataset.id);
    });

    // Reset: elimina SOLO locales (mantiene servidor)
    resetBtn?.addEventListener('click', () => {
      form.reset();
      if (fileInput) fileInput.value = '';
      // eliminar del DOM y revocar solo las locales
      this.items
        .filter(it => it.origin === 'local')
        .forEach(it => {
          URL.revokeObjectURL(it.url);
          this.gallery.querySelector(`[data-id="${it.id}"]`)?.remove();
        });
      // mantener solo 'server' en estado
      this.items = this.items.filter(it => it.origin === 'server');
    });

    // Submit: envía solo las locales (las del servidor ya existen)
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const params = new URLSearchParams(window.location.search);
      const sku_variation = params.get('sku_variation');
      const sku_product   = params.get('sku');

      const locals = this.items.filter(it => it.origin === 'local');
      if (!locals.length) { alert('Please add at least one image.'); return; }

      const fd = new FormData();
      fd.append('action', 'create_update_images');
      fd.append('sku_variation', sku_variation ?? '');
      fd.append('sku_product',   sku_product ?? '');
      fd.append('meta', JSON.stringify(locals.map((it,i)=>({ order:i, filename: it.name }))));

      locals.forEach(it => fd.append('images[]', it.file, it.name));

      this.createUpdateImages(fd);
    });
  }

  // ====== Pinta una miniatura al final (sin limpiar) ======
  appendThumb(it) {
    const fig = document.createElement('figure');
    fig.className = 'cp-thumb';
    fig.dataset.id = it.id;

    const sizeTxt = Number.isFinite(Number(it.size)) ? ` • ${this.humanSize(it.size)}` : '';
    const displayName = it.name || it.url.split('/').pop()?.split('?')[0] || 'Imagen';

    fig.innerHTML = `
      <img class="thumb-img" src="${it.url}" alt="${displayName}" loading="lazy" decoding="async" width="80" height="80">
      <button class="rm" type="button" data-id="${it.id}" aria-label="Quitar imagen">×</button>
      <figcaption class="meta" title="${displayName}">${displayName}${sizeTxt}</figcaption>
    `;

    fig.querySelector('.thumb-img').addEventListener('error', () => fig.classList.add('broken'));
    this.gallery.appendChild(fig);
    return fig;
  }

  // ====== Agrega archivos locales (input/drag) → apéndice ======
  addLocalFiles(fileList) {
    Array.from(fileList || []).forEach(f => {
      if (!this.ACCEPTED.includes(f.type)) { alert(`Unsupported type: ${f.type}`); return; }
      if (f.size > this.MAX_SIZE)           { alert(`${f.name} is too large (${this.humanSize(f.size)}).`); return; }

      const it = {
        id: this.uid(),
        url: URL.createObjectURL(f),
        name: f.name,
        size: f.size,
        origin: 'local',
        file: f
      };
      this.items.push(it);
      this.appendThumb(it); // 👈 apéndice (no limpia)
    });
  }

  // ====== Quitar por id (DOM + estado) ======
  removeById(id) {
    const btn = document.querySelector(`button[data-id="${CSS.escape(id)}"]`);
      if (!btn) return -1;

      // 2) Toma la primera clase del botón
      const cls = btn.classList[0];
      if (!cls) return -1;

      // 3) Busca todos los botones con esa misma clase
      const list = Array.from(document.querySelectorAll(`button.${CSS.escape(cls)}`));

      // 4) Devuelve el índice del botón dentro de esa lista (0, 1, 2, ...)
      var index = list.indexOf(btn);

      // Usar el mismo índice para obtener la imagen correspondiente
      const img = document.querySelectorAll(".thumb-img")[index];

      let src =
        img?.getAttribute('src') ||
        img?.getAttribute('data-src') ||
        img?.getAttribute('data-original') ||
        img?.currentSrc ||
        '';

        src = src.replace(/^(\.\.\/){2}/, "");

      // 'src' es string: obtener pathname con URL para extraer el nombre
      const filename = new URL(src, window.location.href).pathname.split('/').pop();
      const name = decodeURIComponent(filename);

      const allowed = ['jpg','jpeg','png','gif','webp'];

      // obtener extensión (sin el punto)
      const ext = name.split('.').pop().toLowerCase();

      // verificar si NO tiene extensión o NO está permitida
      const validExt = name.includes('.') || !allowed.includes(ext);

      if (validExt) {
        this.sendAjaxtRequestDeleteImage(src);
      }



    const idx = this.items.findIndex(x => x.id === id);
    if (idx === -1) return;
    const it = this.items[idx];
    if (it.origin === 'local') URL.revokeObjectURL(it.url);
    this.items.splice(idx, 1);
    this.gallery.querySelector(`[data-id="${id}"]`)?.remove();
  }

  // (Opcional) Re-render total si alguna vez lo necesitas
  renderAll() {
    this.gallery.innerHTML = '';
    this.items.forEach(it => this.appendThumb(it));
  }

  renderedIds = new Set();

  basename(p) {
    return String(p || '').split('/').pop()?.split('?')[0] || '';
  }

  normalizeUrl(link) {
    // Tu link viene como "controller/uploads/..." y tu vista está en "view/images/"
    // Por eso anteponemos "../../" para llegar a /controller/...
    if (!link) return '';
    return link.startsWith('http') ? link : `../../${String(link).replace(/^\/+/, '')}`;
  }

  sendAjaxtRequestDeleteImage(link_image){
    const params = new URLSearchParams(window.location.search);
    const sku_variation = params.get('sku_variation');


    const url = "../../controller/products/image.php";
    const data = {
      action: "delete_image",
      sku_variation: sku_variation,
      link_image: link_image
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

        var data = JSON.parse(data);

       if (!data["success"]) {
         alert("The image could not be deleted. Please refresh the page and try again.");
        }
      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });
  }

  // ====== Carga inicial desde servidor (append-only) ======
  getImagesDetails() {
    const params = new URLSearchParams(window.location.search);
    const sku = params.get('sku');
    const sku_variation = params.get('sku_variation');

    const url = "../../controller/products/image.php";
    const payload = { action: "get_images_details", sku, sku_variation };

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    })
    .then(res => res.ok ? res.text() : Promise.reject(new Error("Network error.")))
    .then(txt => {
      // Si quieres depurar, usa console.log para no interrumpir el flujo:
      // console.log('RAW:', txt);

      let data;
      try { data = JSON.parse(txt); }
      catch (e) { console.error("JSON parse error", e, txt); return; }

      if (!data?.success) return;

      // ===== Pintar imágenes del servidor (append-only, sin limpiar la galería) =====
      const list = Array.isArray(data.images) ? data.images : [];
      list.forEach(({ image_id, link }) => {
        // Evitar duplicados si ya se pintó ese image_id
        const sid = String(image_id ?? '');
        if (sid && this.renderedIds.has(sid)) return;

        const url = this.normalizeUrl(link);
        const name = this.basename(url);

        // Si ya tienes renderImage (append-only), úsala:
        const res = this.renderImage(undefined, name, url);
        // registra el id pintado
        if (sid) this.renderedIds.add(sid);

        // (Opcional) también podrías sincronizar tu estado this.items si lo usas:
        if (res && this.items) {
          this.items.push({ id: sid || res.id, url, name, origin: 'server' });
        }
      });

      // ===== Menú de variaciones =====
      if (data.variations) {
        this.renderMenuTop(data.variations);
        this.selectMenuCurrentItemBySku();
      }
    })
    .catch(err => {
      console.error("Error:", err);
    });
  }

  // ====== Envío al backend ======
  createUpdateImages(fd){
    const url = "../../controller/products/image.php";
    fetch(url, {
      method: "POST",
      body: fd,
      credentials: "same-origin"
    })
    .then(res => res.json())
    .then(data => {
      if (data["success"]) {
        alert("The images have been uploaded successfully.");
        // aquí podrías marcar las nuevas como 'server' si el backend devuelve IDs
        // y opcionalmente limpiar 'origin' === 'local'
      }
    })
    .catch(err => {
      console.error("Error:", err);
      alert("There was a network or server error while uploading the images.");
    });
  }

  // ====== (Opcional) wrapper para agregar una imagen del servidor manualmente ======
  renderImage(size, name, url) {
    if (!url || !this.gallery) return null;
    const it = {
      id: this.uid(),
      url,
      name,
      size,
      origin: 'server'
    };
    this.items.push(it);
    const el = this.appendThumb(it);
    return { id: it.id, el };
  }

  // ====== Helpers del menú (sin cambios de lógica) ======
  parseNameSkuFromText(text) {
    const raw = (text ?? '').toString().trim();
    if (raw === '') return { name: '', sku: '' };

    const skuPattern = /[A-Z]{3,}-\d{8}-\d{6}-\d{6}-[A-F0-9]{10}/i;

    const anyMatch = raw.match(skuPattern);
    if (anyMatch) {
      const sku  = anyMatch[0].trim();
      const name = raw.replace(skuPattern, '').replace(/[—–\-:()\s]+$/,'').trim();
      return { name: name.replace(/[—–\-:]\s*$/,'').trim(), sku };
    }

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

    const mParen = raw.match(/\(([^)]+)\)\s*$/);
    if (mParen && skuPattern.test(mParen[1])) {
      const sku  = mParen[1].trim();
      const name = raw.slice(0, mParen.index).trim();
      return { name, sku };
    }

    return { name: raw, sku: '' };
  }

  selectMenuCurrentItemBySku() {
    const currentUrl = new URL(window.location.href);
    const skuv = currentUrl.searchParams.get('sku_variation');
    if (!skuv) return false;

    const ul  = this?.menuList || document.getElementById('menu_list');
    const btn = this?.menuBtn  || document.getElementById('menu_btn');
    if (!ul) return false;

    ul.querySelectorAll('.is-selected').forEach(el => el.classList.remove('is-selected'));

    const norm   = s => String(s || '').trim().toUpperCase();
    const wanted = norm(skuv);

    for (const li of ul.querySelectorAll('li')) {
      const skuData = li.dataset?.sku;
      const skuText = li.querySelector('small')?.textContent?.replace(/^—\s*/, '');
      const candidate = norm(skuData || skuText);

      if (candidate && candidate === wanted) {
        li.classList.add('is-selected');
        ul.hidden = true;
        if (btn) btn.setAttribute('aria-expanded', 'false');
        return true;
      }
    }
    return false;
  }

  renderMenuTop(items) {
    const ul = document.getElementById('menu_list');
    if (!ul) return;

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
}

const imagesClass = new Images();
