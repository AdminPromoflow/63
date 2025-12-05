<?php
// Versionado de assets (ajusta las rutas según tu estructura)
$cssTime = filemtime('../../view/product_details/product_details/product_details.css'); // reutilizamos el mismo CSS base
$jsTime  = filemtime('../../view/product_details/product_details/product_details.js'); // nuevo JS para esta pantalla
?>
<link rel="stylesheet" href="../../view/product_details/product_details/product_details.css?v=<?= $cssTime ?>">

<!-- =============== Create Product: Product Details Tab =============== -->
<main class="create_product" aria-labelledby="pd-title">
  <h1 id="pd-title" class="sr-only">Create Product — Product Details</h1>

  <!-- Tabs (mismo panel) -->
  <!-- Tabs (promotions removed) -->
  <?php include "../../view/global/header_add_product/header_add_product.php" ?>


  <!-- Contenido -->
  <section class="cp-card" aria-labelledby="cp-pd-title">
    <header class="cp-card-header">
      <h2 id="cp-pd-title">Product details</h2>
    </header>

    <!-- Formulario de detalles -->
    <!-- Formulario de detalles -->
    <form id="productDetailsForm" class="cp-form" autocomplete="off" novalidate>
      <!-- Nombre -->
      <div class="cp-field">
        <label class="cp-label" for="pd_name">Name</label>
        <input
          id="pd_name"
          name="name"
          type="text"
          maxlength="150"
          placeholder="e.g., Premium Polyester Lanyard"
          aria-describedby="pd_name_help"
        />
        <small id="pd_name_help" class="cp-hint">Up to 150 characters.</small>
      </div>

      <!-- Status -->
      <div class="cp-field">
        <label class="cp-label" for="pd_status">Status</label>
        <select id="pd_status" name="status" aria-describedby="pd_status_help">
          <option value="">Select status…</option>
          <option value="draft">Draft</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="archived">Archived</option>
        </select>
        <small id="pd_status_help" class="cp-hint">Choose how the product should be treated in listings.</small>
      </div>

      <!-- Descriptive tagline (ocupa ancho completo) -->
      <div class="cp-field cp-field-full">
        <label class="cp-label" for="pd_tagline">Descriptive tagline</label>
        <input
          id="pd_tagline"
          name="descriptive_tagline"
          type="text"
          maxlength="160"
          placeholder="e.g., Ideal for events, offices and schools with full colour printing."
          aria-describedby="pd_tagline_help"
        />
        <small id="pd_tagline_help" class="cp-hint">
          Short tagline shown in product cards and listings. Up to 160 characters.
        </small>
      </div>

      <!-- Descripción (ocupa ancho completo) -->
      <div class="cp-field cp-field-full">
        <label class="cp-label" for="pd_desc">Description</label>
        <textarea
          id="pd_desc"
          name="description"
          rows="6"
          placeholder="Short, compelling summary of the product features and materials…"
          aria-describedby="pd_desc_help pd_desc_count"
        ></textarea>
        <div class="cp-field-row">
          <small id="pd_desc_help" class="cp-hint">Optional.</small>
          <small id="pd_desc_count" class="cp-hint">0 characters</small>
        </div>
      </div>
    </form>

  </section>

  <!-- Footer: Next -->
  <div class="cp-footer">
    <button class="btn btn-primary" id="next_product_details" type="button">Save & Next</button>

  </div>
</main>

<script src="../../view/product_details/product_details/product_details.js?v=<?= $jsTime ?>"></script>
