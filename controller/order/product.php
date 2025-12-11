<?php
class Product {
  public function handleProduct(){

    $input = file_get_contents('php://input');
    $data  = json_decode($input, true);

    switch ($data["action"] ?? null) {

      case 'get_preview_product_details':
        $this->getPreviewProductDetails($data);
        break;
      default:
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['response' => false, 'error' => 'Unsupported action']);
        break;
    }
  }


  private function getPreviewProductDetails($data){
    header('Content-Type: application/json; charset=utf-8');

    $connection = new Database();
    $user = new Users($connection);
    $user->setSKU($data['sku']);
    $company = $user->getUserCompanyBySKU();


    $connection = new Database();
    $category = new Categories($connection);
    $category->setSKU($data['sku']);
    $category_name = $category->getCategoryBySKU();

    $connection = new Database();
    $product = new Products($connection);
    $product->setSku($data['sku']);
    $product_details = $product->getProductDetailsBySKU();


    $connection = new Database();
    $variation = new Variation($connection);
    $variation->setSku($data['sku']);
    $variation_details = $variation->getVariationsBySKU();


    echo json_encode(array($company,$category_name, $product_details, $variation_details ));
  }



}

include "../../controller/config/database.php";
include "../../model/products.php";
include "../../model/users.php";
include "../../model/categories.php";
include "../../model/variations.php";
include "../../controller/products/variations.php";

$productClass = new Product(); // instancia
$productClass->handleProduct();
