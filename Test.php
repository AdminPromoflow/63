<?php
/**
 * =====================================================
 * Class: Item
 * Handles AJAX requests for the "Items" module
 * (similar structure to Image controller)
 * =====================================================
 */
class Item { // cambia esto a Price
  public function handleAjax(): void
  {
      // 1) Detect request content type
      $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';// Igual

      // 2) Normalise $data (supporting both JSON and POST/multipart)
      $data = [];
      if (stripos($contentType, 'application/json') !== false) { // igual
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
              $this->getItemsDetails($data);// Cambialo a getPricesDetails
              break;

          case 'create_items':
              $this->saveItems($data); // Cambialo a savePrices
              break;

          case 'delete_item':
              $this->deleteItem($data); // Cambialo a deletePrice
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
      $item = new Items($connection);  //Cambialo por Prices

      // Retrieve all variations by product SKU
      $item->setIDItem($data['id_item']);

      $ok = $item->deleteItemByIdItem(); // Cambialo por el nombre correcto de la funcion que cambiaste ne la clase anterior o en la pregunta anterior que te pedi que modificaras la clase

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
      $item = new Items($connection); //Cambialo por Prices

      // Retrieve all variations by product SKU
      $item->setSKUVariation($data['sku_variation']);

      $items = $item->getItemsBySKUVariation();// Cambialo por el nombre correcto de la funcion que cambiaste ne la clase anterior o en la pregunta anterior que te pedi que modificaras la clase

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
       $item = new Items($connection); //Cambialo por Prices

       // 2) Lectura y validación de datos
       $skuVariation = $data["sku_variation"] ?? null;// Cambialo por el nombre correcto de la funcion que cambiaste ne la clase anterior o en la pregunta anterior que te pedi que modificaras la clase

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
       $item->deleteItemsBySkuVariation();// Cambialo por el nombre correcto de la funcion que cambiaste ne la clase anterior o en la pregunta anterior que te pedi que modificaras la clase


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

           $ok = $item->saveItem(); // ← devuelve bool// Cambialo por el nombre correcto de la funcion que cambiaste ne la clase anterior o en la pregunta anterior que te pedi que modificaras la clase

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
       $variations = $item->getVariationsBySKUProduct();// Cambialo por el nombre correcto de la funcion que cambiaste ne la clase anterior o en la pregunta anterior que te pedi que modificaras la clase

       $items      = $item->getItemsBySKUVariation();// Cambialo por el nombre correcto de la funcion que cambiaste ne la clase anterior o en la pregunta anterior que te pedi que modificaras la clase


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
include_once "../../model/items.php"; // Camba la direccion a ../../model/prices.php

$itemClass = new Item(); // Cambia a Price (en singjular )
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
  $itemClass->handleAjax();
}
