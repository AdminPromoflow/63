<?php
$cssTime = filemtime('../../view/supplier_profile/supplier_profile/supplier_profile.css'); // ejemplo: '../Home/5.Video/video.css'
$jsTime  = filemtime('../../view/supplier_profile/supplier_profile/supplier_profile.js');   // ejemplo: '../Home/5.Video/video.js'
?>
<link rel="stylesheet" href="../../view/supplier_profile/supplier_profile/supplier_profile.css?v=<?= $cssTime ?>">

<main class="supplier_profile" aria-labelledby="supplier-profile-title">
  <header class="sp-header">
    <h1 id="supplier-profile-title">Supplier Profile</h1>
    <div class="sp-actions">
      <button class="btn btn-secondary  sp-cancel" type="button">Cancel</button>
      <button class="btn btn-primary sp-submit" type="submit" >Save changes</button>
    </div>
  </header>

  <section class="card sp-card" aria-label="Profile">
    <form id="supplierProfileForm" class="sp-form" method="post" action="/controller/users/update_supplier.php" novalidate>
      <!-- Contact / Company -->
      <div class="sp-grid">
        <div class="field">
          <label for="contact_name">Contact name</label>
          <input
            id="contact_name" name="contact_name" type="text"
            value="<?= htmlspecialchars($supplier['contact_name'] ?? '') ?>"
            placeholder="Jane Doe" autocomplete="name" />
        </div>

        <div class="field">
          <label for="company_name">Company name</label>
          <input
            id="company_name" name="company_name" type="text"
            value="<?= htmlspecialchars($supplier['company_name'] ?? '') ?>"
            placeholder="Acme Ltd." autocomplete="organization" />
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input
            id="email" name="email" type="email"
            value="<?= htmlspecialchars($supplier['email'] ?? '') ?>"
            placeholder="supplier@example.com" autocomplete="email" />
          <small class="hint">We’ll use this email for notifications.</small>
        </div>

        <div class="field">
          <label for="phone">Phone</label>
          <input
            id="phone" name="phone" type="tel"
            value="<?= htmlspecialchars($supplier['phone'] ?? '') ?>"
            placeholder="+44 300 000 0000" autocomplete="tel" />
        </div>
      </div>

      <!-- Location -->
      <h2 class="sp-subtitle">Business location</h2>
      <div class="sp-grid">
        <div class="field">
          <label for="country">Country</label>
          <input
            id="country" name="country" type="text"
            value="<?= htmlspecialchars($supplier['country'] ?? '') ?>"
            placeholder="Colombia" autocomplete="country-name" />
        </div>

        <div class="field">
          <label for="city">City</label>
          <input
            id="city" name="city" type="text"
            value="<?= htmlspecialchars($supplier['city'] ?? '') ?>"
            placeholder="Arbeláez" autocomplete="address-level2" />
        </div>

        <div class="field">
          <label for="address_line1">Address line 1</label>
          <input
            id="address_line1" name="address_line1" type="text"
            value="<?= htmlspecialchars($supplier['address_line1'] ?? '') ?>"
            placeholder="Street, number" autocomplete="address-line1" />
        </div>

        <div class="field">
          <label for="address_line2">Address line 2</label>
          <input
            id="address_line2" name="address_line2" type="text"
            value="<?= htmlspecialchars($supplier['address_line2'] ?? '') ?>"
            placeholder="Apartment, suite, etc." autocomplete="address-line2" />
        </div>

        <div class="field">
          <label for="postal_code">Postal code</label>
          <input
            id="postal_code" name="postal_code" type="text"
            value="<?= htmlspecialchars($supplier['postal_code'] ?? '') ?>"
            placeholder="110111" autocomplete="postal-code" />
        </div>
      </div>

      <!-- Form footer (mobile-first placement) -->
      <div class="sp-form-actions">
        <button class="btn btn-secondary sp-cancel" type="button">Cancel</button>
        <button class="btn btn-primary sp-submit" type="button">Save changes</button>
      </div>
    </form>
  </section>
</main>



<script src="../../view/supplier_profile/supplier_profile/supplier_profile.js?v=<?= $jsTime ?>"></script>
