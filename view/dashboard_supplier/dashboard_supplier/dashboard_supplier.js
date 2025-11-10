class ClassDashboardSupplier{
  constructor(){
    open_supplier_dashboard.addEventListener("click", function(){
      menu_supplier.verifyLogin();

      window.location.href = "../../view/supplier_profile/index.php";
    });


    button_new_product.addEventListener("click", function(){
      classDashboardSupplier.createNewProduct();
    });


  }


  createNewProduct(){

    const url = "../../controller/products/product.php";
    const data = {
      action: "create_new_product"
    };
    // Make a fetch request to the given URL with the specified data.
    fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data)
    })
      .then(response => {
        // Check if the response is okay, if so, return the response text.
        if (response.ok) {
          return response.text();
        }
        // If the response is not okay, throw an error.
        throw new Error("Network error.");
      })
      .then(data => {
      //  alert(data);
       var data = JSON.parse(data);

       const sku = data["sku"];
       const sku_variation = data["sku_variation"];


        if (data["success"]) {
          window.location.href =
          `../../view/category/index.php?sku=${encodeURIComponent(sku)}&sku_variation=${encodeURIComponent(sku_variation)}`;
        }


      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });

  }

}

const open_supplier_dashboard = document.getElementById("open-supplier-dashboard");
const button_new_product = document.getElementById("button_new_product");
const classDashboardSupplier = new ClassDashboardSupplier();
