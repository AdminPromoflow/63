class Menu_Supplier {
  constructor() {
    // Header para mostrar/ocultar con scroll
    this.header = document.querySelector('.site-header');
    this.lastScrollY = window.scrollY;
    this.ticking = false;

    // Verificar login al iniciar
    this.verifyLogin();

    // Botón logout (si existe)
    if (logout) {
      logout.addEventListener("click", () => {
        this.logout();
      });
    }

    // Inicializar comportamiento de esconder header al hacer scroll
    this.initScrollHideHeader();
  }

  // ====== HEADER SCROLL HIDE ======
  initScrollHideHeader() {
    if (!this.header) return;

    window.addEventListener('scroll', () => {
      if (!this.ticking) {
        window.requestAnimationFrame(() => this.handleScroll());
        this.ticking = true;
      }
    });
  }

  handleScroll() {
    const currentScroll = window.scrollY;

    // Si bajamos y hemos pasado un poco (80px) -> esconder
    if (currentScroll > this.lastScrollY && currentScroll > 80) {
      this.header.classList.add('site-header--hidden');
    } else {
      // Si subimos o estamos muy arriba -> mostrar
      this.header.classList.remove('site-header--hidden');
    }

    this.lastScrollY = currentScroll;
    this.ticking = false;
  }

  // ====== LOGOUT ======
  logout() {
    const url = "../../controller/users/login.php";
    const data = {
      action: "logout_supplier"
    };

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
    })
      .then(res => res.json())
      .then(data => {

        if (JSON.parse(data["response"])) {
          location.reload();
        } else {
          alert("Sign-out failed. Please try again.");
        }

      })
      .catch(() => {
        alert("Error de red. Intenta nuevamente.");
      });
  }

  showHideLogoutButton(visible) {
    if (!logout) return;

    if (visible) {
      logout.style.display = "block";
    } else {
      logout.style.display = "none";
    }
  }

  // ====== VERIFICAR LOGIN ======
  verifyLogin() {
    const url = "../../controller/users/login.php";
    const data = {
      action: "verify_login_supplier"
    };

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
    })
      .then(res => res.json())
      .then(data => {

        // Más claro: si NO está logueado
        if (data['response'] !== true) {
          if (window.location.href.slice(-29) != "view/log_inSupplier/index.php") {
            if (window.location.href.slice(-31) != "view/sign_up_supplier/index.php") {
              window.location.href = "../../view/log_inSupplier/index.php";
            }
          }
          this.showHideLogoutButton(false);
        }
        // Si está logueado y está en login o sign up -> mandar al dashboard
        else if (
          window.location.href.slice(-29) == "view/log_inSupplier/index.php" ||
          window.location.href.slice(-29) == "view/sign_up_supplier/index.php"
        ) {
          window.location.href = "../../view/dashboard_supplier/index.php";
          this.showHideLogoutButton(true);
        }
        // Logueado y en cualquier otra página
        else {
          this.showHideLogoutButton(true);
        }
      })
      .catch(() => {
        alert("Error de red. Intenta nuevamente.");
      });
  }
}

// Elemento logout del DOM
const logout = document.getElementById("logout");

// Instancia global
const menu_supplier = new Menu_Supplier();
