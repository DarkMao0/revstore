<?php
require_once __DIR__ . '/../vendor/functions.php';

// Проверка авторизации
$user = authorizedUserData();
if (!$user) {
    redirect('/signin.php');
}

// Валидация параметров
$review_id = filter_input(INPUT_GET, 'review_id', FILTER_VALIDATE_INT);
$product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);

if (!$review_id || !$product_id) {
    setAlert('review_message', 'Ошибка: Неверные параметры.');
    redirect("/product.php?id=$product_id");
}

// Проверка существования отзыва и принадлежности пользователю
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ? AND product_id = ?");
$stmt->execute([$review_id, $product_id]);
$review = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$review || $review['user_id'] != $user['id']) {
    setAlert('review_message', 'Ошибка: Отзыв не найден или вы не можете его редактировать.');
    redirect("/product.php?id=$product_id");
}

// Получение информации о товаре
$productStmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
$productStmt->execute([$product_id]);
$product = $productStmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    setAlert('review_message', 'Ошибка: Товар не найден.');
    redirect('/catalog.php');
}

// Маппинг для инициализации текущего ранга
$rankMap = ['5' => 'S', '4' => 'A', '3' => 'B', '2' => 'C', '1' => 'D'];
$currentRank = $rankMap[$review['rating']] ?? 'D';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Редактировать отзыв - Revengeance Store</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/review.css">
    <script defer src="/js/rating-scale.js"></script>
    <style>
        .rating-scale {
            display: flex;
            gap: 5px;
            margin-top: 10px;
            cursor: pointer;
        }
        .rating-bar {
            width: 40px;
            height: 20px;
            background: #ccc;
            border-radius: 3px;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .rating-bar.active-S { background: linear-gradient(135deg, #FFC107 0%, #FFCA28 100%); transform: scale(1.1); }
        .rating-bar.active-A { background: linear-gradient(135deg, #FF9800 0%, #FFB300 100%); transform: scale(1.1); }
        .rating-bar.active-B { background: linear-gradient(135deg, #FFC107 0%, #FFCA28 100%); transform: scale(1.1); }
        .rating-bar.active-C { background: linear-gradient(135deg, #8BC34A 0%, #9CCC65 100%); transform: scale(1.1); }
        .rating-bar.active-D { background: linear-gradient(135deg, #4FC3F7 0%, #81D4FA 100%); transform: scale(1.1); }
        .rating-label {
            margin-top: 5px;
            font-family: "reg", sans-serif;
            color: #E0E0E0;
            text-shadow: 0 0 3px #00D4FF;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .rating-label.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../components/header.php'; ?>
    <main class="content">
        <div class="con">
            <div class="main_dir">
                <div class="review_form">
                    <h2>Редактировать отзыв о товаре: <?php echo htmlspecialchars($product['name']); ?></h2>
                    <?php if (checkAlert('review_message')): ?>
                        <div class="error_message"><?php echo getAlert('review_message'); ?></div>
                    <?php endif; ?>
                    <form action="/vendor/review" method="post">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="review_id" value="<?php echo $review_id; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="form_group">
                            <label for="rating">Рейтинг:</label>
                            <input type="hidden" id="rating" name="rating" value="<?php echo $review['rating']; ?>">
                            <div class="rating-scale">
                                <div class="rating-bar <?php echo $review['rating'] >= 1 ? 'active-D' : ''; ?>" data-value="1" data-rank="D"></div>
                                <div class="rating-bar <?php echo $review['rating'] >= 2 ? 'active-C' : ''; ?>" data-value="2" data-rank="C"></div>
                                <div class="rating-bar <?php echo $review['rating'] >= 3 ? 'active-B' : ''; ?>" data-value="3" data-rank="B"></div>
                                <div class="rating-bar <?php echo $review['rating'] >= 4 ? 'active-A' : ''; ?>" data-value="4" data-rank="A"></div>
                                <div class="rating-bar <?php echo $review['rating'] >= 5 ? 'active-S' : ''; ?>" data-value="5" data-rank="S"></div>
                            </div>
                            <div class="rating-label show" id="rating-label">RANK: <?php echo $currentRank; ?></div>
                        </div>
                        <div class="form_group">
                            <label for="comment">Комментарий:</label>
                            <textarea name="comment" id="comment"><?php echo htmlspecialchars($review['comment']); ?></textarea>
                            <?php if (checkError('comment')): ?>
                                <div class="error_message"><?php echo errorMessage('comment'); ?></div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="review_submit">Сохранить изменения</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php include_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>