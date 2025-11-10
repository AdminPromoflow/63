(() => {
  const form          = document.getElementById('variationForm');
  const parentSelect  = document.getElementById('parent_variations');
  const parentChips   = document.getElementById('parent_chips');
  const nameInput     = document.getElementById('variation_name');
  const imgPreview    = document.getElementById('img_preview');
  const clearImageBtn = document.getElementById('clear_image');

  // NUEVO: PDF
  const pdfInput      = document.getElementById('variation_pdf');
  const pdfPreview    = document.getElementById('pdf_preview');

  const addBtn        = document.getElementById('add_variation');
  const menuBtn       = document.getElementById('menu_btn');
  const menuList      = document.getElementById('menu_list');

  const variations = [];

  // Preview de imagen (tamaño ícono controlado por CSS)


  clearImageBtn.addEventListener('click', () => {
    imgInput.value = '';
    imgPreview.innerHTML = '';
  });



  // Agregar variación — añade también el PDF si existe
  addBtn.addEventListener('click', async () => {
    const name = nameInput.value.trim();
    const parentId = parentSelect.value;
    const parentTxt = parentSelect.selectedOptions[0]?.textContent || '';

    if (!parentId) return alert('Please select a parent variation.');
    if (!name)     return alert('Variation name is required.');

    const fd = new FormData();
    fd.append('action', 'create_variation');
    fd.append('variation_name', name);
    fd.append('parent_id', parentId);
    if (imgInput.files?.[0]) fd.append('image', imgInput.files[0]);            // ya existía
    if (pdfInput.files?.[0]) fd.append('pdf_artwork', pdfInput.files[0]);      // NUEVO

    try {
      const res = await fetch('../../controller/variations/create.php', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
      });
      let id = null, ok = res.ok;
      if (ok) {
        const data = await res.json().catch(()=>({}));
        id = data?.variation_id ?? null;
      }

      variations.push({ id, name, parent: parentTxt });
      renderMenu();

      alert(ok ? 'Variation added.' : 'Saved locally (server error).');

      // limpieza mínima
      nameInput.value = '';
      imgInput.value = '';
      imgPreview.innerHTML = '';
      pdfInput.value = '';
      pdfPreview.innerHTML = '';

      menuList.hidden = false;
    } catch (err) {
      console.error(err);
      variations.push({ name, parent: parentTxt });
      renderMenu();
      alert('Saved locally. Please check your connection.');
      menuList.hidden = false;
    }
  });
})();


  const menuBtn = document.getElementById("menu_btn");
  const menuList = document.getElementById("menu_list");

  // Toggle menú Variations
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

  // Cerrar con Escape cuando el menú esté abierto
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !menuList.hidden) {
      menuList.hidden = true;
      menuBtn.setAttribute('aria-expanded', 'false');
      menuBtn.focus();
    }
  });



class Variations {

