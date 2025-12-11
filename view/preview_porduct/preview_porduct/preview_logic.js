// preview_logic.js

class PreviewLogic {
  constructor() {
   this.currentImages = [];
   this.currentImageIndex = 0;

  // this.getDataProduct();
 }

 getDataProduct() {
   // 1) Obtener SKU desde la URL
   const params = new URLSearchParams(window.location.search);
   const sku = params.get("sku");

   //alert(sku);

   if (!sku) {
     console.warn("No SKU in URL");
     return;
   }

   const url = "../../controller/order/product.php";
   const data = {
     action: "get_preview_product_details",
     sku: sku
   };

   // 2) Petición al servidor
   fetch(url, {
     method: "POST",
     headers: { "Content-Type": "application/json" },
     body: JSON.stringify(data)
   })
     .then(response => {
       if (!response.ok) {
         throw new Error("Network error.");
       }
       return response.text();
     })
     .then(text => {
       let json;

       // 3) Parsear JSON con control de errores
       try {
         json = JSON.parse(text);
       } catch (e) {
         console.error("Invalid JSON:", e, text);
         return;
       }

       if (!Array.isArray(json)) {
         console.error("Unexpected JSON format:", json);
         return;
       }

       // Helper para encontrar bloque por clave
       const findBlock = (key) => json.find(obj => obj && obj[key]) || null;

       const supplierBlock   = findBlock("company_name");
       const categoryBlock   = findBlock("category_name");
       const productBlock    = findBlock("product_details");
       const variationsBlock = findBlock("Variations");

       const section_variations = document.getElementById("section_variations");
       if (section_variations) {
         section_variations.innerHTML = "";
       }
       alert(JSON.stringify(variationsBlock));


      // alert(JSON.stringify(variationsBlock.Variations.Default));
    /*  let groupNames = [];
       for (var i = 0; i < variationsBlock.Variations.Default.length; i++) {
         groupNames[i] = variationsBlock.Variations.Default[i].group;
       }

        groupNames = [...new Set(groupNames)];*/

        const list = variationsBlock?.Variations?.Default ?? [];

  // Lista de grupos sin repetir
  const groupNames = [...new Set(list.map(v => (v?.group || "UNGROUPED").trim()))];

  // Aquí guardaremos: [{ group: "WIDTH", items: [...] }, { group: "EXAMPLE", items: [...] }]
  let detailsByGroup = [];

  // 1) Inicializamos detailsByGroup con todos los grupos
  for (let j = 0; j < groupNames.length; j++) {
    detailsByGroup.push({
      group: groupNames[j],
      items: []
    });
  }

  // 2) Recorremos todas las variaciones y las metemos en su grupo correspondiente
  for (let i = 0; i < list.length; i++) {
    const v = list[i];
    const g = (v?.group || "UNGROUPED").trim();

    // Buscamos el objeto del grupo dentro de detailsByGroup
    const index = detailsByGroup.findIndex(x => x.group === g);

    // Si existe, agregamos la variación a su array
    if (index !== -1) {
      detailsByGroup[index].items.push(v);
    }
  }

  //console.log(detailsByGroup);






       // 4) Pintar UI
       this.drawHeaders(supplierBlock, categoryBlock, productBlock);
       this.drawProductDetails(productBlock);

       // 3) (Opcional) Mostrar cada grupo y su array
       for (let k = 0; k < detailsByGroup.length; k++) {
         this.drawVariationsFirstLevel(groupNames[k], detailsByGroup[k].items)
        // alert(JSON.stringify(detailsByGroup[k].items + " " + groupNames[k]));
       }


       ;

     })
     .catch(error => {
       console.error("Error fetching preview:", error);
       alert("Error loading preview data.");
     });
 }



