<?php
require_once __DIR__ . '/../vendor/functions.php';
denyNoUser();
denyNoAdmin();
?>

<!DOCTYPE html>
<html  lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Cerama Granit - Управление заказами</title>
    <link rel="icon" href="/img/fav.png" type="image/x-icon">
    <link rel="stylesheet" href="/css/common.css">
    <script defer src="../js/common.js"></script>
</head>
<body>
<?php include_once __DIR__ . '/../components/header.php' ?>
<main class="content">
    <div class="con">
        <div class="main_dir">

        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../components/footer.php' ?>
</body>
</html>
