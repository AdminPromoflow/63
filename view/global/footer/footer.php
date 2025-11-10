
<?php
$cssTime = filemtime('../../'); // ejemplo: '../Home/5.Video/video.css'
$jsTime = filemtime('');   // ejemplo: '../Home/5.Video/video.js'
?>
<link rel="stylesheet" href="../../?v=<?= $cssTime ?>">
<!-- ===== HEADER PRINCIPAL DE LA PÁGINA ===== -->


</html>
