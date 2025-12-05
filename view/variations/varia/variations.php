<?php
$cssTime = filemtime('../../view/variations/varia/variations.css');
$jsTime  = filemtime('../../view/variations/varia/variations.js');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Variation</title>
  <link rel="stylesheet" href="../../view/variations/varia/variations.css?v=<?= $cssTime ?>">
</head>
<body>

<main class="create_product" aria-labelledby="var-title">
  <!--  <h1 id="var-title" class="sr-only">Create Product — Variations</h1> -->

  <!-- Tabs / Header -->
  <?php include "../../view/global/header_add_product/header_add_product.php"; ?>

  <section class="cp-card" aria-labelledby="cp-var-title">
    <header class="cp-card-header">
      <h2 id="cp-var-title">Create Variation</h2>

      <!-- Mini menú variaciones (derecha) -->
      <div class="cp-actions" style="gap:6px; position:relative;">
        <button class="btn btn-ghost" id="menu_btn" type="button" aria-haspopup="true" aria-expanded="false">
          Change variation ▾
        </button>
        <ul id="menu_list" class="cp-menu-list" hidden
            style="position:absolute; right:0; top:110%; list-style:none; margin:0; padding:6px; background:#fff; border:1px solid var(--border); border-radius:12px; box-shadow:var(--shadow); min-width:220px; max-height:260px; overflow:auto;">
          <!-- Se rellena dinámicamente -->
        </ul>
        <button class="btn btn-primary" id="add_variation" type="button" aria-label="Add variation">+ New variation</button>
      </div>
    </header>

    <form id="variationForm" class="cp-form" autocomplete="off" novalidate>
      <!-- NUEVO: Group -->
      <div class="cp-field">
        <label class="cp-label" for="group">Group</label>
        <select id="group" name="group" class="cp-select" aria-describedby="group_help">
          <!-- Se rellena en JS (placeholder + + Create new group…) -->
        </select>
        <small id="group_help" class="cp-hint">
          Select a variation group or create a new one.
        </small>
      </div>

      <!-- Parent variation (solo 1) -->
      <div class="cp-field">
        <label class="cp-label" for="parent_variations">Parent variation</label>
        <select id="parent_variations" name="parent_variation" class="cp-select" aria-describedby="parent_help">
          <!-- Se rellena dinámicamente -->
        </select>
        <small id="parent_help" class="cp-hint">Select exactly one parent variation.</small>
        <div id="parent_chips" class="cp-chips" aria-hidden="true"></div>
      </div>

      <!-- Variation name -->
      <div class="cp-field">
        <label class="cp-label" for="variation_name">Variation name</label>
        <input id="variation_name" name="variation_name" type="text" required placeholder="e.g., Polyester 20 mm – Green">
      </div>

      <!-- (Dejamos otro espacio en blanco para balancear la grid) -->
      <div class="cp-field"></div>

      <!-- Image -->
      <div class="cp-field cp-field-full">
        <label class="cp-label" for="variation_image">
          Image <small style="font-weight:400; color:var(--muted)">(icon-image)</small>
        </label>
        <div class="cp-inline-row">
          <input id="variation_image" name="variation_image" type="file" accept="image/*">
          <button type="button" class="btn" id="clear_image">Remove</button>
        </div>
        <small class="cp-hint">
          An icon-sized image is recommended (e.g., ≤ 128×128). The server can resize it.
        </small>
        <div class="cp-img-preview" id="img_preview" aria-live="polite"></div>
      </div>

      <!-- PDF (artwork) - columna izquierda -->
      <div class="cp-field">
        <label class="cp-label" for="variation_pdf">PDF artwork</label>
        <div class="cp-inline-row">
          <input id="variation_pdf" name="variation_pdf" type="file" accept="application/pdf">
          <button type="button" class="btn btn-ghost" id="clear_pdf">Remove</button>
        </div>
        <div class="cp-file-preview" id="pdf_preview" aria-live="polite">
          <span class="cp-file-pill">
            <span class="cp-file-pill-main">No file selected.</span>
          </span>
        </div>
      </div>

      <!-- NUEVO: Nombre del PDF (columna derecha) -->
      <div class="cp-field">
        <label class="cp-label" for="name_pdf_artwork">Artwork file name</label>
        <input
          id="name_pdf_artwork"
          name="name_pdf_artwork"
          type="text"
          placeholder="e.g., client-logo-2025.pdf"
        >
        <small class="cp-hint">
          Optional label for this artwork file. Visible in internal views.
        </small>
      </div>

    </form>
  </section>

  <!-- Modal para crear nuevo group -->
  <div id="group_modal" class="cp-modal" hidden>
    <div class="cp-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="group_modal_title">
      <h3 id="group_modal_title">Create group</h3>
      <p class="cp-modal-text">Name your new variation group.</p>

      <input type="text"
             id="group_name_input"
             class="cp-modal-input"
             placeholder="e.g., Width, Material, Colour">

      <div class="cp-modal-actions">
        <button type="button" class="btn btn-ghost" id="group_cancel_btn">Cancel</button>
        <button type="button" class="btn btn-primary" id="group_create_btn">Create</button>
      </div>
    </div>
  </div>

  <div class="cp-footer">
    <button class="btn btn-primary" id="next_variations" type="button">Save & Next</button>
  </div>
</main>

<script src="../../view/variations/varia/variations.js?v=<?= $jsTime ?>"></script>
</body>
</html>
