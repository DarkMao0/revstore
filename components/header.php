<?php
require_once __DIR__ . '/../control/functions.php';
$user = authorizedUserData();
$user_id = $_SESSION['user']['id'] ?? null;

$pdo = getPDO();

$stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE userID = :userID");
try {
    $stmt->execute(['userID' => $user_id]);
}
catch (\PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    require('../errors/500.php');
    die();
}

$total_quantity = $stmt->fetchAll(PDO::FETCH_ASSOC);

global $activeSort, $sortQuery;

$currentUri = $_SERVER['REQUEST_URI'];
$searchValue = $_GET['search'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="/css/common.css">
    <link rel="icon" type="image/svg+xml" href="/img/favicon.svg" />
    <?php if (preg_match('~^/catalog(\?|$)~', $currentUri)): ?>
        <link rel="stylesheet" href="/css/catalog.css">
    <?php endif; ?>
    <script src="/js/burger.js" defer></script>
    <script src="/js/search.js" defer></script>
    <script>
        // Динамическая корректировка margin-top для .content на основе высоты шапки
        function adjustContentMargin() {
            const header = document.querySelector('.header');
            const content = document.querySelector('.content');
            if (header && content) {
                const headerHeight = header.offsetHeight;
                content.style.marginTop = `${headerHeight + 2}px`; // +10px запас
            }
        }
        // Выполняем корректировку при загрузке страницы и при изменении размера окна
        window.addEventListener('load', adjustContentMargin);
        window.addEventListener('resize', adjustContentMargin);

        // Обработка прокрутки для шапки и кнопки "Наверх"
        window.addEventListener('scroll', () => {
            const header = document.querySelector('.header');
            const backToTop = document.querySelector('.back-to-top');
            if (header && backToTop) {
                if (window.scrollY > 0) {
                    header.classList.add('header_shadow');
                    backToTop.classList.add('show_button');
                } else {
                    header.classList.remove('header_shadow');
                    backToTop.classList.remove('show_button');
                }
            }
        });

        // Плавная прокрутка к началу страницы после загрузки DOM
        document.addEventListener('DOMContentLoaded', () => {
            const backToTop = document.querySelector('.back-to-top');
            if (backToTop) {
                backToTop.addEventListener('click', () => {
                    console.log('Back to top clicked'); // Для отладки
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            } else {
                console.log('backToTop element not found'); // Для отладки
            }
        });
    </script>
</head>
<body>
    <header class="header">
        <div class="con">
            <div class="header_actions">
                <div class="top_row">
                    <div class="logo_and_profile">
                        <a href="/" title="На главную"><img class="log" src="/img/svg/logo.svg" alt="Logo"></a>
                        <div class="profile_actions">
                            <div class="header_cart">
                                 <?php foreach ($total_quantity as $cart_quantity): ?>
                                <?php if (!empty($cart_quantity['total'])): ?>
                                    <div class="quantity_mark">
                                        <a class="quantity_a">
                                            <?php if ($cart_quantity['total'] >= 100) {
                                                echo '99+';
                                            } else echo $cart_quantity['total']; ?>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <a class="user_pages" href="/user/cart.php">
                                    <svg class="user_pages_image" width="25px" height="25px" viewBox="0 0 510 510">
                                        <path d="M153,408c-28.05,0-51,22.95-51,51s22.95,51,51,51s51-22.95,51-51S181.05,408,153,408z M0,0v51h51l91.8,193.8L107.1,306
                                        c-2.55,7.65-5.1,17.85-5.1,25.5c0,28.05,22.95,51,51,51h306v-51H163.2c-2.55,0-5.1-2.55-5.1-5.1v-2.551l22.95-43.35h188.7
                                        c20.4,0,35.7-10.2,43.35-25.5L504.9,89.25c5.1-5.1,5.1-7.65,5.1-12.75c0-15.3-10.2-25.5-25.5-25.5H107.1L84.15,0H0z M408,408
                                        c-28.05,0-51,22.95-51,51s22.95,51,51,51s51-22.95,51-51S436.05,408,408,408z"/>
                                    </svg>
                                    <span class="user_pages_title">Корзина</span>
                                </a>
                            </div>
                            <div>
                                <a class="user_pages" href="/user/wishlist.php">
                                    <svg class="user_pages_image" width="25px" height="25px" viewBox="0 0 32 32">
                                        <path d="M26 1.25h-20c-0.414 0-0.75 0.336-0.75 0.75v0 28.178c0 0 0 0 0 0.001 0 0.414
                                        0.336 0.749 0.749 0.749 0.181 0 0.347-0.0640.476-0.171l-0.001 0.001 9.53-7.793 9.526 7.621c0.127
                                        0.102 0.29 0.164 0.468 0.164 0.414 0 0.75-0.336 0.751-0.75v-28c0-0.414-0.336-0.75-0.75-0.75v0z"/>
                                    </svg>
                                    <span class="user_pages_title">Закладки</span>
                                </a>
                            </div>
                            <div class="prof">
                                <?php if (isset($user['id']) && isset($user['avatar'])): ?>
                                    <a class="user_pages" href="/signin-view.php">
                                        <img class="header_avatar" src="<?php echo $user['avatar']; ?>" title="Профиль">
                                    </a>
                                <?php elseif (!isset($user['avatar']) && isset($user['id'])): ?>
                                    <a class="user_pages" href="/signin-view.php">
                                        <svg class="user_pages_image" width="25px" height="25px" viewBox="0 0 40 40">
                                        <path d="M20,0c-1.4,0-2.7,0.2-4,0.7c-1.2,0.5-2.3,1.2-3.2,2v0l0,0c-0.9,0.9-1.6,2-2,3.2C10.3,7.2,10,8.6,10,10c0,1.4,0.2,2.8,0.7,4
                                            c0.5,1.2,1.2,2.3,2,3.2l0,0c0.9,0.9,2,1.6,3.2,2.1v0c1.2,0.5,2.6,0.7,4,0.7c1.4,0,2.7-0.2,4-0.7v0c1.2-0.5,2.3-1.2,3.2-2.1
                                            c0.9-0.9,1.6-2,2.1-3.2c0.5-1.2,0.7-2.6,0.7-4h0c0-1.4-0.2-2.8-0.7-4c-0.5-1.2-1.2-2.3-2.1-3.2c-0.9-0.9-2-1.6-3.2-2.1
                                            C22.7,0.2,21.4,0,20,0z M20.1,26.7c-8.2,0-12.7,0.7-15.9,3c-1.6,1.2-2.7,2.7-3.3,4.6C0.2,36.1,0,37.5,0,40l20,0h20
                                            c0-2.5-0.2-3.9-0.8-5.7c-0.6-1.8-1.7-3.4-3.3-4.6C32.6,27.4,28.3,26.7,20.1,26.7z"/>
                                        </svg>
                                        <span class="user_pages_title">Профиль</span>
                                    </a>
                                <?php else: ?>
                                    <a class="user_pages" href="/signin-view.php">
                                        <svg class="user_pages_image" width="25px" height="25px" viewBox="0 0 409.165 409.164">
                                            <path d="M204.583,216.671c50.664,0,91.74-48.075,91.74-107.378c0-82.237-41.074-107.377-91.74-107.377
                                            c-50.668,0-91.74,25.14-91.74,107.377C112.844,168.596,153.916,216.671,204.583,216.671z"/>
                                            <path d="M407.164,374.717L360.88,270.454c-2.117-4.771-5.836-8.728-10.465-11.138l-71.83-37.392
                                            c-1.584-0.823-3.502-0.663-4.926,0.415c-20.316,15.366-44.203,23.488-69.076,23.488c-24.877,0-48.762-8.122-69.078-23.488
                                            c-1.428-1.078-3.346-1.238-4.93-0.415L58.75,259.316c-4.631,2.41-8.346,6.365-10.465,11.138L2.001,374.717
                                            c-3.191,7.188-2.537,15.412,1.75,22.005c4.285,6.592,11.537,10.526,19.4,10.526h362.861c7.863,0,15.117-3.936,19.402-10.527
                                            C409.699,390.129,410.355,381.902,407.164,374.717z"/>
                                        </svg>
                                        <span class="user_pages_title">Войти</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                            <div class="search_and_sort">
                                <div class="search_icon">
                                    <svg x="0px" y="0px" viewBox="0 0 122.9 119.8">
                                        <path class="st0" d="M50,0L50,0L50,0c13.8,0,26.3,5.6,35.4,14.7c9,9,14.6,21.5,14.6,35.3h0v0v0h0c0,5.6-0.9,10.9-2.6,15.9
                                        c-0.3,0.8-0.6,1.6-0.9,2.4v0c-1.4,3.7-3.3,7.1-5.5,10.3l29.1,26.1l0,0l0.2,0.1l0,0c1.6,1.6,2.5,3.7,2.6,5.8c0.1,2.1-0.5,4.2-2,6
                                        l0,0l-0.2,0.2l0,0l-0.1,0.2l0,0c-1.6,1.6-3.7,2.5-5.8,2.6c-2.1,0.1-4.2-0.5-6-2l0,0l-0.2-0.2l0,0L78.8,90.9
                                        c-0.9,0.6-1.8,1.2-2.7,1.8c-1.2,0.8-2.5,1.5-3.8,2.1C65.6,98.1,58,100,50,100v0h0v0c-13.8,0-26.3-5.6-35.3-14.6
                                        C5.6,76.3,0,63.8,0,50h0v0v0h0c0-13.8,5.6-26.3,14.6-35.3C23.7,5.6,36.2,0,50,0L50,0L50,0L50,0z M50,11.2L50,11.2L50,11.2L50,11.2
                                        L50,11.2c-10.7,0-20.4,4.3-27.4,11.4c-7,7-11.4,16.7-11.4,27.4h0v0v0h0c0,10.7,4.3,20.4,11.4,27.4c7,7,16.7,11.4,27.4,11.4v0h0h0v0
                                        c10.7,0,20.4-4.3,27.4-11.4c7-7,11.4-16.7,11.4-27.4h0v0v0h0c0-10.7-4.3-20.4-11.4-27.4C70.4,15.6,60.7,11.2,50,11.2L50,11.2z"/>
                                    </svg>
                                </div>
                                <div class="search_bar">
                                    <form action="<?php echo isset($_GET['id']) ? '/category.php' : '/search-view.php'; ?>" method="get" class="search">
                                        <?php if (isset($_GET['id'])): ?>
                                        <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
                                        <?php endif; ?>
                                        <input
                                            type="text"
                                            class="search_field"
                                            name="search"
                                            value="<?php echo htmlspecialchars($searchValue); ?>"
                                            placeholder="Поиск по товарам"
                                            autocomplete="off"
                                            spellcheck="false"
                                        >
                                        <button type="submit" class="search_but">
                                            <svg x="0px" y="0px" viewBox="0 0 122.9 119.8">
                                                <path class="st0" d="M50,0L50,0L50,0c13.8,0,26.3,5.6,35.4,14.7c9,9,14.6,21.5,14.6,35.3h0v0v0h0c0,5.6-0.9,10.9-2.6,15.9
                                                c-0.3,0.8-0.6,1.6-0.9,2.4v0c-1.4,3.7-3.3,7.1-5.5,10.3l29.1,26.1l0,0l0.2,0.1l0,0c1.6,1.6,2.5,3.7,2.6,5.8c0.1,2.1-0.5,4.2-2,6
                                                l0,0l-0.2,0.2l0,0l-0.1,0.2l0,0c-1.6,1.6-3.7,2.5-5.8,2.6c-2.1,0.1-4.2-0.5-6-2l0,0l-0.2-0.2l0,0L78.8,90.9
                                                c-0.9,0.6-1.8,1.2-2.7,1.8c-1.2,0.8-2.5,1.5-3.8,2.1C65.6,98.1,58,100,50,100v0h0v0c-13.8,0-26.3-5.6-35.3-14.6
                                                C5.6,76.3,0,63.8,0,50h0v0v0h0c0-13.8,5.6-26.3,14.6-35.3C23.7,5.6,36.2,0,50,0L50,0L50,0L50,0z M50,11.2L50,11.2L50,11.2L50,11.2
                                                L50,11.2c-10.7,0-20.4,4.3-27.4,11.4c-7,7-11.4,16.7-11.4,27.4h0v0v0h0c0,10.7,4.3,20.4,11.4,27.4c7,7,16.7,11.4,27.4,11.4v0h0h0v0
                                                c10.7,0,20.4-4.3,27.4-11.4c7-7,11.4-16.7,11.4-27.4h0v0v0h0c0-10.7-4.3-20.4-11.4-27.4C70.4,15.6,60.7,11.2,50,11.2L50,11.2z"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="clear_search">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                <div class="search_panel">
                                <form action="<?php echo isset($_GET['id']) ? '/category.php' : '/search-view.php'; ?>" method="get" class="search">
                                        <input
                                            type="text"
                                            class="search_field"
                                            name="search"
                                            value="<?php echo htmlspecialchars($searchValue); ?>"
                                            placeholder="Что найти?"
                                            autocomplete="off"
                                            spellcheck="false"
                                        >
                                        <button type="submit" class="search_but">
                                            <svg x="0px" y="0px" viewBox="0 0 122.9 119.8">
                                                <path class="st0" d="M50,0L50,0L50,0c13.8,0,26.3,5.6,35.4,14.7c9,9,14.6,21.5,14.6,35.3h0v0v0h0c0,5.6-0.9,10.9-2.6,15.9
                                                c-0.3,0.8-0.6,1.6-0.9,2.4v0c-1.4,3.7-3.3,7.1-5.5,10.3l29.1,26.1l0,0l0.2,0.1l0,0c1.6,1.6,2.5,3.7,2.6,5.8c0.1,2.1-0.5,4.2-2,6
                                                l0,0l-0.2,0.2l0,0l-0.1,0.2l0,0c-1.6,1.6-3.7,2.5-5.8,2.6c-2.1,0.1-4.2-0.5-6-2l0,0l-0.2-0.2l0,0L78.8,90.9
                                                c-0.9,0.6-1.8,1.2-2.7,1.8c-1.2,0.8-2.5,1.5-3.8,2.1C65.6,98.1,58,100,50,100v0h0v0c-13.8,0-26.3-5.6-35.3-14.6
                                                C5.6,76.3,0,63.8,0,50h0v0v0h0c0-13.8,5.6-26.3,14.6-35.3C23.7,5.6,36.2,0,50,0L50,0L50,0L50,0z M50,11.2L50,11.2L50,11.2L50,11.2
                                                L50,11.2c-10.7,0-20.4,4.3-27.4,11.4c-7,7-11.4,16.7-11.4,27.4h0v0v0h0c0,10.7,4.3,20.4,11.4,27.4c7,7,16.7,11.4,27.4,11.4v0h0h0v0
                                                c10.7,0,20.4-4.3,27.4-11.4c7-7,11.4-16.7,11.4-27.4h0v0v0h0c0-10.7-4.3-20.4-11.4-27.4C70.4,15.6,60.7,11.2,50,11.2L50,11.2z"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="clear_search">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="close_search">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                <?php if (preg_match('~^/category(\?|$)~', $currentUri)): ?>
                                    <div class="sort_con">
                                        <div class="sort" title="Сортировка">
                                            <svg viewBox="0 0 980 784">
                                                <path d="M269,0c10.8,2.3,21.2,5.4,30.3,12.1c17.6,13.1,26.6,30.6,27.5,52.5c0.1,2.2,0,4.3,0,6.5c0,163.9,0,327.9,0,491.8
                                                c0,2.2,0,4.3,0,7.7c2-1.8,3.3-2.8,4.5-4c32.1-30.8,64.1-61.7,96.2-92.5c24.8-23.8,63.5-21.9,84,4.1c16.6,21.2,15.2,49.8-4.3,69
                                                c-24.3,24-49.1,47.5-73.7,71.2C377.2,672.5,321,726.6,264.9,780.7c-1.1,1-1.9,2.2-2.9,3.3c-0.3,0-0.7,0-1,0c-1.2-1.3-2.4-2.7-3.7-4
                                                c-76.9-74-153.7-148.2-230.8-222C14.3,546.3,2.9,534.5,0,517c0-4.3,0-8.7,0-13c2.2-10.2,5.8-19.7,12.9-27.6
                                                c22.2-24.7,58.5-25.4,83.2-1.6c31.7,30.5,63.4,61,95,91.5c1.3,1.3,2.8,2.5,4.9,4.4c0-3.2,0-5.1,0-7.1c0-85.1,0-170.3,0-255.4
                                                c0-80.6,0-161.3,0-241.9c0-30.2,18.9-55.5,47.4-63.8c3.2-0.9,6.4-1.7,9.5-2.5C258.3,0,263.7,0,269,0z"/>
                                                <path d="M711,784c-3.4-0.8-6.8-1.5-10.1-2.5c-28.3-8.2-47.1-33.1-47.5-63.2c-0.2-15.3,0-30.7,0-46c0-150.5,0-300.9,0-451.4
                                                c0-2.1,0-4.2,0-7.5c-2.1,1.8-3.4,2.9-4.7,4.1c-31.9,30.7-63.9,61.5-95.8,92.2c-25.5,24.5-64.8,22-85.2-5.4
                                                c-15.1-20.2-13.7-48.2,4.4-66.3c21.5-21.6,43.8-42.5,65.7-63.6c59.2-57,118.3-114,177.5-171c1-1,1.9-2.2,2.8-3.4c0.3,0,0.7,0,1,0
                                                c0.7,0.7,1.3,1.5,2,2.2c45.6,43.9,91.2,87.8,136.8,131.7c35,33.7,70.2,67.4,105.1,101.3c28.8,28,20,73.2-17.2,87.3
                                                c-22.6,8.6-43.3,4.1-60.9-12.7c-24.6-23.4-49-47.1-73.5-70.7c-8.8-8.5-17.7-17-27.3-26.2c0,3.1,0,5.1,0,7c0,165.8,0,331.6,0,497.3
                                                c0,32.2-20.4,58-51.3,65.2c-1.9,0.4-3.8,1-5.7,1.5C721.7,784,716.3,784,711,784z"/>
                                            </svg>
                                        </div>
                                        <div class="sort_content">
                                            <a href="?<?php echo $sortQuery; ?>&sort=default"
                                                <?php echo ($activeSort == 'default' ? " class='first_str active_sort'" : "") ?> class="first_str">По умолчанию</a>
                                            <a href="?<?php echo $sortQuery; ?>&sort=price_asc"
                                                <?php echo ($activeSort == 'price_asc' ? " class='active_sort'" : "") ?>>По возрастанию цены</a>
                                            <a href="?<?php echo $sortQuery; ?>&sort=price_desc"
                                                <?php echo ($activeSort == 'price_desc' ? " class='active_sort'" : "") ?>>По убыванию цены</a>
                                            <a href="?<?php echo $sortQuery; ?>&sort=sale"
                                                <?php echo ($activeSort == 'sale' ? " class='active_sort'" : "") ?>>По скидке</a>
                                            <a href="?<?php echo $sortQuery; ?>&sort=rating"
                                                <?php echo ($activeSort == 'rating' ? " class='active_sort'" : "") ?>>По самой лучшей оценке</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            </div>
                    <div class="nav_sections">
                        <div class="nav_page"><a href="/catalog.php">Каталог</a></div>
                        <div class="nav_page"><a href="/adress.php">Наши адреса</a></div>
                        <div class="nav_page"><a href="/about.php">О нас</a></div>
                        <div class="nav_page"><a href="/support.php">Поддержка</a></div>
                        <?php if (isset($user['id']) && $user['status'] == 'administrator'): ?>
                        <div class="nav_page"><a href="../admin/adminpanel.php">Панель управления</a></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="bottom_row">
                    <div class="nav_container">
                        <button class="burger">
                            <svg viewBox="0 0 24 24">
                                <rect class="burger-line burger-line-top" x="4" y="6" width="16" height="2"/>
                                <rect class="burger-line burger-line-middle" x="4" y="11" width="16" height="2"/>
                                <rect class="burger-line burger-line-bottom" x="4" y="16" width="16" height="2"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <button class="back-to-top" aria-label="Наверх">
        <svg viewBox="0 0 24 24">
            <path d="M12 20V4m-6 6l6-6 6 6"/>
        </svg>
    </button>
</body>
</html>