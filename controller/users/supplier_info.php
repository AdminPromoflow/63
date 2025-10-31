<?php
// Opcional: headers JSON (útil para respuestas limpias al frontend)
header('Content-Type: application/json; charset=utf-8');

class SupplierInfo {
  public function handleLogin() {
    $input = file_get_contents('php://input');
    $data  = json_decode($input, true);

    if (!is_array($data) || empty($data['action'])) {
      http_response_code(400);
      echo json_encode(['ok' => false, 'error' => 'Payload inválido o sin action']);
      return;
    }

    switch ($data['action']) {
      case 'request_profile_info':
        $this->requestProfileInfo();
        break;
      case 'request_update_profile_info':
        $this->requestUpdateProfileInfo($data);
        break;
      default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Acción no soportada']);
        break;
    }
  }

  private function requestProfileInfo(){
    // ===== Session (email and login only) =====
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }


    $connection = new Database();

    // Use setEmail() and then getPasswordUserByEmail()
    $supplier = new Users($connection);
    $supplier->setEmail($_SESSION['email']);


    $response = $supplier->requestProfileInfo();

    echo json_encode(['response' => $response]);
  }


  private function requestUpdateProfileInfo($data){
    // ===== Session (email and login only) =====
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }


    $connection = new Database();

    // Use setEmail() and then getPasswordUserByEmail()
    $supplier = new Users($connection);
    $supplier->setName($data['contact_name']);
    $supplier->setCompanyName($data['company_name']);
    $supplier->setEmail($data['email']);
    $supplier->setPhone($data['phone']);
    $supplier->setCountry($data['country']);
    $supplier->setCity($data['city']);
    $supplier->setAddressLine1($data['address_line1']);
    $supplier->setAddressLine2($data['address_line2']);
    $supplier->setPostcode($data['postal_code']);



    $response = $supplier->requestUpdateProfileInfo();

    echo json_encode(['response' => $response]);
  }

}

// Includes igual que en tu SignUp
include "../../controller/config/database.php";
include "../../model/users.php";

$supplierInfo = new SupplierInfo(); // instancia
$supplierInfo->handleLogin();
