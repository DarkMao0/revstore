<?php
require_once __DIR__ . '/vendor/functions.php';

$pdo = getPDO();

$query_main_slider = "SELECT * FROM mainslider";
$query_sponsors = "SELECT * FROM sponsors";

$query_random = "SELECT * FROM products ORDER BY RAND() LIMIT 8";
$query_new = "SELECT * FROM products ORDER BY id DESC LIMIT 8";
$query_sale = "SELECT * FROM products WHERE sale IS NOT NULL LIMIT 8";

$stmt_main_slider = $pdo->prepare($query_main_slider);
$stmt_sponsors = $pdo->prepare($query_sponsors);

$stmt_random = $pdo->prepare($query_random);
$stmt_new = $pdo->prepare($query_new);
$stmt_sale = $pdo->prepare($query_sale);

try {
    $stmt_main_slider->execute();
    $stmt_sponsors->execute();

    $stmt_random->execute();
    $stmt_new->execute();
    $stmt_sale->execute();
} catch (\PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    require('errors/500.php');
    die();
}

$images_main_slider = $stmt_main_slider->fetchAll(PDO::FETCH_ASSOC);
$images_sponsors = $stmt_sponsors->fetchAll(PDO::FETCH_ASSOC);

$products_random = $stmt_random->fetchAll(PDO::FETCH_ASSOC);
$products_new = $stmt_new->fetchAll(PDO::FETCH_ASSOC);
$products_sale = $stmt_sale->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>REVSTORE - MGRR Merch</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="stylesheet" href="/css/swiper_lib.css">
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/catalog.css">
    <link rel="stylesheet" href="/css/loader.css">
    <script defer src="/js/scroll.js"></script>
    <script defer src="/js/swiper_lib.js"></script>
    <script defer src="/js/swiper.js"></script>
    <script defer src="/js/loader.js"></script>
