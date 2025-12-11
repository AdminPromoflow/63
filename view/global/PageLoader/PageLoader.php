<?php
$plCssPath = '../../view/global/PageLoader/PageLoader.css';
$plJsPath  = '../../view/global/PageLoader/PageLoader.js';

$plCssTime = is_file($plCssPath) ? filemtime($plCssPath) : time();
$plJsTime  = is_file($plJsPath)  ? filemtime($plJsPath)  : time();
?>
<link rel="stylesheet" href="<?= $plCssPath ?>?v=<?= $plCssTime ?>">
<div id="page-loader" class="loader" role="status" aria-live="polite" aria-busy="true">
  <div class="spin"></div>
  <span class="sr-only">Charging…</span>
</div>

<script src="<?= $plJsPath ?>?v=<?= $plJsTime ?>" type="text/javascript"></script>
