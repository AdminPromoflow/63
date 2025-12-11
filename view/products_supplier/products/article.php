<?php
$cssTime = filemtime('../../view/products_supplier/products/article.css');
$jsTime  = filemtime('../../view/products_supplier/products/article.js');
?>
<link rel="stylesheet" href="../../view/products_supplier/products/article.css?v=<?= $cssTime ?>">

<main class="dashboard_supplier" aria-labelledby="products-title">



  <!-- Toolbar: solo conteo y orden -->


  <!-- Filtros -->
  <section class="card products__filters" aria-labelledby="filters-title">
    <h2 id="filters-title" class="sr-only">Filters</h2>
    <form id="product-filters" class="products__filters-grid" autocomplete="off">
      <label class="products__field field field--withicon">
        <span>Name / SKU</span>
        <input type="search" name="q" placeholder="Search by name or SKU…" />
        <svg class="infield-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M21 21l-4.3-4.3M10.8 18.5a7.7 7.7 0 1 1 0-15.4 7.7 7.7 0 0 1 0 15.4z"
                fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
      </label>

      <label class="products__field field">
        <span>Category</span>
        <select name="category">
          <option value="">All</option>
          <option>Lanyards</option>
          <option>Accessories</option>
          <option>Clips</option>
        </select>
      </label>

      <label class="products__field field">
        <span>Status</span>
        <select name="status">
          <option value="">All</option>
          <option>Active</option>
          <option>Inactive</option>
          <option>Draft</option>
          <option>Archived</option>
        </select>
      </label>

      <div class="products__field products__actions">
        <button class="btn" type="reset">Reset</button>
        <button class="btn btn-primary" type="submit">Apply</button>
      </div>
    </form>
  </section>

  <!-- Listado: SOLO TABLA -->
  <section class="card table-card" aria-labelledby="product-list-title">
    <div class="card-header sticky-blur">
      <h2 id="product-list-title">Product List</h2>
    </div>

    <div class="table-wrap">
      <table class="table products__table" role="table">
        <thead>
          <tr>
            <th scope="col">SKU</th>
            <th scope="col">Product</th>
            <th scope="col">Category</th>
            <th scope="col" class="center">Status</th>
            <th scope="col" class="center">Action</th>
          </tr>
        </thead>

        <tbody id="products__table">
          <!-- Fila demo 1 -->
          <tr class="row-link"
              data-name="rpet lanyard 25mm (1-colour, double-sided)"
              data-sku="lyd-25mm-rpet"
              data-category="Lanyards"
              data-status="Active"
              tabindex="0"
              data-href="./product_edit.php?id=101">
            <td>LYD-25MM-RPET</td>
            <td>
              <div class="prod-name">RPET Lanyard 25mm (1-colour, double-sided)</div>
              <small class="muted">Stock: 320</small>
            </td>
            <td><span class="chip">Lanyards</span></td>
            <td class="center"><span class="badge badge-success"><i></i>Active</span></td>
            <td class="center"><a class="btn btn-small" href="./product_edit.php?id=101">Edit</a></td>
          </tr>

          <!-- Fila demo 2 -->
          <tr class="row-link"
              data-name="badge holder a7 clear"
              data-sku="badge-hold-a7-cl"
              data-category="Accessories"
              data-status="Active"
              tabindex="0"
              data-href="./product_edit.php?id=102">
            <td>BADGE-HOLD-A7-CL</td>
            <td>
              <div class="prod-name">Badge Holder A7 Clear</div>
              <small class="muted">Stock: 2100</small>
            </td>
            <td><span class="chip">Accessories</span></td>
            <td class="center"><span class="badge badge-success"><i></i>Active</span></td>
            <td class="center"><a class="btn btn-small" href="./product_edit.php?id=102">Edit</a></td>
          </tr>

          <!-- Fila demo 3 -->
          <tr class="row-link"
              data-name="standard dog clip"
              data-sku="clip-dog-std"
              data-category="Clips"
              data-status="Draft"
              tabindex="0"
              data-href="./product_edit.php?id=103">
            <td>CLIP-DOG-STD</td>
            <td>
              <div class="prod-name">Standard Dog Clip</div>
              <small class="muted">Out of stock</small>
            </td>
            <td><span class="chip">Clips</span></td>
            <td class="center"><span class="badge badge-warning"><i></i>Draft</span></td>
            <td class="center"><a class="btn btn-small" href="./product_edit.php?id=103">Edit</a></td>
          </tr>

          <!-- Fila demo 4 -->
          <tr class="row-link"
              data-name="polyester lanyard 20mm (1-colour)"
              data-sku="lyd-20mm-poly"
              data-category="Lanyards"
              data-status="Archived"
              tabindex="0"
              data-href="./product_edit.php?id=104">
            <td>LYD-20MM-POLY</td>
            <td>
              <div class="prod-name">Polyester Lanyard 20mm (1-colour)</div>
              <small class="muted">Out of stock</small>
            </td>
            <td><span class="chip">Lanyards</span></td>
            <td class="center"><span class="badge badge-info"><i></i>Archived</span></td>
            <td class="center"><a class="btn btn-small" href="./product_edit.php?id=104">Edit</a></td>
          </tr>
        </tbody>
      </table>
    </div>

    <nav class="products__pagination" aria-label="Pagination">
      <button class="btn" disabled>Prev</button>
      <button class="btn">Next</button>
    </nav>
  </section>
</main>

<script src="../../view/products_supplier/products/article.js?v=<?= $jsTime ?>"></script>
