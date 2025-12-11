<?php
/**
 * =====================================================
 * Class: Item
 * Handles AJAX requests for the "Items" module
 * (similar structure to Image controller)
 * =====================================================
 */
class Item {
  public function handleAjax(): void
  {
      // 1) Detect request content type
      $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

      // 2) Normalise $data (supporting both JSON and POST/multipart)
      $data = [];
      if (stripos($contentType, 'application/json') !== false) {
          $raw  = file_get_contents('php://input');
          $json = json_decode($raw, true);
          if (is_array($json)) {
              $data = $json;
          }
      } else {
          $data = $_POST;
      }

      // 3) Determine requested action
      $action = $data['action'] ?? null;

      // 4) Route to appropriate handler
      switch ($action) {
          case 'get_items_details':
              $this->getItemsDetails($data);
              break;

          case 'create_items':
              $this->saveItems($data);
              break;

          case 'delete_item':
              $this->deleteItem($data);
              break;

          default:
              header('Content-Type: application/json; charset=utf-8');
              echo json_encode(['success' => false, 'error' => 'Unsupported action']);
              break;
      }
  }



  private function deleteItem($data)
  {
      $connection = new Database();
      $item = new Items($connection);

      // Retrieve all variations by product SKU
      $item->setIDItem($data['id_item']);

      $ok = $item->deleteItemByIdItem();

      header('Content-Type: application/json; charset=utf-8');
      echo json_encode([
          "success" => $ok
      ]);
  }

  /**
   * -----------------------------------------------------
   * Fetch details of product variations and their items
   * -----------------------------------------------------
   */
  private function getItemsDetails($data)
  {
      $connection = new Database();
      $item = new Items($connection);

      // Retrieve all variations by product SKU
      $item->setSKUVariation($data['sku_variation']);

      $items = $item->getItemsBySKUVariation();

      header('Content-Type: application/json; charset=utf-8');
      echo json_encode([
          "success" => true,
          "items" => $items
      ]);
  }

  /**
   * -----------------------------------------------------
   * Create or update items for a given variation
   * -----------------------------------------------------
   */
   private function saveItems($data)
   {
       header('Content-Type: application/json; charset=utf-8');

       // 1) Conexión y modelo
       $connection = new Database();
       $item = new Items($connection);

       // 2) Lectura y validación de datos
       $skuVariation = $data["sku_variation"] ?? null;
       $labels = isset($data["labels"]) && is_array($data["labels"]) ? $data["labels"] : [];
       $texts  = isset($data["texts"])  && is_array($data["texts"])  ? $data["texts"]  : [];

       if (!$skuVariation) {
           echo json_encode([
               "success" => false,
               "error"   => "Falta el SKU de la variación"
           ]);
           return;
       }

       if (empty($labels) || empty($texts)) {
           echo json_encode([
               "success" => false,
               "error"   => "No se recibieron labels o texts válidos"
           ]);
           return;
       }

       // 3) Eliminar los items anteriores de esa variación
       $item->setSKUVariation($skuVariation);
       $item->deleteItemsBySkuVariation();

       // 4) Crear los nuevos items
       $total = min(count($labels), count($texts));
       $insertados = 0;
       $errores = [];

       for ($i = 0; $i < $total; $i++) {
           $label = trim((string)$labels[$i]);
           $text  = trim((string)$texts[$i]);

           // Omitir si ambos están vacíos
           if ($label === "" && $text === "") continue;

           $item->setLabel($label);
           $item->setText($text);

           $ok = $item->saveItem(); // ← devuelve bool
           if ($ok) {
               $insertados++;
           } else {
               $errores[] = [
                   "index" => $i,
                   "label" => $label,
                   "text"  => $text,
                   "msg"   => "No se pudo insertar el item"
               ];
           }
       }

       // 5) Recuperar los datos actualizados
       $item->setSKU($data["sku"] ?? null);
       $variations = $item->getVariationsBySKUProduct();
       $items      = $item->getItemsBySKUVariation();

       // 6) Respuesta final
       echo json_encode([
           "success"     => true,
           "insertados"  => $insertados,
           "fallidos"    => count($errores),
           "errores"     => $errores,
           "variations"  => $variations,
           "items"       => $items
       ]);
   }

}

// ------------------------------------------------------
// Bootstrap: includes and self-invocation
// ------------------------------------------------------
include_once "../../controller/config/database.php";
include_once "../../model/items.php";

$itemClass = new Item();
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
  $itemClass->handleAjax();
}
