<?php
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'active') {
    $product_id = $_POST['productID'] ?? null;
    if ($product_id !== null) {
        addToWishlist($product_id);
    }
}

redirect($_SERVER['HTTP_REFERER']);