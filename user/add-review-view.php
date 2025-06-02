<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../control/functions.php';

// Логирование для отладки
error_log("add-review-view - Метод запроса: " . $_SERVER['REQUEST_METHOD']);
error_log("add-review-view - Параметры запроса: " . var_export($_GET, true));

// Проверка авторизации
$user = authorizedUserData();
if (!$user) {
    error_log("add-review-view - Пользователь не авторизован");
    setAlert('review_message', 'Пожалуйста, войдите в аккаунт, чтобы оставить отзыв');
    header("Location: /signin-view.php");
    exit;
}

// Получение product_id из URL
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
if ($product_id === null || $product_id <= 0) {
    error_log("add-review-view - ID товара отсутствует или некорректен: " . var_export($product_id, true));
    setAlert('review_message', 'Ошибка: ID товара не передан или некорректен');
    header("Location: /");
    exit;
}

// Проверка существования товара
$pdo = getPDO();
$productStmt = $pdo->prepare("SELECT id, name FROM products WHERE id = ?");
$productStmt->execute([$product_id]);
$product = $productStmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    error_log("add-review-view - Товар не найден для ID: $product_id");
    setAlert('review_message', 'Ошибка: Товар не найден');
    header("Location: /");
    exit;
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
    $comment = $_POST['comment'] ?? null;
    $images = $_FILES['images'] ?? [];

    error_log("add-review-view - Данные формы: рейтинг=$rating, комментарий=" . substr($comment, 0, 50));
    error_log("add-review-view - Загруженные файлы: " . var_export($images, true));

    // Проверка количества изображений
    if (!empty($images['tmp_name']) && count(array_filter($images['tmp_name'])) > 3) {
        error_log("add-review-view - Слишком много изображений: " . count(array_filter($images['tmp_name'])));
        setAlert('review_message', 'Ошибка: Можно загрузить не более 3 изображений');
        header("Location: /add-review-view.php?product_id=$product_id");
        exit;
    }

    $response = addReview($product_id, $rating, $comment, $user, $images);
    setAlert('review_message', $response['message']);
    error_log("add-review-view - Ответ отзыва: " . var_export($response, true));

    // Прямой редирект на страницу товара
    $redirectUrl = "/product-view.php?id=" . $product_id;
    error_log("add-review-view - Перенаправление на: $redirectUrl");
    header("Location: $redirectUrl");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Добавить отзыв - REVSTORE</title>
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
        .form_group.images {
            margin-top: 15px;
        }
        .images-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .images-input {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
    </style>
</head>
<body>
<?php include_once __DIR__ . '/../components/header.php' ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <h1>Добавить отзыв для "<?php echo htmlspecialchars($product['name']); ?>"</h1>
            <div class="review_form">
                <form method="post" enctype="multipart/form-data">
                    <div class="form_group">
                        <label for="rating">Рейтинг:</label>
                        <input type="hidden" id="rating" name="rating">
                        <div class="rating-scale">
                            <div class="rating-bar" data-value="1" data-rank="D"></div>
                            <div class="rating-bar" data-value="2" data-rank="C"></div>
                            <div class="rating-bar" data-value="3" data-rank="B"></div>
                            <div class="rating-bar" data-value="4" data-rank="A"></div>
                            <div class="rating-bar" data-value="5" data-rank="S"></div>
                        </div>
                        <div class="rating-label" id="rating-label"></div>
                    </div>
                    <div class="form_group">
                        <label for="comment">Комментарий:</label>
                        <textarea id="comment" name="comment" required maxlength="250"></textarea>
                    </div>
                    <div class="form_group images">
                        <label class="images-label" for="images">Прикрепить изображения (до 3, JPEG/PNG):</label>
                        <input type="file" id="images" name="images[]" accept="image/jpeg,image/png" multiple>
                        <small>Максимум 3 изображения, каждый до 5MB.</small>
                    </div>
                    <button type="submit" class="review_submit">Отправить отзыв</button>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../components/footer.php' ?>
</body>
</html>