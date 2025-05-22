<?php
require_once __DIR__ . '/../vendor/functions.php';
denyNoUser();

$pdo = getPDO();

$user_id = $_SESSION['user']['id'] ?? null;

$stmt = $pdo->prepare("SELECT p.id, p.name, p.price, p.image, c.quantity FROM products p JOIN cart c ON p.id = c.productID WHERE c.userID = :userID");
try {
    $stmt->execute(['userID' => $user_id]);
}
catch (\PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    require('../errors/500.php');
    die();
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = 0;
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
                    <?php $total += $data['price'] * $data['quantity']; ?>
                        <tr>
                            <td data-label="Изображение">
                                <a href="/product.php?id=<?php echo $data['id'] ?>">
                                    <img class="cart_image" src="<?php echo $data['image']; ?>">
                                </a>
                            </td>
                            <td data-label="Название"><?php echo $data['name']; ?></td>
                            <td data-label="Цена за единицу"><?php echo $data['price']; ?> ₽</td>
                            <td data-label="Количество">
                                <form class="refresh" action="/vendor/refreshquantity" method="post">
                                    <input type="hidden" name="productID" value="<?php echo $data['id']; ?>">
                                    <div class="quantity_changer">
                                        <button type="button"> - </button>
                                        <input
                                            type="number"
                                            class="quantity_input"
                                            name="quantity"
                                            value="<?php echo $data['quantity']; ?>"
                                            min="1"
                                            readonly
                                        >
                                        <button type="button"> + </button>
                                    </div>
                                    <button type="submit" class="product_interact" name="update">Применить</button>
                                </form>
                            </td>
                            <td data-label="Общая стоимость"><?php echo $data['price'] * $data['quantity']; ?> ₽</td>
                            <td data-label="Удалить">
                                <form action="/vendor/deletefromcart" method="post">
                                    <input type="hidden" name="productID" value="<?php echo $data['id']; ?>">
                                    <button type="submit" class="cart_del" name="remove">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div class="order">
                <a class="total">К оплате: <?php echo $total; ?> ₽</a>
                <button class="product_interact order_but">Продолжить</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../components/footer.php' ?>
</body>
</html>