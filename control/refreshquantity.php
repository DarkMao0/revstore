<?php
require_once __DIR__ . '/functions.php';
$pdo = getPDO();

if (isset($_POST['update'], $_POST['productID'], $_POST['quantity'])) {
    $user_id = $_SESSION['user']['id'];
    $product_id = $_POST['productID'];
    $quantity = $_POST['quantity'];

    $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE userID = :userID AND productID = :productID");
    try {
        $stmt->execute(['quantity' => $quantity, 'userID' => $user_id, 'productID' => $product_id]);
    }
    catch (\PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);
        require('../errors/500.php');
        die();
    }
}

redirect('/user/cart.php');