<?php
class Variations {
  public function handleAjax(): void
  {
      // 1) Detectar tipo de contenido
      $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

      // 2) Normalizar $data desde JSON o POST (multipart)
      $data = [];
      if (stripos($contentType, 'application/json') !== false) {
          $raw  = file_get_contents('php://input');
          $json = json_decode($raw, true);
          if (is_array($json)) {
              $data = $json;
          }
      } else {
          // multipart/form-data o x-www-form-urlencoded
          $data = $_POST;
      }

      // 4) Acción
      $action = $data['action'] ?? null;

      // 5) Enrutar
      switch ($action) {
          case 'get_variation_details':
              // Si necesitas archivos opcionales en esta acción:
              $data['_files'] = $_FILES ?? [];
              $this->getVariationDetails($data);
              break;

          case 'create_new_variation':
              $data['_files'] = $_FILES ?? [];
              $this->createNewVariation($data);
              break;

          case 'save_variation_details':
              // Aquí sí esperas archivos image/pdf desde FormData
              $data['_files'] = $_FILES ?? [];
              $this->saveVariationDetails($data);
              break;

          case 'update_group_name':
              // Aquí sí esperas archivos image/pdf desde FormData
              $this->updateGroupName($data);
              break;
          default:
              header('Content-Type: application/json; charset=utf-8');
              echo json_encode(['success' => false, 'error' => 'Unsupported action']);
              break;
      }
  }


  private function saveVariationDetails(array $data): void
  {
      header('Content-Type: application/json; charset=utf-8');

      // Datos base
      $sku_product   = $_POST['sku_product']   ?? $data['sku_product']   ?? null;
      $sku_parent_variation   = $_POST['sku_parent_variation']   ?? $data['sku_parent_variation']   ?? null;



      $isAttachAnImage   = $_POST['isAttachAnImage']   ?? $data['isAttachAnImage']   ?? null;
      $isAttachAPDF   = $_POST['isAttachAPDF']   ?? $data['isAttachAPDF']   ?? null;



      $sku_variation = $_POST['sku_variation'] ?? $data['sku_variation'] ?? null;
      $name = $_POST['name'] ?? $data['name'] ?? null;
      $variation_name = $_POST['name'] ?? $data['name'] ?? null;
      $name_pdf_artwork = $_POST['name_pdf_artwork'] ?? $data['name_pdf_artwork'] ?? null;
      $group = $_POST['group'] ?? $data['group'] ?? null;
      $imageFile     = $_FILES['imageFile']    ?? null;
      $pdfFile       = $_FILES['pdfFile']      ?? null;  // ← nuevo

      // (Opcional) supplier (como antes)
      $supplier = ['supplier_id' => null, 'supplier_name' => null];

      if ($sku_product) {
          $product = new Products(new Database());
          $product->setSku($sku_product);
          $supplier = $product->getSupplierDetailsBySKU() ?: $supplier;
      }


      $imagePath   = '';
      $pdfPath     = '';

      if ($isAttachAnImage) {
        $resImg = $this->handleImageUpload($imageFile,  $supplier, $sku_product, $sku_variation);
        $imagePath   = $resImg;
      }

      if ($isAttachAPDF) {
        $resPdf = $this->handlePdfUpload($pdfFile, $supplier, $sku_product, $sku_variation);
        $pdfPath     = $resPdf;
      }




      $connection = new Database();
      $variation = new Variation($connection);


      $variation->setName($name ?: '');
      $variation->setIsAttachAnImage($isAttachAnImage);
      $variation->setIsAttachAPDF($isAttachAPDF);
      $variation->setSKUVariation($sku_variation ?: '');
      $variation->setImage($imagePath ?: '');
      $variation->setPdfArtwork($pdfPath   ?: '');
      $variation->setSKUParentVariation($sku_parent_variation   ?: '');

      $variation->setNamePdfArtwork($name_pdf_artwork   ?: '');
      $variation->setGroupName($group   ?: '');


      $ok = $variation->updateVariationDetails();


      echo json_encode([
          'success'        => $ok,
          'image_path'     => $imagePath ?: '',
          'pdf_path'       => $pdfPath   ?: '',
          'variation_name'   => $variation_name,
          'sku_parent_variation' => $sku_parent_variation
      ]);
  }



