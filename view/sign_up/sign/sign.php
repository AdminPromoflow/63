
<?php
$cssTime = filemtime('../../view/sign_up/sign/sign.css'); // ejemplo: '../Home/5.Video/video.css'
$jsTime = filemtime('../../view/sign_up/sign/sign.js');   // ejemplo: '../Home/5.Video/video.js'
?>
<link rel="stylesheet" href="../../view/sign_up/sign/sign.css?v=<?= $cssTime ?>">
<main class="card" aria-labelledby="login-title">
    <h1 id="login-title">Sign up</h1>

    <seccion>
      <div class="field">
        <label for="name_sign_up">Name</label>
        <input id="name_sign_up" name="email" type="email" autocomplete="email" required
               placeholder="Full name" />
        <div id="email-help" class="help" aria-live="polite"></div>
      </div>
      <div class="field">
        <label for="email_sign_up">Email</label>
        <input id="email_sign_up" name="email" type="email" autocomplete="email" required
               placeholder="you@example.com" />
        <div id="email-help" class="help" aria-live="polite"></div>
      </div>

      <div class="field">
        <div class="row" style="margin-bottom:.35rem">
          <label for="password_sign_up" style="margin:0">Password</label>
        </div>
        <div class="password-wrap">
          <input id="password_sign_up" name="password" type="password" autocomplete="current-password" required
                 minlength="6" placeholder="••••••••" />
          <button type="button" class="toggle-pass" aria-label="Show password" data-show="false">Show</button>
        </div>
        <a class="muted-link" href="#forgot">Forgot your password?</a>

        <div id="pass-help" class="help" aria-live="polite"></div>
      </div>

      <div class="actions">
        <button id="signup_enter" class="btn" type="submit">Sign Up</button>
      </div>

      <p class="footer">
        Don’t have an account?
        <a class="link" href="#signup">Sign up</a>
      </p>
    </seccion>
  </main>

<script src="../../view/sign_up/sign/sign.js?v=<?= $jsTime ?>" type="text/javascript"></script>
