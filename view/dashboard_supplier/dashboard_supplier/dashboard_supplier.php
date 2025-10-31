<?php
$cssTime = filemtime('../../view/dashboard_supplier/dashboard_supplier/dashboard_supplier.css');
$jsTime  = filemtime('../../view/dashboard_supplier/dashboard_supplier/dashboard_supplier.js');
?>
<link rel="stylesheet" href="../../view/dashboard_supplier/dashboard_supplier/dashboard_supplier.css?v=<?= $cssTime ?>">

<main class="dashboard_supplier" aria-labelledby="dashboard_supplier-title">
  <header class="ds-header">
    <h1 id="dashboard_supplier-title">Supplier Dashboard</h1>
    <div class="ds-actions">
      <button id="button_new_product" class="btn btn-primary" type="button">+ New Product</button>
      <a href="../../view/products_supplier/index.php"><button class="btn" type="button">Products</button></a>
    </div>
  </header>

  <section class="ds-grid">
    <article class="card stat">
      <h2>Open Orders</h2>
      <p class="stat-num">12</p>
      <small class="muted">+2 since yesterday</small>
    </article>

    <article class="card stat">
      <h2>In Production</h2>
      <p class="stat-num">7</p>
      <small class="muted">3 due today</small>
    </article>

    <article class="card stat">
      <h2>Pending Proofs</h2>
      <p class="stat-num">4</p>
      <small class="muted">Approval required</small>
    </article>

    <article class="card stat">
      <h2>Messages</h2>
      <p class="stat-num">3</p>
      <small class="muted">2 unread</small>
    </article>
  </section>

  <section class="ds-columns">
    <!-- Tabla: últimos pedidos -->
    <div class="card table-card" aria-labelledby="recent-orders-title">
      <div class="card-header">
        <h2 id="recent-orders-title">Recent Orders</h2>
        <a href="#" class="link">View all</a>
      </div>
      <div class="table-wrap">
        <table class="table" role="table">
          <thead>
            <tr>
              <th scope="col">Order</th>
              <th scope="col">Date</th>
              <th scope="col">Status</th>
              <th scope="col" class="right">Total</th>
              <th scope="col" class="center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#10234</td>
              <td>2025-09-10</td>
              <td><span class="badge badge-warning">Pending</span></td>
              <td class="right">$420.00</td>
              <td class="center"><button class="btn btn-small">Open</button></td>
            </tr>
            <tr>
              <td>#10233</td>
              <td>2025-09-09</td>
              <td><span class="badge badge-info">Production</span></td>
              <td class="right">$1,280.00</td>
              <td class="center"><button class="btn btn-small">Open</button></td>
            </tr>
            <tr>
              <td>#10232</td>
              <td>2025-09-08</td>
              <td><span class="badge badge-success">Shipped</span></td>
              <td class="right">$199.99</td>
              <td class="center"><button class="btn btn-small">Open</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Lateral: perfil y acciones rápidas -->
    <aside class="card profile" aria-labelledby="profile-title">
      <h2 id="profile-title" class="sr-only">Supplier Profile</h2>

      <div class="profile-row">
        <div class="avatar" aria-hidden="true">S</div>
        <div>
          <p class="title">Supplier Name</p>
          <p class="muted">supplier@example.com</p>
        </div>
      </div>

      <ul class="meta">
        <li><span>Company:</span> Demo Company Ltd</li>
        <li><span>Country:</span> Colombia</li>
        <li><span>City:</span> Arbeláez</li>
      </ul>

      <div class="quick-links">
        <a id="open-supplier-dashboard" class="btn btn-block" >Supplier Profile</a>
        <a class="btn btn-block" href="#">Manage Promotions</a>
        <a class="btn btn-block" href="#">Download Reports</a>
      </div>

    </aside>
  </section>
</main>

<script src="../../view/dashboard_supplier/dashboard_supplier/dashboard_supplier.js?v=<?= $jsTime ?>"></script>
