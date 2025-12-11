class ClassCategory {
  constructor() {


    document.addEventListener('DOMContentLoaded', () => {
     headerAddProduct.setCurrentHeader('category');
   });

    this.category_selected = "";
    btn_create_new_category.addEventListener("click", function(){
      classCategory.createNewCategory();
    })

    next_category.addEventListener("click", function(){
      if (Number.isInteger(classCategory.category_selected)) {
        classCategory.updatedCategory();
      }
      else {
        alert("Select a category first.");
      }
    })

    this.getCategories();

    setTimeout(() => this.getCategorySelected(), 1000);
  }





  getCategorySelected(){
    const params = new URLSearchParams(window.location.search);
    const sku = params.get('sku');


    const url = "../../controller/products/category.php";
    const data = {
      action: "get_category_selected",
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
      //  alert(data);
        var data = JSON.parse(data);

        if (data["success"]) {
          const id = Number.parseInt(data["category_id"], 10);
        //  alert(id);
          if (!Number.isNaN(id)) classCategory.selectCategory(id-1);
      }
      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });
  //  alert(sku);
  }

  createNewCategory(){
    const params = new URLSearchParams(window.location.search);
    const sku = params.get('sku');
    // alert(sku);
    // alert(email.value + password.value);
    // alert(email.value + password.value);
    const url = "../../controller/products/category.php";
    const data = {
      action: "create_new_category",
      name: new_category.value,
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
      //  alert(data);
        var data = JSON.parse(data);
        if (data["success"]) {
          // classCategory.category_selected;
          alert("The category "+ new_category.value+" was created successfully.");
          location.reload();
        }
        else {
          alert("Error: " + data["error"])
        }

      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });

  }
  updatedCategory(){
    const params = new URLSearchParams(window.location.search);
    const sku = params.get('sku');
    // alert(email.value + password.value);
    // alert(email.value + password.value);
    const url = "../../controller/products/product.php";
    const data = {
      action: "update_category",
      id: this.category_selected,
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
      //  alert(data);
        var data = JSON.parse(data);
        if (data["success"]) {
          headerAddProduct.goNext('../../view/product_details/index.php');
        }
        else {
        }

      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });

  }

  getCategories(){
    const url = "../../controller/products/category.php";
    const data = {
      action: "get_categories"
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

        classCategory.drawListCategories(data);


      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });
  }

  drawListCategories(data){
    if (!window.category_list) return;

    // 1) Limpiar
    category_list.innerHTML = "";

    // 2) Arreglo de categorías
    var list = (data && data.success && Array.isArray(data.data)) ? data.data : [];

    // 3) Pintar y asignar onclick con el índice
    for (var i = 0; i < list.length; i++) {
      var name  = list[i].name || "";
      var count = Number(list[i].products_count) || 0;

      category_list.innerHTML +=
        '<div class="cp-cat" role="listitem" id="cat-' + i + '" onclick="classCategory.selectCategory(' + i + ')">' +
          '<span class="cp-cat-name">' + name + '</span>' +
          '<small class="cp-cat-meta">' + count + ' products</small>' +
        '</div>';
    }
  }

  selectCategory(divSelected){
    if (!window.category_list) return;

    var boxes = category_list.querySelectorAll('.cp-cat');

    // Limpiar bordes
    for (var j = 0; j < boxes.length; j++) {
      boxes[j].style.border = '2px solid var(--border)';
      boxes[j].style.borderRadius = '';
    }

    // Pintar borde del seleccionado (si existe)
    if (divSelected >= 0 && divSelected < boxes.length) {
      boxes[divSelected].style.border = '2px solid var(--brand, #005548)';
      boxes[divSelected].style.borderRadius = '12px';
    }
    this.category_selected = Number.parseInt(divSelected, 10) + 1;

  //  alert(this.category_selected);

  }

}

const btn_create_new_category = document.getElementById("btn-create-new-category");
const new_category = document.getElementById("new_category");
const next_category = document.getElementById("next_category");
const category_list = document.getElementById("category_list");
const classCategory = new ClassCategory();
