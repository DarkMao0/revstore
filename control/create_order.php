<?php
require_once __DIR__ . '/functions.php';
denyNoUser();

$pdo = getPDO();
$user_id = $_SESSION['user']['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    try {
        $pdo->beginTransaction();

        // Получение товаров из корзины
        $stmt = $pdo->prepare("SELECT p.id, p.price, p.sale, p.available, c.quantity 
                               FROM products p
                               JOIN cart c ON p.id = c.productID
                               WHERE c.userID = :userID");
        $stmt->execute(['userID' => $user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($cart_items)) {
            setAlert('error', 'Корзина пуста.');
            $pdo->rollBack();
            redirect('/user/cart.php');
        }

        // Проверка доступности товаров
        foreach ($cart_items as $item) {
            if ($item['available'] < $item['quantity']) {
                setAlert('error', 'Недостаточно товара "' . htmlspecialchars($item['id']) . '" на складе.');
                $pdo->rollBack();
                redirect('/user/cart.php');
            }
        }

        // Расчет общей суммы с учетом скидки в процентах
        $total = 0;
        foreach ($cart_items as $item) {
            $price = $item['price'];
            if ($item['sale'] !== null && $item['sale'] > 0) {
                $price = round($item['price'] * (1 - $item['sale'] / 100), 2);
            }
            $total += $price * $item['quantity'];
        }

        // Создание заказа
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, created_at) VALUES (:user_id, :total, 'в_обработке', NOW())");
        $stmt->execute(['user_id' => $user_id, 'total' => $total]);
        $order_id = $pdo->lastInsertId();

        // Добавление позиций заказа
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
        foreach ($cart_items as $item) {
            $price = $item['price'];
            if ($item['sale'] !== null && $item['sale'] > 0) {
                $price = round($item['price'] * (1 - $item['sale'] / 100), 2);
            }
            $stmt->execute([
                'order_id' => $order_id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $price
            ]);

            // Обновление остатков товара
            $upd = $pdo->prepare("UPDATE products SET available = available - :qty WHERE id = :id");
            $upd->execute(['qty' => $item['quantity'], 'id' => $item['id']]);
        }

        // Очистка корзины
        $stmt = $pdo->prepare("DELETE FROM cart WHERE userID = :userID");
        $stmt->execute(['userID' => $user_id]);

        $pdo->commit();
        setAlert('success', 'Заказ успешно создан!');
        redirect('/user/order.php?order_id=' . $order_id);
    } catch (\PDOException $e) {
        $pdo->rollBack();
        error_log("Ошибка при создании заказа: " . $e->getMessage());
        setAlert('error', 'Ошибка при создании заказа.');
        redirect('/user/cart.php');
    }
} else {
    redirect('/user/cart.php');
}
?>