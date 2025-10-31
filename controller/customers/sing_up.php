<?php
class SingUp {
  public function handleSignUp(){
    $input = file_get_contents('php://input');
    $data  = json_decode($input, true);

    switch ($data["action"] ?? null) {
      case 'requestSignUp':
        $this->signUpCustomer($data);
        break;

      default:
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['response' => false, 'error' => 'Unsupported action']);
        break;
    }
  }

  private function signUpCustomer($data){
    header('Content-Type: application/json; charset=utf-8');

    $connection = new Database();
    $customer   = new Customers($connection);

    // Campos mínimos (coinciden con tu formulario JS)
    $customer->setName($data['name'] ?? '');
    $customer->setEmail($data['email'] ?? '');
    $customer->setPassword($data['password'] ?? ''); // el modelo debe hashear a password_hash

    // Opcionales si los envías
    if (isset($data['notes'])) { $customer->setNotes($data['notes']); }
    if (isset($data['group'])) { $customer->setGroup($data['group']); } // `group` existe en tu tabla

    // Debe devolver true/false
    $customerCreated = $customer->createCustomer();

    echo json_encode(['response' => (bool)$customerCreated]);
  }
}

include "../../controller/config/database.php";
include "../../model/customers.php";

$signUpClass = new SingUp(); // instancia
$signUpClass->handleSignUp();
