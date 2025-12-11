// ===== Referencias DOM base =====
const form          = document.getElementById('variationForm');
const parentSelect  = document.getElementById('parent_variations');
const parentChips   = document.getElementById('parent_chips');
const nameInput     = document.getElementById('variation_name');

const imgInput      = document.getElementById('variation_image');
const imgPreview    = document.getElementById('img_preview');
const clearImageBtn = document.getElementById('clear_image');

const pdfInput      = document.getElementById('variation_pdf');
const pdfPreview    = document.getElementById('pdf_preview');
const clearPdfBtn   = document.getElementById('clear_pdf');

const addBtn        = document.getElementById('add_variation');
const menuBtn       = document.getElementById('menu_btn');
const menuList      = document.getElementById('menu_list');
const nextBtn       = document.getElementById('next_variations');

const groupSelect   = document.getElementById('group');

// Modal group
const groupModal      = document.getElementById('group_modal');
const groupNameInput  = document.getElementById('group_name_input');
const groupCancelBtn  = document.getElementById('group_cancel_btn');
const groupCreateBtn  = document.getElementById('group_create_btn');

// ===== Helpers =====
function extractSkuFromText(txt) {
  if (!txt) return null;
  const m = txt.match(/sku[:\s-]*([A-Z0-9._-]+)/i) || txt.match(/\[([A-Z0-9._-]+)\]/i);
  return m ? m[1] : null;
}

// ===== Clase principal =====
class Variations {

  constructor() {
    this.attachImage = false;
    this.attachPDF   = false;

    this.parentSelect = parentSelect;
    this.menuBtn      = menuBtn;
    this.menuList     = menuList;

    this.bindImagePreview();
    this.bindPdfPreview();
    this.bindMenuList();
    this.bindHeaderAndButtons();

    this.initGroupSelectPlaceholder();

    this.getVariationDetails();
  }

  // --- Bindings básicos ---

  bindImagePreview() {
    if (!imgInput || !imgPreview) return;

    imgInput.addEventListener('change', () => {
      this.attachImage = true;
      imgPreview.innerHTML = '';
      const file = imgInput.files?.[0];
      if (!file) return;

      if (!file.type.startsWith('image/')) {
        alert('El archivo de imagen no es válido.');
        imgInput.value = '';
        return;
      }

      const url = URL.createObjectURL(file);
      const img = document.createElement('img');
      img.src = url;
      img.alt = 'Selected variation image preview (icon)';
      img.loading = 'lazy';
      img.decoding = 'async';
      imgPreview.appendChild(img);
    });

    if (clearImageBtn) {
      clearImageBtn.addEventListener('click', () => {
        imgInput.value = '';
        imgPreview.innerHTML = '';
        this.attachImage = false;
      });
    }
  }

  bindPdfPreview() {
    if (!pdfInput || !pdfPreview) return;

    pdfInput.addEventListener('change', () => {
      this.attachPDF = true;
      pdfPreview.innerHTML = '';
      const file = pdfInput.files?.[0];
      if (!file) return;

      if (file.type !== 'application/pdf') {
        alert('Selecciona un PDF válido.');
        pdfInput.value = '';
        this.attachPDF = false;
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

    if (clearPdfBtn) {
      clearPdfBtn.addEventListener('click', () => {
        pdfInput.value = '';
        pdfPreview.innerHTML = '';
        this.attachPDF = false;
      });
    }
  }

  bindMenuList() {
    if (!this.menuList) return;

    this.menuList.addEventListener('click', (e) => {
      const li = e.target.closest('li');
      if (!li || !this.menuList.contains(li)) return;

      // limpiar selección previa
      this.menuList.querySelectorAll('.is-selected')
        .forEach(el => el.classList.remove('is-selected'));

      li.classList.add('is-selected');

      const { name, sku } = this.parseNameSkuFromText(li.textContent);

      this.menuList.hidden = true;
      if (this.menuBtn) this.menuBtn.setAttribute('aria-expanded', 'false');

      const params = new URLSearchParams(window.location.search);
      const sku_product = params.get('sku');

      window.location.href =
        `../../view/variations/index.php?sku=${encodeURIComponent(sku_product)}&sku_variation=${encodeURIComponent(sku)}`;
    });
  }

  bindHeaderAndButtons() {
    document.addEventListener('DOMContentLoaded', () => {
      if (window.headerAddProduct && typeof headerAddProduct.setCurrentHeader === 'function') {
        headerAddProduct.setCurrentHeader('variations');
      }
    });

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        if (nameInput.value.trim() !== "") {
          this.saveVariationDetails();
        } else {
          alert("Please add a name to the variation.");
        }
      });
    }

    if (addBtn) {
      addBtn.addEventListener('click', () => {
        this.addNewVariation();
      });
    }
  }

