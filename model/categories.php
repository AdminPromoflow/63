<?php
class Categories {
  /** @var Database $connection Debe exponer getConnection(): PDO */
  private $connection;

  /** Atributos del modelo */
  private $category_id; // int
  private $name;        // string
  private $sku;        // string


  public function __construct($connection) {
    $this->connection = $connection;
  }

  /** Setters */
  public function setId($id) { $this->category_id = (int)$id; }
  public function setSKU($sku) { $this->sku = $sku; }
  public function setName($name) { $this->name = $this->normalizeName($name); }

  /** Normaliza nombre (trim + colapsa espacios) */
  private function normalizeName($s) {
    $s = is_string($s) ? trim($s) : '';
    return preg_replace('/\s+/', ' ', $s);
  }


  public function getCategoryIdByName()
  {
      try {
          // Usa la variable global (propiedad de clase)
          $name = isset($this->name) ? trim($this->name) : null;
          if (!$name) {
              return false; // no hay nombre, no se puede buscar
          }

          $pdo = $this->connection->getConnection();

          $stmt = $pdo->prepare("SELECT category_id
              FROM categories
              WHERE LOWER(name) = LOWER(:name)
              LIMIT 1
          ");
          $stmt->execute([':name' => $name]);

          // Retorna el ID si existe o false si no hay coincidencia
          $categoryId = $stmt->fetchColumn();
          return $categoryId !== false ? (int)$categoryId : false;

      } catch (PDOException $e) {
          error_log('getCategoryIdByName error: ' . $e->getMessage());
          return false;
      }
  }

  public function getCategorySelected(): string {
    // 1) Sanitiza el SKU de entrada
    $sku = trim((string)$this->sku);
    if ($sku === '' || strlen($sku) > 50) {
      return json_encode(['success' => false, 'error' => 'SKU required/invalid'], JSON_UNESCAPED_UNICODE);
    }

    try {
      $pdo = $this->connection->getConnection();

      // 2) Busca el category_id por SKU (case-insensitive según colación)
      //    Si tu DB ya es *_ci, bastaría "WHERE p.SKU = :sku".
      $sql = "
        SELECT p.category_id
        FROM products p
        WHERE p.SKU COLLATE utf8mb4_general_ci = :sku
        LIMIT 1
      ";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([':sku' => $sku]);

      // 3) Usa fetch() para distinguir claramente NULL vs false
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($row === false) {
        // No existe el SKU
        return json_encode(['success' => false, 'error' => 'SKU not found'], JSON_UNESCAPED_UNICODE);
      }

      // 4) Aquí sí puede venir NULL legítimo en category_id (tu esquema lo permite)
      //    Si es NULL lo devolvemos tal cual; si es numérico (incluido 0), lo casteamos a int.
      $cat = array_key_exists('category_id', $row) ? $row['category_id'] : null;

      return json_encode([
        'success'      => true,
        'category_id'  => ($cat === null ? null : (int)$cat)
      ], JSON_UNESCAPED_UNICODE);

    } catch (\PDOException $e) {
      error_log('getCategorySelected error: '.$e->getMessage());
      return json_encode(['success' => false, 'error' => 'DB error'], JSON_UNESCAPED_UNICODE);
    }
  }



  /** Verifica si existe una categoría con el mismo nombre (case-insensitive) */
  private function existsByName($name) {
    try {
      $pdo = $this->connection->getConnection();
      $stmt = $pdo->prepare("SELECT 1
        FROM categories
        WHERE LOWER(name) = LOWER(:name)
        LIMIT 1
      ");
      $stmt->execute([':name' => $name]);
      return $stmt->fetchColumn() !== false;
    } catch (PDOException $e) {
      error_log('existsByName error: ' . $e->getMessage());
      return false;
    }
  }

  /** Crea una categoría (primero verifica duplicado por nombre) */
  public function create() {
    if ($this->name === null || $this->name === '') {
      return ['success' => false, 'error' => 'Name required'];
    }
    if (mb_strlen($this->name) > 150) {
      return ['success' => false, 'error' => 'Name too long'];
    }

    try {
      if ($this->existsByName($this->name)) {
        return ['success' => false, 'error' => 'Category already exists'];
      }

      $pdo = $this->connection->getConnection();
      $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
      $stmt->execute([':name' => $this->name]);

      $newId = (int)$pdo->lastInsertId();
      return ['success' => true, 'id' => $newId, 'name' => $this->name];
    } catch (PDOException $e) {
      // Si tienes UNIQUE en name, puedes capturar código 1062 (duplicate)
      error_log('create category error: ' . $e->getMessage());
      return ['success' => false, 'error' => 'DB error'];
    }
  }

  /** Actualiza una categoría por ID (valida duplicado de nombre en otro ID) */
  public function update() {
    if (empty($this->category_id)) {
      return ['success' => false, 'error' => 'ID required'];
    }
    if ($this->name === null || $this->name === '') {
      return ['success' => false, 'error' => 'Name required'];
    }
    if (mb_strlen($this->name) > 150) {
      return ['success' => false, 'error' => 'Name too long'];
    }

    try {
      $pdo = $this->connection->getConnection();

      // ¿Existe el mismo nombre en otro ID?
      $dup = $pdo->prepare("SELECT category_id
        FROM categories
        WHERE LOWER(name) = LOWER(:name) AND category_id <> :id
        LIMIT 1
      ");
      $dup->execute([':name' => $this->name, ':id' => $this->category_id]);
      if ($dup->fetch(PDO::FETCH_ASSOC)) {
        return ['success' => false, 'error' => 'Category name already in use'];
      }

      $stmt = $pdo->prepare("UPDATE categories
        SET name = :name
        WHERE category_id = :id
        LIMIT 1
      ");
      $stmt->execute([':name' => $this->name, ':id' => $this->category_id]);

      // rowCount puede ser 0 si el nombre es igual al anterior
      return ['success' => true, 'updated' => $stmt->rowCount()];
    } catch (PDOException $e) {
      error_log('update category error: ' . $e->getMessage());
      return ['success' => false, 'error' => 'DB error'];
    }
  }

  /** Elimina una categoría por ID */
  public function delete() {
    if (empty($this->category_id)) {
      return ['success' => false, 'error' => 'ID required'];
    }

    try {
      $pdo = $this->connection->getConnection();
      $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = :id LIMIT 1");
      $stmt->execute([':id' => $this->category_id]);

      return ['success' => true, 'deleted' => $stmt->rowCount()];
    } catch (PDOException $e) {
      // Si hay FK a products.category_id, aquí podría fallar: manejar según tu lógica
      error_log('delete category error: ' . $e->getMessage());
      return ['success' => false, 'error' => 'DB error'];
    }
  }

  /**
   * Devuelve todas las categorías (solo nombres).
   * @return array ['Art', 'Accessories', ...]
   */
   public function getAllNames() {
     try {
       $pdo = $this->connection->getConnection();
       $sql = "SELECT
                 c.category_id,
                 c.name,
                 COUNT(p.product_id) AS products_count
               FROM categories c
               LEFT JOIN products p
                 ON p.category_id = c.category_id
               GROUP BY c.category_id, c.name
               ORDER BY c.category_id ASC";
       $stmt = $pdo->query($sql);
       $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

       return json_encode(['success' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
     } catch (PDOException $e) {
       error_log('getAllNames error: ' . $e->getMessage());
       return json_encode(['success' => false, 'error' => 'DB error'], JSON_UNESCAPED_UNICODE);
     }
   }

}
?>
