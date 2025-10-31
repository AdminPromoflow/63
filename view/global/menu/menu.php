
<?php
$cssTime = filemtime('../../view/global/menu/menu.css'); // ejemplo: '../Home/5.Video/video.css'
$jsTime = filemtime('');   // ejemplo: '../Home/5.Video/video.js'
?>
<link rel="stylesheet" href="../../view/global/menu/menu.css?v=<?= $cssTime ?>">
<!-- ===== HEADER PRINCIPAL DE LA PÁGINA ===== -->
<!-- HEADER -->
<header class="site-header">
  <!-- Marca / logo -->
  <a class="brand" href="../../view/main/index.php" aria-label="Inicio">
    <h1 class="brand-text">.63</h1>
    <!-- Si quieres imagen, descomenta:
    <img src="../../view/login/menu/img/logo.png" alt="" class="brand-logo">
    -->
  </a>

  <!-- Toggle (checkbox) -->
  <input type="checkbox" id="nav-toggle" class="nav-toggle" hidden>

  <!-- Botón hamburguesa -->
  <label for="nav-toggle" class="burger" aria-label="Abrir menú" aria-controls="site-nav"></label>

  <!-- Navegación -->
  <nav id="site-nav" class="nav">
    <ul class="nav-list">
      <li><a href="../../view/product/index.php">Product</a></li>
      <li><a href="../../view/about_us/index.php">About Us</a></li>
      <li><a href="../../view/log_in/index.php">Login</a></li>
      <li><a class="cta" href="../../view/sign_up/index.php">Sign up</a></li>
    </ul>
  </nav>
</header>

</html>
