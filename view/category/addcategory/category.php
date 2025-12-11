<?php
$cssTime = filemtime('../../view/category/addcategory/category.css'); // ejemplo: '../Home/5.Video/video.css'
$jsTime  = filemtime('../../view/category/addcategory/category.js');   // ejemplo: '../Home/5.Video/video.js'
?>
<link rel="stylesheet" href="../../view/category/addcategory/category.css?v=<?= $cssTime ?>">

<!-- =============== Create Product: Category Tab (updated) =============== -->
<main class="create_product" aria-labelledby="cp-title">
  <h1 id="cp-title" class="sr-only">Create Product</h1>

  <?php include "../../view/global/header_add_product/header_add_product.php" ?>

  <!-- Content -->
  <section class="cp-card" aria-labelledby="cp-cat-title">
    <header class="cp-card-header">
      <div>
        <h2 id="cp-cat-title">Categories</h2>
        <p class="cp-subtitle">Choose an existing category or create a new one for this product.</p>
      </div>
      <!--  <div class="cp-actions">
        <input id="cp-search" class="cp-search" type="search" placeholder="Search category…" aria-label="Search category">
      </div>-->
    </header>

    <!-- Existing categories -->
    <div class="cp-cat-wrapper">
      <div class="cp-cat-grid" id="category_list" role="list">
        <div class="cp-cat" role="listitem">
          <span class="cp-cat-name">Lanyards</span>
          <small class="cp-cat-meta">12 products</small>
        </div>
        <div class="cp-cat" role="listitem">
          <span class="cp-cat-name">Badges</span>
          <small class="cp-cat-meta">7 products</small>
        </div>
        <div class="cp-cat" role="listitem">
          <span class="cp-cat-name">Wristbands</span>
          <small class="cp-cat-meta">3 products</small>
        </div>
      </div>

      <p class="cp-cat-hint">Click a category to assign it to this product.</p>
    </div>

    <hr class="cp-sep" aria-hidden="true">

    <!-- Create new category inline -->
    <form id="newCategoryForm" class="cp-inline-form" autocomplete="off" novalidate>
      <label class="cp-label" for="new_category">Add new category</label>
      <div class="cp-inline-row">
        <input id="new_category" name="name" type="text" required
               placeholder="Category name"
               aria-describedby="new_category_help">
        <button id="btn-create-new-category" class="btn btn-primary" type="button">Create</button>
      </div>
      <small id="new_category_help" class="cp-hint">
        If your category isn't listed, create a new one here.
      </small>
    </form>
  </section>

  <!-- Footer: Next button -->
  <div class="cp-footer">
    <button class="btn btn-primary" id="next_category" type="button">Save & Next</button>
  </div>
</main>

<script src="../../view/category/addcategory/category.js?v=<?= $jsTime ?>"></script>
