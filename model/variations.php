<?php
class Variation {
  // ===== Atributos =====
  private $connection;          // PDO wrapper (->getConnection())
  private $product_id;          // FK al producto (padre)
  private $name = null;
  private $sku  = null;
  private $sku_variation = null;
  private $sku_parent_variation = null;
  private $image = null;
  private $pdf_artwork = null;
  private $isAttachAnImage;
  private $isAttachAPDF;



  // ===== Constructor =====
  public function __construct($connection) { $this->connection = $connection; }

  // ===== Setters =====
  public function setId($id)              { $this->product_id = (int)$id; }


  public function setIsAttachAnImage($isAttachAnImage): void
  {
      // true para: true, 1, "1", "true", "on", "yes" (case-insensitive)
      // false para: false, 0, "0", "false", "off", "no", "", null, valores no reconocidos
      $this->isAttachAnImage = filter_var(
          $isAttachAnImage,
          FILTER_VALIDATE_BOOLEAN,
          FILTER_NULL_ON_FAILURE
      ) === true;
  }

  public function setIsAttachAPDF($isAttachAPDF): void
  {
      $this->isAttachAPDF = filter_var(
          $isAttachAPDF,
          FILTER_VALIDATE_BOOLEAN,
          FILTER_NULL_ON_FAILURE
      ) === true;
  }



  public function setName(?string $v)     { $v = trim((string)$v); $this->name = ($v === '') ? null : $v; }
  public function setSKU(?string $v)      { $v = trim((string)$v); $this->sku  = ($v === '') ? null : $v; }
  public function setSKUParentVariation(?string $v)      { $v = trim((string)$v); $this->sku_parent_variation  = ($v === '') ? null : $v; }
  public function setSKUVariation(?string $v)      { $v = trim((string)$v); $this->sku_variation  = ($v === '') ? null : $v; }
  public function setImage(?string $v)    { $v = trim((string)$v); $this->image = ($v === '') ? null : $v; }
  public function setPdfArtwork(?string $v){ $v = trim((string)$v); $this->pdf_artwork = ($v === '') ? null : $v; }

  public function checkProductAndVariationExistenceBySkus(): bool
  {
      if (empty($this->sku) || empty($this->sku_variation)) {
          return false;
      }

      try {
          $pdo = $this->connection->getConnection();

          $stmt = $pdo->prepare("SELECT product_id FROM products WHERE SKU = :sku LIMIT 1");
          $stmt->execute([':sku' => $this->sku]);
          $product = $stmt->fetch(PDO::FETCH_ASSOC);
          if (!$product) {
              return false;
          }
          $productId = (int)$product['product_id'];

          $stmt = $pdo->prepare("SELECT variation_id FROM variations WHERE SKU = :vsku AND product_id = :pid LIMIT 1");
          $stmt->execute([':vsku' => $this->sku_variation, ':pid' => $productId]);
          $variation = $stmt->fetch(PDO::FETCH_ASSOC);

          return (bool)$variation;

      } catch (PDOException $e) {
          error_log('getVariationDetailsBySkus error: ' . $e->getMessage());
          return false;
      }
  }

