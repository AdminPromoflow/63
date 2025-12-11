class ClassSupplierProfile {
  constructor() {
    this.requestProfileInfo();

    for (let i = 0; i < sp_cancel.length; i++) {
      sp_cancel[i].addEventListener("click", function(){
        alert("Data update cancelled");
        window.location.reload();
      })
    }

    for (let i = 0; i < sp_submit.length; i++) {
      sp_submit[i].addEventListener("click", function(){
        classSupplierProfile.requestUpdateProfileInfo();
      })
    }




  }












  requestUpdateProfileInfo(){

    const url = "../../controller/users/supplier_info.php";
    const data = {
      action: "request_update_profile_info",
      contact_name: contact_name.value,
      company_name: company_name.value,
      email: email.value,
      phone: phone.value,
      country: country.value,
      city: city.value,
      address_line1: address_line1.value,
      address_line2: address_line2.value,
      postal_code: postal_code.value
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

        let data_ = JSON.parse(data);

        if (data_["response"]) {
          alert("Data updated.");



          location.reload();
        }
      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });

  }
  requestProfileInfo(){

    const url = "../../controller/users/supplier_info.php";
    const data = {
      action: "request_profile_info"
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
        let data_ = JSON.parse(data);
        //alert(data);
        classSupplierProfile.assignValues(data_["response"]);
      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });

  }

  assignValues(data){
    contact_name.value = data.contact_name;
    company_name.value = data.company_name;
    email.value = data.email;
    phone.value = data.phone;
    country.value = data.country;
    city.value = data.city;
    address_line1.value = data.address_line1;
    address_line2.value = data.address_line2;
    postal_code.value = data.postal_code;
  }
}

const contact_name  = document.getElementById("contact_name");
const company_name = document.getElementById("company_name");
const email = document.getElementById("email");
const phone = document.getElementById("phone");
const country = document.getElementById("country");
const city = document.getElementById("city");
const address_line1 = document.getElementById("address_line1");
const address_line2 = document.getElementById("address_line2");
const postal_code = document.getElementById("postal_code");


const sp_cancel = document.querySelectorAll(".sp-cancel");
const sp_submit = document.querySelectorAll(".sp-submit");




const classSupplierProfile = new ClassSupplierProfile();
