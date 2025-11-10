
<?php
$cssTime = filemtime('../../view/forg_password/password/password.css'); // ejemplo: '../Home/5.Video/video.css'
$jsTime = filemtime('../../view/forg_password/password/password.js');   // ejemplo: '../Home/5.Video/video.js'
?>
<link rel="stylesheet" href="../../view/forg_password/password/password.css?v=<?= $cssTime ?>">
<main class="card" aria-labelledby="login-title">
  <h1 id="login-title">Forgot you password</h1>

  <seccion id="loginForm">
    <input type="hidden" name="action" value="login">

    <div class="field">
      <label for="email">Your email</label>
      <input id="email" name="email" type="email" autocomplete="email" required
             placeholder="you@example.com" aria-describedby="email-help"/>
      <div id="email-help" class="help" aria-live="polite"></div>
    </div>

    <div class="field">

      <a class="muted-link" href="#forgot">Forgot your email?</a>
      <div id="pass-help" class="help" aria-live="polite"></div>
    </div>

    <div class="actions">
      <button id="login_enter" class="btn" type="submit">Send</button>
    </div>

    <p class="footer">
      Don’t have an account? <a class="link" href="#signup">Sign up</a>
    </p>
  </seccion>
</main>

<script src="../../view/forg_password/password/password.js?v=<?= $jsTime ?>"></script>
