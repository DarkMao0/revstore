<?php
require_once __DIR__ . '/vendor/functions.php';

$pdo = getPDO();

$query = "SELECT id, manufacturer, name, price, sale, image FROM products";

$sql_query = [];
$params = [];

searchQuery($sql_query, $params, $_GET);

filterQuery('type', $sql_query, $params, $_GET);
filterQuery('shape', $sql_query, $params, $_GET);
filterQuery('volume', $sql_query, $params, $_GET);
filterQuery('manufacturer', $sql_query, $params, $_GET);

if (!empty($sql_query)) {
    $query .= " WHERE " . implode(' AND ', $sql_query);
}

sortQuery($query, $_GET);

$stmt = $pdo->prepare($query);
try {
    $stmt->execute($params);
} catch (\PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    require('errors/500.php');
    die();
}

$sortQuery = http_build_query(array_diff_key($_GET, ['sort' => '']));
$activeSort = $_GET['sort'] ?? 'default';

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Вычисление среднего рейтинга для каждого товара
$productRatings = [];
foreach ($products as $product) {
    $avgRatingStmt = $pdo->prepare("SELECT AVG(rating) as average_rating FROM reviews WHERE product_id = ?");
    $avgRatingStmt->execute([$product['id']]);
    $avgRatingResult = $avgRatingStmt->fetch(PDO::FETCH_ASSOC);
    $productRatings[$product['id']] = $avgRatingResult['average_rating'] ? round($avgRatingResult['average_rating'], 1) : 0;
}
?>

<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>REVSTORE - Каталог</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/catalog.css">
    <script defer src="/js/scroll.js"></script>
    <script defer src="/js/catalog.js"></script>
</head>
<body>
    <?php include_once __DIR__ . '/components/header.php' ?>
    <main class="content">
        <div class="con">
            <div class="main_dir">
                <div class="catalog">
                    <div class="fil_field">
                        <h3 class="name">Фильтры</h3>
                        <form class="filter_content" method="get">
                            <?php if (isset($_GET['search'])): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <?php endif; ?>
                            <div class="filter_box">
                                <div class="open_list">
                                    <a>Тип покрытия</a>
                                </div>
                                <div class="filter">
                                    <div class="checkbox">
                                        <input
                                            type="checkbox"
                                            id="cb1"
                                            class="checkbox_input"
                                            name="type[]"
                                            value="Матовое"
                                            <?php if (isset($_GET['type']) && in_array('Матовое', $_GET['type'])) echo 'checked'; ?>
                                        >
                                        <label class="checkbox_label" for="cb1">Матовое</label>
                                    </div>
                                    <div class="checkbox">
                                        <input
                                            type="checkbox"
                                            id="cb2"
                                            class="checkbox_input"
                                            name="type[]"
                                            value="Глянцевое"
                                            <?php if (isset($_GET['type']) && in_array('Глянцевое', $_GET['type'])) echo 'checked'; ?>
                                        >
                                        <label class="checkbox_label" for="cb2">Глянцевое</label>
                                    </div>
                                </div>
                            </div>
                            <div class="filter_box">
                                <div class="open_list">
                                    <a>Форма</a>
                                </div>
                                <div class="filter">
                                    <div class="checkbox">
                                        <input
                                            type="checkbox"
                                            id="cb3"
                                            class="checkbox_input"
                                            name="shape[]"
                                            value="Квадрат"
                                            <?php if (isset($_GET['shape']) && in_array('Квадрат', $_GET['shape'])) echo 'checked'; ?>
                                        >
                                        <label class="checkbox_label" for="cb3">Квадрат</label>
                                    </div>
                                    <div class="checkbox">
                                        <input
                                            type="checkbox"
                                            id="cb4"
                                            class="checkbox_input"
                                            name="shape[]"
                                            value="Прямоугольник"
                                            <?php if (isset($_GET['shape']) && in_array('Прямоугольник', $_GET['shape'])) echo 'checked'; ?>
                                        >
                                        <label class="checkbox_label" for="cb4">Прямоугольник</label>
                                    </div>
                                </div>
                            </div>
                            <div class="filter_box">
                                <div class="open_list">
                                    <a>Объёмная поверхность</a>
                                </div>
                                <div class="filter">
                                    <div class="checkbox">
                                        <input
                                            type="checkbox"
                                            id="cb5"
                                            class="checkbox_input"
                                            name="volume[]"
                                            value="Да"
                                            <?php if (isset($_GET['volume']) && in_array('Да', $_GET['volume'])) echo 'checked'; ?>
                                        >
                                        <label class="checkbox_label" for="cb5">Да</label>
                                    </div>
                                    <div class="checkbox">
                                        <input
                                            type="checkbox"
                                            id="cb6"
                                            class="checkbox_input"
                                            name="volume[]"
                                            value="Нет"
                                            <?php if (isset($_GET['volume']) && in_array('Нет', $_GET['volume'])) echo 'checked'; ?>
                                        >
                                        <label class="checkbox_label" for="cb6">Нет</label>
                                    </div>
                                </div>
                            </div>
                            <div class="filter_box">
                                <div class="open_list">
                                    <a>Производитель</a>
                                </div>
                                <div class="filter">
                                    <div class="checkbox">
                                        <input
                                            type="checkbox"
                                            id="cb7"
                                            class="checkbox_input"
                                            name="manufacturer[]"
                                            value="Россия"
                                            <?php if (isset($_GET['manufacturer']) && in_array('Россия', $_GET['manufacturer'])) echo 'checked'; ?>
                                        >
                                        <label class="checkbox_label" for="cb7">Россия</label>
                                    </div>
                                    <div class="checkbox">
                                        <input
                                            type="checkbox"
                                            id="cb8"
                                            class="checkbox_input"
                                            name="manufacturer[]"
                                            value="Иностранный"
                                            <?php if (isset($_GET['manufacturer']) && in_array('Иностранный', $_GET['manufacturer'])) echo 'checked'; ?>
                                        >
                                        <label class="checkbox_label" for="cb8">Иностранный</label>
                                    </div>
                                </div>
                            </div>
                            <?php if (isset($_GET['sort'])): ?>
                                <input type="hidden" name="sort" value="<?php echo $_GET['sort'] ?>">
                            <?php endif; ?>
                            <button type="submit" id="filter_submit" class="filter_submit">Применить фильтры</button>
                        </form>
                    </div>
                    <div class="items">
                        <?php if (empty($products)): ?>
                            <a class="no_result">Ничего не найдено</a>
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
                                <!-- Средний рейтинг в виде ранга -->
                                <div class="average-rating">
                                    <?php
                                    $averageRating = $productRatings[$data['id']];
                                    if ($averageRating > 0) {
                                        $averageRank = getRankFromRating($averageRating);
                                        echo '<div class="rank-container">';
                                        echo '<p class="rank-text">RANK:</p> <span class="rank rank-' . $averageRank . '">' . $averageRank . '</span>';
                                        echo '</div>';
                                        echo '<span class="rating-value">(' . number_format($averageRating, 1) . '/5)</span>';
                                    } else {
                                        echo "Нет оценок";
                                    }
                                    ?>
                                </div>
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
        </div>
    </main>
    <?php include_once __DIR__ . '/components/footer.php' ?>
</body>
</html>