  /**
   * Stub: función “desocupada”. No sube ni valida nada.
   * Acepta nulls de forma segura.
   */
   private function handleImageUpload(?array $imageFile = null, ?array $supplier = null, ?string $sku_product = null, ?string $sku_variation = null)
   {

       // Helpers
       $clean = fn($v) => trim((string)$v);
       $seg   = fn($v) => preg_replace('/[^\w\-]/', '_', $clean($v));

       // 1) Archivo de origen
       $f = $imageFile ?? ($_FILES['file'] ?? null);
       if (!$f || !isset($f['error'], $f['name'], $f['tmp_name']) || $f['error'] !== UPLOAD_ERR_OK) {
           return null;
       }
       $tmp  = $f['tmp_name'];
       $orig = $f['name'];
       if (!is_uploaded_file($tmp)) return null;

       // 2) Datos limpios
       $supplierId   = $seg($supplier['supplier_id']   ?? 'nd');
       $supplierName = $seg($supplier['supplier_name'] ?? 'nd');
       $skuP         = $seg($sku_product);
       $skuV         = $seg($sku_variation);

       // 3) Ruta ABSOLUTA al folder "controller" (este archivo vive en controller/products/*)
       $controllerDir = realpath(__DIR__ . '/..'); // -> /ruta/a/63/controller
       if ($controllerDir === false) return null;

       // 4) Directorio destino (dentro de controller/uploads/...)
       $dir = $controllerDir . '/uploads/' . $supplierId . '_' . $supplierName . '/' . $skuP . '/' . $skuV . '/';

       // Crear si no existe
       if (!is_dir($dir)) {
           if (!mkdir($dir, 0755, true)) {
               // Opcional: log para depurar permisos/ruta
               error_log("No se pudo crear el directorio: " . $dir);
               return null;
           }
       }

    //   echo json_encode();

       // 5) Validación extensión + nombre seguro
       $allow = ['jpg','jpeg','png','gif','webp'];
       $ext   = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
       if (!in_array($ext, $allow, true)) return null;

       $safeName = bin2hex(random_bytes(8)) . '.' . $ext;

       // 6) Mover archivo al destino absoluto
       if (!move_uploaded_file($tmp, $dir . $safeName)) return null;

       // 7) Ruta RELATIVA pública que quieres devolver (sin “63”)
       //    uploads está dentro de controller/
       $rel = 'controller/uploads/' . $supplierId . '_' . $supplierName . '/' . $skuP . '/' . $skuV . '/' . $safeName;
       return $rel;
   }



   private function handlePdfUpload(?array $pdfFile = null, ?array $supplier = null, ?string $sku_product = null, ?string $sku_variation = null): ?string
   {
       // Helpers (mismos de imagen)
       $clean = fn($v) => trim((string)$v);
       $seg   = fn($v) => preg_replace('/[^\w\-]/', '_', $clean($v));

       // 1) Archivo de origen
       $f = $pdfFile ?? ($_FILES['file'] ?? null);
       if (!$f || !isset($f['error'], $f['name'], $f['tmp_name']) || $f['error'] !== UPLOAD_ERR_OK) {
           return null;
       }

       $tmp  = $f['tmp_name'];
       $orig = $f['name'];
       if (!is_uploaded_file($tmp)) return null;

       // 2) Datos limpios
       $supplierId   = $seg($supplier['supplier_id']   ?? 'nd');
       $supplierName = $seg($supplier['supplier_name'] ?? 'nd');
       $skuP         = $seg($sku_product);
       $skuV         = $seg($sku_variation);


       // 3) Ruta ABSOLUTA al folder "controller" (este archivo vive en controller/products/*)
       $controllerDir = realpath(__DIR__ . '/..'); // -> /ruta/a/63/controller
       if ($controllerDir === false) return null;

       // 4) Directorio destino
       $dir = $controllerDir . '/uploads/' . $supplierId . '_' . $supplierName . '/' . $skuP . '/' . $skuV . '/';
       if (!is_dir($dir)) {
           if (!mkdir($dir, 0755, true)) {
               error_log("No se pudo crear el directorio: " . $dir);
               return null;
           }
       }

       // 5) Validación: solo PDF
       $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
       if ($ext !== 'pdf') return null;

       // Nombre seguro .pdf
       $safeName = bin2hex(random_bytes(8)) . '.pdf';

       // 6) Mover archivo
       if (!move_uploaded_file($tmp, $dir . $safeName)) return null;

       // 7) Devolver ruta RELATIVA pública
       $rel = 'controller/uploads/' . $supplierId . '_' . $supplierName . '/' . $skuP . '/' . $skuV . '/' . $safeName;
       return $rel;
   }




  private function createNewVariation($data){

    $connection = new Database();
    $variation = new Variation($connection);
    $sku = $this->generate_sku('VRT');
    $variation->setSKU($data['sku']);
    $variation->setSKUVariation($sku);

    echo json_encode ($variation->createEmptyVariationByProductSku());

  }
  private function updateGroupName($data){

    $connection = new Database();
    $variation = new Variation($connection);

    $variation->setSKUVariation($data['sku_variation']);
    $variation->setGroupName($data['group_name'] ?? null);

    echo json_encode ($variation->updategroupNameBySkuVariation());

  }

  private function getVariationDetails($data){

    $connection = new Database();
    $variation = new Variation($connection);
    $variation->setSKUVariation($data['sku_variation']);
    $variation->setSKU($data['sku']);

    echo json_encode ($variation->getVariationDetailsBySkus());
  }

  public function createDefaultVariation($productId): array {

    $connection = new Database();
    $variation = new Variation($connection);
    $sku = $this->generate_sku('VRT');
    $variation->setId($productId);
    $variation->setSKU($sku);
    return $variation->createDefaultVariation();

  }

  private function generate_sku(string $prefix = 'VRT'): string {
    $dt    = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $stamp = $dt->format('Ymd-His-u'); // 20250925-175903-123456
    $rand  = strtoupper(bin2hex(random_bytes(5)));   // 10 hex
    return sprintf(
      '%s-%s-%s',
      strtoupper(preg_replace('/[^A-Z0-9]/', '', $prefix)),
      $stamp,
      $rand
    );
  }


}

include_once "../../controller/config/database.php";
include_once "../../model/products.php";
include_once "../../model/variations.php";



$variationsClass = new Variations();
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
  $variationsClass->handleAjax();
}
