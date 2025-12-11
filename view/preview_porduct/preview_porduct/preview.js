// preview.js

class Preview {
  constructor() {
    this.main = document.getElementById("sp_main");

    if (!this.main) return;

    this.bindEvents();
  }

  // Solo manejamos el zoom sobre la imagen principal
  bindEvents() {
    // Pausar zoom / reset al entrar
    this.main.addEventListener("mouseenter", () => {
      const img = this.main.querySelector("img");
      if (img instanceof HTMLImageElement) {
        img.style.transformOrigin = "center center";
        img.style.transform = "scale(1)";
      }
    });

    // Resetear zoom cuando el mouse sale
    this.main.addEventListener("mouseleave", () => {
      const img = this.main.querySelector("img");
      if (img instanceof HTMLImageElement) {
        img.style.transformOrigin = "center center";
        img.style.transform = "scale(1)";
      }
    });

    // 🔍 Zoom real bajo el cursor (solo desktop)
    this.main.addEventListener("mousemove", (event) => {
      if (window.innerWidth <= 760) return;

      const img = this.main.querySelector("img");
      if (!(img instanceof HTMLImageElement)) {
        return;
      }

      const rect = this.main.getBoundingClientRect();
      const x = ((event.clientX - rect.left) / rect.width) * 100;
      const y = ((event.clientY - rect.top) / rect.height) * 100;

      img.style.transformOrigin = `${x}% ${y}%`;
      img.style.transform = "scale(2.1)";
    });
  }
}

/**
 * Divide dinámicamente los .var-group entre:
 * - .sp-variations (arriba, limitado por altura imagen grande + thumbs)
 * - .sp-variations-bottom (banda inferior, columnas 1 y 2)
 */
function setupVariationsSplit() {
  const main = document.querySelector(".sp-main");
  const topContainer = document.querySelector(".sp-variations");
  const bottomContainer = document.querySelector(".sp-variations-bottom");
  if (!main || !topContainer || !bottomContainer) return;

  // Subir todos los var-group al contenedor de arriba antes de recalcular
  const allGroups = [
    ...topContainer.querySelectorAll(".var-group"),
    ...bottomContainer.querySelectorAll(".var-group"),
  ];
  allGroups.forEach((group) => topContainer.appendChild(group));

  // En móvil: no dividimos, todo arriba
  if (window.innerWidth <= 760) {
    bottomContainer.style.display = "none";
    return;
  } else {
    bottomContainer.style.display = "grid";
  }

  // Altura máxima permitida = altura imagen grande + altura thumbs
  const mainRect = main.getBoundingClientRect();
  const thumbsEl = document.querySelector(".sp-thumbs");
  const thumbsRect = thumbsEl ? thumbsEl.getBoundingClientRect() : { height: 0 };

  const maxHeight = mainRect.height + thumbsRect.height;

  const styles = window.getComputedStyle(topContainer);
  const gap = parseFloat(styles.rowGap || styles.gap || "0") || 0;

  let accumulated = 0;
  let splitIndex = allGroups.length;

  allGroups.forEach((group, index) => {
    const rect = group.getBoundingClientRect();
    const h = rect.height;
    const extraGap = accumulated === 0 ? 0 : gap;

    if (accumulated + extraGap + h <= maxHeight) {
      accumulated += extraGap + h;
    } else if (splitIndex === allGroups.length) {
      splitIndex = index;
    }
  });

  if (splitIndex < allGroups.length) {
    const toMove = allGroups.slice(splitIndex);
    toMove.forEach((group) => bottomContainer.appendChild(group));
  }
}

/**
 * Animaciones al hacer scroll: fade-up y scale-in
 * Aplica .is-visible cuando los elementos entran al viewport.
 */
function setupScrollAnimations() {
  const fadeEls = document.querySelectorAll(".js-fade-up");
  const scaleEls = document.querySelectorAll(".js-scale-in");

  if (!fadeEls.length && !scaleEls.length) return;

  const observer = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          obs.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.15,
    }
  );

  fadeEls.forEach((el) => observer.observe(el));
  scaleEls.forEach((el) => observer.observe(el));
}

