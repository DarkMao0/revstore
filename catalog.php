<?php
require_once __DIR__ . '/vendor/functions.php';

$pdo = getPDO();

$query = "SELECT * FROM categories";
$stmt = $pdo->prepare($query);
try {
    $stmt->execute();
}
catch (\PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    require('errors/500.php');
    die();
}

$category = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html  lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Cerama Granit - Категории товара</title>
    <link rel="icon" href="/img/fav.png" type="image/x-icon">
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/catalog.css">
    <script defer src="js/common.js"></script>
</head>
<body>
<?php include_once __DIR__ . '/components/header.php' ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <div class="items">
                <?php foreach ($category as $data): ?>
                    <div class="category">
                        <a href="/category.php?id=<?php echo $data['id'] ?>">
                            <img class="category_pic" src="<?php echo $data['category_image']; ?>">
                            <div class="category_desc">
                                <span class="category_name"><?php echo $data['category_name']; ?></span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/components/footer.php' ?>
</body>
</html>
