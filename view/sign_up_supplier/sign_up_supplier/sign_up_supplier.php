<?php
$cssTime = filemtime('../../view/sign_up_supplier/sign_up_supplier/sign_up_supplier.css'); // ejemplo: '../Home/5.Video/video.css'
$jsTime = filemtime('../../view/sign_up_supplier/sign_up_supplier/sign_up_supplier.js');   // ejemplo: '../Home/5.Video/video.js'
?>
<link rel="stylesheet" href="../../view/sign_up_supplier/sign_up_supplier/sign_up_supplier.css?v=<?= $cssTime ?>">
<main class="card" aria-labelledby="login-title">
    <h1 id="login-title">Sign up</h1>

    <seccion>
      <div class="field">
        <label for="name_sign_up">Name</label>
        <input id="name_sign_up" name="name" type="text" autocomplete="name" required
               placeholder="Full name"/>
        <div id="name-help" class="help" aria-live="polite"></div>
      </div>

      <div class="field">
        <label for="email_sign_up">Email</label>
        <input id="email_sign_up" name="email" type="email" autocomplete="email" required
               placeholder="you@example.com" />
        <div id="email-help" class="help" aria-live="polite"></div>
      </div>

      <div class="field">
        <label for="phone_sign_up">Phone</label>
        <input id="phone_sign_up" name="phone" type="tel" autocomplete="tel" required
               placeholder="+57 300 000 0000" />
        <div id="phone-help" class="help" aria-live="polite"></div>
      </div>

      <div class="field">
        <label for="company_sign_up">Company name</label>
        <input id="company_sign_up" name="company_name" type="text" required
               placeholder="Your company" />
        <div id="company-help" class="help" aria-live="polite"></div>
      </div>

      <div class="field">
        <label for="country_sign_up">Country</label>
        <input id="country_sign_up" name="country" type="text" required
               placeholder="Country" />
        <div id="country-help" class="help" aria-live="polite"></div>
      </div>

      <div class="field">
        <label for="city_sign_up">City</label>
        <input id="city_sign_up" name="city" type="text" required
               placeholder="City" />
        <div id="city-help" class="help" aria-live="polite"></div>
      </div>

      <div class="field">
        <label for="address1_sign_up">Address line 1</label>
        <input id="address1_sign_up" name="address_line1" type="text" required
               placeholder="Street, number" />
        <div id="address1-help" class="help" aria-live="polite"></div>
      </div>

      <div class="field">
        <label for="address2_sign_up">Address line 2</label>
        <input id="address2_sign_up" name="address_line2" type="text"
               placeholder="Apartment, suite, etc. (optional)" />
        <div id="address2-help" class="help" aria-live="polite"></div>
      </div>

      <div class="field">
        <label for="postcode_sign_up">Post code</label>
        <input id="postcode_sign_up" name="postcode" type="text" required
               placeholder="ZIP / Postal code" />
        <div id="postcode-help" class="help" aria-live="polite"></div>
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
        <div id="pass-help" class="help" aria-live="polite"></div>
      </div>

      <div class="actions">
        <button id="signup_enter" class="btn" type="submit">Sign Up</button>
      </div>

      <p class="footer">
        <a class="link" href="../../view/log_inSupplier/index.php">Already have an account? Login</a>
      </p>
    </seccion>
  </main>

<script src="../../view/sign_up_supplier/sign_up_supplier/sign_up_supplier.js?v=<?= $jsTime ?>" type="text/javascript"></script>
