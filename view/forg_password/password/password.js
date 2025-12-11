class ClassLogin {
  constructor() {
  loginEnter.addEventListener("click", function(){
    classLogin.makeAjaxRecuest();
  })
  }
  makeAjaxRecuest(){
    // alert(email.value + password.value);
    // alert(email.value + password.value);
    const url = "../../controller/users/login.php";
    const data = {
      action: "requestLogin",
      email: email.value,
      password: password.value
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
        alert(data);
      })
      .catch(error => {
        // Log any errors to the console.
        console.error("Error:", error);
      });

  }
}

const email = document.getElementById("email");
const password = document.getElementById("password");
const loginEnter = document.getElementById("login_enter");
const classLogin = new ClassLogin();
