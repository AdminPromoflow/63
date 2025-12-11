<?php
class Images {
  // ===== Atributos =====
  private $connection;          // PDO wrapper (->getConnection())
  private $product_id;          // FK al producto (padre)
  private $sku  = null;
  private $sku_variation = null;
  private $image_address;

  // ===== Constructor =====
  public function __construct($connection) { $this->connection = $connection; }

  // ===== Setters =====
  public function setId($id)              { $this->product_id = (int)$id; }

  public function setSKU(?string $v)      { $v = trim((string)$v); $this->sku  = ($v === '') ? null : $v; }


  public function setImageAddress(?string $v)      { $v = trim((string)$v); $this->image_address  = ($v === '') ? null : $v; }

  public function setSKUVariation(?string $v)      { $v = trim((string)$v); $this->sku_variation  = ($v === '') ? null : $v; }
  //public function setImage(?string $v)    { $v = trim((string)$v); $this->image = ($v === '') ? null : $v; }

  public function deleteImageBySkuVariationAndLink(): bool
  {


      $vsku = $this->sku_variation ?? null;
      $link = $this->image_address ?? null;

      if (!$vsku || !$link) {
          return false; // Faltan datos obligatorios
      }
    //  echo json_encode(["variationId"=> $variationId]);exit;

      try {
          $pdo = $this->connection->getConnection();

          // 1. Obtener el ID de la variación usando el SKU
          $stmtVar = $pdo->prepare("SELECT variation_id FROM variations WHERE SKU = :sku LIMIT 1");
          $stmtVar->execute([':sku' => $vsku]);
          $variation = $stmtVar->fetch(\PDO::FETCH_ASSOC);



          if (!$variation) {
              return false; // No se encontró la variación
          }

          $variationId = $variation['variation_id'];



          // 2. Eliminar la imagen que coincida con el link y el id de variación
          $stmtDel = $pdo->prepare("
              DELETE FROM images
              WHERE variation_id = :vid
                AND link = :link
          ");

          return $stmtDel->execute([
              ':vid'  => $variationId,
              ':link' => $link
          ]);

      } catch (\PDOException $e) {
          error_log('deleteImageBySkuVariationAndLink: ' . $e->getMessage());
          return false;
      }
  }

  public function deleteImageWhereUpdatedIs0BySkuVariation(): bool
{
    $vsku = $this->sku_variation ?? null;
    if (!$vsku) {
        return false; // SKU de la variación requerido
    }

    try {
        $pdo = $this->connection->getConnection();

        // Borrar todas las imágenes de esa variación con updated = 0
        $stmt = $pdo->prepare("
            DELETE i
            FROM images i
            INNER JOIN variations v ON v.variation_id = i.variation_id
            WHERE v.SKU = :vsku
              AND i.updated = 0
        ");

        return $stmt->execute([':vsku' => $vsku]);
    } catch (\PDOException $e) {
        error_log('deleteImageWhereUpdatedIs0BySkuVariation: '.$e->getMessage());
        return false;
    }
}



  public function setTo0UpdatedBySKUVariation(): bool
  {
      $vsku = $this->sku_variation ?? null;
      if (!$vsku) {
          return false; // SKU de la variación requerido
      }

      try {
          $pdo = $this->connection->getConnection();
          $stmt = $pdo->prepare("
              UPDATE images i
              JOIN variations v ON v.variation_id = i.variation_id
              SET i.updated = 0
              WHERE v.SKU = :vsku
          ");
          return $stmt->execute([':vsku' => $vsku]);
      } catch (\PDOException $e) {
          error_log('SetTo0UpdatedBySKUVariation: ' . $e->getMessage());
          return false;
      }
  }

  public function getVariationsBySKUProduct()
  {
      if (!$this->sku) {
          return []; // ← para mantener el shape: "variations": []
      }

      try {
          $pdo = $this->connection->getConnection();

          $stmt = $pdo->prepare("
              SELECT product_id
              FROM products
              WHERE SKU = :sku
              LIMIT 1
          ");
          $stmt->execute([':sku' => $this->sku]);
          $prod = $stmt->fetch(\PDO::FETCH_ASSOC);

          if (!$prod) {
              return []; // ← shape consistente
          }

          $stmt = $pdo->prepare("
              SELECT v.name, v.SKU
              FROM variations v
              WHERE v.product_id = :pid
              ORDER BY v.name ASC, v.variation_id ASC
          ");
          $stmt->execute([':pid' => (int)$prod['product_id']]);
          $variations = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

          return $variations; // ✅ ahora sí
      } catch (\PDOException $e) {
          error_log('getVariationsBySKUProduct: '.$e->getMessage());
          return []; // ← mantiene "variations": []
      }
  }

  public function getImagesBySKUVariation()
  {
      if (!$this->sku_variation) {
          return []; // ← "images": []
      }

      try {
          $pdo = $this->connection->getConnection();

          $stmt = $pdo->prepare("
              SELECT variation_id
              FROM variations
              WHERE SKU = :vsku
              LIMIT 1
          ");
          $stmt->execute([':vsku' => $this->sku_variation]);
          $variation = $stmt->fetch(\PDO::FETCH_ASSOC);

          if (!$variation) {
              return [];
          }

          $variationId = (int)$variation['variation_id'];

          $stmt = $pdo->prepare("
              SELECT image_id, link
              FROM images
              WHERE variation_id = :vid
              ORDER BY image_id ASC
          ");
          $stmt->execute([':vid' => $variationId]);
          $images = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

          return $images; // ✅ listo
      } catch (\PDOException $e) {
          error_log('getImagesBySKUVariation: '.$e->getMessage());
          return []; // ← "images": []
      }
  }

  public function getSupplierDetailsBySKUProduct(): array
  {
      if (!$this->sku) {
          return [];
      }

      try {
          $pdo = $this->connection->getConnection();

          $stmt = $pdo->prepare("
              SELECT
                  p.supplier_id,
                  s.contact_name
              FROM products p
              LEFT JOIN suppliers s ON s.supplier_id = p.supplier_id
              WHERE p.SKU = :sku
              LIMIT 1
          ");
          $stmt->execute([':sku' => $this->sku]);
          $row = $stmt->fetch(\PDO::FETCH_ASSOC);

          if (!$row) {
              return [];
          }

          return [
              'supplier_id'   => $row['supplier_id'] !== null ? (int)$row['supplier_id'] : null,
              'contact_name' => $row['contact_name'] ?? null,
          ];
      } catch (\PDOException $e) {
          error_log('getSupplierDetailsBySKUProduct: '.$e->getMessage());
          return [];
      }
  }

  public function saveImage(): array
  {
      // Validaciones mínimas
      $vsku = $this->sku_variation ?? null;
      $link = isset($this->image_address) ? trim((string)$this->image_address) : '';

      if (!$vsku)  return ['success' => false, 'error' => 'Variation SKU requerido'];
      if ($link==='') return ['success' => false, 'error' => 'Link (image_address) requerido'];

      try {
          $pdo = $this->connection->getConnection();

          // 1) Hallar variation_id por SKU
          $stmt = $pdo->prepare("
              SELECT variation_id
              FROM variations
              WHERE SKU = :vsku
              LIMIT 1
          ");
          $stmt->execute([':vsku' => $vsku]);
          $vid = $stmt->fetch(\PDO::FETCH_COLUMN);
          if ($vid === false) {
              return ['success' => false, 'error' => 'Variación no encontrada por SKU'];
          }
          $vid = (int)$vid;

          // 2) ¿Ya existe (mismo link para esta variación)?
          $stmt = $pdo->prepare("
              SELECT image_id
              FROM images
              WHERE variation_id = :vid AND link = :link
              ORDER BY image_id ASC
          ");
          $stmt->execute([':vid' => $vid, ':link' => $link]);
          $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];

          if (!empty($ids)) {
              // Reemplazar: mantener el primero, actualizarlo y limpiar duplicados
              $keepId = (int)$ids[0];

              $stmt = $pdo->prepare("UPDATE images SET link = :link, updated = 1 WHERE image_id = :id");
              $stmt->execute([':link' => $link, ':id' => $keepId]);

              if (count($ids) > 1) {
                  $rest = array_map('intval', array_slice($ids, 1));
                  $in   = implode(',', array_fill(0, count($rest), '?'));
                  $pdo->prepare("DELETE FROM images WHERE image_id IN ($in)")->execute($rest);
              }

              return [
                  'success'       => true,
                  'action'        => 'replaced',
                  'image_id'      => $keepId,
                  'variation_id'  => $vid,
                  'link'          => $link,
                  'updated'       => 1
              ];
          }

          // 3) No existía: crear con updated=1
          $stmt = $pdo->prepare("
              INSERT INTO images (link, updated, variation_id)
              VALUES (:link, 1, :vid)
          ");
          $stmt->execute([':link' => $link, ':vid' => $vid]);

          return [
              'success'      => true,
              'action'       => 'created',
              'image_id'     => (int)$pdo->lastInsertId(),
              'variation_id' => $vid,
              'link'         => $link,
              'updated'      => 1
          ];

      } catch (\PDOException $e) {
          error_log('saveImage: '.$e->getMessage());
          return ['success' => false, 'error' => 'DB error'];
      }
  }




}


?>
