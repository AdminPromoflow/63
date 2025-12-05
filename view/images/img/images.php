<?php
$cssTime = filemtime('../../view/images/img/images.css');
$jsTime  = filemtime('../../view/images/img/images.js');
?>
<link rel="stylesheet" href="../../view/images/img/images.css?v=<?= $cssTime ?>">

<main class="create_product" aria-labelledby="vi-title">
  <h1 id="vi-title" class="sr-only">Create Product — Variation Images</h1>

  <!-- Tabs / Header -->
  <?php include "../../view/global/header_add_product/header_add_product.php" ?>

  <section class="cp-card" aria-labelledby="cp-vi-title">

    <header class="cp-card-header">
      <div>
        <h2 id="cp-vi-title">Images</h2>
        <p class="cp-subtitle">Upload and manage gallery images for this variation.</p>
      </div>

      <!-- Mini menú variaciones (derecha) -->
      <div class="cp-actions" style="gap:6px; position:relative;">
        <button class="btn btn-ghost" id="menu_btn" type="button" aria-haspopup="true" aria-expanded="false">
          Change variation ▾
        </button>
        <ul id="menu_list" class="cp-menu-list" hidden>
          <!-- Se rellena dinámicamente en JS -->
        </ul>
      </div>
    </header>


    <form id="variationImagesForm" class="cp-form" autocomplete="off" novalidate>
      <!-- ÚNICA SECCIÓN: Dropzone + Galería -->
      <div class="cp-field">


        <div id="dropzone" class="cp-dropzone" tabindex="0" aria-describedby="dz_help">
          <p><strong>Click to select</strong> or drag & drop images here</p>
          <input id="images_input" type="file" accept="image/*" multiple hidden>
          <button type="button" class="btn" id="pick_files">Select files</button>
        </div>

        <small id="dz_help" class="cp-hint">PNG, JPG, WebP. Up to 10MB each.</small>

        <!-- Gallery preview (thumbnails 80×80) -->
        <div id="gallery" class="cp-gallery" aria-live="polite" aria-relevant="additions removals"></div>
      </div>

      <!-- Actions -->
      <div class="cp-actions end">
      <!--  <button class="btn" type="button" id="reset_form">Reset</button> -->
        <button class="btn btn-primary" type="submit" id="save_images">Upload images</button>
      </div>

    </form>
  </section>

  <div class="cp-footer">
    <button class="btn btn-primary" id="next_images" type="button">Save & Next</button>
  </div>
</main>

<script src="../../view/images/img/images.js?v=<?= $jsTime ?>"></script>
