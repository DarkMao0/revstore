<?php
require_once __DIR__ . '/../control/functions.php';
denyNoUser();

$pdo = getPDO();
$user_id = $_SESSION['user']['id'] ?? null;
$order_id = $_POST['order_id'] ?? null;

if (!$order_id) {
    redirect('/user/profile.php');
}

// Проверяем, что заказ принадлежит пользователю и его можно отменить
$stmt = $pdo->prepare("SELECT status FROM orders WHERE id = :order_id AND user_id = :user_id");
$stmt->execute(['order_id' => $order_id, 'user_id' => $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if ($order && in_array($order['status'], ['в_обработке', 'отправлен'])) {
    $upd = $pdo->prepare("UPDATE orders SET status = 'отменён' WHERE id = :order_id");
    $upd->execute(['order_id' => $order_id]);
    setAlert('success', 'Заказ отменён.');
} else {
    setAlert('error', 'Заказ нельзя отменить.');
}

redirect('/user/profile.php');