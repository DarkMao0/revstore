<?php
require_once __DIR__ . '/control/functions.php';

$pdo = getPDO();
$search = trim($_GET['search'] ?? '');

if (empty($search)) {
    redirect('/');
}

$categories = [];

$query = "SELECT p.id AS product_id, p.name AS product_name, p.price, p.sale, p.available, p.image, c.id AS category_id, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.name LIKE :search AND p.category_id IS NOT NULL";

$stmt = $pdo->prepare($query);
$params = '%' . $search . '%';

try {
    $stmt->execute(['search' => $params]);
}
catch (\PDOException $e) {
    error_log('SQL Error in search-view.php: ' . $e->getMessage());
    http_response_code(500);
    require 'errors/500.php';
    die();
}

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем product_id для рейтингов
$product_ids = array_column($results, 'product_id');
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
        error_log("Ошибка рейтинга: " . $e->getMessage());
    }
}

// Формируем категории с рейтингами
foreach ($results as $row) {
    $catId = $row['category_id'];
    $product_id = $row['product_id'];
    $categories[$catId] = $categories[$catId] ?? ['name' => $row['category_name'], 'products' => []];
    $row['average_rating'] = isset($ratings[$product_id]) ? $ratings[$product_id]['average_rating'] : 0;
    $row['average_rank'] = isset($ratings[$product_id]) ? $ratings[$product_id]['average_rank'] : null;
    $categories[$catId]['products'][] = $row;
}
?>
<!DOCTYPE html>
<html  lang="ru" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Cerama Granit - Поиск</title>
    <link rel="icon" href="/img/fav.png" type="image/x-icon">
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/catalog.css">
</head>
<body>
<?php include_once __DIR__ . '/components/header.php'; ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <?php if (empty($categories)): ?>
                <div class="no_result">
                    <a>Ничего не найдено</a>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <div class="search_block">
                        <h2><?php echo htmlspecialchars($category['name']); ?></h2>
                        <div class="items">
                            <?php foreach ($category['products'] as $product): ?>
                                <?php include __DIR__ . '/components/product.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/components/footer.php'; ?>
</body>
</html>