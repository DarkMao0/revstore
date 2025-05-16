<?php
require_once __DIR__ . '/functions.php';
$pdo = getPDO();

if (isset($_POST['remove'], $_POST['productID'])) {
    $user_id = $_SESSION['user']['id'];
    $product_id = $_POST['productID'];

    $stmt = $pdo->prepare("DELETE FROM cart WHERE userID = :userID AND productID = :productID");
    try {
        $stmt->execute(['userID' => $user_id, 'productID' => $product_id]);
    }
    catch (\PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);
        require('../errors/500.php');
        die();
    }
}

redirect('/user/cart.php');