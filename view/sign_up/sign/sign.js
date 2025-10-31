class ClassSignUp {
  constructor() {
    signupEnter.addEventListener("click", () => {
      if (this.validateFormData()) this.makeAjaxRequest();
    });
  }

  // --- UI helpers ---
  resetBorders() {
    [nameSignUp, emailSignUp, passwordSignUp].forEach(i => i.style.border = "");
  }
  addRedBorder(input) { input.style.border = "1px solid red"; }

  // --- Email / Password rules ---
  validEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }
  isMinLength(pw) { return pw && pw.length >= 8; }
  hasUpperCase(pw) { return /[A-Z]/.test(pw); }
  hasLowerCase(pw) { return /[a-z]/.test(pw); }
  hasNumber(pw)    { return /[0-9]/.test(pw); }
  hasSpecial(pw)   { return /[!@#$%^&*(),.?":{}|<>_\-\\\/[\]=+;`~]/.test(pw); }

  validatePassword(pw) {
    if (!this.isMinLength(pw)) { this.addRedBorder(passwordSignUp); alert("Password must be at least 8 characters long."); return false; }
    if (!this.hasUpperCase(pw)) { this.addRedBorder(passwordSignUp); alert("Password must contain at least one uppercase letter."); return false; }
    if (!this.hasLowerCase(pw)) { this.addRedBorder(passwordSignUp); alert("Password must contain at least one lowercase letter."); return false; }
    if (!this.hasNumber(pw))    { this.addRedBorder(passwordSignUp); alert("Password must contain at least one number."); return false; }
    if (!this.hasSpecial(pw))   { this.addRedBorder(passwordSignUp); alert("Password must contain at least one special character (!@#$%^&* etc.)."); return false; }
    return true;
  }

  // --- Main validation ---
  validateFormData() {
    this.resetBorders();

    const data = {
      name: nameSignUp.value.trim(),
      email: emailSignUp.value.trim(),
      password: passwordSignUp.value
    };

    if (!data.name) { this.addRedBorder(nameSignUp); alert("Please enter your name."); return false; }
    if (!data.email || !this.validEmail(data.email)) { this.addRedBorder(emailSignUp); alert("Please enter a valid email address."); return false; }
    if (!data.password) { this.addRedBorder(passwordSignUp); alert("Please enter a password."); return false; }
    if (!this.validatePassword(data.password)) return false;

    return true;
  }

  // --- Ajax ---
  makeAjaxRequest() {
    const url = "controller/customers/sing_up.php";
    const payload = {
      action: "requestSignUp",
      name: nameSignUp.value.trim(),
      email: emailSignUp.value.trim(),
      password: passwordSignUp.value
    };

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    })
      .then(res => res.json())
      .then(data => {
        if (data.response === true) {
          alert("User registered successfully.");
          window.location.href = "../../view/log_in/index.php";
        } else {
          alert(data.error || "Could not complete the registration.");
        }
      })
      .catch(() => {
        alert("Network error. Please try again.");
      });
  }
}

// DOM refs
const nameSignUp = document.getElementById("name_sign_up");
const emailSignUp = document.getElementById("email_sign_up");
const passwordSignUp = document.getElementById("password_sign_up");
const signupEnter = document.getElementById("signup_enter");

// init
const classSignUp = new ClassSignUp();
