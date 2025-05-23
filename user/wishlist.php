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
$productRatings = [];
foreach ($products as $product) {
    $avgRatingStmt = $pdo->prepare("SELECT AVG(rating) as average_rating FROM reviews WHERE product_id = ?");
    $avgRatingStmt->execute([$product['product_id']]);
    $avgRatingResult = $avgRatingStmt->fetch(PDO::FETCH_ASSOC);
    $productRatings[$product['product_id']] = $avgRatingResult['average_rating'] ? round($avgRatingResult['average_rating'], 1) : 0;
}
?>

<!DOCTYPE html>
<html  lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>REVSTORE - Закладки</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/wishlist.css">
    <link rel="stylesheet" href="/css/review.css">
    <script defer src="/js/scroll.js"></script>
</head>
<body>
<?php include_once __DIR__ . '/../components/header.php' ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <?php if (!empty($products)): ?>
                <div class="items">
                    <?php foreach ($products as $product): ?>
                        <?php include __DIR__ . '/../components/product.php' ?>
                    <?php endforeach; ?>
                </div>
            <?php else:; ?>
                <a class="no_data">Здесь пока ничего нет</a>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../components/footer.php' ?>
</body>
</html>