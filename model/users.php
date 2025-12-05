<?php
class Users {
  // Private variables
  private $connection; // The database connection
  private $name;      // User's name
  private $email;     // User's email
  private $password;  // User's password
  private $signup_category;  // User's signup category
  private $sku;     // sku

  // Nuevos atributos
  private $phone;
  private $company_name;
  private $country;
  private $city;
  private $address_line1;
  private $address_line2;
  private $postcode;

  // Constructor that initializes the connection.
  function __construct($connection) {
    $this->connection = $connection;
  }

  // Set the user's name
  public function setName($name) {
    $this->name = $name;
  }
  // Set the user's name
  public function setSKU($SKU) {
    $this->SKU = $SKU;
  }

  // Set the user's email.
  public function setEmail($email) {
    $this->email = $email;
  }

  // Set the user's password.
  public function setPassword($password) {
    $this->password = $password;
  }

  public function setSignupCategory($signup_category) {
      $this->signup_category = $signup_category;
  }

  // Nuevos setters
  public function setPhone($phone) {
      $this->phone = $phone;
  }

  public function setCompanyName($company_name) {
      $this->company_name = $company_name;
  }

  public function setCountry($country) {
      $this->country = $country;
  }

  public function setCity($city) {
      $this->city = $city;
  }

  public function setAddressLine1($address_line1) {
      $this->address_line1 = $address_line1;
  }

  public function setAddressLine2($address_line2) {
      $this->address_line2 = $address_line2;
  }

  public function setPostcode($postcode) {
      $this->postcode = $postcode;
  }



