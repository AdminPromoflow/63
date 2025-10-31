class Menu_Supplier {
  constructor() {
      this.verifyLogin();
      
    logout.addEventListener("click", function(){
      menu_supplier.logout();
    })
  }

  logout(){
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
        }
        else {
          alert("Sign-out failed. Please try again.");
        }


      })
      .catch(() => {
        alert("Error de red. Intenta nuevamente.");
      });
  }

  showHideLogoutButton(visible){
    if (visible) {
      logout.style.display = "block";
    }
    else {
      logout.style.display = "none";
    }
  }

  verifyLogin(){
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

        if (!data['response'] === true) {
          if (window.location.href.slice(-29) != "view/log_inSupplier/index.php") {
            if (window.location.href.slice(-31) != "view/sign_up_supplier/index.php") {
              window.location.href = "../../view/log_inSupplier/index.php";
            }
          }
          menu_supplier.showHideLogoutButton(false);
      }
      else if (window.location.href.slice(-29) == "view/log_inSupplier/index.php" || window.location.href.slice(-29) == "view/sign_up_supplier/index.php") {
        window.location.href = "../../view/dashboard_supplier/index.php";
        menu_supplier.showHideLogoutButton(true);

      }
      else {
        menu_supplier.showHideLogoutButton(true);

      }
      })
      .catch(() => {
        alert("Error de red. Intenta nuevamente.");
      });
  }
}
const logout = document.getElementById("logout");
const menu_supplier = new Menu_Supplier();
