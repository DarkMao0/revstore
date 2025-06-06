<?php
require_once __DIR__ . '/../control/functions.php';
denyNoUser();

$pdo = getPDO();
$user_id = $_SESSION['user']['id'] ?? null;

// Обработка POST-запроса для добавления в корзину
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'active') {
    $product_id = $_POST['productID'] ?? null;
    if ($product_id && is_numeric($product_id)) {
        addToCart((int)$product_id);
        setAlert('success', 'Товар добавлен в корзину.');
        redirect($_SERVER['HTTP_REFERER']);
    } else {
        setAlert('error', 'Неверный ID товара.');
        redirect('/user/cart.php');
    }
}

// Получение товаров из корзины с учетом скидок
$stmt = $pdo->prepare("SELECT p.id, p.name, p.price, p.sale, p.image, c.quantity 
                       FROM products p 
                       JOIN cart c ON p.id = c.productID 
                       WHERE c.userID = :userID");
try {
    $stmt->execute(['userID' => $user_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Ошибка при получении корзины: " . $e->getMessage());
    http_response_code(500);
    require(__DIR__ . '/../errors/500.php');
    die();
}

// Расчет общей стоимости с учетом скидок
$total = 0;
foreach ($products as $data) {
    $price = $data['price'];
    if ($data['sale'] !== null && $data['sale'] > 0) {
        $price = round($data['price'] * (1 - $data['sale'] / 100), 2);
    }
    $total += $price * $data['quantity'];
}
?>

<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>REVSTORE - Корзина</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/cart.css">
    <script defer src="/js/scroll.js"></script>
    <script defer src="/js/cart.js"></script>
</head>
<body>
<?php include_once __DIR__ . '/../components/header.php' ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <div class="cart_items">
                <?php if (empty($products)): ?>
                    <a class="no_data">Корзина пуста</a>
                <?php else: ?>
                <table class="prod">
                    <tr class="titles">
                        <td>Изображение</td>
                        <td>Название</td>
                        <td>Цена за единицу</td>
                        <td>Количество</td>
                        <td>Общая стоимость</td>
                        <td>Удалить</td>
                    </tr>
                    <?php foreach ($products as $data): ?>
                        <?php
                        $price = $data['price'];
                        if ($data['sale'] !== null && $data['sale'] > 0) {
                            $price = round($data['price'] * (1 - $data['sale'] / 100), 2);
                        }
                        $item_total = $price * $data['quantity'];
                        ?>
                        <tr>
                            <td data-label="Изображение">
                                <a href="/product-view.php?id=<?php echo htmlspecialchars($data['id']); ?>">
                                    <img class="cart_image" src="<?php echo htmlspecialchars($data['image']); ?>">
                                </a>
                            </td>
                            <td data-label="Название"><?php echo htmlspecialchars($data['name']); ?></td>
                            <td data-label="Цена за единицу"><?php echo htmlspecialchars($price); ?> ₽</td>
                            <td data-label="Количество">
                                <form class="refresh" action="/control/refreshquantity" method="post">
                                    <input type="hidden" name="productID" value="<?php echo htmlspecialchars($data['id']); ?>">
                                    <div class="quantity_changer">
                                        <button type="button"> - </button>
                                        <input
                                            type="number"
                                            class="quantity_input"
                                            name="quantity"
                                            value="<?php echo htmlspecialchars($data['quantity']); ?>"
                                            min="1"
                                            readonly
                                        >
                                        <button type="button"> + </button>
                                    </div>
                                    <button type="submit" class="product_interact" name="update">Применить</button>
                                </form>
                            </td>
                            <td data-label="Общая стоимость"><?php echo htmlspecialchars($item_total); ?> ₽</td>
                            <td data-label="Удалить">
                                <form action="/control/deletefromcart" method="post">
                                    <input type="hidden" name="productID" value="<?php echo htmlspecialchars($data['id']); ?>">
                                    <button type="submit" class="cart_del" name="remove">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div class="order">
                <a class="total">К оплате: <?php echo htmlspecialchars($total); ?> ₽</a>
                <form action="/control/create_order" method="post">
                    <button type="submit" class="product_interact order_but" name="create_order">Оформить заказ</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../components/footer.php' ?>
</body>
</html>