  initGroupSelectPlaceholder() {
    if (!groupSelect) return;
    groupSelect.innerHTML = `
      <option value="" disabled selected>Select a group</option>
      <option value="__create_group__">+ Create new group…</option>
    `;
  }

  // --- Lógica de guardado ---

  saveVariationDetails() {
    const params        = new URLSearchParams(window.location.search);
    const sku_product   = params.get('sku');
    const sku_variation = params.get('sku_variation');

    const parentUI      = document.getElementById('parent_variations');

    const name_pdf_artwork      = document.getElementById('name_pdf_artwork');
    const group      = document.getElementById('group');

    const sku_parent_variation = this.getSkuParentId(parentUI);
    const imageFile     = this.getSelectedImageFile(imgInput);
    const pdfFile       = this.getSelectedPdfFile(pdfInput);


    const fd = new FormData();
    fd.append('action',        'save_variation_details');
    fd.append('sku_product',   sku_product   || '');
    fd.append('sku_variation', sku_variation || '');
    fd.append('isAttachAnImage', this.attachImage ? '1' : '0');
    fd.append('isAttachAPDF',   this.attachPDF   ? '1' : '0');
    fd.append('name',          (nameInput?.value || '').trim());
    fd.append('name_pdf_artwork',          (name_pdf_artwork?.value || '').trim());
    fd.append('group',          (group?.value || '').trim());

    if (sku_parent_variation) {
      fd.append('sku_parent_variation', sku_parent_variation);
    }

    // NUEVO: enviamos el group si no es la opción de crear
    if (groupSelect && groupSelect.value && groupSelect.value !== '__create_group__') {
      fd.append('group', groupSelect.value);
    }

    if (imageFile) fd.append('imageFile', imageFile);
    if (pdfFile)   fd.append('pdfFile',   pdfFile);

    const url = "../../controller/products/variations.php";

    fetch(url, {
      method: "POST",
      headers: {
        "X-Requested-With": "XMLHttpRequest"
      },
      body: fd
    })
      .then(r => {
        if (!r.ok) throw new Error("Network error.");
        return r.json();
      })
      .then(data => {
        if (data?.success) {
          if (window.headerAddProduct && typeof headerAddProduct.goNext === 'function') {
            headerAddProduct.goNext('../../view/images/index.php');
          }
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

  getSkuParentId(parentEl) {
    if (!parentEl) return null;

    // Caso SELECT
    if (parentEl.tagName === 'SELECT') {
      const opt = parentEl.selectedOptions && parentEl.selectedOptions[0];
      if (!opt) return null;
      if (opt.dataset && opt.dataset.sku) return opt.dataset.sku.trim();
      if (opt.value && !/\s|\|/.test(opt.value)) return opt.value.trim();
      return extractSkuFromText(opt.textContent || '');
    }

    // Caso LISTA (UL/OL)
    const li = parentEl.querySelector('.is-selected');
    if (!li) return null;
    if (li.dataset && li.dataset.sku) return li.dataset.sku.trim();
    return extractSkuFromText(li.textContent || '');
  }

  getSelectedImageFile(inputEl) {
    const f = inputEl?.files?.[0] || null;
    if (!f) return null;
    if (!f.type.startsWith('image/')) return null;
    return f;
  }

  getSelectedPdfFile(inputEl) {
    const f = inputEl?.files?.[0] || null;
    if (!f) return null;
    if (f.type !== 'application/pdf') return null;
    return f;
  }

  // --- Aux SKU & textos ---

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

  // --- Creación y carga de variación ---

  addNewVariation() {
    const params = new URLSearchParams(window.location.search);
    const sku = params.get('sku');

    const url = "../../controller/products/variations.php";
    const data = {
      action: "create_new_variation",
      sku: sku
    };

    fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data)
    })
      .then(response => {
        if (response.ok) {
          return response.text();
        }
        throw new Error("Network error.");
      })
      .then(data => {
        const json = JSON.parse(data);
        const sku_variation = json["sku_variation"];

        if (json["success"]) {
          alert("The new variation has been successfully created. Please fill in the details and save once you’ve finished.");
          window.location.href =
            `../../view/variations/index.php?sku=${encodeURIComponent(sku)}&sku_variation=${encodeURIComponent(sku_variation)}`;
        }
      })
      .catch(error => {
        console.error("Error:", error);
      });
  }

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
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data)
    })
      .then(response => {
        if (response.ok) {
          return response.text();
        }
        throw new Error("Network error.");
      })
      .then(data => {
        const json = JSON.parse(data);
      //  alert(json["groups_by_product"][1]);

        if (json["success"]) {
          this.drawParentsVariationItems(json["variations"], json["product"], json["current"]);
          this.drawImageVariationSelected(json["current"]["image"]);
          this.setPdfPreview(json["current"]["pdf_artwork"], json["current"]["name_pdf_artwork"]);
          this.setImagePreview(json["current"]["image"]);
          this.renderMenuTop(json["variations"]);
          this.drawItemsGroup(json["groups_by_product"], json["current"]["group"]);
          this.selectCurrentVariation(json["variations"], json["product"], json["current"], json["parent"]);
        }
      })
      .catch(error => {
        console.error("Error:", error);
      });
  }

   drawItemsGroup(groups_by_product, currentGroup) {
    // groups_by_product viene como array de strings: ['Group A', 'Group B', ...]
    if (!groupSelect || !Array.isArray(groups_by_product)) return;

    const createOpt = groupSelect.querySelector('option[value="__create_group__"]');
    if (!createOpt) return;

    // Limpiar opciones anteriores creadas dinámicamente
    groupSelect
      .querySelectorAll('option[data-source="group_list"]')
      .forEach(opt => opt.remove());

    // Recorrer el array y crear las opciones
    for (let i = 0; i < groups_by_product.length; i++) {
      const value = groups_by_product[i];
      const opt = document.createElement('option');
      opt.value = value;
      opt.textContent = value;
      opt.dataset.source = 'group_list'; // marca que viene de la lista de grupos
      groupSelect.insertBefore(opt, createOpt);
    }

    // Intentar seleccionar el grupo actual si existe en la lista
    if (currentGroup && groups_by_product.includes(currentGroup)) {
      groupSelect.value = currentGroup;
    } else {
      // No seleccionar nada
      groupSelect.value = ''; // asumiendo que tienes una opción placeholder con value=""
    }
  }



  selectCurrentVariation(dataVariations, dataProduct, dataCurrent, dataParent) {
    if (nameInput) {
      nameInput.value = dataCurrent["name"] || '';
    }

    if (dataCurrent["name"] === "Default") {
      alert(
        "1) If you won't add any variations, keep 'Default variation' selected. (Editing options for 'Default' are disabled.)\n" +
        "2) Click 'Save & Next'.\n" +
        "3) Add images, items, and prices.\n\n" +
        "— OR —\n" +
        "If you will use variations: click 'Add' and create your first-level variations with 'Parent variation' set to 'Default'."
      );

      if (parentSelect) parentSelect.disabled = true;
      if (nameInput)    nameInput.disabled   = true;
      if (imgInput)     imgInput.disabled    = true;
      if (pdfInput)     pdfInput.disabled    = true;
    }

    this.selectMenuCurrentItemBySku();
    this.selectParentVariationsItems(dataVariations, dataProduct, dataCurrent, dataParent);
  }

  selectParentVariationsItems(dataVariations = [], dataProduct = {}, dataCurrent = {}, dataParent = {}) {
    const sel = this.parentSelect || document.getElementById('parent_variations');
    if (!sel) return;

    const target = (dataParent && dataParent.sku)
      ? dataParent.sku
      : dataProduct?.product_sku;

    if (!target) return;

    const norm   = s => String(s || '').trim().toUpperCase();
    const wanted = norm(target);

    let matched = false;
    for (const opt of sel.options) {
      const byValue = norm(opt.value);
      const byData  = norm(opt.getAttribute('data-sku'));
      if ((byValue && byValue === wanted) || (byData && byData === wanted)) {
        opt.selected = true;
        sel.value = opt.value;
        matched = true;
        break;
      }
    }

    if (!matched) sel.selectedIndex = 0;
  }

  selectMenuCurrentItemBySku() {
    const currentUrl = new URL(window.location.href);
    const skuv = currentUrl.searchParams.get('sku_variation');
    if (!skuv) return false;

    const ul  = this.menuList || document.getElementById('menu_list');
    const btn = this.menuBtn  || document.getElementById('menu_btn');
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
    const ul = this.menuList || document.getElementById('menu_list');
    if (!ul) return;

    ul.innerHTML = '';
    if (!Array.isArray(items) || items.length === 0) {
      const li = document.createElement('li');
      li.textContent = 'No items to show';
      li.style.padding = '8px 10px';
      li.style.borderRadius = '10px';
      ul.appendChild(li);
      ul.hidden = false;
      return;
    }

    for (const it of items) {
      const name = it.name ?? '(unnamed)';
      const sku  = it.SKU ?? it.sku ?? '';

      const li = document.createElement('li');
      li.style.padding = '8px 10px';
      li.style.borderRadius = '10px';
      li.style.cursor = 'default';
      li.dataset.sku = sku;
      li.innerHTML = `<strong>${name}</strong>${sku ? ` <small style="color:var(--muted)">— ${sku}</small>` : ''}`;
      ul.appendChild(li);
    }
  }

  drawImageVariationSelected(urlCurrentImage) {
    if (!imgPreview) return;
    imgPreview.innerHTML = '';

    let url;
    if (urlCurrentImage && String(urlCurrentImage).trim() !== '') {
      const u = String(urlCurrentImage).trim();
      if (/^(https?:|data:|blob:)/i.test(u)) {
        url = u;
      } else {
        url = '../../' + u.replace(/^\/+/, '');
      }
    } else {
      url = '../../view/variations/images/add_image.png';
    }

    const img = new Image();
    img.alt = 'Selected variation image preview (icon)';
    img.loading = 'lazy';
    img.decoding = 'async';
    img.src = url;
    img.onerror = () => { img.src = '../../view/variations/images/add_image.png'; };

    imgPreview.appendChild(img);
  }

  setPdfPreview(url, namePDF) {

    const name_pdf_artwork = document.getElementById("name_pdf_artwork");
    name_pdf_artwork.value = namePDF;


    if (!pdfPreview) return;

    const u = (url || '').trim();
    if (!u) {
      pdfPreview.textContent = '';
      return;
    }

    const href = (/^(https?:)?\/\//.test(u) || u.startsWith('/'))
      ? u
      : '/' + u.replace(/^\/+/, '');

    pdfPreview.innerHTML = `<a href="../..${href}" download="artwork.pdf">artwork.pdf</a>`;
  }

  setImagePreview(url) {
    if (!imgPreview) return;

    const u = (url || '').trim();
    if (!u) { imgPreview.textContent = ''; return; }

    const href = (/^(https?:)?\/\//.test(u) || u.startsWith('/'))
      ? u
      : '/' + u.replace(/^\/+/, '');

    imgPreview.innerHTML =
      `<img alt="Selected variation image preview (icon)" loading="lazy" decoding="async" src="../..${href}">`;
  }

  isAttachAnImage(attachImage){
    this.attachImage = !!attachImage;
  }

  isAttachAPDF(attachPDF){
    this.attachPDF = !!attachPDF;
  }

  drawParentsVariationItems(dataVariations = [], dataProduct = {}, dataCurrent = {}) {
    const sel = this.parentSelect || document.getElementById('parent_variations');
    if (!sel) return;

    sel.innerHTML = '<option value="" disabled selected>Select a parent</option>';

    for (const row of (Array.isArray(dataVariations) ? dataVariations : [])) {
      const sku  = row?.SKU ?? row?.sku ?? '';
      if (!sku || sku === (dataCurrent?.sku ?? '')) continue;

      const name = row?.name ?? '(unnamed variation)';
      const opt = document.createElement('option');
      opt.value = sku;
      opt.dataset.sku = sku;
      opt.textContent = `${name} — ${sku}`;
      sel.appendChild(opt);
    }

    // 👇 Aquí ya no se añade "+ Create new group…"
    // porque eso ahora vive en el <select id="group">
  }

}

// ===== Instanciamos la clase =====
const variationClass = new Variations();

// ===== Toggle del menú superior (Change variation) =====
if (menuBtn && menuList) {
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
}

// ===== Lógica del modal "Create group" ligada al <select id="group"> =====
let lastGroupValue = groupSelect ? (groupSelect.value || '') : '';

if (groupSelect) {
  groupSelect.addEventListener('focus', () => {
    lastGroupValue = groupSelect.value || '';
  });

  groupSelect.addEventListener('change', (e) => {
    if (e.target.value === '__create_group__') {
      openGroupModal();
      // volvemos al valor anterior, no dejamos "__create_group__" seleccionado
      groupSelect.value = lastGroupValue || '';
    }
  });
}

function openGroupModal() {
  if (!groupModal) return;
  groupModal.hidden = false;
  groupNameInput.value = '';
  groupNameInput.focus();
}

function closeGroupModal() {
  if (!groupModal) return;
  groupModal.hidden = true;
}

function createGroup() {
  const name = groupNameInput.value.trim();

  if (!name) {
    alert('Please enter a group name.');
    groupNameInput.focus();
    return;
  }

  // Aquí más adelante puedes hacer fetch(...) para guardarlo en la BD.
  // De momento solo mostramos un alert y, opcionalmente, añadimos el grupo al select.

  makeAjaxRequestUpdateGroup(name);
  alert(`The group "${name}" has been created.`);

  if (groupSelect) {
    const opt = document.createElement('option');
    opt.value = name;
    opt.textContent = name;
    groupSelect.insertBefore(opt, groupSelect.querySelector('option[value="__create_group__"]'));
    groupSelect.value = name;
  }
  closeGroupModal();
}

function makeAjaxRequestUpdateGroup(group_name){
  const params = new URLSearchParams(window.location.search);
  const sku_variation = params.get('sku_variation');

  const url = "../../controller/products/variations.php";
  const data = {
    action: "update_group_name",
    sku_variation: sku_variation,
    group_name: group_name
  };

  fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(data)
  })
    .then(response => {
      if (response.ok) {
        return response.text();
      }
      throw new Error("Network error.");
    })
    .then(data => {
      alert(data);
      const json = JSON.parse(data);

      if (json["success"]) {

      }
    })
    .catch(error => {
      console.error("Error:", error);
    });
}

// Eventos de botones del modal
if (groupCreateBtn) {
  groupCreateBtn.addEventListener('click', createGroup);
}

if (groupCancelBtn) {
  groupCancelBtn.addEventListener('click', closeGroupModal);
}

// Cerrar modal haciendo clic en el fondo oscuro
if (groupModal) {
  groupModal.addEventListener('click', (e) => {
    if (e.target === groupModal) {
      closeGroupModal();
    }
  });
}

// Cerrar modal con Escape
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && groupModal && !groupModal.hidden) {
    closeGroupModal();
  }
});
