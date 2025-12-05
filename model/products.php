<?php
class Products {
  /** @var Database $connection Debe exponer getConnection(): PDO */
  private $connection;

  /** Atributos del modelo (coinciden con columnas) */
  private $product_id;   // int
  private $sku;          // string (<= 50)
  private $name;         // string (<= 150)
  private $description;  // text
  private $pd_tagline;  // text
  private $status;       // string (<= 50)
  private $category_id;  // int|null
  private $supplier_id;  // int
  private $email; // string|null


  public function __construct($connection) {
    $this->connection = $connection;
  }

  /* ===========================
     Setters
     =========================== */
  public function setId($id)            { $this->product_id  = (int)$id; }
  public function setSku($sku)          { $this->sku         = $this->normalizeText($sku); }
  public function setName($name)        { $this->name        = $this->normalizeText($name); }
  public function setDescription($desc)  { $this->description = is_string($desc) ? trim($desc) : null; }
  public function setTaglineDescription($pd_tagline)  { $this->pd_tagline = is_string($pd_tagline) ? trim($pd_tagline) : null; }
  public function setStatus($status)    { $this->status      = $this->normalizeText($status); }
  public function setCategoryId($id)    { $this->category_id = ($id === null || $id === '') ? null : (int)$id; }
  public function setSupplierId($id)    { $this->supplier_id = (int)$id; }
  public function setEmail($email) { $this->email = ($email === null || $email === '') ? null : strtolower(trim((string)$email)); }

  /** Normaliza strings (trim + colapsa espacios) */
  private function normalizeText($s) {
    $s = is_string($s) ? trim($s) : '';
    return preg_replace('/\s+/', ' ', $s);
  }

