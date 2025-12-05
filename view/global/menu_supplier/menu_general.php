
<?php
$cssTime = filemtime('../../view/global/menu_supplier/menu_general.css'); // ejemplo: '../Home/5.Video/video.css'
$jsTime = filemtime('../../view/global/menu_supplier/menu_general.js');   // ejemplo: '../Home/5.Video/video.js'
?>
<link rel="stylesheet" href="../../view/global/menu_supplier/menu_general.css?v=<?= $cssTime ?>">
<!-- ===== HEADER PRINCIPAL DE LA PÁGINA ===== -->
<!-- HEADER -->
<header class="site-header">
  <!-- Marca / logo -->
  <a class="brand" href="../../view/dashboard_supplier/index.php" aria-label="Inicio">
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
      <li><a class="cta" id="logout" >Logout</a></li>
    </ul>
  </nav>

</header>
<script src="../../view/global/menu_supplier/menu_general.js?v=<?= $jsTime ?>" type="text/javascript"></script>
