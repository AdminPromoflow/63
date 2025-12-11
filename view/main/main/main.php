
<?php
$cssTime = filemtime('../../view/main/main/main.css'); // ejemplo: '../Home/5.Video/video.css'
$jsTime = filemtime('../../view/main/main/main.js');   // ejemplo: '../Home/5.Video/video.js'
?>
<link rel="stylesheet" href="../../view/main/main/main.css?v=<?= $cssTime ?>">
  <main class="main_main">
    <div class="title_main">
      <h1>Print it all with .63 - from eco bags to branded tech</h1><br>
      <a href="#" class="cta">Shop Now</a>

    </div>
    <div class="container_main">
      <div class="product_main">
        <img src="../../view/main/main/img/bags.png" alt="">
        <h1>Bags</h1>
      </div>
      <div class="product_main">
        <img src="../../view/main/main/img/USBs.png" alt="">
        <h1>Tech & USBs</h1>
      </div>
      <div class="product_main">
        <img src="../../view/main/main/img/accesories.png" alt="">
        <h1>Badges & Accesories</h1>
      </div>
      <div class="product_main">
        <img src="../../view/main/main/img/gift.png" alt="">
        <h1>Gift & Seasonal</h1>
      </div>
    </div>
  </main>

</html>
<script src="../../view/main/main/main.js?v=<?= $jsTime ?>" type="text/javascript"></script>
