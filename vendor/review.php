<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/functions.php';

// Логирование для отладки
error_log("review - Received POST data: " . var_export($_POST, true));
error_log("review - Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("review - Request URI: " . $_SERVER['REQUEST_URI']);

if (!$user) {
    setAlert('review_message', 'Пожалуйста, войдите в аккаунт, чтобы редактировать или удалить отзыв.');
    header("Location: /signin.php");
    exit;
}

$action = $_POST['action'] ?? '';
$review_id = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

if (!$review_id || !$product_id) {
    error_log("review - Review ID or Product ID is missing or invalid: review_id=$review_id, product_id=$product_id");
    setAlert('review_message', 'Ошибка: Неверные параметры запроса.');
    $redirectUrl = getProductUrl($product_id);
    header("Location: $redirectUrl");
    exit;
}

if ($action === 'update') {
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = trim(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_SPECIAL_CHARS));

    if ($rating === null || $rating < 1 || $rating > 5) {
        setAlert('review_message', 'Рейтинг должен быть от 1 до 5.');
        $redirectUrl = getProductUrl($product_id);
        header("Location: $redirectUrl");
        exit;
    }

    if (empty($comment) || strlen($comment) > 250) {
        setAlert('review_message', 'Комментарий обязателен и не должен превышать 250 символов.');
        $redirectUrl = getProductUrl($product_id);
        header("Location: $redirectUrl");
        exit;
    }

    $response = updateReview($review_id, $rating, $comment, $user);
    setAlert('review_message', $response['message']);
    error_log("review - Update review response: " . var_export($response, true));
} elseif ($action === 'delete') {
    $response = deleteReview($review_id, $user);
    setAlert('review_message', $response['message']);
    error_log("review - Delete review response: " . var_export($response, true));
} else {
    setAlert('review_message', 'Ошибка: Неверное действие.');
    error_log("review - Invalid action: $action");
}

// Перенаправление на страницу товара через функцию
$redirectUrl = getProductUrl($product_id);
error_log("review - Redirecting to: $redirectUrl");
header("Location: $redirectUrl");
exit;
?>