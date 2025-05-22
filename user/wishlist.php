<?php
require_once __DIR__ . '/../vendor/functions.php';
denyNoUser();

$pdo = getPDO();

$user_id = $_SESSION['user']['id'];

$query = "SELECT p.id as product_id, p.name as product_name, p.price, p.sale, p.available, p.image FROM products p INNER JOIN wishlist w ON p.id = w.productID WHERE w.userID = :userID";
$params = [
        'userID' => $user_id
];

$stmt = $pdo->prepare($query);
try {
    $stmt->execute($params);
}
catch (\PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    require('errors/500.php');
    die();
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html  lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>REVSTORE - Закладки</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/wishlist.css">
    <script defer src="/js/scroll.js"></script>
</head>
<body>
<?php include_once __DIR__ . '/../components/header.php' ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <div class="wish_items">
                <?php if (empty($products)): ?>
                    <a class="no_data">Здесь пока ничего нет</a>
                <?php endif; ?>
                <?php foreach ($products as $data): ?>
                    <div class="prod">
                        <?php if (isset($data['sale'])): ?>
                            <a class="sale">-<?php echo $data['sale']; ?>%</a>
                        <?php endif; ?>
                        <a href="/product.php?id=<?php echo $data['id'] ?>">
                            <img class="prod_pic" src="<?php echo $data['image']; ?>">
                            <div class="desc">
                                <a class="mnfct"><?php echo $data['manufacturer']; ?></a>
                                <a class="prod_name"><?php echo $data['name']; ?></a>
                            </div>
                            <div class="card_price">
                                <a class="price"><?php echo $data['price']; ?></a>
                                <a class="price_sign">₽</a>
                            </div>
                        </a>
                        <div class="btns">
                            <form action="/vendor/cart" method="post">
                                <input type="hidden" name="productID" value="<?php echo $data['id'] ?>">
                                <input type="hidden" name="action" value="active">
                                <button type="submit" class="cart_but">В корзину</button>
                            </form>
                            <form action="/vendor/wishlist" method="post">
                                <input type="hidden" name="productID" value="<?php echo $data['id'] ?>">
                                <button type="submit" name="action" value="active" class="fav_but <?php echo (checkWishlist($data['id']) ? 'wishlist' : ''); ?>">
                                    <svg width="30px" height="30px" viewBox="0 0 32 32">
                                        <path d="M26 1.25h-20c-0.414 0-0.75 0.336-0.75 0.75v0 28.178c0 0 0 0 0 0.001 0 0.414 0.336 0.749 0.749 0.749 0.181
                                        0 0.347-0.064 0.476-0.171l-0.001 0.001 9.53-7.793 9.526 7.621c0.127 0.102 0.29 0.164 0.468 0.164 0.414 0 0.75-0.336
                                        0.751-0.75v-28c-0-0.414-0.336-0.75-0.75-0.75v0z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../components/footer.php' ?>
</body>
</html>