    constructor() {


      this.attachImage = false;
      this.attachPDF = false;


      // NUEVO: Preview de PDF (nombre + tamaño)
      pdfInput.addEventListener('change', () => {

        this.attachPDF = true;
        pdfPreview.innerHTML = '';
        const file = pdfInput.files?.[0];
        if (!file) return;

        if (file.type !== 'application/pdf') {
          alert('Selecciona un PDF válido.');
          pdfInput.value = '';
          return;
        }

        const pill = document.createElement('div');
        pill.className = 'cp-file-pill';
        const name = document.createElement('span');
        name.textContent = file.name;

        const size = document.createElement('small');
        size.textContent = `(${Math.round(file.size / 1024)} KB)`;

        pill.appendChild(name);
        pill.appendChild(size);
        pdfPreview.appendChild(pill);
      });

      clearPdfBtn.addEventListener('click', () => {
        pdfInput.value = '';
        pdfPreview.innerHTML = '';
      });



      imgInput.addEventListener('change', () => {

        this.attachImage = true;

        imgPreview.innerHTML = '';
        const file = imgInput.files?.[0];
        if (!file) return;

        // Validación básica opcional
        if (!file.type.startsWith('image/')) {
          alert('El archivo de imagen no es válido.');
          imgInput.value = '';
          return;
        }

        const url = URL.createObjectURL(file);
        const img = document.createElement('img');
        img.src = url;
        img.alt = 'Selected variation image preview (icon)';
        imgPreview.appendChild(img);
      });


   const next_variations = document.getElementById("next_variations");

   document.addEventListener('DOMContentLoaded', () => {
     headerAddProduct.setCurrentHeader('variations');
   });

   next_variations.addEventListener("click", function(){
     //headerAddProduct.goNext('../../view/images/index.php');

     if (nameInput.value != "") {
       variationClass.saveVariationDetails();
     }
     else {
       alert("Please add a name to the variation.");
     }

   })

   addBtn.addEventListener("click", function(){
     variationClass.addNewVariation();
   })

   menuList.addEventListener('click', (e) => {
     const li = e.target.closest('li');
     if (!li || !menuList.contains(li)) return;

     // limpiar selección previa
     menuList.querySelectorAll('.is-selected').forEach(el => el.classList.remove('is-selected'));

     // marcar seleccionado
     li.classList.add('is-selected');

     const { name, sku } = variationClass.parseNameSkuFromText(li.textContent);

     // cerrar menú
     menuList.hidden = true;
     menuBtn.setAttribute('aria-expanded', 'false');

     const params = new URLSearchParams(window.location.search);
     const sku_product = params.get('sku');


     window.location.href =
     `../../view/variations/index.php?sku=${encodeURIComponent(sku_product)}&sku_variation=${encodeURIComponent(sku)}`;
   });

   this.getVariationDetails();
  }

    saveVariationDetails() {
    // URL params
    const params        = new URLSearchParams(window.location.search);
    const sku_product   = params.get('sku');             // ?sku=...
    const sku_variation = params.get('sku_variation');   // ?sku_variation=...

    // Inputs & UI
    const nameInput = document.getElementById('variation_name');
    const parentUI  = document.getElementById('parent_variations');
    const imgInput  = document.getElementById('variation_image');
    const pdfInput  = document.getElementById('variation_pdf');

    // Calcular auxiliares
    const sku_parent_variation = this.getSkuParentId(parentUI);       // (si aplica)
    const imageFile     = this.getSelectedImageFile(imgInput); // File | null
    const pdfFile       = this.getSelectedPdfFile(pdfInput);   // File | null

    // FormData con archivos
    const fd = new FormData();
    fd.append('action',        'save_variation_details');
    fd.append('sku_product',   sku_product   || '');
    fd.append('sku_variation', sku_variation || '');
    fd.append('isAttachAnImage',   this.attachImage);
    fd.append('isAttachAPDF', this.attachPDF);
    fd.append('name',          (nameInput?.value || '').trim());
    if (sku_parent_variation) fd.append('sku_parent_variation', sku_parent_variation);
    if (imageFile)     fd.append('imageFile', imageFile);   // ← clave que PHP espera
    if (pdfFile)       fd.append('pdfFile',   pdfFile);     // ← define esta en PHP si la usas

    const url = "../../controller/products/variations.php";

    fetch(url, {
      method: "POST",
      headers: {
        // No pongas Content-Type manual; FormData lo define.
        "X-Requested-With": "XMLHttpRequest"
      },
      body: fd
    })
    .then(r => {
      if (!r.ok) throw new Error("Network error.");
      return r.json();
    })
    .then(data => {
    //  alert(JSON.stringify(data));
      if (data?.success) {
        headerAddProduct.goNext('../../view/images/index.php');

      } else {
        console.error("Guardado no exitoso:", data);
        alert(data?.message || "No se pudo guardar la variación.");
      }
    })
    .catch(err => {
      console.error("Error:", err);
      alert("Error de red o servidor al guardar.");
    });
  }

    extractSkuFromText(txt) {
    if (!txt) return null;
    const m = txt.match(/sku[:\s-]*([A-Z0-9._-]+)/i) || txt.match(/\[([A-Z0-9._-]+)\]/i);
    return m ? m[1] : null;
  }