  // productBlock: { product_details: { sku, product_name, description, descriptive_tagline, status } }
  drawProductDetails(productBlock) {
    if (!productBlock || !productBlock.product_details) return;

    const details = productBlock.product_details;

    const titleEl    = document.getElementById("sp-title");
    const subtitleEl = document.getElementById("sp_subtitle");
    const descEl     = document.getElementById("sp_desc");

    if (titleEl && details.product_name) {
      titleEl.textContent = details.product_name;
    }

    if (subtitleEl && details.descriptive_tagline) {
      subtitleEl.textContent = details.descriptive_tagline;
    }

    if (descEl && details.description) {
      descEl.textContent = details.description;
    }
  }


  // ===== Funciones vacías por ahora =====

  // supplierBlock:  { company_name: "Aleina" }
  // categoryBlock:  { category_name: "LANYARDS & ID ACCESSORIES" }
  // productBlock:   { product_details: { sku, product_name, description, descriptive_tagline, status } }
  drawHeaders(supplierBlock, categoryBlock, productBlock) {
    this.drawBreadcrumbs(supplierBlock, categoryBlock, productBlock);
    this.drawCategoryText(categoryBlock);
    this.drawBrandText(supplierBlock);
  }

  // 1) Breadcrumbs: empresa / categoría / producto
  drawBreadcrumbs(supplierBlock, categoryBlock, productBlock) {
    const breadcrumbs = document.getElementById("sp_breadcrumbs");
    if (!breadcrumbs) return;

    const companyName  = supplierBlock.company_name;
    const categoryName = categoryBlock.category_name;
    const productName  = productBlock.product_details.product_name;

    breadcrumbs.innerHTML = `
      <li><a href="#">${companyName}</a></li>
      <li><a href="#">${categoryName}</a></li>
      <li>${productName}</li>
    `;
  }

  // 2) Categoría en el span #sp_category
  drawCategoryText(categoryBlock) {
    const categoryEl = document.getElementById("sp_category");
    if (!categoryEl) return;

    categoryEl.textContent = categoryBlock.category_name;
  }

  // 3) Nombre de la empresa en #sp-brand
  drawBrandText(supplierBlock) {
    const brandEl = document.getElementById("sp-brand");
    if (!brandEl) return;

    brandEl.textContent = supplierBlock.company_name;
  }

  // variationsBlock: { sku, Variations: { Default: [...] } }
  /**
   * Dibuja el primer nivel de variaciones (ej: WIDTH) dentro de #section_variations
   * @param {string} group - Nombre del grupo (ej: "WIDTH")
   * @param {Array} variationsBlock - Array de variaciones
   * @param {number} selectedIndex - Índice seleccionado por defecto
   */
   drawVariationsFirstLevel(group, variationsBlock, selectedIndex = 0) {

  //   alert(JSON.stringify(variationsBlock))

     const section = document.getElementById("section_variations");
     if (!section) return;

     // ✅ NO sobrescribas: acumula/actualiza por group
     if (!Array.isArray(this.variationsFirstLevel)) this.variationsFirstLevel = [];
     this.variationsFirstLevel = this.variationsFirstLevel
       .filter(v => v?.group !== group)          // quita lo viejo de este grupo (si existía)
       .concat(variationsBlock || []);           // agrega lo nuevo

     const labelId = `var_label_size_${group}`;
     const idGroup = `sp_var_group_size_${group}`;

     section.innerHTML += `<div class="var-group" aria-labelledby="${labelId}">
       <div  class="var-label">
         <span class="var-name">${group}</span>
         <strong id="${labelId}">${variationsBlock?.[selectedIndex]?.name ?? ""}</strong>
       </div>

       <div class="var-options" id="${idGroup}">
       </div>
     </div>`;

     const sectionGroup = document.getElementById(idGroup);
     if (!sectionGroup) return;

     var imageButton = '';

     // ✅ si quieres mantener el 4to parámetro, pásalo seguro (JSON encode)
     const vbEnc = encodeURIComponent(JSON.stringify(variationsBlock || []));

     for (var i = 0; i < variationsBlock.length; i++) {

       imageButton = variationsBlock[i]?.details?.image
         ? `src=../../${variationsBlock[i].details.image}`
         : '';

       const selectedClass = i === selectedIndex ? " is-selected" : "";

       sectionGroup.innerHTML += `
         <button id="${variationsBlock[i].variation_id}"
           onclick="previewLogic.selectVariation('${variationsBlock[i].variation_id}', 'button_variation_${group}', ${i}, '${vbEnc}')"
           type="button"
           class="var-option js-scale-in button_variation_${group}${selectedClass}">
           <img class="var-thumb"
                ${imageButton}
                alt="Slim lanyard sample">
           <span class="opt-main">${variationsBlock[i].name}</span>
         </button>
       `;
     }
   }

