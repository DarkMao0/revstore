<?php
require_once __DIR__ . '/control/functions.php';

$pdo = getPDO();

$query_main_slider = "SELECT * FROM mainslider";
$query_sponsors = "SELECT * FROM sponsors";

$query_random = "SELECT * FROM products ORDER BY RAND() LIMIT 8";
$query_new = "SELECT * FROM products ORDER BY id DESC LIMIT 8";
$query_sale = "SELECT * FROM products WHERE sale IS NOT NULL LIMIT 8";
$query_blade = "SELECT * FROM products WHERE category_id = 1 AND available > 0 LIMIT 8";

$stmt_main_slider = $pdo->prepare($query_main_slider);
$stmt_sponsors = $pdo->prepare($query_sponsors);
$stmt_random = $pdo->prepare($query_random);
$stmt_new = $pdo->prepare($query_new);
$stmt_sale = $pdo->prepare($query_sale);
$stmt_blade = $pdo->prepare($query_blade);

try {
    $stmt_main_slider->execute();
    $stmt_sponsors->execute();
    $stmt_random->execute();
    $stmt_new->execute();
    $stmt_sale->execute();
    $stmt_blade->execute();
} catch (\PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    require('errors/500.php');
    die();
}

$images_main_slider = $stmt_main_slider->fetchAll(PDO::FETCH_ASSOC);
$images_sponsors = $stmt_sponsors->fetchAll(PDO::FETCH_ASSOC);
$products_random = $stmt_random->fetchAll(PDO::FETCH_ASSOC);
$products_new = $stmt_new->fetchAll(PDO::FETCH_ASSOC);
$products_sale = $stmt_sale->fetchAll(PDO::FETCH_ASSOC);
$products_blade = $stmt_blade->fetchAll(PDO::FETCH_ASSOC);

// Получение средних рейтингов для всех товаров одним запросом
$product_ids = array_merge(
    array_column($products_random, 'id'),
    array_column($products_new, 'id'),
    array_column($products_sale, 'id'),
    array_column($products_blade, 'id')
);
$product_ids = array_unique($product_ids);

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
foreach (['products_random', 'products_new', 'products_sale', 'products_blade'] as $section) {
    foreach ($$section as &$product) {
        $product['average_rating'] = isset($ratings[$product['id']]) ? $ratings[$product['id']]['average_rating'] : 0;
        $product['average_rank'] = isset($ratings[$product['id']]) ? $ratings[$product['id']]['average_rank'] : null;
        $product['product_id'] = $product['id'];
        $product['product_name'] = $product['name'];
    }
    unset($product);
}
?>

<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>REVSTORE - MGRR Merch</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="stylesheet" href="/css/swiper_lib.css">
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/catalog.css">
    <link rel="stylesheet" href="/css/loader.css">
    <link rel="stylesheet" href="/css/review.css">
    <script defer src="/js/scroll.js"></script>
    <script defer src="/js/swiper_lib.js"></script>
    <script defer src="/js/swiper.js"></script>
    <script defer src="/js/loader.js"></script>
</head>
<body>
    <!-- Лоадер с полоской -->
    <div class="loader" id="loader">
        <img src="/img/svg/logo.svg" alt="Logo" class="logo">
        <div class="loading-container" id="loadingBar">
            <div class="loading-segment" id="segment1"></div>
            <div class="loading-segment" id="segment2"></div>
            <div class="loading-segment" id="segment3"></div>
            <div class="loading-segment" id="segment4"></div>
            <div class="loading-segment" id="segment5"></div>
        </div>
        <div class="loading-text" id="loadingText">LOADING: 0%</div>
    </div>

    <?php include_once __DIR__ . '/components/header.php' ?>
    <main class="content">
        <div class="con">
            <div class="main_dir">
                <div class="swiper sw1">
                    <div class="swiper-wrapper">
                        <?php foreach ($images_main_slider as $data_main_slider): ?>
                            <div class="swiper-slide sl1">
                                <a href="<?php echo htmlspecialchars($data_main_slider['link']); ?>">
                                    <img src="<?php echo htmlspecialchars($data_main_slider['image']); ?>">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination pag1"></div>
                </div>
            </div>
            <div class="main_dir">
                <h1>Случайные предложения</h1>
                <div class="swiper sw2">
                    <div class="swiper-wrapper">
                        <?php foreach ($products_random as $product): ?>
                            <div class="swiper-slide sl2">
                                <?php include __DIR__ . '/components/product.php' ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-prev prev2"></div>
                    <div class="swiper-button-next next2"></div>
                </div>
            </div>
            <div class="main_dir">
                <h1>Клинки</h1>
                <div class="swiper sw2">
                    <div class="swiper-wrapper">
                        <?php foreach ($products_blade as $product): ?>
                            <div class="swiper-slide sl2">
                                <?php include __DIR__ . '/components/product.php' ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-prev prev2"></div>
                    <div class="swiper-button-next next2"></div>
                </div>
            </div>
            <div class="main_dir">
                <h1>Новинки</h1>
                <div class="swiper sw2">
                    <div class="swiper-wrapper">
                        <?php foreach ($products_new as $product): ?>
                            <div class="swiper-slide sl2">
                                <?php include __DIR__ . '/components/product.php' ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-prev prev2"></div>
                    <div class="swiper-button-next next2"></div>
                </div>
            </div>
            <div class="main_dir">
                <h1>Скидки</h1>
                <div class="swiper sw2">
                    <div class="swiper-wrapper">
                        <?php foreach ($products_sale as $product): ?>
                            <div class="swiper-slide sl2">
                                <?php include __DIR__ . '/components/product.php' ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-prev prev2"></div>
                    <div class="swiper-button-next next2"></div>
                </div>
            </div>
            <div class="main_dir">
                <h1>Наши партнеры</h1>
                <div class="brands">
                    <?php foreach ($images_sponsors as $data_sponsors): ?>
                        <a href="<?php echo htmlspecialchars($data_sponsors['link']); ?>">
                            <img src="<?php echo htmlspecialchars($data_sponsors['image']); ?>">
                        </a>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
        <?php include_once __DIR__ . '/components/footer.php' ?>
    </body>
</html>