  /** Verifica si ya existe un producto con el mismo SKU para el mismo proveedor (case-insensitive) */
  private function existsBySkuForSupplier($sku, $supplierId) {
    try {
      $pdo = $this->connection->getConnection();
      $sql = "SELECT 1
                FROM products
               WHERE LOWER(sku) = LOWER(:sku)
                 AND supplier_id = :supplier_id
               LIMIT 1";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':sku' => $sku,
        ':supplier_id' => $supplierId
      ]);
      return $stmt->fetchColumn() !== false;
    } catch (PDOException $e) {
      error_log('existsBySkuForSupplier error: ' . $e->getMessage());
      return false;
    }
  }


  public function getSupplierDetailsBySKU(): ?array
  {
      if (empty($this->sku)) {
          return null;
      }


      try {
          $pdo = $this->connection->getConnection();

          $sql = "SELECT
                  s.supplier_id AS supplier_id,
                  s.contact_name AS supplier_name
              FROM products p
              INNER JOIN suppliers s  ON s.supplier_id = p.supplier_id
              LEFT  JOIN variations v ON v.product_id  = p.product_id
              WHERE p.SKU = :sku OR v.SKU = :sku
              LIMIT 1
          ";

          $stmt = $pdo->prepare($sql);
          $stmt->execute([':sku' => $this->sku]);

          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          return $row ?: null;

      } catch (PDOException $e) {
          error_log('getSupplierDetailsBySKU error: '.$e->getMessage());
          return null;
      }
  }



  /* ===========================
     CREATE: crea con sku + supplier_id
     =========================== */
     // 3) create() MINIMAL, resolviendo supplier_id por email y usando `SKU`
     public function create() {
       // Validaciones mínimas
       if ($this->sku === null || $this->sku === '') {
         return ['success' => false, 'error' => 'SKU required'];
       }
       if (mb_strlen($this->sku) > 50) {
         return ['success' => false, 'error' => 'SKU too long'];
       }

       try {
         $pdo = $this->connection->getConnection();

         // Resolver supplier_id por email si no vino seteado
         if (empty($this->supplier_id)) {
           if (!$this->email) {
             return ['success' => false, 'error' => 'Email required to resolve supplier'];
           }
           $q = $pdo->prepare("SELECT supplier_id
                               FROM suppliers
                               WHERE LOWER(email) = LOWER(:email)
                               LIMIT 1");
           $q->execute([':email' => $this->email]);
           $sid = $q->fetchColumn();
           if ($sid === false) {
             return ['success' => false, 'error' => 'Supplier not found for email'];
           }
           $this->supplier_id = (int)$sid;
         }

         // Verificar duplicado (asegúrate que existsBySkuForSupplier use la columna `SKU`)
         if ($this->existsBySkuForSupplier($this->sku, $this->supplier_id)) {
           return ['success' => false, 'error' => 'Product already exists for this supplier'];
         }


         // Inserción mínima: SKU + supplier_id
         $stmt = $pdo->prepare("INSERT INTO products (`SKU`, `supplier_id`)
           VALUES (:sku, :supplier_id)
         ");

         $stmt->execute([
           ':sku'         => $this->sku,
           ':supplier_id' => $this->supplier_id
         ]);



         $newId = (int)$pdo->lastInsertId();
         return [
           'success'      => true,
           'id'           => $newId,
           'sku'          => $this->sku,
           'supplier_id'  => $this->supplier_id
         ];
       } catch (PDOException $e) {
         error_log('create product error: ' . $e->getMessage());
         return ['success' => false, 'error' => 'DB error'];
       }
     }


     public function getProductsBasicBySupplierEmail() {
       if (empty($this->email)) {
         return json_encode(['success'=>false,'error'=>'Email required'], JSON_UNESCAPED_UNICODE);
       }

       try {
         $pdo = $this->connection->getConnection();

         $sql = "SELECT
                   p.`SKU`  AS sku,
                   p.`name` AS product_name,
                   COALESCE(c.`name`, '') AS category_name,
                   p.`status` AS status,
                   vdef.`SKU` AS default_variation_sku
                 FROM products p
                 INNER JOIN suppliers s
                   ON s.supplier_id = p.supplier_id
                 LEFT JOIN categories c
                   ON c.category_id = p.category_id
                 LEFT JOIN variations vdef
                   ON vdef.product_id = p.product_id
                  AND LOWER(vdef.`name`) = 'default'
                  AND (vdef.parent_id IS NULL OR vdef.parent_id = 0)
                 WHERE LOWER(s.email) = LOWER(:email)
                 ORDER BY p.`name` ASC";

         $stmt = $pdo->prepare($sql);
         $stmt->execute([':email' => $this->email]);
         $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

         return json_encode(['success'=>true,'data'=>$rows], JSON_UNESCAPED_UNICODE);

       } catch (PDOException $e) {
         error_log('getProductsBasicBySupplierEmail error: '.$e->getMessage());
         return json_encode(['success'=>false,'error'=>'DB error'], JSON_UNESCAPED_UNICODE);
       }
     }


  /* ===========================
     UPDATE (lote): name, description, status, category_id
     Solo actualiza los campos provistos (no null)
     =========================== */
     public function update(): array
     {


       // Validaciones básicas
       $sku = trim((string)($this->sku ?? ''));
       if ($sku === '' || mb_strlen($sku) > 50) {
         return ['success' => false, 'error' => 'SKU required/invalid'];
       }

       try {
         $pdo = $this->connection->getConnection();
         $sql = "UPDATE products
                   SET name        = COALESCE(:name, name),
                       description = COALESCE(:description, description),
                       descriptive_tagline = COALESCE(:descriptive_tagline, descriptive_tagline),
                       status      = COALESCE(:status, status)
                 WHERE SKU = :sku
                 LIMIT 1";
         // Si tu colación fuese case-sensitive, usa:
         // WHERE SKU COLLATE utf8mb4_general_ci = :sku
         //echo json_encode($this->pd_tagline."ssss");exit;

         $stmt = $pdo->prepare($sql);
         $stmt->execute([
           ':name'        => $this->name,
           ':description' => $this->description,
           ':descriptive_tagline' => $this->pd_tagline,
           ':status'      => $this->status,
           ':sku'         => $sku,
         ]);

         return ['success'=>true,'updated'=>$stmt->rowCount()];
       } catch (PDOException $e) {
         error_log('update product by SKU error: '.$e->getMessage());
         return ['success'=>false,'error'=>'DB error'];
       }
     }



  /* ===========================
     UPDATEs individuales
     =========================== */

  public function updateName($id, $name) {
    $name = $this->normalizeText($name);
    if ($name === '') return ['success' => false, 'error' => 'Name required'];
    if (mb_strlen($name) > 150) return ['success' => false, 'error' => 'Name too long'];

    try {
      $pdo = $this->connection->getConnection();
      $stmt = $pdo->prepare("UPDATE products SET name = :name WHERE product_id = :id LIMIT 1");
      $stmt->execute([':name' => $name, ':id' => (int)$id]);
      return ['success' => true, 'updated' => $stmt->rowCount()];
    } catch (PDOException $e) {
      error_log('updateName error: ' . $e->getMessage());
      return ['success' => false, 'error' => 'DB error'];
    }
  }

  public function updateDescription($id, $description) {
    $description = is_string($description) ? trim($description) : null;
    try {
      $pdo = $this->connection->getConnection();
      $stmt = $pdo->prepare("UPDATE products SET description = :description WHERE product_id = :id LIMIT 1");
      $stmt->execute([':description' => $description, ':id' => (int)$id]);
      return ['success' => true, 'updated' => $stmt->rowCount()];
    } catch (PDOException $e) {
      error_log('updateDescription error: ' . $e->getMessage());
      return ['success' => false, 'error' => 'DB error'];
    }
  }

  public function updateStatus($id, $status) {
    $status = $this->normalizeText($status);
    if ($status !== '' && mb_strlen($status) > 50) {
      return ['success' => false, 'error' => 'Status too long'];
    }
    try {
      $pdo = $this->connection->getConnection();
      $stmt = $pdo->prepare("UPDATE products SET status = :status WHERE product_id = :id LIMIT 1");
      $stmt->execute([':status' => $status, ':id' => (int)$id]);
      return ['success' => true, 'updated' => $stmt->rowCount()];
    } catch (PDOException $e) {
      error_log('updateStatus error: ' . $e->getMessage());
      return ['success' => false, 'error' => 'DB error'];
    }
  }

  public function updateCategoryIdBySKU($sku, $categoryId): array
  {
  //  echo json_encode($sku. $categoryId);exit;

    $sku = trim((string)$sku);
    if ($sku === '' || strlen($sku) > 50) {
      return ['success' => false, 'error' => 'Invalid SKU'];
    }

    $categoryId = ($categoryId === null || $categoryId === '' || (int)$categoryId <= 0)
        ? null
        : (int)$categoryId;

    try {
      $pdo  = $this->connection->getConnection();
      $stmt = $pdo->prepare("UPDATE products SET category_id = :category_id WHERE SKU = :sku LIMIT 1");
      $stmt->bindValue(':sku', $sku, PDO::PARAM_STR);
      $categoryId === null
        ? $stmt->bindValue(':category_id', null, PDO::PARAM_NULL)
        : $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);

      $stmt->execute();
      return ['success' => true, 'updated' => $stmt->rowCount()];
    } catch (PDOException $e) {
      error_log('updateCategoryIdBySKU error: '.$e->getMessage());
      return ['success' => false, 'error' => 'DB error'];
    }
  }



  public function getProductBasicBySKU(): string {
    if (empty($this->sku)) {
      return json_encode(['success' => false, 'error' => 'SKU required'], JSON_UNESCAPED_UNICODE);
    }

    try {
      $pdo = $this->connection->getConnection();

      $sql = "SELECT p.name, p.description, p.status, p.descriptive_tagline
              FROM products p
              WHERE p.SKU = :sku
              LIMIT 1";
      // Si tu colación fuera case-sensitive:
      // $sql = "SELECT name, description, status FROM products
      //         WHERE SKU COLLATE utf8mb4_general_ci = :sku LIMIT 1";

      $stmt = $pdo->prepare($sql);
      $stmt->execute([':sku' => trim($this->sku)]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$row) {
        return json_encode(['success' => false, 'error' => 'SKU not found'], JSON_UNESCAPED_UNICODE);
      }

      return json_encode(['success' => true, 'data' => $row], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
      error_log('getProductBasicBySKU error: ' . $e->getMessage());
      return json_encode(['success' => false, 'error' => 'DB error'], JSON_UNESCAPED_UNICODE);
    }
  }

  public function getProductDetailsBySKU(): ?array
  {
      // Validar SKU
      $sku = trim((string)($this->sku ?? ''));
      if ($sku === '' || mb_strlen($sku) > 50) {
          return null;
      }

      try {
          $pdo = $this->connection->getConnection();

          $sql = "
              SELECT
                  p.SKU              AS sku,
                  p.name             AS product_name,
                  p.description,
                  p.descriptive_tagline,
                  p.status
              FROM products p
              WHERE p.SKU = :sku
              LIMIT 1
          ";

          $stmt = $pdo->prepare($sql);
          $stmt->execute([':sku' => $sku]);
          $row = $stmt->fetch(PDO::FETCH_ASSOC);

          if (!$row) {
              return null; // SKU no encontrado
          }

          // Envolvemos en product_details como pediste
          return ['product_details' => $row];

      } catch (PDOException $e) {
          error_log('getProductDetailsBySKU error (SKU '.$sku.'): '.$e->getMessage());
          return null;
      }
  }







  /* ===========================
     Delete (opcional)
     =========================== */
/*  public function delete() {
    if (empty($this->product_id)) {
      return ['success' => false, 'error' => 'ID required'];
    }
    try {
      $pdo = $this->connection->getConnection();
      $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = :id LIMIT 1");
      $stmt->execute([':id' => $this->product_id]);
      return ['success' => true, 'deleted' => $stmt->rowCount()];
    } catch (PDOException $e) {
      error_log('delete product error: ' . $e->getMessage());
      return ['success' => false, 'error' => 'DB error'];
    }
  }*/
}
?>