/**
 * Parallax suave en:
 * - .sp-main-wrapper (galería)
 * - .sp-buybox .box (buybox)
 */
function setupParallaxScroll() {
  const parallaxEls = document.querySelectorAll(".js-parallax");
  if (!parallaxEls.length) return;

  const update = () => {
    const scrollY = window.scrollY || window.pageYOffset;

    parallaxEls.forEach((el) => {
      // Solo en desktop grande para no marear en móvil
      if (window.innerWidth > 1120) {
        const factor = el.closest(".sp-buybox") ? 0.02 : 0.03;
        const offset = scrollY * factor;
        el.style.transform = `translateY(${offset}px)`;
      } else {
        el.style.transform = "";
      }
    });
  };

  window.addEventListener("scroll", update, { passive: true });
  window.addEventListener("resize", update);
  update();
}

/**
 * ✅ Selección de variantes:
 * - Al hacer click en una .var-option:
 *   - Se quita .is-selected de las hermanas dentro del mismo .var-group
 *   - Se añade .is-selected a la opción clicada
 *   - Se actualiza el <strong> del label con el texto de .opt-main (si existe)
 *   - Se recalcula el precio
 */
function setupVariationSelection() {
  const groups = document.querySelectorAll(".var-group");
  if (!groups.length) return;

  groups.forEach((group) => {
    const options = group.querySelectorAll(".var-option");
    if (!options.length) return;

    options.forEach((option) => {
      option.addEventListener("click", () => {
        // Quitar selección de todas dentro del grupo
        options.forEach((o) => o.classList.remove("is-selected"));

        // Marcar la opción clicada
        option.classList.add("is-selected");

        // Actualizar el texto del label (strong) con el opt-main
        const labelStrong = group.querySelector(".var-label strong");
        const mainSpan = option.querySelector(".opt-main");
        if (labelStrong && mainSpan) {
          labelStrong.textContent = mainSpan.textContent;
        }

        // Recalcular precios
        updatePrice();
      });
    });
  });
}

/**
 * Calcula y actualiza el precio en la buybox
 * según:
 * - Width
 * - Print side
 * - Clip type
 * - Pack size
 * - Opción de entrega (radio buttons)
 */
