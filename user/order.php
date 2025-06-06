<?php
require_once __DIR__ . '/../control/functions.php';
denyNoUser();

$pdo = getPDO();
$order_id = $_GET['order_id'] ?? null;
$user_id = $_SESSION['user']['id'] ?? null;

if (!$order_id) {
    redirect('/cart.php');
}

// Получаем заказ
$stmt = $pdo->prepare("SELECT id, total, status, created_at, payment_method, phone FROM orders WHERE id = :order_id AND user_id = :user_id");
$stmt->execute(['order_id' => $order_id, 'user_id' => $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    redirect('/cart.php');
}

// Обработка выбора оплаты и телефона
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'], $_POST['phone'])) {
    $payment_method = $_POST['payment_method'];
    $phone = trim($_POST['phone']);
    // Можно добавить валидацию телефона
    $pdo->prepare("UPDATE orders SET payment_method = :pm, phone = :phone WHERE id = :id")
        ->execute(['pm' => $payment_method, 'phone' => $phone, 'id' => $order_id]);
    redirect('/user/order_confirmation.php?order_id=' . $order_id);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Выбор оплаты</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/cart.css">
    <script defer src="/js/scroll.js"></script>
    <script defer src="/js/cart.js"></script>
</head>
<body>
<?php include_once __DIR__ . '/../components/header.php'; ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <h2>Выберите способ оплаты для заказа #<?php echo htmlspecialchars($order['id']); ?></h2>
            <form method="post">
                <label>
                    <input type="radio" name="payment_method" value="card" required> Банковская карта
                </label><br>
                <label>
                    <input type="radio" name="payment_method" value="cash"> Наличные при получении
                </label><br>
                <label>
                    <input type="radio" name="payment_method" value="sbp"> СБП
                </label><br>
                <label style="color: #888;">
                    <input type="radio" name="payment_method" value="crypto" disabled> Криптовалюта (временно недоступно)
                </label><br><br>
                <label>
                    Телефон для связи:<br>
                    <input type="tel" name="phone" required pattern="^\+?\d{10,15}$" placeholder="+79991234567"
                        value="<?php echo isset($order['phone']) ? htmlspecialchars($order['phone']) : ''; ?>">
                </label><br><br>
                <button type="submit" class="product_interact order_but">Подтвердить</button>
            </form>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>