    getSkuParentId(parentEl) {
    if (!parentEl) return null;

    // Caso SELECT
    if (parentEl.tagName === 'SELECT') {
      const opt = parentEl.selectedOptions && parentEl.selectedOptions[0];
      if (!opt) return null;
      if (opt.dataset && opt.dataset.sku) return opt.dataset.sku.trim();
      if (opt.value && !/\s|\|/.test(opt.value)) return opt.value.trim(); // si el value ya es el SKU
      return extractSkuFromText(opt.textContent || '');
    }

    // Caso LISTA (UL/OL) -> <li class="is-selected">
    const li = parentEl.querySelector('.is-selected');
    if (!li) return null;
    if (li.dataset && li.dataset.sku) return li.dataset.sku.trim();
    return extractSkuFromText(li.textContent || '');
  }

    getSelectedImageFile(imgInput) {
    const f = imgInput?.files?.[0] || null;
    if (!f) return null;
    // Validaciones básicas (opcionales)
    if (!f.type.startsWith('image/')) return null;
    // if (f.size > 5 * 1024 * 1024) return null; // 5MB, opcional
    return f;
  }

    getSelectedPdfFile(pdfInput) {
    const f = pdfInput?.files?.[0] || null;
    if (!f) return null;
    if (f.type !== 'application/pdf') return null;
    // if (f.size > 10 * 1024 * 1024) return null; // 10MB, opcional
    return f;
  }

    parseNameSkuFromText(text) {
    const raw = (text ?? '').toString().trim();
    if (raw === '') return { name: '', sku: '' };

    const skuPattern = /[A-Z]{3,}-\d{8}-\d{6}-\d{6}-[A-F0-9]{10}/i;

    // 0) Si el SKU aparece en cualquier parte del texto, úsalo (prioridad máxima)
    const anyMatch = raw.match(skuPattern);
    if (anyMatch) {
      const sku  = anyMatch[0].trim();
      const name = raw.replace(skuPattern, '').replace(/[—–\-:()\s]+$/,'').trim();
      // Intenta limpiar separadores sobrantes delante del SKU
      return { name: name.replace(/[—–\-:]\s*$/,'').trim(), sku };
    }

    // 1) Nombre SEP SKU  (SEP = —, –, -, :)
    const sepPattern = /\s*[—–\-:]\s*/; // em/en dash, hyphen, colon
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

    // 2) "Nombre (SKU)" al final
    const mParen = raw.match(/\(([^)]+)\)\s*$/);
    if (mParen && skuPattern.test(mParen[1])) {
      const sku  = mParen[1].trim();
      const name = raw.slice(0, mParen.index).trim();
      return { name, sku };
    }

