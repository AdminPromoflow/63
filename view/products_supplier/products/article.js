// ✅ Tabla dinámica + filtros/orden funcionando aunque las filas se carguen por fetch
(function () {
  const form = document.getElementById("product-filters");
  const count = document.getElementById("product-count");
  const sortSelect = document.getElementById("sort-select");

  function getRows() {
    return Array.from(document.querySelectorAll("tbody#products__table tr.row-link"));
  }

  // ✅ Delegación: sirve para filas que se agregan después (no se “pierde”)
  document.addEventListener("click", (e) => {
    const tr = e.target.closest("tr.row-link");
    if (!tr) return;
    if (e.target.closest("a, button")) return;
    const href = tr.getAttribute("data-href");
    if (href) window.location.href = href;
  });

  document.addEventListener("keydown", (e) => {
    const tr = e.target.closest("tr.row-link");
    if (!tr) return;
    if (e.key !== "Enter" && e.key !== " ") return;
    e.preventDefault();
    const href = tr.getAttribute("data-href");
    if (href) window.location.href = href;
  });

  function applyFilters() {
    const rows = getRows();
    if (!form) return;

    const data = new FormData(form);
    const q = (data.get("q") || "").toString().trim().toLowerCase();
    const cat = (data.get("category") || "").toString();
    const sts = (data.get("status") || "").toString();

    let visible = 0;
    rows.forEach((tr) => {
      const name = (tr.dataset.name || "").toLowerCase();
      const sku = (tr.dataset.sku || "").toLowerCase();
      const c = tr.dataset.category || "";
      const s = tr.dataset.status || "";

      const passQ = !q || name.includes(q) || sku.includes(q);
      const passC = !cat || c === cat;
      const passS = !sts || s === sts;

      const show = passQ && passC && passS;
      tr.style.display = show ? "" : "none";
      if (show) visible++;
    });

    if (count) count.textContent = `${visible} product${visible === 1 ? "" : "s"}`;
  }

  function applySort() {
    const rows = getRows();
    const tbody = document.querySelector(".products__table tbody#products__table");
    if (!tbody) return;

    const val = sortSelect?.value || "name-asc";
    const getName = (tr) => (tr.dataset.name || "").toString();
    const getPrice = (tr) => parseFloat(tr.querySelector("td[data-price]")?.dataset.price || "0");

    const sorted = [...rows].sort((a, b) => {
      if (val === "price-asc") return getPrice(a) - getPrice(b);
      if (val === "price-desc") return getPrice(b) - getPrice(a);
      return getName(a).localeCompare(getName(b));
    });

    sorted.forEach((tr) => tbody.appendChild(tr));
    applyFilters();
  }

  // ✅ Expongo para que drawProductsSupplier pueda invocarlas después de render
  window.productsUI = { applyFilters, applySort };

  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      applyFilters();
    });
    form.addEventListener("reset", () => setTimeout(applyFilters, 0));
  }
  sortSelect?.addEventListener("change", applySort);

  // Init (por si hay filas demo)
  applySort();
})();

class ProductsSupplierClass {
  constructor() {
    this.updateProductsSupplier();
  }

  updateProductsSupplier() {
    const url = "../../controller/products/product.php";
    const payload = { action: "get_all_products_supplier" };

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })
      .then((response) => {
        if (response.ok) return response.text();
        throw new Error("Network error.");
      })
      .then((txt) => {
    //    alert(txt);
        const res = JSON.parse(txt);

        // ✅ res esperado: { success:true, data:[...] }
        this.drawProductsSupplier(res?.data || []);

        // ✅ re-aplica orden/filtros con las nuevas filas
        window.productsUI?.applySort?.();
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  }

  drawProductsSupplier(list) {
    const tbody = document.getElementById("products__table");
    if (!tbody) return;

    tbody.innerHTML = "";

    if (!Array.isArray(list) || list.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="5">
            <div style="padding:12px; color: var(--muted);">No products found.</div>
          </td>
        </tr>
      `;
      window.productsUI?.applyFilters?.();
      return;
    }

    for (let i = 0; i < list.length; i++) {
      const p = list[i] || {};

      const sku = (p.sku || "").toString();
      const skuVariation = (p.default_variation_sku || "").toString(); // ✅ nuevo
      const name = (p.product_name || "Untitled product").toString();
      const category = (p.category_name || "—").toString();
      const statusRaw = (p.status || "draft").toString().toLowerCase();

      const statusMap = {
        active:   { text: "Active",   cls: "badge-success" },
        draft:    { text: "Draft",    cls: "badge-warning" },
        inactive: { text: "Inactive", cls: "badge-info" },
        archived: { text: "Archived", cls: "badge-info" },
      };
      const st = statusMap[statusRaw] || { text: statusRaw || "Draft", cls: "badge-warning" };

      // ✅ URL EXACTA como pediste, con sku + sku_variation
      const href = `http://localhost/63/view/category/index.php?sku=${encodeURIComponent(sku)}&sku_variation=${encodeURIComponent(skuVariation)}`;

      tbody.innerHTML += `
        <tr class="row-link"
            data-name="${this._escAttr(name.toLowerCase())}"
            data-sku="${this._escAttr(sku.toLowerCase())}"
            data-category="${this._escAttr(category)}"
            data-status="${this._escAttr(st.text)}"
            tabindex="0"
            data-href="${href}">
          <td>${this._escHtml(sku)}</td>
          <td>
            <div class="prod-name">${this._escHtml(name)}</div>
            <small class="muted">—</small>
          </td>
          <td><span class="chip">${this._escHtml(category)}</span></td>
          <td class="center"><span class="badge ${st.cls}"><i></i>${this._escHtml(st.text)}</span></td>
          <td class="center"><a class="btn btn-small" href="${href}">Edit</a></td>
        </tr>
      `;
    }

    window.productsUI?.applyFilters?.();
  }

  // ✅ helpers mínimos para evitar romper HTML/attrs
  _escHtml(str) {
    return String(str ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }
  _escAttr(str) {
    return this._escHtml(str).replaceAll("`", "&#096;");
  }
}

const productsSupplierClass = new ProductsSupplierClass();
