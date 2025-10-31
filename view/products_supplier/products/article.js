(function(){
  const rows  = Array.from(document.querySelectorAll('tbody tr.row-link'));
  const form  = document.getElementById('product-filters');
  const count = document.getElementById('product-count');
  const sortSelect = document.getElementById('sort-select');

  // Navegación por fila (click/Enter/Espacio)
  rows.forEach(r => {
    r.addEventListener('click', e => {
      if (e.target.closest('a, button')) return;
      const href = r.getAttribute('data-href');
      if (href) window.location.href = href;
    });
    r.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const href = r.getAttribute('data-href');
        if (href) window.location.href = href;
      }
    });
  });

  function applyFilters(){
    const data = new FormData(form);
    const q   = (data.get('q') || '').toString().trim().toLowerCase();
    const cat = (data.get('category') || '').toString();
    const sts = (data.get('status') || '').toString();

    let visible = 0;
    rows.forEach(tr => {
      const name = tr.dataset.name || '';
      const sku  = tr.dataset.sku || '';
      const c    = tr.dataset.category || '';
      const s    = tr.dataset.status || '';

      const passQ = !q || name.includes(q) || sku.includes(q);
      const passC = !cat || c === cat;
      const passS = !sts || s === sts;

      const show = passQ && passC && passS;
      tr.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    if (count) count.textContent = `${visible} product${visible===1?'':'s'}`;
  }

  function applySort(){
    const val = (sortSelect?.value) || 'updated-desc';
    const tbody = document.querySelector('.products__table tbody');
    if (!tbody) return;

    const getPrice = tr => parseFloat(tr.querySelector('td[data-price]')?.dataset.price || '0');
    const getDate  = tr => new Date(tr.querySelector('td[data-updated]')?.dataset.updated || '1970-01-01').getTime();
    const getName  = tr => (tr.dataset.name || '').toString();

    const sorted = [...rows].sort((a,b) => {
      if (val === 'price-asc')  return getPrice(a) - getPrice(b);
      if (val === 'price-desc') return getPrice(b) - getPrice(a);
      if (val === 'name-asc')   return getName(a).localeCompare(getName(b));
      return getDate(b) - getDate(a); // updated-desc default
    });

    sorted.forEach(tr => tbody.appendChild(tr));
    applyFilters();
  }

  if (form){
    form.addEventListener('submit', e => { e.preventDefault(); applyFilters(); });
    form.addEventListener('reset', () => setTimeout(applyFilters, 0));
  }
  sortSelect?.addEventListener('change', applySort);

  // Init
  applySort(); // ordena y luego aplica filtros
})();



class ProductsSupplierClass {
  constructor(){
    this.updateProductsSupplier();
  }
  updateProductsSupplier(){
    const url = "../../controller/products/product.php";
    const data = {
      action: "get_all_products_supplier"
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

        productsSupplierClass.drawProductsSupplier(data);


      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });
  }
  drawProductsSupplier(data){
  //  alert();
  }
}

const products__table = document.getElementById("products__table");
const productsSupplierClass = new ProductsSupplierClass();
