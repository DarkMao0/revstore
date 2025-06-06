<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/functions.php';

// Логирование для отладки
error_log("add_review - Received POST data: " . var_export($_POST, true));
error_log("add_review - Received FILES data: " . var_export($_FILES, true));
error_log("add_review - Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("add_review - Request URI: " . $_SERVER['REQUEST_URI']);

$user = authorizedUserData();
if (!$user) {
    setAlert('review_message', 'Пожалуйста, войдите в аккаунт, чтобы оставить отзыв');
    header("Location: /signin-view.php");
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
$comment = $_POST['comment'] ?? null;

// Проверка изображений (до 3 файлов)
$images = $_FILES['images'] ?? [];
if (!empty($images['tmp_name']) && count(array_filter($images['tmp_name'])) > 3) {
    error_log("add_review - Too many images uploaded: " . count(array_filter($images['tmp_name'])));
    setAlert('review_message', 'Ошибка: Можно загрузить не более 3 изображений');
    header("Location: /product-view.php?id=$product_id");
    exit;
}

if ($product_id === null || $product_id <= 0) {
    error_log("add_review - Product ID is missing or invalid: " . var_export($product_id, true));
    setAlert('review_message', 'Ошибка: ID товара не передан или некорректен');
    header("Location: /");
    exit;
}

$response = addReview($product_id, $rating, $comment, $user, $images);

// Установка сообщения в сессию
setAlert('review_message', $response['message']);
error_log("add_review - Review response: " . var_export($response, true));

// Перенаправление на страницу товара через функцию
$redirectUrl = getProductUrl($product_id);
error_log("add_review - Redirecting to: $redirectUrl");
header("Location: $redirectUrl");
exit;
?>