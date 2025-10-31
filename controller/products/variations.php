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
        $resImg = $this->uploadBlockWithTrace($imageFile, 'imageFile', 'handleImageUpload', $supplier, $sku_product, $sku_variation);
        $imagePath   = $resImg['path'];
      }
      if ($isAttachAPDF) {
        $resPdf = $this->uploadBlockWithTrace($pdfFile, 'pdfFile', 'handlePdfUpload', $supplier, $sku_product, $sku_variation);
        $pdfPath     = $resPdf['path'];
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
   * Ejecuta el bloque de validación + subida y devuelve path y trace.
   * $label debe ser 'imageFile' o 'pdfFile' para mantener las etiquetas del trace.
   */
  private function uploadBlockWithTrace(
      ?array $file,
      string $label,                 // 'imageFile' | 'pdfFile'
      string $uploaderMethod,        // 'handleImageUpload' | 'handlePdfUpload'
      array $supplier,
      ?string $sku_product,
      ?string $sku_variation
      ): array {
      $path  = '';
      $trace = [];

      if ($file) {
          $trace[] = 'has_' . $label;
          if (is_array($file)) {
              $trace[] = 'is_array_' . $label;
              $err = $file['error'] ?? UPLOAD_ERR_NO_FILE;
              if ($err === UPLOAD_ERR_OK) {
                  $trace[] = 'error_ok';
                  $tmp = $file['tmp_name'] ?? '';
                  if (!empty($tmp)) {
                      $trace[] = 'tmp_name_present';
                      if (file_exists($tmp)) {
                          $trace[] = 'tmp_exists';

                          if (method_exists($this, $uploaderMethod)) {
                              $path = $this->{$uploaderMethod}($file, $supplier, $sku_product, $sku_variation) ?? '';
                              $trace[] = $path ? 'moved_ok' : 'move_failed';
                          } else {
                              $trace[] = 'uploader_method_not_found';
                          }
                      } else {
                          $trace[] = 'tmp_not_found_on_fs';
                      }
                  } else {
                      $trace[] = 'tmp_name_empty';
                  }
              } else {
                  $trace[] = 'upload_error_' . $err;
              }
          } else {
              $trace[] = $label . '_not_array';
          }
      } else {
          $trace[] = 'no_' . $label;
      }

      return [
          'path'  => $path,
          'trace' => $trace,
      ];
  }




  /**
   * Stub: función “desocupada”. No sube ni valida nada.
   * Acepta nulls de forma segura.
   */
   private function handleImageUpload(?array $imageFile, ?array $supplier, ?string $sku_product, ?string $sku_variation): ?string {
       if (!$imageFile || empty($imageFile['tmp_name']) || ($imageFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
           return null;
       }

       $base = realpath(__DIR__ . '/../');            // .../controller
       if ($base === false) return null;
       $base = rtrim(str_replace('\\','/',$base), '/');

       $clean = function (?string $s): string {
           $s = (string)$s;
           $s = preg_replace('/[^\pL\pN._-]+/u', '-', $s);
           return trim($s ?? '', '-_. ') ?: 'nd';
       };

       $supplierId   = $clean($supplier['supplier_id']   ?? 'nd');
       $supplierName = $clean($supplier['supplier_name'] ?? 'nd');
       $skuP         = $clean($sku_product);
       $skuV         = $clean($sku_variation);

       $dir = $base . '/uploads/' . $supplierId . '_' . $supplierName . '/' . $skuP . '/' . $skuV;
       if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
           throw new RuntimeException('No se pudo crear el directorio de destino');
       }

       $extAllow = ['jpg','jpeg','png','webp'];
       $name = $clean(pathinfo($imageFile['name'] ?? 'imagen', PATHINFO_FILENAME));
       $ext  = strtolower(pathinfo($imageFile['name'] ?? '', PATHINFO_EXTENSION));
       if (!in_array($ext, $extAllow, true)) {
           throw new RuntimeException('Extensión de imagen no permitida');
       }

       $destPath = $dir . '/' . $name . '.' . $ext;
       $tmp = $imageFile['tmp_name'];

       if (!move_uploaded_file($tmp, $destPath)) {
           // intento alterno por si llega desde fetch() sin is_uploaded_file
           if (!rename($tmp, $destPath) && !copy($tmp, $destPath)) {
               throw new RuntimeException('No se pudo mover el archivo subido');
           }
       }
       chmod($destPath, 0664);

       // ruta relativa desde /controller (coincide con donde queda guardado)
       $rel = ltrim(str_replace('\\','/', substr($destPath, strlen($base))), '/');
       return 'controller/' . $rel;   // p.ej. controller/uploads/123_Supplier/SKU/VRT/imagen.jpg
   }


   /**
    * Sube un PDF de arte final y devuelve la ruta relativa desde /controller
    * p.ej.: controller/uploads/<supplier>/<sku>/<sku_variation>/archivo.pdf
    */
   private function handlePdfUpload(
       ?array $pdfFile,
       ?array $supplier = null,
       ?string $sku_product = null,
       ?string $sku_variation = null
   ): ?string {
       // 1) Validaciones mínimas del upload
       if (
           !$pdfFile ||
           empty($pdfFile['tmp_name']) ||
           ($pdfFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
       ) {
           return null;
       }

       // 2) Base y normalización de rutas
       $base = realpath(__DIR__ . '/../');           // .../controller
       if ($base === false) return null;
       $base = rtrim(str_replace('\\','/',$base), '/');

       // 3) Sanitizador simple para componer rutas/archivos
       $clean = function (?string $s): string {
           $s = (string)$s;
           $s = preg_replace('/[^\pL\pN._-]+/u', '-', $s);
           $s = trim($s, '-_. ');
           return $s !== '' ? $s : 'nd';
       };

       // 4) Bloque de metadatos de destino
       $supplierId   = $clean($supplier['supplier_id']   ?? 'nd');
       $supplierName = $clean($supplier['supplier_name'] ?? 'nd');
       $skuP         = $clean($sku_product);
       $skuV         = $clean($sku_variation);

       // 5) Carpeta final: controller/uploads/<supplier>/<sku>/<variation>
       $dir = $base . '/uploads/' . $supplierId . '_' . $supplierName . '/' . $skuP . '/' . $skuV;
       if (!is_dir($dir)) {
           $old = umask(0000);
           if (!mkdir($dir, 0775, true)) {
               umask($old);
               throw new RuntimeException('No se pudo crear el directorio de destino');
           }
           umask($old);
           chmod($dir, 0775);
       }

       // 6) Validaciones de tipo/size
       $origNameNoExt = pathinfo($pdfFile['name'] ?? 'documento', PATHINFO_FILENAME);
       $name = $clean($origNameNoExt) ?: 'documento';
       $ext  = strtolower(pathinfo($pdfFile['name'] ?? '', PATHINFO_EXTENSION));

       // Fuerza PDF por extensión y valida extensión declarada
       if ($ext !== 'pdf') {
           // Si vino con otra extensión, igual lo guardamos como .pdf
           $ext = 'pdf';
       }

       // (Opcional) límite de tamaño: 20MB
       if (!empty($pdfFile['size']) && (int)$pdfFile['size'] > 20 * 1024 * 1024) {
           throw new RuntimeException('El PDF excede el límite de 20MB');
       }

       // Valida MIME (si está disponible en el server)
       $tmp = $pdfFile['tmp_name'];
       if (function_exists('finfo_open')) {
           $f = finfo_open(FILEINFO_MIME_TYPE);
           if ($f) {
               $mime = finfo_file($f, $tmp) ?: '';
               finfo_close($f);
               // Acepta application/pdf; tolera application/octet-stream si la ext es pdf
               if ($mime !== 'application/pdf' && $mime !== 'application/octet-stream') {
                   throw new RuntimeException('El archivo no parece un PDF válido');
               }
           }
       }

       // 7) Mover archivo (con fallback si llega desde fetch sin is_uploaded_file)
       $destPath = $dir . '/' . $name . '.pdf';
       if (!move_uploaded_file($tmp, $destPath)) {
           if (!rename($tmp, $destPath) && !copy($tmp, $destPath)) {
               throw new RuntimeException('No se pudo mover el PDF subido');
           }
       }
       @chmod($destPath, 0664);

       // 8) Devuelve ruta relativa desde /controller (coherente con tu frontend)
       $rel = ltrim(str_replace('\\','/', substr($destPath, strlen($base))), '/'); // uploads/...
       return 'controller/' . $rel; // controller/uploads/.../archivo.pdf
   }



  private function createNewVariation($data){

    $connection = new Database();
    $variation = new Variation($connection);
    $sku = $this->generate_sku('VRT');
    $variation->setSKU($data['sku']);
    $variation->setSKUVariation($sku);

    echo json_encode ($variation->createEmptyVariationByProductSku());

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
