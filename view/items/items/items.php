
<?php
// Rutas absolutas (seguras)
$base = realpath(__DIR__ . '/../../view/items/items/');

// Si existen los archivos, crea un hash del contenido; si no, usa time()
$cssPath = $base . '/items.css';
$jsPath  = $base . '/items.js';

$cssVer = file_exists($cssPath) ? md5_file($cssPath) : time();
$jsVer  = file_exists($jsPath)  ? md5_file($jsPath)  : time();
?>

<link rel="stylesheet" href="../../view/items/items/items.css?v=<?= $cssVer ?>">

<main class="create_product" aria-labelledby="it-title">
<!--  <h1 id="it-title" class="sr-only">Create Product — Items</h1>-->

  <!-- Tabs -->
  <?php include "../../view/global/header_add_product/header_add_product.php" ?>


  <section class="cp-card" aria-labelledby="cp-it-title">
    <header class="cp-card-header">

      <h2 id="cp-vi-title">Items</h2>

        <!-- Mini menú variaciones (derecha) -->
        <div class="cp-actions" style="gap:6px; position:relative;">
          <button class="btn btn-ghost" id="menu_btn" type="button" aria-haspopup="true" aria-expanded="false">
            Change variation ▾
          </button>
          <ul id="menu_list" class="cp-menu-list" hidden
              style="position:absolute; right:0; top:110%; list-style:none; margin:0; padding:6px; background:#fff; border:1px solid var(--border); border-radius:12px; box-shadow:var(--shadow); min-width:220px; max-height:260px; overflow:auto;">
          
          </ul>
        </div>

    </header>



    <form id="variationItemsForm" class="cp-form" autocomplete="off" novalidate>
      <!-- 1) Parent variations (primero) -->


      <!-- 2) Gestor de Items -->
      <div class="cp-field cp-field-full">

        <div class="cp-actions">
          <button type="button" class="btn" id="add_item">+ Add item</button>
        </div>

        <div id="items_list" class="cp-list" aria-live="polite" aria-relevant="additions removals"></div>

        <small class="cp-hint">Add short text snippets to be displayed to customers.</small>
      </div>

      <!-- Acciones -->
      <div class="cp-actions end cp-field-full">
        <!--<button class="btn" type="button" id="reset_form">Reset</button>-->
        <button class="btn btn-primary" type="submit" id="save_items">Save items</button>
      </div>
    </form>
  </section>

  <div class="cp-footer">
    <button class="btn btn-primary" id="next_items" type="button">Next</button>
  </div>
</main>

<script src="../../view/items/items/items.js?v=<?= $jsVer ?>" defer></script>