    // 3) Fallback: no se pudo detectar SKU con tu formato → todo es nombre
    return { name: raw, sku: '' };
  }

    addNewVariation(){
    const params = new URLSearchParams(window.location.search);
    const sku = params.get('sku');


    const url = "../../controller/products/variations.php";
    const data = {
      action: "create_new_variation",
      sku: sku
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

        const sku_variation = data["sku_variation"];


       if (data["success"]) {
         alert("The new variation has been successfully created. Please fill in the details and save once you’ve finished.");
         window.location.href =
         `../../view/variations/index.php?sku=${encodeURIComponent(sku)}&sku_variation=${encodeURIComponent(sku_variation)}`;
        }
      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });
  }

    getVariationDetails(){

    const params = new URLSearchParams(window.location.search);
    const sku = params.get('sku');
    const sku_variation = params.get('sku_variation');


    const url = "../../controller/products/variations.php";
    const data = {
      action: "get_variation_details",
      sku: sku,
      sku_variation: sku_variation
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
      //  alert(data);

        var data = JSON.parse(data);

       if (data["success"]) {

         variationClass.drawParentsVariationItems(data["variations"], data["product"], data["current"]);
         variationClass.drawImageVariationSelected(data["current"]["image"]);
         variationClass.setPdfPreview(data["current"]["pdf_artwork"]);
         variationClass.setImagePreview(data["current"]["image"]);
         variationClass.renderMenuTop(data["variations"]);
         variationClass.selectCurrentVariation(data["variations"], data["product"], data["current"], data["parent"]);

        }
      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });

  }

    selectCurrentVariation(dataVariations, dataProduct, dataCurrent, dataParent){
    nameInput.value = dataCurrent["name"];

      if (dataCurrent["name"] == "Default") {


        alert(
          "1) If you won't add any variations, keep 'Default variation' selected. (Editing options for 'Default' are disabled.)\n" +
          "2) Click 'Save & Next'.\n" +
          "3) Add images, items, and prices.\n\n" +
          "— OR —\n" +
          "If you will use variations: click 'Add' and create your first-level variations with 'Parent variation' set to 'Default'."
        );

        parent_variations.disabled = true;
        variation_name.disabled = true;
        variation_image.disabled = true;
        variation_pdf.disabled = true;
      }

    this.selectMenuCurrentItemBySku();
    this.selectParentVariationsItems(dataVariations, dataProduct, dataCurrent,  dataParent);


  }

    selectParentVariationsItems(dataVariations = [], dataProduct = {}, dataCurrent = {}, dataParent = {}) {
    const sel = this?.parentSelect || document.getElementById('parent_variations');
    if (!sel) return;

    // Prioridad: SKU del padre; si es null/ vacío, usar product_sku
    const target = (dataParent && dataParent.sku) ? dataParent.sku : dataProduct?.product_sku;
    if (!target) return;

    const norm = s => String(s || '').trim().toUpperCase();
    const wanted = norm(target);

    let matched = false;
    for (const opt of sel.options) {
      const byValue  = norm(opt.value);
      const byData   = norm(opt.getAttribute('data-sku'));
      if ((byValue && byValue === wanted) || (byData && byData === wanted)) {
        opt.selected = true;
        sel.value = opt.value;     // asegura que el <select> refleje la opción
        matched = true;
        break;
      }
    }

    // Si no hubo match, deja el placeholder (índice 0)
    if (!matched) sel.selectedIndex = 0;

    // Si tienes chips de UI, actualízalos
    if (typeof this.refreshParentChips === 'function') this.refreshParentChips();
  }

    selectMenuCurrentItemBySku() {
    const currentUrl = new URL(window.location.href);
    const skuv = currentUrl.searchParams.get('sku_variation');
    if (!skuv) return false;

    const ul  = this?.menuList || document.getElementById('menu_list');
    const btn = this?.menuBtn  || document.getElementById('menu_btn');
    if (!ul) return false;

    // Limpiar selección previa
    ul.querySelectorAll('.is-selected').forEach(el => el.classList.remove('is-selected'));

    const norm   = s => String(s || '').trim().toUpperCase();
    const wanted = norm(skuv);

    // Buscar por data-sku y, si no existe, intentar extraer del <small>
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
    //  alert(JSON.stringify(items));
    const ul = document.getElementById('menu_list');
    if (!ul) return;

    ul.innerHTML = '';
    const n = Math.min(items.length, Array.isArray(items) ? items.length : 0);

    if (n === 0) {
      const li = document.createElement('li');
      li.textContent = 'No items to show';
      li.style.padding = '8px 10px';
      li.style.borderRadius = '10px';
      ul.appendChild(li);
      ul.hidden = false;
      return;
    }

    for (let i = 0; i < n; i++) {
      const it = items[i] || {};
      const name = it.name ?? '(unnamed)';
      const sku  = it.SKU ?? it.sku ?? '';

      const li = document.createElement('li');
      li.style.padding = '8px 10px';
      li.style.borderRadius = '10px';
      li.style.cursor = 'default';
      li.innerHTML = `<strong>${name}</strong>${sku ? ` <small style="color:var(--muted)">— ${sku}</small>` : ''}`;
      ul.appendChild(li);
    }

  }

    drawImageVariationSelected(urlCurrentImage){
    imgPreview.innerHTML = '';

    let url; // <-- no puede ser const si lo reasignas

    if (urlCurrentImage && String(urlCurrentImage).trim() !== '') {
      const u = String(urlCurrentImage).trim();

      // Si ya es absoluta (http/https, data:, blob:), úsala tal cual
      if (/^(https?:|data:|blob:)/i.test(u)) {
        url = u;
      } else {
        // Relativa: evita // al concatenar
        url = '../../' + u.replace(/^\/+/, '');
      }
    } else {
      // Fallback al ícono por defecto
      url = '../../view/variations/images/add_image.png';
    }

    const img = new Image();
    img.alt = 'Selected variation image preview (icon)';
    img.loading = 'lazy';
    img.decoding = 'async';
    img.src = url;

    // Si falla la carga, usar el ícono por defecto
    img.onerror = () => { img.src = '../../view/variations/images/add_image.png'; };

    imgPreview.appendChild(img);
  }

    setPdfPreview(url){
    const pdfPreview = document.getElementById('pdf_preview');
    if (!pdfPreview) return;

    const u = (url || '').trim();
    if (!u) { pdfPreview.textContent = ''; return; }

    // Si no empieza por http(s) ni por '/', la anclamos a la raíz
    const href = (/^(https?:)?\/\//.test(u) || u.startsWith('/')) ? u : '/' + u.replace(/^\/+/, '');
  //  alert(`<a href="../..${href}" download="artwork.pdf">artwork.pdf</a>`);
    pdfPreview.innerHTML = `<a href="../..${href}" download="artwork.pdf">artwork.pdf</a>`;
  }

    setImagePreview(url){
    const imgPreview = document.getElementById('img_preview');

    const u = (url || '').trim();
    if (!u) { imgPreview.textContent = ''; return; }

    // Si no empieza por http(s) ni por '/', la anclamos a la raíz
    const href = (/^(https?:)?\/\//.test(u) || u.startsWith('/')) ? u : '/' + u.replace(/^\/+/, '');

    imgPreview.innerHTML = `<img alt="Selected variation image preview (icon)" loading="lazy" decoding="async" src="../..${href}">`
  }

    isAttachAnImage(attachImage){
      this.attachImage = attachImage;
    }
    isAttachAPDF(attachPDF){
      this.attachPDF = attachPDF;
    }

    drawParentsVariationItems(dataVariations = [], dataProduct = {}, dataCurrent = {}) {
    const sel = this?.parentSelect || document.getElementById('parent_variations');

    sel.innerHTML = '<option value="" disabled selected>Select a parent</option>';

    // Opción del producto (nombre — SKU del producto)
  /*  if (dataProduct?.product_sku && dataProduct?.product_name) {
      sel.innerHTML += `
        <option value="${dataProduct.product_sku}" data-sku="${dataProduct.product_sku}">
          ${dataProduct.product_name} — ${dataProduct.product_sku}
        </option>`;
    }*/

    // Variaciones (nombre — SKU), excluyendo la variación actual
    for (const row of (Array.isArray(dataVariations) ? dataVariations : [])) {
      const sku  = row?.SKU ?? row?.sku ?? '';
      if (!sku || sku === (dataCurrent?.sku ?? '')) continue;

      const name = row?.name ?? '(unnamed variation)';
      sel.innerHTML += `
        <option value="${sku}" data-sku="${sku}">
          ${name} — ${sku}
        </option>`;
    }
  }

}

const addBtn        = document.getElementById('add_variation');

const pdfInput      = document.getElementById('variation_pdf');
const pdfPreview    = document.getElementById('pdf_preview');
const clearPdfBtn   = document.getElementById('clear_pdf');

const imgPreview    = document.getElementById('img_preview');
const imgInput      = document.getElementById('variation_image');

const parentSelect  = document.getElementById('parent_variations');
const nameInput     = document.getElementById('variation_name');

const variationClass = new Variations();
