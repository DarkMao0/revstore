<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/functions.php';

$pdo = getPDO();

// Получение id товара и валидация
$info = $_GET['id'] ?? null;
if (!$info || !filter_var($info, FILTER_VALIDATE_INT) || $info <= 0) {
    error_log("Invalid product ID: " . var_export($info, true));
    http_response_code(404);
    if (file_exists('errors/404.php')) {
        require('errors/404.php');
    } else {
        echo "404 Not Found: File errors/404.php not found";
    }
    die();
}

// Получение товара
$product_query = "SELECT * FROM products WHERE id = ?";
$product_stmt = $pdo->prepare($product_query);
try {
    $product_stmt->execute([$info]);
} catch (\PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    if (file_exists('errors/500.php')) {
        require('errors/500.php');
    } else {
        echo "500 Internal Server Error: File errors/500.php not found";
    }
    die();
}
$product = $product_stmt->fetch(PDO::FETCH_ASSOC);

if (empty($product)) {
    error_log("Product not found for ID: $info");
    http_response_code(404);
    if (file_exists('errors/404.php')) {
        require('errors/404.php');
    } else {
        echo "404 Not Found: File errors/404.php not found";
    }
    die();
}

// Получение фильтров
$filter_query = "SELECT f.filter_name, fv.value FROM filter_connection fc JOIN filter_values fv ON fc.fvalue_id = fv.id JOIN filters f ON fv.filter_id = f.id WHERE fc.product_id = ? ORDER BY f.filter_name, fv.value";
$filter_stmt = $pdo->prepare($filter_query);
try {
    $filter_stmt->execute([$info]);
    $filters = $filter_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log($e->getMessage());
    $filters = [];
}

// Получение среднего рейтинга
$avgRatingStmt = $pdo->prepare("SELECT AVG(rating) as average_rating FROM reviews WHERE product_id = ?");
$avgRatingStmt->execute([$info]);
$avgRatingResult = $avgRatingStmt->fetch(PDO::FETCH_ASSOC);
$averageRating = $avgRatingResult['average_rating'] ? $avgRatingResult['average_rating'] : 0;
$averageRank = getRankFromRating($averageRating);

// Получение отзывов
$reviewsStmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY date DESC");
try {
    $reviewsStmt->execute([$info]);
    $reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Failed to fetch reviews: " . $e->getMessage());
    $reviews = [];
}

// Получение сообщения из сессии
$reviewMessage = getAlert('review_message');
?>
<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Cerama Granit - <?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="icon" href="/img/fav.png" type="image/x-icon">
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/product.css">
    <link rel="stylesheet" href="/css/review.css">
    <script defer src="js/common.js"></script>