  public function getVariationDetailsBySkus(): array
  {
      // 0) Validaciones mínimas
      if (!$this->sku) {
          return ['success' => false, 'error' => 'Product SKU requerido'];
      }
      if (!$this->sku_variation) {
          return ['success' => false, 'error' => 'Variation SKU (sku_variation) requerido'];
      }

      try {
          $pdo = $this->connection->getConnection();

          // 1) Obtener product_id, name y SKU desde products por SKU de producto
          $stmt = $pdo->prepare("
              SELECT product_id, name AS product_name, SKU AS product_sku
              FROM products
              WHERE SKU = :sku
              LIMIT 1
          ");
          $stmt->execute([':sku' => $this->sku]);
          $prod = $stmt->fetch(PDO::FETCH_ASSOC);

          if (!$prod) {
              return ['success' => false, 'error' => 'Producto no encontrado por SKU'];
          }

          $productId   = (int)$prod['product_id'];
          $productName = $prod['product_name'];
          $productSku  = $prod['product_sku'];

          // 2) Variación actual + datos del padre (name/sku) en una sola consulta
          $stmt = $pdo->prepare("
              SELECT
                v.name,
                v.image,
                v.SKU,
                v.pdf_artwork,
                v.parent_id,
                p.name AS parent_name,
                p.SKU  AS parent_sku
              FROM variations v
              LEFT JOIN variations p ON p.variation_id = v.parent_id
              WHERE v.product_id = :pid AND v.SKU = :vsku
              LIMIT 1
          ");
          $stmt->execute([':pid' => $productId, ':vsku' => $this->sku_variation]);
          $row = $stmt->fetch(PDO::FETCH_ASSOC);

          if (!$row) {
              return ['success' => false, 'error' => 'Variation SKU no pertenece al producto dado o no existe'];
          }

          // 3) Listar todas las variaciones del producto (name, SKU)
          $stmt = $pdo->prepare("
              SELECT name, SKU
              FROM variations
              WHERE product_id = :pid
              ORDER BY name ASC
          ");
          $stmt->execute([':pid' => $productId]);
          $variations = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

          // 4) Respuesta final (sin json_encode)
          return [
              'success'    => true,
              'product'    => [
                  'product_id'   => $productId,
                  'product_name' => $productName,
                  'product_sku'  => $productSku,
              ],
              'variations' => $variations, // cada item: ['name'=>..., 'SKU'=>...]
              'current'    => [
                  'name'        => $row['name'],
                  'image'       => $row['image'] ?? null,
                  'sku'         => $row['SKU'],
                  'pdf_artwork' => $row['pdf_artwork'] ?? null,
                  'parent_id'   => $row['parent_id'] ? (int)$row['parent_id'] : null,
              ],
              // ← name y sku del parent_id (puede ser null si no tiene padre)
              'parent'     => [
                  'name' => $row['parent_name'] ?? null,
                  'sku'  => $row['parent_sku']  ?? null,
              ],
          ];

      } catch (PDOException $e) {
          error_log('getVariationDetailsBySkus: '.$e->getMessage());
          return ['success' => false, 'error' => 'DB error'];
      }
  }



  public function createDefaultVariation(): array
  {
    if (empty($this->product_id) || $this->product_id <= 0) {
      return ['success' => false, 'error' => 'product_id required'];
    }

    try {
      $pdo = $this->connection->getConnection();

      $name = $this->name ?? 'Default';
      $sku  = $this->sku ?? null;       // si no seteaste SKU, quedará null
      $img  = $this->image ?? null;
      $pdf  = $this->pdf_artwork ?? null;

      $stmt = $pdo->prepare("
        INSERT INTO variations (name, SKU, image, pdf_artwork, product_id)
        VALUES (:name, :sku, :image, :pdf, :pid)
      ");
      $stmt->execute([
        ':name'  => $name,
        ':sku'   => $sku,
        ':image' => $img,
        ':pdf'   => $pdf,
        ':pid'   => $this->product_id,
      ]);

      // Éxito: retorna el SKU insertado
      return ['success' => true, 'sku_variation' => $sku];

    } catch (PDOException $e) {
      error_log('createDefaultVariation error (product_id '.$this->product_id.'): '.$e->getMessage());
      return ['success' => false, 'error' => 'DB error'];
    }
  }

  // En tu modelo Variation
  public function updateVariationDetails(): bool
  {
      // 1) SKU objetivo (la variación que vamos a actualizar)
      $targetSku = trim((string)($this->sku_variation ?? ''));
      if ($targetSku === '') {
          return false; // Esto está bien
      }

      try {
          $pdo = $this->connection->getConnection();
          $pdo->beginTransaction();

          // 2) parent_id obligatorio: buscar por SKU del padre (variación)
          $stmt = $pdo->prepare("SELECT variation_id
                FROM variations
               WHERE SKU = :parentSku
               LIMIT 1
          ");
          $stmt->execute([':parentSku' => (string)$this->sku_parent_variation]);
          $parentId = $stmt->fetch(\PDO::FETCH_COLUMN);

          // 3) Si no retorna parentId: buscar la variación 'default' por NOMBRE dentro del mismo producto del target
          if ($parentId === false) {
              // 3.a) Obtener product_id de la variación objetivo
              $stmt = $pdo->prepare("SELECT product_id
                    FROM variations
                   WHERE SKU = :sku
                   LIMIT 1
              ");
              $stmt->execute([':sku' => $targetSku]);
              $pid = $stmt->fetch(\PDO::FETCH_COLUMN);
              if ($pid === false) {
                  $pdo->rollBack();
                  return false; // no se pudo determinar el producto del target
              }

              // 3.b) Buscar variación cuyo nombre contenga 'default' (case-insensitive) en ese producto
              $stmt = $pdo->prepare("SELECT variation_id
                    FROM variations
                   WHERE product_id = :pid
                     AND LOWER(name) LIKE :like
                   ORDER BY variation_id ASC
                   LIMIT 1
              ");
              $stmt->execute([
                  ':pid'  => (int)$pid,
                  ':like' => '%default%',
              ]);
              $parentId = $stmt->fetch(\PDO::FETCH_COLUMN);

              // 4) Si tampoco se encuentra, abortar
              if ($parentId === false) {
                  $pdo->rollBack();
                  return false;
              }

              $parentId = (int)$parentId; // normalizar a int
          } else {
              $parentId = (int)$parentId; // normalizar a int cuando sí existía por SKU padre
          }

          // 5) UPDATE por SKU de la variación objetivo




          if ($this->isAttachAnImage && $this->isAttachAPDF) {

            $stmt = $pdo->prepare("UPDATE variations
                   SET name        = :name,
                       image       = :image,
                       pdf_artwork = :pdf_artwork,
                       parent_id   = :parent_id
                 WHERE SKU = :sku
                 LIMIT 1
            ");

            $stmt->bindValue(':name',        (string)($this->name ?? ''),        \PDO::PARAM_STR);
            $stmt->bindValue(':image',       (string)($this->image ?? ''),       \PDO::PARAM_STR); // TEXT
            $stmt->bindValue(':pdf_artwork', (string)($this->pdf_artwork ?? ''), \PDO::PARAM_STR); // TEXT
            $stmt->bindValue(':parent_id',   $parentId,                          \PDO::PARAM_INT);
            $stmt->bindValue(':sku',         $targetSku,                         \PDO::PARAM_STR);
          //  echo json_encode($this->isAttachAnImage .$this->isAttachAPDF);exit;

          }
          elseif ($this->isAttachAnImage && !$this->isAttachAPDF) {
            $stmt = $pdo->prepare("UPDATE variations
                   SET name        = :name,
                       image       = :image,
                       parent_id   = :parent_id
                 WHERE SKU = :sku
                 LIMIT 1
            ");

            $stmt->bindValue(':name',        (string)($this->name ?? ''),        \PDO::PARAM_STR);
            $stmt->bindValue(':image',       (string)($this->image ?? ''),       \PDO::PARAM_STR); // TEXT
            $stmt->bindValue(':parent_id',   $parentId,                          \PDO::PARAM_INT);
            $stmt->bindValue(':sku',         $targetSku,                         \PDO::PARAM_STR);

          }
          elseif (!$this->isAttachAnImage && $this->isAttachAPDF) {
            $stmt = $pdo->prepare("UPDATE variations
                   SET name        = :name,
                       pdf_artwork = :pdf_artwork,
                       parent_id   = :parent_id
                 WHERE SKU = :sku
                 LIMIT 1
            ");

            $stmt->bindValue(':name',        (string)($this->name ?? ''),        \PDO::PARAM_STR);
            $stmt->bindValue(':pdf_artwork', (string)($this->pdf_artwork ?? ''), \PDO::PARAM_STR); // TEXT
            $stmt->bindValue(':parent_id',   $parentId,                          \PDO::PARAM_INT);
            $stmt->bindValue(':sku',         $targetSku,                         \PDO::PARAM_STR);

          }
          elseif (!$this->isAttachAnImage && !$this->isAttachAPDF) {
            $stmt = $pdo->prepare("UPDATE variations
                   SET name        = :name,
                       parent_id   = :parent_id
                 WHERE SKU = :sku
                 LIMIT 1
            ");

            $stmt->bindValue(':name',        (string)($this->name ?? ''),        \PDO::PARAM_STR);
            $stmt->bindValue(':parent_id',   $parentId,                          \PDO::PARAM_INT);
            $stmt->bindValue(':sku',         $targetSku,                         \PDO::PARAM_STR);

          }

          $ok = $stmt->execute();
          $pdo->commit();

          return $ok; // true si ejecutó correctamente (aunque no cambie filas)
      } catch (\PDOException $e) {
          if (isset($pdo)) { $pdo->rollBack(); }
          // error_log('updateVariationDetails error (sku '.$targetSku.'): '.$e->getMessage());
          return false;
      }
  }




  public function createEmptyVariationByProductSku(): array
  {
      // Requiere: $this->sku  (SKU del producto)  y  $this->sku_variation (SKU de la variación)
      $productSku   = isset($this->sku) ? trim((string)$this->sku) : '';
      $variationSku = isset($this->sku_variation) ? trim((string)$this->sku_variation) : '';

      if ($productSku === '') {
          return ['success' => false, 'error' => 'Product SKU requerido'];
      }
      if ($variationSku === '') {
          return ['success' => false, 'error' => 'Variation SKU requerido'];
      }

      try {
          $pdo = $this->connection->getConnection();

          // 1) Obtener product_id por SKU de producto
          $stmt = $pdo->prepare("
              SELECT product_id
              FROM products
              WHERE SKU = :sku
              LIMIT 1
          ");
          $stmt->execute([':sku' => $productSku]);
          $prod = $stmt->fetch(PDO::FETCH_ASSOC);

          if (!$prod) {
              return ['success' => false, 'error' => 'Producto no encontrado por SKU'];
          }

          $productId = (int)$prod['product_id'];

          // 2) Verificar si ya existe una variación con ese SKU (opcional pero recomendado)
          $stmt = $pdo->prepare("
              SELECT variation_id
              FROM variations
              WHERE SKU = :vsku
              LIMIT 1
          ");
          $stmt->execute([':vsku' => $variationSku]);
          if ($stmt->fetch(PDO::FETCH_ASSOC)) {
              return ['success' => false, 'error' => 'Variation SKU ya existe'];
          }

          // 3) Insertar nueva variación con campos vacíos y SKU de variación provisto
          $stmt = $pdo->prepare("
              INSERT INTO variations (name, SKU, image, pdf_artwork, parent_id, product_id)
              VALUES (:name, :sku_variation, :image, :pdf, :parent_id, :pid)
          ");
          $stmt->execute([
              ':name'          => null,              // vacío
              ':sku_variation' => $variationSku,     // SKU de la variación (provisto)
              ':image'         => null,              // vacío
              ':pdf'           => null,              // vacío
              ':parent_id'     => null,              // sin padre por defecto
              ':pid'           => $productId,
          ]);

          $variationId = (int)$pdo->lastInsertId();

          return [
              'success'        => true,
              'product_id'     => $productId,
              'variation_id'   => $variationId,
              'sku_variation'  => $variationSku,
              'name'           => null,
              'image'          => null,
              'pdf_artwork'    => null,
              'parent_id'      => null,
          ];

      } catch (PDOException $e) {
          error_log('createEmptyVariationByProductSku: '.$e->getMessage());
          return ['success' => false, 'error' => 'DB error'];
      }
  }

}


?>