  public function getPasswordUserByEmail() {
    if (empty($this->email)) {
      return null; // Asegúrate de llamar antes a setEmail($email)
    }

    try {
      $pdo = $this->connection->getConnection();
      $sql = $pdo->prepare("SELECT `password`
        FROM `suppliers`
        WHERE `email` = :email
        LIMIT 1
      ");
      $sql->bindParam(':email', $this->email, PDO::PARAM_STR);
      $sql->execute();

      $row = $sql->fetch(PDO::FETCH_ASSOC);
      if (!$row || empty($row['password'])) {
        return null; // No existe usuario o no tiene password guardado
      }

      return (string)$row['password']; // Devuelve el hash (para password_verify)
    } catch (PDOException $e) {
      error_log('getPasswordUserByEmail error (' . $this->email . '): ' . $e->getMessage());
      return null; // No lances excepción para no romper el flujo de login
    }
  }


  /*
   * Crea un nuevo supplier con password (hash).
   * Mapea tus propiedades a las columnas de `suppliers`.
   */
   public function createUser() {
     try {
       // Si ya existe, responde y corta
       if ($this->checkIfUserExistsByEmail()) {
         return(['response' => false, 'error' => 'User already exists']);
       }



       // Hashear contraseña antes de guardar
       $passwordHash = password_hash($this->password, PASSWORD_DEFAULT);

       $sql = $this->connection->getConnection()->prepare("
         INSERT INTO `suppliers`
           (`contact_name`,`email`,`phone`,`company_name`,`country`,`city`,
            `address_line1`,`address_line2`,`postal_code`,`password`)
         VALUES
           (:contact_name,:email,:phone,:company_name,:country,:city,
            :address_line1,:address_line2,:postal_code,:password)
       ");

       $sql->bindParam(':contact_name',  $this->name,          PDO::PARAM_STR);
       $sql->bindParam(':email',         $this->email,         PDO::PARAM_STR);
       $sql->bindParam(':phone',         $this->phone,         PDO::PARAM_STR);
       $sql->bindParam(':company_name',  $this->company_name,  PDO::PARAM_STR);
       $sql->bindParam(':country',       $this->country,       PDO::PARAM_STR);
       $sql->bindParam(':city',          $this->city,          PDO::PARAM_STR);
       $sql->bindParam(':address_line1', $this->address_line1, PDO::PARAM_STR);
       $sql->bindParam(':address_line2', $this->address_line2, PDO::PARAM_STR);
       $sql->bindParam(':postal_code',   $this->postcode,      PDO::PARAM_STR);
       $sql->bindParam(':password',      $passwordHash,        PDO::PARAM_STR);

       $sql->execute();

       $this->connection->closeConnection();

       return (['response' => true]);
       return;
     } catch (PDOException $e) {
       return(['response' => false, 'error' => 'DB error']);
       return;
     }
   }




   public function checkIfUserExistsByEmail() {
     if (empty($this->email)) {
       return false;
     }

     try {
       $pdo = $this->connection->getConnection();
       $stmt = $pdo->prepare("SELECT 1 FROM `suppliers` WHERE `email` = :email LIMIT 1");
       $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
       $stmt->execute();

        return $stmt->fetchColumn() !== false;
     } catch (PDOException $e) {
       error_log('checkIfUserExistsByEmail error (' . $this->email . '): ' . $e->getMessage());
       return false;
     }
   }

   public function getUserCompanyBySKU() {
       // Usamos el SKU que se haya seteado previamente
       $sku = trim((string)($this->SKU ?? $this->sku ?? ''));

       if ($sku === '') {
           return null; // Asegúrate de llamar antes a setSKU($sku)
       }

       try {
           $pdo = $this->connection->getConnection();

           $stmt = $pdo->prepare("
               SELECT
                   s.company_name
               FROM products p
               INNER JOIN suppliers s
                   ON s.supplier_id = p.supplier_id
               WHERE p.SKU = :sku
               LIMIT 1
           ");

           $stmt->execute([':sku' => $sku]);
           $row = $stmt->fetch(PDO::FETCH_ASSOC);

           // Devuelve los datos del supplier + algo del producto, o null si no se encuentra
           return $row ?: null;

       } catch (PDOException $e) {
           error_log('getUserCompanyBySKU error (SKU ' . $sku . '): ' . $e->getMessage());
           return null;
       }
   }

   public function requestProfileInfo() {
     if (empty($this->email)) return null;

     try {
       $pdo = $this->connection->getConnection();
       // Asegúrate de tener PDO::ERRMODE_EXCEPTION en tu conexión
       $stmt = $pdo->prepare("
         SELECT contact_name, email, phone,
                company_name, country, city,
                address_line1, address_line2, postal_code
         FROM suppliers
         WHERE email = :email
         LIMIT 1
       ");
       $stmt->execute([':email' => $this->email]);
       $row = $stmt->fetch(PDO::FETCH_ASSOC);

       return $row ?: null; // null si no existe
     } catch (PDOException $e) {
       error_log('requestProfileInfo error (' . $this->email . '): ' . $e->getMessage());
       return null;
     }
   }




   public function requestUpdateProfileInfo() {
  if (empty($this->email)) return false; // clave de búsqueda

  try {
    $pdo = $this->connection->getConnection();
    // Asegúrate de tener PDO::ERRMODE_EXCEPTION en tu conexión

    $stmt = $pdo->prepare("
      UPDATE suppliers
      SET
        contact_name  = :contact_name,
        phone         = :phone,
        company_name  = :company_name,
        country       = :country,
        city          = :city,
        address_line1 = :address_line1,
        address_line2 = :address_line2,
        postal_code   = :postal_code
      WHERE email = :email
      LIMIT 1
    ");

    $stmt->execute([
      ':contact_name'  => $this->name ?? null,
      ':phone'         => $this->phone ?? null,
      ':company_name'  => $this->company_name ?? null,
      ':country'       => $this->country ?? null,
      ':city'          => $this->city ?? null,
      ':address_line1' => $this->address_line1 ?? null,
      ':address_line2' => $this->address_line2 ?? null,
      ':postal_code'   => $this->postcode ?? null, // ojo: setter setPostcode → columna postal_code
      ':email'         => $this->email
    ]);

    // rowCount() puede ser 0 si no hubo cambios, pero la ejecución fue correcta.
    return true;
  } catch (PDOException $e) {
    error_log('requestUpdateProfileInfo error (' . $this->email . '): ' . $e->getMessage());
    return false;
  }
}






}
?>
