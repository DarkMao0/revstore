<?php
require_once __DIR__ . '/../control/functions.php';
denyNoUser();

$pdo = getPDO();
$order_id = $_GET['order_id'] ?? null;
$user_id = $_SESSION['user']['id'] ?? null;

if (!$order_id) {
    redirect('/cart.php');
}

$stmt = $pdo->prepare("SELECT o.id, o.total, o.status, o.created_at, o.payment_method, o.phone,
                              oi.product_id, oi.quantity, oi.price, p.name 
                       FROM orders o 
                       JOIN order_items oi ON o.id = oi.order_id 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE o.id = :order_id AND o.user_id = :user_id");
$stmt->execute(['order_id' => $order_id, 'user_id' => $user_id]);
$order = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($order)) {
    redirect('/cart.php');
}
?>

<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>REVSTORE - Подтверждение заказа</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/order.css">
    <script defer src="/js/scroll.js"></script>
</head>
<body>
<?php include_once __DIR__ . '/../components/header.php' ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <h2>Заказ #<?php echo htmlspecialchars($order[0]['id']); ?> успешно создан!</h2>
            <p>Дата создания: <?php echo htmlspecialchars($order[0]['created_at']); ?></p>
            <p>Статус: <?php echo htmlspecialchars($order[0]['status']); ?></p>
            <p>Способ оплаты: <?php echo htmlspecialchars($order[0]['payment_method']); ?></p>
            <p>Телефон: <?php echo htmlspecialchars($order[0]['phone']); ?></p>
            <table class="prod">
                <tr class="titles">
                    <td>Название</td>
                    <td>Цена за единицу</td>
                    <td>Количество</td>
                    <td>Общая стоимость</td>
                </tr>
                <?php foreach ($order as $item): ?>
                    <tr>
                        <td data-label="Название"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td data-label="Цена за единицу"><?php echo htmlspecialchars($item['price']); ?> ₽</td>
                        <td data-label="Количество"><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td data-label="Общая стоимость"><?php echo htmlspecialchars($item['price'] * $item['quantity']); ?> ₽</td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="order">
                <a class="total">Итого: <?php echo htmlspecialchars($order[0]['total']); ?> ₽</a>
                <a href="/catalog.php" class="product_interact">Продолжить покупки</a>
            </div>
            <?php if (checkAlert('success')): ?>
                <p class="success"><?php echo getAlert('success'); ?></p>
            <?php elseif (checkAlert('error')): ?>
                <p class="error"><?php echo getAlert('error'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../components/footer.php' ?>
</body>
</html>