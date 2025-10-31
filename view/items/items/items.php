<?php
$cssTime = filemtime('../../view/items/items/items.css');
$jsTime  = filemtime('../../view/items/items/items.js');
?>
<link rel="stylesheet" href="../../view/items/items/items.css?v=<?= $cssTime ?>">

<main class="create_product" aria-labelledby="it-title">
  <h1 id="it-title" class="sr-only">Create Product — Items</h1>

  <!-- Tabs -->
  <?php include "../../view/global/header_add_product/header_add_product.php" ?>


  <section class="cp-card" aria-labelledby="cp-it-title">
    <header class="cp-card-header">
      <h2 id="cp-it-title">Variation Items</h2>
    </header>

    <form id="variationItemsForm" class="cp-form" autocomplete="off" novalidate>
      <!-- 1) Parent variations (primero) -->
      <div class="cp-field cp-field-full">
        <label class="cp-label" for="parent_variations">Parent variations</label>
        <select id="parent_variations" name="parent_variations[]" class="cp-select" multiple aria-describedby="parent_help">
          <!-- Poblar con tus datos reales -->
          <option value="1">Material</option>
          <option value="2">Colour</option>
          <option value="3">Width</option>
          <option value="4">Finish</option>
        </select>
        <small id="parent_help" class="cp-hint">Select one or more parent variations (Ctrl/Cmd-click on desktop).</small>
        <div id="parent_chips" class="cp-chips" aria-hidden="true"></div>
      </div>

      <!-- (Opcional) Variación hija concreta -->
      <div class="cp-field">
        <label class="cp-label" for="variation_child">Variation (child)</label>
        <select id="variation_child" name="variation_id">
          <option value="">Select variation…</option>
          <!-- Poblar dinámicamente si aplica -->
        </select>
        <small class="cp-hint">Optional: link items to a specific child variation.</small>
      </div>

      <!-- 2) Gestor de Items -->
      <div class="cp-field cp-field-full">
        <label class="cp-label">Items</label>

        <div class="cp-actions">
          <button type="button" class="btn" id="add_item">+ Add item</button>
        </div>

        <div id="items_list" class="cp-list" aria-live="polite" aria-relevant="additions removals"></div>

        <small class="cp-hint">Add short text snippets to be displayed to customers. You can mark one as highlight.</small>
      </div>

      <!-- Acciones -->
      <div class="cp-actions end cp-field-full">
        <button class="btn" type="button" id="reset_form">Reset</button>
        <button class="btn btn-primary" type="submit" id="save_items">Save items</button>
      </div>
    </form>
  </section>

  <div class="cp-footer">
    <button class="btn btn-primary" id="next_items" type="button">Next</button>
  </div>
</main>

<script src="../../view/items/items/items.js?v=<?= $jsTime ?>"></script>
