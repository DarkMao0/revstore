<?php
require_once __DIR__ . '/control/functions.php';
denyUser();
?>

<!DOCTYPE html>
<html  lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
	<title>REVSTORE - Регистрация</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
	<link rel="stylesheet" href="/css/common.css">
	<link rel="stylesheet" href="/css/sign.css">
    <script defer src="/js/scroll.js"></script>
    <script defer src="/js/acc_avatar.js"></script>
    <script defer src="/js/acc_name.js"></script>
    <script defer src="/js/acc_pass.js"></script>
</head>
<body>
	<?php include_once __DIR__ . '/components/header.php' ?>
	<main class="content">
		<div class="con">
			<div class="main_dir">
				<div class="form_con">
					<form class="sign_up" action="/control/signup" method="post" enctype="multipart/form-data" novalidate>
						<h1>Регистрация</h1>
						<div class="avatar">
							<div class="img_container">
								<img class="stock_image" src="/img/svg/prof.svg">
								<input type="file"
									name="avatar"
									class="user_image"
									accept="image/*"
									title=''
								>
							</div>
							<a>Изображение профиля</a>
                            <?php if (checkError('avatar')): ?>
                                <a class="reg_alert"><?php echo errorMessage('avatar'); ?></a>
                            <?php endif; ?>
						</div>
                        <?php if (checkAlert('error')): ?>
                            <a class="alert_con"><?php echo getAlert('error'); ?></a>
                        <?php endif; ?>
						<div class="field_con">
							<input
								type="text"
								name="name"
								id="user_name"
								class="sup_field"
								placeholder="Ваше ФИО"
								value="<?php echo old('name'); ?>"
								<?php echo errorFrame('name'); ?>
							>
							<?php if (checkError('name')): ?>
            					<a class="reg_alert"><?php echo errorMessage('name'); ?></a>
       						<?php endif; ?>
						</div>
						<div class="field_con">
							<input
								type="email"
								name="login"
								class="sup_field"
								placeholder="Ваш e-mail"
								value="<?php echo old('login'); ?>"
								<?php echo errorFrame('login'); ?>
							>
							<?php if (checkError('login')): ?>
            					<a class="reg_alert"><?php echo errorMessage('login'); ?></a>
       						<?php endif; ?>
						</div>
						<div class="field_con">
							<input
								type="password"
								name="password"
								class="sup_field secure"
								placeholder="Пароль"
								<?php echo errorFrame('password'); ?>
							>
							<img class="eye_image" src="/img/svg/eye_closed.svg"/>
							<?php if (checkError('password')): ?>
            					<a class="reg_alert"><?php echo errorMessage('password'); ?></a>
       						<?php endif; ?>
						</div>
						<div class="field_con">
							<input
							type="password"
							name="confirm_password"
							class="sup_field secure"
							placeholder="Подтвердите пароль"
							<?php echo errorFrame('confirm_password'); ?>
							>
							<?php if (checkError('confirm_password')): ?>
            					<a class="reg_alert"><?php echo errorMessage('confirm_password'); ?></a>
       						<?php endif; ?>
						</div>
						<button class="sup_but" type="submit">Зарегестрироваться</button>
                        <a class="link_to" href="/signin-view.php">Уже есть профиль?</a>
					</form>
				</div>
			</div>
		</div>
	</main>
	<?php include_once __DIR__ .  '/components/footer.php' ?>
</body>
</html>