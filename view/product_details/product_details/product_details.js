class ClassAddProductDetails {
  constructor(){
    document.addEventListener('DOMContentLoaded', () => {
     headerAddProduct.setCurrentHeader('product details');
   });

    next_product_details.addEventListener("click", ()=>{
      if (pd_name.value.trim() && pd_status.value && pd_desc.value.trim()) {
        classAddProductDetails.saveProductDetails(); // antes: classAddProductDetails.
      } else {
        alert('Please complete all required fields and selections.');
      }
    });


    this.getProductDetails();
  }

  getProductDetails(){
    const params = new URLSearchParams(window.location.search);
    const sku = params.get('sku');


    const url = "../../controller/products/product.php";
    const data = {
      action: "get_product_details",
      sku: sku
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
        var data = JSON.parse(data);

       if (data["success"]) {

         pd_name.value = data["data"]["name"];
         pd_desc.value = data["data"]["description"];
         pd_status.value = data["data"]["status"];
         pd_tagline.value = data["data"]["descriptive_tagline"];


        }
      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });
  }

  saveProductDetails(){
    const params = new URLSearchParams(window.location.search);
    const sku = params.get('sku');

    const url = "../../controller/products/product.php";
    const data = {
      action: "save_product_details",
      name: pd_name.value,
      status: pd_status.value,
      description: pd_desc.value,
      pd_tagline: pd_tagline.value,
      sku: sku
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
        //alert(data);
        var data = JSON.parse(data);

        if (data["success"]) {
          headerAddProduct.goNext('../../view/variations/index.php');

        }//
      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });

  }
}

const pd_name = document.getElementById("pd_name");
const pd_status = document.getElementById("pd_status");
const pd_desc = document.getElementById("pd_desc");
const pd_tagline = document.getElementById("pd_tagline");
const next_product_details = document.getElementById("next_product_details");



const classAddProductDetails = new ClassAddProductDetails();
