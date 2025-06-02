<?php
require_once __DIR__ . '/vendor/functions.php';

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
    error_log('SQL Error in search.php: ' . $e->getMessage());
    http_response_code(500);
    require 'errors/500.php';
    die();
}

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    $catId = $row['category_id'];
    $categories[$catId] = $categories[$catId] ?? ['name' => $row['category_name'], 'products' => []];
    $categories[$catId]['products'][] = [
        'product_id' => $row['product_id'],
        'product_name' => $row['product_name'],
        'price' => $row['price'],
        'sale' => $row['sale'],
        'available' => $row['available'],
        'image' => $row['image']
    ];
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