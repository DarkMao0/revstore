<?php
require_once __DIR__ . '/../vendor/functions.php';
$user = authorizedUserData();
denyNoUser();
?>

<!DOCTYPE html>
<html  lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>REVSTORE - Удаление профиля</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/sign.css">
    <script defer src="/js/scroll.js"></script>
    <script defer src="/js/acc_pass.js"></script>
</head>
<body>
<?php include_once __DIR__ . '/../components/header.php' ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <form class="sign_up" action="/vendor/delete" method="post" novalidate>
                <h1>Удаление профиля</h1>
                <div class="warning">
                    <a>Вся информация о вас будет удалена.</a>
                    <a>Будут обнулены ваши скидки и персональные бонусы.</a>
                    <a>Все недоставленные заказы автоматически отменяются.</a>
                </div>
                <h3>Вы точно уверены?</h3>
                <?php if (checkAlert('error')): ?>
                    <a class="alert_con"><?php echo getAlert('error'); ?></a>
                <?php endif; ?>
                <div class="field_con">
                    <input
                        type="password"
                        name="password"
                        class="sup_field secure"
                        placeholder="Введите пароль"
                        <?php echo errorFrame('password'); ?>
                    >
                    <img class="eye_image" src="/img/svg/eye_closed.svg"/>
                    <?php if (checkError('password')): ?>
                        <a class="reg_alert"><?php echo errorMessage('password'); ?></a>
                    <?php endif; ?>
                </div>
                <button class="sup_but" type="submit">Удалить профиль</button>
                <a class="link_to" href="/user/edit.php">Отмена</a>
            </form>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../components/footer.php' ?>
</body>
</html>