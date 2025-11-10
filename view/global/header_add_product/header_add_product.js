// header_add_product.js
class HeaderAddProduct {
  constructor(root = '.cp-tabs') {

    const sku = new URLSearchParams(location.search).get('sku');

    const cp_tab = document.querySelectorAll(".cp-tab");

    for (var i = 0; i < cp_tab.length; i++) {
      cp_tab[i].addEventListener("click", function (e) {
        e.preventDefault();
        const href = this ? this.dataset.href : null;
        window.headerAddProduct.goNext(href);

      });
    }
  }

  goNext(url) {
    if (!url) return;

    const current = new URL(window.location.href);
    const dest    = new URL(url, current);

    const sku  = current.searchParams.get('sku');
    const skuv = current.searchParams.get('sku_variation');

    if (sku)  dest.searchParams.set('sku', sku);
    if (skuv) dest.searchParams.set('sku_variation', skuv);

    window.location.assign(dest);
  }


  setCurrentHeader(label) {
    // Asegura el root por si no quedó seteado
    this.root = this.root || document.querySelector('.cp-tabs');
    if (!this.root) return;

    const norm = s => (s || '').toLowerCase().replace(/\s+/g, ' ').trim();
    const wanted = norm(label);
    const tabs = this.root.querySelectorAll('a.cp-tab');

    let target = null;

    tabs.forEach(a => {
      // limpiar estado
      a.classList.remove('active');
      a.removeAttribute('aria-current');

      // elegir objetivo (por data-tab si existe; si no, por texto)
      const key = a.dataset.tab ? norm(a.dataset.tab) : norm(a.textContent);
      if (!target && key === wanted) target = a;
    });

    if (target) {
      target.classList.add('active');
      target.setAttribute('aria-current', 'page');
    }
  }

}

window.headerAddProduct = new HeaderAddProduct();
