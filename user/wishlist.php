<?php
require_once __DIR__ . '/../control/functions.php';
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
} catch (\PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    require('errors/500.php');
    die();
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение средних рейтингов для всех товаров одним запросом
$product_ids = array_column($products, 'product_id');
$ratings = [];
if (!empty($product_ids)) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $avgRatingStmt = $pdo->prepare("SELECT product_id, AVG(rating) as average_rating FROM reviews WHERE product_id IN ($placeholders) GROUP BY product_id");
    try {
        $avgRatingStmt->execute($product_ids);
        $ratings_result = $avgRatingStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($ratings_result as $row) {
            $ratings[$row['product_id']] = [
                'average_rating' => round(floatval($row['average_rating']), 1),
                'average_rank' => getRankFromRating(floatval($row['average_rating']))
            ];
        }
    } catch (\PDOException $e) {
        error_log("Я ОПЯТЬ НАСРАЛ " . $e->getMessage());
    }
}
foreach ($products as &$product) {
    $product['average_rating'] = isset($ratings[$product['product_id']]) ? $ratings[$product['product_id']]['average_rating'] : 0;
    $product['average_rank'] = isset($ratings[$product['product_id']]) ? $ratings[$product['product_id']]['average_rank'] : null;
}
unset($product);
?>

<!DOCTYPE html>
<html lang="ru" dir="ltr">
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
            <?php else: ?>
                <a class="no_data">Здесь пока ничего нет</a>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../components/footer.php' ?>
</body>
</html>