<?php
require_once __DIR__ . '/../vendor/functions.php';
$user = authorizedUserData();
denyNoUser();
?>

<!DOCTYPE html>
<html  lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Cerama Granit - Редактирование профиля</title>
    <link rel="icon" href="/img/fav.png" type="image/x-icon">
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="/css/sign.css">
    <script defer src="/js/scroll.js"></script>
    <script defer src="/js/acc_edit_avatar.js"></script>
    <script defer src="/js/acc_name.js"></script>
</head>
<body>
<?php include_once __DIR__ . '/../components/header.php' ?>
<main class="content">
    <div class="con">
        <div class="main_dir">
            <div class="form_con">
                <form class="sign_up" action="/vendor/update" method="post" enctype="multipart/form-data" novalidate>
                    <h1>Редактирование профиля</h1>
                    <div class="avatar">
                        <div class="img_container">
                            <?php if (isset($user['avatar'])): ?>
                                <img
                                    class="stock_image"
                                    src="<?php echo $user['avatar']; ?>"
                                    data-avatar="<?php echo $user['avatar']; ?>"
                                >
                            <?php else: ?>
                                <img class="stock_image" src="/img/svg/prof.svg">
                            <?php endif; ?>
                            <input
                                type="file"
                                name="avatar"
                                class="user_image"
                                accept="image/*"
                                title=''
                            >
                        </div>
                        <a>Изображение профиля</a>
                        <?php if (isset($user['avatar'])): ?>
                            <div class="checkbox">
                                <input
                                    type="checkbox"
                                    id="delete_avatar"
                                    class="checkbox_input"
                                    name="delete_avatar"
                                    value="delete"
                                >
                                <label for="delete_avatar" class="checkbox_label">Удалить</label>
                            </div>
                        <?php endif; ?>
                        <?php if (checkError('avatar')): ?>
                            <a class="reg_alert"><?php echo errorMessage('avatar'); ?></a>
                        <?php endif; ?>
                    </div>
                    <?php if (checkAlert('error')): ?>
                        <a class="alert_con"><?php echo getAlert('error'); ?></a>
                    <?php endif; ?>
                    <div class="field_con">
                        <a class="field_desc">ФИО</a>
                        <input
                            type="text"
                            name="name"
                            id="user_name"
                            class="sup_field"
                            placeholder="Ваше ФИО"
                            value="<?php echo $user['name']; ?>"
                            <?php echo errorFrame('name'); ?>
                        >
                        <?php if (checkError('name')): ?>
                            <a class="reg_alert"><?php echo errorMessage('name'); ?></a>
                        <?php endif; ?>
                    </div>
                    <div class="field_con">
                        <a class="field_desc">E-mail</a>
                        <input
                            type="email"
                            name="login"
                            class="sup_field"
                            placeholder="Ваш e-mail"
                            value="<?php echo $user['login']; ?>"
                            <?php echo errorFrame('login'); ?>
                        >
                        <?php if (checkError('login')): ?>
                            <a class="reg_alert"><?php echo errorMessage('login'); ?></a>
                        <?php endif; ?>
                    </div>
                    <div class="user_buttons">
                        <a href="/user/passchange.php" class="passchange_but">Изменить пароль</a>
                        <button class="sup_but" type="submit">Применить</button>
                    </div>
                    <a class="link_to" href="/user/deleteprofile.php">Удалить профиль</a>
                    <a class="link_to" href="/user/profile.php">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../components/footer.php' ?>
</body>
</html>