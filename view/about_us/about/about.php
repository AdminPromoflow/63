
<?php
$cssTime = filemtime('../../view/about_us/about/about.css'); // ejemplo: '../Home/5.Video/video.css'
$jsTime = filemtime('../../view/about_us/about/about.js');   // ejemplo: '../Home/5.Video/video.js'
?>
<link rel="stylesheet" href="../../view/about_us/about/about.css?v=<?= $cssTime ?>">
<!-- ===== HEADER PRINCIPAL DE LA PÁGINA ===== -->
  <main>
    <!-- Hero -->
    <section class="hero" aria-labelledby="about-title">
      <h1 id="about-title">About Us</h1>
      <p>At <strong>.63</strong> Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
      <div class="cta-wrap">
        <a href="#values" class="btn primary">Learn our values</a>
      </div>
    </section>

    <!-- Stats -->
    <section class="stats" aria-label="Company highlights">
      <div class="stat"><b>12k+</b><span>orders delivered</span></div>
      <div class="stat"><b>96%</b><span>on‑time rate</span></div>
      <div class="stat"><b>4.9★</b><span>customer rating</span></div>
      <div class="stat"><b>60%</b><span>recycled inputs</span></div>
    </section>


<script src="../../view/about_us/about/about.js?v=<?= $jsTime ?>" type="text/javascript"></script>
