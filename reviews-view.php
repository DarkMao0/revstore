<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$user = $_SESSION['user'] ?? null;

require_once __DIR__ . '/control/functions.php';

$pdo = getPDO();

// Получение id товара и валидация
$product_id = $_GET['product_id'] ?? null;
if (!$product_id || !filter_var($product_id, FILTER_VALIDATE_INT) || $product_id <= 0) {
    error_log("Invalid product ID: " . var_export($product_id, true));
    http_response_code(404);
    if (file_exists('errors/404.php')) {
        require('errors/404.php');
    } else {
        echo "404 Not Found: File errors/404.php not found";
    }
    die();
}

// Получение информации о товаре (для заголовка и ссылки)
$product_query = "SELECT name FROM products WHERE id = ?";
$product_stmt = $pdo->prepare($product_query);
try {
    $product_stmt->execute([$product_id]);
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
    error_log("Product not found for ID: $product_id");
    http_response_code(404);
    if (file_exists('errors/404.php')) {
        require('errors/404.php');
    } else {
        echo "404 Not Found: File errors/404.php not found";
    }
    die();
}

// Получение среднего рейтинга
$avgRatingStmt = $pdo->prepare("SELECT AVG(rating) as average_rating FROM reviews WHERE product_id = ?");
$avgRatingStmt->execute([$product_id]);
$avgRatingResult = $avgRatingStmt->fetch(PDO::FETCH_ASSOC);
$averageRating = $avgRatingResult['average_rating'] ? $avgRatingResult['average_rating'] : 0;
$averageRank = getRankFromRating($averageRating);

// Получение всех отзывов с изображениями через JOIN
$reviewsStmt = $pdo->prepare("
    SELECT r.*, ri.image_path
    FROM reviews r
    LEFT JOIN review_images ri ON r.id = ri.review_id
    WHERE r.product_id = ?
    ORDER BY r.date DESC
");
try {
    $reviewsStmt->execute([$product_id]);
    $rows = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Failed to fetch reviews: " . $e->getMessage());
    $rows = [];
}

// Группируем отзывы и изображения
$reviews = [];
foreach ($rows as $row) {
    $rid = $row['id'];
    if (!isset($reviews[$rid])) {
        $reviews[$rid] = $row;
        $reviews[$rid]['images'] = [];
    }
    if ($row['image_path']) {
        $reviews[$rid]['images'][] = $row['image_path'];
    }
}
$reviews = array_values($reviews);

// Получение сообщений из сессии
$reviewMessage = getAlert('review_message');
?>
<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>REVSTORE - Отзывы о <?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/review.css">
    <script defer src="js/common.js"></script>
    <script defer src="js/modal.js"></script>
</head>
<body>
<?php include_once __DIR__ . '/components/header.php' ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <h1 class="prod_name">Отзывы о <?php echo htmlspecialchars($product['name']); ?></h1>
            <a href="/product-view.php?id=<?php echo $product_id; ?>" class="back_link">Вернуться к товару</a>
            <div class="reviews_con">
                <!-- Средний рейтинг -->
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
                <!-- Сообщение -->
                <?php if ($reviewMessage): ?>
                    <div id="message" class="<?php echo strpos($reviewMessage, 'успешно') !== false ? 'success_message' : 'error'; ?>">
                        <?php echo htmlspecialchars($reviewMessage); ?>
                    </div>
                <?php endif; ?>
                <!-- Все отзывы с изображениями -->
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
                            <!-- Изображения отзыва -->
                            <?php
                            $images = $review['images'] ?? [];
                            ?>
                            <?php if ($images): ?>
                                <div class="review-images">
                                    <?php foreach ($images as $image_path): ?>
                                        <img src="<?php echo htmlspecialchars($image_path); ?>" alt="Фото отзыва" class="review-image">
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="no-images">Нет изображений для этого отзыва.</p>
                            <?php endif; ?>
                            <?php if ($user && $review['user_id'] == $user['id']): ?>
                                <div class="review-actions">
                                    <form method="post" action="/control/review.php" class="delete-review-form">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <button type="submit">Удалить</button>
                                    </form>
                                    <a href="/user/edit-review-view.php?review_id=<?php echo $review['id']; ?>&product_id=<?php echo $product_id; ?>">Изменить</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <!-- Модальное окно для полноразмерного изображения -->
                <div id="imageModal" class="modal">
                    <span class="close">×</span>
                    <div class="modal-content">
                        <img id="modalImage" src="" alt="Полноразмерное изображение">
                    </div>
                </div>
                <!-- Форма добавления отзыва -->
                <div class="review_form">
                    <h3>Оставить отзыв</h3>
                    <?php if (!$user): ?>
                        <p class="auth_message">Пожалуйста, <a href="/signin-view.php">войдите</a> в аккаунт, чтобы оставить отзыв.</p>
                    <?php else: ?>
                        <a href="/user/add-review-view.php?product_id=<?php echo $product_id; ?>" class="add_review_link">Добавить отзыв</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/components/footer.php' ?>
</body>
</html>