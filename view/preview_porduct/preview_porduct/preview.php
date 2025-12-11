<?php
$cssPath = '../../view/preview_porduct/preview_porduct/preview.css';
$jsPath  = '../../view/preview_porduct/preview_porduct/preview.js';
$jsPath2  = '../../view/preview_porduct/preview_porduct/preview_logic.js';
$cssTime = @filemtime($cssPath) ?: time();
$jsTime  = @filemtime($jsPath)  ?: time();
$jsTime2  = @filemtime($jsPath2)  ?: time();
?>
<link rel="stylesheet" href="<?= $cssPath ?>?v=<?= $cssTime ?>">

<main class="sp-amz" aria-labelledby="sp-title">

  <div class="sp-shell">
    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" class="sp-breadcrumbs js-fade-up">
      <ol id="sp_breadcrumbs" class="crumbs">
        <li><a href="#">Office &amp; Stationery</a></li>
        <li><a href="#">Lanyards</a></li>
      </ol>
    </nav>

    <!-- GRID PRINCIPAL -->
    <section class="sp-grid">
      <!-- Col 1: Galería (sticky + ligero parallax) -->
      <aside class="sp-col sp-gallery" aria-label="Product media">
        <div class="sp-gallery-inner">
          <div class="sp-main-wrapper js-parallax">
            <button type="button" class="sp-nav sp-nav-prev" aria-label="Previous media">‹</button>

            <div class="sp-main" id="sp_main" aria-live="polite">
              <div class="cp-empty">No media</div>
            </div>

            <button type="button"
                    class="sp-nav sp-nav-next"
                    aria-label="Next media"
                    onclick="previewLogic.nextImage()">
              ›
            </button>
          </div>

          <!-- Miniaturas (imágenes de lanyards + video demo) -->
          <div class="sp-thumbs" id="sp_thumbs" role="list">
            <!-- Imagen 1 -->
          <!--  <button type="button"
                    class="sp-thumb js-scale-in"
                    role="listitem"
                    data-type="image"
                    data-src="https://upload.wikimedia.org/wikipedia/commons/4/4a/Lanyard_mit_Logo_bedruckt.jpg">
              <img src="https://upload.wikimedia.org/wikipedia/commons/4/4a/Lanyard_mit_Logo_bedruckt.jpg"
                   alt="Printed lanyard with company logo">
            </button>

            <button type="button"
                    class="sp-thumb js-scale-in"
                    role="listitem"
                    data-type="image"
                    data-src="https://upload.wikimedia.org/wikipedia/commons/6/6d/Various_lanyards.jpg">
              <img src="https://upload.wikimedia.org/wikipedia/commons/6/6d/Various_lanyards.jpg"
                   alt="Assorted coloured lanyards laid out on a table">
            </button>

            <button type="button"
                    class="sp-thumb js-scale-in"
                    role="listitem"
                    data-type="image"
                    data-src="https://upload.wikimedia.org/wikipedia/commons/4/40/Red_lanyard.jpg">
              <img src="https://upload.wikimedia.org/wikipedia/commons/4/40/Red_lanyard.jpg"
                   alt="Red lanyard on a light background">
            </button>

            <button type="button"
                    class="sp-thumb js-scale-in"
                    role="listitem"
                    data-type="image"
                    data-src="https://upload.wikimedia.org/wikipedia/commons/7/7a/Accreditation_Lanyards.jpg">
              <img src="https://upload.wikimedia.org/wikipedia/commons/7/7a/Accreditation_Lanyards.jpg"
                   alt="Accreditation lanyards hanging ready for an event">
            </button>

            <button type="button"
                    class="sp-thumb js-scale-in"
                    role="listitem"
                    data-type="image"
                    data-src="https://upload.wikimedia.org/wikipedia/commons/f/fb/Lanyard_for_ID_card.jpg">
              <img src="https://upload.wikimedia.org/wikipedia/commons/f/fb/Lanyard_for_ID_card.jpg"
                   alt="Lanyard with ID card close-up">
            </button>

            <button type="button"
                    class="sp-thumb sp-thumb-video js-scale-in"
                    role="listitem"
                    data-type="video"
                    data-src="https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4">
              <span class="thumb-video-label">Video</span>
            </button>-->
          </div>

          <small class="cp-hint">Hover to zoom • Media changes every 5 seconds</small>
        </div>
      </aside>

      <!-- Col 2: Detalles (la más ancha) -->
      <section class="sp-col sp-details js-fade-up">
        <!-- Etiqueta pequeña -->
        <span id="sp_category" class="sp-category">
          Lanyards &amp; ID Accessories
        </span>

        <!-- Título -->
        <h1 id="sp-title" class="sp-title js-fade-up">
          Custom Printed Lanyards – 10mm to 35mm – Full Colour Logo – Breakaway Safety &amp; ID Badge Holders
        </h1>

        <!-- Meta: solo nombre de la marca, sin SKU -->
        <div class="sp-meta js-fade-up">
          <span id="sp-brand" class="brand-link">Promoflow</span>
        </div>

        <!-- Subtítulo (inglés británico) -->
        <p id="sp_subtitle" class="sp-subtitle js-fade-up">
          Choose the width, pack size and clip type that match your event, office or school, then upload your artwork for full colour printing.
        </p>

        <!-- Precio principal -->
        <div class="sp-price-main js-fade-up">
          <span class="sp-price-symbol" id="sp_currency_symbol">£</span>
          <span id="sp_price" class="sp-price">
            8<span class="sp-price-minor">.00</span>
          </span>
          <span id="sp_unit_hint" class="sp-unit-hint">per 100 units</span>
        </div>

        <!-- VARIATIONS ARRIBA -->
        <section id="section_variations" class="sp-variations js-fade-up" aria-label="Product configuration">

          <div class="var-group" aria-labelledby="var_label_size_1">

            <div class="var-label">
              <span class="var-name">Width</span>
              <strong id="var_label_size_1">20mm</strong>
            </div>

            <div class="var-options" id="sp_var_group_size_1">

              <button  type="button" class="var-option js-scale-in">
                <img class="var-thumb"
                     src="https://upload.wikimedia.org/wikipedia/commons/6/6d/Various_lanyards.jpg"
                     alt="Slim lanyard sample">
                <span class="opt-main">10mm</span>
              </button>

              <button type="button" class="var-option js-scale-in">
                <img class="var-thumb"
                     src="https://upload.wikimedia.org/wikipedia/commons/4/40/Red_lanyard.jpg"
                     alt="Narrow lanyard sample">
                <span class="opt-main">15mm</span>
                <span class="opt-sub">Lightweight</span>
              </button>

              <button type="button" class="var-option is-selected js-scale-in">
                <img class="var-thumb"
                     src="https://upload.wikimedia.org/wikipedia/commons/4/4a/Lanyard_mit_Logo_bedruckt.jpg"
                     alt="Standard 20mm printed lanyard">
                <span class="opt-main">20mm</span>
                <span class="opt-sub">Most popular</span>
              </button>

              <button type="button" class="var-option js-scale-in">
                <img class="var-thumb"
                     src="https://upload.wikimedia.org/wikipedia/commons/6/6d/Various_lanyards.jpg"
                     alt="Wide strap lanyard">
                <span class="opt-main">25mm</span>
                <span class="opt-sub">Extra logo space</span>
              </button>

              <button type="button" class="var-option js-scale-in">
                <img class="var-thumb"
                     src="https://upload.wikimedia.org/wikipedia/commons/4/40/Red_lanyard.jpg"
                     alt="Extra wide lanyard">
                <span class="opt-main">30mm</span>
                <span class="opt-sub">Bold branding</span>
              </button>

              <button type="button" class="var-option js-scale-in">
                <img class="var-thumb"
                     src="https://upload.wikimedia.org/wikipedia/commons/4/4a/Lanyard_mit_Logo_bedruckt.jpg"
                     alt="Oversized sponsor lanyard">
                <span class="opt-main">35mm</span>
                <span class="opt-sub">Sponsor logos</span>
              </button>

            </div>

          </div>


          <div class="var-group" aria-labelledby="var_label_size_2">

            <div class="var-label">
              <span class="var-name">Print side</span>
              <strong id="var_label_size_2">Double sided</strong>
            </div>

            <div class="var-options">
              <button type="button" class="var-option is-selected js-scale-in">
                <span class="opt-main">Double sided</span>
              </button>
              <button type="button" class="var-option js-scale-in">
                <span class="opt-main">Single sided</span>
                <span class="opt-sub">Budget option</span>
              </button>
            </div>

          </div>


          <div class="var-group" aria-labelledby="var_label_size_3">
            <div class="var-label">
              <span class="var-name">Clip type</span>
              <strong id="var_label_size_3">Swivel hook</strong>
            </div>

            <div class="var-options">
            <!--  <button type="button" class="var-option is-selected js-scale-in">
                <img class="var-thumb"
                     src="https://upload.wikimedia.org/wikipedia/commons/4/4a/Lanyard_mit_Logo_bedruckt.jpg"
                     alt="Lanyard with swivel hook">
                <span class="opt-main">Swivel hook</span>
                <span class="opt-sub">Standard</span>
              </button>

              <button type="button" class="var-option js-scale-in">
                <img class="var-thumb"
                     src="https://upload.wikimedia.org/wikipedia/commons/4/40/Red_lanyard.jpg"
                     alt="Lanyard with trigger clip">
                <span class="opt-main">Trigger clip</span>
                <span class="opt-sub">Secure</span>
              </button>

              <button type="button" class="var-option js-scale-in">
                <img class="var-thumb"
                     src="https://upload.wikimedia.org/wikipedia/commons/6/6d/Various_lanyards.jpg"
                     alt="Lanyard with split ring">
                <span class="opt-main">Split ring</span>
                <span class="opt-sub">Keys</span>
              </button> -->
            </div>
          </div>
        -->
        </section>
      </section>

      <!-- BOTTOM: SIEMPRE ANTES DE BUYBOX EN EL HTML -->
      <section class="sp-variations-bottom js-fade-up" aria-label="More configuration options">
        <!-- JS moverá aquí los .var-group que no quepan en .sp-variations -->
      </section>

      <!-- NUEVA SECCIÓN: PACK SIZE (COLUMNAS 1 Y 2) -->
      <section class="sp-packsize js-fade-up" id="sp-packsize" aria-label="Pack sizes and bundle pricing">
        <div class="var-group var-group-pack" aria-labelledby="var_label_items">
          <div class="var-label">
            <span class="var-name">Pack size</span>
            <strong id="var_label_items">500 units</strong>
          </div>

          <div class="var-options" id="sp_var_group_items">
            <button type="button" class="var-option js-scale-in">
              <span class="opt-main">50</span>
              <span class="opt-sub">Small teams · from £0.25 each</span>
            </button>
            <button type="button" class="var-option js-scale-in">
              <span class="opt-main">100</span>
              <span class="opt-sub">Events · from £0.18 each</span>
            </button>
            <button type="button" class="var-option js-scale-in">
              <span class="opt-main">200</span>
              <span class="opt-sub">Schools · from £0.14 each</span>
            </button>
            <button type="button" class="var-option is-selected js-scale-in">
              <span class="opt-main">500</span>
              <span class="opt-sub">Best value · from £0.08 each</span>
            </button>
            <button type="button" class="var-option js-scale-in">
              <span class="opt-main">1,000</span>
              <span class="opt-sub">Conferences · bulk rates</span>
            </button>
            <button type="button" class="var-option js-scale-in">
              <span class="opt-main">2,000</span>
              <span class="opt-sub">Large venues · bulk rates</span>
            </button>
            <button type="button" class="var-option js-scale-in">
              <span class="opt-main">3,000</span>
              <span class="opt-sub">Trade shows · bulk rates</span>
            </button>
            <button type="button" class="var-option js-scale-in">
              <span class="opt-main">5,000</span>
              <span class="opt-sub">Multi-site · bulk rates</span>
            </button>
          </div>
        </div>
      </section>

      <!-- NUEVA SECTION: DESCARGA DE TEMPLATES EN PDF -->
      <section class="sp-artwork-downloads js-fade-up" aria-label="Artwork templates">
        <h2 class="sp-artwork-heading">Download artwork templates (PDF)</h2>

        <div class="sp-artwork-grid">
          <!-- 🔗 Cambia las rutas href por las de tus PDFs reales -->
          <a href="/templates/lanyard-10mm.pdf" class="btn btn-artwork" download>
            10&nbsp;mm template
          </a>
          <a href="/templates/lanyard-15mm.pdf" class="btn btn-artwork" download>
            15&nbsp;mm template
          </a>
          <a href="/templates/lanyard-20mm.pdf" class="btn btn-artwork" download>
            20&nbsp;mm template
          </a>
          <a href="/templates/lanyard-25mm.pdf" class="btn btn-artwork" download>
            25&nbsp;mm template
          </a>
          <a href="/templates/lanyard-30mm.pdf" class="btn btn-artwork" download>
            30&nbsp;mm template
          </a>
          <a href="/templates/lanyard-35mm.pdf" class="btn btn-artwork" download>
            35&nbsp;mm template
          </a>
        </div>

        <p class="sp-artwork-note">
          If you do not have a designer, you can still place your order and request help with artwork during checkout.
        </p>
      </section>

      <!-- ITEMS NOTE (cuadro verde independiente, con subtítulo + texto por ítem) -->
      <section class="sp-items-note js-fade-up" id="sp-items-note" aria-label="Items information">
        <ul class="sp-items-list">
          <li>
            <strong class="sp-item-subtitle">What is one item?</strong>
            <span>Each item refers to a single printed lanyard produced with your selected width, pack size and clip type.</span>
          </li>
          <li>
            <strong class="sp-item-subtitle">How do pack sizes work?</strong>
            <span>Pack quantities indicate the total number of individual lanyards supplied in your order, ready to hand out to staff, visitors or guests.</span>
          </li>
          <li>
            <strong class="sp-item-subtitle">Consistent branding every time</strong>
            <span>Your artwork, colours and logos are checked before production so each lanyard looks clean, sharp and on-brand.</span>
          </li>
        </ul>
      </section>

      <!-- Col 3: Buy box (sticky + ligero parallax) -->
      <aside class="sp-col sp-buybox" aria-label="Purchase options">
        <div class="box js-fade-up js-scale-in js-parallax">
          <div class="price-line">
            <span class="label">Unit</span>
            <strong id="bb_unit">£0.08</strong>
          </div>
          <div class="price-line">
            <span class="label">Total</span>
            <strong id="bb_total">£8.00</strong>
          </div>

          <div class="ship">
            <span>Delivery</span>
            <small>Delivery only in England. Standard dispatch in 2–3 weeks.</small>
          </div>

          <div class="stock in">In stock</div>

          <button type="button" class="btn btn-primary btn-buy js-scale-in" id="bb_add">
            Add to basket (disabled)
          </button>
          <button type="button" class="btn btn-ghost btn-buy js-scale-in" id="bb_buy" disabled>
            Buy now (disabled)
          </button>
        </div>
      </aside>
    </section>

    <!-- ABOUT THIS ITEM + DESCRIPCIÓN MÁS BONITA CON IMÁGENES CENTRADAS -->
    <section class="sp-about js-fade-up">
      <h2>Product Description</h2>

      <div id="sp_desc" class="sp-desc">
        <!-- Descripción dinámica -->
      </div>
    </section>

    <!-- MÁS PRODUCTOS AL FINAL (con imágenes relacionadas) -->
    <section class="sp-related js-fade-up">
      <h2>More products you might like</h2>

      <div class="related-grid">
      <!--  <article class="related-card js-fade-up js-scale-in">
          <div class="related-media">
            <img src="https://upload.wikimedia.org/wikipedia/commons/f/fb/Lanyard_for_ID_card.jpg"
                 alt="Lanyard with ID card">
          </div>
          <div class="related-body">
            <h3 class="related-title">ID Card Lanyards</h3>
            <p class="related-category">Lanyards</p>
            <p class="related-variant">Standard • Full colour print</p>
            <p class="related-price">£0.40</p>
          </div>
          <button class="btn related-btn">Buy now</button>
        </article>

        <article class="related-card js-fade-up js-scale-in">
          <div class="related-media">
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/4a/Lanyard_mit_Logo_bedruckt.jpg"
                 alt="Printed logo lanyard">
          </div>
          <div class="related-body">
            <h3 class="related-title">Sponsor Lanyards</h3>
            <p class="related-category">Lanyards</p>
            <p class="related-variant">25mm • Sponsor logos</p>
            <p class="related-price">£0.55</p>
          </div>
          <button class="btn related-btn">Buy now</button>
        </article>

        <article class="related-card js-fade-up js-scale-in">
          <div class="related-media">
            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6d/Various_lanyards.jpg"
                 alt="Mixed colour lanyards">
          </div>
          <div class="related-body">
            <h3 class="related-title">Colour Mix Packs</h3>
            <p class="related-category">Lanyards</p>
            <p class="related-variant">Assorted colours • 500 pack</p>
            <p class="related-price">£0.50</p>
          </div>
          <button class="btn related-btn">Buy now</button>
        </article>

        <article class="related-card js-fade-up js-scale-in">
          <div class="related-media">
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/40/Red_lanyard.jpg"
                 alt="Red safety lanyard">
          </div>
          <div class="related-body">
            <h3 class="related-title">Safety Lanyards</h3>
            <p class="related-category">Lanyards</p>
            <p class="related-variant">Breakaway • Single colour</p>
            <p class="related-price">£0.45</p>
          </div>
          <button class="btn related-btn">Buy now</button>
        </article>-->
      </div>
    </section>
  </div>

  <!-- BOTONES FLOTANTES: BACK / PUBLISH -->
  <div class="sp-actions">

    <button type="button"
            class="btn btn-back-preview"
            id="btn_back_edit">
      Back to editing
    </button>

    <button type="button"
            class="btn btn-publish-preview"
            id="btn_publish">
      Publish
    </button>

  </div>
</main>

<script src="<?= $jsPath ?>?v=<?= $jsTime ?>"></script>
<script src="<?= $jsPath2 ?>?v=<?= $jsTime2 ?>"></script>