function updatePrice() {
  const unitEl = document.getElementById("bb_unit");
  const totalEl = document.getElementById("bb_total");
  if (!unitEl || !totalEl) return;

  // ==== 1. Leer variantes seleccionadas ====

  // Width
  let width = "20mm";
  const widthGroup = document.getElementById("sp_var_group_size_1");
  if (widthGroup) {
    const selected = widthGroup.querySelector(".var-option.is-selected") || widthGroup.querySelector(".var-option");
    if (selected) {
      const span = selected.querySelector(".opt-main");
      if (span) width = span.textContent.trim();
    }
  }

  // Pack size
  let packQty = 500;
  const packGroup = document.getElementById("sp_var_group_items");
  if (packGroup) {
    const selected = packGroup.querySelector(".var-option.is-selected") || packGroup.querySelector(".var-option");
    if (selected) {
      const span = selected.querySelector(".opt-main");
      if (span) {
        const txt = span.textContent.replace(/,/g, "").trim();
        const n = parseInt(txt, 10);
        if (!Number.isNaN(n) && n > 0) packQty = n;
      }
    }
  }

  // Print side
  let printName = "Double sided";
  const printGroup = Array.from(document.querySelectorAll(".var-group")).find((g) => {
    const nameEl = g.querySelector(".var-name");
    return nameEl && nameEl.textContent.trim().toLowerCase() === "print side";
  });
  if (printGroup) {
    const selected = printGroup.querySelector(".var-option.is-selected") || printGroup.querySelector(".var-option");
    if (selected) {
      const span = selected.querySelector(".opt-main");
      if (span) printName = span.textContent.trim();
    }
  }

  // Clip type
  let clipName = "Swivel hook";
  const clipGroup = Array.from(document.querySelectorAll(".var-group")).find((g) => {
    const nameEl = g.querySelector(".var-name");
    return nameEl && nameEl.textContent.trim().toLowerCase() === "clip type";
  });
  if (clipGroup) {
    const selected = clipGroup.querySelector(".var-option.is-selected") || clipGroup.querySelector(".var-option");
    if (selected) {
      const span = selected.querySelector(".opt-main");
      if (span) clipName = span.textContent.trim();
    }
  }

  // ==== 2. Calcular precio base según width + pack size ====

  const BASE_PER_100 = {
    "10mm": 7.0,
    "15mm": 7.5,
    "20mm": 8.0,
    "25mm": 8.8,
    "30mm": 9.4,
    "35mm": 10.2,
  };

  const PACK_ADJUST = {
    50: 0.05,
    100: 0.0,
    200: -0.03,
    500: -0.08,
    1000: -0.12,
    2000: -0.16,
    3000: -0.18,
    5000: -0.22,
  };

  const basePer100 = BASE_PER_100[width] || 8.0;
  const adjust = PACK_ADJUST[packQty] ?? 0;
  const packFactor = 1 + adjust;

  let baseTotal = basePer100 * (packQty / 100) * packFactor;

  // ==== 3. Ajustes por print side y clip ====

  let printFactor = 1;
  if (/single/i.test(printName)) {
    printFactor = 0.8;
  }

  let clipFactor = 1;
  if (/trigger/i.test(clipName)) {
    clipFactor = 1.05;
  } else if (/split/i.test(clipName)) {
    clipFactor = 1.02;
  }

  let subtotal = baseTotal * printFactor * clipFactor;

  // ==== 4. Ajuste por entrega (radio buttons) ====

  const deliveryRadio = document.querySelector('input[name="delivery_speed"]:checked');
  if (deliveryRadio) {
    const mode = deliveryRadio.dataset.mode;
    const val = parseFloat(deliveryRadio.dataset.value || "0") || 0;

    if (mode === "percent") {
      subtotal = subtotal * (1 + val);
    } else if (mode === "absolute") {
      subtotal = subtotal + val;
    }
  }

  // ==== 5. Calcular VAT y escribir en la UI ====

  const VAT_RATE = 0.2;
  const taxEl = document.getElementById("bb_tax");
  const taxAmount = subtotal * VAT_RATE;
  if (taxEl) {
    taxEl.textContent = `Estimated £${taxAmount.toFixed(2)}`;
  }

  const total = subtotal;
  const packQtySafe = packQty || 1;
  const unitPrice = total / packQtySafe;

  unitEl.textContent = `£${unitPrice.toFixed(2)}`;
  totalEl.textContent = `£${total.toFixed(2)}`;
}

/**
 * Vincula los radio buttons de entrega rápida con el cálculo de precio
 */
function setupDeliveryOptions() {
  const radios = document.querySelectorAll('input[name="delivery_speed"]');
  if (!radios.length) return;

  radios.forEach((radio) => {
    radio.addEventListener("change", () => {
      updatePrice();
    });
  });
}

// Instanciar al cargar
document.addEventListener("DOMContentLoaded", () => {
  new Preview();
  setupScrollAnimations();
  setupParallaxScroll();
  setupVariationSelection();
  setupDeliveryOptions();
  updatePrice();
});

// Esperamos a que cargue todo (imágenes) para medir bien alturas
window.addEventListener("load", () => {
  setupVariationsSplit();
});

// Recalcular al redimensionar
window.addEventListener("resize", () => {
  setupVariationsSplit();
});

document.addEventListener('DOMContentLoaded', () => {
  const backBtn = document.getElementById('btn_back_edit');
  const publishBtn = document.getElementById('btn_publish');

  if (backBtn) {
    backBtn.addEventListener('click', () => {
      const url = '../../view/product_details/index.php';

      const current = new URL(window.location.href);
      const dest    = new URL(url, current);

      const sku  = current.searchParams.get('sku');
      const skuv = current.searchParams.get('sku_variation');

      if (sku)  dest.searchParams.set('sku', sku);
      if (skuv) dest.searchParams.set('sku_variation', skuv);

      window.location.assign(dest);
    });
  }

  if (publishBtn) {
    publishBtn.addEventListener('click', () => {
      alert(
        'Your configuration will now be reviewed and then approved. ' +
        'This page is currently under construction.'
      );
    });
  }
});
