<?php
class Image {
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
          case 'get_images_details':
              // Si necesitas archivos opcionales en esta acción:
              $data['_files'] = $_FILES ?? [];
              $this->getImagesDetails($data);
              break;

          case 'create_update_images':
              // Si necesitas archivos opcionales en esta acción:
              $data['_files'] = $_FILES ?? [];
              $this->createUpdateImages($data);
              break;

          case 'delete_image':
              // Si necesitas archivos opcionales en esta acción:
              $this->deleteImage($data);
              break;


          default:
              header('Content-Type: application/json; charset=utf-8');
              echo json_encode(['success' => false, 'error' => 'Unsupported action']);
              break;
      }
  }


  private function deleteImage($data){

    $connection = new Database();
    $image = new Images($connection);
    $image->setSKUVariation($data['sku_variation']);
    $image->setImageAddress($data['link_image']);
    $ok = $image->deleteImageBySkuVariationAndLink();
    echo json_encode(["success"=> $ok]);
  }

  private function createUpdateImages($data){
      header('Content-Type: application/json; charset=utf-8');

      // 1) Conexión + modelo reutilizables
      $connection = new Database();
      $imagesModel = new Images($connection);

      // 2) Supplier por SKU de producto (una vez)
      $imagesModel->setSKU($data['sku_product'] ?? '');
      $supplier = $imagesModel->getSupplierDetailsBySKUProduct();

      $sku_product   = $data["sku_product"]   ?? '';
      $sku_variation = $data["sku_variation"] ?? '';
      $imagesModel->setSKUVariation($sku_variation); // <- fijar una sola vez

      $imagesModel->setTo0UpdatedBySKUVariation(); // <- fijar una sola vez


      // 3) Todos los archivos de images[]
      $files = $data['_files']['images'] ?? null;
      if (!$files || !isset($files['tmp_name']) || !is_array($files['tmp_name'])) {
          echo json_encode(["success"=>false,"error"=>"No se recibieron archivos"]);
          return;
      }

      $saved  = [];
      $errors = [];

      $count = count($files['tmp_name']);
      for ($i = 0; $i < $count; $i++) {
          // reconstruir el archivo i
          $file = [
              'name'     => $files['name'][$i]     ?? null,
              'type'     => $files['type'][$i]     ?? null,
              'tmp_name' => $files['tmp_name'][$i] ?? null,
              'error'    => $files['error'][$i]    ?? UPLOAD_ERR_NO_FILE,
              'size'     => $files['size'][$i]     ?? null,
          ];

          // validar subida
          if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
              $errors[] = ["index"=>$i, "name"=>$file['name'], "error"=>$file['error'] ?? 'tmp_name vacío'];
              continue;
          }

          // 4) Guardar archivo físico
          $path = $this->handleImageUpload($file, $supplier, $sku_product, $sku_variation);
          if ($path === null) {
              $errors[] = ["index"=>$i, "name"=>$file['name'], "error"=>"No se pudo guardar el archivo"];
              continue;
          }

          // 5) Guardar en BD (usa la misma instancia/conexión)
          $imagesModel->setImageAddress($path);
          $result = $imagesModel->saveImage();

          if (!($result['success'] ?? false)) {
              // si falla DB, reportamos y seguimos (el archivo ya quedó en disco)
              $errors[] = ["index"=>$i, "name"=>$file['name'], "error"=>"No se pudo registrar en BD"];
              continue;
          }

          $saved[] = [
              "path"        => $path,
              "image_id"    => $result['image_id'] ?? null,
              "variation_id"=> $result['variation_id'] ?? null,
          ];
      }

  //    $imagesModel->deleteImageWhereUpdatedIs0BySkuVariation(); // <- fijar una sola vez


      echo json_encode([
          "success"  => count($saved) > 0,
          "supplier" => $supplier,
          "paths"    => $saved,
          "errors"   => $errors,
      ]);
  }


  private function handleImageUpload(?array $imageFile = null, ?array $supplier = null, ?string $sku_product = null, ?string $sku_variation = null): ?string
  {
      $base = realpath(__DIR__ . '/../');
      if ($base === false) return null;
      $base = rtrim(str_replace('\\','/',$base), '/'); // <-- (nuevo) normaliza $base

      // Sanitizador simple
      $clean = function (?string $s): string {
          $s = (string)$s;
          $s = preg_replace('/[^\pL\pN._-]+/u', '-', $s);
          $s = trim($s, '-_. ');
          return $s !== '' ? $s : 'nd';
      };

      // Datos limpios
      $supplierId   = $clean($supplier['supplier_id']   ?? 'nd');
      $supplierName = $clean($supplier['contact_name'] ?? 'nd');
      $skuP         = $clean($sku_product);
      $skuV         = $clean($sku_variation);

      // Carpeta destino
      $dir = $base . '/uploads/' . $supplierId . '_' . $supplierName . '/' . $skuP . '/' . $skuV;
      if (!is_dir($dir)) {
          $old = umask(0000);
          @mkdir($dir, 0775, true);
          umask($old);
          @chmod($dir, 0775);
      }

      if (!$imageFile || empty($imageFile['tmp_name'])) {
          return null;
      }

      // Nombre final del archivo
      $name = $clean(pathinfo($imageFile['name'] ?? 'imagen', PATHINFO_FILENAME));
      $ext  = strtolower(pathinfo($imageFile['name'] ?? '', PATHINFO_EXTENSION));
      $ext  = $ext ? ".$ext" : '';
      $destPath = $dir . '/' . $name . $ext;

      // Evitar colisión
      if (is_file($destPath)) {
          $destPath = $dir . '/' . $name . '-' . date('Ymd-His') . $ext;
      }

      // Mover el archivo (un solo archivo)
      $tmp = $imageFile['tmp_name'];
      if (is_uploaded_file($tmp)) {
          if (@move_uploaded_file($tmp, $destPath)) {
              @chmod($destPath, 0664);
              $rel = ltrim(str_replace('\\','/', substr($destPath, strlen($base))), '/'); // <-- (nuevo)
              return "controller/".$rel; // p.ej.: uploads/.../archivo.pdf
          }
      } else {
          if (@rename($tmp, $destPath) || @copy($tmp, $destPath)) {
              @chmod($destPath, 0664);
              $rel = ltrim(str_replace('\\','/', substr($destPath, strlen($base))), '/'); // <-- (nuevo)
              return "controller/".$rel; // p.ej.: uploads/.../archivo.pdf
          }
      }

      return null;
  }











  private function getImagesDetails($data){

    $connection = new Database();
    $image = new Images($connection);
    $image->setSKU($data['sku']);
    $variations = $image->getVariationsBySKUProduct();

    $connection = new Database();
    $image = new Images($connection);
    $image->setSKUVariation($data['sku_variation']);
    $images = $image->getImagesBySKUVariation();

    echo json_encode(["success"=> true, "variations" => $variations, "images" => $images]);
  }

}

include_once "../../controller/config/database.php";
include_once "../../model/images.php";



$imageClass = new Image();
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
  $imageClass->handleAjax();
}


/*private function createNewVariation($data){

  $connection = new Database();
  $variation = new Variation($connection);
  $sku = $this->generate_sku('VRT');
  $variation->setSKU($data['sku']);
  $variation->setSKUVariation($sku);

  echo json_encode ($variation->createEmptyVariationByProductSku());

}*/
