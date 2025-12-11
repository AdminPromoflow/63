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
  private $group_name;
  private $name_pdf_artwork;



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
  public function setGroupName(?string $v)     { $v = trim((string)$v); $this->group_name = ($v === '') ? null : $v; }
  public function setNamePdfArtwork(?string $v)     { $v = trim((string)$v); $this->name_pdf_artwork = ($v === '') ? null : $v; }
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
                v.name_pdf_artwork,
                v.parent_id,
                v.`group` AS variation_group,
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

          // 4) Quinta consulta: listar `group` de todas las variations del producto, sin repetir
          $stmt = $pdo->prepare("
              SELECT DISTINCT `group`
              FROM variations
              WHERE product_id = :pid
                AND `group` IS NOT NULL
                AND `group` <> ''
              ORDER BY `group` ASC
          ");
          $stmt->execute([':pid' => $productId]);

          // Devuelve una lista simple: ['Group A', 'Group B', ...]
          $groupsByProduct = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

          // 5) Respuesta final (sin json_encode)
          return [
              'success'    => true,
              'product'    => [
                  'product_id'   => $productId,
                  'product_name' => $productName,
                  'product_sku'  => $productSku,
              ],
              'variations' => $variations, // cada item: ['name'=>..., 'SKU'=>...]
              'current'    => [
                  'name'             => $row['name'],
                  'image'            => $row['image'] ?? null,
                  'sku'              => $row['SKU'],
                  'pdf_artwork'      => $row['pdf_artwork'] ?? null,
                  'name_pdf_artwork' => $row['name_pdf_artwork'] ?? null,
                  'parent_id'        => $row['parent_id'] ? (int)$row['parent_id'] : null,
                  'group'            => $row['variation_group'] ?? null,
              ],
              // ← name y sku del parent_id (puede ser null si no tiene padre)
              'parent'     => [
                  'name' => $row['parent_name'] ?? null,
                  'sku'  => $row['parent_sku']  ?? null,
              ],
              // Lista de todos los group del producto (sin repetir)
              'groups_by_product' => $groupsByProduct,
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
          $stmt = $pdo->prepare("
              SELECT variation_id
              FROM variations
              WHERE SKU = :parentSku
              LIMIT 1
          ");
          $stmt->execute([':parentSku' => (string)$this->sku_parent_variation]);
          $parentId = $stmt->fetch(\PDO::FETCH_COLUMN);

          // 3) Si no retorna parentId: buscar la variación 'default' por NOMBRE dentro del mismo producto del target
          if ($parentId === false) {
              // 3.a) Obtener product_id de la variación objetivo
              $stmt = $pdo->prepare("
                  SELECT product_id
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
              $stmt = $pdo->prepare("
                  SELECT variation_id
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

              $stmt = $pdo->prepare("
                  UPDATE variations
                     SET name             = :name,
                         `group`          = :group_name,
                         image            = :image,
                         pdf_artwork      = :pdf_artwork,
                         name_pdf_artwork = :name_pdf_artwork,
                         parent_id        = :parent_id
                   WHERE SKU = :sku
                   LIMIT 1
              ");

              $stmt->bindValue(':name',             (string)($this->name ?? ''),              \PDO::PARAM_STR);
              $stmt->bindValue(':group_name',       (string)($this->group_name ?? ''),        \PDO::PARAM_STR);
              $stmt->bindValue(':image',            (string)($this->image ?? ''),             \PDO::PARAM_STR); // TEXT
              $stmt->bindValue(':pdf_artwork',      (string)($this->pdf_artwork ?? ''),       \PDO::PARAM_STR); // TEXT
              $stmt->bindValue(':name_pdf_artwork', (string)($this->name_pdf_artwork ?? ''),  \PDO::PARAM_STR);
              $stmt->bindValue(':parent_id',        $parentId,                                \PDO::PARAM_INT);
              $stmt->bindValue(':sku',              $targetSku,                               \PDO::PARAM_STR);

          } elseif ($this->isAttachAnImage && !$this->isAttachAPDF) {

              $stmt = $pdo->prepare("
                  UPDATE variations
                     SET name             = :name,
                         `group`          = :group_name,
                         image            = :image,
                         name_pdf_artwork = :name_pdf_artwork,
                         parent_id        = :parent_id
                   WHERE SKU = :sku
                   LIMIT 1
              ");

              $stmt->bindValue(':name',             (string)($this->name ?? ''),             \PDO::PARAM_STR);
              $stmt->bindValue(':group_name',       (string)($this->group_name ?? ''),       \PDO::PARAM_STR);
              $stmt->bindValue(':image',            (string)($this->image ?? ''),            \PDO::PARAM_STR); // TEXT
              $stmt->bindValue(':name_pdf_artwork', (string)($this->name_pdf_artwork ?? ''), \PDO::PARAM_STR);
              $stmt->bindValue(':parent_id',        $parentId,                               \PDO::PARAM_INT);
              $stmt->bindValue(':sku',              $targetSku,                              \PDO::PARAM_STR);

          } elseif (!$this->isAttachAnImage && $this->isAttachAPDF) {

              $stmt = $pdo->prepare("
                  UPDATE variations
                     SET name             = :name,
                         `group`          = :group_name,
                         pdf_artwork      = :pdf_artwork,
                         name_pdf_artwork = :name_pdf_artwork,
                         parent_id        = :parent_id
                   WHERE SKU = :sku
                   LIMIT 1
              ");

              $stmt->bindValue(':name',             (string)($this->name ?? ''),             \PDO::PARAM_STR);
              $stmt->bindValue(':group_name',       (string)($this->group_name ?? ''),       \PDO::PARAM_STR);
              $stmt->bindValue(':pdf_artwork',      (string)($this->pdf_artwork ?? ''),      \PDO::PARAM_STR); // TEXT
              $stmt->bindValue(':name_pdf_artwork', (string)($this->name_pdf_artwork ?? ''), \PDO::PARAM_STR);
              $stmt->bindValue(':parent_id',        $parentId,                               \PDO::PARAM_INT);
              $stmt->bindValue(':sku',              $targetSku,                              \PDO::PARAM_STR);

          } elseif (!$this->isAttachAnImage && !$this->isAttachAPDF) {

              $stmt = $pdo->prepare("
                  UPDATE variations
                     SET name             = :name,
                         `group`          = :group_name,
                         name_pdf_artwork = :name_pdf_artwork,
                         parent_id        = :parent_id
                   WHERE SKU = :sku
                   LIMIT 1
              ");

              $stmt->bindValue(':name',             (string)($this->name ?? ''),             \PDO::PARAM_STR);
              $stmt->bindValue(':group_name',       (string)($this->group_name ?? ''),       \PDO::PARAM_STR);
              $stmt->bindValue(':name_pdf_artwork', (string)($this->name_pdf_artwork ?? ''), \PDO::PARAM_STR);
              $stmt->bindValue(':parent_id',        $parentId,                               \PDO::PARAM_INT);
              $stmt->bindValue(':sku',              $targetSku,                              \PDO::PARAM_STR);
          }

          $ok = $stmt->execute();
          $pdo->commit();

          return $ok; // true si ejecutó correctamente (aunque no cambie filas)
      } catch (\PDOException $e) {
          if (isset($pdo)) {
              $pdo->rollBack();
          }
          // error_log('updateVariationDetails error (sku '.$targetSku.'): '.$e->getMessage());
          return false;
      }
  }

  public function updategroupNameBySkuVariation(): bool
  {

      // 1) SKU objetivo (la variación que vamos a actualizar)
      $targetSku = trim((string)($this->sku_variation ?? ''));
      if ($targetSku === '') {
          return false;
      }

  //    echo  json_encode($this->group_name );exit;

      try {
          $pdo = $this->connection->getConnection();
          $pdo->beginTransaction();

          // Ojo: `group` va entre backticks porque es palabra reservada
          $stmt = $pdo->prepare("
              UPDATE variations
                 SET `group` = :group_name
               WHERE SKU = :sku
               LIMIT 1
          ");


          // Usamos exactamente la propiedad que rellena setGroupName()
            $stmt->bindValue(':group_name', (string)($this->group_name ?? ''), \PDO::PARAM_STR);
            $stmt->bindValue(':sku',        $targetSku,                        \PDO::PARAM_STR);

            $ok = $stmt->execute();
            $pdo->commit();

            return $ok; // true aunque no cambie filas

        } catch (\PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            // error_log('updategroupNameBySkuVariation error (sku '.$targetSku.'): '.$e->getMessage());
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


  public function getVariationsBySKU(): array
  {
      // 1) Validar SKU de producto
      $sku = trim((string)($this->sku ?? ''));
      if ($sku === '' || mb_strlen($sku) > 50) {
          return ['success' => false, 'error' => 'Product SKU required/invalid'];
      }

      try {
          $pdo = $this->connection->getConnection();

          // 2) Traer TODAS las variaciones de ese producto (incluyendo padres e hijos)
          $sql = "
              SELECT
                  v.variation_id,
                  v.`group`      AS variation_group,
                  v.name         AS variation_name,
                  v.SKU          AS variation_sku,
                  v.image,
                  v.pdf_artwork,
                  v.name_pdf_artwork,
                  v.parent_id,
                  v.product_id
              FROM products p
              INNER JOIN variations v
                  ON v.product_id = p.product_id
              WHERE p.SKU = :sku
              ORDER BY v.variation_id ASC
          ";
          $stmt = $pdo->prepare($sql);
          $stmt->execute([':sku' => $sku]);
          $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if (!$rows) {
              return ['success' => false, 'error' => 'No variations found for this product'];
          }

          // 2b) Sacar todos los variation_id para buscar sus imágenes, ítems y precios
          $variationIds = [];
          foreach ($rows as $r) {
              if (!empty($r['variation_id'])) {
                  $variationIds[] = (int)$r['variation_id'];
              }
          }
          $variationIds = array_values(array_unique($variationIds));

          // Mapas por variation_id
          $imagesByVariation = [];
          $itemsByVariation  = [];
          $pricesByVariation = [];

          if (!empty($variationIds)) {
              $placeholders = implode(',', array_fill(0, count($variationIds), '?'));

              // -----------------------------------------------------------------
              // IMAGES
              // -----------------------------------------------------------------
              $sqlImg = "
                  SELECT
                      image_id,
                      link,
                      updated,
                      variation_id
                  FROM images
                  WHERE variation_id IN ($placeholders)
              ";
              $stmtImg = $pdo->prepare($sqlImg);
              $stmtImg->execute($variationIds);
              $imgRows = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

              foreach ($imgRows as $img) {
                  $vid = (int)$img['variation_id'];
                  if (!isset($imagesByVariation[$vid])) {
                      $imagesByVariation[$vid] = [];
                  }
                  $imagesByVariation[$vid][] = [
                      'image_id' => (int)$img['image_id'],
                      'link'     => $img['link'],
                      'updated'  => $img['updated'] !== null ? (int)$img['updated'] : null,
                  ];
              }

              // -----------------------------------------------------------------
              // ITEMS
              // -----------------------------------------------------------------
              $sqlItems = "
                  SELECT
                      item_id,
                      name,
                      description,
                      variation_id
                  FROM items
                  WHERE variation_id IN ($placeholders)
              ";
              $stmtItems = $pdo->prepare($sqlItems);
              $stmtItems->execute($variationIds);
              $itemRows = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

              foreach ($itemRows as $it) {
                  $vid = (int)$it['variation_id'];
                  if (!isset($itemsByVariation[$vid])) {
                      $itemsByVariation[$vid] = [];
                  }
                  $itemsByVariation[$vid][] = [
                      'item_id'     => (int)$it['item_id'],
                      'name'        => $it['name'],
                      'description' => $it['description'],
                  ];
              }

              // -----------------------------------------------------------------
              // PRICES
              // -----------------------------------------------------------------
              $sqlPrices = "
                  SELECT
                      price_id,
                      min_quantity,
                      max_quantity,
                      price,
                      variation_id
                  FROM prices
                  WHERE variation_id IN ($placeholders)
              ";
              $stmtPrices = $pdo->prepare($sqlPrices);
              $stmtPrices->execute($variationIds);
              $priceRows = $stmtPrices->fetchAll(PDO::FETCH_ASSOC);

              foreach ($priceRows as $pr) {
                  $vid = (int)$pr['variation_id'];
                  if (!isset($pricesByVariation[$vid])) {
                      $pricesByVariation[$vid] = [];
                  }
                  $pricesByVariation[$vid][] = [
                      'price_id'     => (int)$pr['price_id'],
                      'min_quantity' => $pr['min_quantity'] !== null ? (int)$pr['min_quantity'] : null,
                      'max_quantity' => $pr['max_quantity'] !== null ? (int)$pr['max_quantity'] : null,
                      'price'        => $pr['price'] !== null ? (float)$pr['price'] : null,
                  ];
              }
          }

          // 3) Crear nodos por variation_id
          $nodes     = [];
          $parentMap = []; // variation_id => parent_id

          foreach ($rows as $r) {
              $id        = (int)$r['variation_id'];
              $parentId  = $r['parent_id'] ? (int)$r['parent_id'] : null;
              $groupName = $r['variation_group'];

              // NULL ó vacío -> "Default"
              if ($groupName === null || $groupName === '') {
                  $groupName = 'Default';
              }

              $nodes[$id] = [
                  'variation_id' => $id,
                  'group'        => $groupName,
                  'name'         => $r['variation_name'],
                  'sku'          => $r['variation_sku'],
                  'details'      => [
                      'image'            => $r['image'] ?? null,
                      'pdf_artwork'      => $r['pdf_artwork'] ?? null,
                      'name_pdf_artwork' => $r['name_pdf_artwork'] ?? null,
                      'parent_id'        => $parentId,
                      'product_id'       => $r['product_id'] ? (int)$r['product_id'] : null,
                  ],
                  // orden: details, images, items, prices, children
                  'images'   => $imagesByVariation[$id] ?? [],
                  'items'    => $itemsByVariation[$id] ?? [],
                  'prices'   => $pricesByVariation[$id] ?? [],
                  // hijos agrupados por el group del hijo
                  'children' => [],  // 'WIDTH' => [...], 'PRINT SIDE' => [...]
              ];

              $parentMap[$id] = $parentId;
          }

          // 4) Construir árbol usando parent_id
          $roots = []; // nodos sin padre (parent_id NULL o padre inexistente)

          foreach ($nodes as $id => &$node) {
              $parentId = $parentMap[$id];

              if ($parentId !== null && isset($nodes[$parentId])) {
                  // Soy hijo → me cuelgo del padre,
                  // agrupado por MI propio group (WIDTH, PRINT SIDE, etc)
                  $childGroup = $node['group'];

                  if (!isset($nodes[$parentId]['children'][$childGroup])) {
                      $nodes[$parentId]['children'][$childGroup] = [];
                  }
                  $nodes[$parentId]['children'][$childGroup][] = &$node;
              } else {
                  // No tengo padre → soy raíz
                  $roots[] = &$node;
              }
          }
          unset($node); // por seguridad con las referencias

          // 5) Agrupar solo las raíces por su group → Variations['Default'] = [...]
          $variationsByGroup = [];
          foreach ($roots as $rootNode) {
              $g = $rootNode['group']; // ej. "Default"
              if (!isset($variationsByGroup[$g])) {
                  $variationsByGroup[$g] = [];
              }
              $variationsByGroup[$g][] = $rootNode;
          }

          // 6) Regla especial para Default:
          // - Si Default tiene prices: solo Default, sin children
          // - Si NO tiene prices: todos los hijos directos de Default, sin children (sin nietos)
          if (isset($variationsByGroup['Default']) && !empty($variationsByGroup['Default'])) {
              $defaultRoots = $variationsByGroup['Default'];
              $defaultRoot  = $defaultRoots[0]; // asumimos un solo root Default

              if (!empty($defaultRoot['prices'])) {
                  // Caso 1: Default tiene precios → solo Default sin children
                  $cleanRoot = $defaultRoot;
                  $cleanRoot['children'] = [];
                  $variationsByGroup['Default'] = [$cleanRoot];
              } else {
                  // Caso 2: Default NO tiene precios → usar sus hijos directos, sin children
                  $flatChildren = [];

                  if (!empty($defaultRoot['children']) && is_array($defaultRoot['children'])) {
                      foreach ($defaultRoot['children'] as $groupKey => $childrenList) {
                          if (!is_array($childrenList)) {
                              continue;
                          }
                          foreach ($childrenList as $childNode) {
                              $childCopy = $childNode;      // copia por valor
                              $childCopy['children'] = [];  // sin nietos
                              $flatChildren[] = $childCopy;
                          }
                      }
                  }

                  if (!empty($flatChildren)) {
                      $variationsByGroup['Default'] = $flatChildren;
                  } else {
                      // Si por alguna razón no hay hijos, devolvemos Default sin children
                      $cleanRoot = $defaultRoot;
                      $cleanRoot['children'] = [];
                      $variationsByGroup['Default'] = [$cleanRoot];
                  }
              }
          }

          return [
              'sku'        => $sku,
              'Variations' => $variationsByGroup,
          ];

      } catch (PDOException $e) {
          error_log('getVariationsBySKU error (SKU '.$sku.'): '.$e->getMessage());
          return ['success' => false, 'error' => 'DB error'];
      }
  }

}


?>