</head>
<body>
<?php include_once __DIR__ . '/components/header.php' ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <h1 class="prod_name"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="prod_con">
                <div class="prod_img">
                    <?php if (isset($product['sale'])): ?>
                        <a class="sale">-<?php echo htmlspecialchars($product['sale']); ?>%</a>
                    <?php endif; ?>
                    <img src="<?php echo htmlspecialchars($product['image']); ?>">
                </div>
                <div class="prod_info">
                    <div class="interact_con">
                        <div class="prod_price">
                            <a class="price"><?php echo number_format($product['price'], 0, '', ' '); ?></a>
                            <a class="price_sign">₽</a>
                        </div>
                        <form action="/vendor/wishlist" method="post">
                            <input type="hidden" name="productID" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="action" value="active" class="prod_fav <?php echo (checkWishlist($product['id']) ? 'wishlist' : ''); ?>">
                                <svg width="30px" height="30px" viewBox="0 0 32 32">
                                    <path d="M26 1.25h-20c-0.414 0-0.75 0.336-0.75 0.75v0 28.178c0 0 0 0 0 0.001 0 0.414 0.336 0.749 0.749 0.749 0.181
                                    0 0.347-0.064 0.476-0.171l-0.001 0.001 9.53-7.793 9.526 7.621c0.127 0.102 0.29 0.164 0.468 0.164 0.414 0 0.75-0.336
                                    0.751-0.75v-28c-0-0.414-0.336-0.75-0.75-0.75v0z"/>
                                </svg>
                            </button>
                        </form>
                        <?php if ($product['available'] > 0): ?>
                        <form action="/vendor/cart" method="post">
                            <input type="hidden" name="productID" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="action" value="active">
                            <button type="submit" class="prod_cart">В корзину</button>
                        </form>
                        <?php else: ?>
                            <button type="button" class="prod_unavailable" disabled>Нет в наличии</button>
                        <?php endif; ?>
                    </div>
                    <div class="chars_con">
                        <h3>Характеристики товара</h3>
                        <?php if (!empty($filters)): ?>
                            <?php foreach ($filters as $filter): ?>
                                <div class="char">
                                    <a class="char_name"><?php echo htmlspecialchars($filter['filter_name']); ?>:</a>
                                    <a class="prod_char"><?php echo htmlspecialchars($filter['value']); ?></a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="char">
                                <a class="prod_char">Характеристики отсутствуют</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="desc_con">
                        <h3>Описание товара</h3>
                        <a class="prod_desc"><?php echo htmlspecialchars($product['description']); ?></a>
                    </div>
                    <div class="reviews_con">
                        <h3>Отзывы</h3>
                        <!-- averank -->
                        <?php if ($averageRating > 0): ?>
                            <div class="average-rating">
                                <div class="rank-container">
                                    <span class="rank-text">RANK:</span>
                                    <span class="rank rank-<?php echo $averageRank; ?>"><?php echo $averageRank; ?></span>
                                </div>
                                <span class="rating-value"><?php echo number_format($averageRating, 1); ?>/5</span>
                            </div>
                        <?php else: ?>
                            <p class="no_reviews">Нет оценок.</p>
                        <?php endif; ?>
                       <!-- message -->
                        <?php if ($reviewMessage): ?>
                            <div id="message" class="<?php echo strpos($reviewMessage, 'успешно') !== false ? 'success_message' : 'error'; ?>">
                                <?php echo htmlspecialchars($reviewMessage); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (empty($reviews)): ?>
                            <p class="no_reviews">Пока нет отзывов. Будьте первым, кто оставит отзыв!</p>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="review">
                                    <div class="review_header">
                                        <span class="review_user"><?php echo htmlspecialchars($review['user_name']); ?></span>
                                        <span class="review_date"><?php echo date('d.m.Y', strtotime($review['date'])); ?></span>
                                        <span class="review_rating">
                                            <div class="rank-container">
                                                <span class="rank-text">RANK:</span>
                                                <span class="rank rank-<?php echo getRankFromRating($review['rating']); ?>"><?php echo getRankFromRating($review['rating']); ?></span>
                                            </div>
                                            <span class="rating-value"><?php echo $review['rating']; ?>/5</span>
                                        </span>
                                    </div>
                                    <p class="review_comment"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    <?php if ($user && $review['user_id'] == $user['id']): ?>
                                        <div class="review-actions">
                                            <form method="post" action="/vendor/review" class="delete-review-form">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <button type="submit">Удалить</button>
                                            </form>
                                            <a href="/user/edit-review-view.php?review_id=<?php echo $review['id']; ?>&product_id=<?php echo $product['id']; ?>">Изменить</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <!-- add review -->
                        <div class="review_form">
                            <h3>Оставить отзыв</h3>
                            <?php if (!$user): ?>
                                <p class="auth_message">Пожалуйста, <a href="/signin.php">войдите</a> в аккаунт, чтобы оставить отзыв.</p>
                            <?php else: ?>
                                <a href="/user/add-review-view.php?product_id=<?php echo $product['id']; ?>" class="add_review_link">Добавить отзыв</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/components/footer.php' ?>
</body>
</html>