</head>
<body>
    <!-- Лоадер с полоской -->
    <div class="loader" id="loader">
        <img src="/img/svg/logo.svg" alt="Logo" class="logo">
        <div class="loading-container" id="loadingBar">
            <div class="loading-segment" id="segment1"></div>
            <div class="loading-segment" id="segment2"></div>
            <div class="loading-segment" id="segment3"></div>
            <div class="loading-segment" id="segment4"></div>
            <div class="loading-segment" id="segment5"></div>
        </div>
        <div class="loading-text" id="loadingText">LOADING: 0%</div>
    </div>

    <?php include_once __DIR__ . '/components/header.php' ?>
    <main class="content">
        <div class="con">
            <div class="main_dir">
                <div class="swiper sw1">
                    <div class="swiper-wrapper">
                        <?php foreach ($images_main_slider as $data_main_slider): ?>
                            <div class="swiper-slide sl1">
                                <a href="<?php echo $data_main_slider['link']; ?>">
                                    <img src="<?php echo $data_main_slider['image']; ?>">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination pag1"></div>
                </div>
            </div>
            <div class="main_dir">
                <h1>Случайные предложения</h1>
                <div class="swiper sw2">
                    <div class="swiper-wrapper">
                        <?php foreach ($products_random as $data_random): ?>
                            <div class="swiper-slide sl2">
                                <div class="prod">
                                    <?php if (isset($data_random['sale'])): ?>
                                        <a class="sale">-<?php echo $data_random['sale']; ?>%</a>
                                    <?php endif; ?>
                                    <a href="/product.php?id=<?php echo $data_random['id'] ?>">
                                        <img class="prod_pic" src="<?php echo $data_random['image']; ?>">
                                        <div class="desc">
                                            <span class="mnfct"><?php echo $data_random['manufacturer']; ?></span>
                                            <span class="prod_name"><?php echo $data_random['name']; ?></span>
                                        </div>
                                        <div class="price">
                                            <span><?php echo $data_random['price']; ?></span>
                                            <span class="price_sign">₽</span>
                                        </div>
                                    </a>
                                    <div class="btns">
                                        <form action="/vendor/cart" method="post">
                                            <input type="hidden" name="productID" value="<?php echo $data_random['id'] ?>">
                                            <input type="hidden" name="action" value="active">
                                            <button type="submit" class="cart_but">В корзину</button>
                                        </form>
                                        <form action="/vendor/wishlist" method="post">
                                            <input type="hidden" name="productID" value="<?php echo $data_random['id'] ?>">
                                            <button type="submit" name="action" value="active" class="fav_but <?php echo (checkWishlist($data_random['id']) ? 'wishlist' : ''); ?>">
                                                <svg width="30px" height="30px" viewBox="0 0 32 32">
                                                    <path d="M26 1.25h-20c-0.414 0-0.75 0.336-0.75 0.75v0 28.178c0 0 0 0 0 0.001 0 0.414 0.336 0.749 0.749 0.749 0.181
                                                    0 0.347-0.064 0.476-0.171l-0.001 0.001 9.53-7.793 9.526 7.621c0.127 0.102 0.29 0.164 0.468 0.164 0.414 0 0.75-0.336
                                                    0.751-0.75v-28c-0-0.414-0.336-0.75-0.75-0.75v0z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-prev prev2"></div>
                    <div class="swiper-button-next next2"></div>
                </div>
            </div>
            <div class="main_dir">
                <h1>Новинки</h1>
                <div class="swiper sw2">
                    <div class="swiper-wrapper">
                        <?php foreach ($products_new as $data_new): ?>
                            <div class="swiper-slide sl2">
                                <div class="prod">
                                    <?php if (isset($data_new['sale'])): ?>
                                        <a class="sale">-<?php echo $data_new['sale']; ?>%</a>
                                    <?php endif; ?>
                                    <a href="/product.php?id=<?php echo $data_new['id'] ?>">
                                        <img class="prod_pic" src="<?php echo $data_new['image']; ?>">
                                        <div class="desc">
                                            <span class="mnfct"><?php echo $data_new['manufacturer']; ?></span>
                                            <span class="prod_name"><?php echo $data_new['name']; ?></span>
                                        </div>
                                        <div class="price">
                                            <span><?php echo $data_new['price']; ?></span>
                                            <span class="price_sign">₽</span>
                                        </div>
                                    </a>
                                    <div class="btns">
                                        <form action="/vendor/cart" method="post">
                                            <input type="hidden" name="productID" value="<?php echo $data_new['id'] ?>">
                                            <input type="hidden" name="action" value="active">
                                            <button type="submit" class="cart_but">В корзину</button>
                                        </form>
                                        <form action="/vendor/wishlist" method="post">
                                            <input type="hidden" name="productID" value="<?php echo $data_new['id'] ?>">
                                            <button type="submit" name="action" value="active" class="fav_but <?php echo (checkWishlist($data_new['id']) ? 'wishlist' : ''); ?>">
                                                <svg width="30px" height="30px" viewBox="0 0 32 32">
                                                    <path d="M26 1.25h-20c-0.414 0-0.75 0.336-0.75 0.75v0 28.178c0 0 0 0 0 0.001 0 0.414 0.336 0.749 0.749 0.749 0.181
                                                    0 0.347-0.064 0.476-0.171l-0.001 0.001 9.53-7.793 9.526 7.621c0.127 0.102 0.29 0.164 0.468 0.164 0.414 0 0.75-0.336
                                                    0.751-0.75v-28c-0-0.414-0.336-0.75-0.75-0.75v0z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-prev prev2"></div>
                    <div class="swiper-button-next next2"></div>
                </div>
            </div>
            <div class="main_dir">
                <h1>Скидки</h1>
                <div class="swiper sw2">
                    <div class="swiper-wrapper">
                        <?php foreach ($products_sale as $data_sale): ?>
                            <div class="swiper-slide sl2">
                                <div class="prod">
                                    <?php if (isset($data_sale['sale'])): ?>
                                        <a class="sale">-<?php echo $data_sale['sale']; ?>%</a>
                                    <?php endif; ?>
                                    <a href="/product.php?id=<?php echo $data_sale['id'] ?>">
                                        <img class="prod_pic" src="<?php echo $data_sale['image']; ?>">
                                        <div class="desc">
                                            <span class="mnfct"><?php echo $data_sale['manufacturer']; ?></span>
                                            <span class="prod_name"><?php echo $data_sale['name']; ?></span>
                                        </div>
                                        <div class="price">
                                            <span><?php echo $data_sale['price']; ?></span>
                                            <span class="price_sign">₽</span>
                                        </div>
                                    </a>
                                    <div class="btns">
                                        <form action="/vendor/cart" method="post">
                                            <input type="hidden" name="productID" value="<?php echo $data_sale['id'] ?>">
                                            <input type="hidden" name="action" value="active">
                                            <button type="submit" class="cart_but">В корзину</button>
                                        </form>
                                        <form action="/vendor/wishlist" method="post">
                                            <input type="hidden" name="productID" value="<?php echo $data_sale['id'] ?>">
                                            <button type="submit" name="action" value="active" class="fav_but <?php echo (checkWishlist($data_sale['id']) ? 'wishlist' : ''); ?>">
                                                <svg width="30px" height="30px" viewBox="0 0 32 32">
                                                    <path d="M26 1.25h-20c-0.414 0-0.75 0.336-0.75 0.75v0 28.178c0 0 0 0 0 0.001 0 0.414 0.336 0.749 0.749 0.749 0.181
                                                    0 0.347-0.064 0.476-0.171l-0.001 0.001 9.53-7.793 9.526 7.621c0.127 0.102 0.29 0.164 0.468 0.164 0.414 0 0.75-0.336
                                                    0.751-0.75v-28c-0-0.414-0.336-0.75-0.75-0.75v0z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-prev prev2"></div>
                    <div class="swiper-button-next next2"></div>
                </div>
            </div>
            <div class="main_dir">
                <h1>Наши партнеры</h1>
                <div class="brands">
                    <?php foreach ($images_sponsors as $data_sponsors): ?>
                        <a href="<?php echo $data_sponsors['link']; ?>">
                            <img src="<?php echo $data_sponsors['image']; ?>">
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include_once __DIR__ . '/components/footer.php' ?>
</body>
</html>