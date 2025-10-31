<?php
class SingUp {
  public function handleSignUp(){
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    switch ($data["action"]) {
      case 'requestSignUpSupplier':
        $this->signUpSupplier($data);
        break;

      default:
        break;
    }

  }
  private function signUpSupplier($data){
    $connection = new Database();
    $user = new Users($connection);

    $user->setName($data['name']);
    $user->setEmail($data['email']);
    $user->setPassword($data['password']);
    $user->setSignupCategory("normal");
    $user->setPhone($data['phone']);
    $user->setCompanyName($data['company_name']);
    $user->setCountry($data['country']);
    $user->setCity($data['city']);
    $user->setAddressLine1($data['address_line1']);
    $user->setAddressLine2($data['address_line2']);
    $user->setPostcode($data['postcode']);

    $userCreated = $user->createUser();


    // Normaliza para evitar offset de null
    if (!is_array($userCreated)) {
      $userCreated = ['response' => false];
    }

    // Convierte a booleano seguro
    $boolSendEmail = filter_var(
      $userCreated['response'] ?? null,
      FILTER_VALIDATE_BOOLEAN,
      FILTER_NULL_ON_FAILURE
    );

    // Enviar sólo si es verdadero
    if ($boolSendEmail === true) {
      $emailSender = new EmailSender();
      $emailSender->setRecipientEmail($data['email']);
      $emailSender->setRecipientName($data['name']);
      $emailSender->setRecipientPassword($data['password']);
      $emailSender->sendEmailRegistration();
    }

    echo json_encode ($userCreated);
  }


}


include "../../controller/users/send_emails.php";
include "../../controller/config/database.php";
include "../../model/users.php";
$signUpClass = new SingUp(); //intancia = ponerle nombre a la variable
$signUpClass->handleSignUp();
?>
