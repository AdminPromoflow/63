<?php
class Product {
  public function handleProduct(){

    $input = file_get_contents('php://input');
    $data  = json_decode($input, true);

    switch ($data["action"] ?? null) {
      case 'create_new_product':
        $this->createNewProduct();
        break;

      case 'get_products':
        $this->getProducts();
        break;

      case 'update_products':
        $this->getUpdate();
        break;

      case 'update_category':
        $this->updateCategory($data);
        break;

      case 'save_product_details':
        $this->saveProductDetails($data);
        break;

      case 'get_all_products_supplier':
        $this->getProductsBasicBySupplierEmail($data);
        break;

      case 'get_product_details':
        $this->getProductBasicBySKU($data);
        break;

      case 'get_preview_product_details':
        $this->getPreviewProductDetails($data);
        break;


//
      default:
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['response' => false, 'error' => 'Unsupported action']);
        break;
    }
  }


  private function getPreviewProductDetails($data){
    header('Content-Type: application/json; charset=utf-8');



  /*  $connection = new Database();
    $product   = new Products($connection);


    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    $product->setId($_SESSION['idProduct']);

    $product->setName($data["name"]);
    $product->setStatus($data["status"]);
    $product->setDescription($data["description"]);
    $product->setTaglineDescription($data["pd_tagline"]);
    $product->setSku($data["sku"]);*/


  //  $response = $product->update();


    echo json_encode("response");
  }

    private function getProductBasicBySKU($data){
      header('Content-Type: application/json; charset=utf-8');

      $connection = new Database();
      $product   = new Products($connection);

      $product->setSku($data['sku'] ?? '');
      $response = $product->getProductBasicBySKU();

      echo ($response);
    }

    private function getProductsBasicBySupplierEmail(){
      header('Content-Type: application/json; charset=utf-8');
      $connection = new Database();
      $products   = new Products($connection);

      if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
      }
    //  echo json_encode("haha");exit;

      $products->setEmail($_SESSION['email']);

      $response   = $products->getProductsBasicBySupplierEmail();

      echo ($response);
    }

  private function saveProductDetails($data){
    header('Content-Type: application/json; charset=utf-8');

    $connection = new Database();
    $product   = new Products($connection);


    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    $product->setId($_SESSION['idProduct']);

    $product->setName($data["name"]);
    $product->setStatus($data["status"]);
    $product->setDescription($data["description"]);
    $product->setTaglineDescription($data["pd_tagline"]);
    $product->setSku($data["sku"]);


    $response = $product->update();


    echo json_encode($response);
  }

  private function updateCategory(array $data) {
    header('Content-Type: application/json; charset=utf-8');

    $connection = new Database();
    $product    = new Products($connection);

    // Valida inputs
    $sku = isset($data['sku']) ? trim((string)$data['sku']) : '';
    $categoryId = $data['id'] ?? null; // o $data['category_id'] si así lo envías

    if ($sku === '') {
      echo json_encode(['success' => false, 'error' => 'SKU required'], JSON_UNESCAPED_UNICODE);
      return;
    }

    // Llama al modelo (que NO debe hacer echo interno)
    $response = $product->updateCategoryIdBySKU($sku, $categoryId);

    // Imprime una sola vez
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
  }


  private function createNewProduct(){
    header('Content-Type: application/json; charset=utf-8');

    // Instancia del modelo
    $connection = new Database();
    $products   = new Products($connection);

    // Generar y setear SKU
    $sku = $this->generate_sku();
    $products->setSku($sku);

    // Obtener email desde la sesión (para resolver supplier internamente)
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    $email = $_SESSION['email'] ?? '';
    if ($email === '') {
      echo json_encode(['success' => false, 'error' => 'Email required in session'], JSON_UNESCAPED_UNICODE);
      return;
    }
    $products->setEmail($email);


    // Crear producto (el modelo deduce supplier por email)
    $response = $products->create();//

    if ($response['success']) {
      $variation = new Variations();
      $response2 = $variation->createDefaultVariation($response['id']);

      if ($response2['success']) {
        $result =  ['success' => true, 'sku' => $response['sku'], 'sku_variation' => $response2['sku_variation']];

        echo json_encode($result);
      }

    }



    //echo json_encode($response, JSON_UNESCAPED_UNICODE);

  //  echo json_encode($response);
  }

  private function generate_sku(string $prefix = 'PRD'): string {
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


  private function getProducts(){
    header('Content-Type: application/json; charset=utf-8');

    $connection = new Database();
    $products   = new Products($connection);
    $response   = $products->getAllNames();

    echo ($response);
  }

  private function getUpdate(){
    header('Content-Type: application/json; charset=utf-8');

    $connection = new Database();
    $products   = new Products($connection);
    $response   = $products->getAllNames();

    echo ($response);
  }
}

include "../../controller/config/database.php";
include "../../model/products.php";
include "../../controller/products/variations.php";

$productClass = new Product(); // instancia
$productClass->handleProduct();
