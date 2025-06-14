<?php
require_once __DIR__ . '/../control/functions.php';
denyNoUser();
denyNoAdmin();

$pdo = getPDO();

// Получаем все заказы с пользователями и телефоном
$stmt = $pdo->query("SELECT o.id, o.user_id, u.name AS user_name, o.total, o.status, o.created_at, o.phone
                     FROM orders o
                     LEFT JOIN users u ON o.user_id = u.id
                     ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка смены статуса заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $upd = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $upd->execute(['status' => $status, 'id' => $order_id]);
    header("Location: adminpanel.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Cerama Granit - Управление заказами</title>
    <link rel="icon" href="/img/fav.png" type="image/x-icon">
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/order.css">
    <script defer src="../js/common.js"></script>
</head>
<body>
<?php include_once __DIR__ . '/../components/header.php' ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <h2>Управление заказами</h2>
            <?php if (empty($orders)): ?>
                <div class="no_result"><a>Заказов нет</a></div>
            <?php else: ?>
                <table class="prod">
                    <tr class="titles">
                        <td>ID</td>
                        <td>Пользователь</td>
                        <td>Сумма</td>
                        <td>Статус</td>
                        <td>Дата</td>
                        <td>Телефон</td>
                        <td>Действия</td>
                    </tr>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td data-label="ID"><?php echo htmlspecialchars($order['id']); ?></td>
                            <td data-label="Пользователь"><?php echo htmlspecialchars($order['user_name'] ?? 'Неизвестно'); ?></td>
                            <td data-label="Сумма"><?php echo htmlspecialchars($order['total']); ?> ₽</td>
                            <td data-label="Статус">
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="в_обработке" <?php if($order['status']=='в_обработке') echo 'selected'; ?>>В обработке</option>
                                        <option value="отправлен" <?php if($order['status']=='отправлен') echo 'selected'; ?>>Отправлен</option>
                                        <option value="завершён" <?php if($order['status']=='завершён') echo 'selected'; ?>>Завершён</option>
                                        <option value="отменён" <?php if($order['status']=='отменён') echo 'selected'; ?>>Отменён</option>
                                    </select>
                                </form>
                            </td>
                            <td data-label="Дата"><?php echo htmlspecialchars($order['created_at']); ?></td>
                            <td data-label="Телефон"><?php echo htmlspecialchars($order['phone']); ?></td>
                            <td data-label="Действия">
                                <a href="/admin/order_info.php?order_id=<?php echo $order['id']; ?>" target="_blank">Подробнее</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../components/footer.php' ?>
</body>
</html>