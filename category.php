<?php
require_once __DIR__ . '/control/functions.php';

$pdo = getPDO();
$categoryId = $_GET['id'];
$selectedFilters = $_GET['filter'] ?? [];
$search = trim($_GET['search'] ?? '');
$searchConditions = [];
$groupedFilters = [];

if (empty($categoryId)) {
    redirect('/catalog.php');
}

$productsQuery = "SELECT DISTINCT p.id AS product_id, p.name AS product_name, p.price, p.sale, p.available, p.image FROM products p WHERE p.category_id = ?";

$filtersQuery = "SELECT f.id AS filter_id, f.filter_name, fv.id AS value_id, fv.value AS filter_value FROM filters f LEFT JOIN filter_values fv ON f.id = fv.filter_id WHERE f.category_id = ?";

$params = [$categoryId];
foreach ($selectedFilters as $filterId => $valueIds) {
    if (!empty($valueIds)) {
        $filterConditions[] = "EXISTS (SELECT 1 FROM filter_connection fc WHERE fc.product_id = p.id AND fc.fvalue_id IN (" . implode(',', array_fill(0, count($valueIds), '?')) . "))";
        $params = array_merge($params, $valueIds);
    }
}

if (!empty($filterConditions)) {
    $productsQuery .= " AND (" . implode(' AND ', $filterConditions) . ")";
}

searchQuery($searchConditions, $params, $_GET);
if (!empty($searchConditions)) {
    $productsQuery .= " AND (" . implode(' AND ', $searchConditions) . ")";
}

sortQuery($productsQuery, $_GET);

$filtersStmt = $pdo->prepare($filtersQuery);
$productsStmt = $pdo->prepare($productsQuery);

try {
    $filtersStmt->execute([$categoryId]);
    $productsStmt->execute($params);
} catch (\PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    require 'errors/500.php';
    die();
}

$filtersData = $filtersStmt->fetchAll(PDO::FETCH_ASSOC);
$products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

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

// Привязка рейтингов к товарам
foreach ($products as &$product) {
    $product['average_rating'] = isset($ratings[$product['product_id']]) ? $ratings[$product['product_id']]['average_rating'] : 0;
    $product['average_rank'] = isset($ratings[$product['product_id']]) ? $ratings[$product['product_id']]['average_rank'] : null;
}
unset($product);

foreach ($filtersData as $filter) {
    $name = $filter['filter_name'];
    $groupedFilters[$name] ??= ['filter_id' => $filter['filter_id'], 'values' => []];
    if (!empty($filter['filter_value'])) {
        $groupedFilters[$name]['values'][] = [
            'id' => $filter['value_id'],
            'value' => $filter['filter_value']
        ];
    }
}

$sortQuery = http_build_query(array_diff_key($_GET, ['sort' => '']));
$activeSort = $_GET['sort'] ?? 'default';
?>

<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>REVSTORE - Каталог</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/catalog.css">
    <link rel="stylesheet" href="/css/review.css">
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
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($categoryId); ?>">
                            <?php if (isset($_GET['search'])): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <?php endif; ?>
                            <?php foreach ($groupedFilters as $filterName => $filterData): ?>
                                <div class="filter_box">
                                    <div class="open_list">
                                        <a><?php echo htmlspecialchars($filterName); ?></a>
                                    </div>
                                    <div class="filter">
                                        <?php foreach ($filterData['values'] as $value): ?>
                                            <div class="checkbox">
                                                <input
                                                    type="checkbox"
                                                    id="cb_<?php echo $value['id']; ?>"
                                                    class="checkbox_input"
                                                    name="filter[<?php echo $filterData['filter_id']; ?>][]"
                                                    value="<?php echo $value['id']; ?>"
                                                    <?php if (isset($_GET['filter'][$filterData['filter_id']]) && in_array($value['id'], $_GET['filter'][$filterData['filter_id']])) echo 'checked'; ?>
                                                >
                                                <label class="checkbox_label" for="cb_<?php echo $value['id']; ?>">
                                                    <?php echo htmlspecialchars($value['value']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (isset($_GET['sort'])): ?>
                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort']); ?>">
                            <?php endif; ?>
                            <button type="submit" id="filter_submit" class="filter_submit">Применить фильтры</button>
                        </form>
                    </div>
                    <?php if (!empty($products)): ?>
                    <div class="items">
                        <?php foreach ($products as $product): ?>
                            <?php include __DIR__ . '/components/product.php' ?>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                        <div class="no_result">
                            <a>Ничего не найдено</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include_once __DIR__ . '/components/footer.php' ?>
</body>
</html>