   // Dentro de la clase PreviewLogic
   selectVariation(variationId, extraClass, indexSelected, variationsBlock) {

     // 1) Selección visual (solo dentro del grupo/clase que llega)
     const baseClass = (extraClass || "").split(" ")[0];

     // ✅ referencia al <strong> del grupo para actualizar el texto seleccionado
     let strongEl = null;

     if (baseClass) {
       const buttons = document.querySelectorAll("." + baseClass);
       buttons.forEach(b => b.classList.remove("is-selected"));
       buttons[indexSelected]?.classList.add("is-selected");

       // ✅ group sale de: "button_variation_${group}"
       const group = baseClass.replace(/^button_variation_/, "");
       strongEl = document.getElementById(`var_label_size_${group}`);
     }

     // 2) Lista base: primero lo que ya guardaste en drawVariationsFirstLevel
     let list = this.variationsFirstLevel;
     if (!Array.isArray(list) || list.length === 0) return;

     // 3) Buscar la variación
     const selected = list.find(v => String(v.variation_id) === String(variationId));
     if (!selected) return;

     // ✅ Actualiza el <strong> con el nombre seleccionado
     if (strongEl) strongEl.textContent = selected.name ?? "";

     // Solo llamamos si existen y tienen contenido
     if (Array.isArray(selected.images) && selected.images.length) {
       this.drawThumbImages(selected.images);
     }

     if (Array.isArray(selected.items) && selected.items.length) {
       this.drawItems(selected.items);
     }

     if (Array.isArray(selected.prices) && selected.prices.length) {
       this.drawPrices(selected.prices);
     }
   }



  drawPrices(prices) {
    const container = document.getElementById("sp_var_group_items");
    if (!container) return;

    // Limpiar el contenedor
    container.innerHTML = "";

    // Si no hay precios, mostrar un estado vacío sencillo
    if (!Array.isArray(prices) || prices.length === 0) {
      container.innerHTML = `
        <div class="var-empty">
          <span>No price bands available for this variation.</span>
        </div>
      `;
      return;
    }

    // Recorremos el array prices y generamos los botones
    for (let i = 0; i < prices.length; i++) {
      const p = prices[i];

      const minQ = Number(p.min_quantity ?? 0);
      const maxQ = p.max_quantity === null || typeof p.max_quantity === "undefined"
        ? null
        : Number(p.max_quantity);

      // Texto principal: rango de cantidades
      // 0–10, 11–20, 100+
      let mainValue = "";
      if (maxQ === null) {
        mainValue = `${minQ}+`;
      } else {
        mainValue = `${minQ}–${maxQ}`;
      }

      // Texto secundario: precio por unidad
      const unitPrice = Number(p.price ?? 0);
      // Si quieres siempre 2 decimales:
      const unitPriceText = unitPrice.toFixed(2);
      const subText = `From £${unitPriceText} each`;

      // Marcamos el primer tramo como seleccionado por defecto
      const isSelected = i === 0;
      const selectedClass = isSelected ? " is-selected" : "";

      container.innerHTML += `
        <button
          type="button"
          class="var-option js-scale-in${selectedClass}"
          data-price-id="${p.price_id}"
          data-min-qty="${minQ}"
          data-max-qty="${maxQ === null ? "" : maxQ}"
          data-unit-price="${unitPrice}"
        >
          <span class="opt-main">${mainValue}</span>
          <span class="opt-sub">${subText}</span>
        </button>
      `;
    }
  }



