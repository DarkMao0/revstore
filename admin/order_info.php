<?php
require_once __DIR__ . '/../control/functions.php';
denyNoUser();
denyNoAdmin();

$pdo = getPDO();
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    redirect('/admin/adminpanel.php');
}

// Обработка смены статуса заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $upd = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $upd->execute(['status' => $new_status, 'id' => $order_id]);
    // Перезагрузка страницы, чтобы увидеть обновлённый статус
    redirect('/admin/order_info.php?order_id=' . $order_id);
}

// Получаем заказ и пользователя
$stmt = $pdo->prepare("SELECT o.id, o.total, o.status, o.created_at, o.user_id, o.payment_method, o.phone, u.name AS user_name
                                FROM orders o
                                LEFT JOIN users u ON o.user_id = u.id
                                WHERE o.id = :order_id");
$stmt->execute(['order_id' => $order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    redirect('/admin/adminpanel.php');
}

// Получаем товары заказа
$stmt = $pdo->prepare("SELECT oi.product_id, oi.quantity, oi.price, p.name
                       FROM order_items oi
                       JOIN products p ON oi.product_id = p.id
                       WHERE oi.order_id = :order_id");
$stmt->execute(['order_id' => $order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Информация о заказе #<?php echo htmlspecialchars($order['id']); ?></title>
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/order.css">
</head>
<body>
<?php include_once __DIR__ . '/../components/header.php'; ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <h2>Заказ #<?php echo htmlspecialchars($order['id']); ?></h2>
            <p>Пользователь: <?php echo htmlspecialchars($order['user_name'] ?? 'Неизвестно'); ?> (ID: <?php echo htmlspecialchars($order['user_id']); ?>)</p>
            <p>Дата создания: <?php echo htmlspecialchars($order['created_at']); ?></p>
            <p>Способ оплаты: <?php echo htmlspecialchars($order['payment_method']); ?></p>
            <p>Телефон: <?php echo htmlspecialchars($order['phone']); ?></p>
            <form method="post" style="margin-bottom: 20px;">
                <label for="status">Статус:</label>
                <select name="status" id="status" onchange="this.form.submit()">
                    <option value="в_обработке" <?php if($order['status']=='в_обработке') echo 'selected'; ?>>В обработке</option>
                    <option value="отправлен" <?php if($order['status']=='отправлен') echo 'selected'; ?>>Отправлен</option>
                    <option value="завершён" <?php if($order['status']=='завершён') echo 'selected'; ?>>Завершён</option>
                    <option value="отменён" <?php if($order['status']=='отменён') echo 'selected'; ?>>Отменён</option>
                </select>
            </form>
            <table class="prod">
                <tr class="titles">
                    <td>Название</td>
                    <td>Цена за единицу</td>
                    <td>Количество</td>
                    <td>Общая стоимость</td>
                </tr>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['price']); ?> ₽</td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($item['price'] * $item['quantity']); ?> ₽</td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="order">
                <a class="total">Итого: <?php echo htmlspecialchars($order['total']); ?> ₽</a>
            </div>
            <a href="/admin/adminpanel.php" class="product_interact">Назад к заказам</a>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>