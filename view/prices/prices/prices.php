<?php
// Rutas absolutas (seguras)
$base = realpath(__DIR__ . '/../../view/prices/prices/');

// Si existen los archivos, crea un hash del contenido; si no, usa time()
$cssPath = $base . '/prices.css';
$jsPath  = $base . '/prices.js';

$cssVer = file_exists($cssPath) ? md5_file($cssPath) : time();
$jsVer  = file_exists($jsPath)  ? md5_file($jsPath) : time();
?>
<link rel="stylesheet" href="../../view/prices/prices/prices.css?v=<?= $cssVer ?>">

<main class="create_product" aria-labelledby="it-title">
  <h1 id="it-title" class="sr-only">Create Product — Items</h1>

  <!-- Tabs -->
  <?php include "../../view/global/header_add_product/header_add_product.php" ?>

  <section class="cp-card" aria-labelledby="cp-pr-title">
    <header class="cp-card-header">
      <h2 id="cp-pr-title">Prices</h2>

      <!-- Menú de variaciones (derecha) -->
      <div class="cp-actions" style="gap:6px; position:relative;">
        <button class="btn btn-ghost" id="menu_btn" type="button" aria-haspopup="true" aria-expanded="false">
          Change variation ▾
        </button>
        <ul id="menu_list" class="cp-menu-list" hidden
            style="position:absolute; right:0; top:110%; list-style:none; margin:0; padding:6px; background:#fff; border:1px solid var(--border); border-radius:12px; box-shadow:var(--shadow); min-width:240px; max-height:260px; overflow:auto;">
          <li>Loading…</li>
        </ul>
      </div>
    </header>

    <!-- Formulario principal -->
    <form id="variationPricesForm" class="cp-form" autocomplete="off" novalidate>
      <div class="cp-field cp-field-full">
        <div class="cp-actions">
          <button type="button" class="btn" id="add_price">+ Add price</button>
        </div>

        <!-- Lista dinámica de filas de precios -->
        <div id="prices_list" class="cp-list cp-list-onecol" aria-live="polite" aria-relevant="additions removals"></div>

        <small class="cp-hint">
          Add price rows. Each row spans the full width and includes only: Min qty, Max qty, Price.
        </small>
      </div>

      <div class="cp-actions end cp-field-full">
        <button class="btn btn-primary" type="submit" id="save_prices">Save prices</button>
      </div>
    </form>
  </section>

  <div class="cp-footer">
    <button class="btn btn-primary" id="next_prices" type="button">Next</button>
  </div>
</main>

<script src="../../view/prices/prices/prices.js?v=<?= $jsVer ?>" defer></script>
