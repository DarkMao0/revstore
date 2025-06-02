<?php
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['productID'] ?? null;
    $action = $_POST['action'] ?? '';

    if (!isset($_SESSION['user']['id'])) {
        redirect('/signin-view.php');
    }

    if ($action === 'active') {
        addToCart($product_id);
    }

    redirect($_SERVER['HTTP_REFERER']);
}
else {
    redirect('/');
}
