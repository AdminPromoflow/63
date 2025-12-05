<link rel="stylesheet" href="<?php $u='../../view/global/header_add_product/header_add_product.css'; $f=realpath(__DIR__.'/'.$u); echo $u.'?v='.( $f && file_exists($f) ? filemtime($f) : time()); ?>">

<nav class="cp-tabs" role="tablist" aria-label="Create product steps">
  <a class="cp-tab" data-href="../../view/category/index.php" tabindex="0">category</a>
  <a class="cp-tab" data-href="../../view/product_details/index.php" tabindex="0">product details</a>
  <a class="cp-tab" data-href="../../view/variations/index.php" tabindex="0">variations</a>
  <a class="cp-tab" data-href="../../view/images/index.php" tabindex="0">images</a>
  <a class="cp-tab" data-href="../../view/items/index.php" tabindex="0">items</a>
  <a class="cp-tab" data-href="../../view/prices/index.php" tabindex="0">prices</a>
  <a class="cp-tab" data-href="../../view/preview_porduct/index.php" tabindex="0">preview_porduct</a>
</nav>

<script src="<?php $u='../../view/global/header_add_product/header_add_product.js'; $f=realpath(__DIR__.'/'.$u); echo $u.'?v='.( $f && file_exists($f) ? filemtime($f) : time()); ?>"></script>
