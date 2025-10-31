<?php
$cssTime = filemtime('../../view/preview_porduct/preview_porduct/preview.css');
$jsTime  = filemtime('../../view/preview_porduct/preview_porduct/preview.js');
?>
<link rel="stylesheet" href="../../view/preview_porduct/preview_porduct/preview.css?v=<?= $cssTime ?>">

<main class="sp-amz" aria-labelledby="sp-title">
  <!-- Breadcrumb -->
  <nav aria-label="Breadcrumb" class="sp-breadcrumbs">
    <ol id="sp_breadcrumbs" class="crumbs"></ol>
  </nav>

  <section class="sp-grid">
    <!-- Col 1: Galería -->
    <aside class="sp-col sp-gallery" aria-label="Product images">
      <div class="sp-thumbs" id="sp_thumbs" role="list"></div>
      <div class="sp-main" id="sp_main" aria-live="polite">
        <div class="cp-empty">No images</div>
      </div>
      <small class="cp-hint">Hover to zoom</small>
    </aside>

    <!-- Col 2: Detalles -->
    <section class="sp-col sp-details">
      <h1 id="sp-title" class="sp-title">—</h1>
      <div class="sp-meta">
        <a href="#" id="sp-brand" class="brand">Brand</a>
        <div class="rating" aria-label="4.7 out of 5">
          <span class="stars">★★★★☆</span>
          <a href="#" class="reviews-count">1,024 ratings</a>
        </div>
        <span id="sp_sku" class="sku">SKU: —</span>
      </div>

      <!-- Precio “desde” -->
      <div class="sp-price-from">
        <span class="muted">From</span>
        <strong id="sp_from">—</strong>
        <small id="sp_currency" class="cp-hint"></small>
      </div>

      <!-- Variación + qty inline (estilo Amazon) -->
      <div class="sp-config">
        <div class="cfg-row">
          <label class="cfg-label" for="sp_variation">Variation</label>
          <select id="sp_variation" class="cp-select"></select>
        </div>
        <div class="cfg-row">
          <label class="cfg-label" for="sp_qty">Qty</label>
          <input id="sp_qty" type="number" min="1" step="1" value="100">
        </div>
      </div>

      <!-- Bullets -->
      <ul id="sp_features" class="sp-bullets"></ul>

      <!-- Descripción -->
      <div class="sp-desc">
        <h3>Product description</h3>
        <p id="sp_desc">—</p>
      </div>

      <!-- Tabla de tramos (acordeón simple) -->
      <details class="sp-tiers">
        <summary>Price tiers</summary>
        <table class="cp-table">
          <thead><tr><th>Min</th><th>Max</th><th>Unit price</th></tr></thead>
          <tbody id="sp_tiers"></tbody>
        </table>
      </details>

      <!-- Especificaciones (mock) -->
      <details class="sp-specs">
        <summary>Product information</summary>
        <table class="sp-specs-table">
          <tbody id="sp_specs">
            <!-- Rellena si tu backend expone specs; aquí dejamos placeholders -->
          </tbody>
        </table>
      </details>
    </section>

    <!-- Col 3: Buy box -->
    <aside class="sp-col sp-buybox" aria-label="Purchase options">
      <div class="box">
        <div class="price-line">
          <span class="label">Unit</span>
          <strong id="bb_unit">—</strong>
        </div>
        <div class="price-line">
          <span class="label">Total</span>
          <strong id="bb_total">—</strong>
        </div>

        <div class="ship">
          <span>Delivery</span>
          <small>Preview to Bogotá, Colombia</small>
        </div>

        <div class="stock in">In stock</div>

        <button type="button" class="btn btn-primary btn-buy" id="bb_add">Add to cart (preview)</button>
        <button type="button" class="btn btn-ghost btn-buy" id="bb_buy">Buy now (disabled)</button>

        <div class="seller">
          <small>Sold by <a href="#" id="bb_seller">Promoflow Seller</a></small>
          <small>Returns: 30-day return (preview)</small>
        </div>
      </div>

      <div class="box slim">
        <button type="button" class="btn">Add to Wish List</button>
      </div>
    </aside>
  </section>
</main>

<script src="../../view/preview_porduct/preview_porduct/preview.js?v=<?= $jsTime ?>"></script>
