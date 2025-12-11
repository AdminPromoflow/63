class ClassSecurityHelper {
  constructor() {
    this.checkParameters()
  }

  checkParameters() {
    const params = new URLSearchParams(window.location.search);
    const sku = (params.get('sku') || '').trim();
    const sku_variation = (params.get('sku_variation') || '').trim();

    if (!(sku && sku_variation)) {
      window.location.replace("../../view/dashboard_supplier/index.php");
      return;
    }

    const url = "../../controller/security/security_helper.php";
    const data = {
      action: "check_parameters",
      sku: sku,
      sku_variation: sku_variation
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

        if (!data["success"]) {
          window.location.replace("../../view/dashboard_supplier/index.php");
          return;
        }

      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });
  }

}

const classSecurityHelper = new ClassSecurityHelper();
