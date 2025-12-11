<?php
// Opcional: headers JSON (útil para respuestas limpias al frontend)
header('Content-Type: application/json; charset=utf-8');

class Login {
  public function handleLogin() {
    $input = file_get_contents('php://input');
    $data  = json_decode($input, true);

    if (!is_array($data) || empty($data['action'])) {
      http_response_code(400);
      echo json_encode(['ok' => false, 'error' => 'Payload inválido o sin action']);
      return;
    }

    switch ($data['action']) {
      case 'requestLoginSupplier':
        $this->loginSupplier($data);
        break;
      case 'verify_login_supplier':
        $this->verifyLoginSupplier($data);
        break;
      case 'request_profile_info':
        $this->requestProfileInfo();
        break;
      case 'logout_supplier':
        $this->logoutSupplier();
        break;
      default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Acción no soportada']);
        break;
    }
  }

  private function logoutSupplier(): void
  {
      if (session_status() !== PHP_SESSION_ACTIVE) {
          session_start();
      }

      // Vaciar y destruir
      $_SESSION = [];
      $ok = session_destroy();

      header('Content-Type: application/json');
      echo json_encode(['response' => $ok]);
      exit; // evita enviar más salida
  }


  private function loginSupplier(array $data) {
    header('Content-Type: application/json; charset=utf-8');

    // ===== Input (validation is handled in JS, as you indicated) =====
    $email    = isset($data['email']) ? trim($data['email']) : '';
    $password = $data['password'] ?? '';

    // ===== Models / DB =====
    $connection = new Database();

    // Use setEmail() and then getPasswordUserByEmail()
    $user = new Users($connection);
    $user->setEmail($email);

    // Must return the password hash (string). If your method returns an array, adapt this line.
    $storedHash = $user->getPasswordUserByEmail();
    // Example if it returned an array:
    // $row = $user->getPasswordUserByEmail();
    // $storedHash = $row['password_hash'] ?? $row['password'] ?? null;

    // User doesn't exist or no stored hash
    if (!$storedHash) {
      http_response_code(401);
      echo json_encode(['response' => false, 'error' => 'Invalid credentials']);
      return;
    }

    // Password verification
    if (!password_verify($password, $storedHash)) {
      http_response_code(401);
      echo json_encode(['response' => false, 'error' => 'Invalid credentials']);
      return;
    }

    // ===== Session (email and login only) =====
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    // Prevent session fixation (recommended)
    if (function_exists('session_regenerate_id')) {
      session_regenerate_id(true);
    }

    $_SESSION['email'] = $email;
    $_SESSION['login'] = true; // or 'active' if you prefer: $_SESSION['login'] = 'active';

    // ===== Minimal response =====
    echo json_encode([
      'response' => true
    ]);
  }
  private function verifyLoginSupplier($data){
    // ===== Session (email and login only) =====
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    echo json_encode(['response' => $_SESSION['login']]);
  }

}

// Includes igual que en tu SignUp
include "../../controller/config/database.php";
include "../../model/users.php";

$loginClass = new Login(); // instancia
$loginClass->handleLogin();
