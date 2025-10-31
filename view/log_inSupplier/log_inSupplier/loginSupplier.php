
<?php
$cssTime = filemtime('../../view/log_inSupplier/log_inSupplier/loginSupplier.css'); // ejemplo: '../Home/5.Video/video.css'
$jsTime = filemtime('../../view/log_inSupplier/log_inSupplier/loginSupplier.js');   // ejemplo: '../Home/5.Video/video.js'
?>
<link rel="stylesheet" href="../../view/log_inSupplier/log_inSupplier/loginSupplier.css?v=<?= $cssTime ?>">
<main class="card" aria-labelledby="login-title">
  <h1 id="login-title">Log in</h1>

  <seccion id="loginForm">
    <input type="hidden" name="action" value="login">

    <div class="field">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" autocomplete="email" required
             placeholder="you@example.com" aria-describedby="email-help"/>
      <div id="email-help" class="help" aria-live="polite"></div>
    </div>

    <div class="field">
      <div class="row" style="margin-bottom:.35rem">
        <label for="password" style="margin:0">Password</label>
      </div>
      <div class="password-wrap">
        <input id="password" name="password" type="password" autocomplete="current-password" required
               minlength="6" placeholder="••••••••" aria-describedby="pass-help"/>
        <button type="button" class="toggle-pass" aria-label="Show password" aria-pressed="false" data-show="false">Show</button>
      </div>
      <a class="muted-link" href="#forgot">Forgot your password?</a>
      <div id="pass-help" class="help" aria-live="polite"></div>
    </div>

    <div class="actions">
      <button id="login_enter" class="btn" type="submit">Login</button>
    </div>

    <p class="footer">
       <a class="link" href="../../view/sign_up_supplier/index.php"> Don’t have an account? Sign up</a>
    </p>
  </seccion>
</main>

<script src="../../view/log_inSupplier/log_inSupplier/loginSupplier.js?v=<?= $jsTime ?>"></script>
