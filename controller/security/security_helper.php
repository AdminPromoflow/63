<?php

class securityHelper
{
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
            case 'check_parameters':
                $this->checkParameters($data);
                break;

            default:
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'error' => 'Unsupported action'], JSON_UNESCAPED_UNICODE);
                break;
        }
    }

    private function checkParameters(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $connection = new Database();
        $variation = new Variation($connection);
        $variation->setSKU($data['sku']);
        $variation->setSKUVariation($data['sku_variation']);
        $result = $variation->checkProductAndVariationExistenceBySkus();

        echo json_encode([
            'success'  => $result
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

include_once "../../controller/config/database.php";
include_once "../../model/categories.php";
include_once "../../model/products.php";
include_once "../../model/variations.php";

$variationsClass = new securityHelper();
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $variationsClass->handleAjax();
}
?>