  // === Nueva función ===
  drawItems(items) {
    //alert(JSON.stringify(items));

    const section = document.getElementById("sp-items-note");
    if (!section) return;

    // 1) Limpiar el contenedor completo
    section.innerHTML = "";

    // Si no hay items, puedes dejar un mensaje simple opcional
    if (!Array.isArray(items) || items.length === 0) {
      section.innerHTML = `
        <ul class="sp-items-list">
          <li>
            <strong class="sp-item-subtitle">Items information</strong>
            <span>No extra information for this variation.</span>
          </li>
        </ul>
      `;
      return;
    }

    // 2) Crear el <ul> base
    section.innerHTML = `
      <ul class="sp-items-list"></ul>
    `;

    const ul = section.querySelector(".sp-items-list");
    if (!ul) return;

    // 3) Recorrer items y crear los <li>
    for (let i = 0; i < items.length; i++) {
      const item = items[i];
      const name = item.name || `Item ${i + 1}`;
      const desc = item.description || "";

      ul.innerHTML += `
        <li>
          <strong class="sp-item-subtitle">${name}</strong>
          <span>${desc}</span>
        </li>
      `;
    }
  }



// Miniaturas
// Dentro de la clase PreviewLogic

drawThumbImages(images) {
  const sp_thumbs = document.getElementById("sp_thumbs");
  if (!sp_thumbs) return;

  sp_thumbs.innerHTML = '';

  // Guardar estado de la galería
  this.currentImages = Array.isArray(images) ? images : [];
  this.currentImageIndex = 0;

  // Si no hay imágenes, dejamos un estado vacío
  if (!Array.isArray(images) || images.length === 0) {
    sp_thumbs.innerHTML = `
      <button type="button"
              class="sp-thumb js-scale-in"
              role="listitem"
              data-type="image"
              data-src="">
        <img src="https://via.placeholder.com/200x80?text=No+image"
             alt="No image available">
      </button>
    `;

    // También limpiamos la imagen principal
    this.changeMainImage('', 'No image available');
    return;
  }

  const BASE_PATH = "../../";

  for (let i = 0; i < images.length; i++) {
    const imgObj = images[i];
    const src    = BASE_PATH + imgObj.link;
    const alt    = `Product image ${i + 1}`;

    sp_thumbs.innerHTML += `
      <button type="button"
              class="sp-thumb js-scale-in"
              role="listitem"
              data-type="image"
              data-src="${src}"
              onclick="previewLogic.changeMainImage('${src}', '${alt}')">
        <img src="${src}"
             alt="${alt}">
      </button>
    `;
  }

  // Mostrar por defecto la primera imagen como principal
  const firstSrc = BASE_PATH + images[0].link;
  this.changeMainImage(firstSrc, "Product image 1");
}
nextImage() {
  const images = this.currentImages || [];
  if (!Array.isArray(images) || images.length === 0) return;

  // Avanzar índice con loop
  this.currentImageIndex = (this.currentImageIndex + 1) % images.length;

  const BASE_PATH = "../../";
  const imgObj = images[this.currentImageIndex];
  const src = BASE_PATH + imgObj.link;
  const alt = `Product image ${this.currentImageIndex + 1}`;

  this.changeMainImage(src, alt);
}

// Nueva función para cambiar la imagen principal
changeMainImage(src, altText = "Product image") {
  const sp_main = document.getElementById("sp_main");
  if (!sp_main) return;

  if (!src) {
    sp_main.innerHTML = '<div class="cp-empty">No media</div>';
    return;
  }

  sp_main.innerHTML = `
    <img src="${src}" alt="${altText}">
  `;
}







}

// Instancia simple
const previewLogic = new PreviewLogic();
